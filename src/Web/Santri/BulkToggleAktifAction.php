<?php declare(strict_types=1);
namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class BulkToggleAktifAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true) ?? $request->getParsedBody() ?? [];
        $kdsList = $data['kds'] ?? [];
        
        if (empty($kdsList) || !is_array($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        // Sanitize to integers
        $kdsList = array_map('intval', $kdsList);

        try {
            $db->createCommand()->update('master_santri', ['aktif' => 0], ['IN', 'kds', $kdsList])->execute();
            AuditLogger::log($db, 'UPDATE', 'SANTRI', null, null, "Menonaktifkan " . count($kdsList) . " data santri secara massal");

            // FIREBASE DUAL-WRITE
            foreach ($kdsList as $kds) {
                $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                if ($santriDb) {
                    \App\Shared\FirebaseSync::syncSantri((string)$kds, $santriDb, null);
                }
            }
            return JsonResponse::create(['success' => true, 'message' => count($kdsList) . ' santri berhasil dinonaktifkan']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
