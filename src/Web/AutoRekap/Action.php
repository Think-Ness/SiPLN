<?php

declare(strict_types=1);

namespace App\Web\AutoRekap;

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
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        $q = $request->getQueryParams()['q'] ?? '';
        
        // Default ke Kepengurusan sendiri jika bukan super admin dan belum ada filter
        if (!isset($request->getQueryParams()['kep']) && $role !== 'super_admin' && !empty($myKepengurusan)) {
            $kep = $myKepengurusan;
        } else {
            $kep = $request->getQueryParams()['kep'] ?? '';
        }

        $whereClause = "WHERE s.aktif=1";
        $params = [];
        
        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        if ($role !== 'super_admin' && (!empty($myKepengurusan) || !empty($myPondok))) {
            $whereClause .= " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok)";
            $params[':my_kepengurusan'] = $myKepengurusan;
            $params[':my_pondok'] = $myPondok;
        }

        // Fetch list of Kepengurusan and Pondok for dropdowns (Filtered by isolation)
        $kepList    = $db->createCommand("SELECT DISTINCT kepengurusan FROM master_santri s $whereClause AND kepengurusan != '' ORDER BY kepengurusan", $params)->queryColumn();
        $pondokList = $db->createCommand("SELECT DISTINCT pondok FROM master_santri s $whereClause AND pondok IS NOT NULL AND pondok != '' ORDER BY pondok", $params)->queryColumn();

        if ($q !== '') {
            $whereClause .= " AND (s.nama LIKE :q OR p.no_paspor LIKE :q OR s.stambuk LIKE :q)";
            $params[':q'] = "%$q%";
        }
        if ($kep !== '') {
            $whereClause .= " AND s.kepengurusan = :kep";
            $params[':kep'] = $kep;
        }

        $sqlItas = "
            SELECT s.kds, p.no_paspor, s.nama, s.pondok, s.kelas, s.negara as daerah, s.kepengurusan, i.exp_itas
            FROM master_santri s
            LEFT JOIN (SELECT kds, no_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
            $whereClause AND i.exp_itas <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
            ORDER BY i.exp_itas ASC
        ";
        $dataItas = $db->createCommand($sqlItas, $params)->queryAll();

        // Ambil data kasus Job Desk aktif beserta nama proses dan tahap saat ini
        $activeJobDeskRows = $db->createCommand("
            SELECT 
                j.kds,
                j.created_at,
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
            ORDER BY j.created_at ASC
        ")->queryAll();

        $activeJobDeskMap = [];
        foreach ($activeJobDeskRows as $r) {
            if (!isset($activeJobDeskMap[$r['kds']])) {
                $activeJobDeskMap[$r['kds']] = [];
            }
            $activeJobDeskMap[$r['kds']][] = [
                'nama_proses'    => $r['nama_proses'] ?? 'Proses Tidak Diketahui',
                'tahap_saat_ini' => $r['tahap_saat_ini'] ?? null,
            ];
        }
        $activeJobDeskKdsSet = $activeJobDeskMap; // alias for backward compat


        $sqlPaspor = "
            SELECT s.kds, p.no_paspor, s.nama, s.pondok, s.kelas, s.negara as daerah, s.kepengurusan, p.exp_paspor
            FROM master_santri s
            JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            $whereClause AND p.exp_paspor <= DATE_ADD(CURDATE(), INTERVAL 18 MONTH)
            ORDER BY p.exp_paspor ASC
        ";
        $dataPaspor = $db->createCommand($sqlPaspor, $params)->queryAll();

        return $viewRenderer->render(__DIR__ . '/template', [
                'dataItas'   => $dataItas,
                'dataPaspor' => $dataPaspor,
                'kepList'    => $kepList,
                'pondokList' => $pondokList,
                'q'          => $q,
                'kep'        => $kep,
                'activeJobDeskKdsSet' => $activeJobDeskKdsSet,
            ]);
    }
}

