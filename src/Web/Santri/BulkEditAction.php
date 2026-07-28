<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\AuditLogger;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class BulkEditAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true) ?? $request->getParsedBody() ?? [];
        
        $ids = $data['kds'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada santri yang dipilih.'], 400);
        }

        // Kumpulkan field yang akan diupdate (hanya yang tidak kosong di request)
        $updateData = [];
        $allowedFields = ['kelas', 'pondok', 'rayon', 'kepengurusan', 'kewarganegaraan', 'negara'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field]) && trim($data[$field]) !== '') {
                $updateData[$field] = trim($data[$field]);
            }
        }

        // Kumpulkan field untuk Paspor
        $pasporData = [];
        $pasporFields = ['tempat_paspor' => 'tempat_dikeluarkan', 'tgl_paspor' => 'tanggal_dikeluarkan', 'exp_paspor' => 'exp_paspor'];
        foreach ($pasporFields as $reqField => $dbField) {
            if (!empty($data[$reqField])) {
                $pasporData[$dbField] = $data[$reqField];
            }
        }

        // Kumpulkan field untuk ITAS
        $itasData = [];
        if (isset($data['level_itas']) && trim((string)$data['level_itas']) !== '') {
            $itasData['level_itas'] = (int)$data['level_itas'];
        }
        if (!empty($data['exp_itas'])) {
            $itasData['exp_itas'] = $data['exp_itas'];
        }

        if (empty($updateData) && empty($pasporData) && empty($itasData)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada kolom yang diisi untuk diubah.'], 400);
        }

        $docMode = $data['doc_mode'] ?? 'koreksi'; // 'koreksi' atau 'baru'

        try {
            $transaction = $db->beginTransaction();
            
            foreach ($ids as $kds) {
                $kds = (int)$kds;
                $oldData = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                
                if ($oldData) {
                    // 1. Update Master Santri
                    if (!empty($updateData)) {
                        $db->createCommand()->update('master_santri', $updateData, ['kds' => $kds])->execute();
                        $newData = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                        AuditLogger::log($db, 'UPDATE', 'SANTRI', $kds, $oldData, $newData, "Edit Massal Biodata Santri");
                    }

                    // 2. Update Paspor
                    if (!empty($pasporData)) {
                        $latestPaspor = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds=:kds AND aktif=1 LIMIT 1", [':kds' => $kds])->queryOne();
                        if ($latestPaspor) {
                            if ($docMode === 'baru') {
                                $db->createCommand()->update('mtb_paspor', ['aktif' => 0], ['kds' => $kds])->execute();
                                $newPasporData = array_merge([
                                    'kds' => $kds,
                                    'no_paspor' => '', // Dikosongkan jika baru
                                    'tempat_dikeluarkan' => $latestPaspor['tempat_dikeluarkan'],
                                    'tanggal_dikeluarkan' => $latestPaspor['tanggal_dikeluarkan'],
                                    'exp_paspor' => $latestPaspor['exp_paspor'],
                                    'aktif' => 1
                                ], $pasporData);
                                $db->createCommand()->insert('mtb_paspor', $newPasporData)->execute();
                            } else {
                                $db->createCommand()->update('mtb_paspor', $pasporData, ['id' => $latestPaspor['id']])->execute();
                            }
                        }
                    }

                    // 3. Update ITAS
                    if (!empty($itasData)) {
                        $latestItas = $db->createCommand("SELECT * FROM mtb_itas WHERE kds=:kds AND aktif=1 LIMIT 1", [':kds' => $kds])->queryOne();
                        if ($latestItas) {
                            if ($docMode === 'baru') {
                                $db->createCommand()->update('mtb_itas', ['aktif' => 0], ['kds' => $kds])->execute();
                                $newItasData = array_merge([
                                    'kds' => $kds,
                                    'no_itas' => '', // Dikosongkan jika baru
                                    'level_itas' => isset($itasData['level_itas']) ? $itasData['level_itas'] : (int)$latestItas['level_itas'] + 1,
                                    'exp_itas' => $latestItas['exp_itas'],
                                    'aktif' => 1
                                ], $itasData);
                                
                                // Make sure we override the level_itas if it was passed empty but array_merge overwrites it
                                if (!isset($itasData['level_itas'])) {
                                    $newItasData['level_itas'] = (int)$latestItas['level_itas'] + 1;
                                }
                                
                                $db->createCommand()->insert('mtb_itas', $newItasData)->execute();
                            } else {
                                $db->createCommand()->update('mtb_itas', $itasData, ['id' => $latestItas['id']])->execute();
                            }
                        }
                    }

                    // 4. FIREBASE DUAL-WRITE
                    $updatedData = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                    if ($updatedData) {
                        $latestPaspor = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds=:kds AND aktif=1 ORDER BY id DESC LIMIT 1", [':kds' => $kds])->queryOne();
                        $latestItas = $db->createCommand("SELECT * FROM mtb_itas WHERE kds=:kds AND aktif=1 ORDER BY id DESC LIMIT 1", [':kds' => $kds])->queryOne();
                        
                        \App\Shared\FirebaseSync::syncSantri(
                            (string)$kds, 
                            $updatedData, 
                            null, // instansiKode
                            $latestPaspor ?: null,
                            $latestItas ?: null
                        );
                    }
                }
            }

            $transaction->commit();

            return JsonResponse::create([
                'success' => true,
                'message' => count($ids) . ' santri berhasil diperbarui secara massal.'
            ]);
        } catch (\Throwable $e) {
            if (isset($transaction)) {
                $transaction->rollBack();
            }
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengedit massal: ' . $e->getMessage()], 500);
        }
    }
}
