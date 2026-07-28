<?php declare(strict_types=1);
namespace App\Web\Santri;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class DeleteAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');
        try {
            $db->createCommand()->update('master_santri', ['aktif' => 0], ['kds' => $kds])->execute();
            AuditLogger::log($db, 'UPDATE', 'SANTRI', $kds, null, "Menonaktifkan data santri (KDS: $kds)");
            
            // FIREBASE DUAL-WRITE
            $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
            if ($santriDb) {
                \App\Shared\FirebaseSync::syncSantri((string)$kds, $santriDb, null);
            }

            return JsonResponse::create(['success' => true, 'message' => 'Santri berhasil dinonaktifkan']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
