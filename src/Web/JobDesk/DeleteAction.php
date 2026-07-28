<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

class DeleteAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $caseId = $currentRoute->getArgument('id');

        $transaction = $db->beginTransaction();
        try {
            // Delete notes, steps, and case
            $db->createCommand("DELETE FROM jobdesk_step_notes WHERE step_id IN (SELECT id FROM jobdesk_case_steps WHERE case_id = :cid)", [':cid' => $caseId])->execute();
            $db->createCommand("DELETE FROM jobdesk_case_steps WHERE case_id = :cid", [':cid' => $caseId])->execute();
            // Grab info before deleting
            $info = $db->createCommand("SELECT kds, process_id FROM jobdesk_cases WHERE id = :cid", [':cid' => $caseId])->queryOne();

            $db->createCommand("DELETE FROM jobdesk_cases WHERE id = :cid", [':cid' => $caseId])->execute();

            if ($info) {
                AuditLogger::log($db, 'DELETE', 'JOB_DESK', $caseId, $info, "Menghapus proses Job Desk (KDS: {$info['kds']})");
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
