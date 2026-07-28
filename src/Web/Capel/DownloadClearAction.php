<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class DownloadClearAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        @session_start();
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        $db->createCommand("DELETE FROM capel_download_queue WHERE instansi_id = :instId OR :instId = 0", [':instId' => $instansiId])->execute();
        
        return JsonResponse::create([
            'success' => true,
            'message' => 'Riwayat pengunduhan berhasil dibersihkan'
        ]);
    }
}
