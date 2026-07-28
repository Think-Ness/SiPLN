<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

class ReportAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $statusFilter = $queryParams['status'] ?? 'all';
        $processFilter = $queryParams['process_id'] ?? 'all';
        // Force landscape for this modern matrix report
        $orientation = 'landscape';

        // Get all active processes for filter dropdown
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $role = $_SESSION['role'] ?? '';
        $superAdminInstansiFilter = $queryParams['instansi_id'] ?? '';
        
        if ($role === 'super_admin') {
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

        // Get all master steps for these processes
        $masterStepsRaw = $db->createCommand("SELECT * FROM jobdesk_master_steps ORDER BY process_id, urut ASC")->queryAll();
        $masterSteps = [];
        foreach ($masterStepsRaw as $ms) {
            $masterSteps[$ms['process_id']][] = $ms;
        }

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

        $sql = "
            SELECT 
                j.*, 
                s.nama, s.pondok, s.kelas, s.negara as daerah, s.kds, 
                p.nama_proses
            FROM jobdesk_cases j
            JOIN master_santri s ON j.kds = s.kds
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

        $sql .= " ORDER BY j.process_id ASC, j.created_at DESC ";

        $cases = $db->createCommand($sql, $params)->queryAll();

        $overallStats = ['aktif' => 0, 'selesai' => 0, 'batal' => 0, 'blocked' => 0];
        $bottlenecks = [];

        // Fetch steps for each case
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
                $pid = $case['process_id'];
                $case['steps'] = $stepsByCase[$case['id']] ?? [];
                
                // Track overall stats
                if ($case['status'] === 'aktif') $overallStats['aktif']++;
                elseif ($case['status'] === 'selesai') $overallStats['selesai']++;
                elseif ($case['status'] === 'batal') $overallStats['batal']++;

                $isBlocked = false;
                $mSteps = $masterSteps[$pid] ?? [];
                $matrix = [];
                $completedSteps = 0;
                $totalSteps = count($mSteps);
                $currentStepUrut = 0;
                $foundActive = false;

                // Build Matrix and count completed
                foreach ($case['steps'] as $st) {
                    $u = $st['step_urut'];
                    if ($st['status'] === 'selesai') {
                        $matrix["P{$u}"] = 'done';
                        $completedSteps++;
                    } elseif ($st['status'] === 'blocked') {
                        $matrix["P{$u}"] = 'blocked';
                        $isBlocked = true;
                        if ($currentStepUrut === 0) $currentStepUrut = $u;
                        $foundActive = true;
                    } else {
                        // If it's the first incomplete step, it's the active process. Otherwise, pending.
                        if (!$foundActive) {
                            $matrix["P{$u}"] = 'process';
                            if ($currentStepUrut === 0) $currentStepUrut = $u;
                            $foundActive = true;
                        } else {
                            $matrix["P{$u}"] = 'pending';
                        }
                    }
                }
                
                if ($isBlocked) $overallStats['blocked']++;

                // Calculate Progress %
                $progressPct = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
                if ($case['status'] === 'selesai') $progressPct = 100;
                
                $case['matrix'] = $matrix;
                $case['progress_pct'] = $progressPct;
                $case['current_step_urut'] = $currentStepUrut > 0 ? $currentStepUrut : ($case['status'] === 'selesai' ? $totalSteps + 1 : 1);

                // Fetch Notes for blocked cases
                $sqlNotes = "
                    SELECT c.* 
                    FROM jobdesk_step_notes c
                    JOIN jobdesk_case_steps s ON c.step_id = s.id
                    WHERE s.case_id = :cid 
                    ORDER BY c.created_at DESC LIMIT 3
                ";
                $case['notes'] = $db->createCommand($sqlNotes, [':cid' => $case['id']])->queryAll();

                // Bottleneck Analysis (Cumulative / Funnel)
                // Number of people who have COMPLETED this step.
                if (!isset($bottlenecks[$pid])) {
                    $bottlenecks[$pid] = [];
                    foreach ($mSteps as $ms) {
                        $bottlenecks[$pid][$ms['urut']] = ['nama' => $ms['step_nama'], 'count' => 0];
                    }
                }
                
                $reached = $case['current_step_urut'];
                foreach ($mSteps as $ms) {
                    $u = $ms['urut'];
                    if ($u < $reached) {
                        $bottlenecks[$pid][$u]['count']++;
                    }
                }
            }
        }
        
        $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();

        return $viewRenderer->withLayout(null)->render(__DIR__ . '/print_report', [
                'cases' => $cases,
                'statusFilter' => $statusFilter,
                'processFilter' => $processFilter,
                'processes' => $processes,
                'orientation' => $orientation,
                'instansi' => $instansi,
                'masterSteps' => $masterSteps,
                'overallStats' => $overallStats,
                'bottlenecks' => $bottlenecks
            ]);
    }
}
