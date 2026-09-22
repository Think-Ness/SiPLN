<?php declare(strict_types=1);
namespace App\Web\InaktifData;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class BulkReaktifkanAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        $myKep = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        if (empty($role)) {
            return JsonResponse::create(['success' => false, 'message' => 'Unauthorized - Silakan login terlebih dahulu'], 403);
        }

        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true) ?? $request->getParsedBody() ?? [];
        $kdsList = $data['kds'] ?? [];

        if (empty($kdsList) || !is_array($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        $kdsList = array_map('intval', $kdsList);
        $kdsList = array_filter($kdsList, fn($k) => $k > 0);

        if (empty($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data valid yang dipilih'], 400);
        }

        // Check ownership if not super_admin
        if ($role !== 'super_admin' && (!empty($myKep) || !empty($myPondok))) {
            $kdsIn = implode(',', $kdsList);
            $conditions = [];
            $params = [];
            if (!empty($myKep)) {
                $conditions[] = "kepengurusan = :my_kep";
                $params[':my_kep'] = $myKep;
            }
            if (!empty($myPondok)) {
                $conditions[] = "pondok = :my_pondok";
                $params[':my_pondok'] = $myPondok;
            }
            $whereSql = implode(' OR ', $conditions);
            $validKds = $db->createCommand("SELECT kds FROM master_santri WHERE kds IN ($kdsIn) AND ($whereSql)", $params)->queryColumn();
            $kdsList = array_map('intval', $validKds);
            
            if (empty($kdsList)) {
                return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data milik instansi/pondok Anda yang dapat diaktifkan'], 403);
            }
        }

        try {
            $db->createCommand()->update('master_santri', ['aktif' => 1], ['IN', 'kds', $kdsList])->execute();
            AuditLogger::log($db, 'UPDATE', 'SANTRI', null, null, "Mengaktifkan kembali " . count($kdsList) . " data santri secara massal");

            // FIREBASE DUAL-WRITE
            foreach ($kdsList as $kds) {
                $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                if ($santriDb) {
                    \App\Shared\FirebaseSync::syncSantri((string)$kds, $santriDb, null);
                }
            }

            return JsonResponse::create([
                'success' => true,
                'message' => count($kdsList) . ' data santri berhasil diaktifkan kembali'
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
