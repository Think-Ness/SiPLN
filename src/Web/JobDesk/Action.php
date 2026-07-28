<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

class Action
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $statusFilter = $queryParams['status'] ?? 'aktif';
        $processFilter = $queryParams['process_id'] ?? 'all';

        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        $whereExt = "";
        $params = [];
        if ($role !== 'super_admin') {
            if (!empty($myKepengurusan) && !empty($myPondok)) {
                $whereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok) ";
                $params[':my_kepengurusan'] = $myKepengurusan;
                $params[':my_pondok'] = $myPondok;
            } else if (!empty($myKepengurusan)) {
                $whereExt = " AND s.kepengurusan = :my_kepengurusan ";
                $params[':my_kepengurusan'] = $myKepengurusan;
            } else if (!empty($myPondok)) {
                $whereExt = " AND s.pondok = :my_pondok ";
                $params[':my_pondok'] = $myPondok;
            }
        }

        // Get all active processes for filter dropdown and cards
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $role = $_SESSION['role'] ?? '';
        $superAdminInstansiFilter = $queryParams['instansi_id'] ?? '';
        $instansiList = [];

        if ($role === 'super_admin') {
            $instansiList = $db->createCommand("SELECT * FROM master_instansi ORDER BY nama_instansi")->queryAll();
            if (!empty($superAdminInstansiFilter)) {
                $processes = $db->createCommand("SELECT p.*, i.nama_instansi FROM jobdesk_master_process p LEFT JOIN master_instansi i ON p.instansi_id = i.kode WHERE p.aktif = 1 AND p.instansi_id = :instansi_id ORDER BY p.nama_proses")
                                ->bindValue(':instansi_id', $superAdminInstansiFilter)
                                ->queryAll();
            } else {
                $processes = $db->createCommand("SELECT p.*, i.nama_instansi FROM jobdesk_master_process p LEFT JOIN master_instansi i ON p.instansi_id = i.kode WHERE p.aktif = 1 AND p.instansi_id IS NOT NULL ORDER BY i.nama_instansi, p.nama_proses")->queryAll();
            }
        } else {
            $processes = $db->createCommand("SELECT * FROM jobdesk_master_process WHERE aktif = 1 AND instansi_id = :instansi_id ORDER BY nama_proses")
                            ->bindValue(':instansi_id', $instansiId)
                            ->queryAll();
        }

        // Calculate stats per process (status only)
        $stats = $db->createCommand("
            SELECT j.process_id, j.status, COUNT(*) as cnt 
            FROM jobdesk_cases j 
            JOIN master_santri s ON j.kds = s.kds 
            WHERE 1=1 $whereExt 
            GROUP BY j.process_id, j.status
        ", $params)->queryAll();
        
        // Calculate blocked cases per process
        $blockedStats = $db->createCommand("
            SELECT j.process_id, COUNT(DISTINCT j.id) as cnt
            FROM jobdesk_cases j
            JOIN jobdesk_case_steps st ON j.id = st.case_id
            JOIN master_santri s ON j.kds = s.kds
            WHERE st.status = 'blocked' AND j.status = 'aktif' $whereExt
            GROUP BY j.process_id
        ", $params)->queryAll();

        $processStats = [];
        foreach ($stats as $row) {
            $pid = $row['process_id'] ?? 0;
            $st = $row['status'];
            if (!isset($processStats[$pid])) {
                $processStats[$pid] = ['total' => 0, 'aktif' => 0, 'selesai' => 0, 'batal' => 0, 'kendala' => 0];
            }
            $processStats[$pid][$st] = (int)$row['cnt'];
            $processStats[$pid]['total'] += (int)$row['cnt'];
        }

        foreach ($blockedStats as $row) {
            $pid = $row['process_id'] ?? 0;
            if (!isset($processStats[$pid])) {
                $processStats[$pid] = ['total' => 0, 'aktif' => 0, 'selesai' => 0, 'batal' => 0, 'kendala' => 0];
            }
            $processStats[$pid]['kendala'] = (int)$row['cnt'];
        }

        foreach ($processes as &$p) {
            $p['stats'] = $processStats[$p['id']] ?? ['total' => 0, 'aktif' => 0, 'selesai' => 0, 'batal' => 0, 'kendala' => 0];
        }

        $globalTotalAktif = 0;
        $globalTotalSelesai = 0;
        $globalTotalBatal = 0;
        $globalTotalKendala = 0;

        foreach ($processStats as $pid => $st) {
            $globalTotalAktif += $st['aktif'] ?? 0;
            $globalTotalSelesai += $st['selesai'] ?? 0;
            $globalTotalBatal += $st['batal'] ?? 0;
            $globalTotalKendala += $st['kendala'] ?? 0;
        }

        $sql = "
            SELECT 
                j.*, 
                s.nama, s.pondok, s.kelas, s.negara as daerah, s.kepengurusan, s.kds, 
                pas.exp_paspor, pas.no_paspor,
                its.exp_itas,
                p.nama_proses
            FROM jobdesk_cases j
            JOIN master_santri s ON j.kds = s.kds
            LEFT JOIN (SELECT kds, exp_paspor, no_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) pas ON s.kds = pas.kds
            LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) its ON s.kds = its.kds
            LEFT JOIN jobdesk_master_process p ON j.process_id = p.id
            WHERE 1=1 $whereExt
        ";

        if ($statusFilter !== 'all') {
            $sql .= " AND j.status = :status ";
            $params[':status'] = $statusFilter;
        }

        if ($processFilter !== 'all') {
            $sql .= " AND j.process_id = :process_id ";
            $params[':process_id'] = $processFilter;
        }

        $sql .= " ORDER BY j.created_at DESC ";

        $cases = $db->createCommand($sql, $params)->queryAll();

        // Fetch steps for each case using named parameters
        if (!empty($cases)) {
            $caseIds = array_column($cases, 'id');
            $namedParams = [];
            $placeholderParts = [];
            foreach ($caseIds as $idx => $cid) {
                $key = ':cid' . $idx;
                $placeholderParts[] = $key;
                $namedParams[$key] = $cid;
            }
            $placeholders = implode(',', $placeholderParts);
            $sqlSteps = "SELECT * FROM jobdesk_case_steps WHERE case_id IN ($placeholders) ORDER BY case_id, step_urut ASC";
            $allSteps = $db->createCommand($sqlSteps, $namedParams)->queryAll();

            $stepsByCase = [];
            foreach ($allSteps as $step) {
                $stepsByCase[$step['case_id']][] = $step;
            }

            foreach ($cases as &$case) {
                $case['steps'] = $stepsByCase[$case['id']] ?? [];
            }
        }

        // Get dynamic template steps if process filter is active
        $templateSteps = [];
        if ($processFilter !== 'all') {
            $templateSteps = $db->createCommand("SELECT * FROM jobdesk_master_steps WHERE process_id = :pid ORDER BY urut ASC", [':pid' => $processFilter])->queryAll();
        }

        // ===== FINANCIAL SUMMARY =====
        $finSummary = ['total_santri' => 0, 'total_instansi' => 0, 'total_selisih' => 0, 'lunas_santri' => 0, 'lunas_instansi' => 0, 'total_cases' => 0];
        $finPerProcess = [];
        try {
            $finRows = $db->createCommand("
                SELECT 
                    j.process_id,
                    p.nama_proses,
                    COUNT(pay.id) as total_cases,
                    SUM(pay.nominal_santri) as total_santri,
                    SUM(pay.nominal_instansi) as total_instansi,
                    SUM(pay.selisih_operasional) as total_selisih,
                    SUM(CASE WHEN pay.status_bayar_santri = 'lunas' THEN 1 ELSE 0 END) as lunas_santri,
                    SUM(CASE WHEN pay.status_bayar_instansi = 'lunas' THEN 1 ELSE 0 END) as lunas_instansi
                FROM jobdesk_case_payment pay
                JOIN jobdesk_cases j ON pay.case_id = j.id
                JOIN master_santri s ON j.kds = s.kds
                LEFT JOIN jobdesk_master_process p ON j.process_id = p.id
                WHERE 1=1 $whereExt
                GROUP BY j.process_id, p.nama_proses
            ", $params)->queryAll();

            foreach ($finRows as $fr) {
                $finPerProcess[$fr['process_id']] = $fr;
                $finSummary['total_santri'] += (float)$fr['total_santri'];
                $finSummary['total_instansi'] += (float)$fr['total_instansi'];
                $finSummary['total_selisih'] += (float)$fr['total_selisih'];
                $finSummary['lunas_santri'] += (int)$fr['lunas_santri'];
                $finSummary['lunas_instansi'] += (int)$fr['lunas_instansi'];
                $finSummary['total_cases'] += (int)$fr['total_cases'];
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        // Fetch payment status per case for table display
        $casePayments = [];
        try {
            if (!empty($cases)) {
                $caseIds2 = array_column($cases, 'id');
                $namedParams2 = [];
                $placeholderParts2 = [];
                foreach ($caseIds2 as $idx => $cid) {
                    $key = ':cpid' . $idx;
                    $placeholderParts2[] = $key;
                    $namedParams2[$key] = $cid;
                }
                $placeholders2 = implode(',', $placeholderParts2);
                $payRows = $db->createCommand("SELECT case_id, status_bayar_santri, status_bayar_instansi, nominal_santri, nominal_instansi, selisih_operasional FROM jobdesk_case_payment WHERE case_id IN ($placeholders2)", $namedParams2)->queryAll();
                foreach ($payRows as $pr) {
                    $casePayments[$pr['case_id']] = $pr;
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'cases'              => $cases,
            'statusFilter'       => $statusFilter,
            'processFilter'      => $processFilter,
            'processes'          => $processes,
            'templateSteps'      => $templateSteps,
            'globalTotalAktif'   => $globalTotalAktif,
            'globalTotalSelesai' => $globalTotalSelesai,
            'globalTotalBatal'   => $globalTotalBatal,
            'globalTotalKendala' => $globalTotalKendala,
            'role'               => $role,
            'instansiList'       => $instansiList,
            'superAdminInstansiFilter' => $superAdminInstansiFilter,
            'finSummary'         => $finSummary,
            'finPerProcess'      => $finPerProcess,
            'casePayments'       => $casePayments,
        ]);
    }
}
