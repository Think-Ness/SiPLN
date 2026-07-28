<?php
declare(strict_types=1);

namespace App\Web\Sync;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\JsonResponse;
use App\Shared\FirebaseSync;

/**
 * Endpoint untuk cek status koneksi Firebase
 * GET /api/firebase/status
 */
final class StatusAction
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $status = FirebaseSync::checkConnection();
        return JsonResponse::create($status, $status['connected'] ? 200 : 503);
    }
}
