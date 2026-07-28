<?php declare(strict_types=1);
namespace App\Web\Santri;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class ViewPhotoAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');
        $santri = $db->createCommand("SELECT path_foto, nama FROM master_santri WHERE kds=:kds", [':kds' => $kds])->queryOne();
        
        if (!$santri || empty($santri['path_foto'])) {
            $r = new Response(404);
            $r->getBody()->write("Photo not found.");
            return $r;
        }

        $path = $santri['path_foto'];
        if (str_starts_with($path, '/serve.php?path=')) {
            $path = urldecode(substr($path, 16));
        } else if (str_starts_with($path, '/') && !file_exists($path)) {
            $path = dirname(__DIR__, 4) . '/public' . $path;
        }

        if (!file_exists($path) || !is_file($path)) {
            $r = new Response(404);
            $r->getBody()->write("File not found on disk.");
            return $r;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };

        $response = new Response(200);
        $response = $response->withHeader('Content-Type', $mime)
                             ->withHeader('Content-Disposition', 'inline')
                             ->withHeader('Cache-Control', 'public, max-age=86400');
                             
        $response->getBody()->write(file_get_contents($path));
        return $response;
    }
}
