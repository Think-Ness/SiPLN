<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class DeleteBerkasAction
{
    use BerkasTrait;

    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');
        
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        $filename = $body['filename'] ?? '';
        if (!$filename) {
            return JsonResponse::create(['success' => false, 'message' => 'Filename required'], 400);
        }

        $santri = $db->createCommand("SELECT nama, kode FROM master_santri WHERE kds = :k", [':k' => $kds])->queryOne();
        if (!$santri) {
            return JsonResponse::create(['success' => false, 'message' => 'Santri not found'], 404);
        }

        $targetFolder = $this->getTargetFolder($db, $santri, false);
        if (!$targetFolder) {
            return JsonResponse::create(['success' => false, 'message' => 'Folder tidak ditemukan'], 404);
        }

        $filename = basename($filename);
        $path = $targetFolder . $filename;

        if (file_exists($path) && is_file($path)) {
            if (unlink($path)) {
                return JsonResponse::create(['success' => true, 'message' => 'Berkas berhasil dihapus']);
            } else {
                return JsonResponse::create(['success' => false, 'message' => 'Gagal menghapus file'], 500);
            }
        }

        return JsonResponse::create(['success' => false, 'message' => 'File tidak ditemukan'], 404);
    }
}
