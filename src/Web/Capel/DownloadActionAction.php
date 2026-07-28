<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class DownloadActionAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        
        $action = $body['action'] ?? '';
        $kodeSantri = $body['kode_santri'] ?? 'ALL';
        
        @session_start();
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        if (!in_array($action, ['pause', 'resume', 'cancel'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Aksi tidak valid.']);
        }
        
        // instansiId = 0 is for global admin
        $whereSql = "(instansi_id = :instId OR :instId = 0)";
        $params = [':instId' => $instansiId];
        
        if ($kodeSantri !== 'ALL') {
            $whereSql .= " AND kode_santri = :kode";
            $params[':kode'] = $kodeSantri;
        }
        
        $hasDownloads = false;

        if ($action === 'pause') {
            $db->createCommand("UPDATE capel_download_queue SET status = 'paused' WHERE status IN ('pending') AND $whereSql", $params)->execute();
        } elseif ($action === 'resume') {
            $db->createCommand("UPDATE capel_download_queue SET status = 'pending' WHERE status IN ('paused') AND $whereSql", $params)->execute();
            $hasDownloads = true;
        } elseif ($action === 'cancel') {
            $db->createCommand("UPDATE capel_download_queue SET status = 'failed', error_msg = 'Dibatalkan oleh user' WHERE status IN ('pending', 'downloading', 'paused') AND $whereSql", $params)->execute();
        }

        if ($hasDownloads) {
            $workerPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'download_worker.php';
            $phpBin = PHP_BINARY;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $phpExe = dirname(ini_get('extension_dir')) . DIRECTORY_SEPARATOR . 'php.exe';
                $phpBin = file_exists($phpExe) ? $phpExe : 'php';
                try {
                    $wsh = new \COM("WScript.Shell");
                    $wsh->Run("\"$phpBin\" \"$workerPath\"", 0, false);
                } catch (\Throwable $e) {
                    pclose(popen("start /B \"\" \"$phpBin\" \"$workerPath\" > NUL", "r"));
                }
            } else {
                exec("\"$phpBin\" \"$workerPath\" > /dev/null 2>&1 &");
            }
        }

        return JsonResponse::create(['success' => true, 'message' => 'Aksi berhasil dilakukan.']);
    }
}
