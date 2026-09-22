<?php
declare(strict_types=1);

namespace App\Web\Pengaturan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;
use App\Shared\ItasParserEngine;

final class SaveItasParserAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'super_admin') {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Hanya Super Admin yang berhak mengubah konfigurasi parser ITAS.'
            ], 403);
        }

        $body = $request->getParsedBody();
        $config = $body['config'] ?? null;

        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        if (!is_array($config) || !isset($config['profiles'])) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Format konfigurasi tidak valid.'
            ], 400);
        }

        try {
            $oldConfig = ItasParserEngine::getConfig($db);
            ItasParserEngine::saveConfig($db, $config);

            AuditLogger::log(
                $db,
                'UPDATE',
                'PENGATURAN_ITAS_PARSER',
                null,
                ['config' => $oldConfig],
                ['config' => $config],
                'Memperbarui konfigurasi template & rule parser ITAS'
            );

            return JsonResponse::create([
                'success' => true,
                'message' => 'Konfigurasi parser ITAS berhasil disimpan.',
                'config' => $config
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
