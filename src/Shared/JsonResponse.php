<?php
declare(strict_types=1);

namespace App\Shared;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    public static function create(array $data, int $status = 200): ResponseInterface
    {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = new Response($status);
        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
