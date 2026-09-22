<?php
declare(strict_types=1);

namespace App\Web\Pengaturan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\JsonResponse;
use App\Shared\ItasParserEngine;

final class AnalyzeSampleItasAction
{
    public function __invoke(
        ServerRequestInterface $request
    ): ResponseInterface {
        $files = $request->getUploadedFiles();
        if (!isset($files['sample_pdf'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada file sampel PDF yang diunggah.'], 400);
        }

        $file = $files['sample_pdf'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengunggah file sampel.'], 400);
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid('itas_sample_') . '.pdf';
        try {
            $file->moveTo($tempPath);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file sementara: ' . $e->getMessage()], 500);
        }

        try {
            $analysis = ItasParserEngine::analyzeSamplePdf($tempPath);
            @unlink($tempPath);

            return JsonResponse::create($analysis);
        } catch (\Throwable $e) {
            @unlink($tempPath);
            return JsonResponse::create(['success' => false, 'message' => 'Error menganalisis sampel: ' . $e->getMessage()], 500);
        }
    }
}
