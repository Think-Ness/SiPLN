<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class ViewDocumentAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $type = $currentRoute->getArgument('type'); // 'paspor' or 'itas'
        $id = (int) $currentRoute->getArgument('id');

        if (!in_array($type, ['paspor', 'itas'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Tipe dokumen tidak valid'], 400);
        }

        $table = $type === 'paspor' ? 'mtb_paspor' : 'mtb_itas';
        
        $doc = $db->createCommand(
            "SELECT path_file FROM {$table} WHERE id = :id",
            [':id' => $id]
        )->queryOne();

        $path = $doc['path_file'];
        if (str_starts_with($path, '/serve.php?path=')) {
            $path = urldecode(substr($path, 16));
        } else if (str_starts_with($path, '/') && !file_exists($path)) {
            $path = dirname(__DIR__, 4) . '/public' . $path;
        }

        if (!$path || !file_exists($path) || !is_file($path)) {
            $r = new Response(404);
            $r->getBody()->write("Document not found.");
            return $r;
        }

        $doc['path_file'] = $path;

        $ext = strtolower(pathinfo($doc['path_file'], PATHINFO_EXTENSION));
        $mime = match($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream'
        };

        $response = new Response(200);
        $response = $response->withHeader('Content-Type', $mime)
                             ->withHeader('Content-Disposition', 'inline; filename="' . basename($doc['path_file']) . '"');
                             
        $response->getBody()->write(file_get_contents($doc['path_file']));
        return $response;
    }
}
