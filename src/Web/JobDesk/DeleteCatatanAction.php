<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

class DeleteCatatanAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = $currentRoute->getArgument('id');

        try {
            $noteInfo = $db->createCommand("SELECT step_id, isi FROM jobdesk_step_notes WHERE id = :id", [':id' => $id])->queryOne();
            
            $db->createCommand()->delete('jobdesk_step_notes', ['id' => $id])->execute();
            
            if ($noteInfo) {
                AuditLogger::log($db, 'DELETE', 'JOB_DESK', $noteInfo['step_id'], $noteInfo['isi'], "Menghapus catatan pada tahap ID {$noteInfo['step_id']}");
            }
            
            return JsonResponse::create(['success' => true]);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
