<?php
declare(strict_types=1);

namespace App\Web\ManajemenUser;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

final class ListAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db,
        DataResponseFactoryInterface $responseFactory
    ): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        
        // Hanya Super Admin & Admin Instansi yang boleh akses
        if ($role !== 'super_admin' && $role !== 'admin_instansi') {
            return $responseFactory->createResponse('', 302)->withHeader('Location', '/');
        }

        $instansiId = $_SESSION['instansi_id'] ?? null;
        $params = [];
        $where = "";

        // Admin Instansi hanya melihat user instansinya dan dilarang melihat akun Super Admin
        if ($role === 'admin_instansi') {
            $where = "WHERE u.instansi_id = :id AND u.role != 'super_admin'";
            $params[':id'] = $instansiId;
        }

        $users = $db->createCommand("
            SELECT u.*, i.nama_instansi 
            FROM users u
            LEFT JOIN master_instansi i ON u.instansi_id = i.kode
            $where
            ORDER BY u.id DESC
        ", $params)->queryAll();

        // Pilihan daftar instansi (hanya jika Super Admin yang bisa mengedit)
        $instansis = [];
        if ($role === 'super_admin') {
            $instansis = $db->createCommand("SELECT kode, nama_instansi FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
        }

        // Fetch all active menus for permission checkboxes
        $menus = $db->createCommand("SELECT * FROM master_menus WHERE is_active = 1 ORDER BY urut")->queryAll();

        return $viewRenderer->render(__DIR__ . '/template', [
            'users' => $users,
            'instansis' => $instansis,
            'menus' => $menus,
            'myRole' => $role,
            'myInstansiId' => $instansiId
        ]);
    }
}
