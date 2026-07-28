<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class DownloadStatusAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        @session_start();
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        $santriStats = $db->createCommand("
            SELECT q.kode_santri, s.nama,
                   COUNT(*) as total_files,
                   SUM(CASE WHEN q.status = 'completed' THEN 1 ELSE 0 END) as completed_files,
                   SUM(CASE WHEN q.status = 'downloading' THEN 1 ELSE 0 END) as downloading_files,
                   SUM(CASE WHEN q.status = 'paused' THEN 1 ELSE 0 END) as paused_files,
                   SUM(CASE WHEN q.status = 'failed' THEN 1 ELSE 0 END) as failed_files,
                   SUM(CASE WHEN q.status = 'pending' THEN 1 ELSE 0 END) as pending_files
            FROM capel_download_queue q
            LEFT JOIN master_santri s ON q.kode_santri = s.kode
            WHERE q.instansi_id = :instId OR :instId = 0
            GROUP BY q.kode_santri, s.nama
        ", [':instId' => $instansiId])->queryAll();
        
        $result = [
            'total' => 0,
            'pending' => 0,
            'downloading' => 0,
            'completed' => 0,
            'failed' => 0,
            'paused' => 0
        ];
        $santriList = [];
        $currentDownloading = null;

        foreach ($santriStats as $row) {
            $result['total'] += (int)$row['total_files'];
            $result['pending'] += (int)$row['pending_files'];
            $result['downloading'] += (int)$row['downloading_files'];
            $result['completed'] += (int)$row['completed_files'];
            $result['failed'] += (int)$row['failed_files'];
            $result['paused'] += (int)$row['paused_files'];
            
            $status = 'Menunggu';
            if ((int)$row['downloading_files'] > 0) {
                $status = 'Mengunduh';
                $currentDownloading = $row['nama'] ?? $row['kode_santri'];
            } elseif ((int)$row['paused_files'] > 0 && (int)$row['pending_files'] == 0 && (int)$row['downloading_files'] == 0) {
                $status = 'Jeda';
            } elseif ((int)$row['completed_files'] + (int)$row['failed_files'] == (int)$row['total_files']) {
                if ((int)$row['failed_files'] == (int)$row['total_files']) {
                    $status = 'Dibatalkan';
                } elseif ((int)$row['failed_files'] > 0) {
                    $status = 'Selesai (Ada Gagal)';
                } else {
                    $status = 'Selesai';
                }
            }

            $santriList[] = [
                'kode_santri' => $row['kode_santri'],
                'nama' => $row['nama'] ?? $row['kode_santri'],
                'total' => (int)$row['total_files'],
                'completed' => (int)$row['completed_files'],
                'failed' => (int)$row['failed_files'],
                'status' => $status
            ];
        }
        
        $effectiveTotal = $result['total'] - $result['failed'];
        $percent = $effectiveTotal > 0 ? floor(($result['completed'] / $effectiveTotal) * 100) : ($result['total'] > 0 ? 100 : 0);
        
        return JsonResponse::create([
            'active' => $result['pending'] > 0 || $result['downloading'] > 0 || $result['paused'] > 0,
            'total' => $result['total'],
            'completed' => $result['completed'],
            'failed' => $result['failed'],
            'percent' => $percent,
            'current' => $currentDownloading,
            'santri_list' => $santriList,
            'paused_all' => ($result['pending'] === 0 && $result['downloading'] === 0 && $result['paused'] > 0)
        ]);
    }
}
