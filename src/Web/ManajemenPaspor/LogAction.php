<?php
declare(strict_types=1);

namespace App\Web\ManajemenPaspor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class LogAction
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        $isSuperAdmin = $role === 'super_admin';

        $db = $this->db;

        $where = "WHERE s.status_santri = 'Aktif'";
        $bindParams = [];

        if (!$isSuperAdmin && (!empty($myKepengurusan) || !empty($myPondok))) {
            $conditions = [];
            if (!empty($myKepengurusan)) {
                $conditions[] = "s.kepengurusan = :my_kepengurusan";
                $bindParams[':my_kepengurusan'] = $myKepengurusan;
            }
            if (!empty($myPondok)) {
                $conditions[] = "s.pondok = :my_pondok";
                $bindParams[':my_pondok'] = $myPondok;
            }
            if (!empty($conditions)) {
                $where .= " AND (" . implode(" OR ", $conditions) . ") ";
            }
        }

        $sql = "SELECT l.*, s.nama, p.no_paspor
                FROM log_paspor l
                JOIN master_santri s ON l.kds = s.kds
                JOIN mtb_paspor p ON l.paspor_id = p.id
                $where
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT 500";

        $rows = $db->createCommand($sql, $bindParams)->queryAll();

        return JsonResponse::create($rows);
    }
}
