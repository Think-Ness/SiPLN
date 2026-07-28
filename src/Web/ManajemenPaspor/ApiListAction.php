<?php
declare(strict_types=1);

namespace App\Web\ManajemenPaspor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class ApiListAction
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

        $params = $request->getQueryParams();
        $filter = $params['filter'] ?? 'all'; // all, di_kantor, keluar, terlambat

        $db = $this->db;

        // Base query
        $where = "WHERE s.status_santri = 'Aktif' AND p.aktif = 1";
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

        if ($filter === 'di_kantor') {
            $where .= " AND (p.status_lokasi = 'di_kantor' OR p.status_lokasi IS NULL)";
        } elseif ($filter === 'keluar') {
            $where .= " AND p.status_lokasi = 'keluar'";
        } elseif ($filter === 'terlambat') {
            $where .= " AND p.status_lokasi = 'keluar'";
        }

        $sql = "SELECT s.kds, s.nama, s.kelas, s.rayon, s.negara, s.pondok,
                       p.id as paspor_id, p.no_paspor, p.exp_paspor, 
                       i.exp_itas,
                       COALESCE(p.status_lokasi, 'di_kantor') as status_lokasi,
                       l.alasan as last_alasan, l.tanggal_aksi as last_tanggal_keluar,
                       l.tanggal_rencana_kembali, l.dicatat_oleh as last_dicatat
                FROM master_santri s
                JOIN mtb_paspor p ON s.kds = p.kds AND p.aktif = 1
                LEFT JOIN mtb_itas i ON s.kds = i.kds AND i.aktif = 1
                LEFT JOIN (
                    SELECT lp.* FROM log_paspor lp
                    INNER JOIN (SELECT paspor_id, MAX(id) as max_id FROM log_paspor WHERE tipe='keluar' GROUP BY paspor_id) latest
                    ON lp.id = latest.max_id
                ) l ON p.id = l.paspor_id AND p.status_lokasi = 'keluar'
                $where
                ORDER BY s.nama ASC";

        $rows = $db->createCommand($sql, $bindParams)->queryAll();

        // Filter terlambat after query
        if ($filter === 'terlambat') {
            $today = date('Y-m-d');
            $rows = array_values(array_filter($rows, function($r) use ($today) {
                return $r['tanggal_rencana_kembali'] && $r['tanggal_rencana_kembali'] < $today;
            }));
        }

        // Summary counts
        $allRows = $db->createCommand("
            SELECT COALESCE(p.status_lokasi, 'di_kantor') as status_lokasi, 
                   l.tanggal_rencana_kembali
            FROM master_santri s
            JOIN mtb_paspor p ON s.kds = p.kds AND p.aktif = 1
            LEFT JOIN (
                SELECT lp.* FROM log_paspor lp
                INNER JOIN (SELECT paspor_id, MAX(id) as max_id FROM log_paspor WHERE tipe='keluar' GROUP BY paspor_id) latest
                ON lp.id = latest.max_id
            ) l ON p.id = l.paspor_id AND p.status_lokasi = 'keluar'
            " . str_replace('p.status_lokasi = \'keluar\'', '1=1', 
                str_replace("AND (p.status_lokasi = 'di_kantor' OR p.status_lokasi IS NULL)", '', $where)) . "
        ", $bindParams)->queryAll();

        $today = date('Y-m-d');
        $countDiKantor = 0;
        $countKeluar = 0;
        $countTerlambat = 0;
        foreach ($allRows as $r) {
            if ($r['status_lokasi'] === 'keluar') {
                $countKeluar++;
                if ($r['tanggal_rencana_kembali'] && $r['tanggal_rencana_kembali'] < $today) {
                    $countTerlambat++;
                }
            } else {
                $countDiKantor++;
            }
        }

        return JsonResponse::create([
            'data' => $rows,
            'summary' => [
                'di_kantor' => $countDiKantor,
                'keluar' => $countKeluar,
                'terlambat' => $countTerlambat,
                'total' => count($allRows),
            ]
        ]);
    }
}
