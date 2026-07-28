<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use App\Shared\AuditLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class DeleteMailingAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int)$currentRoute->getArgument('id', '0');

        $mailing = $db->createCommand(
            "SELECT kode_mailing FROM surat_mailing WHERE id = :id",
            [':id' => $id]
        )->queryOne();

        if (!$mailing) {
            return JsonResponse::create(['success' => false, 'message' => 'Mailing tidak ditemukan.'], 404);
        }

        // CASCADE will handle surat_mailing_santri and surat_generated
        $db->createCommand("DELETE FROM surat_mailing WHERE id = :id", [':id' => $id])->execute();

        AuditLogger::log($db, 'DELETE', 'SURAT_MAILING', (string)$id, null,
            "Menghapus mailing {$mailing['kode_mailing']}");

        return JsonResponse::create([
            'success' => true,
            'message' => "Mailing {$mailing['kode_mailing']} berhasil dihapus.",
        ]);
    }
}
