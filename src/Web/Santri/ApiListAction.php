<?php
declare(strict_types=1);

namespace App\Web\Santri;

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
        $status = $request->getQueryParams()['status'] ?? '1';
        
        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        $whereExt = "";
        $params = [':status' => $status];
        
        if ($role !== 'super_admin' && (!empty($myKepengurusan) || !empty($myPondok))) {
            $conditions = [];
            if (!empty($myKepengurusan)) {
                $conditions[] = "s.kepengurusan = :my_kepengurusan";
                $params[':my_kepengurusan'] = $myKepengurusan;
            }
            if (!empty($myPondok)) {
                $conditions[] = "s.pondok = :my_pondok";
                $params[':my_pondok'] = $myPondok;
            }
            if (!empty($conditions)) {
                $whereExt = " AND (" . implode(" OR ", $conditions) . ") ";
            }
        }
        
        $sql = "
            SELECT s.kds, s.stambuk, s.nama, s.kelas, s.rayon, s.negara, s.kewarganegaraan, s.pondok, s.kepengurusan, s.daerah, s.aktif, s.status_santri,
                   p.no_paspor, p.exp_paspor, i.no_itas, i.exp_itas, i.level_itas as lvl
            FROM master_santri s
            LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            LEFT JOIN (SELECT kds, no_itas, exp_itas, level_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
            WHERE s.aktif = :status $whereExt
            ORDER BY s.nama ASC
        ";
        $santris = $db->createCommand($sql, $params)->queryAll();

        $pondokList       = $db->createCommand("SELECT DISTINCT s.pondok FROM master_santri s WHERE s.aktif=:status AND s.pondok IS NOT NULL AND s.pondok != '' $whereExt ORDER BY s.pondok", $params)->queryColumn();
        $kelasList        = $db->createCommand("SELECT DISTINCT s.kelas FROM master_santri s WHERE s.aktif=:status AND s.kelas IS NOT NULL AND s.kelas != '' $whereExt ORDER BY s.kelas", $params)->queryColumn();
        $negaraList       = $db->createCommand("SELECT DISTINCT s.negara FROM master_santri s WHERE s.aktif=:status AND s.negara IS NOT NULL AND s.negara != '' $whereExt ORDER BY s.negara", $params)->queryColumn();
        $kewarganegaraanList = $db->createCommand("SELECT DISTINCT s.kewarganegaraan FROM master_santri s WHERE s.aktif=:status AND s.kewarganegaraan IS NOT NULL AND s.kewarganegaraan != '' $whereExt ORDER BY s.kewarganegaraan", $params)->queryColumn();
        $kepengurusanList = $db->createCommand("SELECT DISTINCT s.kepengurusan FROM master_santri s WHERE s.aktif=:status AND s.kepengurusan IS NOT NULL AND s.kepengurusan != '' $whereExt ORDER BY s.kepengurusan", $params)->queryColumn();
        $rayonList        = $db->createCommand("SELECT DISTINCT s.rayon FROM master_santri s WHERE s.aktif=:status AND s.rayon IS NOT NULL AND s.rayon != '' $whereExt ORDER BY s.rayon", $params)->queryColumn();

        $expItasList = [];
        $expPasporList = [];
        foreach ($santris as $s) {
            if (!empty($s['exp_itas'])) {
                $ym = substr((string)$s['exp_itas'], 0, 7); // YYYY-MM
                $expItasList[$ym] = $ym;
            }
            if (!empty($s['exp_paspor'])) {
                $y = substr((string)$s['exp_paspor'], 0, 4); // YYYY
                $expPasporList[$y] = $y;
            }
        }
        krsort($expItasList);
        krsort($expPasporList);

        // Ambil data kasus Job Desk aktif beserta nama proses dan tahap saat ini
        $activeJobDeskRows = $db->createCommand("
            SELECT 
                j.kds,
                p.nama_proses,
                (
                    SELECT jcs.step_nama 
                    FROM jobdesk_case_steps jcs 
                    WHERE jcs.case_id = j.id AND jcs.status != 'selesai'
                    ORDER BY jcs.step_urut ASC 
                    LIMIT 1
                ) AS tahap_saat_ini
            FROM jobdesk_cases j
            LEFT JOIN jobdesk_master_process p ON j.process_id = p.id
            WHERE j.status = 'aktif'
        ")->queryAll();

        $activeJobDeskMap = [];
        foreach ($activeJobDeskRows as $r) {
            $activeJobDeskMap[$r['kds']] = [
                'nama_proses'    => $r['nama_proses'] ?? 'Proses Tidak Diketahui',
                'tahap_saat_ini' => $r['tahap_saat_ini'] ?? null,
            ];
        }

        return JsonResponse::create([
            'success' => true,
            'data' => [
                'santris' => $santris,
                'activeJobDesk' => $activeJobDeskMap,
                'filters' => [
                    'pondok' => $pondokList,
                    'kelas' => $kelasList,
                    'negara' => $negaraList,
                    'kewarganegaraan' => $kewarganegaraanList,
                    'kepengurusan' => $kepengurusanList,
                    'rayon' => $rayonList,
                    'exp_itas' => array_values($expItasList),
                    'exp_paspor' => array_values($expPasporList),
                ]
            ]
        ]);
    }
}

