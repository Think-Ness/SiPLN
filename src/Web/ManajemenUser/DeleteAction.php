<?php
declare(strict_types=1);

namespace App\Web\ManajemenUser;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

final class DeleteAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $myRole = $_SESSION['role'] ?? '';
        if ($myRole !== 'super_admin' && $myRole !== 'admin_instansi') {
            return JsonResponse::create(['success' => false, 'message' => 'Akses Ditolak'], 403);
        }

        $id = (int) $currentRoute->getArgument('id', '0');
        
        if ($id <= 0) {
            return JsonResponse::create(['success' => false, 'message' => 'ID Pengguna tidak valid!'], 400);
        }

        if ($id === (int)$_SESSION['user_id']) {
            return JsonResponse::create(['success' => false, 'message' => 'Anda tidak bisa menghapus akun Anda sendiri!'], 400);
        }

        if ($myRole === 'admin_instansi') {
            $target = $db->createCommand("SELECT instansi_id FROM users WHERE id = :id", [':id' => $id])->queryScalar();
            if ($target != $_SESSION['instansi_id']) {
                return JsonResponse::create(['success' => false, 'message' => 'Anda hanya berhak menghapus akun dari instansi Anda.'], 403);
            }
        }

        try {
            $user = $db->createCommand("SELECT username, instansi_id FROM users WHERE id = :id", [':id' => $id])->queryOne();
            
            $db->createCommand()->delete('users', ['id' => $id])->execute();
            
            if ($user) {
                AuditLogger::log($db, 'DELETE', 'MANAJEMEN_PENGGUNA', $id, $user, "Menghapus pengguna: {$user['username']}");
                
                // FIREBASE DUAL-WRITE
                \App\Shared\FirebaseSync::deleteUser((string)$id, (string)($user['instansi_id'] ?? '0'));
            }
            
            return JsonResponse::create(['success' => true, 'message' => 'Akun Pengguna berhasil dihapus!']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menghapus pengguna: ' . $e->getMessage()], 500);
        }
    }
}
