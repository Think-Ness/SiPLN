<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class DownloadHistoryAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        @session_start();
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        $files = $db->createCommand("
            SELECT q.kode_santri, s.nama, q.jenis_dokumen, q.status, q.error_msg
            FROM capel_download_queue q
            LEFT JOIN master_santri s ON q.kode_santri = s.kode
            WHERE q.instansi_id = :instId OR :instId = 0
            ORDER BY q.id ASC
        ", [':instId' => $instansiId])->queryAll();
        
        return JsonResponse::create(['files' => $files]);
    }
}
