<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

class UpdateCatatanAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = $currentRoute->getArgument('id');
        $body = $request->getParsedBody();
        $stepId = $body['step_id'] ?? null;
        $tipe = $body['tipe'] ?? 'info';
        $isi = $body['isi'] ?? '';
        $berkas = $body['berkas_kurang'] ?? null;

        if (empty(trim($isi)) || empty($stepId)) {
            return JsonResponse::create(['success' => false, 'message' => 'Isi catatan dan tahap tidak boleh kosong'], 400);
        }

        try {
            $db->createCommand()->update('jobdesk_step_notes', [
                'step_id' => $stepId,
                'tipe' => $tipe,
                'isi' => $isi,
                'berkas_kurang' => $berkas
            ], ['id' => $id])->execute();
            
            AuditLogger::log($db, 'UPDATE', 'JOB_DESK', $stepId, null, "Mengubah catatan '$tipe' pada tahap ID $stepId");

            return JsonResponse::create(['success' => true]);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
