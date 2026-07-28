<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class DownloadCancelAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $db->createCommand("
            UPDATE capel_download_queue 
            SET status = 'failed', error_msg = 'Dibatalkan oleh user' 
            WHERE status IN ('pending', 'downloading')
        ")->execute();
        
        return JsonResponse::create(['success' => true, 'message' => 'Sisa antrian berhasil dibatalkan.']);
    }
}
