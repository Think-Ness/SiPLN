<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

class BulkDeleteAction
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

        if (empty($caseIds) || !is_array($caseIds)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        $transaction = $db->beginTransaction();
        try {
            $deletedCount = 0;

            foreach ($caseIds as $caseId) {
                // Delete notes, steps, and case
                $db->createCommand("DELETE FROM jobdesk_step_notes WHERE step_id IN (SELECT id FROM jobdesk_case_steps WHERE case_id = :cid)", [':cid' => $caseId])->execute();
                $db->createCommand("DELETE FROM jobdesk_case_steps WHERE case_id = :cid", [':cid' => $caseId])->execute();
                // Grab info before deleting
                $info = $db->createCommand("SELECT kds, process_id FROM jobdesk_cases WHERE id = :cid", [':cid' => $caseId])->queryOne();

                $db->createCommand("DELETE FROM jobdesk_cases WHERE id = :cid", [':cid' => $caseId])->execute();
                
                if ($info) {
                    AuditLogger::log($db, 'DELETE', 'JOB_DESK', $caseId, $info, "Menghapus proses Job Desk (KDS: {$info['kds']}) secara massal");
                }
                
                $deletedCount++;
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => "$deletedCount data berhasil dihapus."]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
