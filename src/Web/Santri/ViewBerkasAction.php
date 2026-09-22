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
    use BerkasTrait;

    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');
        $filename = urldecode($currentRoute->getArgument('filename'));

        $santri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :k", [':k' => $kds])->queryOne();
        if (!$santri) {
            $r = new Response(404);
            $r->getBody()->write("Santri not found.");
            return $r;
        }

        $targetFolder = $this->getTargetFolder($db, $santri, false);
        
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
