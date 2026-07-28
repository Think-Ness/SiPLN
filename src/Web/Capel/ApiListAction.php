<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class ApiListAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        @session_start();
        $status = $request->getQueryParams()['status'] ?? 'Pending';
        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        $params = [':s' => $status];
        $whereInstansi = "";
        
        if (!$isSuperAdmin) {
            $whereInstansi = "AND d.instansi_id = :inst";
            $params[':inst'] = $instansiId;
        } elseif (!empty($request->getQueryParams()['instansi_id'])) {
            $whereInstansi = "AND d.instansi_id = :inst";
            $params[':inst'] = (int)$request->getQueryParams()['instansi_id'];
        }

        $query = $db->createCommand("
            SELECT d.*, s.status_santri as final_status_santri, i.pondok as pondok_instansi, i.nama_instansi 
            FROM mtb_capel_draft d 
            LEFT JOIN master_santri s ON d.kds_approved = s.kds 
            LEFT JOIN master_instansi i ON d.instansi_id = i.kode
            WHERE d.status_approval = :s $whereInstansi
            ORDER BY d.id DESC", $params
        )->queryAll();
        
        // Let's add duplicate warning if Pending
        if ($status === 'Pending') {
            foreach ($query as &$row) {
                $nama = $row['nama_lengkap'];
                // Check if name already exists in master_santri
                $dups = $db->createCommand("SELECT stambuk, nama, kelas, pondok FROM master_santri WHERE nama LIKE :n", [':n' => '%' . trim($nama) . '%'])->queryAll();
                $row['potential_duplicates'] = $dups;
            }
        }

        return JsonResponse::create($query);
    }
}
