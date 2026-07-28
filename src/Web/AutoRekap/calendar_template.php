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
        'id' => 'itas-start-' . $row['kds'],
        'type' => 'itas_start',
        'date' => $start,
        'label' => 'Proses ITAS: ' . $row['nama'],
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
    $events[] = [
        'id' => 'itas-exp-' . $row['kds'],
        'type' => 'itas_exp',
        'date' => $exp,
        'label' => 'Exp ITAS: ' . $row['nama'],
        'nama' => $row['nama'],
        'kds' => $row['kds'],
        'kelas' => $row['kelas'] ?? '',
        'daerah' => $row['daerah'] ?? '',
        'kepengurusan' => $row['kepengurusan'] ?? '',
        'no_paspor' => $row['no_paspor'] ?? '-',
        'exp_date' => $exp,
        'start_date' => $start,
        'is_expired' => $isExpired,
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

    $events[] = [
        'id' => 'paspor-start-' . $row['kds'],
        'type' => 'paspor_start',
        'date' => $start,
        'label' => 'Proses Paspor: ' . $row['nama'],
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

.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #cbd5e1; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
.cal-header-cell { background: #e0e7ff; text-align: center; padding: 10px 4px; font-size: .7rem; font-weight: 700; color: #3730a3; text-transform: uppercase; letter-spacing: .05em; }
.cal-cell {
    background: white; min-height: 100px; padding: 6px; position: relative;
    transition: background .15s;
    cursor: default;
}
.cal-cell:hover { background: #f8fafc; }
.cal-cell.other-month { opacity: .35; }
.cal-cell.today { box-shadow: inset 0 0 0 2px #3461ff; }
.cal-day { font-size: .75rem; font-weight: 700; color: #475569; margin-bottom: 3px; display: flex; align-items: center; justify-content: space-between; }
.cal-day-num.is-today {
    background: #3461ff; color: white; width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: .72rem;
}
.cal-day-count { font-size: .6rem; color: #94a3b8; font-weight: 600; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; }

.cal-event {
    font-size: .65rem; padding: 2px 6px; border-radius: 4px; margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    cursor: pointer; font-weight: 600; transition: all .15s; display: block; border: none; width: 100%; text-align: left;
}
.cal-event:hover { filter: brightness(0.92); transform: translateX(1px); }

/* Event type colors */
.cal-event.itas_start { background: #b45309; color: white; }
.cal-event.itas_exp { background: #b91c1c; color: white; }
.cal-event.paspor_start { background: #1e40af; color: white; }
.cal-event.paspor_exp { background: #7e22ce; color: white; }
.cal-event.expired { background: #fecaca !important; color: #7f1d1d !important; text-decoration: line-through; opacity: .8; border: 1px solid #f87171; }
.cal-event i { font-size: 0.75rem; margin-right: 4px; vertical-align: middle; }

@keyframes pulse-late { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.7); } 70% { box-shadow: 0 0 0 5px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }
.cal-event.late { animation: pulse-late 2s infinite; border: 1px solid #ef4444; position: relative; z-index: 10; font-weight: 800; }

.cal-more { font-size: .62rem; color: #3461ff; cursor: pointer; font-weight: 700; text-align: center; padding: 2px; }
.cal-more:hover { text-decoration: underline; }

/* â•â•â• Stat cards â•â•â• */
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

/* â•â•â• Filter bar â•â•â• */
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

/* â•â•â• Upcoming panel â•â•â• */
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

/* â•â•â• Legend â•â•â• */
.cal-legend { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; padding: 12px 16px; background: #f8fafc; border-top: 1px solid #f1f5f9; }
.legend-item { display: flex; align-items: center; gap: 6px; font-size: .7rem; color: #475569; font-weight: 500; }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; }

/* â•â•â• Detail Modal â•â•â• */
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
    position: absolute; z-index: 100; left: 0; top: 100%; width: 220px;
    background: white; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.18); border: 1px solid #e8ecf0;
    padding: 8px; display: flex; flex-direction: column; gap: 2px;
}

/* Fullscreen tweaks */
.cal-main:fullscreen .card { height: 100vh; display: flex; flex-direction: column; border-radius: 0 !important; }
.cal-main:fullscreen .cal-grid { flex: 1; grid-template-rows: 35px; grid-auto-rows: 1fr; border-radius: 0; overflow: hidden; }
.cal-main:-webkit-full-screen .card { height: 100vh; display: flex; flex-direction: column; border-radius: 0 !important; }
.cal-main:-webkit-full-screen .cal-grid { flex: 1; grid-template-rows: 35px; grid-auto-rows: 1fr; border-radius: 0; overflow: hidden; }
.cal-popover.show { display: block; }

/* â•â•â• Responsive â•â•â• */
@media (max-width: 1100px) {
    .cal-wrapper { flex-direction: column; align-items: stretch; }
    .cal-aside { width: 100%; margin-top: 10px; }
    .cal-aside.collapsed { display: none; margin-left: 0; }
}
@media (max-width: 768px) {
    .cal-main:fullscreen .card { height: auto; min-height: 100vh; }
    .stat-row { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .cal-cell { min-height: 75px; padding: 2px; }
    .cal-event { font-size: .55rem; padding: 2px 4px; margin-bottom: 3px; }
    .cal-event i { display: none; }
    .cal-header-cell { font-size: .6rem; padding: 6px 1px; }
    .cal-day { font-size: .65rem; }
}
</style>

<!-- â•â•â•â•â•â•â•â•â•â•â• PAGE HEADER â•â•â•â•â•â•â•â•â•â•â• -->
<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= API_URL ?>/auto-rekap" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Kembali ke Auto Rekap" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar3 text-primary me-2"></i>Kalender Expiry</h5>
        <p class="mb-0 text-muted" style="font-size:.78rem;">Tracking jadwal perpanjangan ITAS & Paspor santri</p>
    </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• STATS â•â•â•â•â•â•â•â•â•â•â• -->
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

<!-- â•â•â•â•â•â•â•â•â•â•â• FILTER BAR â•â•â•â•â•â•â•â•â•â•â• -->
<div class="filter-bar">
    <div class="d-flex align-items-center gap-2 text-muted">
        <i class="bi bi-funnel"></i>
        <span style="font-size:.78rem;font-weight:600;">Filter:</span>
    </div>
    <div class="filter-group" id="typeFilter">
        <button class="filter-btn active" data-type="all" onclick="setTypeFilter('all')">Semua</button>
        <button class="filter-btn" data-type="itas" onclick="setTypeFilter('itas')">ITAS</button>
        <button class="filter-btn" data-type="paspor" onclick="setTypeFilter('paspor')">Paspor</button>
    </div>
    <select class="form-select form-select-sm" style="width:180px;font-size:.78rem;" id="kepFilter" onchange="setKepFilter(this.value)">
        <option value="">Semua Kepengurusan</option>
        <?php foreach ($kepList as $k): ?>
            <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• CALENDAR + UPCOMING â•â•â•â•â•â•â•â•â•â•â• -->
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

            <!-- â•â•â•â•â•â•â•â•â•â•â• DETAIL MODAL (Moved inside cal-main for fullscreen support) â•â•â•â•â•â•â•â•â•â•â• -->
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
// â•â•â•â•â•â•â•â•â•â•â• DATA & STATE â•â•â•â•â•â•â•â•â•â•â•
const ALL_EVENTS = <?= $eventsJson ?>;
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const DAYS = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
const TODAY = new Date();
const TODAY_STR = `${TODAY.getFullYear()}-${String(TODAY.getMonth()+1).padStart(2,'0')}-${String(TODAY.getDate()).padStart(2,'0')}`;

let curYear = TODAY.getFullYear();
let curMonth = TODAY.getMonth();
let filterType = 'all';
let filterKep = '';

const TYPE_CONFIG = {
    itas_start:  { icon: 'bi-hourglass-split', label: 'ITAS Proses',   bg: '#b45309', headBg: '#fffbeb', headBorder: '#fde68a' },
    itas_exp:    { icon: 'bi-exclamation-octagon', label: 'ITAS Exp',       bg: '#b91c1c', headBg: '#fef2f2', headBorder: '#fecaca' },
    paspor_start:{ icon: 'bi-journal-text', label: 'Paspor Proses',  bg: '#1e40af', headBg: '#f5f3ff', headBorder: '#ddd6fe' },
    paspor_exp:  { icon: 'bi-journal-x', label: 'Paspor Exp',     bg: '#7e22ce', headBg: '#fdf2f8', headBorder: '#fbcfe8' },
};

// â•â•â•â•â•â•â•â•â•â•â• FILTER â•â•â•â•â•â•â•â•â•â•â•
function getFilteredEvents() {
    return ALL_EVENTS.filter(e => {
        if (filterType !== 'all' && !e.type.startsWith(filterType)) return false;
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

// â•â•â•â•â•â•â•â•â•â•â• TABS â•â•â•â•â•â•â•â•â•â•â•
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
        // Let renderLate handle the text color of the late tab, just remove active border
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

// â•â•â•â•â•â•â•â•â•â•â• NAVIGATION â•â•â•â•â•â•â•â•â•â•â•
function goToday() { curMonth = TODAY.getMonth(); curYear = TODAY.getFullYear(); renderCalendar(); }
function goPrev() { curMonth--; if (curMonth < 0) { curMonth = 11; curYear--; } renderCalendar(); }
function goNext() { curMonth++; if (curMonth > 11) { curMonth = 0; curYear++; } renderCalendar(); }

// â•â•â•â•â•â•â•â•â•â•â• HELPERS â•â•â•â•â•â•â•â•â•â•â•
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

// â•â•â•â•â•â•â•â•â•â•â• RENDER CALENDAR â•â•â•â•â•â•â•â•â•â•â•
function renderCalendar() {
    const grid = document.getElementById('calendarGrid');
    document.getElementById('calMonthTitle').textContent = `${MONTHS[curMonth]} ${curYear}`;

    const events = getFilteredEvents();
    const byDate = {};
    events.forEach(e => { if (!byDate[e.date]) byDate[e.date] = []; byDate[e.date].push(e); });

    const firstDay = new Date(curYear, curMonth, 1);
    const lastDay = new Date(curYear, curMonth + 1, 0);
    let startDow = firstDay.getDay() - 1;
    if (startDow < 0) startDow = 6;

    const cells = [];
    // Day headers
    DAYS.forEach(d => cells.push(`<div class="cal-header-cell">${d}</div>`));

    // Previous month fill
    const prevLast = new Date(curYear, curMonth, 0).getDate();
    for (let i = startDow - 1; i >= 0; i--) {
        const day = prevLast - i;
        const m = curMonth === 0 ? 12 : curMonth;
        const y = curMonth === 0 ? curYear - 1 : curYear;
        const ds = `${y}-${pad(m)}-${pad(day)}`;
        cells.push(renderCell(ds, day, false, byDate[ds] || []));
    }

    // Current month
    for (let d = 1; d <= lastDay.getDate(); d++) {
        const ds = `${curYear}-${pad(curMonth + 1)}-${pad(d)}`;
        cells.push(renderCell(ds, d, true, byDate[ds] || []));
    }

    // Next month fill
    const total = cells.length - 7; // subtract headers
    const remaining = (Math.ceil(total / 7) * 7) - total;
    for (let d = 1; d <= remaining; d++) {
        const nm = curMonth + 2 > 12 ? 1 : curMonth + 2;
        const ny = curMonth + 2 > 12 ? curYear + 1 : curYear;
        const ds = `${ny}-${pad(nm)}-${pad(d)}`;
        cells.push(renderCell(ds, d, false, byDate[ds] || []));
    }

    grid.innerHTML = cells.join('');
    document.getElementById('totalEventsCount').textContent = events.length;
}

function renderCell(dateStr, day, isCurrent, events) {
    const isToday = dateStr === TODAY_STR;
    const maxShow = 3;
    const visible = events.slice(0, maxShow);
    const extra = events.length - maxShow;

    let cls = 'cal-cell';
    if (!isCurrent) cls += ' other-month';
    if (isToday) cls += ' today';

    let html = `<div class="${cls}" data-date="${dateStr}">`;
    html += `<div class="cal-day">`;
    html += isToday
        ? `<span class="cal-day-num is-today">${day}</span>`
        : `<span class="cal-day-num">${day}</span>`;
    if (events.length > 0) html += `<span class="cal-day-count">${events.length}</span>`;
    html += `</div>`;

    visible.forEach(ev => {
        const expCls = ev.is_expired ? ' expired' : (ev.is_late ? ' late' : '');
        const nameParts = ev.nama.split(' ');
        const name = nameParts.length > 1 ? nameParts[0] + ' ' + nameParts[1] : nameParts[0];
        const cfg = TYPE_CONFIG[ev.type] || {};
        let icon = ev.is_expired ? 'bi-x-circle' : cfg.icon;
        if (ev.is_late) icon = 'bi-exclamation-triangle-fill text-warning';
        if (ev.is_in_progress) icon = 'bi-check-circle-fill text-success';
        
        let titleSuffix = '';
        if (ev.is_late) titleSuffix = ' (TERLEWAT)';
        if (ev.is_in_progress) titleSuffix = ' (SEDANG DIPROSES)';

        html += `<button class="cal-event ${ev.type}${expCls}" style="${ev.is_in_progress ? 'border-left:3px solid #10b981;' : ''}" onclick="showDetail(${escAttr(JSON.stringify(ev))})" title="${escHtml(ev.label)}${titleSuffix}">`;
        html += `<i class="bi ${icon}"></i>`;
        html += `${escHtml(name)}</button>`;
    });

    if (extra > 0) {
        const evJson = encodeURIComponent(JSON.stringify(events.slice(maxShow)));
        html += `<div class="cal-more" onclick="showMore(event, '${evJson}')">+${extra} lainnya</div>`;
    }

    html += `</div>`;
    return html;
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
function escAttr(s) { return s.replace(/'/g, "\\'").replace(/"/g, '&quot;'); }

// â•â•â•â•â•â•â•â•â•â•â• MORE POPOVER â•â•â•â•â•â•â•â•â•â•â•
let activePopover = null;
function showMore(evt, evJsonEncoded) {
    evt.stopPropagation();
    closePopovers();
    const events = JSON.parse(decodeURIComponent(evJsonEncoded));
    const cell = evt.target.closest('.cal-cell');
    let pop = document.createElement('div');
    pop.className = 'cal-popover show';
    events.forEach(ev => {
        const expCls = ev.is_expired ? ' expired' : (ev.is_late ? ' late' : '');
        const nameParts = ev.nama.split(' ');
        const name = nameParts.length > 1 ? nameParts[0] + ' ' + nameParts[1] : nameParts[0];
        const cfg = TYPE_CONFIG[ev.type] || {};
        let icon = ev.is_expired ? 'bi-x-circle' : cfg.icon;
        if (ev.is_late) icon = 'bi-exclamation-triangle-fill text-warning';
        if (ev.is_in_progress) icon = 'bi-check-circle-fill text-success';
        
        let titleSuffix = '';
        if (ev.is_late) titleSuffix = ' (TERLEWAT)';
        if (ev.is_in_progress) titleSuffix = ' (SEDANG DIPROSES)';

        pop.innerHTML += `<button class="cal-event ${ev.type}${expCls}" style="${ev.is_in_progress ? 'border-left:3px solid #10b981;' : ''}" onclick="showDetail(${escAttr(JSON.stringify(ev))})" title="${escHtml(ev.label)}${titleSuffix}">
            <i class="bi ${icon}"></i>
            ${escHtml(name)}</button>`;
    });
    cell.style.position = 'relative';
    cell.appendChild(pop);
    activePopover = pop;
}
function closePopovers() { if (activePopover) { activePopover.remove(); activePopover = null; } }
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
    const events = getFilteredEvents();
    const lates = events.filter(e => e.is_late).sort((a,b) => new Date(a.date) - new Date(b.date));
    
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
            <div class="upcoming-dot" style="background:${cfg.bg}"></div>
            <div style="flex:1;min-width:0;">
                <div class="upcoming-name">${escHtml(ev.nama)}</div>
                <div class="upcoming-type" style="color:${cfg.bg}">${escHtml(cfg.label)}</div>
                <div class="upcoming-date"><i class="bi bi-calendar me-1"></i>${formatDateID(ev.date)}</div>
            </div>
            <div class="pt-1 upcoming-diff text-danger" style="font-size:.6rem;">${daysDiff(ev.date)*-1} hr lalu</div>
        </div>`;
    });
}

// â•â•â•â•â•â•â•â•â•â•â• RENDER UPCOMING â•â•â•â•â•â•â•â•â•â•â•
function renderUpcoming() {
    const list = document.getElementById('upcomingList');
    const now = new Date(); now.setHours(0,0,0,0);
    const end = new Date(now); end.setDate(end.getDate() + 60);

    const events = getFilteredEvents()
        .filter(e => { const d = new Date(e.date); return d >= now && d <= end; })
        .sort((a, b) => new Date(a.date) - new Date(b.date))
        .slice(0, 25);

    if (events.length === 0) {
        list.innerHTML = `<div class="text-center py-4 text-muted" style="font-size:.78rem;">
            <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
            Tidak ada event mendatang</div>`;
        return;
    }

    list.innerHTML = events.map(ev => {
        const cfg = TYPE_CONFIG[ev.type];
        const diff = daysDiff(ev.date);
        let diffCls = 'color:#10b981;';
        if (diff <= 7) diffCls = 'color:#ef4444;';
        else if (diff <= 30) diffCls = 'color:#f59e0b;';

        return `<div class="upcoming-item" onclick='showDetail(${escAttr(JSON.stringify(ev))})'>
            <span class="upcoming-dot dot-${ev.type}"></span>
            <div style="flex:1;min-width:0;">
                <div class="upcoming-name text-truncate">${escHtml(ev.nama)}</div>
                <div class="upcoming-type type-${ev.type}">${cfg?.label || ev.type}</div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="upcoming-date">${formatDateID(ev.date)}</span>
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
    const head = document.getElementById('modalHead');
    head.style.background = cfg.headBg || '#f8fafc';
    head.style.borderColor = cfg.headBorder || '#e8ecf0';

    const icon = document.getElementById('modalIcon');
    icon.style.background = cfg.bg || '#64748b';
    const iconMap = { itas_start: 'bi-clock-history', itas_exp: 'bi-exclamation-circle', paspor_start: 'bi-calendar2-check', paspor_exp: 'bi-file-earmark-x' };
    icon.innerHTML = `<i class="bi ${iconMap[ev.type] || 'bi-person'}"></i>`;

    document.getElementById('modalNama').textContent = ev.nama;
    const badge = document.getElementById('modalBadge');
    badge.textContent = cfg.label || ev.type;
    badge.style.background = cfg.bg || '#64748b';
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
