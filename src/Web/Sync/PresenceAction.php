<?php
declare(strict_types=1);

namespace App\Web\Sync;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\JsonResponse;
use App\Shared\FirebaseSync;

/**
 * Endpoint untuk menerima ping keaktifan user (heartbeat)
 * POST /api/firebase/presence
 */
final class PresenceAction
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        file_put_contents(__DIR__ . '/presence.log', date('Y-m-d H:i:s') . " - Presence Ping Received\n", FILE_APPEND);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? null;
        $role = $_SESSION['role'] ?? null;
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $namaLengkap = $_SESSION['nama_lengkap'] ?? null;

        if (!$userId || !$instansiId || $instansiId === '0') {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Not logged in or super admin'
            ]);
        }

        // Push ke Firebase
        FirebaseSync::updatePresence(
            (string)$instansiId, 
            (string)$userId, 
            (string)$namaLengkap, 
            (string)$role
        );

        return JsonResponse::create([
            'success' => true
        ]);
    }
}
