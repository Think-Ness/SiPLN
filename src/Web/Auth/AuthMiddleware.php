<?php
declare(strict_types=1);

namespace App\Web\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use App\Shared\JsonResponse;

use Yiisoft\Db\Connection\ConnectionInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    private DataResponseFactoryInterface $responseFactory;
    private ConnectionInterface $db;

    public function __construct(DataResponseFactoryInterface $responseFactory, ConnectionInterface $db)
    {
        $this->responseFactory = $responseFactory;
        $this->db = $db;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Jika tidak ada data Session Login
        if (!isset($_SESSION['user_id'])) {
            
            // Jika yang diakses adalah Endpoint API, kembalikan JSON Error
            if (str_starts_with($request->getUri()->getPath(), '/api/')) {
                return JsonResponse::create([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                ], 401);
            }
            
            // Jika yang diakses adalah Halaman Web biasa, alihkan ke /login
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            return $this->responseFactory->createResponse('', 302)->withHeader('Location', $scriptName . '/login');
        }

        // Lanjutkan ke halaman/proses yang diminta jika tiket valid
        
        // ==========================================
        // DYNAMIC ACL (Sistem Hak Akses Cerdas)
        // ==========================================
        $permissions = $_SESSION['permissions'] ?? [];
        if (isset($_SESSION['user_id'])) {
            try {
                $user = $this->db->createCommand("SELECT permissions FROM users WHERE id = :id", [':id' => $_SESSION['user_id']])->queryOne();
                if ($user && !empty($user['permissions'])) {
                    $permissions = json_decode($user['permissions'], true) ?? [];
                    $_SESSION['permissions'] = $permissions; // Auto-refresh session
                }
            } catch (\Throwable $e) {
                // Abaikan error
            }
        }
        
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // 1. Cek Khusus Aksi Edit/Hapus Santri (POST/DELETE ke /api/santri/*)
        if (str_starts_with($path, '/api/santri/')) {
            if (in_array($method, ['POST', 'DELETE', 'PUT', 'PATCH'])) {
                if (($_SESSION['role'] ?? '') !== 'super_admin' && !in_array('menu_master_data_edit', $permissions)) {
                    return JsonResponse::create(['success' => false, 'message' => 'Otoritas Akses Tindakan Ini Ditolak!'], 403);
                }
            }
        }

        // 2. Peta Otoritas Menu Dinamis
        // Ambil semua rute dan permission dari database
        try {
            $menus = $this->db->createCommand("SELECT url, permission_key FROM master_menus WHERE is_active = 1")->queryAll();
            
            // Urutkan url paling panjang (spesifik) lebih dulu. Contoh: /job-desk/settings dicek sebelum /job-desk
            usort($menus, fn($a, $b) => strlen($b['url']) <=> strlen($a['url']));
            
            $requiredPerm = null;
            foreach ($menus as $m) {
                // Cek awalan path persis seperti url menu ATAU /api{url_menu}
                if (str_starts_with($path, rtrim($m['url'], '/')) || str_starts_with($path, '/api' . rtrim($m['url'], '/'))) {
                    $requiredPerm = $m['permission_key'];
                    break;
                }
            }

            if ($requiredPerm) {
                // Super Admin bebas akses, lainnya cek array permissions
                if (($_SESSION['role'] ?? '') !== 'super_admin' && !in_array($requiredPerm, $permissions)) {
                    if (str_starts_with($path, '/api/')) {
                        return JsonResponse::create(['success' => false, 'message' => 'Otoritas Akses Menu Ini Ditolak!'], 403);
                    }
                    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                    return $this->responseFactory->createResponse('', 302)->withHeader('Location', $scriptName === '' ? '/' : $scriptName);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan error koneksi DB saat middleware agar aplikasi tetap jalan
        }

        return $handler->handle($request);
    }
}
