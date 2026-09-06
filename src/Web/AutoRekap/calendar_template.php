<?php
declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $dataItas
 * @var array $dataPaspor
 * @var array $kepList
 * @var array $activeItasJobs
 */

$activeItasJobs = $activeItasJobs ?? [];

$this->setTitle('Kalender Expiry | ' . $applicationParams->name);

// Build events array for JS
$events = [];
$today = date('Y-m-d');

// Stats
$itasProcessMonth = 0;
$pasporProcessMonth = 0;
$itasExpired = 0;
$pasporExpired = 0;
$currentMonth = date('Y-m');

foreach ($dataItas as $row) {
    $exp = $row['exp_itas'];
    $start = $row['start_proses'];
    $isExpired = $exp < $today;
    $isInProgress = in_array($row['kds'], $activeItasJobs);
    $isLate = ($start !== null && $start < $today && !$isInProgress);

    $events[] = [
        'id' => 'itas-' . $row['kds'],
        'type' => 'itas_timeline',
        'date' => $exp, // for sorting in list
        'label' => 'ITAS: ' . $row['nama'],
        'nama' => $row['nama'],
        'kds' => $row['kds'],
        'kelas' => $row['kelas'] ?? '',
        'daerah' => $row['daerah'] ?? '',
        'kepengurusan' => $row['kepengurusan'] ?? '',
        'no_paspor' => $row['no_paspor'] ?? '-',
        'exp_date' => $exp,
        'start_date' => $start,
        'is_expired' => $isExpired,
        'is_late' => $isLate,
        'is_in_progress' => $isInProgress,
    ];

    if ($start !== null && substr($start, 0, 7) === $currentMonth) $itasProcessMonth++;
    if ($isExpired) $itasExpired++;
}

foreach ($dataPaspor as $row) {
    $exp = $row['exp_paspor'];
    $start = $row['start_proses'];
    $isExpired = $exp < $today;
    $isLate = ($start !== null && $start < $today);
    $isInProgress = false; // Paspor tidak diproses di JobDesk sesuai request

    if ($start !== null) {
        $events[] = [
            'id' => 'paspor-start-' . $row['kds'],
            'type' => 'paspor_start',
            'date' => $start,
            'label' => 'Mulai Paspor: ' . $row['nama'],
            'nama' => $row['nama'],
            'kds' => $row['kds'],
            'kelas' => $row['kelas'] ?? '',
            'daerah' => $row['daerah'] ?? '',
            'kepengurusan' => $row['kepengurusan'] ?? '',
            'no_paspor' => $row['no_paspor'] ?? '-',
            'exp_date' => $exp,
            'start_date' => $start,
            'is_expired' => false,
            'is_late' => $isLate,
            'is_in_progress' => false,
        ];
    }
    
    $events[] = [
        'id' => 'paspor-exp-' . $row['kds'],
        'type' => 'paspor_exp',
        'date' => $exp,
        'label' => 'Exp Paspor: ' . $row['nama'],
        'nama' => $row['nama'],
        'kds' => $row['kds'],
        'kelas' => $row['kelas'] ?? '',
        'daerah' => $row['daerah'] ?? '',
        'kepengurusan' => $row['kepengurusan'] ?? '',
        'no_paspor' => $row['no_paspor'] ?? '-',
        'exp_date' => $exp,
        'start_date' => $start,
        'is_expired' => $isExpired,
        'is_late' => false,
        'is_in_progress' => false,
    ];

    if ($start !== null && substr($start, 0, 7) === $currentMonth) $pasporProcessMonth++;
    if ($isExpired) $pasporExpired++;
}

$eventsJson = json_encode($events, JSON_UNESCAPED_UNICODE);
$kepListJson = json_encode($kepList);
?>

<style>
/* â•â•â•â•â•â•â•â•â•â•â• CALENDAR STYLES â•â•â•â•â•â•â•â•â•â•â• */
.cal-wrapper { display: flex; gap: 20px; align-items: stretch; }
.cal-main { flex: 1; min-width: 0; transition: all 0.3s ease; display: flex; flex-direction: column; }
.cal-aside { width: 300px; flex-shrink: 0; transition: all 0.3s ease; transform-origin: right; position: relative; }
.cal-aside.collapsed { width: 0; opacity: 0; margin-left: -20px; }

.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #e2e8f0; border: 1px solid #e2e8f0; border-radius: 12px; }
.cal-header-cell { background: white; text-align: center; padding: 10px 4px 6px; font-size: .7rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.cal-cell {
    background: white; min-height: 100px; padding: 4px; position: relative;
    transition: background .15s;
    cursor: default;
}
.cal-cell:hover { background: #f8fafc; }
.cal-cell.other-month { opacity: .5; }
.cal-cell.today { box-shadow: inset 0 0 0 2px #3461ff; }
.cal-day { font-size: .75rem; font-weight: 600; color: #475569; margin-bottom: 4px; margin-top: 2px; display: flex; justify-content: center; }
.cal-day-num.is-today {
    background: #3461ff; color: white; width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: .72rem;
}
.cal-event {
    font-size: .65rem; padding: 2px 6px; border-radius: 4px; margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    cursor: pointer; font-weight: 600; transition: all .15s; display: block; border: none; width: 100%; text-align: left;
}
.timeline-bar {
    border: none;
    padding: 2px 6px;
    height: 22px;
    margin-bottom: 2px !important;
    display: flex;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: .65rem;
    font-weight: 600;
    transition: filter 0.2s;
}
.timeline-bar:hover { filter: brightness(1.1); }
.border-radius-full { border-radius: 4px; }
.border-radius-left { border-radius: 4px 0 0 4px; }
.border-radius-right { border-radius: 0 4px 4px 0; }
.border-radius-none { border-radius: 0; }
.cal-more { font-size: .6rem; text-align: center; color: #3b82f6; cursor: pointer; font-weight: 700; margin-top: 2px; }

/* Event type colors */
.cal-event.itas_start { background: #b45309; color: white; }
.cal-event.itas_exp { background: #b91c1c; color: white; }
.cal-event.paspor_start { background: #1e40af; color: white; }
.cal-event.paspor_exp { background: #7e22ce; color: white; }
.cal-event.expired { background: #fecaca !important; color: #7f1d1d !important; text-decoration: line-through; opacity: .8; border: 1px solid #f87171; }
.cal-event i { font-size: 0.75rem; margin-right: 4px; vertical-align: middle; }

@keyframes pulse-late { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.7); } 70% { box-shadow: 0 0 0 5px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }
.cal-event.late { animation: pulse-late 2s infinite; border: 1px solid #ef4444; position: relative; z-index: 10; font-weight: 800; }

/* â• â• â•  Stat cards â• â• â•  */
.stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.stat-mini {
    border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;
    transition: transform .15s, box-shadow .15s; border: 1px solid;
}
.stat-mini:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.stat-mini .stat-icon {
    width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; background: white; box-shadow: 0 1px 3px rgba(0,0,0,.1);
}
.stat-mini .stat-val { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
.stat-mini .stat-label { font-size: .68rem; font-weight: 600; opacity: .75; }
.stat-mini .stat-sub { font-size: .58rem; opacity: .55; margin-top: 1px; }

/* â• â• â•  Filter bar â• â• â•  */
.filter-bar {
    background: white; border-radius: 12px; border: 1px solid #e8ecf0; padding: 10px 16px;
    display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;
}
.filter-group { display: flex; background: #f1f5f9; border-radius: 8px; padding: 2px; }
.filter-btn {
    padding: 5px 14px; border: none; border-radius: 6px; font-size: .75rem; font-weight: 600;
    background: transparent; color: #64748b; cursor: pointer; transition: all .15s;
}
.filter-btn.active { background: white; color: #3461ff; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.filter-btn:hover:not(.active) { color: #334155; }

/* â• â• â•  Upcoming panel â• â• â•  */
.upcoming-panel {
    background: white; border-radius: 12px; border: 1px solid #e8ecf0; overflow: hidden;
    display: flex; flex-direction: column; 
    position: absolute; top: 0; bottom: 0; left: 0; right: 0;
}
.tab-btn { border: none; background: transparent; transition: all 0.2s; }
.tab-btn:hover { background: rgba(0,0,0,0.03); }
.tab-btn.active { color: #3461ff !important; border-bottom: 2px solid #3461ff !important; }
.pulse-tab { animation: pulse-text 2s infinite; }
@keyframes pulse-text { 0% { opacity: 1; } 50% { opacity: 0.6; color: #ef4444; } 100% { opacity: 1; } }
.upcoming-list { flex: 1; overflow-y: auto; height: 0; min-height: 0; }
.upcoming-item {
    padding: 10px 16px; border-bottom: 1px solid #f8fafc; display: flex; align-items: flex-start; gap: 10px;
    cursor: pointer; transition: background .15s;
}
.upcoming-item:hover { background: #f8fafc; }
.upcoming-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
.upcoming-name { font-size: .78rem; font-weight: 600; color: #334155; }
.upcoming-type { font-size: .65rem; font-weight: 600; }
.upcoming-date { font-size: .65rem; color: #94a3b8; }
.upcoming-diff { font-size: .65rem; font-weight: 700; }

.dot-itas_start { background: #b45309; }
.dot-itas_exp { background: #b91c1c; }
.dot-paspor_start { background: #1e40af; }
.dot-paspor_exp { background: #7e22ce; }
.type-itas_start { color: #b45309; }
.type-itas_exp { color: #b91c1c; }
.type-paspor_start { color: #1e40af; }
.type-paspor_exp { color: #7e22ce; }

/* â• â• â•  Legend â• â• â•  */
.cal-legend { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; padding: 12px 16px; background: #f8fafc; border-top: 1px solid #f1f5f9; }
.legend-item { display: flex; align-items: center; gap: 6px; font-size: .7rem; color: #475569; font-weight: 500; }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; }

/* â• â• â•  Detail Modal â• â• â•  */
.cal-modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2000;
    background: rgba(0,0,0,.4); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center; padding: 16px;
    opacity: 0; visibility: hidden; transition: all .25s;
}
.cal-modal-overlay.show { opacity: 1; visibility: visible; }
.cal-modal {
    background: white; border-radius: 16px; width: 100%; max-width: 440px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); overflow: hidden;
    transform: scale(.95) translateY(10px); transition: transform .25s;
}
.cal-modal-overlay.show .cal-modal { transform: scale(1) translateY(0); }
.cal-modal-head { padding: 16px 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid; }
.cal-modal-head .icon-box {
    width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: white; box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.cal-modal-body { padding: 20px; }
.cal-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.cal-info-item { background: #f8fafc; border-radius: 8px; padding: 8px 12px; }
.cal-info-label { font-size: .6rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.cal-info-value { font-size: .82rem; font-weight: 700; color: #334155; }

.cal-timeline { background: #f8fafc; border-radius: 12px; padding: 14px 16px; margin-top: 12px; }
.cal-timeline-title { font-size: .62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px; }
.cal-timeline-step { display: flex; align-items: flex-start; gap: 10px; }
.cal-timeline-dot-wrap { display: flex; flex-direction: column; align-items: center; }
.cal-timeline-dot { width: 10px; height: 10px; border-radius: 50%; box-shadow: 0 0 0 3px; }
.cal-timeline-line { width: 2px; height: 28px; background: #e2e8f0; }
.cal-timeline-text { font-size: .8rem; font-weight: 600; color: #334155; }
.cal-timeline-date { font-size: .72rem; color: #64748b; }
.cal-timeline-note { font-size: .6rem; color: #94a3b8; margin-top: 1px; }

/* Popover for +N more */
.cal-popover {
    position: absolute; z-index: 1000; left: 50%; top: -10px; transform: translateX(-50%); width: 220px;
    background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.2); border: none;
    display: flex; flex-direction: column; overflow: hidden;
}
.cal-popover-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 12px 6px;
}
.cal-popover-title {
    font-size: .75rem; font-weight: 700; color: #475569; letter-spacing: .02em;
}
.cal-popover-close {
    background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 4px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    transition: all .2s; width: 24px; height: 24px;
}
.cal-popover-close:hover { background: #f1f5f9; color: #334155; }
.cal-popover-body {
    padding: 0 12px 12px; display: flex; flex-direction: column; gap: 2px;
    max-height: 250px; overflow-y: auto;
}

/* Fullscreen tweaks */
.cal-main:fullscreen .card { height: 100vh; display: flex; flex-direction: column; border-radius: 0 !important; }
.cal-main:fullscreen .cal-grid { flex: 1; grid-template-rows: 35px; grid-auto-rows: 1fr; border-radius: 0; overflow: hidden; }
.cal-main:-webkit-full-screen .card { height: 100vh; display: flex; flex-direction: column; border-radius: 0 !important; }
.cal-main:-webkit-full-screen .cal-grid { flex: 1; grid-template-rows: 35px; grid-auto-rows: 1fr; border-radius: 0; overflow: hidden; }
.cal-popover.show { display: block; }

/* â• â• â•  Responsive â• â• â•  */
@media (max-width: 1100px) {
    .cal-wrapper { flex-direction: column; align-items: stretch; }
    .cal-aside { width: 100%; margin-top: 10px; }
    .cal-aside.collapsed { display: none; margin-left: 0; }
}
@media (max-width: 768px) {
    .cal-main:fullscreen .card { height: auto; min-height: 100vh; }
    
    /* Stats horizontal scroll */
    .stat-row { 
        display: flex; 
        flex-wrap: nowrap; 
        overflow-x: auto; 
        scroll-snap-type: x mandatory; 
        padding-bottom: 10px;
        -webkit-overflow-scrolling: touch;
    }
    .stat-row::-webkit-scrollbar { display: none; }
    .stat-mini { 
        flex: 0 0 85%; 
        scroll-snap-align: center; 
    }
    
    /* Calendar Cells */
    .cal-cell { min-height: 60px; padding: 4px; display: flex; flex-direction: column; align-items: center; }
    .cal-day { width: 100%; font-size: .65rem; justify-content: center; }
    /* Use horizontal scroll for calendar cells on very small screens instead of dots */
    .cal-grid { overflow-x: auto; }
    .cal-cell { min-width: 60px; }
    
    /* Mobile Modal Overlay fixes */
    .cal-modal-overlay { align-items: flex-end; padding: 0; }
    .cal-modal { border-radius: 20px 20px 0 0; transform: translateY(100%); max-width: 100%; margin: 0; padding-bottom: env(safe-area-inset-bottom); }
    .cal-modal-overlay.show .cal-modal { transform: translateY(0); }
    
    /* Upcoming Panel */
    .cal-wrapper { flex-direction: column; }
    .cal-aside.collapsed { display: block; opacity: 1; margin-left: 0; width: 100%; margin-top: 15px; }
    .upcoming-panel { position: relative; height: 500px; max-height: 60vh; }
}
</style>

<!-- â• â• â• â• â• â• â• â• â• â• â•  PAGE HEADER â• â• â• â• â• â• â• â• â• â• â•  -->
<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= API_URL ?>/auto-rekap" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Kembali ke Auto Rekap" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar3 text-primary me-2"></i>Kalender Expiry</h5>
        <p class="mb-0 text-muted" style="font-size:.78rem;">Tracking jadwal perpanjangan ITAS & Paspor santri</p>
    </div>
</div>

<!-- â• â• â• â• â• â• â• â• â• â• â•  STATS â• â• â• â• â• â• â• â• â• â• â•  -->
<div class="stat-row">
    <div class="stat-mini" style="background:#fffbeb;border-color:#fde68a;color:#92400e;">
        <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
        <div>
            <div class="stat-val"><?= $itasProcessMonth ?></div>
            <div class="stat-label">Proses ITAS Bulan Ini</div>
            <div class="stat-sub">Mulai pengerjaan</div>
        </div>
    </div>
    <div class="stat-mini" style="background:#f5f3ff;border-color:#ddd6fe;color:#5b21b6;">
        <div class="stat-icon"><i class="bi bi-calendar2-check"></i></div>
        <div>
            <div class="stat-val"><?= $pasporProcessMonth ?></div>
            <div class="stat-label">Proses Paspor Bulan Ini</div>
            <div class="stat-sub">Mulai pengerjaan</div>
        </div>
    </div>
    <div class="stat-mini" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;">
        <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-val"><?= $itasExpired ?></div>
            <div class="stat-label">ITAS Sudah Expired</div>
            <div class="stat-sub">Perlu tindakan segera</div>
        </div>
    </div>
    <div class="stat-mini" style="background:#fff1f2;border-color:#fecdd3;color:#9f1239;">
        <div class="stat-icon"><i class="bi bi-file-earmark-x"></i></div>
        <div>
            <div class="stat-val"><?= $pasporExpired ?></div>
            <div class="stat-label">Paspor Sudah Expired</div>
            <div class="stat-sub">Perlu tindakan segera</div>
        </div>
    </div>
</div>

<!-- ═══════════ FILTER BAR & TOOLBAR ═══════════ -->
<div class="filter-bar d-flex justify-content-between">
    <div class="d-flex align-items-center gap-2 text-muted">
        <i class="bi bi-funnel"></i>
        <div class="filter-group" id="typeFilter">
            <button class="filter-btn active" data-type="all" onclick="setTypeFilter('all')">Semua</button>
            <button class="filter-btn" data-type="itas" onclick="setTypeFilter('itas')">ITAS</button>
            <button class="filter-btn" data-type="paspor" onclick="setTypeFilter('paspor')">Paspor</button>
        </div>
        <div class="vr mx-1"></div>
        <select class="form-select form-select-sm border-0 bg-light text-secondary" style="width: 140px; font-size:.75rem;" onchange="setKepFilter(this.value)">
            <option value="">Semua Kepengurusan</option>
            <?php foreach ($kepList as $k): ?>
                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="viewModeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 6px; font-weight: 600;">
                Bulan
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="viewModeDropdown" style="font-size: .8rem; border-radius: 10px;">
                <li><a class="dropdown-item mode-item active" href="javascript:void(0)" data-mode="month" onclick="switchViewMode('month', this, 'Bulan')">Bulan</a></li>
                <li><a class="dropdown-item mode-item" href="javascript:void(0)" data-mode="week" onclick="switchViewMode('week', this, 'Minggu')">Minggu</a></li>
                <li><a class="dropdown-item mode-item" href="javascript:void(0)" data-mode="day" onclick="switchViewMode('day', this, 'Hari')">Hari</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item mode-item" href="javascript:void(0)" data-mode="agenda" onclick="switchViewMode('agenda', this, 'Jadwal (Agenda)')">Jadwal (Agenda)</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- â• â• â• â• â• â• â• â• â• â• â•  CALENDAR + UPCOMING â• â• â• â• â• â• â• â• â• â• â•  -->
<div class="cal-wrapper">
    <!-- Main Calendar -->
    <div class="cal-main">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <!-- Month nav -->
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2 px-3">
                <h6 class="mb-0 fw-bold text-dark" id="calMonthTitle"></h6>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 me-1" style="font-size:.72rem;" onclick="downloadImage()" title="Unduh Gambar"><i class="bi bi-image"></i> Gambar</button>
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3 me-2" style="font-size:.72rem;" onclick="downloadPdf()" title="Unduh PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2" style="font-size:.72rem;" onclick="goToday()">Hari Ini</button>
                    <button class="btn btn-sm btn-light rounded-circle me-1" style="width:30px;height:30px;" onclick="goPrev()" title="Bulan Sebelumnya"><i class="bi bi-chevron-left"></i></button>
                    <button class="btn btn-sm btn-light rounded-circle me-3" style="width:30px;height:30px;" onclick="goNext()" title="Bulan Berikutnya"><i class="bi bi-chevron-right"></i></button>
                    <div class="vr mx-1"></div>
                    <button class="btn btn-sm btn-light rounded-circle ms-2" style="width:30px;height:30px;" onclick="togglePanel()" title="Toggle Panel Samping" id="btnTogglePanel"><i class="bi bi-layout-sidebar-reverse"></i></button>
                    <button class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;" onclick="toggleFullscreen()" title="Layar Penuh"><i class="bi bi-arrows-fullscreen"></i></button>
                </div>
            </div>

            <!-- Grid -->
            <div id="calendarGrid" class="cal-grid"></div>

            <!-- Legend -->
            <div class="cal-legend">
                <div class="legend-item"><span class="legend-dot" style="background:#b45309;"></span> Mulai Proses ITAS</div>
                <div class="legend-item"><span class="legend-dot" style="background:#b91c1c;"></span> Expiry ITAS</div>
                <div class="legend-item"><span class="legend-dot" style="background:#1e40af;"></span> Mulai Proses Paspor</div>
                <div class="legend-item"><span class="legend-dot" style="background:#7e22ce;"></span> Expiry Paspor</div>
                <div class="legend-item"><span class="legend-dot" style="background:#10b981;"></span> <i class="bi bi-check-circle-fill text-success"></i> Diproses</div>
                <div class="legend-item"><span class="legend-dot" style="background:#fecaca; border:1px solid #f87171;"></span> <span style="text-decoration:line-through;">Sudah Expired</span></div>
            </div>

            <!-- â• â• â• â• â• â• â• â• â• â• â•  DETAIL MODAL (Moved inside cal-main for fullscreen support) â• â• â• â• â• â• â• â• â• â• â•  -->
            <div class="cal-modal-overlay" id="detailOverlay" onclick="closeDetail()">
                <div class="cal-modal" onclick="event.stopPropagation()">
                    <div class="cal-modal-head" id="modalHead">
                        <div class="icon-box" id="modalIcon"><i class="bi bi-person"></i></div>
                        <div style="flex:1;min-width:0;">
                            <h6 class="mb-0 fw-bold text-dark" id="modalNama" style="font-size:.95rem;"></h6>
                            <span class="badge rounded-pill" id="modalBadge" style="font-size:.65rem;"></span>
                        </div>
                        <button class="btn btn-sm btn-light rounded-circle" onclick="closeDetail()" style="width:30px;height:30px;"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="cal-modal-body">
                        <div class="rounded-3 text-center fw-semibold py-2 mb-3" id="modalStatus" style="font-size:.82rem;"></div>
                        <div class="cal-info-grid">
                            <div class="cal-info-item"><div class="cal-info-label">No. Paspor</div><div class="cal-info-value" id="modalPaspor">-</div></div>
                            <div class="cal-info-item"><div class="cal-info-label">Kelas</div><div class="cal-info-value" id="modalKelas">-</div></div>
                            <div class="cal-info-item"><div class="cal-info-label">Daerah</div><div class="cal-info-value" id="modalDaerah">-</div></div>
                            <div class="cal-info-item"><div class="cal-info-label">Kepengurusan</div><div class="cal-info-value" id="modalKep">-</div></div>
                        </div>
                        <div class="cal-timeline">
                            <div class="cal-timeline-title">Timeline Proses</div>
                            <div class="cal-timeline-step">
                                <div class="cal-timeline-dot-wrap">
                                    <div class="cal-timeline-dot" id="dotStart" style="background:#10b981;box-shadow:0 0 0 3px #d1fae5;"></div>
                                    <div class="cal-timeline-line"></div>
                                </div>
                                <div>
                                    <div class="cal-timeline-text">Mulai Pengerjaan</div>
                                    <div class="cal-timeline-date" id="modalStartDate">-</div>
                                    <div class="cal-timeline-note" id="modalStartNote">-</div>
                                </div>
                            </div>
                            <div class="cal-timeline-step">
                                <div class="cal-timeline-dot-wrap">
                                    <div class="cal-timeline-dot" id="dotExp" style="background:#94a3b8;box-shadow:0 0 0 3px #f1f5f9;"></div>
                                </div>
                                <div>
                                    <div class="cal-timeline-text">Tanggal Expiry</div>
                                    <div class="cal-timeline-date" id="modalExpDate">-</div>
                                    <div class="cal-timeline-note text-danger fw-semibold d-none" id="modalExpNote">Sudah expired!</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Detail Modal -->
        </div>
    </div>

    <!-- Side Panel (Tabs) -->
    <div class="cal-aside" id="calAside">
        <div class="upcoming-panel">
            <div class="upcoming-header p-0 d-flex justify-content-between align-items-center bg-light border-bottom">
                <div class="d-flex flex-grow-1">
                    <button class="btn flex-grow-1 rounded-0 py-3 fw-bold tab-btn active" id="tabUpcoming" onclick="switchTab('upcoming')" style="font-size: .8rem; border-bottom: 2px solid #3461ff; color: #3461ff;">
                        Upcoming
                    </button>
                    <button class="btn flex-grow-1 rounded-0 py-3 fw-bold tab-btn text-muted position-relative" id="tabLate" onclick="switchTab('late')" style="font-size: .8rem; border-bottom: 2px solid transparent;">
                        Terlewat
                        <span class="position-absolute top-25 start-100 translate-middle badge rounded-pill bg-danger d-none" id="lateCountBadge" style="font-size: .55rem; margin-left: -20px; margin-top: 10px;">0</span>
                    </button>
                </div>
                <button class="btn btn-sm text-secondary px-3" onclick="document.getElementById('calAside').classList.add('collapsed')" title="Tutup Panel"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <!-- Upcoming Content -->
            <div id="contentUpcoming" class="d-flex flex-column flex-grow-1" style="min-height:0;">
                <div class="px-3 py-2 text-muted" style="font-size:.65rem; border-bottom: 1px solid #f1f5f9;">60 hari ke depan</div>
                <div class="upcoming-list" id="upcomingList"></div>
                <div class="px-3 py-2 border-top" style="background:#f8fafc;">
                    <div class="d-flex justify-content-between" style="font-size:.65rem;color:#94a3b8;">
                        <span><i class="bi bi-people me-1"></i>Total Events</span>
                        <span class="fw-bold text-dark" id="totalEventsCount">0</span>
                    </div>
                </div>
            </div>

            <!-- Late Content -->
            <div id="contentLate" class="d-none d-flex flex-column flex-grow-1" style="min-height:0;">
                <div class="px-3 py-2" id="lateStatusText" style="font-size:.65rem; border-bottom: 1px solid #f1f5f9;"></div>
                <div class="upcoming-list" id="lateList"></div>
            </div>
        </div>
    </div>
</div>



<script>
// â• â• â• â• â• â• â• â• â• â• â•  DATA & STATE â• â• â• â• â• â• â• â• â• â• â• 
const ALL_EVENTS = <?= $eventsJson ?>;
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const DAYS = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
const TODAY = new Date();
const TODAY_STR = `${TODAY.getFullYear()}-${String(TODAY.getMonth()+1).padStart(2,'0')}-${String(TODAY.getDate()).padStart(2,'0')}`;

let curYear = TODAY.getFullYear();
let curMonth = TODAY.getMonth();
let currentViewMode = 'month';
let currentViewDate = new Date(TODAY.getTime());
let filterType = 'all';
let filterKep = '';

function switchViewMode(mode, el, label) {
    currentViewMode = mode;
    document.getElementById('viewModeDropdown').innerText = label;
    document.querySelectorAll('.mode-item').forEach(item => item.classList.remove('active'));
    if (el) el.classList.add('active');
    
    // For Agenda, maybe we still use the main grid but render list, or switch to Agenda view rendering.
    renderCalendar();
}

const TYPE_CONFIG = {
    itas_timeline:   { 
        icon: 'bi-hourglass-split', label: 'ITAS',   
        h1: 33, s1: 90, l1: 37, 
        h2: 0, s2: 74, l2: 42 
    },
    paspor_timeline: { 
        icon: 'bi-journal-text', label: 'Paspor',  
        h1: 226, s1: 71, l1: 40, 
        h2: 272, s2: 72, l2: 47 
    },
};

// ═══════════ FILTER ═══════════
function getFilteredEvents() {
    return ALL_EVENTS.filter(e => {
        let eType = e.type;
        if (e.type === 'itas_timeline') eType = 'itas';
        if (e.type === 'paspor_start' || e.type === 'paspor_exp') eType = 'paspor';
        if (filterType !== 'all' && !eType.startsWith(filterType)) return false;
        if (filterKep && e.kepengurusan !== filterKep) return false;
        return true;
    });
}

function setTypeFilter(type) {
    filterType = type;
    document.querySelectorAll('#typeFilter .filter-btn').forEach(b => b.classList.toggle('active', b.dataset.type === type));
    renderCalendar();
    renderUpcoming();
    renderLate();
}

function setKepFilter(kep) {
    filterKep = kep;
    renderCalendar();
    renderUpcoming();
    renderLate();
}

// ═══════════ TABS ═══════════
function switchTab(tab) {
    const btnUpcoming = document.getElementById('tabUpcoming');
    const btnLate = document.getElementById('tabLate');
    const contentUpcoming = document.getElementById('contentUpcoming');
    const contentLate = document.getElementById('contentLate');
    
    if (tab === 'upcoming') {
        btnUpcoming.classList.add('active');
        btnUpcoming.style.color = '#3461ff';
        btnUpcoming.style.borderBottomColor = '#3461ff';
        
        btnLate.classList.remove('active');
        btnLate.style.borderBottomColor = 'transparent';
        
        contentUpcoming.classList.remove('d-none');
        contentLate.classList.add('d-none');
    } else {
        btnLate.classList.add('active');
        btnLate.style.borderBottomColor = btnLate.style.color || '#b91c1c';
        
        btnUpcoming.classList.remove('active');
        btnUpcoming.style.color = '';
        btnUpcoming.style.borderBottomColor = 'transparent';
        
        contentLate.classList.remove('d-none');
        contentUpcoming.classList.add('d-none');
    }
}

// ═══════════ NAVIGATION ═══════════
function goToday() { 
    currentViewDate = new Date(TODAY.getTime()); 
    curMonth = currentViewDate.getMonth(); 
    curYear = currentViewDate.getFullYear(); 
    renderCalendar(); 
}
function goPrev() { 
    if (currentViewMode === 'month' || currentViewMode === 'agenda') {
        currentViewDate.setMonth(currentViewDate.getMonth() - 1);
    } else if (currentViewMode === 'week') {
        currentViewDate.setDate(currentViewDate.getDate() - 7);
    } else if (currentViewMode === 'day') {
        currentViewDate.setDate(currentViewDate.getDate() - 1);
    }
    curMonth = currentViewDate.getMonth();
    curYear = currentViewDate.getFullYear();
    renderCalendar(); 
}
function goNext() { 
    if (currentViewMode === 'month' || currentViewMode === 'agenda') {
        currentViewDate.setMonth(currentViewDate.getMonth() + 1);
    } else if (currentViewMode === 'week') {
        currentViewDate.setDate(currentViewDate.getDate() + 7);
    } else if (currentViewMode === 'day') {
        currentViewDate.setDate(currentViewDate.getDate() + 1);
    }
    curMonth = currentViewDate.getMonth();
    curYear = currentViewDate.getFullYear();
    renderCalendar(); 
}

// ═══════════ HELPERS ═══════════
function pad(n) { return String(n).padStart(2, '0'); }
function formatDateID(ds) {
    if (!ds) return '-';
    const d = new Date(ds);
    return `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
}
function daysDiff(ds) {
    const now = new Date(); now.setHours(0,0,0,0);
    const t = new Date(ds); t.setHours(0,0,0,0);
    return Math.ceil((t - now) / 86400000);
}
function getMidnight(ds) {
    if (!ds) return null;
    const d = new Date(ds);
    d.setHours(0,0,0,0);
    return d;
}

// ═══════════ RENDER CALENDAR ═══════════
function renderCalendar() {
    const grid = document.getElementById('calendarGrid');
    grid.className = 'cal-grid'; // Reset class
    grid.style.display = 'grid'; // Reset style
    
    // Set Header Title based on view
    if (currentViewMode === 'month') {
        document.getElementById('calMonthTitle').textContent = `${MONTHS[curMonth]} ${curYear}`;
    } else if (currentViewMode === 'week') {
        let sw = new Date(currentViewDate);
        let startDow = sw.getDay() - 1;
        if (startDow < 0) startDow = 6;
        sw.setDate(sw.getDate() - startDow);
        
        let ew = new Date(sw);
        ew.setDate(ew.getDate() + 6);
        if (sw.getMonth() === ew.getMonth()) {
            document.getElementById('calMonthTitle').textContent = `${sw.getDate()} - ${ew.getDate()} ${MONTHS[sw.getMonth()]} ${sw.getFullYear()}`;
        } else {
            document.getElementById('calMonthTitle').textContent = `${sw.getDate()} ${MONTHS[sw.getMonth()]} - ${ew.getDate()} ${MONTHS[ew.getMonth()]} ${ew.getFullYear()}`;
        }
    } else if (currentViewMode === 'day') {
        document.getElementById('calMonthTitle').textContent = `${currentViewDate.getDate()} ${MONTHS[currentViewDate.getMonth()]} ${currentViewDate.getFullYear()}`;
    } else if (currentViewMode === 'agenda') {
        document.getElementById('calMonthTitle').textContent = `Jadwal ${MONTHS[curMonth]} ${curYear}`;
    }

    let events = getFilteredEvents();
    
    events.forEach(e => {
        if (e.type === 'paspor_start' || e.type === 'paspor_exp') {
            e._startMs = getMidnight(e.date).getTime();
            e._expMs = e._startMs;
        } else {
            if (!e.start_date) e.start_date = e.exp_date;
            e._startMs = getMidnight(e.start_date).getTime();
            e._expMs = getMidnight(e.exp_date).getTime();
        }
    });

    if (currentViewMode === 'agenda') {
        return renderAgendaView(events);
    }

    let gridStart, totalGridCells;
    
    if (currentViewMode === 'month') {
        const firstDay = new Date(curYear, curMonth, 1);
        const lastDay = new Date(curYear, curMonth + 1, 0);
        let startDow = firstDay.getDay() - 1;
        if (startDow < 0) startDow = 6;

        gridStart = new Date(firstDay);
        gridStart.setDate(firstDay.getDate() - startDow);
        gridStart.setHours(0,0,0,0);
        
        const totalDaysInMonth = lastDay.getDate();
        const totalCellsBeforeNextMonthFill = startDow + totalDaysInMonth;
        const remaining = (Math.ceil(totalCellsBeforeNextMonthFill / 7) * 7) - totalCellsBeforeNextMonthFill;
        totalGridCells = totalCellsBeforeNextMonthFill + remaining;
        
        grid.style.gridTemplateColumns = 'repeat(7, 1fr)';
    } else if (currentViewMode === 'week') {
        gridStart = new Date(currentViewDate);
        let startDow = gridStart.getDay() - 1;
        if (startDow < 0) startDow = 6;
        gridStart.setDate(gridStart.getDate() - startDow);
        gridStart.setHours(0,0,0,0);
        
        totalGridCells = 7;
        grid.style.gridTemplateColumns = 'repeat(7, 1fr)';
    } else if (currentViewMode === 'day') {
        gridStart = new Date(currentViewDate);
        gridStart.setHours(0,0,0,0);
        totalGridCells = 1;
        grid.style.gridTemplateColumns = '1fr';
    }

    const gridEnd = new Date(gridStart);
    gridEnd.setDate(gridStart.getDate() + totalGridCells - 1);
    gridEnd.setHours(0,0,0,0);

    let activeEvents = events.filter(e => e._expMs >= gridStart.getTime() && e._startMs <= gridEnd.getTime());
    activeEvents.sort((a, b) => {
        if (a._startMs !== b._startMs) return a._startMs - b._startMs;
        return (b._expMs - b._startMs) - (a._expMs - a._startMs);
    });

    const slotUsage = {}; 
    activeEvents.forEach(e => {
        let slot = 0;
        let isFree = false;
        while (!isFree) {
            isFree = true;
            for (let d = new Date(e._startMs); d.getTime() <= e._expMs; d.setDate(d.getDate()+1)) {
                let ts = d.getTime();
                if (!slotUsage[ts]) slotUsage[ts] = [];
                if (slotUsage[ts][slot]) {
                    isFree = false;
                    break;
                }
            }
            if (!isFree) slot++;
        }
        e._slot = slot;
        for (let d = new Date(e._startMs); d.getTime() <= e._expMs; d.setDate(d.getDate()+1)) {
            let ts = d.getTime();
            if (!slotUsage[ts]) slotUsage[ts] = [];
            slotUsage[ts][slot] = e;
        }
    });

    const cells = [];
    if (currentViewMode !== 'day') {
        DAYS.forEach(d => cells.push(`<div class="cal-header-cell">${d}</div>`));
    } else {
        cells.push(`<div class="cal-header-cell">${DAYS[gridStart.getDay() === 0 ? 6 : gridStart.getDay() - 1]}</div>`);
    }

    for (let i = 0; i < totalGridCells; i++) {
        let currentCellDate = new Date(gridStart);
        currentCellDate.setDate(gridStart.getDate() + i);
        let ds = `${currentCellDate.getFullYear()}-${pad(currentCellDate.getMonth() + 1)}-${pad(currentCellDate.getDate())}`;
        let day = currentCellDate.getDate();
        let isCurrentMonth = currentCellDate.getMonth() === curMonth;
        
        let ts = currentCellDate.getTime();
        let dayEventsBySlot = slotUsage[ts] || [];
        
        cells.push(renderCell(ds, day, isCurrentMonth, dayEventsBySlot, currentCellDate.getDay() === 1));
    }

    grid.innerHTML = cells.join('');
    document.getElementById('totalEventsCount').textContent = events.length;
}

function renderAgendaView(events) {
    const grid = document.getElementById('calendarGrid');
    
    let dStart = new Date(curYear, curMonth, 1);
    dStart.setHours(0,0,0,0);
    let dEnd = new Date(curYear, curMonth + 1, 0);
    dEnd.setHours(23,59,59,999);
    
    let grouped = {};
    events.forEach(e => {
        let startInMonth = e._startMs >= dStart.getTime() && e._startMs <= dEnd.getTime();
        let expInMonth = e._expMs >= dStart.getTime() && e._expMs <= dEnd.getTime();
        
        if (startInMonth) {
            let d = new Date(e._startMs);
            let ds = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            if (!grouped[ds]) grouped[ds] = [];
            let startEv = Object.assign({}, e);
            if (startEv.type === 'itas_timeline') {
                startEv.label = 'Mulai Proses ITAS';
                startEv._agendaColor = '#b45309';
            }
            grouped[ds].push(startEv);
        }
        
        if (expInMonth && e._expMs !== e._startMs) {
            let d = new Date(e._expMs);
            let ds = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            if (!grouped[ds]) grouped[ds] = [];
            let expEv = Object.assign({}, e);
            if (expEv.type === 'itas_timeline') {
                expEv.label = 'Expiry ITAS';
                expEv._agendaColor = '#b91c1c';
            }
            grouped[ds].push(expEv);
        }
    });

    let keys = Object.keys(grouped).sort();
    
    let html = `<div style="padding: 24px; background: white; min-height: 200px; display: flex; flex-direction: column; gap: 24px; overflow-y: auto;">`;
    
    if (keys.length === 0) {
        html += `<div class="text-center text-muted mt-5"><i class="bi bi-calendar-x fs-1 d-block mb-3"></i>Tidak ada jadwal di bulan ini.</div>`;
    }

    keys.forEach(ds => {
        let evs = grouped[ds];
        let d = new Date(ds);
        let dow = d.getDay() - 1; if (dow < 0) dow = 6;
        let dayName = DAYS[dow];
        
        html += `
        <div style="display: flex; gap: 24px;">
            <div style="width: 70px; flex-shrink: 0; text-align: right; border-right: 2px solid #e2e8f0; padding-right: 16px;">
                <div style="font-size: 1.6rem; font-weight: 800; color: #334155; line-height: 1;">${d.getDate()}</div>
                <div style="font-size: .65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 4px;">${dayName}, ${MONTHS[d.getMonth()]}</div>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; gap: 10px;">`;
            
        evs.forEach(ev => {
            const cfg = TYPE_CONFIG[ev.type] || { hueStart: 0, hueEnd: 0 };
            
            let icon = ev.is_expired ? 'bi-x-circle' : (cfg.icon || 'bi-circle-fill');
            if (ev.is_late) icon = 'bi-exclamation-triangle-fill text-warning';
            if (ev.is_in_progress) icon = 'bi-check-circle-fill text-success';
            
            let badge = '';
            if (ev.is_late) badge = '<span class="badge bg-danger-subtle text-danger ms-2" style="font-size:.6rem;">Terlewat</span>';
            if (ev.is_in_progress) badge = '<span class="badge bg-success-subtle text-success ms-2" style="font-size:.6rem;">Diproses</span>';
            if (ev.is_expired) badge = '<span class="badge bg-dark ms-2" style="font-size:.6rem;">Expired</span>';

            let leftBorderColor = ev._agendaColor || (cfg.h1 ? `hsl(${cfg.h1}, ${cfg.s1}%, ${cfg.l1}%)` : (ev.type==='paspor_start'?'#1e40af':'#7e22ce'));

            html += `
                <div class="card border-0 shadow-sm rounded-3" style="cursor: pointer; transition: transform 0.15s, box-shadow 0.15s;" onclick="showDetail(${escAttr(JSON.stringify(ev))})" onmouseover="this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08) !important';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075) !important';">
                    <div class="card-body p-3 d-flex align-items-center" style="position: relative;">
                        <div style="width: 4px; height: 100%; position: absolute; left: 0; top: 0; bottom: 0; background: ${leftBorderColor}; border-radius: 4px 0 0 4px;"></div>
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin-right: 16px; margin-left: 8px;">
                            <i class="bi ${icon} fs-5" style="color: ${leftBorderColor};"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div class="fw-bold text-dark text-truncate" style="font-size: .85rem;">${ev.nama} ${badge}</div>
                            <div class="text-muted text-truncate" style="font-size: .7rem; margin-top: 2px;">${ev.label} &bull; ${ev.kepengurusan}</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted opacity-50"></i>
                    </div>
                </div>`;
        });
        
        html += `
            </div>
        </div>`;
    });
    
    html += `</div>`;
    
    grid.style.display = 'block'; 
    grid.style.gridTemplateColumns = 'none';
    grid.innerHTML = html;
    document.getElementById('totalEventsCount').textContent = events.length;
}

function renderCell(dateStr, day, isCurrent, dayEventsBySlot, isStartOfWeek) {
    const isToday = dateStr === TODAY_STR;
    const maxShow = currentViewMode === 'month' ? 4 : 20; 
    
    let cls = 'cal-cell';
    if (!isCurrent && currentViewMode === 'month') cls += ' other-month';
    if (isToday) cls += ' today';

    let html = `<div class="${cls}" data-date="${dateStr}" style="min-height:100px;">`;
    html += `<div class="cal-day">`;
    html += isToday
        ? `<span class="cal-day-num is-today">${day}</span>`
        : `<span class="cal-day-num">${day}</span>`;
    html += `</div>`;

    let extraCount = 0;
    
    for (let s = 0; s < maxShow; s++) {
        let ev = dayEventsBySlot[s];
        if (ev) {
            html += renderEventBar(ev, dateStr, isStartOfWeek);
        } else {
            let hasEventBelow = false;
            for(let j=s+1; j<dayEventsBySlot.length; j++) if (dayEventsBySlot[j]) hasEventBelow = true;
            if (hasEventBelow && s < maxShow - 1) {
                html += `<div class="cal-event-spacer" style="height:22px; margin-bottom:2px;"></div>`;
            }
        }
    }
    
    for (let s = maxShow; s < dayEventsBySlot.length; s++) {
        if (dayEventsBySlot[s]) extraCount++;
    }

    if (extraCount > 0) {
        const hiddenEvents = [];
        for (let s = 0; s < dayEventsBySlot.length; s++) {
            if (dayEventsBySlot[s]) hiddenEvents.push(dayEventsBySlot[s]);
        }
        // Store in global window object
        window._hiddenEvents = window._hiddenEvents || {};
        window._hiddenEvents[dateStr] = hiddenEvents;
        html += `<div class="cal-more" onclick="showMore(event, '${dateStr}')">${extraCount} more</div>`;
    }

    html += `</div>`;
    return html;
}

function renderEventBar(ev, currentDateStr, isStartOfWeek) {
    if (ev.type === 'paspor_start' || ev.type === 'paspor_exp') {
        const title = ev.type === 'paspor_start' ? 'Mulai Proses Paspor' : 'Exp Paspor';
        const cls = ev.type === 'paspor_start' ? 'paspor_start' : 'paspor_exp';
        const icon = ev.type === 'paspor_start' ? 'bi-journal-text' : 'bi-journal-x';
        const nameParts = ev.nama.split(' ');
        const name = nameParts.length > 1 ? nameParts[0] + ' ' + nameParts[1] : nameParts[0];
        return `<button class="cal-event ${cls}" onclick="showDetail(${escAttr(JSON.stringify(ev))})" title="${title}">
            <i class="bi ${icon}"></i>${name}
        </button>`;
    }
    const cfg = TYPE_CONFIG[ev.type] || { hueStart: 0, hueEnd: 0 };
    
    const totalDays = Math.max(1, (ev._expMs - ev._startMs) / 86400000);
    const passedDays = Math.max(0, (new Date(currentDateStr).getTime() - ev._startMs) / 86400000);
    const progress = Math.min(1, passedDays / totalDays);
    
    let h1 = cfg.h1, s1 = cfg.s1, l1 = cfg.l1;
    if (ev.is_in_progress) {
        h1 = 155; s1 = 83; l1 = 39; // Green
    }
    const h = h1 + (cfg.h2 - h1) * progress;
    const s = s1 + (cfg.s2 - s1) * progress;
    const l = l1 + (cfg.l2 - l1) * progress;
    const bgColor = `hsl(${h}, ${s}%, ${l}%)`;
    const textColor = `#fff`;

    const isStart = ev.start_date === currentDateStr;
    const isEnd = ev.exp_date === currentDateStr;
    
    let brClass = '';
    if (isStart && isEnd) brClass = 'border-radius-full';
    else if (isStart) brClass = 'border-radius-left';
    else if (isEnd) brClass = 'border-radius-right';
    else brClass = 'border-radius-none';

    const showText = isStart || isStartOfWeek || currentDateStr.endsWith('-01') || currentViewMode === 'day';
    const nameParts = ev.nama.split(' ');
    const name = nameParts.length > 1 ? nameParts[0] + ' ' + nameParts[1] : nameParts[0];

    let icon = ev.is_expired ? 'bi-x-circle' : cfg.icon;
    if (ev.is_late) icon = 'bi-exclamation-triangle-fill';
    if (ev.is_in_progress) icon = 'bi-check-circle-fill';
    
    let titleSuffix = '';
    if (ev.is_late) titleSuffix = ' (TERLEWAT)';
    if (ev.is_in_progress) titleSuffix = ' (SEDANG DIPROSES)';
    if (ev.is_expired) titleSuffix = ' (EXPIRED)';

    let innerHtml = '';
    if (showText) {
        innerHtml = `<i class="bi ${icon} me-1"></i>${escHtml(name)}`;
    } else {
        innerHtml = `&nbsp;`;
    }

    let marginStyle = 'margin-left: -6px; margin-right: -6px; width: calc(100% + 12px); padding-left: 12px;';
    if (isStart) marginStyle = 'margin-left: 0; width: calc(100% + 6px);';
    if (isEnd) marginStyle = 'margin-left: -6px; margin-right: 0; width: calc(100% + 6px); padding-left: 12px;';
    if (isStart && isEnd) marginStyle = 'margin: 0; width: 100%;';

    return `<button class="cal-event timeline-bar ${brClass}" style="${marginStyle} background-color: ${bgColor}; color: ${textColor};" onclick="showDetail(${escAttr(JSON.stringify(ev))})" title="${escHtml(ev.label)}${titleSuffix} | ${ev.start_date} s/d ${ev.exp_date}">${innerHtml}</button>`;
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
function escAttr(s) { return s.replace(/'/g, "\\'").replace(/"/g, '&quot;'); }

// ═══════════ MORE POPOVER ═══════════
let activePopover = null;
function showMore(evt, dateStr) {
    evt.stopPropagation();
    closePopovers(evt);
    const events = window._hiddenEvents[dateStr] || [];
    const cell = evt.target.closest('.cal-cell');
    let pop = document.createElement('div');
    pop.className = 'cal-popover show';
    
    // Header
    const d = new Date(dateStr);
    let dow = d.getDay() - 1;
    if (dow < 0) dow = 6;
    const headerTitle = DAYS[dow].toUpperCase() + ' ' + d.getDate();
    
    let html = `<div class="cal-popover-header">
        <div class="cal-popover-title">${headerTitle}</div>
        <button class="cal-popover-close" onclick="closePopovers(event)"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="cal-popover-body">`;
    
    events.forEach(ev => {
        let evHtml = '';
        if (ev.type === 'paspor_start' || ev.type === 'paspor_exp') {
            const cls = ev.type === 'paspor_start' ? 'paspor_start' : 'paspor_exp';
            const icon = ev.type === 'paspor_start' ? 'bi-journal-text' : 'bi-journal-x';
            const nameParts = ev.nama.split(' ');
            const name = nameParts.length > 1 ? nameParts[0] + ' ' + nameParts[1] : nameParts[0];
            evHtml = `<button class="cal-event ${cls}" style="flex-shrink: 0; width: 100%; margin: 0 0 2px 0;" onclick="showDetail(${escAttr(JSON.stringify(ev))})">
                <i class="bi ${icon}"></i>${name}
            </button>`;
        } else {
            const cfg = TYPE_CONFIG[ev.type] || { hueStart: 0, hueEnd: 0 };
            const totalDays = Math.max(1, (ev._expMs - ev._startMs) / 86400000);
            const passedDays = Math.max(0, (new Date(dateStr).getTime() - ev._startMs) / 86400000);
            const progress = Math.min(1, passedDays / totalDays);
            
            let h1 = cfg.h1, s1 = cfg.s1, l1 = cfg.l1;
            if (ev.is_in_progress) {
                h1 = 155; s1 = 83; l1 = 39; // Green
            }
            const h = h1 + (cfg.h2 - h1) * progress;
            const s = s1 + (cfg.s2 - s1) * progress;
            const l = l1 + (cfg.l2 - l1) * progress;
            const bgColor = `hsl(${h}, ${s}%, ${l}%)`;
            const textColor = `#fff`;

            const nameParts = ev.nama.split(' ');
            const name = nameParts.length > 1 ? nameParts[0] + ' ' + nameParts[1] : nameParts[0];

            let icon = ev.is_expired ? 'bi-x-circle' : (cfg.icon || 'bi-circle-fill');
            if (ev.is_late) icon = 'bi-exclamation-triangle-fill';
            if (ev.is_in_progress) icon = 'bi-check-circle-fill';
            
            let titleSuffix = '';
            if (ev.is_late) titleSuffix = ' (TERLEWAT)';
            if (ev.is_in_progress) titleSuffix = ' (SEDANG DIPROSES)';

            let innerHtml = `<i class="bi ${icon} me-1"></i>${escHtml(name)}`;
            
            evHtml = `<button class="cal-event timeline-bar border-radius-full" style="flex-shrink: 0; width: 100%; margin: 0 0 2px 0 !important; background-color: ${bgColor}; color: ${textColor}; padding-left: 8px;" onclick="showDetail(${escAttr(JSON.stringify(ev))})" title="${escHtml(ev.label)}${titleSuffix}">${innerHtml}</button>`;
        }
        html += evHtml;
    });
    
    html += `</div>`;
    pop.innerHTML = html;
    
    document.body.appendChild(pop);
    activePopover = pop;

    const rect = cell.getBoundingClientRect();
    const scrollY = window.scrollY || document.documentElement.scrollTop;
    const scrollX = window.scrollX || document.documentElement.scrollLeft;

    pop.style.left = (rect.left + scrollX + rect.width / 2) + 'px';
    let topPos = rect.top + scrollY - 10;
    pop.style.top = topPos + 'px';

    setTimeout(() => {
        const popRect = pop.getBoundingClientRect();
        if (popRect.bottom > window.innerHeight) {
            pop.style.top = Math.max(scrollY, rect.bottom + scrollY - popRect.height + 10) + 'px';
        }
    }, 0);
}
function closePopovers(e) { 
    if (e) e.stopPropagation();
    if (activePopover) { activePopover.remove(); activePopover = null; } 
}
document.addEventListener('click', closePopovers);

function togglePanel() {
    const aside = document.getElementById('calAside');
    const btn = document.getElementById('btnTogglePanel');
    aside.classList.toggle('collapsed');
    if (aside.classList.contains('collapsed')) {
        btn.classList.add('bg-primary', 'text-white');
        btn.classList.remove('btn-light');
    } else {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('btn-light');
    }
}

function toggleFullscreen() {
    const elem = document.querySelector('.cal-main');
    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch(err => {
            console.error(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}

document.addEventListener('fullscreenchange', (event) => {
    const main = document.querySelector('.cal-main');
    if (document.fullscreenElement) {
        main.style.padding = '0'; // Let CSS handle 100vh
        main.style.background = '#f0f2f5';
    } else {
        main.style.padding = '0';
        main.style.background = 'transparent';
    }
});

function renderLate() {
    const list = document.getElementById('lateList');
    const badge = document.getElementById('lateCountBadge');
    const statusText = document.getElementById('lateStatusText');
    let lateArr = [];
    getFilteredEvents().forEach(e => {
        if (e.is_late) {
            let d = getMidnight(e.start_date || e.date).getTime();
            let evCopy = Object.assign({}, e);
            evCopy._sortMs = d;
            if (evCopy.type === 'itas_timeline') evCopy._upcLabel = 'Mulai Proses ITAS';
            lateArr.push(evCopy);
        }
    });
    lateArr.sort((a,b) => a._sortMs - b._sortMs);
    const lates = lateArr;
    
    if (lates.length === 0) {
        badge.classList.add('d-none');
        document.getElementById('tabLate').style.color = '';
        document.getElementById('tabLate').classList.remove('pulse-tab');
        
        statusText.style.background = '#f8fafc';
        statusText.style.color = '#64748b';
        statusText.style.borderColor = '#f1f5f9';
        statusText.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Status pengerjaan aman';
        
        list.innerHTML = `<div class="p-4 text-center" style="font-size:.7rem; color:#94a3b8;">
            <div class="fs-1 mb-2">ðŸŽ‰</div>
            Tidak ada tugas proses yang terlewat.
        </div>`;
        return;
    }
    
    badge.classList.remove('d-none');
    badge.innerText = lates.length;
    document.getElementById('tabLate').style.color = '#b91c1c';
    document.getElementById('tabLate').classList.add('pulse-tab');
    
    statusText.style.background = '#fef2f2';
    statusText.style.color = '#991b1b';
    statusText.style.borderColor = '#fecaca';
    statusText.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Melewati batas mulai pengerjaan';

    list.innerHTML = '';
    lates.forEach(ev => {
        const cfg = TYPE_CONFIG[ev.type] || {};
        list.innerHTML += `<div class="upcoming-item" onclick="showDetail(${escAttr(JSON.stringify(ev))})">
            <div class="upcoming-dot" style="background:${cfg.bg || (ev.type.includes('itas') ? '#b45309' : '#1e40af')}"></div>
            <div style="flex:1;min-width:0;">
                <div class="upcoming-name">${escHtml(ev.nama)}</div>
                <div class="upcoming-type" style="color:${cfg.bg || (ev.type.includes('itas') ? '#b45309' : '#1e40af')}">${escHtml(ev._upcLabel || cfg.label || ev.type)}</div>
                <div class="upcoming-date"><i class="bi bi-calendar me-1"></i>${formatDateID(ev._sortMs)}</div>
            </div>
            <div class="pt-1 upcoming-diff text-danger" style="font-size:.6rem;">${daysDiff(ev._sortMs)*-1} hr lalu</div>
        </div>`;
    });
}

// â•â•â•â•â•â•â•â•â•â•â• RENDER UPCOMING â•â•â•â•â•â•â•â•â•â•â•
function renderUpcoming() {
    const list = document.getElementById('upcomingList');
    const now = new Date(); now.setHours(0,0,0,0);
    const end = new Date(now); end.setDate(end.getDate() + 60);

    let upcomingArr = [];
    getFilteredEvents().forEach(e => {
        if (e.type === 'itas_timeline') {
            let startD = getMidnight(e.start_date || e.date).getTime();
            let expD = getMidnight(e.exp_date || e.date).getTime();
            if (startD >= now.getTime() && startD <= end.getTime()) {
                let evStart = Object.assign({}, e);
                evStart._sortMs = startD;
                evStart._upcLabel = 'Mulai Proses ITAS';
                upcomingArr.push(evStart);
            }
            if (expD >= now.getTime() && expD <= end.getTime() && expD !== startD) {
                let evExp = Object.assign({}, e);
                evExp._sortMs = expD;
                evExp._upcLabel = 'Expiry ITAS';
                upcomingArr.push(evExp);
            }
        } else {
            let dObj = getMidnight(e.date || e.start_date);
            if (dObj) {
                let d = dObj.getTime();
                if (d >= now.getTime() && d <= end.getTime()) {
                    let evCopy = Object.assign({}, e);
                    evCopy._sortMs = d;
                    upcomingArr.push(evCopy);
                }
            }
        }
    });

    upcomingArr.sort((a, b) => a._sortMs - b._sortMs);
    const events = upcomingArr.slice(0, 25);

    if (events.length === 0) {
        list.innerHTML = `<div class="text-center py-4 text-muted" style="font-size:.78rem;">
            <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
            Tidak ada event mendatang</div>`;
        return;
    }

    list.innerHTML = events.map(ev => {
        const cfg = TYPE_CONFIG[ev.type];
        const diff = daysDiff(ev._sortMs);
        let diffCls = 'color:#10b981;';
        if (diff <= 7) diffCls = 'color:#ef4444;';
        else if (diff <= 30) diffCls = 'color:#f59e0b;';

        return `<div class="upcoming-item" onclick='showDetail(${escAttr(JSON.stringify(ev))})'>
            <span class="upcoming-dot dot-${ev.type}"></span>
            <div style="flex:1;min-width:0;">
                <div class="upcoming-name text-truncate">${escHtml(ev.nama)}</div>
                <div class="upcoming-type type-${ev.type}">${ev._upcLabel || cfg?.label || ev.type}</div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="upcoming-date">${formatDateID(ev._sortMs)}</span>
                    <span class="upcoming-diff" style="${diffCls}">${diff === 0 ? 'Hari ini' : diff + 'h'}</span>
                </div>
            </div>
        </div>`;
    }).join('');
}

// â•â•â•â•â•â•â•â•â•â•â• DETAIL MODAL â•â•â•â•â•â•â•â•â•â•â•
function showDetail(ev) {
    if (typeof ev === 'string') ev = JSON.parse(ev);
    closePopovers();

    const cfg = TYPE_CONFIG[ev.type] || {};
    const diff = daysDiff(ev.exp_date);
    const diffStart = daysDiff(ev.start_date);

    // Head
    let headBg = '#f8fafc', headBorder = '#e8ecf0', bg = '#64748b', iconCls = 'bi-person', badgeText = ev.type;
    
    if (ev.type === 'itas_timeline') {
        bg = '#b45309'; headBg = '#fffbeb'; headBorder = '#fde68a'; iconCls = 'bi-hourglass-split'; badgeText = 'ITAS';
    } else if (ev.type === 'paspor_start') {
        bg = '#1e40af'; headBg = '#f5f3ff'; headBorder = '#ddd6fe'; iconCls = 'bi-journal-text'; badgeText = 'Mulai Paspor';
    } else if (ev.type === 'paspor_exp') {
        bg = '#7e22ce'; headBg = '#fdf2f8'; headBorder = '#fbcfe8'; iconCls = 'bi-journal-x'; badgeText = 'Exp Paspor';
    }

    const head = document.getElementById('modalHead');
    head.style.background = headBg;
    head.style.borderColor = headBorder;

    const icon = document.getElementById('modalIcon');
    icon.style.background = bg;
    icon.innerHTML = `<i class="bi ${iconCls}"></i>`;

    document.getElementById('modalNama').textContent = ev.nama;
    const badge = document.getElementById('modalBadge');
    badge.textContent = badgeText;
    badge.style.background = bg;
    badge.style.color = 'white';

    // Status
    const status = document.getElementById('modalStatus');
    if (ev.is_expired) {
        status.style.background = '#fef2f2'; status.style.color = '#991b1b'; status.style.border = '1px solid #fecaca';
        status.textContent = `âš ï¸ Sudah expired ${Math.abs(diff)} hari yang lalu`;
    } else if (diff <= 30) {
        status.style.background = '#fffbeb'; status.style.color = '#92400e'; status.style.border = '1px solid #fde68a';
        status.textContent = `â³ ${diff} hari lagi menuju expiry`;
    } else {
        status.style.background = '#f0fdf4'; status.style.color = '#166534'; status.style.border = '1px solid #bbf7d0';
        status.textContent = `✅ Masih ${diff} hari menuju expiry`;
    }

    // Info
    document.getElementById('modalPaspor').textContent = ev.no_paspor || '-';
    document.getElementById('modalKelas').textContent = ev.kelas || '-';
    document.getElementById('modalDaerah').textContent = ev.daerah || '-';
    document.getElementById('modalKep').textContent = ev.kepengurusan || '-';

    // Timeline
    document.getElementById('modalStartDate').textContent = formatDateID(ev.start_date);
    document.getElementById('modalStartNote').textContent = ev.type.includes('itas') ? '3 bulan sebelum expiry' : '18 bulan sebelum expiry';
    document.getElementById('modalExpDate').textContent = formatDateID(ev.exp_date);

    const dotStart = document.getElementById('dotStart');
    dotStart.style.background = diffStart <= 0 ? '#10b981' : '#94a3b8';
    dotStart.style.boxShadow = `0 0 0 3px ${diffStart <= 0 ? '#d1fae5' : '#f1f5f9'}`;

    const dotExp = document.getElementById('dotExp');
    dotExp.style.background = ev.is_expired ? '#ef4444' : '#94a3b8';
    dotExp.style.boxShadow = `0 0 0 3px ${ev.is_expired ? '#fee2e2' : '#f1f5f9'}`;

    const expNote = document.getElementById('modalExpNote');
    if (ev.is_expired) { expNote.classList.remove('d-none'); } else { expNote.classList.add('d-none'); }

    document.getElementById('detailOverlay').classList.add('show');
}

function closeDetail() { document.getElementById('detailOverlay').classList.remove('show'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });

// â•â•â•â•â•â•â•â•â•â•â• INIT â•â•â•â•â•â•â•â•â•â•â•
renderCalendar();
renderUpcoming();
renderLate();

async function downloadImage() {
    const el = document.querySelector('.cal-main .card');
    const title = document.getElementById('calMonthTitle').innerText;
    if (!el) return;
    
    const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const link = document.createElement('a');
    link.download = `Kalender Expiry - ${title}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
}

async function downloadPdf() {
    const el = document.querySelector('.cal-main .card');
    const title = document.getElementById('calMonthTitle').innerText;
    if (!el) return;

    const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const imgData = canvas.toDataURL('image/jpeg', 0.95);
    const { jsPDF } = window.jspdf;
    
    // A4 landscape
    const pdf = new jsPDF('l', 'mm', 'a4');
    const pdfWidth = pdf.internal.pageSize.getWidth();
    // Scale height based on aspect ratio
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
    
    // If the height exceeds A4 height, we might want to scale it down further, but usually calendar fits A4 landscape well.
    pdf.addImage(imgData, 'JPEG', 0, 10, pdfWidth, pdfHeight);
    pdf.save(`Kalender Expiry - ${title}.pdf`);
}
</script>

<!-- PDF Exports -->
<script src="<?= ASSET_URL ?>/assets/offline/js/html2canvas.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jspdf.umd.min.js"></script>

