<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

class BulkUpdateStepAction
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
        
        $caseIds = $body['case_ids'] ?? [];
        $stepUrut = isset($body['step_urut']) ? (int)$body['step_urut'] : null;
        $status = $body['status'] ?? 'pending';
        $date = $body['date'] ?? null;
        $note = $body['note'] ?? '';

        if (empty($caseIds) || !is_array($caseIds)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        if (!$stepUrut) {
            return JsonResponse::create(['success' => false, 'message' => 'Tahap tidak dipilih'], 400);
        }

        if (!$date) $date = null;

        file_put_contents(__DIR__ . '/debug_bulk.log', "Payload: " . print_r($body, true) . "\n", FILE_APPEND);

        $transaction = $db->beginTransaction();
        try {
            $updatedCount = 0;

            foreach ($caseIds as $caseId) {
                $caseId = (int)$caseId;
                // Find the step ID for this case and step_urut
                $step = $db->createCommand("SELECT id FROM jobdesk_case_steps WHERE case_id = :cid AND step_urut = :urut", [
                    ':cid' => $caseId,
                    ':urut' => $stepUrut
                ])->queryOne();
                
                file_put_contents(__DIR__ . '/debug_bulk.log', "Checking case_id $caseId, step_urut $stepUrut. Result: " . print_r($step, true) . "\n", FILE_APPEND);

                if ($step) {
                    $stepId = $step['id'];

                    // Update step
                    $db->createCommand()->update('jobdesk_case_steps', [
                        'status' => $status,
                        'tgl_realisasi' => $date,
                        'updated_by' => 'Staf'
                    ], ['id' => $stepId])->execute();

                    // Jika ada note, masukkan ke log/catatan
                    if (!empty(trim($note))) {
                        $db->createCommand()->insert('jobdesk_step_notes', [
                            'step_id' => $stepId,
                            'tipe' => $status === 'selesai' ? 'selesai' : ($status === 'blocked' ? 'kekurangan' : 'info'),
                            'isi' => $note,
                            'created_by' => 'Staf'
                        ])->execute();
                    }

                    // Auto-complete previous steps
                    if ($status === 'selesai' || $status === 'proses') {
                        $db->createCommand()->update('jobdesk_case_steps', [
                            'status' => 'selesai',
                            'tgl_realisasi' => $date ?? date('Y-m-d'),
                            'updated_by' => 'Staf (Auto)'
                        ], ['and', ['=', 'case_id', $caseId], ['<', 'step_urut', $stepUrut], ['!=', 'status', 'selesai']])->execute();
                    }

                    // Check if all steps are done to close the case
                    $totalSteps = $db->createCommand("SELECT COUNT(*) FROM jobdesk_case_steps WHERE case_id = :id", [':id' => $caseId])->queryScalar();
                    $completed = $db->createCommand("SELECT COUNT(*) FROM jobdesk_case_steps WHERE case_id = :id AND status = 'selesai'", [':id' => $caseId])->queryScalar();
                    
                    if ($totalSteps > 0 && $completed == $totalSteps) {
                        $db->createCommand()->update('jobdesk_cases', ['status' => 'selesai'], ['id' => $caseId])->execute();
                    } else {
                        $db->createCommand()->update('jobdesk_cases', ['status' => 'aktif'], ['id' => $caseId])->execute();
                    }
                    
                    AuditLogger::log($db, 'UPDATE', 'JOB_DESK', $stepId, null, "Mengubah status tahap ke-$stepUrut menjadi '$status' untuk proses ID $caseId (massal)");

                    $updatedCount++;
                }
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => "$updatedCount data berhasil diupdate."]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
