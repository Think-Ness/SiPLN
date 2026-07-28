<?php
declare(strict_types=1);

namespace App\Web\AutoRekap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class ApiAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        $whereExt = "";
        $params = [];
        if ($role !== 'super_admin' && (!empty($myKepengurusan) || !empty($myPondok))) {
            $whereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok)";
            $params[':my_kepengurusan'] = $myKepengurusan;
            $params[':my_pondok'] = $myPondok;
        }

        // Fetch list of Kepengurusan for dropdown
        $kepList = $db->createCommand("SELECT DISTINCT s.kepengurusan FROM master_santri s WHERE s.aktif=1 AND s.kepengurusan != '' $whereExt ORDER BY s.kepengurusan", $params)->queryColumn();

        $whereClause = "WHERE s.aktif=1 $whereExt";

        $sqlItas = "
            SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara as daerah, s.kepengurusan, i.exp_itas
            FROM master_santri s
            LEFT JOIN (SELECT kds, no_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
            $whereClause AND i.exp_itas <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
            ORDER BY i.exp_itas ASC
        ";
        $dataItas = $db->createCommand($sqlItas, $params)->queryAll();

        $sqlPaspor = "
            SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara as daerah, s.kepengurusan, p.exp_paspor
            FROM master_santri s
            JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            $whereClause AND p.exp_paspor <= DATE_ADD(CURDATE(), INTERVAL 18 MONTH)
            ORDER BY p.exp_paspor ASC
        ";
        $dataPaspor = $db->createCommand($sqlPaspor, $params)->queryAll();

        return JsonResponse::create([
            'success' => true,
            'data' => [
                'itas' => $dataItas,
                'paspor' => $dataPaspor,
                'kepList' => $kepList,
            ]
        ]);
    }
}

