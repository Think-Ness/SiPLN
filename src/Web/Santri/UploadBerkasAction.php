<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class UploadBerkasAction
{
    use BerkasTrait;

    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');

        $santri = $db->createCommand("SELECT nama, kode FROM master_santri WHERE kds = :k", [':k' => $kds])->queryOne();
        if (!$santri) {
            return JsonResponse::create(['success' => false, 'message' => 'Santri not found'], 404);
        }

        $targetFolder = $this->getTargetFolder($db, $santri, true);
        if (!$targetFolder) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menentukan folder penyimpanan'], 500);
        }

        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;

        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengunggah file'], 400);
        }
        
        $body = $request->getParsedBody();
        $customName = $body['custom_name'] ?? null;

        $filename = $customName ?: basename($file->getClientFilename());
        
        // Ensure extension is preserved if custom name doesn't have it
        $originalExt = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $newExt = pathinfo($filename, PATHINFO_EXTENSION);
        if (!$newExt && $originalExt) {
            $filename .= '.' . $originalExt;
        }
        
        $finalExt = pathinfo($filename, PATHINFO_EXTENSION);
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array(strtolower($finalExt), $allowedExts)) {
            return JsonResponse::create(['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya menerima: ' . implode(', ', $allowedExts)], 400);
        }

        // Clean up filename to avoid spaces and weird characters, but keep it readable
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
        
        // Prevent overwriting
        $path = $targetFolder . $filename;
        $counter = 1;
        $info = pathinfo($filename);
        $base = $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
        while (file_exists($path)) {
            $filename = $base . '_' . $counter . $ext;
            $path = $targetFolder . $filename;
            $counter++;
        }

        try {
            $file->moveTo($path);
            return JsonResponse::create(['success' => true, 'message' => 'Berkas berhasil diunggah']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file: ' . $e->getMessage()], 500);
        }
    }
}
