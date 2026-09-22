<?php

declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class ReorderItasAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');

        $body = json_decode((string)$request->getBody(), true) ?? [];
        $startLevel = max(1, (int)($body['start_level'] ?? 1));

        // Ambil semua riwayat ITAS santri, diurutkan secara kronologis (dari tanggal paling lampau ke terbaru)
        $itasList = $db->createCommand(
            "SELECT id, no_itas, level_itas, exp_itas, aktif FROM mtb_itas 
             WHERE kds = :kds 
             ORDER BY (exp_itas IS NULL OR exp_itas = '' OR exp_itas = '0000-00-00') ASC, exp_itas ASC, id ASC",
            [':kds' => $kds]
        )->queryAll();

        if (empty($itasList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data ITAS untuk santri ini.'], 404);
        }

        $currentLevel = $startLevel;
        $updatedCount = 0;

        foreach ($itasList as $row) {
            $db->createCommand()->update(
                'mtb_itas',
                ['level_itas' => (string)$currentLevel],
                ['id' => $row['id']]
            )->execute();
            $currentLevel++;
            $updatedCount++;
        }

        return JsonResponse::create([
            'success' => true,
            'message' => "Berhasil menyusun ulang {$updatedCount} riwayat ITAS berurutan mulai dari Tingkat {$startLevel}.",
            'total' => $updatedCount
        ]);
    }
}
