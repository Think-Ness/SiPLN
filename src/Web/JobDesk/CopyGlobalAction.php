<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\JsonResponse;
use Yiisoft\Db\Connection\ConnectionInterface;

final class CopyGlobalAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $instansiId = $_SESSION['instansi_id'] ?? null;
        if (!$instansiId) {
            return JsonResponse::create(['success' => false, 'message' => 'Otoritas gagal, sesi instansi tidak ditemukan.'], 400);
        }

        $body = json_decode((string)$request->getBody(), true) ?? $request->getParsedBody();
        $processIds = $body['process_ids'] ?? [];
        if (empty($processIds)) {
            return JsonResponse::create(['success' => false, 'message' => 'Pilih minimal satu proses untuk disalin.'], 400);
        }

        $transaction = $db->beginTransaction();
        try {
            $maxUrut = (int) $db->createCommand("SELECT MAX(urut) FROM jobdesk_master_process WHERE instansi_id = :instansi_id")
                                ->bindValue(':instansi_id', $instansiId)
                                ->queryScalar();
            
            $placeholders = [];
            $params = [];
            foreach ($processIds as $index => $id) {
                $placeholders[] = ":p$index";
                $params[":p$index"] = $id;
            }
            $placeholderStr = implode(',', $placeholders);
            
            $globalProcesses = $db->createCommand("SELECT * FROM jobdesk_master_process WHERE instansi_id IS NULL AND id IN ($placeholderStr)", $params)->queryAll();
            
            foreach ($globalProcesses as $gp) {
                $maxUrut++;
                $db->createCommand()->insert('jobdesk_master_process', [
                    'nama_proses' => $gp['nama_proses'],
                    'urut' => $maxUrut,
                    'aktif' => $gp['aktif'],
                    'instansi_id' => $instansiId
                ])->execute();
                
                $newProcessId = $db->getLastInsertID();
                
                $globalSteps = $db->createCommand("SELECT * FROM jobdesk_master_steps WHERE process_id = :pid ORDER BY urut ASC")
                                  ->bindValue(':pid', $gp['id'])->queryAll();
                foreach ($globalSteps as $gs) {
                    $db->createCommand()->insert('jobdesk_master_steps', [
                        'process_id' => $newProcessId,
                        'urut' => $gs['urut'],
                        'step_kode' => $gs['step_kode'],
                        'step_nama' => $gs['step_nama']
                    ])->execute();
                }
                
                $globalTarif = $db->createCommand("SELECT * FROM jobdesk_process_tarif WHERE process_id = :pid")
                                  ->bindValue(':pid', $gp['id'])->queryOne();
                if ($globalTarif) {
                    $db->createCommand()->insert('jobdesk_process_tarif', [
                        'process_id' => $newProcessId,
                        'biaya_instansi' => $globalTarif['biaya_instansi'],
                        'biaya_santri' => $globalTarif['biaya_santri'],
                        'keterangan' => $globalTarif['keterangan']
                    ])->execute();
                }
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Berhasil menyalin referensi dari Super Admin.']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyalin referensi: ' . $e->getMessage()], 500);
        }
    }
}
