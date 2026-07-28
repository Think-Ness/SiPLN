<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

class AddCatatanAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $stepId = $currentRoute->getArgument('id');
        $body = $request->getParsedBody();
        $tipe = $body['tipe'] ?? 'info';
        $isi = $body['isi'] ?? '';
        $berkas = $body['berkas_kurang'] ?? null;

        if (empty(trim($isi))) {
            return JsonResponse::create(['success' => false, 'message' => 'Isi catatan tidak boleh kosong'], 400);
        }

        try {
            $db->createCommand()->insert('jobdesk_step_notes', [
                'step_id' => $stepId,
                'tipe' => $tipe,
                'isi' => $isi,
                'berkas_kurang' => $berkas,
                'created_by' => 'Staf'
            ])->execute();
            
            $noteId = $db->getLastInsertID();
            AuditLogger::log($db, 'CREATE', 'JOB_DESK', $stepId, null, "Menambahkan catatan '$tipe' pada tahap ID $stepId");

            return JsonResponse::create(['success' => true]);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
