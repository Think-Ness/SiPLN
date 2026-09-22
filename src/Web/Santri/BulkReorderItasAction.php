<?php

declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\AuditLogger;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class BulkReorderItasAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true) ?? $request->getParsedBody() ?? [];

        $scope = $data['scope'] ?? 'selected'; // 'selected' atau 'all'
        $startLevel = max(1, (int)($data['start_level'] ?? 1));

        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        $kdsList = [];

        if ($scope === 'selected') {
            $ids = $data['kds'] ?? [];
            if (empty($ids) || !is_array($ids)) {
                return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data santri yang dipilih.'], 400);
            }
            $kdsList = array_map('intval', $ids);
        } else {
            // Scope ALL: Ambil semua santri aktif dengan filter isolasi data tenant jika bukan super_admin
            $whereExt = "";
            $params = [];
            if ($role !== 'super_admin' && (!empty($myKepengurusan) || !empty($myPondok))) {
                $conditions = [];
                if (!empty($myKepengurusan)) {
                    $conditions[] = "kepengurusan = :my_kepengurusan";
                    $params[':my_kepengurusan'] = $myKepengurusan;
                }
                if (!empty($myPondok)) {
                    $conditions[] = "pondok = :my_pondok";
                    $params[':my_pondok'] = $myPondok;
                }
                if (!empty($conditions)) {
                    $whereExt = " AND (" . implode(" OR ", $conditions) . ") ";
                }
            }

            $sql = "SELECT DISTINCT kds FROM master_santri WHERE aktif = 1 $whereExt";
            $kdsList = $db->createCommand($sql, $params)->queryColumn();
            $kdsList = array_map('intval', $kdsList);
        }

        if (empty($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data santri yang ditemukan untuk diproses.'], 400);
        }

        $totalSantriProcessed = 0;
        $totalRowsUpdated = 0;

        $db->transaction(function() use ($db, $kdsList, $startLevel, &$totalSantriProcessed, &$totalRowsUpdated) {
            foreach ($kdsList as $kds) {
                $itasRows = $db->createCommand(
                    "SELECT id, level_itas, exp_itas FROM mtb_itas 
                     WHERE kds = :kds 
                     ORDER BY (exp_itas IS NULL OR exp_itas = '' OR exp_itas = '0000-00-00') ASC, exp_itas ASC, id ASC",
                    [':kds' => $kds]
                )->queryAll();

                if (empty($itasRows)) {
                    continue;
                }

                $totalSantriProcessed++;
                $currentLevel = $startLevel;

                foreach ($itasRows as $row) {
                    $targetLevelStr = (string)$currentLevel;
                    if ((string)$row['level_itas'] !== $targetLevelStr) {
                        $db->createCommand()->update(
                            'mtb_itas',
                            ['level_itas' => $targetLevelStr],
                            ['id' => $row['id']]
                        )->execute();
                        $totalRowsUpdated++;
                    }
                    $currentLevel++;
                }
            }
        });

        // Audit Log
        AuditLogger::log(
            $db,
            'BULK_REORDER_ITAS',
            "Menyusun ulang level ITAS massal (Scope: {$scope}) untuk {$totalSantriProcessed} santri, {$totalRowsUpdated} riwayat level diperbarui mulai Tingkat {$startLevel}."
        );

        return JsonResponse::create([
            'success' => true,
            'message' => "Berhasil memproses {$totalSantriProcessed} santri. Sebanyak {$totalRowsUpdated} riwayat level ITAS berhasil disesuaikan secara berurutan.",
            'total_santri' => $totalSantriProcessed,
            'total_updated' => $totalRowsUpdated
        ]);
    }
}
