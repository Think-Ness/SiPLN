<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\JsonResponse;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class SaveSettingsAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $data = json_decode((string)$request->getBody(), true) ?? $request->getParsedBody();
        
        if (empty($data['processes']) || !is_array($data['processes'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Invalid data format'], 400);
        }

        $instansiId = $_SESSION['instansi_id'] ?? null;
        $role = $_SESSION['role'] ?? '';
        $dbInstansiId = ($role === 'super_admin') ? null : $instansiId;
        
        $transaction = $db->beginTransaction();
        try {
            $whereClause = ($role === 'super_admin') ? "instansi_id IS NULL" : "instansi_id = :instansi_id";
            $params = ($role === 'super_admin') ? [] : [':instansi_id' => $instansiId];
            
            $oldProcesses = $db->createCommand("SELECT id, nama_proses, urut FROM jobdesk_master_process WHERE $whereClause", $params)->queryAll();
            // Fetch steps for those processes
            $oldSteps = $db->createCommand("SELECT id, process_id, step_nama, step_kode, urut FROM jobdesk_master_steps WHERE process_id IN (SELECT id FROM jobdesk_master_process WHERE $whereClause)", $params)->queryAll();
            $oldData = ['Proses' => $oldProcesses, 'Langkah' => $oldSteps];
            $existingProcessIds = [];
            foreach ($data['processes'] as $p) {
                if (!empty($p['id'])) {
                    $existingProcessIds[] = (int)$p['id'];
                }
            }

            // Remove processes that were deleted
            if (!empty($existingProcessIds)) {
                $db->createCommand("DELETE FROM jobdesk_master_process WHERE $whereClause AND id NOT IN (" . implode(',', $existingProcessIds) . ")", $params)->execute();
                $db->createCommand("DELETE FROM jobdesk_master_steps WHERE process_id NOT IN (SELECT id FROM jobdesk_master_process)")->execute();
            } else {
                $db->createCommand("DELETE FROM jobdesk_master_process WHERE $whereClause", $params)->execute();
                $db->createCommand("DELETE FROM jobdesk_master_steps WHERE process_id NOT IN (SELECT id FROM jobdesk_master_process)")->execute();
            }

            foreach ($data['processes'] as $pIndex => $p) {
                $processId = null;
                $urut = $pIndex + 1;
                
                if (!empty($p['id'])) {
                    $db->createCommand()
                        ->update('jobdesk_master_process', [
                            'nama_proses' => $p['nama_proses'],
                            'urut' => $urut,
                            'instansi_id' => $dbInstansiId
                        ], ['id' => $p['id']])
                        ->execute();
                    $processId = $p['id'];
                } else {
                    $db->createCommand()
                        ->insert('jobdesk_master_process', [
                            'nama_proses' => $p['nama_proses'],
                            'urut' => $urut,
                            'instansi_id' => $dbInstansiId
                        ])
                        ->execute();
                    $processId = $db->getLastInsertID();
                }

                $existingStepIds = [];
                if (!empty($p['steps']) && is_array($p['steps'])) {
                    foreach ($p['steps'] as $s) {
                        if (!empty($s['id']) && strpos((string)$s['id'], 'new') === false) {
                            $existingStepIds[] = (int)$s['id'];
                        }
                    }
                }

                if (!empty($existingStepIds)) {
                    $db->createCommand("DELETE FROM jobdesk_master_steps WHERE process_id = :pid AND id NOT IN (" . implode(',', $existingStepIds) . ")")
                       ->bindValue(':pid', $processId)
                       ->execute();
                } else {
                    $db->createCommand("DELETE FROM jobdesk_master_steps WHERE process_id = :pid")
                       ->bindValue(':pid', $processId)
                       ->execute();
                }

                if (!empty($p['steps']) && is_array($p['steps'])) {
                    foreach ($p['steps'] as $sIndex => $s) {
                        $stepUrut = $sIndex + 1;
                        if (!empty($s['id']) && strpos((string)$s['id'], 'new') === false) {
                            $db->createCommand()
                                ->update('jobdesk_master_steps', [
                                    'step_nama' => $s['step_nama'],
                                    'step_kode' => $s['step_kode'],
                                    'urut' => $stepUrut,
                                ], ['id' => $s['id']])
                                ->execute();
                        } else {
                            $db->createCommand()
                                ->insert('jobdesk_master_steps', [
                                    'process_id' => $processId,
                                    'step_nama' => $s['step_nama'],
                                    'step_kode' => $s['step_kode'],
                                    'urut' => $stepUrut,
                                ])
                                ->execute();
                        }
                    }
                }

                // Upsert tarif for this process
                $biayaInstansi = (float)($p['biaya_instansi'] ?? 0);
                $biayaSantri = (float)($p['biaya_santri'] ?? 0);
                $keteranganTarif = trim($p['keterangan_tarif'] ?? '');
                
                $existingTarif = $db->createCommand("SELECT id FROM jobdesk_process_tarif WHERE process_id = :pid", [':pid' => $processId])->queryScalar();
                if ($existingTarif) {
                    $db->createCommand()->update('jobdesk_process_tarif', [
                        'biaya_instansi' => $biayaInstansi,
                        'biaya_santri' => $biayaSantri,
                        'keterangan' => $keteranganTarif ?: null,
                    ], ['process_id' => $processId])->execute();
                } else {
                    $db->createCommand()->insert('jobdesk_process_tarif', [
                        'process_id' => $processId,
                        'biaya_instansi' => $biayaInstansi,
                        'biaya_santri' => $biayaSantri,
                        'keterangan' => $keteranganTarif ?: null,
                    ])->execute();
                }
            }


            $newProcesses = $db->createCommand("SELECT id, nama_proses, urut FROM jobdesk_master_process WHERE $whereClause", $params)->queryAll();
            $newSteps = $db->createCommand("SELECT id, process_id, step_nama, step_kode, urut FROM jobdesk_master_steps WHERE process_id IN (SELECT id FROM jobdesk_master_process WHERE $whereClause)", $params)->queryAll();
            $newData = ['Proses' => $newProcesses, 'Langkah' => $newSteps];

            AuditLogger::log($db, 'UPDATE', 'PENGATURAN_JOB_DESK', null, $oldData, $newData, "Menyimpan pengaturan alur proses Job Desk");

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Pengaturan berhasil disimpan.']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
