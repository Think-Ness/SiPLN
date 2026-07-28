<?php
declare(strict_types=1);

namespace App\Web\AuditLog;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? '';
        
        // Tandai semua log (yang berkaitan dengan user/instansi ini atau super admin) sebagai terbaca
        if ($role === 'super_admin') {
            $db->createCommand("UPDATE audit_logs SET is_read = 1 WHERE is_read = 0")->execute();
            // Super admin melihat semua log
            $logs = $db->createCommand("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 500")->queryAll();
        } else {
            // Admin instansi hanya melihat log dari instansinya sendiri
            $db->createCommand("UPDATE audit_logs SET is_read = 1 WHERE instansi_id = :instansi_id AND is_read = 0", [':instansi_id' => $instansiId])->execute();
            $logs = $db->createCommand("SELECT * FROM audit_logs WHERE instansi_id = :instansi_id ORDER BY created_at DESC LIMIT 500", [':instansi_id' => $instansiId])->queryAll();
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'logs' => $logs
        ]);
    }
}
