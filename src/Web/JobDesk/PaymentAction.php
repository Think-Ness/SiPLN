<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

class PaymentAction
{
    /**
     * GET /api/job-desk/payment/{id} — Ambil data pembayaran untuk case
     */
    public function get(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $caseId = (int)$currentRoute->getArgument('id');
        
        $payment = $db->createCommand(
            "SELECT * FROM jobdesk_case_payment WHERE case_id = :cid",
            [':cid' => $caseId]
        )->queryOne();

        return JsonResponse::create(['success' => true, 'data' => $payment ?: null]);
    }

    /**
     * POST /api/job-desk/payment/{id} — Update pembayaran
     */
    public function update(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $caseId = (int)$currentRoute->getArgument('id');
        $data = $request->getParsedBody();
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $data = json_decode($rawBody, true) ?? [];
            }
        }

        $transaction = $db->beginTransaction();
        try {
            $existing = $db->createCommand(
                "SELECT id FROM jobdesk_case_payment WHERE case_id = :cid",
                [':cid' => $caseId]
            )->queryOne();

            $updateFields = [];
            
            if (isset($data['nominal_santri'])) {
                $updateFields['nominal_santri'] = (float)$data['nominal_santri'];
            }
            if (isset($data['nominal_instansi'])) {
                $updateFields['nominal_instansi'] = (float)$data['nominal_instansi'];
            }
            if (isset($data['status_bayar_santri'])) {
                $updateFields['status_bayar_santri'] = $data['status_bayar_santri'];
                if ($data['status_bayar_santri'] === 'lunas' && empty($data['tgl_bayar_santri'])) {
                    $updateFields['tgl_bayar_santri'] = date('Y-m-d');
                }
            }
            if (isset($data['tgl_bayar_santri'])) {
                $updateFields['tgl_bayar_santri'] = $data['tgl_bayar_santri'] ?: null;
            }
            if (isset($data['status_bayar_instansi'])) {
                $updateFields['status_bayar_instansi'] = $data['status_bayar_instansi'];
                if ($data['status_bayar_instansi'] === 'lunas' && empty($data['tgl_bayar_instansi'])) {
                    $updateFields['tgl_bayar_instansi'] = date('Y-m-d');
                }
            }
            if (isset($data['tgl_bayar_instansi'])) {
                $updateFields['tgl_bayar_instansi'] = $data['tgl_bayar_instansi'] ?: null;
            }
            if (isset($data['catatan'])) {
                $updateFields['catatan'] = $data['catatan'];
            }

            if ($existing) {
                $db->createCommand()->update('jobdesk_case_payment', $updateFields, ['case_id' => $caseId])->execute();
            } else {
                $updateFields['case_id'] = $caseId;
                $updateFields['created_by'] = 'Staf';
                $db->createCommand()->insert('jobdesk_case_payment', $updateFields)->execute();
            }

            AuditLogger::log($db, 'UPDATE', 'JOB_DESK_PAYMENT', $caseId, null, 
                "Memperbarui data pembayaran untuk case ID: $caseId");

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Data pembayaran berhasil disimpan.']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/job-desk/payment/bulk-update — Update massal pembayaran
     */
    public function bulkUpdate(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $data = $request->getParsedBody();
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $data = json_decode($rawBody, true) ?? [];
            }
        }

        $caseIds = $data['case_ids'] ?? [];
        $type = $data['type'] ?? 'santri'; // 'santri', 'instansi', or 'keduanya'
        $tglBayar = $data['tgl_bayar'] ?? date('Y-m-d');

        if (empty($caseIds)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada kasus yang dipilih.'], 400);
        }

        $transaction = $db->beginTransaction();
        try {
            $updateFields = [];
            if ($type === 'santri' || $type === 'keduanya') {
                $updateFields['status_bayar_santri'] = 'lunas';
                $updateFields['tgl_bayar_santri'] = $tglBayar;
            }
            if ($type === 'instansi' || $type === 'keduanya') {
                $updateFields['status_bayar_instansi'] = 'lunas';
                $updateFields['tgl_bayar_instansi'] = $tglBayar;
            }

            foreach ($caseIds as $cid) {
                $cid = (int)$cid;
                $existing = $db->createCommand("SELECT id FROM jobdesk_case_payment WHERE case_id = :cid", [':cid' => $cid])->queryOne();
                if ($existing) {
                    $db->createCommand()->update('jobdesk_case_payment', $updateFields, ['case_id' => $cid])->execute();
                } else {
                    $insFields = array_merge($updateFields, [
                        'case_id' => $cid,
                        'created_by' => 'Staf'
                    ]);
                    $db->createCommand()->insert('jobdesk_case_payment', $insFields)->execute();
                }
                AuditLogger::log($db, 'UPDATE', 'JOB_DESK_PAYMENT', $cid, null, "Memperbarui pembayaran massal (Tipe: $type)");
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Data pembayaran berhasil diupdate secara massal.']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/job-desk/payment/{id}/upload — Upload bukti bayar
     */
    public function upload(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $caseId = (int)$currentRoute->getArgument('id');
        
        $uploadedFiles = $request->getUploadedFiles();
        if (!isset($uploadedFiles['bukti_bayar']) || $uploadedFiles['bukti_bayar']->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'File tidak ditemukan atau gagal upload.'], 400);
        }

        $file = $uploadedFiles['bukti_bayar'];
        $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
        if (!in_array(strtolower($ext), $allowed)) {
            return JsonResponse::create(['success' => false, 'message' => 'Format file tidak didukung. Gunakan: ' . implode(', ', $allowed)], 400);
        }

        $filename = 'bukti_' . $caseId . '_' . time() . '.' . $ext;
        $dir = __DIR__ . '/../../../../public/uploads/bukti_bayar';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $file->moveTo($dir . '/' . $filename);
        $filePath = '/uploads/bukti_bayar/' . $filename;

        $db->createCommand()->update('jobdesk_case_payment', [
            'bukti_bayar_path' => $filePath,
        ], ['case_id' => $caseId])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Bukti bayar berhasil diunggah.', 'path' => $filePath]);
    }

    /**
     * GET /api/job-desk/payment/history/{kds} — Riwayat pembayaran per santri
     */
    public function history(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $kds = $currentRoute->getArgument('kds');

        $history = $db->createCommand("
            SELECT 
                p.*,
                j.process_id, j.tanggal_referensi, j.tgl_mulai_proses, j.status as case_status,
                j.created_at as case_created_at,
                pr.nama_proses,
                COALESCE((SELECT SUM(i.nominal) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id), 0) as total_cicilan,
                (SELECT COUNT(i.id) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id) as jumlah_cicilan
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases j ON p.case_id = j.id
            LEFT JOIN jobdesk_master_process pr ON j.process_id = pr.id
            WHERE j.kds = :kds
            ORDER BY j.created_at DESC
        ", [':kds' => $kds])->queryAll();

        // Calculate summary stats
        $totalBayarSantri = 0;
        $totalBayarInstansi = 0;
        $totalSelisih = 0;
        foreach ($history as $h) {
            if ($h['status_bayar_santri'] === 'lunas') {
                $totalBayarSantri += (float)$h['nominal_santri'];
            }
            if ($h['status_bayar_instansi'] === 'lunas') {
                $totalBayarInstansi += (float)$h['nominal_instansi'];
            }
            $totalSelisih += (float)$h['selisih_operasional'];
        }

        return JsonResponse::create([
            'success' => true,
            'data' => $history,
            'summary' => [
                'total_bayar_santri' => $totalBayarSantri,
                'total_bayar_instansi' => $totalBayarInstansi,
                'total_selisih' => $totalSelisih,
                'total_transaksi' => count($history),
            ]
        ]);
    }

    /**
     * POST /api/job-desk/payment/{id}/cicil — Tambah cicilan pembayaran santri
     */
    public function cicil(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $caseId = (int)$currentRoute->getArgument('id');
        $data = $request->getParsedBody();
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $data = json_decode($rawBody, true) ?? [];
            }
        }

        $nominal = (float)($data['nominal'] ?? 0);
        $tglBayar = $data['tgl_bayar'] ?? date('Y-m-d');
        $catatan = $data['catatan'] ?? '';

        if ($nominal <= 0) {
            return JsonResponse::create(['success' => false, 'message' => 'Nominal cicilan harus lebih dari 0.'], 400);
        }

        $transaction = $db->beginTransaction();
        try {
            // Get payment record
            $payment = $db->createCommand(
                "SELECT * FROM jobdesk_case_payment WHERE case_id = :cid",
                [':cid' => $caseId]
            )->queryOne();

            if (!$payment) {
                return JsonResponse::create(['success' => false, 'message' => 'Data pembayaran tidak ditemukan.'], 404);
            }

            // Calculate total existing cicilan
            $totalCicilan = (float)($db->createCommand(
                "SELECT COALESCE(SUM(nominal),0) FROM jobdesk_payment_installment WHERE case_id = :cid",
                [':cid' => $caseId]
            )->queryScalar() ?: 0);

            $nominalSantri = (float)$payment['nominal_santri'];
            $sisaTagihan = $nominalSantri - $totalCicilan;

            if ($nominal > $sisaTagihan) {
                return JsonResponse::create([
                    'success' => false, 
                    'message' => "Nominal cicilan (Rp " . number_format($nominal,0,',','.') . ") melebihi sisa tagihan (Rp " . number_format($sisaTagihan,0,',','.') . ")."
                ], 400);
            }

            // Insert cicilan
            $db->createCommand()->insert('jobdesk_payment_installment', [
                'case_id'    => $caseId,
                'nominal'    => $nominal,
                'tgl_bayar'  => $tglBayar,
                'catatan'    => $catatan,
                'created_by' => $_SESSION['nama_lengkap'] ?? 'Staf',
            ])->execute();

            $newTotal = $totalCicilan + $nominal;

            // Auto-lunas jika sudah cukup
            $isLunas = $newTotal >= $nominalSantri;
            if ($isLunas) {
                $db->createCommand()->update('jobdesk_case_payment', [
                    'status_bayar_santri' => 'lunas',
                    'tgl_bayar_santri'    => $tglBayar,
                ], ['case_id' => $caseId])->execute();
            }

            AuditLogger::log($db, 'INSERT', 'JOB_DESK_PAYMENT_CICIL', $caseId, null,
                "Cicilan Rp ".number_format($nominal,0,',','.')." untuk case ID: $caseId");

            $transaction->commit();
            return JsonResponse::create([
                'success'     => true,
                'message'     => $isLunas ? 'Cicilan terakhir tercatat & pembayaran LUNAS!' : 'Cicilan berhasil dicatat.',
                'is_lunas'    => $isLunas,
                'total_cicilan' => $newTotal,
                'sisa_tagihan'  => max(0, $nominalSantri - $newTotal),
                'nominal_santri' => $nominalSantri,
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/job-desk/payment/{id}/installments — Daftar cicilan untuk case
     */
    public function installments(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $caseId = (int)$currentRoute->getArgument('id');

        $payment = $db->createCommand(
            "SELECT p.*, m.nama_proses, s.nama, c.kds 
             FROM jobdesk_case_payment p
             JOIN jobdesk_cases c ON p.case_id = c.id
             JOIN master_santri s ON c.kds = s.kds
             JOIN jobdesk_master_process m ON c.process_id = m.id
             WHERE p.case_id = :cid",
            [':cid' => $caseId]
        )->queryOne();

        $installments = $db->createCommand(
            "SELECT * FROM jobdesk_payment_installment WHERE case_id = :cid ORDER BY tgl_bayar ASC, id ASC",
            [':cid' => $caseId]
        )->queryAll();

        $totalCicilan = array_sum(array_column($installments, 'nominal'));

        return JsonResponse::create([
            'success' => true,
            'payment' => $payment,
            'installments' => $installments,
            'total_cicilan' => $totalCicilan,
            'sisa_tagihan' => max(0, (float)($payment['nominal_santri'] ?? 0) - $totalCicilan),
        ]);
    }
}
