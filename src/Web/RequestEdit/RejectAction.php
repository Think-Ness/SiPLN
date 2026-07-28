<?php
declare(strict_types=1);

namespace App\Web\RequestEdit;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class RejectAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        
        $req = $db->createCommand("SELECT * FROM santri_edit_requests WHERE id = :id AND status = 'pending'", [':id' => $id])->queryOne();

        if (!$req) {
            return JsonResponse::create(['success' => false, 'message' => 'Request tidak ditemukan atau sudah diproses.'], 404);
        }

        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        if ($myKepengurusan !== '' && trim(strtolower($req['target_kepengurusan'])) !== trim(strtolower($myKepengurusan))) {
            return JsonResponse::create(['success' => false, 'message' => 'Anda tidak berhak menolak request ini.'], 403);
        }

        $db->createCommand()->update('santri_edit_requests', ['status' => 'rejected'], ['id' => $id])->execute();

        $changes = json_decode($req['requested_changes'], true);
        $oldData = ['Status_Request' => 'Pending', 'KDS_Target' => $req['kds']];
        $newData = ['Status_Request' => 'Ditolak', 'KDS_Target' => $req['kds'], 'Perubahan_Yang_Ditolak' => $changes];

        AuditLogger::log($db, 'REJECT', 'REQUEST_EDIT', $id, $oldData, $newData, "Menolak permohonan perubahan data santri KDS: {$req['kds']}");

        return JsonResponse::create(['success' => true, 'message' => 'Request telah ditolak. Data santri tidak mengalami perubahan.']);
    }
}
