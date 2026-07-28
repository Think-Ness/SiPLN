<?php
declare(strict_types=1);
use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var int $total
 * @var int $aktif
 * @var int $inaktif
 * @var int $expPasporSoon
 * @var int $expItasSoon
 * @var int $capel
 * @var int $alumni
 * @var int $pengabdian
 * @var array $pengabdianBreakdown
 * @var array $santris
 * @var string $status
 */
$this->setTitle('Dashboard | Sistem Informasi');

$santrisJson = json_encode($santris);
?>

<?php
$role = $_SESSION['role'] ?? '';
$myKep = $_SESSION['def_kepengurusan'] ?? '';
$viewFilter = $view ?? 'my'; // default from Action.php
?>
<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #0d6efd15;">
            <i class="bi bi-speedometer2 fs-5 text-primary"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Dashboard Statistik Santri</h4>
            <div class="text-muted small fw-medium mt-1">Data diperbarui secara real-time. Klik pada grafik untuk rincian.</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- View Filter (Cross-Tenant) -->
        <?php if ($role !== 'super_admin' && !empty($myKep)): ?>
        <div class="btn-group btn-group-sm shadow-sm rounded-pill p-1 bg-white border" role="group">
            <a href="?view=my&status=<?= $status ?>" class="btn rounded-pill border-0 <?= $viewFilter === 'my' ? 'btn-primary' : 'btn-light text-secondary' ?> fw-bold px-3">
                <i class="bi bi-building me-1"></i> Instansi Saya
            </a>
            <a href="?view=all&status=<?= $status ?>" class="btn rounded-pill border-0 <?= $viewFilter === 'all' ? 'btn-primary' : 'btn-light text-secondary' ?> fw-bold px-3">
                <i class="bi bi-globe me-1"></i> Semua
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Status Filter -->
        <div class="btn-group btn-group-sm shadow-sm" role="group">
            <a href="?view=<?= $viewFilter ?>&status=1" class="btn <?= $status === '1' ? 'btn-primary' : 'btn-outline-primary' ?>">Aktif</a>
            <a href="?view=<?= $viewFilter ?>&status=0" class="btn <?= $status === '0' ? 'btn-primary' : 'btn-outline-primary' ?>">Non Aktif</a>
            <a href="?view=<?= $viewFilter ?>&status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">Semua</a>
        </div>
        <div>
            <?php
            $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;
            if ($isLocal): ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-2" id="networkStatusBadge"><i class="bi bi-hdd-network-fill me-1" style="font-size:.5rem"></i> Server Lokal (Offline)</span>
            <?php else: ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle me-2" id="networkStatusBadge"><i class="bi bi-cloud-check-fill me-1" style="font-size:.5rem"></i> Server Online</span>
            <?php endif; ?>
            <span class="text-muted small"><?= date('d M Y, H:i') ?></span>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <!-- Row 1 -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #198754 !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(25,135,84,.12);">
                    <i class="bi bi-people-fill text-success fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-success lh-1 mb-1"><?= $aktif ?></div>
                    <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: .5px; font-size: .75rem;">Santri Aktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(220,53,69,.12);">
                    <i class="bi bi-person-x-fill text-danger fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-danger lh-1 mb-1"><?= $inaktif ?></div>
                    <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: .5px; font-size: .75rem;">Santri Inaktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #059669 !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(5,150,105,.12);">
                    <i class="bi bi-award-fill fs-4" style="color: #059669;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1 mb-1" style="color: #059669;"><?= $alumni ?></div>
                    <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: .5px; font-size: .75rem;">Alumni</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #7c3aed !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(124,58,237,.12);">
                    <i class="bi bi-building-fill text-purple fs-4" style="color: #7c3aed;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1 mb-1" style="color: #7c3aed;"><?= $pengabdian ?></div>
                    <div class="text-muted small fw-medium text-uppercase mb-2" style="letter-spacing: .5px; font-size: .75rem;">Pengabdian</div>
                    <?php if (!empty($pengabdianBreakdown)): ?>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($pengabdianBreakdown as $b): ?>
                                <span class="badge" style="background: rgba(124,58,237,.1); color: #7c3aed; border: 1px solid rgba(124,58,237,.2); font-size: .65rem;">
                                    <?= htmlspecialchars($b['pondok'] ?: 'Unknown') ?>: <?= $b['count'] ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2 -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #0d6efd !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(13,110,253,.12);">
                    <i class="bi bi-person-lines-fill text-primary fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-primary lh-1 mb-1"><?= $capel ?></div>
                    <div class="text-muted small fw-medium text-uppercase mb-2" style="letter-spacing: .5px; font-size: .75rem;">Calon Pelajar</div>
                    <?php if (!empty($capelBreakdown)): ?>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($capelBreakdown as $b): ?>
                                <span class="badge" style="background: rgba(13,110,253,.1); color: #0d6efd; border: 1px solid rgba(13,110,253,.2); font-size: .65rem;">
                                    <?= htmlspecialchars($b['program'] ?: 'Unknown') ?>: <?= $b['count'] ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #fd7e14 !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(253,126,20,.12);">
                    <i class="bi bi-passport-fill text-warning fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-warning lh-1 mb-1"><?= $expPasporSoon ?></div>
                    <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: .5px; font-size: .75rem;">Paspor Exp. ≤ 1 Bln</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-left:4px solid #0dcaf0 !important;">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:rgba(13,202,240,.12);">
                    <i class="bi bi-card-text text-info fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-info lh-1 mb-1"><?= $expItasSoon ?></div>
                    <div class="text-muted small fw-medium text-uppercase" style="letter-spacing: .5px; font-size: .75rem;">ITAS Exp. ≤ 3 Bln</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- INTERACTIVE WORLD MAP - Sebaran Santri Dunia -->
<!-- ============================================ -->
<div id="dashboardFullscreenWrapper" class="dashboard-fs-wrapper" style="position:relative;">
<!-- Charts -->
<!-- Charts Row 1 (Pie & Doughnut Charts) -->
<?php 
$showAsliPindahan = ($viewFilter === 'my' && !empty($myKep) && !empty($_SESSION['def_pondok']));
$colSize = $showAsliPindahan ? 3 : 4;
?>
<div class="row g-4 mb-4 fs-hide-in-immersive">
    <div class="col-lg-<?= $colSize ?>">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-globe-americas text-primary me-2"></i>Distribusi Negara Asal</h6>
            </div>
            <div class="card-body p-4"><canvas id="negaraChart" height="250"></canvas></div>
        </div>
    </div>
    <div class="col-lg-<?= $colSize ?>">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-vcard text-info me-2"></i>Kewarganegaraan</h6>
            </div>
            <div class="card-body p-4"><canvas id="kewarganegaraanChart" height="250"></canvas></div>
        </div>
    </div>
    <div class="col-lg-<?= $colSize ?>">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-building-fill text-danger me-2"></i>Distribusi Pondok</h6>
            </div>
            <div class="card-body p-4"><canvas id="pondokChart" height="250"></canvas></div>
        </div>
    </div>
    <?php if ($showAsliPindahan): ?>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill text-success me-2"></i>Asli vs Pindahan</h6>
            </div>
            <div class="card-body p-4"><canvas id="asliPindahanChart" height="250"></canvas></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Charts Row 2 (Bar Charts) -->
<div class="row g-4 mb-4 fs-hide-in-immersive">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3-fill text-success me-2"></i>Distribusi Kepengurusan</h6>
            </div>
            <div class="card-body p-4"><canvas id="kepengurusanChart" height="280"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-mortarboard-fill text-warning me-2"></i>Distribusi Angkatan</h6>
            </div>
            <div class="card-body p-4"><canvas id="angkatanChart" height="280"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div id="mapCard" class="card border-0 shadow-sm rounded-4 overflow-hidden map-card-light">
            <div class="card-header border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center map-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="map-icon-circle rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="bi bi-globe2 map-icon-glyph" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold map-title" style="letter-spacing:-.3px;">Sebaran & Demografi Wilayah</h6>
                        <small class="map-subtitle" style="font-size:.7rem;">Klik negara untuk melihat detail santri</small>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button id="mapStyleToggle" class="btn btn-sm rounded-pill px-3 map-btn d-none" onclick="toggleGlobeStyle()" title="Ganti Style Globe">
                        <i class="bi bi-image me-1" id="mapStyleIcon"></i><span id="mapStyleLabel">Satelit</span>
                    </button>
                    <button id="map3DToggle" class="btn btn-sm rounded-pill px-3 map-btn" onclick="toggleMap3D()" title="Ganti Mode 3D/2D">
                        <i class="bi bi-box me-1" id="map3DIcon"></i><span id="map3DLabel">3D Mode</span>
                    </button>
                    <button id="mapThemeToggle" class="btn btn-sm rounded-pill px-3 map-btn" onclick="toggleMapTheme()" title="Ganti Tema">
                        <i class="bi bi-moon-stars-fill me-1" id="mapThemeIcon"></i><span id="mapThemeLabel">Dark</span>
                    </button>
                    <button class="btn btn-sm rounded-pill px-3 map-btn" onclick="resetMapZoom()">
                        <i class="bi bi-arrows-fullscreen me-1"></i>Reset
                    </button>
                    <button id="mapFullscreenBtn" class="btn btn-sm rounded-pill px-3 map-btn" onclick="toggleMapFullscreen()" title="Full Screen">
                        <i class="bi bi-arrows-angle-expand me-1" id="mapFsIcon"></i><span id="mapFsLabel" class="d-none d-md-inline">Full Screen</span>
                    </button>
                </div>
            </div>
            <div class="card-body p-0 position-relative" style="min-height:70vh;">
                <!-- Fixed Height Wrapper for Map/Globe to overlap them without display:none -->
                <div id="mapContainersWrapper" style="position:relative;width:100%;height:70vh;min-height:600px;">
                    <div id="worldMapContainer" style="position:absolute;top:0;left:0;width:100%;height:100%;transition:opacity .3s ease;z-index:2;"></div>
                    <div id="globeVizContainer" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;pointer-events:none;transition:opacity .3s ease;border-radius:0 0 16px 16px;overflow:hidden;background:transparent;z-index:1;"></div>
                </div>
                <!-- Summary Badge -->
                <div id="mapSummaryBadge" class="map-summary-badge">
                    <div class="map-summary-label">Total Negara</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span id="mapTotalCountries" class="map-summary-big">0</span>
                        <span class="map-summary-unit">negara</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span id="mapTotalSantri" class="map-summary-accent">0</span>
                        <span class="map-summary-unit">santri</span>
                    </div>
                </div>
                <!-- Country Sidebar -->
                <div id="countrySidebarToggle" class="country-sidebar-toggle" onclick="toggleCountrySidebar()">
                    <i class="bi bi-chevron-right"></i>
                </div>
                <div id="mapCountrySidebar" class="map-sidebar">
                    <div class="map-sidebar-title"><i class="bi bi-bar-chart-fill me-1"></i>Per Negara</div>
                    <div id="mapCountryList"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Map Tooltip (OUTSIDE card agar tidak terpotong) -->
<div id="mapTooltip" class="map-tooltip">
    <div class="d-flex align-items-center gap-2 mb-1">
        <span id="mapTooltipFlag" style="font-size:1.4rem;line-height:1;"></span>
        <div>
            <div id="mapTooltipName" class="map-tooltip-name"></div>
            <div id="mapTooltipCode" style="font-size:.6rem;color:#64748b;letter-spacing:1px;font-weight:600;"></div>
        </div>
    </div>
    <div style="border-top:1px solid rgba(148,163,184,.15);margin:6px 0;padding-top:6px;">
        <div class="d-flex align-items-baseline gap-1">
            <span id="mapTooltipCount" class="map-tooltip-count"></span>
            <span class="map-tooltip-unit">santri</span>
        </div>
        <div id="mapTooltipPercent" class="map-tooltip-percent"></div>
        <div id="mapTooltipBar" style="height:4px;border-radius:3px;background:rgba(148,163,184,.15);margin-top:6px;overflow:hidden;">
            <div id="mapTooltipBarFill" style="height:100%;border-radius:3px;background:#38bdf8;transition:width .3s ease;"></div>
        </div>
    </div>
</div>

<!-- Libraries untuk jVectorMap CSS -->
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/jquery-jvectormap.min.css">
<style>
/* ===== LIGHT THEME (Default) ===== */
.map-card-light { background: linear-gradient(135deg,#e0f2fe 0%,#dbeafe 25%,#ede9fe 50%,#fce7f3 75%,#e0f2fe 100%) !important; transition: background .5s ease; position: relative; overflow: hidden; }
.map-card-light::before { content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(ellipse at 30% 50%,rgba(59,130,246,.08) 0%,transparent 50%),radial-gradient(ellipse at 70% 30%,rgba(168,85,247,.06) 0%,transparent 50%),radial-gradient(ellipse at 50% 80%,rgba(14,165,233,.06) 0%,transparent 50%); animation:meshFloat 20s ease-in-out infinite; pointer-events:none; z-index:0; }
@keyframes meshFloat { 0%,100%{transform:translate(0,0) rotate(0deg)} 33%{transform:translate(2%,-2%) rotate(1deg)} 66%{transform:translate(-1%,1%) rotate(-0.5deg)} }
.map-card-dark  { background: linear-gradient(135deg,#020617 0%,#0f172a 30%,#1e1b4b 60%,#0f172a 100%) !important; transition: background .5s ease; position: relative; overflow: hidden; }
.map-card-dark::before { content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(ellipse at 20% 60%,rgba(56,189,248,.06) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(139,92,246,.05) 0%,transparent 50%),radial-gradient(ellipse at 50% 90%,rgba(249,115,22,.04) 0%,transparent 50%); animation:meshFloat 20s ease-in-out infinite; pointer-events:none; z-index:0; }
.map-card-header { background:transparent !important; position:relative; z-index:2; }

.map-card-light .map-icon-circle { background:rgba(59,130,246,.12); box-shadow:0 0 20px rgba(59,130,246,.15); }
.map-card-light .map-icon-glyph  { color:#3b82f6; }
.map-card-light .map-title    { color:#1e293b; }
.map-card-light .map-subtitle { color:#94a3b8; }
.map-card-light .map-btn { background:rgba(255,255,255,.7);color:#3b82f6;border:1px solid rgba(59,130,246,.2);font-size:.75rem;font-weight:600;transition:all .25s ease;backdrop-filter:blur(8px);box-shadow:0 2px 8px rgba(0,0,0,.04); }
.map-card-light .map-btn:hover { background:rgba(59,130,246,.12);transform:translateY(-1px);box-shadow:0 4px 12px rgba(59,130,246,.15); }
.map-card-light .map-summary-badge { background:rgba(255,255,255,.85);border:1px solid rgba(59,130,246,.15);box-shadow:0 8px 32px rgba(0,0,0,.08); }
.map-card-light .map-summary-label  { color:#3b82f6; }
.map-card-light .map-summary-big    { color:#1e293b; }
.map-card-light .map-summary-accent { color:#3b82f6; }
.map-card-light .map-summary-unit   { color:#94a3b8; }
.map-card-light .map-sidebar { background:rgba(255,255,255,.82);border-color:rgba(59,130,246,.12);box-shadow:0 8px 32px rgba(0,0,0,.06); }
.map-card-light .map-sidebar-title  { color:#3b82f6; }
.map-card-light .map-country-name   { color:#334155; }
.map-card-light .map-country-bar    { background:rgba(59,130,246,.1); }
.map-card-light .map-country-pct    { color:#94a3b8; }
.map-card-light .map-country-item:hover { background:rgba(59,130,246,.06);border-color:rgba(59,130,246,.12); }

/* ===== DARK THEME ===== */
.map-card-dark .map-icon-circle { background:rgba(56,189,248,.15); box-shadow:0 0 20px rgba(56,189,248,.15); }
.map-card-dark .map-icon-glyph  { color:#38bdf8; }
.map-card-dark .map-title    { color:#e2e8f0; }
.map-card-dark .map-subtitle { color:#64748b; }
.map-card-dark .map-btn { background:rgba(15,23,42,.6);color:#38bdf8;border:1px solid rgba(56,189,248,.2);font-size:.75rem;font-weight:600;transition:all .25s ease;backdrop-filter:blur(8px);box-shadow:0 2px 8px rgba(0,0,0,.2); }
.map-card-dark .map-btn:hover { background:rgba(56,189,248,.15);transform:translateY(-1px);box-shadow:0 4px 16px rgba(56,189,248,.2); }
.map-card-dark .map-summary-badge { background:rgba(15,23,42,.8);border:1px solid rgba(56,189,248,.2);box-shadow:0 8px 32px rgba(0,0,0,.3); }
.map-card-dark .map-summary-label  { color:#38bdf8; }
.map-card-dark .map-summary-big    { color:#f1f5f9; }
.map-card-dark .map-summary-accent { color:#38bdf8; }
.map-card-dark .map-summary-unit   { color:#64748b; }
.map-card-dark .map-sidebar { background:rgba(15,23,42,.8);border-color:rgba(56,189,248,.12);box-shadow:0 8px 32px rgba(0,0,0,.3); }
.map-card-dark .map-sidebar-title  { color:#38bdf8; }
.map-card-dark .map-country-name   { color:#e2e8f0; }
.map-card-dark .map-country-bar    { background:rgba(56,189,248,.12); }
.map-card-dark .map-country-pct    { color:#64748b; }
.map-card-dark .map-country-item:hover { background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.15); }

/* ===== Shared ===== */
.map-summary-badge { position:absolute;bottom:20px;left:20px;backdrop-filter:blur(16px);border-radius:16px;padding:16px 22px;z-index:10;transition:all .4s ease; }
.map-summary-label { font-size:.6rem;text-transform:uppercase;letter-spacing:1.8px;font-weight:700; }
.map-summary-big { font-size:1.8rem;font-weight:800;line-height:1; }
.map-summary-accent { font-size:1.2rem;font-weight:700;line-height:1; }
.map-summary-unit { font-size:.65rem; }
.map-sidebar { position:absolute;top:10px;right:10px;bottom:10px;width:220px;backdrop-filter:blur(16px);border:1px solid;border-radius:16px;padding:14px;overflow-y:auto;z-index:10;transition:all .4s cubic-bezier(0.16,1,0.3,1); }
.map-sidebar.closed { right: -240px !important; }
.country-sidebar-toggle { position:absolute;top:50%;right:240px;transform:translateY(-50%);width:28px;height:60px;background:rgba(15,23,42,.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.1);border-right:none;border-radius:8px 0 0 8px;color:#38bdf8;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:9;transition:all .4s cubic-bezier(0.16,1,0.3,1); }
.country-sidebar-toggle.closed { right:0; border-radius:8px 0 0 8px; }
.country-sidebar-toggle.closed i { transform:rotate(180deg); }
.map-sidebar-title { font-size:.6rem;text-transform:uppercase;letter-spacing:1.8px;font-weight:700;margin-bottom:10px; }
.map-country-item { display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:10px;margin-bottom:3px;cursor:pointer;transition:all .3s cubic-bezier(.4,0,.2,1);border:1px solid transparent; }
.map-country-item:hover { transform:translateX(4px); }
.map-country-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0;box-shadow:0 0 8px currentColor;animation:dotPulse 2.5s ease-in-out infinite; }
@keyframes dotPulse { 0%,100%{box-shadow:0 0 4px currentColor}50%{box-shadow:0 0 12px currentColor,0 0 20px currentColor} }
.map-country-bar { height:3px;border-radius:2px;overflow:hidden;margin-top:3px; }
.map-country-bar-fill { height:100%;border-radius:2px;transition:width .8s cubic-bezier(.25,.46,.45,.94); }
.map-sidebar::-webkit-scrollbar { width:3px; }
.map-sidebar::-webkit-scrollbar-track { background:transparent; }
.map-sidebar::-webkit-scrollbar-thumb { background:rgba(100,116,139,.2);border-radius:3px; }

/* Globe 3D container */
#globeVizContainer { position:relative; z-index:1; }
#globeVizContainer canvas { border-radius:0 0 16px 16px; }

/* Tooltip - glassmorphism upgrade */
.map-tooltip { display:none;position:fixed;background:rgba(15,23,42,.88);backdrop-filter:blur(20px) saturate(180%);border:1px solid rgba(56,189,248,.3);border-radius:14px;padding:14px 18px;z-index:1040;pointer-events:none;box-shadow:0 16px 48px rgba(0,0,0,.4),0 0 0 1px rgba(56,189,248,.1) inset;min-width:160px;transition:opacity .15s ease; }
.map-tooltip-name { font-size:.82rem;font-weight:700;color:#f1f5f9; }
.map-tooltip-count { font-size:1.4rem;font-weight:800;color:#38bdf8; }
.map-tooltip-unit { font-size:.65rem;color:#64748b; }
.map-tooltip-percent { font-size:.65rem;color:#94a3b8;margin-top:2px; }

/* jVectorMap Controls */
.jvectormap-zoomin,.jvectormap-zoomout { background:rgba(100,116,139,.12) !important;color:#64748b !important;border:1px solid rgba(100,116,139,.2) !important;border-radius:8px !important;width:28px !important;height:28px !important;line-height:26px !important;font-size:14px !important;left:15px !important;transition:all .25s ease !important; }
.jvectormap-zoomin { top:15px !important; }
.jvectormap-zoomout { top:48px !important; }
.jvectormap-zoomin:hover,.jvectormap-zoomout:hover { background:rgba(59,130,246,.2) !important;color:#3b82f6 !important; }
.map-card-dark .jvectormap-zoomin,.map-card-dark .jvectormap-zoomout { background:rgba(56,189,248,.12) !important;color:#38bdf8 !important;border-color:rgba(56,189,248,.25) !important; }
/* 2D Map SVG overlay markers */
.map-2d-marker { position:absolute;left:0;top:0; pointer-events:none; z-index:5; will-change:transform; }
.map-2d-marker-tag { background:rgba(15,23,42,.78);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.18);border-radius:8px;padding:3px 8px;font-size:9px;font-weight:700;color:#f8fafc;white-space:nowrap;display:flex;align-items:center;gap:4px;box-shadow:0 3px 12px rgba(0,0,0,.3);transform:translateX(-50%);position:relative; }
.map-2d-marker-tag::after { content:'';position:absolute;bottom:-5px;left:50%;transform:translateX(-50%);border-left:5px solid transparent;border-right:5px solid transparent;border-top:5px solid rgba(15,23,42,.78); }
.map-2d-marker-flag { font-size:12px;line-height:1; }
.map-2d-marker-count { color:#38bdf8;font-weight:800;font-size:10px; }
.map-2d-pulse { position:absolute;left:0;top:0; pointer-events:none; z-index:4; will-change:transform; }
.map-2d-pulse-dot { width:8px;height:8px;border-radius:50%;position:absolute;transform:translate(-50%,-50%); }
.map-2d-pulse-ring { width:20px;height:20px;border-radius:50%;position:absolute;transform:translate(-50%,-50%);border:2px solid;opacity:0;animation:pulse2d 2s ease-out infinite; }
@keyframes pulse2d { 0%{transform:translate(-50%,-50%) scale(0.5);opacity:0.7} 100%{transform:translate(-50%,-50%) scale(2.5);opacity:0} }
.map-2d-connection { position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:3; }
.map-2d-fadein { opacity:0; animation: m2dFadeIn 0.5s ease forwards; }
@keyframes m2dFadeIn { from{opacity:0;transform:translate(var(--mx),var(--my)) translateY(6px)} to{opacity:1;transform:translate(var(--mx),var(--my))} }

/* Enhanced 2D hover effect */
#worldMapContainer path[data-code] { transition:fill .3s ease,fill-opacity .3s ease,stroke .3s ease,stroke-width .3s ease,filter .3s ease !important; }
#worldMapContainer path.map-2d-hovered { filter:brightness(1.15) drop-shadow(0 0 8px currentColor) !important; stroke-width:1px !important; }

/* Globe label tags */
.globe-label-tag { background:rgba(15,23,42,.78);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.18);border-radius:10px;padding:4px 10px;font-size:10px;font-weight:700;color:#f8fafc;white-space:nowrap;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,.35);line-height:1.4;display:flex;align-items:center;gap:5px; }
.globe-label-tag .globe-label-flag { font-size:14px;line-height:1;filter:drop-shadow(0 1px 2px rgba(0,0,0,.3)); }
.globe-label-tag .globe-label-name { font-weight:700; }
.globe-label-tag .globe-label-count { color:#38bdf8;font-size:11px;font-weight:800;margin-left:2px; }

/* Polygon hover glow animation */
@keyframes polyGlow { 0%,100%{opacity:0.6} 50%{opacity:1} }

/* ====== Immersive FS Detail Panel ====== */
.fs-detail-backdrop {
    display: none;
    position: absolute; inset: 0;
    background: rgba(2,6,23,0.6);
    backdrop-filter: blur(4px);
    z-index: 1200;
}
@keyframes fsBackdropIn { from{opacity:0} to{opacity:1} }
.fs-detail-backdrop.active { display: block; animation: fsBackdropIn 0.3s ease; }

.fs-detail-panel {
    display: none;
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -48%) scale(0.96);
    width: min(92vw, 1080px);
    max-height: 82vh;
    background: rgba(10, 18, 40, 0.82);
    backdrop-filter: blur(28px) saturate(180%);
    -webkit-backdrop-filter: blur(28px) saturate(180%);
    border: 1px solid rgba(56, 189, 248, 0.18);
    border-radius: 20px;
    z-index: 1210;
    flex-direction: column;
    box-shadow: 0 32px 80px rgba(0,0,0,0.7), 0 0 0 1px rgba(56,189,248,0.08) inset;
    overflow: hidden;
    opacity: 0;
    transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.16,1,0.3,1);
}
.fs-detail-panel.active {
    display: flex;
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
    animation: fsPanelIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes fsPanelIn {
    from { opacity:0; transform:translate(-50%,-46%) scale(0.95); }
    to   { opacity:1; transform:translate(-50%,-50%) scale(1); }
}
.fs-detail-inner { display:flex; flex-direction:column; width:100%; height:100%; overflow:hidden; }
.fs-detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-shrink: 0;
    background: linear-gradient(135deg, rgba(59,130,246,0.18) 0%, rgba(139,92,246,0.12) 100%);
}
.fs-detail-header-left { display:flex; align-items:center; gap:14px; }
.fs-detail-badge {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 20px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(59,130,246,0.4);
}
.fs-detail-title { font-size: 1.15rem; font-weight: 800; color: #f1f5f9; line-height: 1.2; }
.fs-detail-subtitle { font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }
.fs-detail-close {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    color: #94a3b8;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
    flex-shrink: 0;
}
.fs-detail-close:hover { background:rgba(239,68,68,0.25); color:#fff; border-color:rgba(239,68,68,0.5); }

.fs-detail-search-bar {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 24px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}
.fs-detail-search-wrap {
    flex: 1;
    position: relative;
}
.fs-detail-search-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #64748b; font-size: 0.85rem;
}
.fs-detail-search-input {
    width: 100%;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 9px 14px 9px 36px;
    color: #f1f5f9;
    font-size: 0.85rem;
    outline: none;
    transition: all 0.2s ease;
}
.fs-detail-search-input::placeholder { color: #475569; }
.fs-detail-search-input:focus { border-color: rgba(56,189,248,0.5); background: rgba(56,189,248,0.06); box-shadow: 0 0 0 3px rgba(56,189,248,0.1); }
.fs-detail-info { font-size: 0.72rem; color: #64748b; white-space: nowrap; }

.fs-detail-table-wrap { flex: 1; overflow-y: auto; }
.fs-detail-table-wrap::-webkit-scrollbar { width: 4px; }
.fs-detail-table-wrap::-webkit-scrollbar-track { background: transparent; }
.fs-detail-table-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

.fs-detail-table { width: 100%; border-collapse: collapse; }
.fs-detail-table thead th {
    position: sticky; top: 0;
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase;
    padding: 10px 14px; white-space: nowrap;
}
.fs-detail-thead-main th {
    background: rgba(15,23,42,0.95);
    color: #94a3b8;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    z-index: 2;
}
.fs-detail-thead-main th:first-child { padding-left: 24px; }
.fs-detail-thead-main th:last-child { padding-right: 24px; }
.fs-detail-thead-filter th {
    background: rgba(15,23,42,0.9);
    padding: 6px 8px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    top: 37px;
    z-index: 1;
}
.fs-detail-thead-filter th:first-child { padding-left: 24px; }
.fs-detail-thead-filter th:last-child { padding-right: 24px; }
.fs-detail-col-search {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 7px;
    padding: 5px 8px;
    color: #f1f5f9;
    font-size: 0.72rem;
    outline: none;
    transition: all 0.2s ease;
}
.fs-detail-col-search::placeholder { color: #475569; }
.fs-detail-col-search:focus { border-color: rgba(56,189,248,0.4); background: rgba(56,189,248,0.06); }

.fs-detail-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.15s ease;
}
.fs-detail-table tbody tr:hover { background: rgba(56,189,248,0.07); }
.fs-detail-table tbody td {
    padding: 10px 14px;
    font-size: 0.82rem;
    color: #e2e8f0;
    vertical-align: middle;
}
.fs-detail-table tbody td:first-child { padding-left: 24px; color: #38bdf8; font-weight: 700; font-family: monospace; font-size:0.8rem; }
.fs-detail-table tbody td:last-child { padding-right: 24px; }
.fs-detail-table tbody tr:nth-child(even) { background: rgba(255,255,255,0.02); }
.fs-detail-table .fs-empty-row td { text-align: center; color: #475569; padding: 40px; }

.fs-detail-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 24px;
    border-top: 1px solid rgba(255,255,255,0.08);
    flex-shrink: 0;
    background: rgba(15,23,42,0.5);
}
.fs-detail-pagination { display:flex; gap:6px; align-items: center; flex-wrap: wrap; }
.fs-detail-page-btn {
    min-width: 32px; height: 32px; padding: 0 10px;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    color: #94a3b8;
    font-size: 0.78rem; font-weight: 600;
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.2s ease;
}
.fs-detail-page-btn:hover:not(:disabled) { background:rgba(56,189,248,0.15); color:#38bdf8; border-color:rgba(56,189,248,0.3); }
.fs-detail-page-btn.active { background: linear-gradient(135deg,#3b82f6,#8b5cf6); color:#fff; border-color:transparent; }
.fs-detail-page-btn:disabled { opacity:0.35; cursor:not-allowed; }
.fs-detail-page-size { display:flex; align-items:center; gap:8px; font-size:0.75rem; color:#64748b; }
.fs-detail-select {
    background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
    color: #f1f5f9; border-radius: 7px; padding: 4px 8px; font-size:0.78rem; outline:none;
}
/* ====== End Immersive FS Detail Panel ====== */

/* Immersive Fullscreen Mode */
body.immersive-fs-active {
    overflow: hidden !important;
}
body.immersive-fs-active .map-card-header {
    position: relative;
    z-index: 1060;
    transition: padding-left 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
body.immersive-fs-active.fs-sidebar-open .map-card-header {
    padding-left: 430px !important;
}
body.immersive-fs-active.fs-sidebar-open #mapSummaryBadge {
    left: 430px !important;
}
.dashboard-fs-wrapper:fullscreen,
.dashboard-fs-wrapper:-webkit-full-screen {
    padding: 0 !important;
    background: #020617;
    overflow: hidden !important;
    display: flex;
    flex-direction: column;
}
.dashboard-fs-wrapper:fullscreen #mapCard,
.dashboard-fs-wrapper:-webkit-full-screen #mapCard {
    height: 100vh !important;
    border-radius: 0 !important;
    background: transparent !important;
    display: flex !important;
    flex-direction: column !important;
}
.dashboard-fs-wrapper:fullscreen #mapCard .card-body,
.dashboard-fs-wrapper:-webkit-full-screen #mapCard .card-body {
    flex: 1 !important;
    height: auto !important;
    min-height: 0 !important;
    position: relative;
}
.dashboard-fs-wrapper:fullscreen #worldMapContainer,
.dashboard-fs-wrapper:fullscreen #globeVizContainer,
.dashboard-fs-wrapper:fullscreen #mapContainersWrapper,
.dashboard-fs-wrapper:-webkit-full-screen #worldMapContainer,
.dashboard-fs-wrapper:-webkit-full-screen #globeVizContainer,
.dashboard-fs-wrapper:-webkit-full-screen #mapContainersWrapper {
    height: 100% !important;
    min-height: 100% !important;
    border-radius: 0 !important;
}
.dashboard-fs-wrapper:fullscreen .fs-hide-in-immersive,
.dashboard-fs-wrapper:-webkit-full-screen .fs-hide-in-immersive {
    display: none !important;
}

body.immersive-fs-active #mapCountrySidebar {
    right: 10px !important;
}
body.immersive-fs-active .country-sidebar-toggle {
    right: 240px !important;
}
body.immersive-fs-active #mapCountrySidebar.closed {
    right: -240px !important;
}
body.immersive-fs-active .country-sidebar-toggle.closed {
    right: 0 !important;
}

/* Glassmorphism Floating Sidebar */
.fs-analytics-sidebar {
    position: fixed;
    top: 0;
    left: -420px;
    width: 400px;
    height: 100vh;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1050;
    transition: left 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    padding: 24px;
    color: #f8fafc;
    overflow-y: auto;
    box-shadow: 10px 0 40px rgba(0,0,0,0.6);
}
.fs-analytics-sidebar.active { left: 0; }
.fs-analytics-sidebar::-webkit-scrollbar { width: 4px; }
.fs-analytics-sidebar::-webkit-scrollbar-track { background: transparent; }
.fs-analytics-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

/* The Toggle Button (Floating on left edge) */
.fs-sidebar-toggle {
    position: fixed;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    width: 44px;
    height: 90px;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    border-left: none;
    border-radius: 0 12px 12px 0;
    color: #38bdf8;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1049;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 4px 0 20px rgba(0,0,0,0.4);
}
.dashboard-fs-wrapper:fullscreen .fs-sidebar-toggle,
.dashboard-fs-wrapper:-webkit-full-screen .fs-sidebar-toggle {
    display: flex;
}
.fs-sidebar-toggle:hover {
    background: rgba(15, 23, 42, 0.85);
    color: #fff;
    width: 52px;
}
.fs-sidebar-toggle.active {
    left: 400px;
    background: rgba(15, 23, 42, 0.9);
}
.fs-sidebar-toggle.active i { transform: rotate(180deg); }

/* Glassmorphism Cards inside Sidebar */
.fs-glass-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
}
.fs-glass-card-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fs-glass-card canvas {
    max-height: 200px;
}
.fs-sidebar-close {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
    flex-shrink: 0;
}
.fs-sidebar-close:hover {
    background: rgba(255,255,255,0.2);
}
</style>

<div class="fs-sidebar-toggle" id="fsSidebarToggle" onclick="toggleFsSidebar()" title="Tampilkan Analisis">
    <i class="bi bi-chevron-left fs-5"></i>
</div>
<div class="fs-analytics-sidebar" id="fsAnalyticsSidebar">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold" style="letter-spacing:1px;text-transform:uppercase;font-size:0.95rem;"><i class="bi bi-bar-chart-fill me-2 text-info"></i>Analisis Data</h5>
        <button class="fs-sidebar-close" onclick="toggleFsSidebar()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="fs-glass-card">
        <div class="fs-glass-card-title"><i class="bi bi-globe-americas"></i>Distribusi Negara Asal</div>
        <canvas id="fsNegaraChart"></canvas>
    </div>
    <div class="fs-glass-card">
        <div class="fs-glass-card-title"><i class="bi bi-diagram-3-fill"></i>Distribusi Kepengurusan</div>
        <canvas id="fsKepengurusanChart"></canvas>
    </div>
    <div class="fs-glass-card">
        <div class="fs-glass-card-title"><i class="bi bi-person-vcard"></i>Kewarganegaraan</div>
        <canvas id="fsKewarganegaraanChart"></canvas>
    </div>
    <div class="fs-glass-card">
        <div class="fs-glass-card-title"><i class="bi bi-building-fill"></i>Distribusi Pondok</div>
        <canvas id="fsPondokChart"></canvas>
    </div>
    <div class="fs-glass-card">
        <div class="fs-glass-card-title"><i class="bi bi-mortarboard-fill"></i>Distribusi Angkatan</div>
        <canvas id="fsAngkatanChart"></canvas>
    </div>
</div>

<!-- Immersive Fullscreen Detail Panel (INSIDE the fs-wrapper for correct stacking) -->
<div id="fsDetailPanel" class="fs-detail-panel">
    <div class="fs-detail-inner">
        <div class="fs-detail-header">
            <div class="fs-detail-header-left">
                <div class="fs-detail-badge" id="fsDetailBadge">Detail</div>
                <div>
                    <div class="fs-detail-title" id="fsDetailTitle">Detail Santri</div>
                    <div class="fs-detail-subtitle" id="fsDetailSubtitle"></div>
                </div>
            </div>
            <button class="fs-detail-close" onclick="closeFsDetail()" title="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="fs-detail-search-bar">
            <div class="fs-detail-search-wrap">
                <i class="bi bi-search fs-detail-search-icon"></i>
                <input type="text" id="fsDetailGlobalSearch" class="fs-detail-search-input" placeholder="Cari di semua kolom...">
            </div>
            <div class="fs-detail-info" id="fsDetailInfo">Menampilkan 0 data</div>
        </div>
        <div class="fs-detail-table-wrap">
            <table id="fsDetailTable" class="fs-detail-table">
                <thead>
                    <tr class="fs-detail-thead-main">
                        <th>Stambuk</th><th>Nama</th><th>Kelas</th>
                        <th>Negara</th><th>Kewarganegaraan</th><th>Pondok</th><th>Pengurus</th>
                    </tr>
                    <tr class="fs-detail-thead-filter" id="fsDetailFilterRow">
                        <th><input type="text" class="fs-detail-col-search" data-col="0" placeholder="Filter..."></th>
                        <th><input type="text" class="fs-detail-col-search" data-col="1" placeholder="Filter..."></th>
                        <th><input type="text" class="fs-detail-col-search" data-col="2" placeholder="Filter..."></th>
                        <th><input type="text" class="fs-detail-col-search" data-col="3" placeholder="Filter..."></th>
                        <th><input type="text" class="fs-detail-col-search" data-col="4" placeholder="Filter..."></th>
                        <th><input type="text" class="fs-detail-col-search" data-col="5" placeholder="Filter..."></th>
                        <th><input type="text" class="fs-detail-col-search" data-col="6" placeholder="Filter..."></th>
                    </tr>
                </thead>
                <tbody id="fsDetailTbody"></tbody>
            </table>
        </div>
        <div class="fs-detail-footer">
            <div class="fs-detail-pagination" id="fsDetailPagination"></div>
            <div class="fs-detail-page-size">
                <span>Tampil</span>
                <select id="fsDetailPageSize" class="fs-detail-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="-1">Semua</option>
                </select>
                <span>baris</span>
            </div>
        </div>
    </div>
</div>
<!-- FS Detail Backdrop -->
<div id="fsDetailBackdrop" class="fs-detail-backdrop" onclick="closeFsDetail()"></div>

</div>

<!-- Modal Detail Santri -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 px-4 py-3">
                <div class="d-flex flex-column">
                    <h5 class="modal-title fw-bold mb-0" id="detailModalTitle">Detail Santri</h5>
                    <small id="detailModalSubtitle" class="text-white-50 mt-1"></small>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" style="box-shadow: none;"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="detailTable" class="table table-striped table-hover align-middle w-100 mb-0" style="font-size:0.85rem">
                                <thead class="table-light shadow-sm sticky-top">
                                    <tr>
                                        <th class="ps-3">Stambuk</th><th>Nama</th><th>Kelas</th>
                                        <th>Negara</th><th>Kewarganegaraan</th><th>Pondok</th><th class="pe-3">Pengurus</th>
                                    </tr>
                                    <tr class="table-secondary">
                                        <th class="ps-3 py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="0" placeholder="Cari..."></th>
                                        <th class="py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="1" placeholder="Cari..."></th>
                                        <th class="py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="2" placeholder="Cari..."></th>
                                        <th class="py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="3" placeholder="Cari..."></th>
                                        <th class="py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="4" placeholder="Cari..."></th>
                                        <th class="py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="5" placeholder="Cari..."></th>
                                        <th class="pe-3 py-2"><input type="text" class="form-control form-control-sm border-0 shadow-sm dt-search-detail" data-col="6" placeholder="Cari..."></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Includes -->
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/dataTables.bootstrap5.min.css">
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/chart.umd.min.js"></script>

<!-- Libraries Peta & Globe Lokal -->
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery-jvectormap.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery-jvectormap-world-mill-en.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/three.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/globe.gl.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/topojson-client.min.js"></script>
<!-- GeoJSON country polygons for 3D globe -->
<script>
let _geoJsonCountries = null;
let _geoJsonFeatures = [];
fetch('<?= ASSET_URL ?>/assets/offline/json/countries-110m.json')
    .then(r => r.json())
    .then(topo => {
        _geoJsonCountries = topo;
        if (typeof topojson !== 'undefined') {
            _geoJsonFeatures = topojson.feature(topo, topo.objects.countries).features;
        }
    })
    .catch(e => console.warn('GeoJSON load optional:', e));

// ISO alpha-2 to ISO numeric-3 mapping (used by world-atlas TopoJSON)
const _iso2toNum = {
    'AF':'004','AL':'008','DZ':'012','AS':'016','AD':'020','AO':'024','AI':'660','AQ':'010','AG':'028','AR':'032','AM':'051','AW':'533','AU':'036','AT':'040','AZ':'031','BS':'044','BH':'048','BD':'050','BB':'052','BY':'112','BE':'056','BZ':'084','BJ':'204','BM':'060','BT':'064','BO':'068','BA':'070','BW':'072','BV':'074','BR':'076','IO':'086','BN':'096','BG':'100','BF':'854','BI':'108','CV':'132','KH':'116','CM':'120','CA':'124','KY':'136','CF':'140','TD':'148','CL':'152','CN':'156','CX':'162','CC':'166','CO':'170','KM':'174','CG':'178','CD':'180','CK':'184','CR':'188','HR':'191','CU':'192','CY':'196','CZ':'203','DK':'208','DJ':'262','DM':'212','DO':'214','EC':'218','EG':'818','SV':'222','GQ':'226','ER':'232','EE':'233','SZ':'748','ET':'231','FK':'238','FO':'234','FJ':'242','FI':'246','FR':'250','GF':'254','PF':'258','TF':'260','GA':'266','GM':'270','GE':'268','DE':'276','GH':'288','GI':'292','GR':'300','GL':'304','GD':'308','GP':'312','GU':'316','GT':'320','GN':'324','GW':'624','GY':'328','HT':'332','HM':'334','VA':'336','HN':'340','HK':'344','HU':'348','IS':'352','IN':'356','ID':'360','IR':'364','IQ':'368','IE':'372','IL':'376','IT':'380','JM':'388','JP':'392','JO':'400','KZ':'398','KE':'404','KI':'296','KP':'408','KR':'410','KW':'414','KG':'417','LA':'418','LV':'428','LB':'422','LS':'426','LR':'430','LY':'434','LI':'438','LT':'440','LU':'442','MO':'446','MG':'450','MW':'454','MY':'458','MV':'462','ML':'466','MT':'470','MH':'584','MQ':'474','MR':'478','MU':'480','YT':'175','MX':'484','FM':'583','MD':'498','MC':'492','MN':'496','ME':'499','MS':'500','MA':'504','MZ':'508','MM':'104','NA':'516','NR':'520','NP':'524','NL':'528','NC':'540','NZ':'554','NI':'558','NE':'562','NG':'566','NU':'570','NF':'574','MK':'807','MP':'580','NO':'578','OM':'512','PK':'586','PW':'585','PS':'275','PA':'591','PG':'598','PY':'600','PE':'604','PH':'608','PN':'612','PL':'616','PT':'620','PR':'630','QA':'634','RE':'638','RO':'642','RU':'643','RW':'646','BL':'652','SH':'654','KN':'659','LC':'662','MF':'663','PM':'666','VC':'670','WS':'882','SM':'214','ST':'678','SA':'682','SN':'686','RS':'688','SC':'690','SL':'694','SG':'702','SK':'703','SI':'705','SB':'090','SO':'706','ZA':'710','GS':'239','SS':'728','ES':'724','LK':'144','SD':'729','SR':'740','SJ':'744','SE':'752','CH':'756','SY':'760','TW':'158','TJ':'762','TZ':'834','TH':'764','TL':'626','TG':'768','TK':'772','TO':'776','TT':'780','TN':'788','TR':'792','TM':'795','TC':'796','TV':'798','UG':'800','UA':'804','AE':'784','GB':'826','US':'840','UM':'581','UY':'858','UZ':'860','VU':'548','VE':'862','VN':'704','VG':'092','VI':'850','WF':'876','EH':'732','YE':'887','ZM':'894','ZW':'716'
};
</script>

<script>
const rawData = <?= $santrisJson ?>;
const santriAsli = <?= $santriAsli ?? 0 ?>;
const santriPindahan = <?= $santriPindahan ?? 0 ?>;
const showAsliPindahan = <?= isset($showAsliPindahan) && $showAsliPindahan ? 'true' : 'false' ?>;
const myKepengurusanStr = <?= json_encode($myKep ?? '') ?>;

function parseKelas(raw) {
    if (!raw) return 'Lainnya';
    const s = raw.toString().toLowerCase();
    if (s.includes('penerimaan')) return 'Capel Penerimaan';
    if (s.includes('persiapan')) return 'Capel Persiapan';
    if (s.includes('alumni')) return 'Alumni';
    if (s.includes('pengabdian')) return 'Pengabdian';
    if (s.includes('1') && s.includes('int')) return 'Kelas 1 Int';
    if (s.includes('3') && s.includes('int')) return 'Kelas 3 Int';
    if (s.includes('1')) return 'Kelas 1';
    if (s.includes('2')) return 'Kelas 2';
    if (s.includes('3')) return 'Kelas 3';
    if (s.includes('4')) return 'Kelas 4';
    if (s.includes('5')) return 'Kelas 5';
    if (s.includes('6')) return 'Kelas 6';
    return 'Lainnya';
}

const classOrder = {
    'Kelas 1': 1, 'Kelas 1 Int': 2, 'Kelas 2': 3,
    'Kelas 3': 4, 'Kelas 3 Int': 5, 'Kelas 4': 6,
    'Kelas 5': 7, 'Kelas 6': 8, 'Capel Penerimaan': 9,
    'Capel Persiapan': 10, 'Pengabdian': 11,
    'Alumni': 12, 'Lainnya': 13
};

rawData.forEach(r => {
    r.angkatan = parseKelas(r.kelas);
});

function groupBy(data, field) {
    const counts = {};
    data.forEach(item => {
        let val = item[field];
        if (!val || val.trim() === '') val = '-';
        else val = val.trim().split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
        counts[val] = (counts[val] || 0) + 1;
    });
    
    // Khusus angkatan, urutkan berdasarkan classOrder
    if (field === 'angkatan') {
        const sorted = Object.keys(counts).map(k => ({label: k, count: counts[k]}))
            .sort((a, b) => (classOrder[a.label] || 99) - (classOrder[b.label] || 99));
        return { labels: sorted.map(i => i.label), data: sorted.map(i => i.count) };
    }

    const sorted = Object.keys(counts).map(k => ({label: k, count: counts[k]})).sort((a,b) => b.count - a.count);
    return { labels: sorted.map(i => i.label), data: sorted.map(i => i.count) };
}

const negaraData = groupBy(rawData, 'negara');
const kewarganegaraanData = groupBy(rawData, 'kewarganegaraan');
const kepengurusanData = groupBy(rawData, 'kepengurusan');
const angkatanData = groupBy(rawData, 'angkatan');
const pondokData = groupBy(rawData, 'pondok');
const colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#f97316','#06b6d4','#84cc16','#ec4899','#6366f1','#14b8a6','#f43f5e'];

let detailTable, detailModal;

document.addEventListener('DOMContentLoaded', function() {
    detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    function initTable() {
        if (detailTable) return;
        detailTable = $('#detailTable').DataTable({
            deferRender: true,
            pageLength: 10, lengthMenu: [[10,25,50,-1],[10,25,50,"Semua"]],
            language: { search:"Cari:", lengthMenu:"Tampil _MENU_ baris", info:"Menampilkan _START_ s/d _END_ dari _TOTAL_ data", infoEmpty:"Tidak ada data", zeroRecords:"Data tidak ditemukan", paginate:{first:"Awal",last:"Akhir",next:"Lanjut",previous:"Kembali"} },
            orderCellsTop: true,
            columns: [{data:'stambuk'},{data:'nama'},{data:'kelas'},{data:'negara'},{data:'kewarganegaraan'},{data:'pondok'},{data:'kepengurusan'}],
            initComplete: function() { $('.dt-search-detail').on('keyup change clear', function() { detailTable.column($(this).data('col')).search(this.value).draw(); }); }
        });
    }
    function showDetail(title, subtitle, filterFn) {
        initTable();
        const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
        if (isFs) {
            showFsDetail(title, subtitle, rawData.filter(filterFn));
        } else {
            document.getElementById('detailModalTitle').textContent = title;
            document.getElementById('detailModalSubtitle').textContent = subtitle;
            detailTable.clear().rows.add(rawData.filter(filterFn)).draw();
            detailModal.show();
        }
    }
    function createChart(ctxId, type, aggregated, titlePrefix, filterField) {
        new Chart(document.getElementById(ctxId), {
            type, data: { labels: aggregated.labels, datasets: [{ data: aggregated.data, backgroundColor: colors, borderWidth: type==='pie'||type==='doughnut'?2:0, borderRadius: type==='bar'?4:0 }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:type==='pie'||type==='doughnut',position:'right'}},
                onClick:(e,ae)=>{ if(ae.length>0){const idx=ae[0].index;const label=aggregated.labels[idx];showDetail(`${titlePrefix}: ${label}`,`Menampilkan ${aggregated.data[idx]} santri`,row=>{let val=row[filterField];if(!val||val.trim()==='')val='-';else val=val.trim().split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1).toLowerCase()).join(' ');return val===label;});} }
            }
        });
    }
    createChart('negaraChart','pie',negaraData,'Negara Asal','negara');
    createChart('kewarganegaraanChart','doughnut',kewarganegaraanData,'Kewarganegaraan','kewarganegaraan');
    createChart('kepengurusanChart','bar',kepengurusanData,'Kepengurusan','kepengurusan');
    createChart('angkatanChart','bar',angkatanData,'Angkatan','angkatan');
    createChart('pondokChart','pie',pondokData,'Pondok','pondok');
    
    // Create Mini Fullscreen versions
    function createFsChart(ctxId, type, aggregated) {
        const el = document.getElementById(ctxId);
        if(!el) return;
        new Chart(el, {
            type, data: { labels: aggregated.labels, datasets: [{ data: aggregated.data, backgroundColor: colors, borderWidth: 0, borderRadius: type==='bar'?4:0 }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:type==='pie'||type==='doughnut',position:'right',labels:{color:'#cbd5e1',font:{size:10}}}}, scales: type==='bar'?{x:{ticks:{color:'#cbd5e1',font:{size:10}},grid:{color:'rgba(255,255,255,0.05)'}},y:{ticks:{color:'#cbd5e1',font:{size:10}},grid:{color:'rgba(255,255,255,0.05)'}}}:{} }
        });
    }
    createFsChart('fsNegaraChart','doughnut',negaraData);
    createFsChart('fsKepengurusanChart','bar',kepengurusanData);
    createFsChart('fsPondokChart','pie',pondokData);
    createFsChart('fsKewarganegaraanChart','doughnut',kewarganegaraanData);
    createFsChart('fsAngkatanChart','bar',angkatanData);

    window.toggleFsSidebar = function() {
        const sb = document.getElementById('fsAnalyticsSidebar');
        const tog = document.getElementById('fsSidebarToggle');
        if(sb && tog) {
            sb.classList.toggle('active');
            tog.classList.toggle('active');
            document.body.classList.toggle('fs-sidebar-open');
        }
    };

    window.toggleCountrySidebar = function() {
        const sb = document.getElementById('mapCountrySidebar');
        const tog = document.getElementById('countrySidebarToggle');
        if(sb && tog) {
            sb.classList.toggle('closed');
            tog.classList.toggle('closed');
        }
    };

    // ── Fullscreen detail panel engine ───────────────────────────────────────
    let _fsDetailData = [];
    let _fsDetailFiltered = [];
    let _fsDetailPage = 1;
    let _fsDetailPageSize = 10;
    let _fsDetailColFilters = ['','','','','','',''];
    let _fsDetailGlobal = '';

    // Country name to flag emoji helper
    const countryToCode = {
        'malaysia':'my','thailand':'th','filipina':'ph','philippines':'ph','singapura':'sg','singapore':'sg',
        'brunei':'bn','brunei darussalam':'bn','myanmar':'mm','kamboja':'kh','cambodia':'kh','laos':'la',
        'vietnam':'vn','timor leste':'tl','indonesia':'id','china':'cn','tiongkok':'cn','jepang':'jp','japan':'jp',
        'korea selatan':'kr','south korea':'kr','korea utara':'kp','taiwan':'tw','india':'in','pakistan':'pk',
        'bangladesh':'bd','sri lanka':'lk','nepal':'np','afganistan':'af','afghanistan':'af','iran':'ir',
        'irak':'iq','iraq':'iq','arab saudi':'sa','saudi arabia':'sa','uni emirat arab':'ae',
        'qatar':'qa','bahrain':'bh','kuwait':'kw','oman':'om','yaman':'ye','yemen':'ye','turki':'tr','turkey':'tr'
    };
    const countryToCode2 = {
        'mesir':'eg','egypt':'eg','maroko':'ma','morocco':'ma','nigeria':'ng','kenya':'ke','afrika selatan':'za',
        'south africa':'za','australia':'au','selandia baru':'nz','new zealand':'nz','amerika serikat':'us',
        'united states':'us','kanada':'ca','canada':'ca','inggris':'gb','united kingdom':'gb','jerman':'de',
        'germany':'de','perancis':'fr','france':'fr','italia':'it','italy':'it','spanyol':'es','spain':'es',
        'belanda':'nl','netherlands':'nl','rusia':'ru','russia':'ru','brasil':'br','brazil':'br',
        'meksiko':'mx','mexico':'mx','argentina':'ar','kolombia':'co','colombia':'co','palestina':'ps','palestine':'ps',
        'suriah':'sy','syria':'sy','lebanon':'lb','yordania':'jo','jordan':'jo','uzbekistan':'uz',
        'kazakhstan':'kz','tajikistan':'tj','turkmenistan':'tm','kyrgyzstan':'kg','mongolia':'mn',
        'somalia':'so','sudan':'sd','libya':'ly','tunisia':'tn','aljazair':'dz','algeria':'dz',
        'ghana':'gh','senegal':'sn','pantai gading':'ci','ivory coast':'ci','mali':'ml','benin':'bj',
        'guinea':'gn','sierra leone':'sl','chad':'td','niger':'ne','kamerun':'cm','cameroon':'cm'
    };

    function getFlagHtml(name) {
        if (!name) return '🏳️';
        const code = countryToCode[name.toLowerCase()] || countryToCode2[name.toLowerCase()];
        if (!code || code.length !== 2) return '🏳️';
        const offset = 127397;
        const emoji = String.fromCodePoint(...[...code.toUpperCase()].map(c => c.charCodeAt() + offset));
        return `<span style="font-size:16px; margin-right:4px;">${emoji}</span>`;
    }

    function _fsApplyFilters() {
        _fsDetailFiltered = _fsDetailData.filter(row => {
            const cells = [row.stambuk, row.nama, row.kelas, row.negara, row.kewarganegaraan, row.pondok, row.kepengurusan];
            // Global search
            if (_fsDetailGlobal) {
                const g = _fsDetailGlobal.toLowerCase();
                if (!cells.some(c => (c||'').toLowerCase().includes(g))) return false;
            }
            // Per-column filters
            for (let i = 0; i < _fsDetailColFilters.length; i++) {
                const f = _fsDetailColFilters[i];
                if (f && !(cells[i]||'').toLowerCase().includes(f.toLowerCase())) return false;
            }
            return true;
        });
        _fsDetailPage = 1;
        _fsRender();
    }

    function _fsRender() {
        const tbody = document.getElementById('fsDetailTbody');
        const info = document.getElementById('fsDetailInfo');
        if (!tbody) return;
        const total = _fsDetailFiltered.length;
        const pageSize = _fsDetailPageSize === -1 ? total : _fsDetailPageSize;
        const totalPages = pageSize > 0 ? Math.ceil(total / pageSize) : 1;
        if (_fsDetailPage > totalPages) _fsDetailPage = Math.max(1, totalPages);
        const start = pageSize > 0 ? (_fsDetailPage - 1) * pageSize : 0;
        const slice = pageSize > 0 ? _fsDetailFiltered.slice(start, start + pageSize) : _fsDetailFiltered;

        if (info) info.textContent = total > 0
            ? `Menampilkan ${pageSize > 0 ? start+1 : 1}–${Math.min(start + slice.length, total)} dari ${total} data`
            : 'Tidak ada data';

        if (slice.length === 0) {
            tbody.innerHTML = '<tr class="fs-empty-row"><td colspan="7"><i class="bi bi-search me-2"></i>Data tidak ditemukan</td></tr>';
        } else {
            tbody.innerHTML = slice.map(r =>
                `<tr>
                    <td>${r.stambuk||'-'}</td>
                    <td>${r.nama||'-'}</td>
                    <td>${r.kelas||'-'}</td>
                    <td>${r.negara||'-'}</td>
                    <td>${r.kewarganegaraan||'-'}</td>
                    <td>${r.pondok||'-'}</td>
                    <td>${r.kepengurusan||'-'}</td>
                </tr>`
            ).join('');
        }

        // Pagination
        const pg = document.getElementById('fsDetailPagination');
        if (!pg) return;
        let html = `<button class="fs-detail-page-btn" onclick="_fsPrevPage()" ${_fsDetailPage<=1?'disabled':''}>
                        <i class="bi bi-chevron-left"></i>
                    </button>`;
        const maxBtns = 5;
        let pStart = Math.max(1, _fsDetailPage - Math.floor(maxBtns/2));
        let pEnd   = Math.min(totalPages, pStart + maxBtns - 1);
        if (pEnd - pStart < maxBtns - 1) pStart = Math.max(1, pEnd - maxBtns + 1);
        if (pStart > 1) html += `<button class="fs-detail-page-btn" onclick="_fsGoPage(1)">1</button>${pStart>2?'<span style="color:#475569;padding:0 4px">…</span>':''}`;
        for (let p = pStart; p <= pEnd; p++)
            html += `<button class="fs-detail-page-btn${p===_fsDetailPage?' active':''}" onclick="_fsGoPage(${p})">${p}</button>`;
        if (pEnd < totalPages) html += `${pEnd<totalPages-1?'<span style="color:#475569;padding:0 4px">…</span>':''}<button class="fs-detail-page-btn" onclick="_fsGoPage(${totalPages})">${totalPages}</button>`;
        html += `<button class="fs-detail-page-btn" onclick="_fsNextPage()" ${_fsDetailPage>=totalPages?'disabled':''}>
                     <i class="bi bi-chevron-right"></i>
                 </button>`;
        pg.innerHTML = html;
    }

    window._fsGoPage = p => { _fsDetailPage = p; _fsRender(); };
    window._fsPrevPage = () => { if(_fsDetailPage>1){_fsDetailPage--;_fsRender();} };
    window._fsNextPage = () => { const ps=_fsDetailPageSize===-1?_fsDetailFiltered.length:_fsDetailPageSize; const tp=Math.ceil(_fsDetailFiltered.length/ps); if(_fsDetailPage<tp){_fsDetailPage++;_fsRender();} };

    window.showFsDetail = function(title, subtitle, data) {
        _fsDetailData = data;
        _fsDetailFiltered = [...data];
        _fsDetailPage = 1;
        _fsDetailPageSize = parseInt(document.getElementById('fsDetailPageSize').value) || 10;
        _fsDetailColFilters = ['','','','','','',''];
        _fsDetailGlobal = '';

        // Reset inputs
        document.querySelectorAll('.fs-detail-col-search').forEach(i => i.value = '');
        const gs = document.getElementById('fsDetailGlobalSearch');
        if (gs) gs.value = '';

        // Update header
        document.getElementById('fsDetailTitle').textContent = title;
        document.getElementById('fsDetailSubtitle').textContent = subtitle;

        // Parse badge keyword from title
        const parts = title.split(':');
        const badgeEl = document.getElementById('fsDetailBadge');
        if (title.toLowerCase().startsWith('negara:') && parts.length > 1) {
            const countryName = parts[1].trim();
            badgeEl.innerHTML = _nameToFlag(countryName);
            badgeEl.style.background = 'transparent';
            badgeEl.style.boxShadow = 'none';
            badgeEl.style.padding = '0';
            const img = badgeEl.querySelector('img');
            if (img) {
                img.style.width = '36px';
                img.style.height = '27px';
                img.style.borderRadius = '4px';
                img.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
            }
        } else {
            badgeEl.textContent = parts.length > 1 ? parts[0].trim() : 'Detail';
            badgeEl.style.background = '';
            badgeEl.style.boxShadow = '';
            badgeEl.style.padding = '';
        }

        _fsRender();

        const panel = document.getElementById('fsDetailPanel');
        const backdrop = document.getElementById('fsDetailBackdrop');
        panel.classList.add('active');
        backdrop.classList.add('active');
    };

    window.closeFsDetail = function() {
        const panel = document.getElementById('fsDetailPanel');
        const backdrop = document.getElementById('fsDetailBackdrop');
        panel.classList.remove('active');
        backdrop.classList.remove('active');
    };

    // Wire up search and pagination controls (elements already in DOM at this point)
    (function _wireUpFsDetailControls() {
        const gs = document.getElementById('fsDetailGlobalSearch');
        if (gs) gs.addEventListener('input', function() {
            _fsDetailGlobal = this.value;
            _fsApplyFilters();
        });
        document.querySelectorAll('.fs-detail-col-search').forEach(inp => {
            inp.addEventListener('input', function() {
                _fsDetailColFilters[parseInt(this.dataset.col)] = this.value;
                _fsApplyFilters();
            });
        });
        const ps = document.getElementById('fsDetailPageSize');
        if (ps) ps.addEventListener('change', function() {
            _fsDetailPageSize = parseInt(this.value) || 10;
            if (_fsDetailPageSize === -1) _fsDetailPageSize = -1;
            _fsDetailPage = 1;
            _fsRender();
        });
    })();

    if (showAsliPindahan) {
        const ctx = document.getElementById('asliPindahanChart');
        if (ctx) new Chart(ctx, { type:'pie', data:{labels:['Santri Asli','Santri Pindahan'],datasets:[{data:[santriAsli,santriPindahan],backgroundColor:['#10b981','#ef4444'],borderWidth:2}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},
                onClick:(e,ae)=>{ if(ae.length>0){const idx=ae[0].index;const isAsli=idx===0;showDetail(`Domisili: ${isAsli?'Santri Asli':'Santri Pindahan'}`,`Menampilkan ${isAsli?santriAsli:santriPindahan} santri`,row=>{const rk=row.kepengurusan?row.kepengurusan.trim():'';const ira=(rk.toLowerCase()===myKepengurusanStr.toLowerCase());return isAsli?ira:!ira;});} }
            }
        });
    }
});
</script>

<!-- jVectorMap World Map & Globe 3D -->
<script>
(function(){
    const c2c = {
        'malaysia':'MY','thailand':'TH','filipina':'PH','philippines':'PH','singapura':'SG','singapore':'SG',
        'brunei':'BN','brunei darussalam':'BN','myanmar':'MM','kamboja':'KH','cambodia':'KH','laos':'LA',
        'vietnam':'VN','timor leste':'TL','indonesia':'ID','china':'CN','tiongkok':'CN','jepang':'JP','japan':'JP',
        'korea selatan':'KR','south korea':'KR','korea utara':'KP','taiwan':'TW','mongolia':'MN',
        'india':'IN','pakistan':'PK','bangladesh':'BD','sri lanka':'LK','nepal':'NP','bhutan':'BT',
        'maladewa':'MV','maldives':'MV','afghanistan':'AF',
        'arab saudi':'SA','saudi arabia':'SA','uni emirat arab':'AE','uae':'AE','qatar':'QA','bahrain':'BH',
        'kuwait':'KW','oman':'OM','yaman':'YE','yemen':'YE','irak':'IQ','iraq':'IQ','iran':'IR',
        'turki':'TR','turkey':'TR','turkiye':'TR','palestina':'PS','palestine':'PS','suriah':'SY','syria':'SY',
        'yordania':'JO','jordan':'JO','lebanon':'LB','libanon':'LB',
        'mesir':'EG','egypt':'EG','maroko':'MA','morocco':'MA','tunisia':'TN','aljazair':'DZ','algeria':'DZ',
        'libya':'LY','sudan':'SD','somalia':'SO','kenya':'KE','tanzania':'TZ','nigeria':'NG','ghana':'GH',
        'senegal':'SN','mali':'ML','kamerun':'CM','cameroon':'CM','pantai gading':'CI','ivory coast':'CI',
        'afrika selatan':'ZA','south africa':'ZA','ethiopia':'ET','mozambik':'MZ','mozambique':'MZ',
        'madagaskar':'MG','madagascar':'MG','niger':'NE','chad':'TD','burkina faso':'BF','guinea':'GN',
        'sierra leone':'SL','gambia':'GM','mauritania':'MR','komoro':'KM','comoros':'KM','djibouti':'DJ',
        'uzbekistan':'UZ','kazakhstan':'KZ','kyrgyzstan':'KG','tajikistan':'TJ','turkmenistan':'TM','azerbaijan':'AZ',
        'inggris':'GB','united kingdom':'GB','uk':'GB','england':'GB','perancis':'FR','france':'FR',
        'jerman':'DE','germany':'DE','belanda':'NL','netherlands':'NL','spanyol':'ES','spain':'ES',
        'italia':'IT','italy':'IT','rusia':'RU','russia':'RU','swedia':'SE','sweden':'SE',
        'norwegia':'NO','norway':'NO','bosnia':'BA','bosnia dan herzegovina':'BA','albania':'AL','kosovo':'XK','makedonia':'MK',
        'amerika serikat':'US','united states':'US','usa':'US','kanada':'CA','canada':'CA','meksiko':'MX','mexico':'MX',
        'brazil':'BR','brasil':'BR','argentina':'AR','kolombia':'CO','suriname':'SR','guyana':'GY','trinidad dan tobago':'TT',
        'australia':'AU','selandia baru':'NZ','new zealand':'NZ','fiji':'FJ','papua nugini':'PG','papua new guinea':'PG'
    };
    const cc = ['#2563eb','#059669','#d97706','#dc2626','#7c3aed','#ea580c','#0891b2','#16a34a','#db2777','#4f46e5','#0d9488','#c026d3','#ca8a04','#0284c7','#15803d'];

    const nc = {}; let ts = 0;
    rawData.forEach(s => {
        let n = s.negara ? s.negara.trim() : ''; if(!n) return;
        n = n.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1).toLowerCase()).join(' ');
        nc[n]=(nc[n]||0)+1; ts++;
    });
    const sc = Object.entries(nc).sort((a,b)=>b[1]-a[1]).map(([name,count],i)=>({
        name,count,color:cc[i%cc.length],code:c2c[name.toLowerCase()]||null,
        percent:ts>0?((count/ts)*100).toFixed(1):'0'
    }));
    const mv = {}; sc.forEach(c=>{if(c.code)mv[c.code]=c.count;});

    document.getElementById('mapTotalCountries').textContent = sc.length;
    document.getElementById('mapTotalSantri').textContent = ts.toLocaleString('id-ID');

    // Sidebar
    const lc = document.getElementById('mapCountryList');
    const mx = sc.length>0?sc[0].count:1;
    sc.forEach(c=>{
        const bw=((c.count/mx)*100).toFixed(0);
        const flag = codeToFlag(c.code);
        const it=document.createElement('div'); it.className='map-country-item';
        it.innerHTML=`<div class="map-country-dot" style="color:${c.color};background:${c.color};"></div>
        <div style="flex:1;min-width:0;">
            <div class="d-flex justify-content-between align-items-baseline">
                <span class="map-country-name" style="font-size:.75rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><span style="font-size:13px;margin-right:3px;">${flag}</span>${c.name}</span>
                <span style="font-size:.8rem;font-weight:800;color:${c.color};margin-left:6px;">${c.count}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="map-country-bar" style="flex:1;"><div class="map-country-bar-fill" style="width:${bw}%;background:${c.color};"></div></div>
                <span class="map-country-pct" style="font-size:.6rem;margin-left:6px;">${c.percent}%</span>
            </div>
        </div>`;
        it.addEventListener('click',()=>{if(window.showDetailFromMap)showDetailFromMap(c.name);});
        lc.appendChild(it);
    });

    // Smart focus: weighted centroid of data countries
    const cen={'MY':[3.5,109.5],'TH':[15.8,100.9],'PH':[12.8,121.7],'SG':[1.3,103.8],'BN':[4.5,114.7],'MM':[21.9,96],'KH':[12.5,104.9],'LA':[19.8,102.4],'VN':[14,108.2],'ID':[-7.86,111.46],'CN':[35.8,104.1],'JP':[36.2,138.2],'KR':[35.9,127.7],'IN':[20.5,78.9],'PK':[30.3,69.3],'BD':[23.6,90.3],'LK':[7.8,80.7],'NP':[28.3,84.1],'AF':[33.9,67.7],'SA':[23.8,45],'AE':[23.4,53.8],'QA':[25.3,51.1],'KW':[29.3,47.4],'OM':[21.4,55.9],'YE':[15.5,48.5],'IQ':[33.2,43.6],'IR':[32.4,53.6],'TR':[38.9,35.2],'PS':[31.9,35.2],'SY':[34.8,39],'JO':[30.5,36.2],'LB':[33.8,35.8],'EG':[26.8,30.8],'MA':[31.7,-7],'TN':[33.8,9.5],'DZ':[28,1.6],'LY':[26.3,17.2],'SD':[12.8,30.2],'SO':[5.1,46.1],'KE':[-0.02,37.9],'TZ':[-6.3,34.8],'NG':[9.08,8.6],'GH':[7.9,-1],'SN':[14.4,-14.4],'ML':[17.5,-4],'CM':[7.3,12.3],'ZA':[-30.5,22.9],'ET':[9.1,40.4],'UZ':[41.3,64.5],'KZ':[48,66.9],'GB':[55.3,-3.4],'FR':[46.2,2.2],'DE':[51.1,10.4],'NL':[52.1,5.2],'RU':[61.5,105.3],'US':[37,-95.7],'CA':[56.1,-106.3],'BR':[-14.2,-51.9],'AU':[-25.2,133.7]};
    let sLat=0,sLng=0,tw=0;
    sc.forEach(c=>{if(c.code&&cen[c.code]){sLat+=cen[c.code][0]*c.count;sLng+=cen[c.code][1]*c.count;tw+=c.count;}});
    const fLat=tw>0?sLat/tw:5, fLng=tw>0?sLng/tw:100;
    const df = { x:(fLng+180)/360, y:(90-fLat)/180, scale:3.2 };

    let ct='light', mo=null;
    const th={
        light:{rf:'#cbd5e1',ro:0.55,rs:'#94a3b8',rw:0.3,rso:0.5},
        dark:{rf:'#1e3a5f',ro:0.6,rs:'#2d5a8e',rw:0.3,rso:0.4}
    };

    function applyColors(theme){
        if(!mo)return; const t=th[theme];
        Object.keys(mo.regions).forEach(code=>{
            const r=mo.regions[code]; if(!r)return;
            const nd=r.element.shape.node;
            const co=sc.find(c=>c.code===code);
            if(co){
                nd.style.fill=co.color;
                nd.style.fillOpacity='0.75';
                nd.style.stroke=theme==='light'?'rgba(255,255,255,0.6)':'rgba(255,255,255,0.25)';
                nd.style.strokeWidth='0.5';
                nd.style.strokeOpacity='1';
            } else {
                nd.style.fill=t.rf;
                nd.style.fillOpacity=String(t.ro);
                nd.style.stroke=t.rs;
                nd.style.strokeWidth=String(t.rw);
                nd.style.strokeOpacity=String(t.rso);
            }
        });
    }

    // ISO code -> Flag Image
    function codeToFlag(code) {
        if (!code || code.length !== 2) return '🏳️';
        const offset = 127397;
        const emoji = String.fromCodePoint(...[...code.toUpperCase()].map(c => c.charCodeAt() + offset));
        return `<span style="font-size:16px;">${emoji}</span>`;
    }

    function showTooltip(code, evt) {
        const tt = document.getElementById('mapTooltip');
        const co = sc.find(c => c.code === code);
        if (co) {
            document.getElementById('mapTooltipFlag').innerHTML = codeToFlag(code);
            document.getElementById('mapTooltipName').textContent = co.name;
            document.getElementById('mapTooltipCode').textContent = code;
            document.getElementById('mapTooltipCount').textContent = co.count;
            document.getElementById('mapTooltipPercent').textContent = co.percent + '% dari total';
            document.getElementById('mapTooltipBarFill').style.width = co.percent + '%';
            document.getElementById('mapTooltipBarFill').style.background = co.color;
        } else {
            const rn = mo ? mo.getRegionName(code) : code;
            document.getElementById('mapTooltipFlag').innerHTML = codeToFlag(code);
            document.getElementById('mapTooltipName').textContent = rn || code;
            document.getElementById('mapTooltipCode').textContent = code;
            document.getElementById('mapTooltipCount').textContent = '0';
            document.getElementById('mapTooltipPercent').textContent = 'Tidak ada santri';
            document.getElementById('mapTooltipBarFill').style.width = '0%';
        }
        tt.style.display = 'block';
        positionTooltip(evt);
    }

    function hideTooltip() {
        document.getElementById('mapTooltip').style.display = 'none';
    }

    function positionTooltip(e) {
        const tt = document.getElementById('mapTooltip');
        if (tt.style.display !== 'block') return;
        const tw2 = tt.offsetWidth, th2 = tt.offsetHeight;
        const ww = window.innerWidth, wh = window.innerHeight;
        let tx = e.clientX + 20, ty = e.clientY - 15;
        if (tx + tw2 + 10 > ww) tx = e.clientX - tw2 - 20;
        if (ty + th2 + 10 > wh) ty = e.clientY - th2 - 15;
        if (ty < 5) ty = 5;
        tt.style.left = tx + 'px';
        tt.style.top = ty + 'px';
    }

    window.toggleMapTheme=function(){
        const card=document.getElementById('mapCard');
        ct=ct==='light'?'dark':'light';
        card.className=card.className.replace(/map-card-(light|dark)/,'map-card-'+ct);
        document.getElementById('mapThemeIcon').className=ct==='light'?'bi bi-moon-stars-fill me-1':'bi bi-sun-fill me-1';
        document.getElementById('mapThemeLabel').textContent=ct==='light'?'Dark':'Light';
        applyColors(ct);
        
        // Update globe appearance if active
        if(mo3D) {
            // Re-apply the current style (Vector or Satellite) using the new theme color
            window.toggleGlobeStyle(isGlobeSatellite);
            
            // Adjust atmosphere for theme
            if(ct==='light') {
                mo3D.atmosphereColor('#38bdf8');
                mo3D.atmosphereAltitude(0.15);
            } else {
                mo3D.atmosphereColor('#0284c7');
                mo3D.atmosphereAltitude(0.15);
            }
        }
    };
    
    // 3D Globe Implementation
    let mo3D = null;
    let is3DMode = false;
    
    window.toggleMap3D=function() {
        const btnIcon = document.getElementById('map3DIcon');
        const btnLabel = document.getElementById('map3DLabel');
        const styleBtn = document.getElementById('mapStyleToggle');
        const c2D = document.getElementById('worldMapContainer');
        const c3D = document.getElementById('globeVizContainer');
        
        if (!is3DMode) {
            is3DMode = true;
            btnIcon.className = 'bi bi-map me-1';
            btnLabel.textContent = '2D Mode';
            styleBtn.classList.remove('d-none'); // Show style toggle in 3D
            c2D.style.opacity = '0';
            c2D.style.pointerEvents = 'none';
            setTimeout(() => {
                c3D.style.pointerEvents = 'auto';
                c3D.style.zIndex = '3';
                c2D.style.zIndex = '1';
                if (!mo3D) init3DGlobe();
                const width = c3D.offsetWidth;
                const height = c3D.offsetHeight;
                if(mo3D) mo3D.width(width).height(height);
                setTimeout(() => c3D.style.opacity = '1', 50);
            }, 300);
        } else {
            is3DMode = false;
            btnIcon.className = 'bi bi-box me-1';
            btnLabel.textContent = '3D Mode';
            styleBtn.classList.add('d-none'); // Hide style toggle in 2D
            c3D.style.opacity = '0';
            c3D.style.pointerEvents = 'none';
            setTimeout(() => {
                c2D.style.pointerEvents = 'auto';
                c2D.style.zIndex = '3';
                c3D.style.zIndex = '1';
                if (mo) {
                    // Slight position update since layout might have changed
                    setTimeout(() => {
                        if(typeof window.update2DPositions === 'function') window.update2DPositions();
                    }, 50);
                }
                setTimeout(() => c2D.style.opacity = '1', 50);
            }, 300);
        }
    };

    // --- Globe Style Toggle (Vector vs Satellite) ---
    let isGlobeSatellite = false;
    window.toggleGlobeStyle = function(forceMode) {
        if (!mo3D) return;
        const targetMode = typeof forceMode === 'boolean' ? forceMode : !isGlobeSatellite;
        if (isGlobeSatellite === targetMode) return;
        
        isGlobeSatellite = targetMode;
        const icon = document.getElementById('mapStyleIcon');
        const label = document.getElementById('mapStyleLabel');
        const isLight = ct === 'light';
        const globeMat = mo3D.globeMaterial();
        
        if (isGlobeSatellite) {
            icon.className = 'bi bi-vector-pen me-1';
            label.textContent = 'Vector';
            // Switch to Satellite
            mo3D.globeImageUrl(isLight ? '<?= ASSET_URL ?>/assets/offline/img/earth-blue-marble.jpg' : '<?= ASSET_URL ?>/assets/offline/img/earth-night.jpg');
            globeMat.color.set('#ffffff');
            globeMat.roughness = 0.8;
            globeMat.metalness = 0.0;
        } else {
            icon.className = 'bi bi-image me-1';
            label.textContent = 'Satelit';
            // Switch back to Vector using a 1x1 solid color pixel image
            // This prevents the globe.gl internal mesh from turning invisible when map is null.
            const blankCanvas = document.createElement('canvas');
            blankCanvas.width = 1; blankCanvas.height = 1;
            const ctx = blankCanvas.getContext('2d');
            ctx.fillStyle = isLight ? '#13385b' : '#0a1220';
            ctx.fillRect(0,0,1,1);
            mo3D.globeImageUrl(blankCanvas.toDataURL());
            
            globeMat.color.set('#ffffff'); // Keep white so texture color isn't multiplied
            globeMat.roughness = 0.6;
            globeMat.metalness = 0.1;
        }

        // Re-apply polygon colors since rules changed by passing new function references 
        // to bypass globe.gl's internal reference equality check.
        if (window.getPolyCapColor) {
            mo3D.polygonCapColor(f => window.getPolyCapColor(f))
                .polygonSideColor(f => window.getPolySideColor(f))
                .polygonStrokeColor(f => window.getPolyStroke(f));
        }
    };

    // --- Fullscreen Map Logic ---
    window.toggleMapFullscreen = function() {
        const wrapper = document.getElementById('dashboardFullscreenWrapper');
        const icon = document.getElementById('mapFsIcon');
        
        // Match theme for fullscreen bg
        if (ct === 'dark') {
            wrapper.classList.add('dark-mode-fs');
        } else {
            wrapper.classList.remove('dark-mode-fs');
        }

        if (!document.fullscreenElement) {
            if (wrapper.requestFullscreen) {
                wrapper.requestFullscreen();
            } else if (wrapper.webkitRequestFullscreen) { /* Safari */
                wrapper.webkitRequestFullscreen();
            } else if (wrapper.msRequestFullscreen) { /* IE11 */
                wrapper.msRequestFullscreen();
            }
            icon.className = 'bi bi-fullscreen-exit me-1';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) { /* Safari */
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) { /* IE11 */
                document.msExitFullscreen();
            }
            icon.className = 'bi bi-arrows-angle-expand me-1';
        }
    };

    // --- Resize & Fullscreen Redraw Handlers ---
    function handleMapResize() {
        if (is3DMode && mo3D) {
            const c3D = document.getElementById('globeVizContainer');
            if (c3D && c3D.offsetWidth > 0) {
                mo3D.width(c3D.offsetWidth).height(c3D.offsetHeight);
            }
        } else if (!is3DMode && mo) {
            mo.updateSize();
            if(typeof window.update2DPositions === 'function') window.update2DPositions();
        }
    }
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleMapResize, 100);
    });
    function handleFullscreenChange() {
        if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
            document.body.classList.add('immersive-fs-active');
            const sb = document.getElementById('fsAnalyticsSidebar');
            const tog = document.getElementById('fsSidebarToggle');
            if(sb && tog) {
                setTimeout(() => { 
                    sb.classList.add('active'); 
                    tog.classList.add('active'); 
                    document.body.classList.add('fs-sidebar-open');
                }, 600);
            }
        } else {
            document.body.classList.remove('immersive-fs-active');
            document.body.classList.remove('fs-sidebar-open');
            const sb = document.getElementById('fsAnalyticsSidebar');
            const tog = document.getElementById('fsSidebarToggle');
            if(sb && tog) { sb.classList.remove('active'); tog.classList.remove('active'); }
        }
        setTimeout(handleMapResize, 100);
        setTimeout(handleMapResize, 350);
        setTimeout(handleMapResize, 600);
        setTimeout(handleMapResize, 1000);
    }
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('msfullscreenchange', handleFullscreenChange);

    function init3DGlobe() {
        // ========================================
        // PREMIUM 3D GLOBE - Country Polygons,
        // Flag Labels, Hover Territory Highlight
        // ========================================

        // --- Arc data: animated dashed lines from each country to Indonesia ---
        const arcsData = sc.filter(c => c.code && c.code !== 'ID' && cen[c.code] && cen['ID']).map((c, i) => ({
            startLat: cen[c.code][0],
            startLng: cen[c.code][1],
            endLat: cen['ID'][0],
            endLng: cen['ID'][1],
            color: [c.color, '#f97316'],
            name: c.name,
            count: c.count,
            dashOffset: i * 3
        }));

        // --- Point data with altitude bars ---
        const maxCount = sc.length > 0 ? sc[0].count : 1;
        const pointsData = sc.filter(c => c.code && cen[c.code]).map(c => ({
            lat: cen[c.code][0],
            lng: cen[c.code][1],
            color: c.color,
            name: c.name,
            code: c.code,
            count: c.count,
            altitude: 0.02 + (c.count / maxCount) * 0.25
        }));

        // --- Label data with flag emojis ---
        const labelsData = sc.filter(c => c.code && cen[c.code]).map(c => ({
            lat: cen[c.code][0],
            lng: cen[c.code][1],
            name: c.name,
            code: c.code,
            count: c.count,
            color: c.color,
            flag: codeToFlag(c.code),
            altitude: 0.04 + (c.count / maxCount) * 0.27
        }));

        // --- Prepare polygon features with country code tagging ---
        let polygonFeatures = [];
        if (_geoJsonFeatures && _geoJsonFeatures.length > 0) {
            // Tag each polygon feature with the matching ISO alpha-2 code
            const numToAlpha = {};
            Object.entries(_iso2toNum).forEach(([a2, num]) => { numToAlpha[num] = a2; });
            polygonFeatures = _geoJsonFeatures.map(f => {
                const numId = f.id || (f.properties && f.properties.iso_n3);
                const alpha2 = numToAlpha[String(numId)] || numToAlpha[String(numId).padStart(3,'0')] || null;
                const dataCountry = alpha2 ? sc.find(c => c.code === alpha2) : null;
                return { ...f, _alpha2: alpha2, _dataCountry: dataCountry };
            });
        }

        let hoverPoint = null;
        let hoverAlpha2 = null;
        const isLight = ct === 'light';

        // --- Helper: get polygon colors based on hover state & map style ---
        window.getPolyCapColor = function(feat) {
            const a2 = feat._alpha2;
            const dc = feat._dataCountry;
            const isLightTheme = ct === 'light';
            
            if (isGlobeSatellite) {
                // In satellite mode, use transparent/tinted polygons so the earth image shows
                if (hoverAlpha2 && a2 === hoverAlpha2 && dc) return dc.color + 'aa';
                if (hoverAlpha2 && a2 === hoverAlpha2) return isLightTheme ? 'rgba(255,255,255,0.1)' : 'rgba(255,255,255,0.08)';
                if (dc) return dc.color + '22';
                return isLightTheme ? 'rgba(100,130,180,0.04)' : 'rgba(100,130,180,0.03)';
            } else {
                // In vector mode, use solid flat colors
                if (hoverAlpha2 && a2 === hoverAlpha2 && dc) return dc.color + 'dd';
                if (hoverAlpha2 && a2 === hoverAlpha2) return isLightTheme ? '#f1f5f9' : '#475569';
                if (dc) return isLightTheme ? '#e2e8f0' : '#334155';
                return isLightTheme ? '#cbd5e1' : '#1e293b';
            }
        };
        window.getPolySideColor = function(feat) {
            const dc = feat._dataCountry;
            const isLightTheme = ct === 'light';
            if (isGlobeSatellite) {
                if (hoverAlpha2 && feat._alpha2 === hoverAlpha2 && dc) return dc.color + '55';
                return 'rgba(100,130,180,0.03)';
            } else {
                if (hoverAlpha2 && feat._alpha2 === hoverAlpha2 && dc) return dc.color + 'aa';
                return isLightTheme ? '#94a3b8' : '#0f172a';
            }
        };
        window.getPolyAlt = function(feat) {
            if (hoverAlpha2 && feat._alpha2 === hoverAlpha2) {
                return feat._dataCountry ? 0.025 : 0.015; 
            }
            // Elevate slightly to prevent clipping with the globe surface
            return 0.012;
        };
        window.getPolyStroke = function(feat) {
            const isLightTheme = ct === 'light';
            
            // Highlight stroke on hover (both for data countries and empty countries)
            if (hoverAlpha2 && feat._alpha2 === hoverAlpha2) {
                return feat._dataCountry ? feat._dataCountry.color : (isLightTheme ? '#64748b' : '#94a3b8');
            }
            if (isGlobeSatellite) {
                return isLightTheme ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.15)';
            } else {
                if (feat._dataCountry) {
                    // Countries with data have white-ish borders
                    return isLightTheme ? 'rgba(255,255,255,0.7)' : 'rgba(255,255,255,0.3)';
                } else {
                    // Empty countries use semi-transparent black/white for strong contrast
                    return isLightTheme ? 'rgba(0,0,0,0.25)' : 'rgba(255,255,255,0.15)';
                }
            }
        };

        mo3D = Globe()
            (document.getElementById('globeVizContainer'))
            .globeImageUrl(isGlobeSatellite ? (ct==='light' ? '<?= ASSET_URL ?>/assets/offline/img/earth-blue-marble.jpg' : '<?= ASSET_URL ?>/assets/offline/img/earth-night.jpg') : null)
            .showGlobe(true)
            .backgroundColor('rgba(0,0,0,0)')
            .showAtmosphere(true)
            .atmosphereColor(ct === 'light' ? '#38bdf8' : '#0284c7')
            .atmosphereAltitude(0.15)

            // --- GeoJSON Polygons (country shapes with solid flat colors) ---
            .polygonsData(polygonFeatures)
            .polygonCapColor(window.getPolyCapColor)
            .polygonSideColor(window.getPolySideColor)
            .polygonStrokeColor(window.getPolyStroke)
            .polygonAltitude(window.getPolyAlt)
            .polygonsTransitionDuration(300)

            // --- Animated Arcs (dashed, glowing trails) ---
            .arcsData(arcsData)
            .arcColor('color')
            .arcDashLength(0.4)
            .arcDashGap(0.15)
            .arcDashAnimateTime(d => 2200 + d.count * 20)
            .arcStroke(d => 0.3 + (d.count / maxCount) * 0.8)
            .arcAltitudeAutoScale(0.4)

            // --- Ripple rings at each country ---
            .ringsData(pointsData)
            .ringColor(d => {
                const c = d.color;
                return t => {
                    const opacity = 1 - t;
                    return c + Math.round(opacity * 255).toString(16).padStart(2,'0');
                };
            })
            .ringMaxRadius(d => d.count > 10 ? 4 : 2)
            .ringPropagationSpeed(2)
            .ringRepeatPeriod(d => 800 + (1 - d.count/maxCount) * 1200)

            // --- 3D Hex columns (data bars rising from globe) ---
            .hexPolygonsData(pointsData)
            .hexPolygonResolution(3)
            .hexPolygonMargin(0.6)
            .hexPolygonColor(d => d.color + 'cc')
            .hexPolygonAltitude(d => d === hoverPoint ? d.altitude * 1.5 : d.altitude)
            .hexPolygonCurvatureResolution(0)

            // --- Floating Labels with Flag Emojis ---
            .htmlElementsData(labelsData)
            .htmlElement(d => {
                const el = document.createElement('div');
                el.className = 'globe-label-tag';
                el.innerHTML = `<span class="globe-label-flag">${d.flag}</span><span class="globe-label-name">${d.name}</span><span class="globe-label-count">${d.count}</span>`;
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.4s ease, transform 0.3s ease';
                setTimeout(() => { el.style.opacity = '0.92'; }, 400 + Math.random() * 600);
                return el;
            })
            .htmlAltitude(0.01)

            // --- Point markers (subtle glow dots) ---
            .pointsData(pointsData)
            .pointColor('color')
            .pointAltitude(d => d === hoverPoint ? 0.12 : 0.015)
            .pointRadius(d => d === hoverPoint ? 1.2 : 0.5 + (d.count / maxCount) * 0.5)
            .pointsMerge(false)
            .onPointHover(point => {
                hoverPoint = point;
                hoverAlpha2 = point ? point.code : null;

                // Update point visuals
                mo3D.pointAltitude(d => d === hoverPoint ? 0.12 : 0.015)
                    .pointRadius(d => d === hoverPoint ? 1.2 : 0.5 + (d.count / maxCount) * 0.5)
                    .hexPolygonAltitude(d => d === hoverPoint ? d.altitude * 1.5 : d.altitude);

                // Trigger polygon re-render for hover highlight
                mo3D.polygonCapColor(f => window.getPolyCapColor(f))
                    .polygonSideColor(f => window.getPolySideColor(f))
                    .polygonAltitude(f => window.getPolyAlt(f))
                    .polygonStrokeColor(f => window.getPolyStroke(f));

                if (point) {
                    document.getElementById('globeVizContainer').style.cursor = 'pointer';
                    showTooltip(point.code, {clientX: lastMouseX, clientY: lastMouseY});
                } else {
                    document.getElementById('globeVizContainer').style.cursor = 'default';
                    hideTooltip();
                }
            })
            .onPointClick(point => {
                hideTooltip();
                if(window.showDetailFromMap) showDetailFromMap(point.name);
            })

            // --- Also handle polygon hover for countries ---
            .onPolygonHover(polygon => {
                const prevHover = hoverAlpha2;
                if (polygon && polygon._alpha2) {
                    hoverAlpha2 = polygon._alpha2;
                    document.getElementById('globeVizContainer').style.cursor = 'pointer';
                    showTooltip(polygon._alpha2, {clientX: lastMouseX, clientY: lastMouseY});
                } else if (!hoverPoint) {
                    hoverAlpha2 = null;
                    document.getElementById('globeVizContainer').style.cursor = 'default';
                    hideTooltip();
                }
                if (prevHover !== hoverAlpha2) {
                    mo3D.polygonCapColor(f => window.getPolyCapColor(f))
                        .polygonSideColor(f => window.getPolySideColor(f))
                        .polygonAltitude(f => window.getPolyAlt(f))
                        .polygonStrokeColor(f => window.getPolyStroke(f));
                }
            })
            .onPolygonClick(polygon => {
                if (polygon && polygon._alpha2) {
                    hideTooltip();
                    // Fallback to polygon.properties.ADMIN or mo for empty countries
                    const name = polygon._dataCountry ? polygon._dataCountry.name : (mo ? mo.getRegionName(polygon._alpha2) : (polygon.properties ? polygon.properties.ADMIN : polygon._alpha2));
                    if(window.showDetailFromMap) showDetailFromMap(name);
                }
            });

        // --- Auto-rotate disabled (manual rotation only) ---
        mo3D.controls().autoRotate = false;
        mo3D.controls().enableDamping = true;
        mo3D.controls().dampingFactor = 0.08;
        mo3D.controls().minDistance = 180;
        mo3D.controls().maxDistance = 600;

        // Custom tooltip positioning
        let lastMouseX = window.innerWidth/2;
        let lastMouseY = window.innerHeight/2;
        const renderer = mo3D.renderer();
        renderer.domElement.addEventListener('mousemove', (e) => {
            lastMouseX = e.clientX;
            lastMouseY = e.clientY;
            const tt = document.getElementById('mapTooltip');
            if(tt.style.display==='block') positionTooltip(e);
        });

        // --- Enhanced globe material ---
        const scene = mo3D.scene();
        const globeMat = mo3D.globeMaterial();
        // Set ocean color
        globeMat.color.set(isLight ? '#13385b' : '#0a1220');
        // Make the ocean slightly shiny to look premium
        globeMat.roughness = 0.6;
        globeMat.metalness = 0.1;

        // Add a subtle inner glow sphere
        try {
            const glowGeo = new THREE.SphereGeometry(100.5, 64, 64);
            const glowMat = new THREE.MeshBasicMaterial({
                color: isLight ? 0x38bdf8 : 0x0284c7,
                transparent: true,
                opacity: 0.03,
                side: THREE.BackSide
            });
            const glowMesh = new THREE.Mesh(glowGeo, glowMat);
            scene.add(glowMesh);
        } catch(e) { /* ignore if THREE not available */ }

        // Set initial point of view
        mo3D.pointOfView({ lat: fLat, lng: fLng, altitude: 2.2 }, 1200);
    }

    window.resetMapZoom=function(){
        if(is3DMode && mo3D) {
            mo3D.pointOfView({ lat: fLat, lng: fLng, altitude: 2.2 }, 1000);
        } else if(mo) {
            mo.setFocus({x:df.x,y:df.y,scale:df.scale,animate:true});
        }
    };

    $(document).ready(function(){
        const $c=$('#worldMapContainer'), t=th[ct];
        $c.vectorMap({
            map:'world_mill_en',backgroundColor:'transparent',zoomOnScroll:true,zoomMin:1,zoomMax:12,
            focusOn:df,
            regionStyle:{
                initial:{fill:t.rf,'fill-opacity':t.ro,stroke:t.rs,'stroke-width':t.rw,'stroke-opacity':t.rso},
                hover:{'fill-opacity':0.9,cursor:'pointer'}
            },
            series:{regions:[{values:mv,scale:['#bfdbfe','#2563eb'],normalizeFunction:'polynomial',min:0,max:mx}]},
            onRegionTipShow:function(e){ e.preventDefault(); },
            onRegionOver:function(e,code){
                const nd = mo.regions[code] && mo.regions[code].element.shape.node;
                if(nd) nd.classList.add('map-2d-hovered');
            },
            onRegionOut:function(e,code){
                const nd = mo.regions[code] && mo.regions[code].element.shape.node;
                if(nd) nd.classList.remove('map-2d-hovered');
            },
            onRegionClick:function(e,code){const co=sc.find(c=>c.code===code);if(co&&window.showDetailFromMap)showDetailFromMap(co.name);}
        });
        mo=$c.vectorMap('get','mapObject');
        applyColors(ct);

        // =============================================
        // SMOOTH 2D OVERLAYS - Create once, update pos
        // =============================================
        const _overlayEls = { markers: [], pulses: [], paths: [], svgEl: null, created: false };

        function create2DOverlays() {
            const container = document.getElementById('worldMapContainer');
            if(!container || !mo || _overlayEls.created) return;
            _overlayEls.created = true;

            // --- SVG container for connection lines ---
            const svgNS = 'http://www.w3.org/2000/svg';
            const svgEl = document.createElementNS(svgNS, 'svg');
            svgEl.setAttribute('class', 'map-2d-connection');
            svgEl.style.position = 'absolute';
            svgEl.style.top = '0'; svgEl.style.left = '0';
            svgEl.style.overflow = 'visible';
            _overlayEls.svgEl = svgEl;

            // Gradient defs
            const defs = document.createElementNS(svgNS, 'defs');
            sc.forEach((c, i) => {
                if(!c.code || c.code === 'ID' || !cen[c.code]) return;
                const grad = document.createElementNS(svgNS, 'linearGradient');
                grad.id = 'lineGrad'+i;
                const s1 = document.createElementNS(svgNS, 'stop');
                s1.setAttribute('offset','0%'); s1.setAttribute('stop-color', c.color); s1.setAttribute('stop-opacity','0.8');
                const s2 = document.createElementNS(svgNS, 'stop');
                s2.setAttribute('offset','100%'); s2.setAttribute('stop-color', '#f97316'); s2.setAttribute('stop-opacity','0.6');
                grad.appendChild(s1); grad.appendChild(s2); defs.appendChild(grad);
            });
            svgEl.appendChild(defs);

            // Create SVG paths (one per arc)
            sc.forEach((c, i) => {
                if(!c.code || c.code === 'ID' || !cen[c.code]) return;
                const path = document.createElementNS(svgNS, 'path');
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', `url(#lineGrad${i})`);
                path.setAttribute('stroke-width', String(0.8 + (c.count/mx)*1.5));
                path.setAttribute('stroke-dasharray', '6 3');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('opacity', '0.5');
                // Dash animation via CSS
                const speed = 3 + (i % 5) * 0.5;
                path.style.animation = `dashFlow ${speed}s linear infinite`;
                svgEl.appendChild(path);
                _overlayEls.paths.push({ el: path, code: c.code, countRatio: c.count/mx });
            });
            container.appendChild(svgEl);

            // Inject dash animation style once
            if(!document.getElementById('dashAnimStyle2')) {
                const st = document.createElement('style');
                st.id = 'dashAnimStyle2';
                st.textContent = '@keyframes dashFlow{0%{stroke-dashoffset:0}100%{stroke-dashoffset:-18}}';
                document.head.appendChild(st);
            }

            // --- Create marker + pulse elements for each country ---
            sc.forEach((c, idx) => {
                if(!c.code || !cen[c.code]) return;
                const flag = codeToFlag(c.code);

                // Pulsing dot
                const pulse = document.createElement('div');
                pulse.className = 'map-2d-pulse';
                pulse.innerHTML = `<div class="map-2d-pulse-dot" style="background:${c.color};box-shadow:0 0 6px ${c.color};"></div><div class="map-2d-pulse-ring" style="border-color:${c.color};animation-delay:${idx*0.3}s"></div>`;
                container.appendChild(pulse);

                // Label marker
                const marker = document.createElement('div');
                marker.className = 'map-2d-marker map-2d-fadein';
                marker.style.animationDelay = (idx * 0.08) + 's';
                marker.innerHTML = `<div class="map-2d-marker-tag">
                    <span class="map-2d-marker-flag">${flag}</span>
                    <span>${c.name}</span>
                    <span class="map-2d-marker-count">${c.count}</span>
                </div>`;
                container.appendChild(marker);

                _overlayEls.markers.push({ el: marker, code: c.code });
                _overlayEls.pulses.push({ el: pulse, code: c.code });
            });

            // Initial position update
            window.update2DPositions();
        }

        window.update2DPositions = function() {
            if(!mo || !_overlayEls.created) return;
            const container = document.getElementById('worldMapContainer');
            // Abort if container is hidden or hasn't calculated layout yet, to prevent jumping to 0,0
            if(!container || container.offsetWidth === 0) return;
            const w = container.offsetWidth, h = container.offsetHeight;

            // Update SVG viewBox
            if(_overlayEls.svgEl) {
                _overlayEls.svgEl.setAttribute('viewBox', `0 0 ${w} ${h}`);
                _overlayEls.svgEl.style.width = w + 'px';
                _overlayEls.svgEl.style.height = h + 'px';
            }

            // Indonesia destination point
            const idPt = mo.latLngToPoint(cen['ID'][0], cen['ID'][1]);

            // Update SVG paths
            _overlayEls.paths.forEach(p => {
                const pt = mo.latLngToPoint(cen[p.code][0], cen[p.code][1]);
                if(!pt || !idPt) { p.el.setAttribute('d',''); return; }
                const midX = (pt.x + idPt.x) / 2;
                const midY = Math.min(pt.y, idPt.y) - 40 - p.countRatio * 30;
                p.el.setAttribute('d', `M${pt.x},${pt.y} Q${midX},${midY} ${idPt.x},${idPt.y}`);
            });

            // Update marker + pulse positions via CSS transform (GPU-accelerated)
            _overlayEls.markers.forEach(m => {
                const pt = mo.latLngToPoint(cen[m.code][0], cen[m.code][1]);
                if(!pt) { m.el.style.display='none'; return; }
                m.el.style.display = '';
                m.el.style.transform = `translate(${pt.x}px, ${pt.y - 18}px)`;
                // Store for fadein animation
                m.el.style.setProperty('--mx', pt.x + 'px');
                m.el.style.setProperty('--my', (pt.y - 18) + 'px');
            });

            _overlayEls.pulses.forEach(p => {
                const pt = mo.latLngToPoint(cen[p.code][0], cen[p.code][1]);
                if(!pt) { p.el.style.display='none'; return; }
                p.el.style.display = '';
                p.el.style.transform = `translate(${pt.x}px, ${pt.y}px)`;
            });
        }

        // Create overlays after map renders
        setTimeout(create2DOverlays, 800);

        // Smooth real-time position sync on viewport change
        let _rafId = null;
        $c.on('viewportChange', function() {
            if(_rafId) cancelAnimationFrame(_rafId);
            _rafId = requestAnimationFrame(update2DPositions);
        });

        // Attach tooltip events directly to SVG paths (most reliable)
        setTimeout(() => {
            document.querySelectorAll('#worldMapContainer path').forEach(path => {
                path.addEventListener('mouseenter', function(e) {
                    showTooltip(this.getAttribute('data-code'), e);
                });
                path.addEventListener('mouseleave', function() {
                    hideTooltip();
                    this.classList.remove('map-2d-hovered');
                });
                path.addEventListener('mousemove', positionTooltip);
                path.addEventListener('click', hideTooltip);
            });
        }, 500);
    });

    window.showDetailFromMap=function(name){
        const count=nc[name]||0;
        const filtered=rawData.filter(r=>{let v=r.negara?r.negara.trim():'';if(v)v=v.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1).toLowerCase()).join(' ');return v===name;});
        const isFs=!!(document.fullscreenElement||document.webkitFullscreenElement||document.msFullscreenElement);
        if(isFs && typeof showFsDetail==='function'){
            showFsDetail('Negara: '+name,'Menampilkan '+count+' santri',filtered);
            return;
        }
        if(typeof detailModal==='undefined'||!detailModal)return;
        if(typeof detailTable==='undefined'||!detailTable){
            detailTable=$('#detailTable').DataTable({
                pageLength:10,lengthMenu:[[10,25,50,-1],[10,25,50,"Semua"]],
                language:{search:"Cari:",lengthMenu:"Tampil _MENU_ baris",info:"Menampilkan _START_ s/d _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",zeroRecords:"Data tidak ditemukan",paginate:{first:"Awal",last:"Akhir",next:"Lanjut",previous:"Kembali"}},
                orderCellsTop:true,
                columns:[{data:'stambuk'},{data:'nama'},{data:'kelas'},{data:'negara'},{data:'kewarganegaraan'},{data:'pondok'},{data:'kepengurusan'}]
            });
        }
        document.getElementById('detailModalTitle').textContent='Negara: '+name;
        document.getElementById('detailModalSubtitle').textContent='Menampilkan '+count+' santri';
        detailTable.clear().rows.add(filtered).draw();
        detailModal.show();
    };
})();
</script>
