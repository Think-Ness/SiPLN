<?php declare(strict_types=1);
namespace App\Web\Santri;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class ToggleAktifAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');
        try {
            $current = (int) $db->createCommand("SELECT aktif FROM master_santri WHERE kds=:k", [':k' => $kds])->queryScalar();
            $new = $current === 1 ? 0 : 1;
            $db->createCommand()->update('master_santri', ['aktif' => $new], ['kds' => $kds])->execute();
            $msg = $new === 1 ? 'Santri berhasil diaktifkan' : 'Santri berhasil dinonaktifkan';
            
            AuditLogger::log($db, 'UPDATE', 'SANTRI', $kds, null, "$msg (KDS: $kds)");
            
            return JsonResponse::create(['success' => true, 'message' => $msg, 'aktif' => $new]);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
