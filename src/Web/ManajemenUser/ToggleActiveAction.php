<?php
declare(strict_types=1);

namespace App\Web\ManajemenUser;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

final class ToggleActiveAction
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
            return JsonResponse::create(['success' => false, 'message' => 'ID tidak valid.'], 400);
        }

        try {
            $user = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $id])->queryOne();
            if (!$user) {
                return JsonResponse::create(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
            }

            // Admin instansi hanya bisa toggle user instansinya
            if ($myRole === 'admin_instansi') {
                if ($user['instansi_id'] != $_SESSION['instansi_id']) {
                    return JsonResponse::create(['success' => false, 'message' => 'Anda tidak berhak mengubah akun ini.'], 403);
                }
            }

            // Jangan bisa nonaktifkan diri sendiri
            if ((int)$user['id'] === (int)($_SESSION['user_id'] ?? 0)) {
                return JsonResponse::create(['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'], 400);
            }

            $newStatus = (int)$user['is_active'] === 1 ? 0 : 1;
            $db->createCommand()->update('users', ['is_active' => $newStatus], ['id' => $id])->execute();

            $newUser = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $id])->queryOne();
            $action = $newStatus === 1 ? 'Mengaktifkan' : 'Menonaktifkan';
            AuditLogger::log($db, 'UPDATE', 'MANAJEMEN_PENGGUNA', $id, $user, $newUser, "$action staf: " . $user['username']);
            
            // FIREBASE DUAL-WRITE
            \App\Shared\FirebaseSync::syncUser((string)$id, $newUser, (string)($newUser['instansi_id'] ?? '0'));

            return JsonResponse::create([
                'success' => true,
                'is_active' => $newStatus,
                'message' => "Staf berhasil " . ($newStatus === 1 ? 'diaktifkan' : 'dinonaktifkan') . "."
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}
