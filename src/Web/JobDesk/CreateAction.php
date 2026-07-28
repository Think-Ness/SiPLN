<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

class CreateAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }
        
        $processId = (int)($body['process_id'] ?? 1); // Default 1 for ITAS
        $items = $body['items'] ?? [];
        
        if (empty($items) || !is_array($items)) {
            // Fallback to single item for backward compatibility if needed, but we'll focus on bulk
            $kds = $body['kds'] ?? null;
            $exp_itas_lama = $body['exp_itas'] ?? null;
            if ($kds && $exp_itas_lama) {
                $items = [['kds' => $kds, 'exp_itas' => $exp_itas_lama]];
            } else {
                return JsonResponse::create(['success' => false, 'message' => 'Data tidak lengkap. Harap pilih minimal 1 santri.'], 400);
            }
        }

        // Get template steps for this process
        $templateSteps = $db->createCommand("SELECT urut, step_kode, step_nama FROM jobdesk_master_steps WHERE process_id = :pid ORDER BY urut ASC", [':pid' => $processId])->queryAll();
        if (empty($templateSteps)) {
            return JsonResponse::create(['success' => false, 'message' => 'Jenis proses tidak ditemukan atau tidak memiliki tahapan.'], 400);
        }

        // Get default tarif for this process
        $tarif = $db->createCommand("SELECT biaya_instansi, biaya_santri FROM jobdesk_process_tarif WHERE process_id = :pid", [':pid' => $processId])->queryOne();
        $defaultBiayaInstansi = $tarif ? (float)$tarif['biaya_instansi'] : 0;
        $defaultBiayaSantri = $tarif ? (float)$tarif['biaya_santri'] : 0;

        $transaction = $db->beginTransaction();
        try {
            $createdCount = 0;
            $skippedActiveCount = 0;
            $skippedPindahanCount = 0;
            $skippedActiveNames = [];
            $skippedPindahanNames = [];

            foreach ($items as $item) {
                $kds = $item['kds'] ?? null;
                $tglReferensi = $item['exp_itas'] ?? $item['tanggal_referensi'] ?? null;

                if (!$kds) {
                    continue;
                }

                // SECURITY: Cek Hak Akses Kepengurusan & Pondok (Cross-Tenant Data Isolation)
                $role = $_SESSION['role'] ?? '';
                $myKepengurusan = trim($_SESSION['def_kepengurusan'] ?? '');
                $myPondok = trim($_SESSION['def_pondok'] ?? '');
                
                if ($role !== 'super_admin' && ($myKepengurusan !== '' || $myPondok !== '')) {
                    $santriInfo = $db->createCommand("SELECT kepengurusan, pondok FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                    
                    $sKep = trim((string)($santriInfo['kepengurusan'] ?? ''));
                    $sPon = trim((string)($santriInfo['pondok'] ?? ''));
                    
                    $hasAccess = false;
                    if ($myKepengurusan !== '' && strcasecmp($sKep, $myKepengurusan) === 0) $hasAccess = true;
                    if ($myPondok !== '' && strcasecmp($sPon, $myPondok) === 0) $hasAccess = true;
                    
                    if (!$hasAccess) {
                        $skippedPindahanCount++;
                        $santriName = $db->createCommand("SELECT nama FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryScalar();
                        if ($santriName) $skippedPindahanNames[] = $santriName;
                        continue;
                    }
                }

                // Cek apakah sudah ada proses aktif untuk kds ini dengan process_id yang sama
                $cek = $db->createCommand("SELECT id FROM jobdesk_cases WHERE kds = :kds AND process_id = :pid AND status = 'aktif'", [':kds' => $kds, ':pid' => $processId])->queryOne();
                if ($cek) {
                    $skippedActiveCount++;
                    $santriName = $db->createCommand("SELECT nama FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryScalar();
                    if ($santriName) $skippedActiveNames[] = $santriName;
                    continue;
                }

                // Default tgl mulai = hari ini. Jika ITAS, bisa dikurangi 3 bulan dari tgl referensi, atau biarkan statis
                $tglMulai = $tglReferensi ? date('Y-m-d', strtotime('-3 months', strtotime($tglReferensi))) : date('Y-m-d');

                $db->createCommand()->insert('jobdesk_cases', [
                    'process_id' => $processId,
                    'kds' => $kds,
                    'tanggal_referensi' => $tglReferensi,
                    'tgl_mulai_proses' => $tglMulai,
                    'status' => 'aktif',
                    'created_by' => 'Sistem / Staf'
                ])->execute();
                
                $caseId = $db->getLastInsertID();
                
                $processName = $processId == 1 ? 'ITAS' : 'Paspor';
                AuditLogger::log($db, 'CREATE', 'JOB_DESK', $caseId, null, "Membuka proses perpanjangan $processName baru untuk KDS: $kds");

                // Insert Dynamic Steps
                foreach ($templateSteps as $s) {
                    $db->createCommand()->insert('jobdesk_case_steps', [
                        'case_id' => $caseId,
                        'step_urut' => $s['urut'],
                        'step_kode' => $s['step_kode'],
                        'step_nama' => $s['step_nama'],
                        'status' => 'pending'
                    ])->execute();
                }

                // Auto-insert payment record with default tarif
                $db->createCommand()->insert('jobdesk_case_payment', [
                    'case_id' => $caseId,
                    'nominal_santri' => $defaultBiayaSantri,
                    'nominal_instansi' => $defaultBiayaInstansi,
                    'status_bayar_santri' => 'belum',
                    'status_bayar_instansi' => 'belum',
                    'created_by' => 'Sistem (Auto)'
                ])->execute();
                
                $createdCount++;
            }

            $transaction->commit();
            
            $message = "$createdCount proses perpanjangan berhasil dibuka.";
            
            if ($skippedActiveCount > 0) {
                $names = implode(', ', array_slice($skippedActiveNames, 0, 3));
                $more = count($skippedActiveNames) > 3 ? " dan " . (count($skippedActiveNames) - 3) . " lainnya" : "";
                $message .= "<br><br><b>$skippedActiveCount dilewati</b> karena prosesnya sudah aktif:<br><span class='text-muted'>$names$more</span>";
            }
            
            if ($skippedPindahanCount > 0) {
                $names = implode(', ', array_slice($skippedPindahanNames, 0, 3));
                $more = count($skippedPindahanNames) > 3 ? " dan " . (count($skippedPindahanNames) - 3) . " lainnya" : "";
                $message .= "<br><br><b>$skippedPindahanCount diblokir</b> karena berstatus Santri Pindahan (hanya bisa diproses oleh kepengurusan asal):<br><span class='text-danger'>$names$more</span>";
            }
            
            return JsonResponse::create(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
