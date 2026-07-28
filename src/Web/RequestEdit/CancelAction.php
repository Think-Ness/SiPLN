<?php
declare(strict_types=1);

namespace App\Web\RequestEdit;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class CancelAction
{
    public function __invoke(
        ServerRequestInterface $request,
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = $currentRoute->getArgument('id');
        $myInstansiKode = $_SESSION['instansi_id'] ?? '';

        $req = $db->createCommand("SELECT * FROM santri_edit_requests WHERE id = :id", [':id' => $id])->queryOne();
        if (!$req) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Usulan tidak ditemukan.'
            ]);
        }

        if ((string)$req['requested_by_instansi_id'] !== (string)$myInstansiKode) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Anda tidak berhak membatalkan usulan milik instansi lain.'
            ]);
        }

        if ($req['status'] !== 'pending') {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Usulan sudah diproses, tidak bisa dibatalkan.'
            ]);
        }

        try {
            $db->createCommand()->delete('santri_edit_requests', ['id' => $id])->execute();
            AuditLogger::log($db, 'CANCEL', 'REQUEST_EDIT', $id, $req, null);
            return JsonResponse::create([
                'success' => true,
                'message' => 'Pengajuan berhasil dibatalkan dan dihapus.'
            ]);
        } catch (\Exception $e) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }
}
