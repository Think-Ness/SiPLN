<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;
use Yiisoft\Router\UrlGeneratorInterface;

if (!function_exists('calculateDaysInfo')) {
    function calculateDaysInfo($startDate, $endDate = null) {
        if (!$startDate) return null;
        $start = new DateTime($startDate);
        $end = $endDate ? new DateTime($endDate) : new DateTime();
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);
        if ($start > $end) return ['total' => 0, 'kerja' => 0, 'libur' => 0];
        $totalDays = $start->diff($end)->days;
        $kerja = 0; $libur = 0;
        $current = clone $start;
        for ($i = 0; $i < $totalDays; $i++) {
            $dayOfWeek = $current->format('N');
            if ($dayOfWeek >= 6) $libur++; else $kerja++;
            $current->modify('+1 day');
        }
        return ['total' => $totalDays, 'kerja' => $kerja, 'libur' => $libur];
    }
}

/**
 * @var array $cases
 * @var string $statusFilter
 * @var string $processFilter
 * @var array $processes
 * @var array $templateSteps
 */

/*
 * =========================================
 * DASHBOARD ANALITIK & STATISTIK OVERVIEW
 * =========================================
 */

// Gunakan statistik global dari Action.php
$totalAktif = $globalTotalAktif ?? 0;
$totalSelesai = $globalTotalSelesai ?? 0;
$totalBatal = $globalTotalBatal ?? 0;
$totalBlocked = $globalTotalKendala ?? 0;

$slaLimit = isset($_GET['sla']) ? (int)$_GET['sla'] : 3;

// Analytics Calculation Global
$totalFinishedCases = 0;
$sumFinishedDays = 0;
$stepDurations = []; 

foreach ($cases as $c) {
    $cStart = $c['tgl_mulai_proses'];
    $cEnd = null;
    $prevDate = $cStart;
    foreach (($c['steps'] ?? []) as $st) {
        $urut = $st['step_urut'];
        if (!isset($stepDurations[$urut])) {
            $stepDurations[$urut] = ['sum' => 0, 'count' => 0, 'nama' => $st['step_nama']];
        }
        if ($st['status'] === 'selesai' && $st['tgl_realisasi']) {
            $dur = calculateDaysInfo($prevDate, $st['tgl_realisasi']);
            if ($dur) {
                $stepDurations[$urut]['sum'] += $dur['kerja'];
                $stepDurations[$urut]['count']++;
            }
            $prevDate = $st['tgl_realisasi'];
            $cEnd = $st['tgl_realisasi'];
        }
    }
    if ($c['status'] === 'selesai' && $cEnd) {
        $totalFinishedCases++;
        $cDur = calculateDaysInfo($cStart, $cEnd);
        if ($cDur) {
            $sumFinishedDays += $cDur['kerja'];
        }
    }
}

$avgTotalDays = $totalFinishedCases > 0 ? round($sumFinishedDays / $totalFinishedCases, 1) : 0;
$bottleneckStep = null;
$maxAvg = 0;
foreach ($stepDurations as $urut => $data) {
    if ($data['count'] > 0) {
        $avg = $data['sum'] / $data['count'];
        if ($avg > $maxAvg) {
            $maxAvg = $avg;
            $bottleneckStep = $data['nama'];
        }
    }
}
?>
<style>
    .jd-card { transition: transform .2s, box-shadow .2s; border: 1px solid #e9ecef !important; }
    .jd-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.08) !important; }
    .step-dots { display: flex; gap: 4px; align-items: center; }
    .step-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid #dee2e6; background: #fff; }
    .step-dot.done { background: #198754; border-color: #198754; }
    .step-dot.proses { background: #0d6efd; border-color: #0d6efd; animation: pulse-dot 1.5s infinite; }
    .step-dot.blocked { background: #dc3545; border-color: #dc3545; }
    @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.4} }
    .stat-card { border-radius: 12px; padding: 16px 20px; transition: all .2s; }
    .stat-card:hover { box-shadow: 0 6px 15px rgba(0,0,0,.05) !important; transform: translateY(-2px); }
    .filter-pill { border-radius: 20px; padding: 6px 16px; font-size: .8rem; font-weight: 600; text-decoration: none; transition: all .2s; cursor: pointer; }
    .filter-pill:hover { opacity: .85; }
    .filter-pill.active-pill { color: #fff !important; }
    .step-label-mini { font-size: .65rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px; text-align: center; }
    
    /* Highlight for selected row */
    .table-hover tbody tr.selected-row { background-color: #f0f6ff !important; }
</style>

<div class="px-2 py-3">
    <?php if ($processFilter === 'all'): ?>
    <!-- ApexCharts CDN -->
    <script src="<?= ASSET_URL ?>/assets/offline/js/apexcharts.js"></script>

    <!-- Dashboard Cards Only -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3 page-header-responsive">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-grid-1x2-fill text-primary me-2"></i>Job Desk Dashboard</h4>
            <p class="text-muted small mb-0">Pusat kendali birokrasi dan progres dokumen santri.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <?php if (isset($role) && $role === 'super_admin'): ?>
                <form method="GET" class="d-flex mb-0" id="instansiFilterForm">
                    <select name="instansi_id" class="form-select shadow-sm rounded-pill px-4 fw-medium text-primary" style="background-color: #e0f2fe; border: 1px solid #bae6fd; min-width: 260px;" onchange="document.getElementById('instansiFilterForm').submit()">
                        <option value="">Semua Instansi (Tanpa Referensi)</option>
                        <?php foreach ($instansiList ?? [] as $ins): ?>
                            <option value="<?= $ins['kode'] ?>" <?= (isset($superAdminInstansiFilter) && $superAdminInstansiFilter == $ins['kode']) ? 'selected' : '' ?>><?= htmlspecialchars($ins['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
            <a href="<?= API_URL ?>/job-desk/report<?= !empty($superAdminInstansiFilter) ? '?instansi_id='.$superAdminInstansiFilter : '' ?>" target="_blank" class="btn btn-primary px-3 rounded-pill fw-medium shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> Cetak Laporan Progres
            </a>
        </div>
    </div>
    
    <!-- Overall Stats Overview -->
    <?php
    $totalAll = $totalAktif + $totalSelesai + $totalBlocked;
    $completionRate = $totalAll > 0 ? round(($totalSelesai / $totalAll) * 100) : 0;
    $activeRate = $totalAll > 0 ? round(($totalAktif / $totalAll) * 100) : 0;
    $blockedRate = $totalAll > 0 ? round(($totalBlocked / $totalAll) * 100) : 0;
    ?>
    <div class="d-flex align-items-center mb-4 mt-2">
        <h5 class="fw-bolder text-dark mb-0"><i class="bi bi-collection-play-fill text-primary me-2"></i>Daftar Proses Job Desk</h5>
        <div class="ms-auto"><span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-ui-checks-grid me-1"></i><?= count($processes) ?> Proses Ditemukan</span></div>
    </div>

        <?php 
        // Modern Premium Colors (Tailwind-inspired)
        $cardColors = [
            ['bg' => 'linear-gradient(135deg, #0ea5e9, #3b82f6)', 'text' => '#0ea5e9', 'light' => '#e0f2fe', 'shadow' => 'rgba(14, 165, 233, 0.25)'],
            ['bg' => 'linear-gradient(135deg, #8b5cf6, #6366f1)', 'text' => '#8b5cf6', 'light' => '#ede9fe', 'shadow' => 'rgba(139, 92, 246, 0.25)'],
            ['bg' => 'linear-gradient(135deg, #f59e0b, #d97706)', 'text' => '#f59e0b', 'light' => '#fef3c7', 'shadow' => 'rgba(245, 158, 11, 0.25)'],
            ['bg' => 'linear-gradient(135deg, #10b981, #059669)', 'text' => '#10b981', 'light' => '#d1fae5', 'shadow' => 'rgba(16, 185, 129, 0.25)'],
            ['bg' => 'linear-gradient(135deg, #ec4899, #e11d48)', 'text' => '#ec4899', 'light' => '#fce7f3', 'shadow' => 'rgba(236, 72, 153, 0.25)']
        ];

        // Grouping logic
        $groupedProcesses = [];
        if (isset($role) && $role === 'super_admin' && empty($superAdminInstansiFilter)) {
            foreach ($processes as $p) {
                $instName = empty($p['nama_instansi']) ? 'Global / Tanpa Referensi Instansi' : $p['nama_instansi'];
                $groupedProcesses[$instName][] = $p;
            }
        } else {
            $groupedProcesses[''] = $processes;
        }
        
        foreach ($groupedProcesses as $groupName => $groupProcs): 
            if ($groupName !== ''):
                $instansiHash = crc32($groupName);
                $instansiTheme = $cardColors[abs($instansiHash) % count($cardColors)];
        ?>
            <div class="d-flex align-items-center mt-5 mb-3 pb-2 border-bottom" style="border-color: <?= $instansiTheme['light'] ?> !important;">
                <span class="badge rounded-circle p-2 me-2" style="background: <?= $instansiTheme['light'] ?>; color: <?= $instansiTheme['text'] ?>;">
                    <i class="bi bi-building fs-5"></i>
                </span>
                <h5 class="fw-bolder mb-0" style="color: <?= $instansiTheme['text'] ?>;"><?= htmlspecialchars($groupName) ?></h5>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <?php foreach ($groupProcs as $index => $p): 
                $theme = $cardColors[$index % count($cardColors)];
                $total = $p['stats']['total'] ?: 1; // Prevent div by zero
                $progressPct = round(($p['stats']['selesai'] / $total) * 100);
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 jd-card position-relative overflow-hidden" 
                         style="cursor: pointer; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.04); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" 
                         onmouseover="this.style.boxShadow='0 20px 40px <?= $theme['shadow'] ?>'; this.style.transform='translateY(-6px)';"
                         onmouseout="this.style.boxShadow='0 10px 30px rgba(0,0,0,0.04)'; this.style.transform='translateY(0)';"
                         onclick="setProcessFilter('<?= $p['id'] ?>')">
                        
                        <!-- Gradient Top Banner -->
                        <div style="height: 60px; width: 100%; background: <?= $theme['bg'] ?>; position: absolute; top: 0; left: 0;"></div>
                        
                        <div class="card-body p-4 pt-4 d-flex flex-column position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <!-- Floating Icon -->
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow" 
                                     style="width: 56px; height: 56px; background: white; border: 3px solid <?= $theme['light'] ?>; margin-top: -15px; color: <?= $theme['text'] ?>; font-size: 1.5rem;">
                                    <i class="bi bi-folder-fill"></i>
                                </div>
                                <!-- Completion Badge -->
                                <div class="px-3 py-1 rounded-pill fw-bold shadow-sm" style="background: <?= $theme['light'] ?>; color: <?= $theme['text'] ?>; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.8);">
                                    <?= $progressPct ?>% Selesai
                                </div>
                            </div>
                            
                            <h5 class="fw-bolder text-dark mb-2" style="font-size: 1.25rem; letter-spacing: -0.5px;"><?= htmlspecialchars($p['nama_proses']) ?></h5>
                            <?php if (isset($role) && $role === 'super_admin' && empty($superAdminInstansiFilter) && !empty($p['nama_instansi'])): 
                                $instansiHash = crc32($p['nama_instansi']);
                                $instansiTheme = $cardColors[abs($instansiHash) % count($cardColors)];
                            ?>
                                <div class="mb-2">
                                    <span class="badge rounded-pill fw-bold" style="background: <?= $instansiTheme['light'] ?>; color: <?= $instansiTheme['text'] ?>; font-size: 0.75rem; border: 1px solid <?= $instansiTheme['text'] ?>;">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($p['nama_instansi']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <p class="text-muted small mb-4" style="line-height: 1.5;">Kelola dan pantau seluruh antrean dokumen santri untuk proses ini secara real-time.</p>
                            
                            <!-- Progress Bar -->
                            <div class="progress mb-4 rounded-pill shadow-sm" style="height: 8px; background-color: #f1f5f9;">
                                <div class="progress-bar rounded-pill" role="progressbar" style="width: <?= $progressPct ?>%; background: <?= $theme['bg'] ?>;" aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                            <!-- Metrics -->
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center text-center">
                                <div class="flex-fill">
                                    <div class="text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">TOTAL</div>
                                    <div class="fw-bolder text-dark fs-5"><?= $p['stats']['total'] ?></div>
                                </div>
                                <div style="width: 1px; height: 30px; background: #e2e8f0;"></div>
                                <div class="flex-fill">
                                    <div class="text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">AKTIF</div>
                                    <div class="fw-bolder text-primary fs-5"><?= $p['stats']['aktif'] ?></div>
                                </div>
                                <div style="width: 1px; height: 30px; background: #e2e8f0;"></div>
                                <div class="flex-fill">
                                    <div class="text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">KENDALA</div>
                                    <div class="fw-bolder text-danger fs-5"><?= $p['stats']['kendala'] + $p['stats']['batal'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

    <hr class="border-light opacity-50 my-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h5 class="fw-bolder text-dark mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Dashboard Analitik Kinerja</h5>
        <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill border shadow-sm">
            <label for="slaSettingGlobal" class="small fw-bold text-muted mb-0"><i class="bi bi-gear me-1"></i>Batas SLA (HK):</label>
            <input type="number" id="slaSettingGlobal" class="form-control form-control-sm text-center fw-bold text-danger border-danger-subtle rounded-3" style="width: 60px;" value="<?= $slaLimit ?>" min="1" max="30">
            <button class="btn btn-sm btn-danger rounded-circle shadow-sm" style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;" onclick="updateSlaGlobal()"><i class="bi bi-check-lg"></i></button>
        </div>
    </div>

    <div class="row mb-5 g-4">
        <div class="col-lg-8">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); color: white; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3) !important;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-white-50 fw-semibold tracking-wide" style="font-size: 0.8rem; letter-spacing: 1px;">PROSES AKTIF</div>
                                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="bi bi-activity fs-5 text-white"></i></div>
                            </div>
                            <h2 class="fw-bolder mb-0 mt-auto text-white" style="font-size: 2.8rem;"><?= $totalAktif ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3) !important;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-white-50 fw-semibold tracking-wide" style="font-size: 0.8rem; letter-spacing: 1px;">KASUS SELESAI</div>
                                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="bi bi-check-circle fs-5 text-white"></i></div>
                            </div>
                            <h2 class="fw-bolder mb-0 mt-auto text-white" style="font-size: 2.8rem;"><?= $totalSelesai ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #f43f5e, #e11d48); color: white; box-shadow: 0 10px 30px rgba(225, 29, 72, 0.3) !important;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-white-50 fw-semibold tracking-wide" style="font-size: 0.8rem; letter-spacing: 1px;">KENDALA / BLOCKED</div>
                                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="bi bi-exclamation-octagon fs-5 text-white"></i></div>
                            </div>
                            <h2 class="fw-bolder mb-0 mt-auto text-white" style="font-size: 2.8rem;"><?= $totalBlocked ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Analytics Row -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3) !important;">
                        <div class="card-body p-4">
                            <div class="text-white-50 fw-semibold tracking-wide mb-3" style="font-size: .8rem; letter-spacing: 1px;">RATA-RATA PENYELESAIAN</div>
                            <div class="d-flex align-items-end gap-2 mb-2">
                                <h1 class="fw-bolder text-white mb-0" style="font-size: 3rem; line-height: 1;"><?= $avgTotalDays ?></h1>
                                <span class="text-white-50 pb-1 fw-bold">Hari Kerja</span>
                            </div>
                            <div class="small text-white mt-3 bg-white bg-opacity-10 px-3 py-2 rounded-3" style="font-size: .75rem;"><i class="bi bi-info-circle me-1"></i>Dari total <?= $totalFinishedCases ?> kasus selesai</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3) !important;">
                        <div class="card-body p-4">
                            <div class="text-white-50 fw-semibold tracking-wide mb-3" style="font-size: .8rem; letter-spacing: 1px;">TITIK KEMACETAN (BOTTLENECK)</div>
                            <div class="d-flex flex-column gap-1 mb-2">
                                <h4 class="fw-bolder text-white mb-0 text-truncate" title="<?= htmlspecialchars((string)$bottleneckStep) ?>"><?= $bottleneckStep ? htmlspecialchars((string)$bottleneckStep) : 'Tidak ada' ?></h4>
                                <?php if ($bottleneckStep): ?>
                                <span class="text-white fw-bold bg-white bg-opacity-25 d-inline-block px-2 py-1 rounded-3 mt-1" style="width: fit-content; font-size: .85rem;"><i class="bi bi-stopwatch text-white me-1"></i>Rata-rata <?= round($maxAvg, 1) ?> HK</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-white mt-3 bg-white bg-opacity-10 px-3 py-2 rounded-3" style="font-size: .75rem;"><i class="bi bi-exclamation-triangle text-white me-1"></i>Tahapan paling lama selesai.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 h-100 overflow-hidden" style="background: white; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
                <div class="card-body p-4 d-flex flex-column position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bolder text-dark mb-0" style="letter-spacing: -0.3px;">Performa Eksekutif</h6>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div id="radialChart" style="margin-left: -20px;"></div>
                        <div class="ms-2">
                            <div class="text-muted fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">TOTAL KASUS</div>
                            <h2 class="fw-bolder text-dark mb-0" style="font-size: 2.2rem;"><?= $totalAll ?></h2>
                            <div class="badge bg-success-subtle text-success mt-2 border border-success-subtle px-2 py-1"><i class="bi bi-graph-up-arrow me-1"></i><?= $completionRate ?>% Selesai</div>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small fw-semibold">Proses Aktif</span>
                                <span class="text-dark small fw-bold"><?= $activeRate ?>%</span>
                            </div>
                            <div class="progress rounded-pill shadow-sm" style="height: 6px; background-color: #eff6ff;">
                                <div class="progress-bar rounded-pill" style="width: <?= $activeRate ?>%; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small fw-semibold">Kendala / Batal</span>
                                <span class="text-dark small fw-bold"><?= $blockedRate ?>%</span>
                            </div>
                            <div class="progress rounded-pill shadow-sm" style="height: 6px; background-color: #fef2f2;">
                                <div class="progress-bar rounded-pill" style="width: <?= $blockedRate ?>%; background: linear-gradient(90deg, #f43f5e, #fb7185);"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Bar Chart -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 rounded-4" style="background: white; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="bi bi-bar-chart-line-fill text-primary" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h6 class="fw-bolder text-dark mb-0" style="letter-spacing: -0.3px;">Distribusi Beban Kerja per Proses</h6>
                                <span class="text-muted small">Analisis status kasus berdasarkan masing-masing proses Job Desk secara keseluruhan</span>
                            </div>
                        </div>
                    </div>
                    <div id="analyticsBarChart" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var completionRate = <?= $completionRate ?>;
            var totalAll = <?= $totalAll ?>;
            
            // Radial Chart (Performa Eksekutif)
            if (totalAll > 0) {
                var optionsRadial = {
                    series: [completionRate],
                    chart: { type: 'radialBar', height: 200, sparkline: { enabled: true } },
                    plotOptions: {
                        radialBar: {
                            startAngle: -100,
                            endAngle: 100,
                            hollow: { size: '60%' },
                            track: { background: '#f1f5f9', strokeWidth: '100%', margin: 0 },
                            dataLabels: {
                                name: { show: false },
                                value: { offsetY: 8, fontSize: '26px', fontWeight: 800, color: '#1e293b', formatter: function (val) { return val + "%"; } }
                            }
                        }
                    },
                    fill: { type: 'gradient', gradient: { shade: 'dark', type: 'horizontal', gradientToColors: ['#34d399'], stops: [0, 100] } },
                    stroke: { lineCap: 'round' },
                    colors: ['#10b981']
                };
                new ApexCharts(document.querySelector("#radialChart"), optionsRadial).render();
            } else {
                document.querySelector("#radialChart").innerHTML = '<div class="text-muted small mt-4 text-center">Belum ada data</div>';
            }

            // Stacked Bar Chart (Distribusi Beban Kerja)
            var processNames = <?= json_encode(array_column($processes, 'nama_proses')) ?>;
            var processAktif = <?= json_encode(array_map(function($p) { return $p['stats']['aktif']; }, $processes)) ?>;
            var processSelesai = <?= json_encode(array_map(function($p) { return $p['stats']['selesai']; }, $processes)) ?>;
            var processBatal = <?= json_encode(array_map(function($p) { return $p['stats']['kendala'] + $p['stats']['batal']; }, $processes)) ?>;
            
            var hasData = processNames.length > 0;
            if (hasData) {
                var optionsBar = {
                    series: [{
                        name: 'Selesai',
                        data: processSelesai
                    }, {
                        name: 'Aktif',
                        data: processAktif
                    }, {
                        name: 'Kendala / Batal',
                        data: processBatal
                    }],
                    chart: {
                        type: 'bar',
                        height: 280,
                        stacked: true,
                        toolbar: { show: false },
                        fontFamily: 'inherit'
                    },
                    colors: ['#10b981', '#3b82f6', '#f43f5e'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            borderRadius: 4,
                            columnWidth: '35%'
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 0 },
                    xaxis: {
                        categories: processNames,
                        labels: {
                            style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 },
                            trim: true,
                            maxHeight: 60
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } }
                    },
                    grid: {
                        borderColor: '#f1f5f9',
                        strokeDashArray: 4,
                        yaxis: { lines: { show: true } },
                        padding: { top: 0, right: 0, bottom: 0, left: 10 }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        offsetY: -20,
                        markers: { radius: 12 },
                        itemMargin: { horizontal: 10 }
                    },
                    fill: { opacity: 1 }
                };
                new ApexCharts(document.querySelector("#analyticsBarChart"), optionsBar).render();
            } else {
                document.querySelector("#analyticsBarChart").innerHTML = '<div class="text-muted small text-center w-100 mt-5 pt-3">Belum ada proses yang terdaftar</div>';
            }
        });
    </script>
    
    <!-- ===== RINGKASAN KEUANGAN BIROKRASI ===== -->
    <?php if ($finSummary['total_cases'] > 0): ?>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h5 class="fw-bolder text-dark mb-0"><i class="bi bi-cash-stack text-success me-2"></i>Ringkasan Keuangan Birokrasi</h5>
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-receipt me-1"></i><?= $finSummary['total_cases'] ?> Transaksi</span>
    </div>
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 h-100 stat-card overflow-hidden position-relative" style="background: linear-gradient(135deg, #059669, #10b981); color: white; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3) !important;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-white-50 fw-semibold tracking-wide" style="font-size: 0.75rem; letter-spacing: 1px;">DITERIMA DARI SANTRI</div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="bi bi-wallet2 fs-5 text-white"></i></div>
                    </div>
                    <h3 class="fw-bolder mb-1 mt-auto text-white">Rp <?= number_format($finSummary['total_santri'], 0, ',', '.') ?></h3>
                    <div class="small text-white bg-white bg-opacity-10 px-3 py-1 rounded-3 mt-2 d-inline-block" style="font-size: .7rem; width: fit-content;">
                        <i class="bi bi-check-circle me-1"></i><?= $finSummary['lunas_santri'] ?> / <?= $finSummary['total_cases'] ?> Lunas
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 h-100 stat-card overflow-hidden position-relative" style="background: linear-gradient(135deg, #4f46e5, #6366f1); color: white; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3) !important;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-white-50 fw-semibold tracking-wide" style="font-size: 0.75rem; letter-spacing: 1px;">DIBAYAR KE INSTANSI</div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="bi bi-building fs-5 text-white"></i></div>
                    </div>
                    <h3 class="fw-bolder mb-1 mt-auto text-white">Rp <?= number_format($finSummary['total_instansi'], 0, ',', '.') ?></h3>
                    <div class="small text-white bg-white bg-opacity-10 px-3 py-1 rounded-3 mt-2 d-inline-block" style="font-size: .7rem; width: fit-content;">
                        <i class="bi bi-check-circle me-1"></i><?= $finSummary['lunas_instansi'] ?> / <?= $finSummary['total_cases'] ?> Sudah Bayar
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 h-100 stat-card overflow-hidden position-relative" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: white; box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3) !important;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-white-50 fw-semibold tracking-wide" style="font-size: 0.75rem; letter-spacing: 1px;">SELISIH OPERASIONAL</div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="bi bi-piggy-bank fs-5 text-white"></i></div>
                    </div>
                    <h3 class="fw-bolder mb-1 mt-auto text-white">Rp <?= number_format($finSummary['total_selisih'], 0, ',', '.') ?></h3>
                    <div class="small text-white bg-white bg-opacity-10 px-3 py-1 rounded-3 mt-2 d-inline-block" style="font-size: .7rem; width: fit-content;">
                        <i class="bi bi-info-circle me-1"></i>Masuk kas operasional
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Page Header (Process View) -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3 page-header-responsive">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;" onclick="setProcessFilter('all')" title="Kembali ke Dashboard">
                <i class="bi bi-arrow-left text-dark"></i>
            </button>
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <?php foreach($processes as $p) { if($p['id'] == $processFilter) { echo htmlspecialchars($p['nama_proses']); break; } } ?>
                </h4>
                <p class="text-muted small mb-0">Detail & manajemen tahapan proses</p>
            </div>
        </div>
        <div class="d-flex gap-3 align-items-center flex-wrap">
            <div class="d-flex gap-2 bg-light p-1 rounded-pill border">
                <a href="#" onclick="setFilterStatus('aktif')" class="filter-pill <?= $statusFilter === 'aktif' ? 'active-pill bg-primary shadow-sm' : 'text-secondary' ?>">Aktif</a>
                <a href="#" onclick="setFilterStatus('selesai')" class="filter-pill <?= $statusFilter === 'selesai' ? 'active-pill bg-success shadow-sm' : 'text-secondary' ?>">Selesai</a>
                <a href="#" onclick="setFilterStatus('batal')" class="filter-pill <?= $statusFilter === 'batal' ? 'active-pill bg-danger shadow-sm' : 'text-secondary' ?>">Batal</a>
                <a href="#" onclick="setFilterStatus('all')" class="filter-pill <?= $statusFilter === 'all' ? 'active-pill bg-dark shadow-sm' : 'text-secondary' ?>">Semua</a>
            </div>
            <!-- Dynamic Print Button for this specific process -->
            <a href="<?= API_URL ?>/job-desk/report?process_id=<?= $processFilter ?>&status=<?= $statusFilter ?>" target="_blank" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2" style="background: linear-gradient(45deg, #3b82f6, #0ea5e9); border: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="bi bi-printer"></i> Cetak Job Desk Ini
            </a>
        </div>
    </div>

    <!-- Flow Diagram (Only show if a specific process is selected) -->
    <?php if ($processFilter !== 'all' && !empty($templateSteps)): ?>
    <?php
    // Calculate current step for each case to populate the interactive cards
    $stepCounts = [];
    $stepCases = [];
    foreach ($cases as $c) {
        $currentStep = null;
        foreach (($c['steps'] ?? []) as $st) {
            if ($st['status'] === 'pending' || $st['status'] === 'blocked') {
                $currentStep = $st;
                break; // Found the active/waiting step
            }
        }
        
        // If all steps are finished, optionally put them at the final step
        if (!$currentStep && !empty($c['steps'])) {
            $currentStep = end($c['steps']);
        }
        
        if ($currentStep) {
            $urut = (int)$currentStep['step_urut'];
            if (!isset($stepCounts[$urut])) $stepCounts[$urut] = 0;
            if (!isset($stepCases[$urut])) $stepCases[$urut] = [];
            $stepCounts[$urut]++;
            $stepCases[$urut][] = [
                'id' => $c['id'],
                'kds' => $c['kds'] ?? '-',
                'nama' => $c['nama'] ?? '-',
                'kelas' => $c['kelas'] ?? '-',
                'pondok' => $c['pondok'] ?? '-',
                'daerah' => $c['daerah'] ?? '-',
                'no_paspor' => $c['no_paspor'] ?? '-',
                'exp_paspor' => $c['exp_paspor'] ?? '',
                'exp_itas' => $c['exp_itas'] ?? '',
                'kepengurusan' => $c['kepengurusan'] ?? '',
                'status' => $c['status'] ?? '-',
                'step_status' => $currentStep['status']
            ];
        }
    }
    $stepCasesJson = json_encode($stepCases);
    ?>
    <div class="card border-0 rounded-4 mb-4" style="background: white; box-shadow: 0 8px 30px rgba(0,0,0,0.03);">
        <div class="card-body py-3 px-4">
            <!-- Clickable Header for Collapse -->
            <div class="d-flex align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#collapseFlowMap" aria-expanded="false" aria-controls="collapseFlowMap" style="cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-signpost-split-fill text-primary" style="font-size: 1.1rem;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bolder text-dark mb-0" style="letter-spacing: -0.3px;">Peta Alur Proses</h6>
                        <span class="text-muted small" style="font-size: 0.75rem;">
                            <?php foreach($processes as $p) { if($p['id'] == $processFilter) echo htmlspecialchars($p['nama_proses']); } ?> &bull; <span class="badge bg-light text-dark border rounded-pill px-2 py-1"><?= count($templateSteps) ?> Tahapan</span>
                        </span>
                    </div>
                </div>
                <div class="text-muted bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                    <i class="bi bi-chevron-down toggle-icon" style="transition: transform 0.3s; font-size: 0.9rem;"></i>
                </div>
            </div>
            
            <!-- Collapsible Content -->
            <div class="collapse" id="collapseFlowMap">
                <div class="pt-3 border-top border-light mt-3">
                    <div class="d-flex align-items-stretch flex-wrap pb-3 justify-content-center justify-content-lg-start" style="gap: 1.5rem 0; padding-top: 10px;">
                        <?php 
                        foreach ($templateSteps as $fi => $fs): 
                            $cnt = $stepCounts[$fs['urut']] ?? 0;
                            $hasSantri = $cnt > 0;
                            
                            // Firm Cool Blue for Active, Elegant Slate for Inactive
                            if ($hasSantri) {
                                $baseColor = '#2b6ce6'; // Cool bold blue
                                $baseGradient = 'linear-gradient(135deg, #4285f4, #2b6ce6)';
                                $cardOpacity = '1';
                                $cardShadow = '0 12px 25px rgba(43, 108, 230, 0.25)';
                            } else {
                                $baseColor = '#94a3b8'; // Elegant Slate/Silver
                                $baseGradient = 'linear-gradient(135deg, #cbd5e1, #94a3b8)';
                                $cardOpacity = '0.85'; 
                                $cardShadow = '0 4px 10px rgba(0,0,0,0.04)';
                            }
                        ?>
                        <div class="d-flex align-items-start">
                            
                            <div class="step-item d-flex flex-column align-items-center position-relative" style="width: 160px; z-index: 2; opacity: <?= $cardOpacity ?>; transition: opacity 0.3s;">
                                
                                <!-- Step Node -->
                                <div class="step-node d-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm position-relative" 
                                     style="width: 48px; height: 48px; background: <?= $baseGradient ?>; color: white; border: 4px solid white; font-weight: 800; font-size: 1.1rem; z-index: 5; transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);">
                                    <?= $fs['urut'] ?>
                                </div>

                                <!-- Step Card Full Color -->
                                <div class="card border-0 rounded-4 step-card w-100" style="background: <?= $baseGradient ?>; box-shadow: <?= $cardShadow ?>; cursor: pointer; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);" onclick="openStepDetailModal('<?= $fs['urut'] ?>', '<?= htmlspecialchars(addslashes($fs['step_nama'])) ?>', '<?= $baseColor ?>')">
                                    <div class="card-body p-3 text-center d-flex flex-column justify-content-between" style="min-height: 125px;">
                                        <div class="text-white fw-bold mb-3 px-1" style="font-size: 0.72rem; line-height: 1.4; word-wrap: break-word; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                            <?= htmlspecialchars($fs['step_nama']) ?>
                                        </div>
                                        <div class="mt-auto w-100">
                                            <?php if ($hasSantri): ?>
                                                <div class="badge-santri py-2 px-2 rounded-pill fw-bolder w-100 d-flex align-items-center justify-content-center gap-1 shadow-sm" style="background: white; color: <?= $baseColor ?>; font-size: 0.7rem;">
                                                    <i class="bi bi-people-fill"></i> <?= $cnt ?> Santri
                                                </div>
                                            <?php else: ?>
                                                <div class="badge-santri py-2 px-2 rounded-pill fw-bolder w-100 d-flex align-items-center justify-content-center gap-1" style="background: rgba(255,255,255,0.25); color: white; font-size: 0.7rem;">
                                                    <?= $cnt ?> Santri
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inline Connector Line with Arrow -->
                            <?php if ($fi < count($templateSteps) - 1): ?>
                            <div class="step-connector d-flex align-items-center justify-content-center" style="width: 36px; height: 48px; opacity: <?= $cardOpacity ?>; transition: opacity 0.3s;">
                                <i class="bi bi-arrow-right" style="color: <?= $baseColor ?>; font-size: 1.5rem; opacity: 0.8;"></i>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Premium Hover Effects for Step Cards */
        .step-item:hover .step-node {
            transform: scale(1.15);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1) !important;
        }
        .step-card {
            border-left: 1px solid #f8fafc !important;
            border-right: 1px solid #f8fafc !important;
            border-bottom: 1px solid #f8fafc !important;
        }
        .step-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important;
            border-color: #f1f5f9 !important;
            z-index: 10;
        }
        .step-card .badge-santri {
            transition: all 0.2s ease;
        }
        .step-card:hover .badge-santri {
            transform: scale(1.04);
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const collapseElement = document.getElementById('collapseFlowMap');
            if (collapseElement) {
                collapseElement.addEventListener('show.bs.collapse', function () {
                    this.parentElement.querySelector('.toggle-icon').style.transform = 'rotate(180deg)';
                });
                collapseElement.addEventListener('hide.bs.collapse', function () {
                    this.parentElement.querySelector('.toggle-icon').style.transform = 'rotate(0deg)';
                });
            }
        });
    </script>
    <?php endif; ?>

    <?php if (empty($cases)): ?>
    <!-- Empty State -->
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
        <div class="text-muted mb-3">
            <i class="bi bi-inbox" style="font-size: 3.5rem; opacity: .4;"></i>
        </div>
        <h5 class="fw-bold text-dark">Belum Ada Data</h5>
        <p class="text-muted small mb-3" style="max-width: 400px; margin: 0 auto;">
            Belum ada proses yang terdaftar untuk filter ini. 
            Buka proses baru melalui halaman <strong>Data Santri</strong>.
        </p>
        <a href="<?= API_URL ?>/master-data" class="btn btn-sm btn-primary px-4 rounded-pill fw-medium">
            <i class="bi bi-arrow-right me-1"></i>Ke Data Santri
        </a>
    </div>
    <?php else: ?>



    <!-- Summary Stats Row (only when there's data) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card border-0 rounded-4 shadow-sm h-100 p-3" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 42px; height: 42px;">
                        <i class="bi bi-play-circle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-white-50 tracking-wide" style="font-size: .7rem; font-weight: 600; letter-spacing: 1px;">AKTIF</div>
                        <div class="fw-bolder text-white fs-4" style="line-height: 1.2; margin-top: 2px;"><?= $totalAktif ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card border-0 rounded-4 shadow-sm h-100 p-3" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 42px; height: 42px;">
                        <i class="bi bi-check-circle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-white-50 tracking-wide" style="font-size: .7rem; font-weight: 600; letter-spacing: 1px;">SELESAI</div>
                        <div class="fw-bolder text-white fs-4" style="line-height: 1.2; margin-top: 2px;"><?= $totalSelesai ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card border-0 rounded-4 shadow-sm h-100 p-3" style="background: linear-gradient(135deg, #f43f5e, #e11d48); color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 42px; height: 42px;">
                        <i class="bi bi-exclamation-triangle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-white-50 tracking-wide" style="font-size: .7rem; font-weight: 600; letter-spacing: 1px;">TERKENDALA</div>
                        <div class="fw-bolder text-white fs-4" style="line-height: 1.2; margin-top: 2px;"><?= $totalBlocked ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card border-0 rounded-4 shadow-sm h-100 p-3" style="background: linear-gradient(135deg, #64748b, #475569); color: white;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 42px; height: 42px;">
                        <i class="bi bi-collection-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-white-50 tracking-wide" style="font-size: .7rem; font-weight: 600; letter-spacing: 1px;">TOTAL KASUS</div>
                        <div class="fw-bolder text-white fs-4" style="line-height: 1.2; margin-top: 2px;"><?= count($cases) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    <div id="bulkActionBar" class="card border border-primary bg-primary-subtle shadow-sm rounded-4 mb-3" style="display: none;">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check2-square text-primary fs-5"></i>
                <span class="text-dark fw-bold small"><span id="bulkCount">0</span> Kasus Terpilih</span>
            </div>
            <?php if ($processFilter === 'all'): ?>
            <div class="small text-danger fw-bold bg-white px-3 py-1 rounded-pill shadow-sm"><i class="bi bi-exclamation-circle me-1"></i>Pilih 1 Jenis Proses di filter atas untuk Update Massal</div>
            <?php else: ?>
            <div class="d-flex gap-2">
                <button onclick="bulkDeleteCases()" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-medium shadow-sm">
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
                <button onclick="openBulkUpdateModal()" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium shadow-sm">
                    <i class="bi bi-pencil-square me-1"></i>Update Tahapan Massal
                </button>
                <button onclick="openBulkPaymentModal()" class="btn btn-sm btn-success rounded-pill px-4 fw-medium shadow-sm ms-2">
                    <i class="bi bi-cash-coin me-1"></i>Tandai Lunas Massal
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cases Table View -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: .82rem;">
                <thead>
                    <tr class="bg-light">
                        <th class="ps-3 border-0" style="width: 40px;">
                            <div class="form-check m-0">
                                <input class="form-check-input shadow-sm" type="checkbox" id="checkAllCases" onchange="toggleAllCases(this)">
                            </div>
                        </th>
                        <th class="border-0 text-muted fw-bold" style="font-size: .7rem; letter-spacing: .05em; cursor: pointer; min-width: 250px;" onclick="sortTable(1)">
                            <div class="d-flex justify-content-between align-items-center mb-2">SANTRI <i class="bi bi-arrow-down-up ms-1 opacity-50"></i></div>
                            <input type="text" class="form-control form-control-sm border shadow-sm col-search" data-col="1" placeholder="Cari santri..." style="font-size: .7rem; border-radius: 6px;" onkeyup="filterTable()" onclick="event.stopPropagation()">
                        </th>
                        <th class="border-0 text-muted fw-bold" style="font-size: .7rem; letter-spacing: .05em; cursor: pointer; min-width: 140px;" onclick="sortTable(2)">
                            <div class="d-flex justify-content-between align-items-center mb-2">EXP PASPOR <i class="bi bi-arrow-down-up ms-1 opacity-50"></i></div>
                            <input type="text" class="form-control form-control-sm border shadow-sm col-search" data-col="2" placeholder="Cari tgl..." style="font-size: .7rem; border-radius: 6px;" onkeyup="filterTable()" onclick="event.stopPropagation()">
                        </th>
                        <th class="border-0 text-muted fw-bold" style="font-size: .7rem; letter-spacing: .05em; cursor: pointer; min-width: 140px;" onclick="sortTable(3)">
                            <div class="d-flex justify-content-between align-items-center mb-2">EXP ITAS <i class="bi bi-arrow-down-up ms-1 opacity-50"></i></div>
                            <input type="text" class="form-control form-control-sm border shadow-sm col-search" data-col="3" placeholder="Cari tgl..." style="font-size: .7rem; border-radius: 6px;" onkeyup="filterTable()" onclick="event.stopPropagation()">
                        </th>
                        <th class="border-0 text-muted fw-bold" style="font-size: .7rem; letter-spacing: .05em; cursor: pointer; min-width: 200px;" onclick="sortTable(4)">
                            <div class="d-flex justify-content-between align-items-center mb-2">PROGRES TAHAPAN <i class="bi bi-arrow-down-up ms-1 opacity-50"></i></div>
                            <input type="text" class="form-control form-control-sm border shadow-sm col-search" data-col="4" placeholder="Cari progres..." style="font-size: .7rem; border-radius: 6px;" onkeyup="filterTable()" onclick="event.stopPropagation()">
                        </th>
                        <th class="border-0 text-muted fw-bold" style="font-size: .7rem; letter-spacing: .05em; cursor: pointer; min-width: 130px;" onclick="sortTable(5)">
                            <div class="d-flex justify-content-between align-items-center mb-2">STATUS <i class="bi bi-arrow-down-up ms-1 opacity-50"></i></div>
                            <input type="text" class="form-control form-control-sm border shadow-sm col-search" data-col="5" placeholder="Cari status..." style="font-size: .7rem; border-radius: 6px;" onkeyup="filterTable()" onclick="event.stopPropagation()">
                        </th>
                        <th class="border-0 text-muted fw-bold" style="font-size: .7rem; letter-spacing: .05em; cursor: pointer; min-width: 120px;" onclick="sortTable(6)">
                            <div class="d-flex justify-content-between align-items-center mb-2">PEMBAYARAN <i class="bi bi-arrow-down-up ms-1 opacity-50"></i></div>
                            <input type="text" class="form-control form-control-sm border shadow-sm col-search" data-col="6" placeholder="Cari byr..." style="font-size: .7rem; border-radius: 6px;" onkeyup="filterTable()" onclick="event.stopPropagation()">
                        </th>
                        <th class="pe-4 border-0 text-muted fw-bold text-center align-top pt-3" style="font-size: .7rem; letter-spacing: .05em;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $case): 
                        $myKep = $_SESSION['def_kepengurusan'] ?? '';
                        $sKep  = trim((string)($case['kepengurusan'] ?? ''));
                        $isPindahan = ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0);
                        
                        $totalSteps = count($case['steps']);
                        $completedSteps = 0;
                        $currentStepName = '';
                        $currentStepNum = 0;
                        $hasBlocked = false;
                        foreach ($case['steps'] as $s) {
                            if ($s['status'] === 'selesai') {
                                $completedSteps++;
                            } elseif ($s['status'] === 'proses') {
                                $currentStepName = $s['step_nama'];
                                $currentStepNum = $s['step_urut'];
                            } elseif ($s['status'] === 'blocked') {
                                $currentStepName = $s['step_nama'];
                                $currentStepNum = $s['step_urut'];
                                $hasBlocked = true;
                            }
                        }
                        $progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
                        
                        if ($completedSteps > 0 && empty($currentStepName) && $progress < 100) {
                            $nextIdx = $completedSteps; // 0-indexed
                            if (isset($case['steps'][$nextIdx])) {
                                $currentStepName = $case['steps'][$nextIdx]['step_nama'];
                                $currentStepNum = $case['steps'][$nextIdx]['step_urut'];
                            }
                        }
                        if ($progress === 100) {
                            $currentStepName = 'Semua Selesai';
                            $currentStepNum = $totalSteps;
                        }
                        if (empty($currentStepName)) {
                            $currentStepName = 'Belum Dimulai';
                            $currentStepNum = 0;
                        }

                        // SLA Check
                        $isSlaExceeded = false;
                        $slaDurHK = 0;
                        if ($progress < 100 && $currentStepNum > 0 && !$hasBlocked) {
                            $prevDateForSla = $case['tgl_mulai_proses'];
                            foreach ($case['steps'] as $s) {
                                if ($s['step_urut'] < $currentStepNum && $s['tgl_realisasi']) {
                                    $prevDateForSla = $s['tgl_realisasi'];
                                }
                            }
                            $curDurInfo = calculateDaysInfo($prevDateForSla, null);
                            if ($curDurInfo && $curDurInfo['kerja'] > $slaLimit) {
                                $isSlaExceeded = true;
                                $slaDurHK = $curDurInfo['kerja'];
                            }
                        }
                    ?>
                    <tr id="row-<?= $case['id'] ?>">
                        <td class="ps-3" onclick="event.stopPropagation();">
                            <?php if (!$isPindahan): ?>
                            <div class="form-check m-0">
                                <input class="form-check-input case-checkbox row-cb shadow-sm" type="checkbox" value="<?= $case['id'] ?>" onchange="updateBulkBar()">
                            </div>
                            <?php else: ?>
                            <div class="text-muted text-center" style="font-size: .8rem;" title="Hanya pantau"><i class="bi bi-eye-fill"></i></div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">
                                <?= htmlspecialchars($case['nama']) ?>
                                <?php if ($isPindahan): ?>
                                    <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem; padding: 2px 4px; border-radius: 4px;">Pantau (<?= htmlspecialchars($sKep) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" style="font-size: .7rem;">
                                <i class="bi bi-flag me-1"></i><?= htmlspecialchars($case['daerah']) ?>
                                <span class="ms-2"><i class="bi bi-building me-1"></i><?= htmlspecialchars($case['pondok']) ?> - <?= htmlspecialchars($case['kelas']) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if ($case['exp_paspor']): 
                                $expDateP = new DateTime($case['exp_paspor']);
                                $expDateP->setTime(0, 0, 0);
                                $diffP = (int) (new DateTime())->setTime(0, 0, 0)->diff($expDateP)->format('%r%a');
                            ?>
                                <span class="fw-bold <?= $diffP <= 0 ? 'text-danger' : ($diffP <= 540 ? 'text-warning' : 'text-dark') ?>"><?= date('d-M-Y', strtotime($case['exp_paspor'])) ?></span>
                                <?php if ($diffP <= 0): ?>
                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                <?php elseif ($diffP <= 540): ?>
                                    <?php $textP = $diffP > 90 ? floor($diffP / 30) . ' bln lagi' : $diffP . ' hari lagi'; ?>
                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textP ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($case['exp_itas']): 
                                $expDateI = new DateTime($case['exp_itas']);
                                $expDateI->setTime(0, 0, 0);
                                $diffI = (int) (new DateTime())->setTime(0, 0, 0)->diff($expDateI)->format('%r%a');
                            ?>
                                <span class="fw-bold <?= $diffI <= 0 ? 'text-danger' : ($diffI <= 90 ? 'text-warning' : 'text-dark') ?>"><?= date('d-M-Y', strtotime($case['exp_itas'])) ?></span>
                                <?php if ($diffI <= 0): ?>
                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                <?php elseif ($diffI <= 90): ?>
                                    <?php $textI = $diffI > 90 ? floor($diffI / 30) . ' bln lagi' : $diffI . ' hari lagi'; ?>
                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="min-width: 200px;">
                            <!-- Step dots visual dynamic -->
                            <div class="step-dots mb-1 flex-wrap">
                                <?php foreach ($case['steps'] as $si => $stp): 
                                    $dotClass = '';
                                    if ($stp['status'] === 'selesai') $dotClass = 'done';
                                    elseif ($stp['status'] === 'proses') $dotClass = 'proses';
                                    elseif ($stp['status'] === 'blocked') $dotClass = 'blocked';
                                ?>
                                    <div class="step-dot <?= $dotClass ?>" title="Tahap <?= $stp['step_urut'] ?>: <?= htmlspecialchars($stp['step_nama']) ?> — <?= ucfirst($stp['status']) ?>"></div>
                                <?php endforeach; ?>
                                <span class="ms-2 fw-bold small <?= $progress == 100 ? 'text-success' : ($hasBlocked ? 'text-danger' : 'text-primary') ?>"><?= $progress ?>%</span>
                            </div>
                            <div class="text-muted" style="font-size: .68rem;">
                                <?php if ($hasBlocked): ?>
                                    <i class="bi bi-exclamation-circle text-danger me-1"></i><span class="text-danger fw-bold">Terkendala:</span> <?= htmlspecialchars($currentStepName) ?>
                                <?php elseif ($progress == 100): ?>
                                    <i class="bi bi-check-all text-success me-1"></i>Semua tahap selesai
                                <?php elseif ($currentStepNum > 0): ?>
                                    <i class="bi bi-arrow-right-circle text-primary me-1"></i>Tahap <?= $currentStepNum ?>: <?= htmlspecialchars($currentStepName) ?>
                                <?php else: ?>
                                    <i class="bi bi-hourglass text-secondary me-1"></i>Belum dimulai
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($case['status'] === 'aktif' && $hasBlocked): ?>
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle py-1 px-2 mb-1" style="font-size: .7rem; display: inline-block;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Terkendala
                                </span>
                            <?php elseif ($case['status'] === 'aktif' && $isSlaExceeded): ?>
                                <span class="badge rounded-pill bg-danger text-white py-1 px-2 mb-1 shadow-sm" style="font-size: .7rem; display: inline-block;">
                                    <i class="bi bi-alarm me-1"></i>SLA Terlewati
                                </span>
                            <?php elseif ($case['status'] === 'aktif'): ?>
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle py-1 px-2 mb-1" style="font-size: .7rem; display: inline-block;">
                                    <i class="bi bi-play-fill me-1"></i>Aktif
                                </span>
                            <?php elseif ($case['status'] === 'selesai'): ?>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle py-1 px-2 mb-1" style="font-size: .7rem; display: inline-block;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-light text-secondary border py-1 px-2 mb-1" style="font-size: .7rem; display: inline-block;">
                                    <?= ucfirst($case['status']) ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php
                                $endProcessDate = null;
                                if ($case['status'] === 'selesai') {
                                    $maxDate = null;
                                    foreach ($case['steps'] as $st) {
                                        if ($st['tgl_realisasi'] && (!$maxDate || $st['tgl_realisasi'] > $maxDate)) {
                                            $maxDate = $st['tgl_realisasi'];
                                        }
                                    }
                                    $endProcessDate = $maxDate;
                                }
                                $caseDur = calculateDaysInfo($case['tgl_mulai_proses'], $endProcessDate);
                            ?>
                            <?php if ($caseDur): ?>
                                <div class="text-muted fw-bold mt-1" style="font-size: .62rem;">
                                    <i class="bi bi-clock-history text-secondary me-1"></i><?= $caseDur['total'] == 0 ? 'Hari ini' : $caseDur['total'] . ' Hari Keseluruhan' ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($isSlaExceeded): ?>
                                <div class="text-danger fw-bold mt-1" style="font-size: .62rem;">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Mandek <?= $slaDurHK ?> HK
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $pay = $casePayments[$case['id']] ?? null;
                            if ($pay): 
                                $ps = $pay['status_bayar_santri'] === 'lunas';
                                $pi = $pay['status_bayar_instansi'] === 'lunas';
                            ?>
                                <div class="d-flex flex-column gap-1" style="font-size: .65rem;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted fw-bold">Santri</span>
                                        <span class="badge <?= $ps ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle' ?> border rounded-pill px-2 py-0"><?= $ps ? '<i class="bi bi-check2"></i> Lunas' : 'Belum' ?></span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted fw-bold">Instansi</span>
                                        <span class="badge <?= $pi ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle' ?> border rounded-pill px-2 py-0"><?= $pi ? '<i class="bi bi-check2"></i> Lunas' : 'Belum' ?></span>
                                    </div>
                                    <button class="btn btn-link p-0 text-decoration-none mt-1 text-primary text-start" style="font-size: .65rem;" onclick="event.stopPropagation(); openPaymentHistory('<?= $case['kds'] ?>')">
                                        <i class="bi bi-clock-history me-1"></i>Riwayat
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small" style="font-size: .65rem;">Belum ada data</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-center" onclick="event.stopPropagation();">
                            <a href="<?= API_URL ?>/job-desk/<?= $case['id'] ?>" class="btn btn-sm <?= $isPindahan ? 'btn-outline-secondary' : 'btn-outline-primary' ?> py-1 px-3 rounded-pill fw-medium" style="font-size: .75rem;">
                                <i class="bi <?= $isPindahan ? 'bi-eye-fill' : 'bi-eye' ?> me-1"></i><?= $isPindahan ? 'Pantau' : 'Detail' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Summary -->
        <div class="bg-light border-top px-4 py-2 d-flex justify-content-between align-items-center small">
            <span class="text-muted fw-medium">Menampilkan <strong class="text-dark"><?= count($cases) ?></strong> kasus</span>
            <span class="text-muted">
                <i class="bi bi-info-circle me-1"></i>Klik baris untuk melihat detail tahapan
            </span>
        </div>
    </div>
    <?php endif; ?> <!-- End of empty cases check -->
    <?php endif; ?> <!-- End of process filter check -->
</div>

<script>
function updateSlaGlobal() {
    let sla = document.getElementById('slaSettingGlobal').value;
    if (sla < 1) sla = 1;
    let url = new URL(window.location.href);
    url.searchParams.set('sla', sla);
    window.location.href = url.toString();
}

// Filter Handling
let currentStatus = '<?= $statusFilter ?>';
let currentProcessId = '<?= $processFilter ?>';

function setFilterStatus(status) {
    currentStatus = status;
    applyFilters();
}

function setProcessFilter(processId) {
    currentProcessId = processId;
    applyFilters();
}

function applyFilters() {
    window.location.href = `?status=${currentStatus}&process_id=${currentProcessId}`;
}

function toggleAllCases(source) {
    const checkboxes = document.querySelectorAll('.case-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = source.checked;
        const row = cb.closest('tr');
        if (source.checked) row.classList.add('selected-row');
        else row.classList.remove('selected-row');
    });
    updateBulkBar();
}

function updateBulkBar() {
    const checkboxes = document.querySelectorAll('.case-checkbox');
    let checkedCount = 0;
    checkboxes.forEach(cb => {
        const row = cb.closest('tr');
        if (cb.checked) {
            checkedCount++;
            row.classList.add('selected-row');
        } else {
            row.classList.remove('selected-row');
        }
    });
    
    const bar = document.getElementById('bulkActionBar');
    const countSpan = document.getElementById('bulkCount');
    
    if (checkedCount > 0) {
        bar.style.display = 'block';
        countSpan.textContent = checkedCount;
    } else {
        bar.style.display = 'none';
        document.getElementById('checkAllCases').checked = false;
    }
}

// Tambahkan event listener untuk drag-to-select checkbox
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.case-checkbox');
    let isDragging = false;
    let targetState = true;

    checkboxes.forEach(cb => {
        // Prevent row click when clicking checkbox
        cb.addEventListener('click', (e) => {
            e.stopPropagation();
            // Mouse clicks are handled by mousedown to support drag-to-select.
            // If e.detail > 0, it's a mouse click, prevent native double-toggle.
            // If e.detail === 0, it's a keyboard click (Spacebar), so we allow it and update.
            if (e.detail > 0) {
                e.preventDefault();
            } else {
                updateBulkBar();
            }
        });
        
        cb.addEventListener('mousedown', (e) => {
            // Left click only
            if (e.button !== 0) return;
            
            isDragging = true;
            targetState = !cb.checked;
            cb.checked = targetState;
            updateBulkBar();
        });
        
        cb.addEventListener('mouseenter', (e) => {
            if (isDragging) {
                cb.checked = targetState;
                updateBulkBar();
            }
        });
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });
});

<?php if ($processFilter !== 'all' && !empty($templateSteps)): ?>
const dynamicSteps = [
    <?php foreach ($templateSteps as $ts): ?>
    { value: "<?= $ts['urut'] ?>", label: "Tahap <?= $ts['urut'] ?> — <?= htmlspecialchars(addslashes($ts['step_nama'])) ?>" },
    <?php endforeach; ?>
];

function openBulkUpdateModal() {
    const checked = Array.from(document.querySelectorAll('.case-checkbox:checked')).map(cb => cb.value);
    if (checked.length === 0) return;

    let optionsHtml = '';
    dynamicSteps.forEach(s => {
        optionsHtml += `<option value="${s.value}">${s.label}</option>`;
    });

    Swal.fire({
        title: '<span style="font-size: 1.1rem;">Update Massal Tahapan</span>',
        html: `
            <div class="text-start mt-2">
                <div class="alert alert-primary py-2 px-3 small border-primary-subtle mb-3">
                    <i class="bi bi-info-circle me-1"></i> <strong>${checked.length} Kasus</strong> akan diupdate.
                </div>
                
                <label class="form-label small fw-bold text-muted mb-1">Tahap yang Diupdate</label>
                <select id="swal-bulk-step" class="form-select form-select-sm mb-3">
                    ${optionsHtml}
                </select>
                
                <label class="form-label small fw-bold text-muted mb-1">Status Baru</label>
                <select id="swal-bulk-status" class="form-select form-select-sm mb-3">
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="proses">Sedang Diproses</option>
                    <option value="blocked">Terkendala / Kurang Berkas</option>
                    <option value="selesai" selected>Selesai</option>
                </select>
                
                <label class="form-label small fw-bold text-muted mb-1">Tanggal Realisasi</label>
                <input type="date" id="swal-bulk-date" class="form-control form-control-sm mb-3" value="${new Date().toISOString().split('T')[0]}">
                
                <label class="form-label small fw-bold text-muted mb-1">Catatan Tambahan (opsional)</label>
                <textarea id="swal-bulk-note" class="form-control form-control-sm" rows="2" placeholder="Catatan ini akan ditambahkan ke semua kasus terpilih..."></textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-all me-1"></i>Update Semua',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => {
            return {
                step_urut: document.getElementById('swal-bulk-step').value,
                status: document.getElementById('swal-bulk-status').value,
                date: document.getElementById('swal-bulk-date').value,
                note: document.getElementById('swal-bulk-note').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/job-desk/step/bulk-update', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    case_ids: checked,
                    step_urut: result.value.step_urut,
                    status: result.value.status,
                    date: result.value.date,
                    note: result.value.note
                })
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: res.message,
                        timer: 2000, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function bulkDeleteCases() {
    const checked = Array.from(document.querySelectorAll('.case-checkbox:checked')).map(cb => cb.value);
    if (checked.length === 0) return;

    Swal.fire({
        title: 'Hapus Massal?',
        text: `Anda yakin ingin menghapus ${checked.length} kasus ini secara permanen? Data tahapan dan riwayat juga akan ikut terhapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/job-desk/case/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ case_ids: checked })
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Terhapus!', text: res.message,
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menghapus kasus', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

// Table Filtering and Sorting
function filterTable() {
    const inputs = document.querySelectorAll('.col-search');
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let isVisible = true;
        inputs.forEach(input => {
            const index = input.getAttribute('data-col');
            const searchVal = input.value.toLowerCase().trim();
            if (searchVal) {
                const cell = row.cells[index];
                if (cell) {
                    const text = cell.textContent || cell.innerText;
                    if (text.toLowerCase().indexOf(searchVal) === -1) {
                        isVisible = false;
                    }
                }
            }
        });
        row.style.display = isVisible ? '' : 'none';
    });
}

let sortDirection = {};
function sortTable(colIndex) {
    const tbody = document.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    let dir = sortDirection[colIndex] === 'asc' ? 'desc' : 'asc';
    sortDirection[colIndex] = dir;
    
    // Update icons
    document.querySelectorAll('thead th i.bi').forEach(icon => {
        icon.className = 'bi bi-arrow-down-up ms-1 opacity-50';
    });
    const thIcon = document.querySelector(`thead th:nth-child(${colIndex + 1}) i.bi`);
    if (thIcon) {
        thIcon.className = dir === 'asc' ? 'bi bi-arrow-up text-primary' : 'bi bi-arrow-down text-primary';
    }

    rows.sort((a, b) => {
        let textA = a.cells[colIndex].textContent.trim().toLowerCase();
        let textB = b.cells[colIndex].textContent.trim().toLowerCase();
        
        // Progress sorting (extract %)
        if (textA.includes('%') && textB.includes('%')) {
            let numA = parseFloat(textA.match(/[\d\.]+/)) || 0;
            let numB = parseFloat(textB.match(/[\d\.]+/)) || 0;
            return dir === 'asc' ? numA - numB : numB - numA;
        }
        
        // Date sorting
        let dateA = Date.parse(textA);
        let dateB = Date.parse(textB);
        // Valid if both are dates and not just simple numbers
        if (!isNaN(dateA) && !isNaN(dateB) && textA.length > 5 && textB.length > 5 && isNaN(textA) && isNaN(textB)) {
             return dir === 'asc' ? dateA - dateB : dateB - dateA;
        }

        if (textA < textB) return dir === 'asc' ? -1 : 1;
        if (textA > textB) return dir === 'asc' ? 1 : -1;
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

// Step Detail Modal Logic
const stepCasesMap = <?= isset($stepCasesJson) ? $stepCasesJson : '{}' ?>;
let stepDetailModalInstance;

function openStepDetailModal(urut, stepNama, color) {
    if (!stepDetailModalInstance) {
        stepDetailModalInstance = new bootstrap.Modal(document.getElementById('stepDetailModal'));
    }
    
    document.getElementById('stepModalTitle').textContent = `Tahap ${urut}: ${stepNama}`;
    document.getElementById('stepModalTitle').style.color = color;
    document.getElementById('stepModalCurrentUrut').value = urut; // Store for bulk update
    
    const cases = stepCasesMap[urut] || [];
    const tbody = document.getElementById('stepModalTbody');
    tbody.innerHTML = '';
    
    if (cases.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>Belum ada santri di tahap ini</td></tr>`;
        document.getElementById('btnStepBulkUpdate').style.display = 'none';
    } else {
        cases.forEach(c => {
            let statusBadge = '';
            if (c.step_status === 'blocked') {
                statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-exclamation-triangle-fill me-1"></i>Terkendala</span>';
            } else if (c.step_status === 'selesai') {
                statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
            } else {
                statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-arrow-repeat me-1"></i>Proses</span>';
            }
            
            // Format Dates safely
            const formatDate = (dateStr) => {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                if (isNaN(d)) return dateStr;
                return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            };
            
            const isExpired = (dateStr) => {
                if (!dateStr) return false;
                return new Date(dateStr) < new Date();
            };
            
            let pasporColor = isExpired(c.exp_paspor) ? 'text-danger' : 'text-dark';
            let itasColor = isExpired(c.exp_itas) ? 'text-danger' : 'text-dark';
            const isPindahan = ('<?= $_SESSION['def_kepengurusan'] ?? '' ?>' !== '' && c.kepengurusan && c.kepengurusan.trim().toLowerCase() !== '<?= strtolower($_SESSION['def_kepengurusan'] ?? '') ?>');
                
            tbody.innerHTML += `
                <tr>
                    <td class="ps-4 py-3 align-middle" style="width: 40px;">
                        ${isPindahan ? '<i class="bi bi-eye-fill text-muted" title="Hanya pantau"></i>' : `<input class="form-check-input step-check-item" type="checkbox" value="${c.id}" checked>`}
                    </td>
                    <td class="py-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-0" style="font-size: 0.65rem;" title="Stambuk / KDS">${c.kds}</span>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-0" style="font-size: 0.65rem;" title="No Paspor"><i class="bi bi-passport me-1"></i>${c.no_paspor || '-'}</span>
                        </div>
                        <div class="fw-bold text-dark mb-1" style="font-size: .85rem;">${c.nama}</div>
                        <div class="d-flex align-items-center gap-3 text-muted" style="font-size: .7rem;">
                            <span><i class="bi bi-flag me-1"></i>${c.daerah}</span>
                            <span><i class="bi bi-building me-1"></i>${c.pondok} - ${c.kelas}</span>
                        </div>
                    </td>
                    <td class="fw-bold ${pasporColor}">${formatDate(c.exp_paspor)}</td>
                    <td class="fw-bold ${itasColor}">${formatDate(c.exp_itas)}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
        
        // --- Drag to Select Logic for Modal Rows ---
        const stepCheckboxes = document.querySelectorAll('.step-check-item');
        const stepRows = document.querySelectorAll('#stepModalTbody tr');

        stepRows.forEach((row, index) => {
            const cb = stepCheckboxes[index];
            row.style.cursor = 'pointer'; // Make row look clickable
            
            row.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return; // Only left click
                e.preventDefault(); // Prevent text selection while dragging
                
                stepDragging = true;
                stepTargetState = !cb.checked;
                cb.checked = stepTargetState;
                updateStepModalCheckAll();
            });
            
            row.addEventListener('mouseenter', (e) => {
                if (stepDragging) {
                    cb.checked = stepTargetState;
                    updateStepModalCheckAll();
                }
            });
            
            cb.addEventListener('click', (e) => {
                e.stopPropagation(); // prevent double toggle if clicked directly
            });
        });
        // -------------------------------------------

        const checkAll = document.querySelector('.step-check-all');
        if (checkAll) checkAll.checked = true;
        document.getElementById('btnStepBulkUpdate').style.display = 'inline-block';
    }
    
    stepDetailModalInstance.show();
}

let stepDragging = false;
let stepTargetState = false;
document.addEventListener('mouseup', () => {
    stepDragging = false;
});

function updateStepModalCheckAll() {
    const allCbs = document.querySelectorAll('.step-check-item');
    const checkedCbs = document.querySelectorAll('.step-check-item:checked');
    const checkAll = document.querySelector('.step-check-all');
    if (checkAll) {
        checkAll.checked = (allCbs.length > 0 && allCbs.length === checkedCbs.length);
    }
}

function toggleStepModalCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.step-check-item');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function openStepBulkUpdateModal() {
    const urut = document.getElementById('stepModalCurrentUrut').value;
    const checkedBoxes = document.querySelectorAll('.step-check-item:checked');
    
    if (checkedBoxes.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu santri yang ingin diproses.', 'warning');
        return;
    }
    
    const caseIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    let optionsHtml = '';
    dynamicSteps.forEach(s => {
        optionsHtml += `<option value="${s.value}">${s.label}</option>`;
    });

    stepDetailModalInstance.hide(); // Hide the table modal before showing SweetAlert
    
    Swal.fire({
        title: '<span style="font-size: 1.1rem;">Lanjutkan Proses Santri</span>',
        html: `
            <div class="text-start mt-2">
                <div class="alert alert-primary py-2 px-3 small border-primary-subtle mb-3">
                    <i class="bi bi-info-circle me-1"></i> <strong>${caseIds.length} Santri</strong> dari Tahap ${urut} akan diproses.
                </div>
                
                <label class="form-label small fw-bold text-muted mb-1">Pindahkan ke Tahap</label>
                <select id="swal-bulk-step2" class="form-select form-select-sm mb-3">
                    ${optionsHtml}
                </select>
                
                <label class="form-label small fw-bold text-muted mb-1">Status Baru</label>
                <select id="swal-bulk-status2" class="form-select form-select-sm mb-3">
                    <option value="proses" selected>Sedang Diproses (Tahap Baru)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="blocked">Terkendala / Kurang Berkas</option>
                    <option value="selesai">Selesai (Di Tahap Baru)</option>
                </select>
                
                <label class="form-label small fw-bold text-muted mb-1">Tanggal Realisasi</label>
                <input type="date" id="swal-bulk-date2" class="form-control form-control-sm mb-3" value="${new Date().toISOString().split('T')[0]}">
                
                <label class="form-label small fw-bold text-muted mb-1">Catatan Tambahan (opsional)</label>
                <textarea id="swal-bulk-note2" class="form-control form-control-sm" rows="2" placeholder="Catatan ini akan ditambahkan ke semua kasus terpilih..."></textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-rocket-takeoff me-1"></i>Proses Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => {
            return {
                step_urut: document.getElementById('swal-bulk-step2').value,
                status: document.getElementById('swal-bulk-status2').value,
                date: document.getElementById('swal-bulk-date2').value,
                note: document.getElementById('swal-bulk-note2').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/job-desk/step/bulk-update', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    case_ids: caseIds,
                    step_urut: result.value.step_urut,
                    status: result.value.status,
                    date: result.value.date,
                    note: result.value.note
                })
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: res.message,
                        timer: 2000, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function openBulkPaymentModal() {
    let checked = document.querySelectorAll('.case-checkbox:checked');
    if (checked.length === 0) {
        Swal.fire('Peringatan', 'Silakan centang minimal 1 kasus santri terlebih dahulu.', 'warning');
        return;
    }
    
    let caseIds = Array.from(checked).map(cb => cb.value);
    
    Swal.fire({
        title: 'Tandai Lunas Massal',
        html: `
            <div class="text-start mt-3">
                <p class="text-muted small mb-3">Kasus Terpilih: <span class="badge bg-primary rounded-pill px-2 py-1">${checked.length} santri</span></p>
                <label class="form-label small fw-bold text-muted mb-1">Tipe Pembayaran</label>
                <select id="swal-bulk-pay-type" class="form-select form-select-sm mb-3">
                    <option value="santri">Lunas Pembayaran Santri ke Staf</option>
                    <option value="instansi">Lunas Pembayaran Staf ke Instansi</option>
                    <option value="keduanya">Lunas Keduanya</option>
                </select>
                <label class="form-label small fw-bold text-muted mb-1">Tanggal Bayar</label>
                <input type="date" id="swal-bulk-pay-date" class="form-control form-control-sm mb-3" value="${new Date().toISOString().split('T')[0]}">
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-cash-coin me-1"></i>Tandai Lunas',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => {
            return {
                type: document.getElementById('swal-bulk-pay-type').value,
                date: document.getElementById('swal-bulk-pay-date').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            fetch('<?= API_URL ?>/api/job-desk/payment/bulk-update', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    case_ids: caseIds,
                    type: result.value.type,
                    tgl_bayar: result.value.date
                })
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error'));
        }
    });
}

function openPaymentHistory(kds) {
    Swal.fire({ title: 'Memuat Riwayat...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
    fetch('<?= API_URL ?>/api/job-desk/payment/history/' + kds)
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message);
            
            let html = '<div class="text-start">';
            if (res.data.length === 0) {
                html += '<p class="text-muted text-center py-3">Belum ada riwayat pembayaran.</p>';
            } else {
                html += '<div class="list-group list-group-flush mb-3" style="font-size: .85rem; max-height: 350px; overflow-y: auto;">';
                res.data.forEach(h => {
                    let dateSantri = h.status_bayar_santri === 'lunas' ? (h.tgl_bayar_santri || 'Lunas') : '<span class="text-warning">Belum Lunas</span>';
                    let dateInstansi = h.status_bayar_instansi === 'lunas' ? (h.tgl_bayar_instansi || 'Lunas') : '<span class="text-warning">Belum Lunas</span>';
                    
                    html += `
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-primary">${h.nama_proses}</h6>
                                <span class="badge bg-secondary rounded-pill">${new Date(h.case_created_at).getFullYear()}</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted" style="font-size: .75rem;">
                                <div><i class="bi bi-person-fill me-1"></i>Santri: ${dateSantri} <br>Rp ${Number(h.nominal_santri).toLocaleString('id-ID')}</div>
                                <div class="text-end"><i class="bi bi-bank2 me-1"></i>Instansi: ${dateInstansi} <br>Rp ${Number(h.nominal_instansi).toLocaleString('id-ID')}</div>
                            </div>
                            ${h.catatan ? `<div class="mt-2 p-2 bg-light rounded text-muted" style="font-size:.75rem;"><i class="bi bi-info-circle me-1"></i>${h.catatan}</div>` : ''}
                        </div>
                    `;
                });
                html += '</div>';
                
                html += `
                    <div class="bg-light p-3 rounded-3 text-center border mt-2">
                        <div class="text-muted" style="font-size: .75rem;">TOTAL SELISIH OPERASIONAL HISTORIS</div>
                        <h5 class="mb-0 text-success fw-bold">Rp ${Number(res.summary.total_selisih).toLocaleString('id-ID')}</h5>
                    </div>
                `;
            }
            html += '</div>';
            
            Swal.fire({
                title: 'Riwayat Pembayaran KDS: ' + kds,
                html: html,
                width: '600px',
                confirmButtonText: 'Tutup',
                customClass: { popup: 'shadow-lg rounded-4' }
            });
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Gagal memuat data riwayat', 'error');
        });
}
<?php endif; ?>
</script>

<!-- Step Detail Modal -->
<div class="modal fade" id="stepDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="stepModalTitle">
                    Tahap Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-4" style="width: 40px;">
                                    <input class="form-check-input step-check-all" type="checkbox" onclick="toggleStepModalCheckboxes(this)" checked>
                                </th>
                                <th>Santri</th>
                                <th>Exp Paspor</th>
                                <th>Exp ITAS</th>
                                <th>Status Tahap</th>
                            </tr>
                        </thead>
                        <tbody id="stepModalTbody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top d-flex justify-content-between px-4">
                <input type="hidden" id="stepModalCurrentUrut" value="">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="btnStepBulkUpdate" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium shadow-sm d-flex align-items-center gap-2" onclick="openStepBulkUpdateModal()">
                    <i class="bi bi-rocket-takeoff"></i> Lanjutkan Proses Santri Ini
                </button>
            </div>
        </div>
    </div>
</div>
