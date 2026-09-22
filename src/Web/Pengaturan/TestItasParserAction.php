<?php
declare(strict_types=1);

namespace App\Web\Pengaturan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\ItasParserEngine;

final class TestItasParserAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $files = $request->getUploadedFiles();
        if (!isset($files['test_pdf'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada file PDF yang diunggah untuk pengujian.'], 400);
        }

        $file = $files['test_pdf'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengunggah file untuk pengujian.'], 400);
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid('itas_test_') . '.pdf';
        try {
            $file->moveTo($tempPath);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file sementara: ' . $e->getMessage()], 500);
        }

        // Check if custom config provided in request body for live testing unsaved settings
        $body = $request->getParsedBody();
        $customConfig = null;
        if (!empty($body['config'])) {
            $customConfig = is_string($body['config']) ? json_decode($body['config'], true) : $body['config'];
        }

        try {
            $result = ItasParserEngine::parsePdf($tempPath, $db, $customConfig);
            @unlink($tempPath);

            return JsonResponse::create([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Throwable $e) {
            @unlink($tempPath);
            return JsonResponse::create(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
