<?php
declare(strict_types=1);

namespace App\Web\Sync;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\FirebaseSync;

/**
 * Endpoint untuk trigger full sync semua data ke Firebase
 * POST /api/firebase/sync-all
 */
final class SyncAllAction
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Prevent timeout when syncing thousands of records
        set_time_limit(0);

        // Hanya super_admin dan admin_instansi yang boleh full sync
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['super_admin', 'admin_instansi'])) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan full sync'
            ], 403);
        }

        try {
            $limitInstansiId = null;
            if ($role !== 'super_admin') {
                $limitInstansiId = (string)($_SESSION['instansi_id'] ?? '');
            }
            
            $result = FirebaseSync::syncAll($this->db, $limitInstansiId);
            return JsonResponse::create($result, $result['success'] ? 200 : 500);
        } catch (\Throwable $e) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Exception in syncAll: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }
}
