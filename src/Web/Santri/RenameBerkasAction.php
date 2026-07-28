<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class RenameBerkasAction
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
        $oldName = $body['old_name'] ?? '';
        $newName = $body['new_name'] ?? '';
        
        if (!$oldName || !$newName) {
            return JsonResponse::create(['success' => false, 'message' => 'Old and new filename required'], 400);
        }

        $santri = $db->createCommand("SELECT nama, kode FROM master_santri WHERE kds = :k", [':k' => $kds])->queryOne();
        if (!$santri) {
            return JsonResponse::create(['success' => false, 'message' => 'Santri not found'], 404);
        }

        $targetFolder = $this->getTargetFolder($db, $santri, false);
        if (!$targetFolder) {
            return JsonResponse::create(['success' => false, 'message' => 'Folder tidak ditemukan'], 404);
        }

        $oldName = basename($oldName);
        $newName = basename($newName);
        // keep extension if not provided in newName, or let user decide?
        // safer to retain the old extension
        $oldExt = pathinfo($oldName, PATHINFO_EXTENSION);
        $newExt = pathinfo($newName, PATHINFO_EXTENSION);
        if (!$newExt && $oldExt) {
            $newName .= '.' . $oldExt;
        }

        $newName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $newName);

        $oldPath = $targetFolder . $oldName;
        $newPath = $targetFolder . $newName;

        if (!file_exists($oldPath) || !is_file($oldPath)) {
            return JsonResponse::create(['success' => false, 'message' => 'File lama tidak ditemukan'], 404);
        }

        if (file_exists($newPath)) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama file baru sudah ada'], 400);
        }

        if (rename($oldPath, $newPath)) {
            return JsonResponse::create(['success' => true, 'message' => 'Berkas berhasil diubah namanya']);
        } else {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengubah nama file'], 500);
        }
    }
}
