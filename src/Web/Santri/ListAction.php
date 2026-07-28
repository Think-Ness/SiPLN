<?php
declare(strict_types=1);

namespace App\Web\Santri;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class ListAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        try {
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
                SELECT s.kds, s.stambuk, s.nama, s.kelas, s.rayon, s.negara, s.kewarganegaraan, s.pondok, s.kepengurusan, s.status_santri,
                       p.no_paspor, p.exp_paspor, i.exp_itas, i.level_itas as lvl
                FROM master_santri s
                LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
                LEFT JOIN (SELECT kds, exp_itas, level_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
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
            krsort($expItasList); // Descending
            krsort($expPasporList);

            $role = $_SESSION['role'] ?? '';
            $instansiId = $_SESSION['instansi_id'] ?? null;
            
            $instansiList = [];
            if ($role === 'super_admin') {
                $processes = $db->createCommand("SELECT p.*, i.nama_instansi FROM jobdesk_master_process p LEFT JOIN master_instansi i ON p.instansi_id = i.kode WHERE p.aktif = 1 AND p.instansi_id IS NOT NULL ORDER BY i.nama_instansi, p.urut ASC")->queryAll();
                $instansiList = $db->createCommand("SELECT kode, nama_instansi FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
            } else {
                $processes = $db->createCommand("SELECT * FROM jobdesk_master_process WHERE aktif = 1 AND instansi_id = :instansi_id ORDER BY urut ASC")
                                ->bindValue(':instansi_id', $instansiId)
                                ->queryAll();
            }

            $totalCountWhere = str_replace("s.", "", $whereExt);
            $total = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE aktif=:status $totalCountWhere", $params)->queryScalar();

            if ($instansiId) {
                $instansi = $db->createCommand("SELECT def_kepengurusan, def_pondok, def_jenis_kelamin FROM master_instansi WHERE kode = :kode")
                    ->bindValue(':kode', $instansiId)
                    ->queryOne();
            } else {
                $instansi = $db->createCommand("SELECT def_kepengurusan, def_pondok, def_jenis_kelamin FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
            }

            $allowedFieldsJson = $db->createCommand("SELECT setting_value FROM app_settings WHERE setting_key = 'pindahan_allowed_fields'")->queryScalar();
            $allowedFields = $allowedFieldsJson ? json_decode($allowedFieldsJson, true) : ['kelas', 'rayon', 'pondok', 'kamar'];

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

            return $viewRenderer->render(__DIR__ . '/template', [
                'santris'          => $santris,
                'total'            => $total,
                'pondokList'       => $pondokList,
                'kelasList'        => $kelasList,
                'negaraList'       => $negaraList,
                'kewarganegaraanList' => $kewarganegaraanList,
                'kepengurusanList' => $kepengurusanList,
                'rayonList'        => $rayonList,
                'expItasList'      => array_values($expItasList),
                'expPasporList'    => array_values($expPasporList),
                'processes'        => $processes,
                'instansiDefaults' => $instansi ?: [],
                'allowedFields'    => $allowedFields,
                'activeJobDeskKdsSet' => $activeJobDeskMap,
                'instansiList'     => $instansiList
            ]);
        } catch (\Throwable $e) {
            die('Error occurred: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}

