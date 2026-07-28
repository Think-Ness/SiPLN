<?php
declare(strict_types=1);

namespace App\Web\AuditLog;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class UnreadCountAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? '';
        
        if ($role === 'super_admin') {
            $count = $db->createCommand("SELECT COUNT(*) FROM audit_logs WHERE is_read = 0")->queryScalar();
        } else {
            $count = $db->createCommand("SELECT COUNT(*) FROM audit_logs WHERE instansi_id = :instansi_id AND is_read = 0", [':instansi_id' => $instansiId])->queryScalar();
        }

        return JsonResponse::create([
            'count' => (int)$count
        ]);
    }
}
