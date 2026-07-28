<?php
declare(strict_types=1);

namespace App\Web\Santri;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class ViewBerkasAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');
        $filename = urldecode($currentRoute->getArgument('filename'));

        $santri = $db->createCommand("SELECT nama, kode FROM master_santri WHERE kds = :k", [':k' => $kds])->queryOne();
        if (!$santri) {
            $r = new Response(404);
            $r->getBody()->write("Santri not found.");
            return $r;
        }

        $namaCleanFolder = preg_replace('/[^A-Za-z0-9]/', '_', $santri['nama']);
        $santriFolder = rtrim($namaCleanFolder, '_') . '_' . $santri['kode'];
        
        $targetFolder = null;
        
        // Try session instansi first
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        $instansis = $db->createCommand("SELECT path_folder FROM master_instansi WHERE path_folder IS NOT NULL AND path_folder != ''")->queryAll();
        $pathsToCheck = [];
        if ($instansiId) {
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :id", [':id' => $instansiId])->queryOne();
            if ($instansi && !empty($instansi['path_folder'])) {
                $pathsToCheck[] = rtrim($instansi['path_folder'], '\\/');
            }
        }
        // Add all other instansis
        foreach ($instansis as $inst) {
            $p = rtrim($inst['path_folder'], '\\/');
            if (!in_array($p, $pathsToCheck)) {
                $pathsToCheck[] = $p;
            }
        }
        // Add default capel
        $pathsToCheck[] = 'capel';

        foreach ($pathsToCheck as $p) {
            $kategoriFolder = $p . '/berkas/' . $santriFolder;
            $isAbsolute = str_starts_with(str_replace('\\', '/', $kategoriFolder), 'D:/') || str_starts_with(str_replace('\\', '/', $kategoriFolder), 'C:/') || str_starts_with($kategoriFolder, '/');
            if ($isAbsolute) {
                $testPath = rtrim($kategoriFolder, '\\/') . '/';
            } else {
                $testPath = dirname(__DIR__, 4) . '/public/uploads/' . $kategoriFolder . '/';
            }
            if (is_dir($testPath)) {
                $targetFolder = $testPath;
                break;
            }
        }
        
        if (!$targetFolder) {
            $r = new Response(404);
            $r->getBody()->write("Folder not found.");
            return $r;
        }

        // Prevent directory traversal
        $filename = basename($filename);
        $path = $targetFolder . $filename;

        if (!file_exists($path) || !is_file($path)) {
            $r = new Response(404);
            $r->getBody()->write("Document not found.");
            return $r;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream'
        };

        $response = new Response(200);
        $response = $response->withHeader('Content-Type', $mime)
                             ->withHeader('Content-Disposition', 'inline; filename="' . basename($path) . '"');
                             
        $stream = fopen($path, 'rb');
        if ($stream !== false) {
            $response = $response->withBody(new \HttpSoft\Message\Stream($stream));
        } else {
            $response->getBody()->write(file_get_contents($path));
        }

        return $response;
    }
}
