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
        $isPreview = !empty($data['preview']);

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

            $sql = "SELECT DISTINCT kds FROM master_santri WHERE aktif = 1 $whereExt ORDER BY nama ASC";
            $kdsList = $db->createCommand($sql, $params)->queryColumn();
            $kdsList = array_map('intval', $kdsList);
        }

        if (empty($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data santri yang ditemukan untuk diproses.'], 400);
        }

        // =========================================================================
        // MODE 1: PRATINJAU (DRY-RUN / PREVIEW)
        // =========================================================================
        if ($isPreview) {
            $previewItems = [];
            $totalSantriWithChanges = 0;
            $totalRowsWithChanges = 0;
            $totalSantriExamined = 0;

            // Fetch info nama, kelas, pondok santri
            $santriMap = [];
            if (!empty($kdsList)) {
                // Chunk to prevent large IN queries
                $chunks = array_chunk($kdsList, 500);
                foreach ($chunks as $chunk) {
                    $inKds = implode(',', $chunk);
                    $santriRows = $db->createCommand("SELECT kds, nama, kelas, pondok FROM master_santri WHERE kds IN ($inKds)")->queryAll();
                    foreach ($santriRows as $sr) {
                        $santriMap[$sr['kds']] = $sr;
                    }
                }
            }

            foreach ($kdsList as $kds) {
                $itasRows = $db->createCommand(
                    "SELECT id, no_itas, level_itas, exp_itas FROM mtb_itas 
                     WHERE kds = :kds 
                     ORDER BY (exp_itas IS NULL OR exp_itas = '' OR exp_itas = '0000-00-00') ASC, exp_itas ASC, id ASC",
                    [':kds' => $kds]
                )->queryAll();

                if (empty($itasRows)) {
                    continue;
                }

                $totalSantriExamined++;
                $currentLevel = $startLevel;
                $santriHasChanges = false;
                $itasListPreview = [];

                foreach ($itasRows as $row) {
                    $targetLevelStr = (string)$currentLevel;
                    $isDiff = ((string)($row['level_itas'] ?? '') !== $targetLevelStr);
                    if ($isDiff) {
                        $santriHasChanges = true;
                        $totalRowsWithChanges++;
                    }

                    $itasListPreview[] = [
                        'id' => $row['id'],
                        'no_itas' => $row['no_itas'] ?? '-',
                        'exp_itas' => $row['exp_itas'],
                        'current_level' => $row['level_itas'] !== null && $row['level_itas'] !== '' ? $row['level_itas'] : '-',
                        'target_level' => $targetLevelStr,
                        'is_changed' => $isDiff
                    ];
                    $currentLevel++;
                }

                if ($santriHasChanges) {
                    $totalSantriWithChanges++;
                }

                $sInfo = $santriMap[$kds] ?? ['nama' => "Santri #{$kds}", 'kelas' => '-', 'pondok' => '-'];

                $previewItems[] = [
                    'kds' => $kds,
                    'nama' => $sInfo['nama'] ?? '-',
                    'kelas' => $sInfo['kelas'] ?? '-',
                    'pondok' => $sInfo['pondok'] ?? '-',
                    'has_changes' => $santriHasChanges,
                    'total_itas' => count($itasRows),
                    'itas_list' => $itasListPreview
                ];
            }

            return JsonResponse::create([
                'success' => true,
                'is_preview' => true,
                'summary' => [
                    'total_santri_examined' => $totalSantriExamined,
                    'total_santri_changed' => $totalSantriWithChanges,
                    'total_rows_changed' => $totalRowsWithChanges,
                    'start_level' => $startLevel,
                    'scope' => $scope
                ],
                'items' => $previewItems
            ]);
        }

        // =========================================================================
        // MODE 2: EKSEKUSI PENYIMPANAN MASSAL (COMMIT)
        // =========================================================================
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
