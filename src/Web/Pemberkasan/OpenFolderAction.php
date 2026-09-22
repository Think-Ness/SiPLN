<?php

declare(strict_types=1);

namespace App\Web\Pemberkasan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use HttpSoft\Message\Response;

final class OpenFolderAction
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $path = $queryParams['path'] ?? '';

        if (!empty($path)) {
            $normPath = str_replace('/', DIRECTORY_SEPARATOR, trim($path));
            if (is_dir($normPath)) {
                $realPath = realpath($normPath) ?: $normPath;
                $winPath = str_replace('/', '\\', $realPath);
                
                if (PHP_OS_FAMILY === 'Windows') {
                    // Jalankan explorer secara non-blocking
                    @pclose(@popen("start explorer.exe " . escapeshellarg($winPath), "r"));
                    @exec('powershell.exe -NoProfile -Command "Start-Process explorer.exe -ArgumentList ' . escapeshellarg($winPath) . '"');
                } else {
                    @exec('xdg-open ' . escapeshellarg($realPath) . ' > /dev/null 2>&1 &');
                }
                
                $response = new Response(200);
                $response = $response->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode(['success' => true, 'path' => $winPath]));
                return $response;
            }
        }

        $response = new Response(400);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Folder tidak ditemukan di server: ' . $path]));
        return $response;
    }
}
