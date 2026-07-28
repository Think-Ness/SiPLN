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

        if (!empty($path) && is_dir($path)) {
            $realPath = realpath($path);
            if ($realPath !== false) {
                // Gunakan realpath untuk memastikan path valid dan tidak ada trailing slash yang mengacaukan quotes
                exec('explorer.exe "' . $realPath . '"');
                
                $response = new Response(200);
                $response = $response->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode(['success' => true]));
                return $response;
            }
        }

        $response = new Response(400);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode(['success' => false]));
        return $response;
    }
}
