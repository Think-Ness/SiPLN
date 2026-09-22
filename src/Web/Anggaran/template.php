<?php
declare(strict_types=1);
use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $pengajuanList
 * @var bool $isAdmin
 * @var string $myKepengurusan
 * @var array $hijriMonths
 * @var string $currentHijriMonth
 * @var int $currentHijriYear
 */
// $this->setTitle('Manajemen Anggaran Operasional | Sistem Informasi');
?>

<style>
    /* Print styles */
    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; }
        #printArea { position: absolute; left: 0; top: 0; width: 100%; }
        .modal, .swal2-container { display: none !important; }
    }

    /* =============================================
       FORM CONTROLS
       ============================================= */
    .form-control, .form-select {
        border-color: #cbd5e1;
        border-radius: 10px;
        font-size: 0.875rem;
        min-height: 40px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
    }
    .form-control-sm, .form-select-sm {
        min-height: 36px;
        padding: 6px 10px;
        font-size: 0.83rem;
        border-radius: 8px;
    }
    .input-group-text {
        border-color: #cbd5e1;
        font-size: 0.875rem;
    }

    /* =============================================
       PAGE HEADER
       ============================================= */
    .anggaran-header-responsive {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }
    .anggaran-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .anggaran-actions .btn {
        height: 40px;
        display: inline-flex;
        align-items: center;
        padding: 0 18px;
        font-size: 0.875rem;
        font-weight: 600;
        white-space: nowrap;
    }
    @media (max-width: 640px) {
        .anggaran-header-responsive { flex-direction: column; align-items: stretch; }
        .anggaran-actions { flex-direction: column; width: 100%; }
        .anggaran-actions .btn { width: 100%; height: 44px; justify-content: center; }
    }

    /* =============================================
       STAT METRIC CARDS
       ============================================= */
    .anggaran-stat-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        padding: 18px;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .anggaran-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        border-color: #94a3b8;
    }
    .anggaran-stat-card.active-filter {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.18);
    }
    .stat-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon-wrap.icon-warning { background: #fef3c7; border: 1px solid #fde68a; color: #b45309; }
    .stat-icon-wrap.icon-danger  { background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; }
    .stat-icon-wrap.icon-info    { background: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7; }
    .stat-icon-wrap.icon-success { background: #dcfce7; border: 1px solid #bbf7d0; color: #16a34a; }
    .stat-icon-wrap svg { width: 20px; height: 20px; fill: currentColor; }
    .stat-card-row { display: flex; justify-content: space-between; align-items: center; }
    .stat-value {
        font-size: clamp(1.3rem, 2.5vw, 1.6rem);
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.5px;
    }
    .stat-unit { font-size: 0.82rem; font-weight: 600; }
    .stat-note { font-size: 0.73rem; font-weight: 600; }
    @media (max-width: 576px) {
        .anggaran-stat-card { padding: 14px 12px; border-radius: 14px; }
        .stat-icon-wrap { width: 36px; height: 36px; border-radius: 10px; }
        .stat-icon-wrap svg { width: 17px; height: 17px; }
        .stat-value { font-size: 1.2rem; }
    }

    /* =============================================
       REMINDER BOX
       ============================================= */
    .anggaran-reminder-box {
        background: #fff;
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(245,158,11,0.07);
    }
    .anggaran-reminder-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        cursor: pointer;
        gap: 10px;
    }
    .anggaran-reminder-header:hover { background: #fffbeb; border-radius: 12px; }
    .anggaran-reminder-item {
        background: #fffbf2;
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 10px 14px;
        transition: all 0.18s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: nowrap;
    }
    .anggaran-reminder-item:hover { background: #fff; border-color: #f97316; }
    .anggaran-reminder-item .reminder-info { min-width: 0; flex: 1; }
    .anggaran-reminder-item .reminder-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .anggaran-reminder-item .reminder-actions .btn {
        height: 34px;
        display: inline-flex;
        align-items: center;
        padding: 0 12px;
        font-size: 0.81rem;
        font-weight: 600;
        white-space: nowrap;
    }
    @media (max-width: 640px) {
        .anggaran-reminder-item { flex-wrap: wrap; }
        .anggaran-reminder-item .reminder-actions { width: 100%; }
        .anggaran-reminder-item .reminder-actions .btn { flex: 1; height: 38px; justify-content: center; }
    }
    /* Fix: outline button hover — prevent text becoming white/invisible */
    .btn-outline-secondary:hover, .btn-outline-secondary:focus { color: #343a40 !important; }
    .btn-outline-success:hover, .btn-outline-success:focus { color: #fff !important; }
    .btn-outline-primary:hover, .btn-outline-primary:focus { color: #fff !important; }
    .btn-outline-danger:hover, .btn-outline-danger:focus { color: #fff !important; }
    .btn-outline-warning:hover, .btn-outline-warning:focus { color: #212529 !important; }
    .btn-outline-info:hover, .btn-outline-info:focus { color: #fff !important; }
    /* Nota Barang Items List */
    .nota-barang-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
        background: #fafafa;
        transition: background 0.15s;
    }
    .nota-barang-item:hover { background: #f1f5f9; }
    .nota-barang-item .item-name { font-size: 0.83rem; font-weight: 600; color: #334155; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nota-barang-item .item-meta { font-size: 0.72rem; color: #94a3b8; }
    .nota-barang-item .item-price { font-size: 0.83rem; font-weight: 700; color: #0f172a; white-space: nowrap; }
    .nota-barang-item .item-del { width: 22px; height: 22px; border-radius: 6px; border: none; background: transparent; color: #94a3b8; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.15s; font-size: 0.8rem; padding: 0; }
    .nota-barang-item .item-del:hover { background: #fee2e2; color: #dc2626; }

    /* =============================================
       FILTER TABS + SEARCH BAR
       ============================================= */
    .anggaran-filter-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .anggaran-tabs-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: nowrap;
    }
    .anggaran-nav-tabs {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        flex: 1;
        padding-bottom: 2px;
    }
    .anggaran-nav-tabs::-webkit-scrollbar { display: none; }
    .anggaran-nav-tabs .btn-filter {
        white-space: nowrap;
        border-radius: 30px;
        font-size: 0.83rem;
        font-weight: 600;
        padding: 7px 16px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.18s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        flex-shrink: 0;
        cursor: pointer;
    }
    .anggaran-nav-tabs .btn-filter:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .anggaran-nav-tabs .btn-filter.active {
        background: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: 0 3px 10px rgba(13,110,253,0.3);
    }
    .anggaran-nav-tabs .btn-filter.active .badge {
        background: rgba(255,255,255,0.25) !important;
        color: #fff !important;
    }
    .anggaran-nav-tabs .btn-filter .badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 20px;
    }
    /* Search Bar */
    .anggaran-search-wrap {
        position: relative;
        width: 100%;
    }
    .anggaran-search-wrap .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 0.9rem;
        z-index: 2;
    }
    .anggaran-search-wrap input {
        width: 100%;
        height: 42px;
        padding: 0 90px 0 40px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        font-size: 0.875rem;
        color: #334155;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .anggaran-search-wrap input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
    }
    .anggaran-search-wrap input::placeholder { color: #94a3b8; }
    .anggaran-search-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        height: 28px;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .anggaran-search-clear:hover { background: #e2e8f0; color: #0f172a; }
    @media (min-width: 768px) {
        .anggaran-filter-wrapper { flex-direction: row; align-items: center; }
        .anggaran-search-wrap { width: 280px; flex-shrink: 0; }
        .anggaran-tabs-row { flex: 1; overflow: hidden; }
    }

    /* =============================================
       ANGGARAN CARDS
       ============================================= */
    .anggaran-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    .anggaran-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.09);
        border-color: #94a3b8;
        transform: translateY(-2px);
    }
    .anggaran-card .card-header {
        padding: 16px 18px 12px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
    }
    .anggaran-card .card-body {
        padding: 16px 18px;
        flex: 1;
    }
    .anggaran-card .card-footer {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    /* Card metric box */
    .card-metric-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .card-metric-label {
        font-size: 0.69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .card-metric-value {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .card-metric-divider {
        width: 1px;
        background: #e2e8f0;
        align-self: stretch;
        margin: 0 2px;
    }

    /* Card usage bar box */
    .card-usage-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }

    /* Card footer action */
    .card-action-primary { margin-bottom: 10px; }
    .card-action-primary .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 700;
        padding: 0 16px;
        border-radius: 12px;
        transition: all 0.18s;
    }
    .card-action-utility {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .card-action-utility .btn-preview-action {
        flex: 1;
        height: 38px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #334155;
        transition: all 0.18s;
        cursor: pointer;
    }
    .card-action-utility .btn-preview-action:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }
    .card-action-utility .btn-icon-action {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        padding: 0;
        transition: all 0.18s;
        cursor: pointer;
    }
    .card-action-utility .btn-icon-action.btn-wa:hover  { background: #dcfce7; border-color: #22c55e; color: #15803d; }
    .card-action-utility .btn-icon-action.btn-copy:hover{ background: #eff6ff; border-color: #3b82f6; color: #1d4ed8; }
    .card-action-utility .btn-icon-action.btn-delete:hover{ background: #fee2e2; border-color: #ef4444; color: #b91c1c; }

    /* Step Tracker */
    .step-tracker-wrapper {
        position: relative;
        padding: 4px 0 0;
    }
    .step-tracker-line {
        position: absolute;
        top: 15px;
        left: 16px;
        right: 16px;
        height: 3px;
        z-index: 1;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
    }
    .step-tracker-line .progress-bar { height: 100%; }
    .step-tracker-node {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    .step-tracker-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 5px;
        font-size: 0.78rem;
        border: 2.5px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.12);
    }
    .step-tracker-label {
        font-size: 0.67rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #64748b;
        white-space: nowrap;
    }

    /* =============================================
       MODALS
       ============================================= */
    .item-kebutuhan-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        padding: 14px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .item-kebutuhan-card:hover { border-color: #94a3b8; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .mobile-nota-tab-btn {
        flex: 1;
        min-height: 40px;
        padding: 8px 12px;
        font-size: 0.84rem;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #64748b;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 30px;
        cursor: pointer;
    }
    .mobile-nota-tab-btn.active {
        background: #0d6efd;
        color: #fff;
        box-shadow: 0 3px 8px rgba(13,110,253,0.3);
    }
</style>

<div id="printArea" style="display:none;"></div>

<?php
// Perhitungan Ringkasan & Pengingat Anggaran
$countDiajukan = 0;
$countDisetujui = 0;
$countDilaporkan = 0;
$countSelesai = 0;
$totalPlafond = 0;
$totalRealisasi = 0;
$reminderItems = [];

foreach ($pengajuanList as $p) {
    $st = $p['status'] ?? 'draft';
    if ($st === 'diajukan') {
        $countDiajukan++;
        $reminderItems[] = [
            'type' => 'diajukan',
            'title' => 'Rencana Anggaran Belum Ditetapkan',
            'desc' => ($isAdmin ? 'Instansi ' . htmlspecialchars((string)($p['instansi'] ?: '-')) : 'Instansi Anda') . ' • Bulan ' . htmlspecialchars((string)$p['bulan_hijriah']) . ' (Rp ' . number_format((float)$p['total_ajuan'], 0, ',', '.') . ')',
            'badge' => ($p['hari_sejak_ajuan'] ?? 0) . ' Hari Dicatat',
            'badge_color' => ($p['hari_sejak_ajuan'] ?? 0) >= 3 ? 'warning' : 'info',
            'p' => $p
        ];
    } elseif ($st === 'disetujui') {
        $countDisetujui++;
        $hariCair = (int)($p['hari_sejak_cair'] ?? 0);
        $badgeText = $hariCair > 0 ? $hariCair . ' Hari Sejak Dicairkan' : 'Baru Aktif Hari Ini';
        $badgeColor = $hariCair > 7 ? 'danger' : ($hariCair >= 3 ? 'warning' : 'primary');
        $reminderItems[] = [
            'type' => 'disetujui',
            'title' => 'Perlu Lapor Nota Belanja',
            'desc' => ($isAdmin ? 'Instansi ' . htmlspecialchars((string)($p['instansi'] ?: '-')) : 'Instansi Anda') . ' • Bulan ' . htmlspecialchars((string)$p['bulan_hijriah']) . ' — Pagu: Rp ' . number_format((float)$p['total_disetujui'], 0, ',', '.'),
            'badge' => $badgeText,
            'badge_color' => $badgeColor,
            'p' => $p
        ];
    } elseif ($st === 'dilaporkan') {
        $countDilaporkan++;
    } elseif ($st === 'selesai') {
        $countSelesai++;
    }
    
    $totalPlafond += (float)($p['total_disetujui'] ?? 0);
    $totalRealisasi += (float)($p['total_digunakan'] ?? 0);
}
$totalSisa = $totalPlafond - $totalRealisasi;
$totalPendingActions = $countDiajukan + $countDisetujui;
?>

<!-- Header Section -->
<div class="anggaran-header-responsive mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white;">
            <i class="bi bi-wallet2 fs-4"></i>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="mb-0 fw-bold text-dark" style="font-size: clamp(1.2rem, 2.5vw, 1.45rem);">Anggaran Operasional</h4>
                <?php if ($totalPendingActions > 0): ?>
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 flex-shrink-0" style="font-size: 0.75rem;">
                        <i class="bi bi-bell-fill"></i> <?= $totalPendingActions ?> Pengingat
                    </span>
                <?php endif; ?>
            </div>
            <div class="text-muted small fw-medium mt-0.5">
                <?= $isAdmin ? '<span class="badge bg-dark bg-opacity-10 text-dark me-1">Super Admin</span> Mode Seluruh Instansi' : 'Instansi: <strong class="text-primary">' . htmlspecialchars((string)$myPondok) . '</strong>' ?>
            </div>
        </div>
    </div>
    <div class="anggaran-actions">
        <button type="button" class="btn btn-primary rounded-pill shadow-sm" onclick="openPengajuanModal()">
            <i class="bi bi-plus-circle me-2 fs-6"></i> Catat Anggaran Baru
        </button>
        <button type="button" class="btn btn-outline-primary rounded-pill shadow-sm bg-white" onclick="openKopSettingsModal()">
            <i class="bi bi-aspect-ratio me-2 fs-6"></i> Atur Kop Surat
        </button>
    </div>
</div>

<!-- Stat Metric Cards (Ringkasan & Filter Cepat) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="anggaran-stat-card h-100" onclick="filterAnggaranStatus('diajukan', this)">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Rencana Anggaran</span>
                <div class="stat-icon-wrap icon-warning">
                    <svg viewBox="0 0 24 24"><path d="M6 2v6h.01L6 8.01 10 12l-4 4 .01.01H6V22h12v-5.99h-.01L18 16l-4-4 4-3.99-.01-.01H18V2H6zm10 14.5V20H8v-3.5l4-4 4 4zm-4-5l-4-4V4h8v3.5l-4 4z"/></svg>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2 my-1.5">
                <span class="stat-value text-dark"><?= $countDiajukan ?></span>
                <span class="stat-unit text-muted">Catatan</span>
            </div>
            <div class="small text-warning-emphasis fw-medium text-truncate mt-1" style="font-size: 0.75rem;">
                Anggaran direncanakan
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="anggaran-stat-card h-100 <?= $countDisetujui > 0 ? 'border-danger-subtle' : '' ?>" style="<?= $countDisetujui > 0 ? 'background: #fff8f8;' : '' ?>" onclick="filterAnggaranStatus('disetujui', this)">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-danger small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Perlu Lapor Nota</span>
                <div class="stat-icon-wrap icon-danger">
                    <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2 my-1.5">
                <span class="stat-value text-danger"><?= $countDisetujui ?></span>
                <span class="stat-unit text-danger">Belum Lapor</span>
            </div>
            <div class="small text-danger fw-semibold text-truncate mt-1" style="font-size: 0.75rem;">
                Dana aktif, butuh nota
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="anggaran-stat-card h-100" onclick="filterAnggaranStatus('dilaporkan', this)">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Dilaporkan</span>
                <div class="stat-icon-wrap icon-info">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2 my-1.5">
                <span class="stat-value text-dark"><?= $countDilaporkan ?></span>
                <span class="stat-unit text-muted">Menunggu</span>
            </div>
            <div class="small text-info fw-medium text-truncate mt-1" style="font-size: 0.75rem;">
                Nota tercatat
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="anggaran-stat-card h-100" onclick="filterAnggaranStatus('selesai', this)">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Realisasi / Sisa</span>
                <div class="stat-icon-wrap icon-success">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
            </div>
            <div class="my-1.5">
                <div class="stat-value text-success text-truncate" style="font-size: clamp(1.15rem, 2.2vw, 1.45rem);">Rp <?= number_format($totalRealisasi, 0, ',', '.') ?></div>
            </div>
            <div class="small text-muted text-truncate mt-1" style="font-size: 0.75rem;">
                Sisa: <strong class="<?= $totalSisa < 0 ? 'text-danger' : 'text-success' ?>">Rp <?= number_format($totalSisa, 0, ',', '.') ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Smart Reminder & Follow-up Action Box (Pusat Pengingat Cerdas) -->
<?php if (!empty($reminderItems)): ?>
<div class="anggaran-reminder-box mb-4">
    <div class="anggaran-reminder-header" data-bs-toggle="collapse" data-bs-target="#collapseReminderDetails" aria-expanded="false">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-warning bg-opacity-20 text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                <i class="bi bi-bell-fill" style="font-size: 0.85rem;"></i>
            </div>
            <div>
                <span class="fw-bold text-dark" style="font-size: 0.88rem;">Pusat Pengingat Anggaran</span>
                <span class="badge bg-warning text-dark rounded-pill ms-2" style="font-size: 0.72rem;"><?= count($reminderItems) ?> Perlu Tindak Lanjut</span>
            </div>
        </div>
        <i class="bi bi-chevron-down text-muted" style="font-size: 0.85rem; transition: transform 0.2s;" id="reminderChevron"></i>
    </div>

    <div class="collapse" id="collapseReminderDetails">
        <div class="d-flex flex-column gap-2 px-3 pb-3">
            <?php foreach ($reminderItems as $rem): 
                $pData = $rem['p'];
            ?>
            <div class="anggaran-reminder-item">
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="rounded-circle bg-<?= $rem['badge_color'] ?> bg-opacity-10 text-<?= $rem['badge_color'] ?> d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.85rem;">
                        <i class="bi bi-<?= $rem['type'] === 'diajukan' ? 'hourglass-split' : 'exclamation-diamond-fill' ?>"></i>
                    </div>
                </div>
                <div class="reminder-info">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <strong class="text-dark" style="font-size: 0.84rem;"><?= $rem['title'] ?></strong>
                        <span class="badge bg-<?= $rem['badge_color'] ?>-subtle text-<?= $rem['badge_color'] ?> rounded-pill" style="font-size: 0.68rem;"><?= $rem['badge'] ?></span>
                    </div>
                    <div class="text-muted text-truncate" style="font-size: 0.75rem; max-width: 300px;"><?= $rem['desc'] ?></div>
                </div>
                <div class="reminder-actions">
                    <?php if ($rem['type'] === 'diajukan'): ?>
                        <button class="btn btn-warning btn-sm rounded-pill text-dark" onclick='reviewPengajuan(<?= htmlspecialchars(json_encode($pData), ENT_QUOTES, "UTF-8") ?>)'>
                            <i class="bi bi-check2-circle me-1"></i> Tetapkan
                        </button>
                    <?php elseif ($rem['type'] === 'disetujui'): ?>
                        <button class="btn btn-success btn-sm rounded-pill" onclick="laporNota(<?= $pData['id'] ?>)">
                            <i class="bi bi-receipt me-1"></i> Lapor
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-success btn-sm rounded-pill" onclick='openWhatsAppReminderModal(<?= htmlspecialchars(json_encode($pData), ENT_QUOTES, "UTF-8") ?>)' title="Kirim Pengingat WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filter Tabs & Search Bar -->
<div class="anggaran-filter-wrapper mb-4">
    <div class="anggaran-tabs-row">
        <div class="anggaran-nav-tabs">
            <button type="button" class="btn-filter active" onclick="filterAnggaranStatus('all', this)">
                <i class="bi bi-grid-fill"></i> Semua <span class="badge bg-secondary"><?= count($pengajuanList) ?></span>
            </button>
            <button type="button" class="btn-filter" onclick="filterAnggaranStatus('diajukan', this)">
                <i class="bi bi-hourglass-split"></i> Rencana Anggaran <span class="badge bg-warning text-dark"><?= $countDiajukan ?></span>
            </button>
            <button type="button" class="btn-filter" onclick="filterAnggaranStatus('disetujui', this)">
                <i class="bi bi-receipt"></i> Perlu Lapor Nota <span class="badge bg-danger"><?= $countDisetujui ?></span>
            </button>
            <button type="button" class="btn-filter" onclick="filterAnggaranStatus('dilaporkan', this)">
                <i class="bi bi-file-earmark-check"></i> Dilaporkan <span class="badge bg-info text-white"><?= $countDilaporkan ?></span>
            </button>
            <button type="button" class="btn-filter" onclick="filterAnggaranStatus('selesai', this)">
                <i class="bi bi-check-circle-fill"></i> Selesai <span class="badge bg-success"><?= $countSelesai ?></span>
            </button>
        </div>
    </div>
    <div class="anggaran-search-wrap">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="searchAnggaranInput" placeholder="Cari instansi/bulan hijriah..." oninput="searchAnggaranCards(this.value)">
        <button class="anggaran-search-clear" type="button" onclick="document.getElementById('searchAnggaranInput').value=''; searchAnggaranCards('');">
            <i class="bi bi-x-lg"></i> Hapus
        </button>
    </div>
</div>


<!-- Grid Daftar Pengajuan Anggaran -->
<div class="row g-3 g-md-4 mb-4" id="anggaranCardContainer">
    <?php if (empty($pengajuanList)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
            <h5>Belum ada data catatan anggaran</h5>
            <p>Silakan catat rencana anggaran baru untuk memulai pencatatan operasional.</p>
        </div>
    <?php else: ?>
        <?php foreach ($pengajuanList as $p): 
            $statusColors = [
                'draft' => 'secondary',
                'diajukan' => 'warning',
                'disetujui' => 'primary',
                'dilaporkan' => 'info',
                'ditolak' => 'danger',
                'selesai' => 'success'
            ];
            $statusLabels = [
                'draft' => 'Draft',
                'diajukan' => 'Rencana',
                'disetujui' => 'Ditetapkan',
                'dilaporkan' => 'Dilaporkan',
                'ditolak' => 'Dibatalkan',
                'selesai' => 'Selesai'
            ];
            $color = $statusColors[$p['status']] ?? 'primary';
            $displayStatus = $statusLabels[$p['status']] ?? strtoupper($p['status']);
            $pct = $p['total_disetujui'] > 0 ? min(100, ($p['total_digunakan'] / $p['total_disetujui']) * 100) : 0;
            $isApproved = in_array($p['status'], ['disetujui', 'dilaporkan', 'selesai']);
            
            // Reminder Urgency Tag with clean Bootstrap icons (no emojis)
            $reminderBadgeHtml = '';
            if ($p['status'] === 'disetujui') {
                $hariCair = (int)($p['hari_sejak_cair'] ?? 0);
                if ($hariCair > 7) {
                    $reminderBadgeHtml = '<div class="alert alert-danger py-2 px-3 mb-3 rounded-3 d-flex align-items-center gap-2 small fw-bold" style="font-size:0.8rem;"><i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i> <span>Mendesak: Belum Lapor Nota (' . $hariCair . ' Hari Aktif)</span></div>';
                } elseif ($hariCair >= 3) {
                    $reminderBadgeHtml = '<div class="alert alert-warning py-2 px-3 mb-3 rounded-3 d-flex align-items-center gap-2 small fw-bold" style="font-size:0.8rem;"><i class="bi bi-clock-history fs-6 flex-shrink-0"></i> <span>Pengingat: Segera Lapor Nota (' . $hariCair . ' Hari)</span></div>';
                } else {
                    $reminderBadgeHtml = '<div class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2.5 mb-3 rounded-pill d-inline-flex align-items-center gap-1.5 small" style="font-size:0.75rem;"><i class="bi bi-stopwatch"></i> Baru Aktif (' . $hariCair . ' Hari)</div>';
                }
            } elseif ($p['status'] === 'diajukan') {
                $hariAjuan = (int)($p['hari_sejak_ajuan'] ?? 0);
                $reminderBadgeHtml = '<div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1.5 px-2.5 mb-3 rounded-pill d-inline-flex align-items-center gap-1.5 small" style="font-size:0.75rem;"><i class="bi bi-hourglass-split"></i> Rencana Anggaran (' . $hariAjuan . ' Hari)</div>';
            }
        ?>
        <div class="col-md-6 col-lg-4 anggaran-item-card" data-status="<?= htmlspecialchars((string)$p['status']) ?>" data-search="<?= strtolower(htmlspecialchars((string)($p['instansi'] . ' ' . $p['bulan_hijriah'] . ' ' . $p['id'] . ' ' . $p['status']))) ?>">
            <div class="card anggaran-card h-100 overflow-hidden d-flex flex-column">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-start">
                    <div>
                        <?php if($isAdmin): ?>
                            <span class="badge bg-dark bg-opacity-10 text-dark mb-1.5 px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;"><?= htmlspecialchars((string)$p['instansi']) ?></span>
                        <?php endif; ?>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;">
                            <i class="bi bi-calendar-event text-primary me-2"></i><?= htmlspecialchars((string)$p['bulan_hijriah']) ?>
                        </h5>
                        <div class="text-muted small mt-0.5" style="font-size: 0.78rem;">ID Dokumen: <strong class="text-secondary">#ANGG-<?= str_pad((string)$p['id'], 4, '0', STR_PAD_LEFT) ?></strong></div>
                    </div>
                    <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle px-3 py-1.5 rounded-pill text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.8px;">
                        <?= htmlspecialchars($displayStatus) ?>
                    </span>
                </div>
                
                <div class="card-body">
                    <!-- Dynamic Reminder Notice -->
                    <?php if ($reminderBadgeHtml): ?>
                        <div class="mb-3"><?= $reminderBadgeHtml ?></div>
                    <?php endif; ?>

                    <!-- Summary Metric Box (Rencana vs Ditetapkan) -->
                    <div class="card-metric-box d-flex align-items-stretch gap-0">
                        <div class="flex-fill pe-3">
                            <div class="card-metric-label">Total Rencana</div>
                            <div class="card-metric-value text-dark" style="font-size:0.95rem;">Rp <?= number_format((float)$p['total_ajuan'], 0, ',', '.') ?></div>
                        </div>
                        <div class="card-metric-divider"></div>
                        <div class="flex-fill ps-3">
                            <div class="card-metric-label text-success">Pagu Ditetapkan</div>
                            <div class="card-metric-value text-success">Rp <?= number_format((float)$p['total_disetujui'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    
                    <?php if ($isApproved): ?>
                    <div class="card-usage-box">
                        <div class="d-flex justify-content-between small mb-1.5 align-items-center">
                            <span class="fw-bold text-dark" style="font-size: 0.78rem;">
                                <i class="bi bi-pie-chart text-primary me-1"></i> Terpakai: <span class="text-primary">Rp <?= number_format((float)$p['total_digunakan'], 0, ',', '.') ?></span>
                            </span>
                            <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 0.76rem;"><?= round($pct) ?>%</span>
                        </div>
                        <div class="progress rounded-pill bg-light mb-2" style="height: 8px;">
                            <div class="progress-bar <?= $pct > 90 ? 'bg-danger' : ($pct > 75 ? 'bg-warning' : 'bg-primary') ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $pct ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small flex-wrap gap-1 pt-1 border-top border-light">
                            <span class="d-flex gap-1.5 flex-wrap">
                                <?php if($p['durasi_cair'] !== '-'): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-0.5" style="font-size: 0.68rem;" title="Lama Waktu Ditetapkan"><i class="bi bi-stopwatch me-1"></i>Aktif: <?= $p['durasi_cair'] ?></span>
                                <?php endif; ?>
                                <?php if($p['durasi_lapor'] !== '-'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-0.5" style="font-size: 0.68rem;" title="Lama Lapor Nota"><i class="bi bi-clock-history me-1"></i>Lapor: <?= $p['durasi_lapor'] ?></span>
                                <?php endif; ?>
                            </span>
                            <span style="font-size: 0.8rem;"><span class="text-muted">Sisa: </span><strong class="<?= $p['sisa_anggaran'] < 0 ? 'text-danger' : 'text-success' ?>">Rp <?= number_format((float)$p['sisa_anggaran'], 0, ',', '.') ?></strong></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Status Step Tracker -->
                    <div class="mt-3 pt-2.5 border-top step-tracker-wrapper">
                        <div class="step-tracker-line">
                            <?php 
                                $w = 0; 
                                if($p['status'] === 'diajukan') $w = 0; 
                                if($p['status'] === 'disetujui') $w = 33.33; 
                                if($p['status'] === 'dilaporkan') $w = 66.66;
                                if($p['status'] === 'selesai') $w = 100;
                            ?>
                            <div class="progress-bar bg-success h-100 rounded" style="width: <?= $w ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between position-relative">
                            <div class="step-tracker-node">
                                <div class="step-tracker-circle bg-<?= $p['status']!=='draft' ? 'success' : 'secondary' ?> text-white"><i class="bi bi-check2"></i></div>
                                <div class="step-tracker-label">Direncanakan</div>
                            </div>
                            <div class="step-tracker-node">
                                <div class="step-tracker-circle bg-<?= in_array($p['status'], ['disetujui', 'dilaporkan', 'selesai']) ? 'success' : 'secondary' ?> text-white"><i class="bi bi-check2"></i></div>
                                <div class="step-tracker-label">Ditetapkan</div>
                            </div>
                            <div class="step-tracker-node">
                                <div class="step-tracker-circle bg-<?= in_array($p['status'], ['dilaporkan', 'selesai']) ? 'success' : 'secondary' ?> text-white"><i class="bi bi-file-earmark-check"></i></div>
                                <div class="step-tracker-label">Dilaporkan</div>
                            </div>
                            <div class="step-tracker-node">
                                <div class="step-tracker-circle bg-<?= $p['status']==='selesai' ? 'success' : 'secondary' ?> text-white"><i class="bi bi-flag-fill"></i></div>
                                <div class="step-tracker-label">Selesai</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Structured 2-Tier Card Footer (Spacious & Clean) -->
                <div class="card-footer border-top border-secondary-subtle">
                    <!-- Tier 1: Primary Action Button -->
                    <div class="card-action-primary">
                        <?php if ($p['status'] === 'diajukan'): ?>
                            <div class="d-flex gap-2 w-100">
                                <button class="btn btn-warning flex-grow-1 shadow-xs text-dark" onclick='reviewPengajuan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)' title="Tetapkan Anggaran / Plafond">
                                    <i class="bi bi-check2-circle me-1.5 fs-6"></i> Tetapkan Anggaran
                                </button>
                                <button class="btn btn-info text-white shadow-xs px-3" onclick='editPengajuan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)' title="Edit Rencana Anggaran">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </button>
                            </div>
                        <?php elseif ($p['status'] === 'disetujui'): ?>
                            <div class="d-flex gap-2 w-100">
                                <button class="btn btn-success flex-grow-1 shadow-xs" onclick="laporNota(<?= $p['id'] ?>)">
                                    <i class="bi bi-receipt me-2 fs-6"></i> Lapor Nota Belanja
                                </button>
                                <button class="btn btn-info text-white shadow-xs px-3.5" onclick="tandaiDilaporkan(<?= $p['id'] ?>)" title="Tandai Sebagai Dilaporkan">
                                    <i class="bi bi-send-fill me-1.5"></i> Kirim
                                </button>
                            </div>
                        <?php elseif ($p['status'] === 'dilaporkan'): ?>
                            <div class="d-flex gap-2 w-100">
                                <button class="btn btn-success flex-grow-1 shadow-xs" onclick="laporNota(<?= $p['id'] ?>)">
                                    <i class="bi bi-receipt me-2 fs-6"></i> Cek / Tambah Nota
                                </button>
                                <button class="btn btn-primary shadow-xs px-3.5" onclick="tandaiSelesai(<?= $p['id'] ?>)" title="Selesaikan Anggaran">
                                    <i class="bi bi-flag-fill me-1.5"></i> Selesai
                                </button>
                            </div>
                        <?php elseif ($p['status'] === 'selesai'): ?>
                            <button class="btn btn-outline-success w-100 bg-white shadow-xs" onclick='viewDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                                <i class="bi bi-check2-all me-2 fs-6"></i> Selesai & Terlapor
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary w-100 shadow-xs" onclick='viewDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                                <i class="bi bi-eye me-2 fs-6"></i> Lihat Detail
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Tier 2: Auxiliary / Utility Action Bar -->
                    <div class="card-action-utility w-100">
                        <button class="btn btn-preview-action" onclick='viewDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                            <i class="bi bi-eye me-1.5 text-primary fs-6"></i> Preview Detil
                        </button>
                        <button class="btn btn-icon-action btn-wa text-success" onclick='openWhatsAppReminderModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)' title="Kirim Pengingat WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <button class="btn btn-icon-action btn-copy text-primary" onclick='copyPengajuan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)' title="Duplikat / Salin Catatan">
                            <i class="bi bi-files"></i>
                        </button>
                        <button class="btn btn-icon-action btn-delete text-danger" onclick="deletePengajuan(<?= $p['id'] ?>)" title="Hapus Catatan Anggaran">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal WhatsApp Reminder -->
<div class="modal fade" id="modalWhatsAppReminder" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white border-0 px-4 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-whatsapp me-2"></i>Kirim Pengingat WhatsApp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4 bg-light">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1.5">Nomor WhatsApp Tujuan (Opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-secondary-subtle px-3"><i class="bi bi-telephone"></i></span>
                        <input type="text" id="wa_nomor_tujuan" class="form-control bg-white border-secondary-subtle" placeholder="Contoh: 08123456789 atau 628123456789">
                    </div>
                    <div class="form-text small text-muted mt-1">Kosongkan jika ingin memilih kontak langsung di aplikasi WhatsApp.</div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1.5">Draft Pesan Pengingat</label>
                    <textarea id="wa_pesan_text" class="form-control bg-white border-secondary-subtle p-3 rounded-3" rows="8" style="font-size: 0.86rem; font-family: monospace; line-height: 1.5;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 py-3 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-medium" onclick="copyWhatsAppReminderText()">
                    <i class="bi bi-clipboard me-1.5"></i> Salin Teks
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="sendWhatsAppReminderDirect()">
                    <i class="bi bi-send me-1.5"></i> Buka WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Anggaran Baru -->
<div class="modal fade" id="modalPengajuan" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 px-3 px-md-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-file-earmark-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalPengajuanTitle" style="font-size: 1.05rem;">Catat Rencana Anggaran</h5>
                        <div class="text-muted small" style="font-size: 0.75rem;">Isi rincian rencana kebutuhan operasional bulanan</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-3 px-md-4 py-3 bg-light">
                <input type="hidden" id="form_pengajuan_id">
                <div class="card border-0 bg-white shadow-xs rounded-3 p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase" style="font-size: 0.72rem;">Bulan Hijriah</label>
                            <select class="form-select form-select-sm" id="form_bulan">
                                <?php foreach($hijriMonths as $m): ?>
                                    <option value="<?= $m ?>" <?= $m === $currentHijriMonth ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase" style="font-size: 0.72rem;">Tahun Hijriah</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control form-control-sm" id="form_tahun" value="<?= $currentHijriYear ?>">
                                <span class="input-group-text bg-light text-muted">H</span>
                            </div>
                        </div>
                        <?php if ($isAdmin): ?>
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase" style="font-size: 0.72rem;">Pilih Pondok / Instansi</label>
                            <select class="form-select form-select-sm" id="form_instansi">
                                <option value="">-- Pilih Pondok --</option>
                                <?php foreach($pondokList as $ins): ?>
                                    <option value="<?= htmlspecialchars($ins['nama']) ?>"><?= htmlspecialchars($ins['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                        <i class="bi bi-card-checklist text-primary me-1"></i> Rincian Kebutuhan
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs" onclick="addItemRow()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item
                    </button>
                </div>

                <div id="tbodyItems" class="d-flex flex-column gap-2 mb-3">
                    <!-- JS injected items -->
                </div>
                
                <div class="d-flex justify-content-center mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-medium shadow-xs px-3 py-1.5" onclick="addItemRow()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Kebutuhan Baru
                    </button>
                </div>
                
                <div class="card border-0 bg-primary bg-opacity-10 rounded-3 shadow-xs">
                    <div class="card-body px-3 py-2.5 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">TOTAL RENCANA ANGGARAN :</span>
                        <span class="fw-bold text-primary fs-5" id="lblTotalAjuan">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-3 px-md-4 py-2.5 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 text-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSubmitPengajuan" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm fw-bold py-1.5" onclick="submitPengajuan()">Simpan Anggaran</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tetapkan Plafond Anggaran -->
<div class="modal fade" id="modalReview" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning border-0 px-3 px-md-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-dark bg-opacity-10 text-dark d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-check2-circle fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" style="font-size: 1.05rem;">Tetapkan Plafond Anggaran Operasional</h5>
                        <div class="text-dark text-opacity-75 small" style="font-size: 0.75rem;">Tetapkan pagu nominal anggaran operasional instansi</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-3 px-md-4 py-3 bg-light">
                <input type="hidden" id="r_pengajuan_id">
                <div class="card border-0 bg-white shadow-xs rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small" style="font-size: 0.72rem;">Instansi</div>
                            <h6 class="fw-bold mb-0 text-dark" id="r_instansi"></h6>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small" style="font-size: 0.72rem;">Bulan</div>
                            <h6 class="fw-bold mb-0 text-primary" id="r_bulan"></h6>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 bg-white mb-3 shadow-xs rounded-3">
                    <div class="card-body px-3 py-2.5 d-flex justify-content-between align-items-center">
                        <div class="fw-bold text-dark small"><i class="bi bi-sliders text-warning me-1.5"></i>Mode Input Nominal:</div>
                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                            <input class="form-check-input m-0" style="cursor:pointer" type="checkbox" id="modeLumpsum" onchange="toggleLumpsum()">
                            <label class="form-check-label fw-medium text-dark small" style="cursor:pointer" for="modeLumpsum">Tulis Total Keseluruhan Saja</label>
                        </div>
                    </div>
                </div>

                <div id="lumpsumContainer" style="display:none;" class="mb-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 border border-success border-opacity-25 text-center">
                        <label class="text-success small fw-bold mb-1.5">Total Nominal Pagu / Plafond (Rp)</label>
                        <input type="number" id="r_totalLumpsum" class="form-control form-control-sm text-center fw-bold text-success fs-4 border-success shadow-xs" placeholder="0">
                        <div class="small text-muted mt-1" style="font-size: 0.75rem;">Harga per-item rincian akan disesuaikan dengan nilai total akhir yang ditetapkan.</div>
                    </div>
                </div>

                <div class="table-responsive bg-white rounded-3 shadow-xs mb-3 border" id="r_tableItems">
                    <table class="table table-bordered mb-0 align-middle" style="font-size: 0.8125rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2">Item Kebutuhan</th>
                                <th class="py-2">Rencana</th>
                                <th width="180" class="bg-warning bg-opacity-10 text-dark py-2">Ditetapkan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="r_tbodyItems"></tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="text-end fw-bold py-2">TOTAL :</td>
                                <td class="fw-bold text-secondary py-2" id="r_totalAjuan"></td>
                                <td class="fw-bold text-success fs-6 py-2" id="r_totalAcc">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mb-2">
                    <label class="text-muted small fw-bold mb-1" style="font-size: 0.72rem;">Catatan Tambahan (Opsional)</label>
                    <textarea id="r_catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan peruntukan anggaran, keterangan saldo, dll..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 px-3 px-md-4 py-2.5 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-xs fw-bold py-1.5" onclick="approvePengajuan('ditolak')">Batalkan Anggaran</button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-4 shadow-xs fw-bold py-1.5" onclick="approvePengajuan('disetujui')">Simpan & Tetapkan Anggaran</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lapor Nota -->
<div class="modal fade" id="modalNota" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white border-0 px-3 px-md-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-white bg-opacity-20 text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-receipt fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="font-size: 1.05rem;">Laporan Nota Pembelanjaan</h5>
                        <div class="text-white-50 small" style="font-size: 0.75rem;">Unggah bukti kuitansi & rincian belanja operasional</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-3 px-md-4 py-3 bg-light">
                <input type="hidden" id="n_pengajuan_id">
                <input type="hidden" id="n_nota_id">

                <!-- Mobile Segmented View Switcher (Visible on Mobile Only) -->
                <div class="d-md-none mb-3">
                    <div class="d-flex p-1 bg-white rounded-pill border shadow-xs">
                        <button type="button" class="mobile-nota-tab-btn active" id="tabMobileNotaForm" onclick="switchMobileNotaTab('form')">
                            <i class="bi bi-plus-circle me-1"></i> Input Nota Baru
                        </button>
                        <button type="button" class="mobile-nota-tab-btn" id="tabMobileNotaList" onclick="switchMobileNotaTab('list')">
                            <i class="bi bi-receipt me-1"></i> Daftar Nota (<span id="mobileNotaCount">0</span>)
                        </button>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Sisi Kiri: Daftar Nota Masuk -->
                    <div class="col-12 col-md-7" id="colNotaList">
                        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                                    <i class="bi bi-receipt-cutoff text-success me-1"></i> Daftar Nota Masuk
                                </h6>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-medium" style="font-size: 0.75rem;" onclick="printSemuaNota()">
                                    <i class="bi bi-printer me-1"></i> Cetak Laporan
                                </button>
                            </div>
                            <div id="notaListContainer" class="d-flex flex-column gap-2" style="max-height: calc(100vh - 280px); overflow-y: auto; padding-right: 4px;">
                                <div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat nota...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Form Tambah/Edit Nota -->
                    <div class="col-12 col-md-5" id="colNotaForm">
                        <div class="card border-0 shadow-xs rounded-3 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold text-primary mb-0" style="font-size: 0.92rem;" id="lblNotaFormTitle">
                                    <i class="bi bi-plus-square me-1.5"></i> Tambah Nota Baru
                                </h6>
                                <button type="button" class="btn btn-sm btn-light py-1 px-2.5 rounded-pill text-muted small border shadow-xs" onclick="resetNotaForm()" title="Bersihkan Form">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                            </div>
                            
                            <div class="row g-2.5 mb-3">
                                <div class="col-12 col-sm-6">
                                    <label class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Nomor/Label Nota *</label>
                                    <input type="text" id="n_nomor" class="form-control form-control-sm" placeholder="Misal: Nota 1" onkeydown="notaInputKeydown(event, 'n_tanggal', null)">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Tanggal Belanja *</label>
                                    <input type="date" id="n_tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" onkeydown="notaInputKeydown(event, 'n_bagian_nota', 'n_nomor')">
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Bagian / Divisi (Opsional)</label>
                                    <input type="text" id="n_bagian_nota" class="form-control form-control-sm" placeholder="Pilih dari daftar atau ketik baru" list="listBagianNota" onkeydown="notaInputKeydown(event, 'nb_nama', 'n_tanggal')">
                                    <datalist id="listBagianNota"></datalist>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Foto Bukti Kuitansi (Opsional)</label>
                                    <input type="file" id="n_file" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>
                            
                            <!-- Rincian Barang Box -->
                            <div class="bg-light p-3 rounded-3 border border-secondary-subtle mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="text-dark small fw-bold text-uppercase mb-0" style="font-size: 0.75rem;"><i class="bi bi-box-seam me-1 text-primary"></i> Input Barang Nota</label>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12">
                                        <label class="small text-muted mb-0.5" style="font-size:0.68rem;">Nama Barang / Pembelian *</label>
                                        <input type="text" id="nb_nama" class="form-control form-control-sm" placeholder="Misal: Konsumsi / ATK" onkeydown="notaInputKeydown(event, 'nb_qty', 'n_bagian_nota')">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-0.5" style="font-size:0.68rem;">Jumlah (Qty)</label>
                                        <input type="number" id="nb_qty" class="form-control form-control-sm text-center" placeholder="1" value="1" onkeydown="notaInputKeydown(event, 'nb_satuan', 'nb_nama')">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-0.5" style="font-size:0.68rem;">Satuan</label>
                                        <input type="text" id="nb_satuan" class="form-control form-control-sm" placeholder="Pcs/Bln" onkeydown="notaInputKeydown(event, 'nb_harga', 'nb_qty')">
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted mb-0.5" style="font-size:0.68rem;">Harga Satuan (Rp) *</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white text-muted">Rp</span>
                                            <input type="number" id="nb_harga" class="form-control form-control-sm" placeholder="0" onkeydown="notaInputKeydown(event, 'nb_bagian', 'nb_satuan')">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted mb-0.5" style="font-size:0.68rem;">Bagian / Seksi (Opsional)</label>
                                        <input type="text" id="nb_bagian" class="form-control form-control-sm" placeholder="Misal: Dapur / Kantor" onkeydown="notaInputKeydown(event, 'ADD_BARANG', 'nb_harga')">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button class="btn btn-primary w-100 fw-bold py-1.5 shadow-xs rounded-pill" onclick="addBarangNota()">
                                            <i class="bi bi-plus-circle me-1.5"></i> Tambah Barang Ke Nota
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex flex-column gap-1 mt-2" id="tblBarangNota" style="max-height: 160px; overflow-y: auto;"></div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                                    <span class="small text-muted fw-bold">TOTAL NOTA:</span>
                                    <span id="nb_total" class="text-success fw-bold fs-6">Rp 0</span>
                                </div>
                            </div>
                            <button id="btnSimpanNota" class="btn btn-success w-100 rounded-pill fw-bold shadow-xs py-2.5" onclick="simpanNota()">
                                <i class="bi bi-cloud-upload me-1.5"></i> Simpan Nota (Ctrl+Enter)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-3 px-md-4 py-2 bg-white d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Standalone Floating Studio & Kanvas Pengaturan Kop Surat (Direct High-Z-Index Overlay) -->
<div id="studioKopOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999999; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="position: relative; width: 95vw; max-width: 1180px; height: 88vh; max-height: 88vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); display: flex; flex-direction: column; overflow: hidden; z-index: 10000000;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; background: #0f172a; color: #ffffff; padding: 14px 24px; flex-shrink: 0; border-bottom: 1px solid #334155;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(13, 110, 253, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-aspect-ratio"></i>
                </div>
                <div>
                    <h5 style="font-weight: 700; margin: 0; font-size: 16px; color: #ffffff;">Studio & Kanvas Pengatur Posisi Kop Surat</h5>
                    <div style="font-size: 12px; color: #94a3b8;">Sesuaikan ukuran, posisi margin, perataan, dan format kop surat secara visual & realtime</div>
                </div>
            </div>
            <button type="button" style="width: 34px; height: 34px; border-radius: 50%; border: 1px solid #475569; background: transparent; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onclick="closeKopSettingsModal()" onmouseover="this.style.background='#ef4444'; this.style.borderColor='#ef4444';" onmouseout="this.style.background='transparent'; this.style.borderColor='#475569';">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <!-- Body (2 Columns: Left Live Canvas, Right Controls) -->
        <div style="flex: 1; min-height: 0; display: flex; overflow: hidden; background: #f1f5f9;">
            <!-- Sisi Kiri: Visual Live Canvas -->
            <div style="flex: 7; background: #cbd5e1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; align-items: center;">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 650px; margin-bottom: 12px;">
                    <span style="background: #1e293b; color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-eye"></i> Live Paper Canvas (A4 Simulation)
                    </span>
                    <div class="btn-group btn-group-sm shadow-sm">
                        <button type="button" id="btnPreviewPengajuan" class="btn btn-primary active btn-sm" style="font-size: 12px;" onclick="previewCanvasDoc('pengajuan')">Preview Rencana</button>
                        <button type="button" id="btnPreviewLaporan" class="btn btn-outline-secondary bg-white btn-sm" style="font-size: 12px;" onclick="previewCanvasDoc('laporan')">Preview Laporan</button>
                    </div>
                </div>
                
                <!-- Simulated Paper Document -->
                <div id="canvasPaper" style="width: 100%; max-width: 650px; background: #ffffff; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 20px 25px; font-family: 'Times New Roman', Times, serif; color: #000000; border: 1px solid #e2e8f0; transition: padding 0.2s;">
                    <!-- Live Kop Container inside canvas -->
                    <div id="canvasKopWrapper" style="transition: all 0.2s;">
                        <!-- Rendered dynamically by updateCanvasPreview() -->
                    </div>
                    
                    <!-- Sample Content Preview -->
                    <div id="canvasDocContent">
                        <div style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
                            <h5 style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin: 0; font-size: 15px; text-transform: uppercase;">
                                RENCANA ANGGARAN OPERASIONAL
                            </h5>
                            <h6 style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin: 3px 0 0 0; font-size: 14px; text-transform: uppercase;">
                                PEMBIMBING LUAR NEGERI BULAN RABIUL AKHIR 1448 H
                            </h6>
                            <div style="font-family: 'Times New Roman', Times, serif; font-style: italic; margin-top: 5px; font-size: 12.5px; color: #444;">
                                Saturday, September 12, 2026
                            </div>
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 12px; border: 1.5px solid #000; margin-bottom: 20px;">
                            <thead>
                                <tr style="border-bottom: 1.5px solid #000; background-color: #fafafa;">
                                    <th style="border: 1px solid #000; padding: 5px; width: 35px; text-align: center;">No</th>
                                    <th style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Nama Barang</th>
                                    <th style="border: 1px solid #000; padding: 5px; width: 50px; text-align: center;">Jumlah</th>
                                    <th style="border: 1px solid #000; padding: 5px; width: 60px; text-align: center;">Satuan</th>
                                    <th style="border: 1px solid #000; padding: 5px 8px; width: 110px; text-align: center;">Harga Barang</th>
                                    <th style="border: 1px solid #000; padding: 5px 8px; width: 110px; text-align: center;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                                    <td style="border: 1px solid #000; padding: 4px 8px;">Kebutuhan Operasional Bulanan</td>
                                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                                    <td style="border: 1px solid #000; padding: 4px 8px; text-align: center; font-style: italic;">Bulan</td>
                                    <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Rp350.000,00</td>
                                    <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Rp350.000,00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 1.5px solid #000; font-weight: bold;">
                                    <td colspan="5" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Total Keseluruhan</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Rp350.000,00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Sisi Kanan: Controls Panel -->
            <div style="flex: 5; background: #ffffff; padding: 20px 24px; overflow-y: auto; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column;">
                <h6 style="font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-sliders text-primary"></i> Parameter Posisi & Dimensi Kop
                </h6>
                
                <!-- Pilihan Pondok (Super Admin) -->
                <?php if($isAdmin && !empty($pondokList)): ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">Pilih Pondok / Instansi Target</label>
                    <select id="cfg_pondok" class="form-select form-select-sm" onchange="onConfigPondokChange()">
                        <option value="default">Semua Pondok (Default Global)</option>
                        <?php foreach($pondokList as $pl): ?>
                            <option value="<?= htmlspecialchars($pl['nama']) ?>"><?= htmlspecialchars($pl['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                    <input type="hidden" id="cfg_pondok" value="<?= htmlspecialchars($myPondok ?: 'default') ?>">
                <?php endif; ?>

                <!-- Mode Kop -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">Tipe / Model Kop Surat</label>
                    <select id="cfg_mode" class="form-select form-select-sm" onchange="updateCanvasPreview()">
                        <option value="auto">Otomatis (Cek Gambar Upload Instansi -> Fallback Kotak Baku)</option>
                        <option value="box_only">Paksa Kotak Format Baku (Border Biru - Teks Arab/Inggris)</option>
                        <option value="image_only">Paksa Gambar Kop Instansi Resmi Saja</option>
                        <option value="none">Sembunyikan Kop (Kertas Sudah Ada Kop Cetak)</option>
                    </select>
                </div>

                <!-- Presets -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">Preset Cepat</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2" style="font-size: 0.75rem;" onclick="applyKopPreset('exact_table')">Presisi 100% Pas Tabel</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2" style="font-size: 0.75rem;" onclick="applyKopPreset('compact')">Compact 95%</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2" style="font-size: 0.75rem;" onclick="applyKopPreset('tall')">Tinggi 160px</button>
                    </div>
                </div>

                <hr class="my-2 text-secondary opacity-25">

                <!-- Sliders -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label small fw-bold mb-1 text-dark">Lebar Kop (Width)</label>
                        <span class="badge bg-light text-dark border" id="lbl_cfg_width">100%</span>
                    </div>
                    <input type="range" class="form-range" id="cfg_width" min="50" max="100" step="1" value="100" oninput="updateCanvasPreview()">
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label small fw-bold mb-1 text-dark">Tinggi Maksimal (Max Height)</label>
                        <span class="badge bg-light text-dark border" id="lbl_cfg_max_height">140px</span>
                    </div>
                    <input type="range" class="form-range" id="cfg_max_height" min="60" max="250" step="5" value="140" oninput="updateCanvasPreview()">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-bold mb-1 text-dark">Margin Atas</label>
                            <span class="badge bg-light text-dark border" id="lbl_cfg_margin_top">0px</span>
                        </div>
                        <input type="range" class="form-range" id="cfg_margin_top" min="-20" max="60" step="2" value="0" oninput="updateCanvasPreview()">
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-bold mb-1 text-dark">Margin Bawah</label>
                            <span class="badge bg-light text-dark border" id="lbl_cfg_margin_bottom">18px</span>
                        </div>
                        <input type="range" class="form-range" id="cfg_margin_bottom" min="0" max="60" step="2" value="18" oninput="updateCanvasPreview()">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label small fw-bold mb-1 text-dark">Margin Kiri-Kanan Kertas</label>
                        <span class="badge bg-light text-dark border" id="lbl_cfg_page_padding">8px</span>
                    </div>
                    <input type="range" class="form-range" id="cfg_page_padding" min="0" max="50" step="2" value="8" oninput="updateCanvasPreview()">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">Perataan Kop (Alignment)</label>
                    <div class="btn-group w-100 btn-group-sm">
                        <input type="radio" class="btn-check" name="cfg_align" id="cfg_align_left" value="left" onchange="updateCanvasPreview()">
                        <label class="btn btn-outline-secondary" for="cfg_align_left"><i class="bi bi-text-left me-1"></i>Kiri</label>
                        
                        <input type="radio" class="btn-check" name="cfg_align" id="cfg_align_center" value="center" checked onchange="updateCanvasPreview()">
                        <label class="btn btn-outline-secondary" for="cfg_align_center"><i class="bi bi-text-center me-1"></i>Tengah</label>
                        
                        <input type="radio" class="btn-check" name="cfg_align" id="cfg_align_right" value="right" onchange="updateCanvasPreview()">
                        <label class="btn btn-outline-secondary" for="cfg_align_right"><i class="bi bi-text-right me-1"></i>Kanan</label>
                    </div>
                </div>

                <!-- Accordion Kustomisasi Teks Format Kotak Baku -->
                <div class="accordion mb-3" id="accCustomText">
                    <div class="accordion-item border rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 small fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCustomText">
                                <i class="bi bi-fonts me-2 text-primary"></i>Kustomisasi Teks Kotak Baku
                            </button>
                        </h2>
                        <div id="collapseCustomText" class="accordion-collapse collapse" data-bs-parent="#accCustomText">
                            <div class="accordion-body p-3 small">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Inggris Baris 1</label>
                                    <input type="text" id="cfg_txt_en1" class="form-control form-control-sm" value="ADVISORY OF FOREIGN STUDENT" oninput="updateCanvasPreview()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Inggris Baris 2</label>
                                    <input type="text" id="cfg_txt_en2" class="form-control form-control-sm" value="DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL" oninput="updateCanvasPreview()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Inggris Baris 3 (Lokasi)</label>
                                    <input type="text" id="cfg_txt_en3" class="form-control form-control-sm" value="GONTOR-PONOROGO-INDONESIA" oninput="updateCanvasPreview()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Arab Baris 1</label>
                                    <input type="text" id="cfg_txt_ar1" class="form-control form-control-sm text-end" dir="rtl" value="هيئة إشراف شؤون الطلاب الوافدين" oninput="updateCanvasPreview()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Arab Baris 2</label>
                                    <input type="text" id="cfg_txt_ar2" class="form-control form-control-sm text-end" dir="rtl" value="معهد دار السلام كونتور فونوروغو" oninput="updateCanvasPreview()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Arab Baris 3</label>
                                    <input type="text" id="cfg_txt_ar3" class="form-control form-control-sm text-end" dir="rtl" value="للتربية الإسلامية الحديثة" oninput="updateCanvasPreview()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">Teks Pita Bawah (Alamat & Email)</label>
                                    <input type="text" id="cfg_txt_foot" class="form-control form-control-sm" value="Head Office : Sahifah One Building Second Floor Room 203, Email: plngontor@gmail.com" oninput="updateCanvasPreview()">
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold mb-1">Warna Border & Pita</label>
                                        <input type="color" id="cfg_color_border" class="form-control form-control-color form-control-sm w-100" value="#004aad" oninput="updateCanvasPreview()">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold mb-1">Tebal Border</label>
                                        <select id="cfg_border_width" class="form-select form-select-sm" onchange="updateCanvasPreview()">
                                            <option value="1.5px">1.5 px</option>
                                            <option value="2px" selected>2 px</option>
                                            <option value="2.5px">2.5 px</option>
                                            <option value="3px">3 px</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 12px 24px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 btn-sm fw-medium" onclick="resetKopSettings()">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset ke Default
            </button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary rounded-pill px-3 btn-sm fw-bold" onclick="testPrintFromCanvas()">
                    <i class="bi bi-printer me-1"></i> Uji Cetak
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4 btn-sm fw-bold shadow-sm" onclick="saveKopSettings()">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const allPengajuans = <?= json_encode($pengajuanList ?? []) ?>;
const instansiMap = <?= json_encode($instansiMap ?? []) ?>;

// Reminder chevron toggle animation
(function() {
    var el = document.getElementById('collapseReminderDetails');
    var chevron = document.getElementById('reminderChevron');
    if (el && chevron) {
        el.addEventListener('show.bs.collapse', function() {
            chevron.style.transform = 'rotate(180deg)';
        });
        el.addEventListener('hide.bs.collapse', function() {
            chevron.style.transform = 'rotate(0deg)';
        });
    }
})();

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m];
    });
}

// ==========================================
// DYNAMIC KOP SURAT CONFIG & STUDIO CANVAS
// ==========================================
function getSavedKopConfig(pondokKey) {
    let key = 'sipln_kop_cfg_' + (pondokKey || 'default');
    let saved = localStorage.getItem(key);
    if (!saved) {
        saved = localStorage.getItem('sipln_kop_cfg_default');
    }
    
    let defaults = {
        mode: 'auto', // 'auto', 'box_only', 'image_only', 'none'
        width: 100, // %
        max_height: 140, // px
        margin_top: 0, // px
        margin_bottom: 18, // px
        page_padding: 8, // px — minimum default
        align: 'center', // 'left', 'center', 'right'
        txt_en1: 'ADVISORY OF FOREIGN STUDENT',
        txt_en2: 'DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL',
        txt_en3: 'GONTOR-PONOROGO-INDONESIA',
        txt_ar1: 'هيئة إشراف شؤون الطلاب الوافدين',
        txt_ar2: 'معهد دار السلام كونتور فونوروغو',
        txt_ar3: 'للتربية الإسلامية الحديثة',
        txt_foot: 'Head Office : Sahifah One Building Second Floor Room 203, Email: plngontor@gmail.com',
        color_border: '#004aad',
        border_width: '2px'
    };
    
    if (saved) {
        try {
            return Object.assign(defaults, JSON.parse(saved));
        } catch(e) {}
    }
    return defaults;
}

function saveKopConfigToStorage(pondokKey, config) {
    let key = 'sipln_kop_cfg_' + (pondokKey || 'default');
    localStorage.setItem(key, JSON.stringify(config));
    if (pondokKey !== 'default') {
        localStorage.setItem('sipln_kop_cfg_default', JSON.stringify(config));
    }
}

let activeCanvasDocType = 'pengajuan';

function openKopSettingsModal() {
    let overlay = document.getElementById('studioKopOverlay');
    if (!overlay) return;
    
    if (overlay.parentElement !== document.body) {
        document.body.appendChild(overlay);
    }
    
    let pondokSelect = document.getElementById('cfg_pondok');
    let currentPondok = pondokSelect ? pondokSelect.value : 'default';
    loadConfigIntoForm(currentPondok);
    updateCanvasPreview();

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
window.openKopSettingsModal = openKopSettingsModal;

function closeKopSettingsModal() {
    let overlay = document.getElementById('studioKopOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
    document.body.style.overflow = '';
}
window.closeKopSettingsModal = closeKopSettingsModal;

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        let overlay = document.getElementById('studioKopOverlay');
        if (overlay && overlay.style.display === 'flex') {
            closeKopSettingsModal();
        }
    }
});

function onConfigPondokChange() {
    let pondokEl = document.getElementById('cfg_pondok');
    let pondok = pondokEl ? pondokEl.value : 'default';
    loadConfigIntoForm(pondok);
    updateCanvasPreview();
}
window.onConfigPondokChange = onConfigPondokChange;

function loadConfigIntoForm(pondokKey) {
    let cfg = getSavedKopConfig(pondokKey);
    
    let targetInstansi = (typeof instansiMap === 'object' && instansiMap !== null) ? (instansiMap[pondokKey] || {}) : {};
    let kepengurusan = targetInstansi.kepengurusan || targetInstansi.def_kepengurusan || 'Ponorogo';
    let emailInstansi = targetInstansi.email || 'plngontor@gmail.com';
    
    let arabLocMap = {
        'Ponorogo': 'فونوروغو', 'Mantingan': 'مانتينجان', 'Kediri': 'كينديري',
        'Banyuwangi': 'بانيوانجي', 'Magelang': 'ماجيلانج',
        'G1': 'فونوروغو', 'GP': 'مانتينجان', 'G2': 'فونوروغو',
        'G3': 'كينديري', 'G4': 'بانيوانجي', 'G5': 'ماجيلانج'
    };
    let arabLoc = arabLocMap[kepengurusan] || arabLocMap[pondokKey] || 'فونوروغو';

    let setVal = (id, v) => {
        let el = document.getElementById(id);
        if (el) el.value = v;
    };

    setVal('cfg_mode', cfg.mode || 'auto');
    setVal('cfg_width', cfg.width || 100);
    setVal('cfg_max_height', cfg.max_height || 140);
    setVal('cfg_margin_top', cfg.margin_top || 0);
    setVal('cfg_margin_bottom', cfg.margin_bottom || 18);
    setVal('cfg_page_padding', cfg.page_padding !== undefined ? cfg.page_padding : 8);
    
    let alignVal = cfg.align || 'center';
    let radio = document.getElementById('cfg_align_' + alignVal);
    if (radio) radio.checked = true;

    setVal('cfg_txt_en1', cfg.txt_en1 || 'ADVISORY OF FOREIGN STUDENT');
    setVal('cfg_txt_en2', cfg.txt_en2 || 'DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL');
    setVal('cfg_txt_en3', cfg.txt_en3 || ('GONTOR-' + kepengurusan.toUpperCase() + '-INDONESIA'));
    
    setVal('cfg_txt_ar1', cfg.txt_ar1 || 'هيئة إشراف شؤون الطلاب الوافدين');
    setVal('cfg_txt_ar2', cfg.txt_ar2 || ('معهد دار السلام كونتور ' + arabLoc));
    setVal('cfg_txt_ar3', cfg.txt_ar3 || 'للتربية الإسلامية الحديثة');
    setVal('cfg_txt_foot', cfg.txt_foot || ('Head Office : Sahifah One Building Second Floor Room 203, Email: ' + emailInstansi));
    
    setVal('cfg_color_border', cfg.color_border || '#004aad');
    setVal('cfg_border_width', cfg.border_width || '2px');
}

function getConfigFromForm() {
    let alignVal = 'center';
    let leftR = document.getElementById('cfg_align_left');
    let rightR = document.getElementById('cfg_align_right');
    if (leftR && leftR.checked) alignVal = 'left';
    if (rightR && rightR.checked) alignVal = 'right';

    let getVal = (id, def) => {
        let el = document.getElementById(id);
        return el ? el.value : def;
    };

    return {
        mode: getVal('cfg_mode', 'auto'),
        width: Number(getVal('cfg_width', 100)),
        max_height: Number(getVal('cfg_max_height', 140)),
        margin_top: Number(getVal('cfg_margin_top', 0)),
        margin_bottom: Number(getVal('cfg_margin_bottom', 18)),
        page_padding: Number(getVal('cfg_page_padding', 8)),
        align: alignVal,
        txt_en1: getVal('cfg_txt_en1', 'ADVISORY OF FOREIGN STUDENT'),
        txt_en2: getVal('cfg_txt_en2', 'DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL'),
        txt_en3: getVal('cfg_txt_en3', 'GONTOR-PONOROGO-INDONESIA'),
        txt_ar1: getVal('cfg_txt_ar1', 'هيئة إشراف شؤون الطلاب الوافدين'),
        txt_ar2: getVal('cfg_txt_ar2', 'معهد دار السلام كونتور فونوروغو'),
        txt_ar3: getVal('cfg_txt_ar3', 'للتربية الإسلامية الحديثة'),
        txt_foot: getVal('cfg_txt_foot', 'Head Office : Sahifah One Building Second Floor Room 203, Email: plngontor@gmail.com'),
        color_border: getVal('cfg_color_border', '#004aad'),
        border_width: getVal('cfg_border_width', '2px')
    };
}

function updateCanvasPreview() {
    let cfg = getConfigFromForm();
    let pondokEl = document.getElementById('cfg_pondok');
    let pondokKey = pondokEl ? pondokEl.value : 'default';
    
    // Update Badge Labels
    let setTxt = (id, text) => {
        let el = document.getElementById(id);
        if (el) el.innerText = text;
    };
    setTxt('lbl_cfg_width', cfg.width + '%');
    setTxt('lbl_cfg_max_height', cfg.max_height + 'px');
    setTxt('lbl_cfg_margin_top', cfg.margin_top + 'px');
    setTxt('lbl_cfg_margin_bottom', cfg.margin_bottom + 'px');
    setTxt('lbl_cfg_page_padding', cfg.page_padding + 'px');

    // Update Canvas Paper Container Padding
    let paper = document.getElementById('canvasPaper');
    if (paper) {
        paper.style.padding = `20px ${cfg.page_padding}px`;
    }

    // Render Kop on Live Canvas
    let targetInstansi = (typeof instansiMap === 'object' && instansiMap !== null) ? (instansiMap[pondokKey] || {}) : {};
    let kodeInstansi = targetInstansi.kode || targetInstansi.kode_instansi || pondokKey || '';
    let imgSrc = `<?= API_URL ?>/profil-instansi/kop-surat/view?kode=${encodeURIComponent(kodeInstansi)}&v=${new Date().getTime()}`;
    
    let wrapper = document.getElementById('canvasKopWrapper');
    if (!wrapper) return;

    if (cfg.mode === 'none') {
        wrapper.innerHTML = `<div class="p-3 text-center text-muted border border-dashed rounded mb-3 small bg-light"><i class="bi bi-eye-slash me-1"></i>Kop Surat disembunyikan (mode cetak di kertas sudah berkop fisik)</div>`;
        return;
    }

    let alignStyle = cfg.align === 'left' ? 'margin-left: 0; margin-right: auto;' : (cfg.align === 'right' ? 'margin-left: auto; margin-right: 0;' : 'margin-left: auto; margin-right: auto;');
    let boxHtml = `
        <div style="width: ${cfg.width}%; ${alignStyle} border: ${cfg.border_width} solid ${cfg.color_border}; margin-top: ${cfg.margin_top}px; margin-bottom: ${cfg.margin_bottom}px; font-family: 'Times New Roman', Times, serif; background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box;">
            <div style="display: flex; justify-content: space-between; align-items: center; color: ${cfg.color_border}; padding: 10px 14px 6px 14px;">
                <div style="text-align: left; line-height: 1.25;">
                    <h2 style="margin: 0; font-weight: bold; font-size: 19px; font-family: 'Times New Roman', Times, serif; letter-spacing: 0.5px;">${cfg.txt_en1}</h2>
                    <h5 style="margin: 4px 0 0 0; font-size: 12.5px; font-weight: bold; font-family: 'Times New Roman', Times, serif;">${cfg.txt_en2}</h5>
                    <h5 style="margin: 3px 0 0 0; font-size: 12.5px; font-weight: bold; font-family: 'Times New Roman', Times, serif;">${cfg.txt_en3}</h5>
                </div>
                <div style="text-align: right; line-height: 1.3;" dir="rtl">
                    <h2 style="margin: 0; font-weight: bold; font-size: 23px; font-family: 'Times New Roman', serif;">${cfg.txt_ar1}</h2>
                    <h5 style="margin: 4px 0 0 0; font-size: 13.5px; font-weight: bold; font-family: 'Times New Roman', serif;">${cfg.txt_ar2}</h5>
                    <h5 style="margin: 3px 0 0 0; font-size: 13.5px; font-weight: bold; font-family: 'Times New Roman', serif;">${cfg.txt_ar3}</h5>
                </div>
            </div>
            <div style="background-color: ${cfg.color_border}; color: white; text-align: center; padding: 5px 10px; font-style: italic; font-size: 11px; font-family: 'Times New Roman', Times, serif; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                ${cfg.txt_foot}
            </div>
        </div>
    `;

    if (cfg.mode === 'box_only') {
        wrapper.innerHTML = boxHtml;
    } else if (cfg.mode === 'image_only') {
        wrapper.innerHTML = `
            <div style="text-align: ${cfg.align}; margin-top: ${cfg.margin_top}px; margin-bottom: ${cfg.margin_bottom}px;">
                <img src="${imgSrc}" style="width: ${cfg.width}%; max-height: ${cfg.max_height}px; object-fit: contain; object-position: top; display: inline-block;">
            </div>
        `;
    } else {
        // Auto: Image with fallback to box
        wrapper.innerHTML = `
            <div style="text-align: ${cfg.align}; margin-top: ${cfg.margin_top}px; margin-bottom: ${cfg.margin_bottom}px;">
                <img src="${imgSrc}" style="width: ${cfg.width}%; max-height: ${cfg.max_height}px; object-fit: contain; object-position: top; display: inline-block;"
                     onerror="this.style.display='none'; document.getElementById('canvas_fallback_box').style.display='block';">
            </div>
            <div id="canvas_fallback_box" style="display: none;">
                ${boxHtml}
            </div>
        `;
    }
}

function previewCanvasDoc(type) {
    activeCanvasDocType = type;
    document.getElementById('btnPreviewPengajuan').className = (type === 'pengajuan' ? 'btn btn-primary active btn-sm' : 'btn btn-outline-secondary bg-white btn-sm');
    document.getElementById('btnPreviewLaporan').className = (type === 'laporan' ? 'btn btn-primary active btn-sm' : 'btn btn-outline-secondary bg-white btn-sm');

    let doc = document.getElementById('canvasDocContent');
    if (type === 'pengajuan') {
        doc.innerHTML = `
            <div style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
                <h5 style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin: 0; font-size: 15px; text-transform: uppercase;">
                    PENGAJUAN ANGGARAN OPERASIONAL
                </h5>
                <h6 style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin: 3px 0 0 0; font-size: 14px; text-transform: uppercase;">
                    PEMBIMBING LUAR NEGERI BULAN RABIUL AKHIR 1448 H
                </h6>
                <div style="font-family: 'Times New Roman', Times, serif; font-style: italic; margin-top: 5px; font-size: 12.5px; color: #444;">
                    Saturday, September 12, 2026
                </div>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 12px; border: 1.5px solid #000; margin-bottom: 20px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid #000; background-color: #fafafa;">
                        <th style="border: 1px solid #000; padding: 5px; width: 35px; text-align: center;">No</th>
                        <th style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Nama Barang</th>
                        <th style="border: 1px solid #000; padding: 5px; width: 50px; text-align: center;">Jumlah</th>
                        <th style="border: 1px solid #000; padding: 5px; width: 60px; text-align: center;">Satuan</th>
                        <th style="border: 1px solid #000; padding: 5px 8px; width: 110px; text-align: center;">Harga Barang</th>
                        <th style="border: 1px solid #000; padding: 5px 8px; width: 110px; text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                        <td style="border: 1px solid #000; padding: 4px 8px;">Kebutuhan Operasional Bulanan</td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                        <td style="border: 1px solid #000; padding: 4px 8px; text-align: center; font-style: italic;">Bulan</td>
                        <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Rp350.000,00</td>
                        <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Rp350.000,00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="border-top: 1.5px solid #000; font-weight: bold;">
                        <td colspan="5" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Total Keseluruhan</td>
                        <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Rp350.000,00</td>
                    </tr>
                </tfoot>
            </table>
        `;
    } else {
        doc.innerHTML = `
            <div style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
                <h5 style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin: 0; font-size: 15px; text-transform: uppercase;">
                    LAPORAN ANGGARAN OPERASIONAL
                </h5>
                <h6 style="font-family: 'Times New Roman', Times, serif; font-weight: bold; margin: 3px 0 0 0; font-size: 14px; text-transform: uppercase;">
                    PEMBIMBING LUAR NEGERI BULAN RABIUL AKHIR 1448 H
                </h6>
            </div>
            
            <div style="font-weight: bold; font-size: 12.5px; margin-bottom: 4px;">Nota Ke- 1</div>
            <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 12px; border: 1.5px solid #000; margin-bottom: 20px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid #000; background-color: #fafafa;">
                        <th style="border: 1px solid #000; padding: 4px; width: 35px; text-align: center;">No</th>
                        <th style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Nama Barang</th>
                        <th style="border: 1px solid #000; padding: 4px; width: 50px; text-align: center;">Jumlah</th>
                        <th style="border: 1px solid #000; padding: 4px 8px; width: 110px; text-align: center;">Harga Satuan</th>
                        <th style="border: 1px solid #000; padding: 4px 8px; width: 110px; text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                        <td style="border: 1px solid #000; padding: 4px 8px;">Realisasi Pembelian ATK</td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                        <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Rp350.000,00</td>
                        <td style="border: 1px solid #000; padding: 4px 8px; text-align: center;">Rp350.000,00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="border-top: 1.5px solid #000; font-weight: bold;">
                        <td colspan="4" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Total Nota 1</td>
                        <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Rp350.000,00</td>
                    </tr>
                </tfoot>
            </table>
        `;
    }
}

function applyKopPreset(preset) {
    if (preset === 'exact_table') {
        document.getElementById('cfg_width').value = 100;
        document.getElementById('cfg_max_height').value = 140;
        document.getElementById('cfg_margin_top').value = 0;
        document.getElementById('cfg_margin_bottom').value = 18;
        document.getElementById('cfg_page_padding').value = 8;
        document.getElementById('cfg_align_center').checked = true;
    } else if (preset === 'compact') {
        document.getElementById('cfg_width').value = 95;
        document.getElementById('cfg_max_height').value = 120;
        document.getElementById('cfg_margin_top').value = 0;
        document.getElementById('cfg_margin_bottom').value = 14;
        document.getElementById('cfg_page_padding').value = 8;
        document.getElementById('cfg_align_center').checked = true;
    } else if (preset === 'tall') {
        document.getElementById('cfg_width').value = 100;
        document.getElementById('cfg_max_height').value = 160;
        document.getElementById('cfg_margin_top').value = 5;
        document.getElementById('cfg_margin_bottom').value = 22;
        document.getElementById('cfg_page_padding').value = 6;
        document.getElementById('cfg_align_center').checked = true;
    }
    updateCanvasPreview();
}

function saveKopSettings() {
    let pondokKey = document.getElementById('cfg_pondok').value;
    let cfg = getConfigFromForm();
    saveKopConfigToStorage(pondokKey, cfg);
    
    Swal.fire({
        icon: 'success',
        title: 'Pengaturan Disimpan!',
        text: 'Posisi dan dimensi Kop Surat telah berhasil diperbarui dan diterapkan ke semua format cetak.',
        timer: 1500,
        showConfirmButton: false
    });
    
    closeKopSettingsModal();
}

function resetKopSettings() {
    Swal.fire({
        title: 'Reset Pengaturan Kop?',
        text: 'Pengaturan posisi dan ukuran kop surat akan dikembalikan ke nilai standar awal.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then(res => {
        if (res.isConfirmed) {
            let pondokKey = document.getElementById('cfg_pondok').value;
            let key = 'sipln_kop_cfg_' + (pondokKey || 'default');
            localStorage.removeItem(key);
            localStorage.removeItem('sipln_kop_cfg_default');
            loadConfigIntoForm(pondokKey);
            updateCanvasPreview();
            Swal.fire('Berhasil', 'Pengaturan telah direset ke default.', 'success');
        }
    });
}

function testPrintFromCanvas() {
    let dummyP = allPengajuans[0] || {
        id: 1,
        instansi: document.getElementById('cfg_pondok').value !== 'default' ? document.getElementById('cfg_pondok').value : 'G1',
        bulan_hijriah: 'Rabiul Akhir 1448 H',
        tanggal_pengajuan: '<?= date('Y-m-d') ?>',
        total_ajuan: 350000,
        total_disetujui: 350000,
        status: 'diajukan',
        items: [
            { nama_item: 'Kebutuhan Operasional Bulanan', qty: 1, satuan: 'Bulan', harga_satuan: 350000, nominal_ajuan: 350000 }
        ]
    };

    if (activeCanvasDocType === 'pengajuan') {
        printSuratPengajuanClassic(dummyP);
    } else {
        let dummyNotas = [
            { nomor_nota: '1', tanggal: '<?= date('Y-m-d') ?>', total_belanja: 350000, items: [ { nama_barang: 'Realisasi ATK & Operasional', qty: 1, harga_satuan: 350000, subtotal: 350000 } ] }
        ];
        printLaporanAnggaranClassic(dummyP, dummyNotas);
    }
}

// ==========================================
// PENCATATAN ANGGARAN
// ==========================================
let itemsAjuan = [];

function openPengajuanModal() {
    document.getElementById('form_pengajuan_id').value = '';
    document.getElementById('modalPengajuanTitle').innerHTML = '<i class="bi bi-file-earmark-plus text-primary me-2"></i>Catat Rencana Anggaran';
    document.getElementById('btnSubmitPengajuan').innerText = 'Simpan Anggaran';
    itemsAjuan = [];
    renderItemsAjuan();
    new bootstrap.Modal(document.getElementById('modalPengajuan')).show();
    addItemRow();
}

function editPengajuan(p) {
    document.getElementById('form_pengajuan_id').value = p.id;
    document.getElementById('modalPengajuanTitle').innerHTML = '<i class="bi bi-pencil-square text-info me-2"></i>Edit Rencana Anggaran';
    document.getElementById('btnSubmitPengajuan').innerText = 'Simpan Perubahan';
    
    let parts = p.bulan_hijriah.split(' ');
    if (parts.length >= 3) {
        document.getElementById('form_bulan').value = parts[0] + (parts[1] && parts[1] !== 'Awal' && parts[1] !== 'Akhir' ? '' : ' ' + parts[1]);
        let thn = parts[parts.length - 2];
        if(!isNaN(thn)) document.getElementById('form_tahun').value = thn;
    } else if (parts.length >= 2) {
        document.getElementById('form_bulan').value = parts[0];
        document.getElementById('form_tahun').value = parts[1];
    }
    
    let instansiEl = document.getElementById('form_instansi');
    if (instansiEl) instansiEl.value = p.instansi;

    itemsAjuan = p.items.map(it => ({
        nama_item: it.nama_item,
        qty: it.qty,
        satuan: it.satuan,
        bagian: it.bagian,
        harga_satuan: it.harga_satuan,
        nominal_ajuan: it.nominal_ajuan
    }));
    
    if (itemsAjuan.length === 0) addItemRow();
    else renderItemsAjuan();
    
    new bootstrap.Modal(document.getElementById('modalPengajuan')).show();
}

function copyPengajuan(p) {
    document.getElementById('form_pengajuan_id').value = '';
    document.getElementById('modalPengajuanTitle').innerHTML = '<i class="bi bi-files text-primary me-2"></i>Duplikat Catatan Anggaran';
    document.getElementById('btnSubmitPengajuan').innerText = 'Simpan Catatan Baru';
    
    let instansiEl = document.getElementById('form_instansi');
    if (instansiEl) instansiEl.value = p.instansi;

    itemsAjuan = p.items.map(it => ({
        nama_item: it.nama_item,
        qty: it.qty,
        satuan: it.satuan,
        bagian: it.bagian,
        harga_satuan: it.harga_satuan,
        nominal_ajuan: it.nominal_ajuan
    }));
    
    if (itemsAjuan.length === 0) addItemRow();
    else renderItemsAjuan();
    
    new bootstrap.Modal(document.getElementById('modalPengajuan')).show();
}

function addItemRow() {
    itemsAjuan.push({ nama_item: '', qty: 1, satuan: '', bagian: '', harga_satuan: 0, nominal_ajuan: 0 });
    renderItemsAjuan();
}

function renderItemsAjuan() {
    const tbody = document.getElementById('tbodyItems');
    tbody.innerHTML = '';
    let total = 0;
    itemsAjuan.forEach((item, index) => {
        let nominal = Number(item.qty || 0) * Number(item.harga_satuan || 0);
        item.nominal_ajuan = nominal;
        total += nominal;
        tbody.innerHTML += `
            <div class="card item-kebutuhan-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;"><i class="bi bi-tag me-1"></i>Item Kebutuhan #${index + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 rounded-pill fw-medium" style="font-size: 0.76rem;" onclick="removeItem(${index})" title="Hapus Baris Ini">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                </div>
                <div class="row g-2.5">
                    <div class="col-12 col-md-8">
                        <label class="small text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.7rem;">Nama Kebutuhan / Barang *</label>
                        <input type="text" id="i_nama_item_${index}" class="form-control form-control-sm" placeholder="Misal: Kertas HVS / Token Listrik" value="${escapeHtml(item.nama_item || '')}" oninput="updateItem(${index}, 'nama_item', this.value)" onkeydown="handleEnter(event, ${index}, 'nama_item')">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="small text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.7rem;">Bagian / Seksi (Opsional)</label>
                        <input type="text" id="i_bagian_${index}" class="form-control form-control-sm" placeholder="Misal: Sekretariat" value="${escapeHtml(item.bagian || '')}" oninput="updateItem(${index}, 'bagian', this.value)" onkeydown="handleEnter(event, ${index}, 'bagian')">
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="small text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.7rem;">Jumlah (Qty)</label>
                        <input type="number" id="i_qty_${index}" class="form-control form-control-sm text-center" placeholder="1" value="${item.qty}" oninput="updateItem(${index}, 'qty', this.value)" onkeydown="handleEnter(event, ${index}, 'qty')">
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="small text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.7rem;">Satuan</label>
                        <input type="text" id="i_satuan_${index}" class="form-control form-control-sm" placeholder="Rim/Pcs/Bln" value="${escapeHtml(item.satuan || '')}" oninput="updateItem(${index}, 'satuan', this.value)" onkeydown="handleEnter(event, ${index}, 'satuan')">
                    </div>
                    <div class="col-4 col-md-4">
                        <label class="small text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.7rem;">Harga Satuan (Rp) *</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted">Rp</span>
                            <input type="number" id="i_harga_satuan_${index}" class="form-control form-control-sm" placeholder="0" value="${item.harga_satuan || ''}" oninput="updateItem(${index}, 'harga_satuan', this.value)" onkeydown="handleEnter(event, ${index}, 'harga_satuan')">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex justify-content-between align-items-center bg-light bg-opacity-75 p-2 rounded-3 border border-secondary-subtle">
                        <span class="text-muted small fw-bold text-uppercase ms-1" style="font-size: 0.72rem;">Subtotal:</span>
                        <span class="fw-bold text-dark fs-6 me-1" id="lblSubtotal_${index}">Rp ${formatRupiah(nominal)}</span>
                    </div>
                </div>
            </div>
        `;
    });
    document.getElementById('lblTotalAjuan').innerText = 'Rp ' + formatRupiah(total);
}

function updateItem(index, key, value) {
    itemsAjuan[index][key] = value;
    if (['qty', 'harga_satuan'].includes(key)) {
        let item = itemsAjuan[index];
        let nominal = Number(item.qty || 0) * Number(item.harga_satuan || 0);
        item.nominal_ajuan = nominal;
        document.getElementById('lblSubtotal_' + index).innerText = 'Rp ' + formatRupiah(nominal);
        let total = itemsAjuan.reduce((sum, it) => sum + (Number(it.qty || 0) * Number(it.harga_satuan || 0)), 0);
        document.getElementById('lblTotalAjuan').innerText = 'Rp ' + formatRupiah(total);
    }
}

function handleEnter(e, index, currentField) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const flow = ['nama_item', 'qty', 'satuan', 'bagian', 'harga_satuan'];
        let currentIndex = flow.indexOf(currentField);
        if (currentIndex < flow.length - 1) {
            let nextField = flow[currentIndex + 1];
            let nextEl = document.getElementById(`i_${nextField}_${index}`);
            if (nextEl) nextEl.focus();
        } else {
            addItemRow();
            setTimeout(() => {
                let nextRowEl = document.getElementById(`i_nama_item_${index + 1}`);
                if (nextRowEl) {
                    nextRowEl.scrollIntoView({behavior: "smooth", block: "center"});
                    nextRowEl.focus();
                }
            }, 100);
        }
    }
}

function removeItem(index) {
    itemsAjuan.splice(index, 1);
    renderItemsAjuan();
}

function submitPengajuan() {
    const btn = document.getElementById('btnSubmitPengajuan');
    Swal.fire({
        title: 'Simpan Catatan Anggaran?',
        text: "Pastikan rincian kebutuhan operasional sudah sesuai.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            let bulanValue = document.getElementById('form_bulan').value;
            let tahunValue = document.getElementById('form_tahun').value;
            let bulan = bulanValue + ' ' + tahunValue + ' H';
            let validItems = itemsAjuan.filter(i => i.nama_item.trim() !== '');
            
            if (!bulanValue || !tahunValue) {
                Swal.fire('Peringatan', 'Silakan lengkapi bulan dan tahun hijriah', 'warning');
                return;
            }

            let instansiEl = document.getElementById('form_instansi');
            let dataPayload = { bulan_hijriah: bulan, items: validItems };
            if (instansiEl) {
                if (!instansiEl.value) {
                    Swal.fire('Peringatan', 'Silakan pilih Instansi terlebih dahulu.', 'warning');
                    return;
                }
                dataPayload.instansi = instansiEl.value;
            }

            if (validItems.length === 0) {
                Swal.fire('Peringatan', 'Minimal harus ada 1 item kebutuhan.', 'warning');
                return;
            }

            let id = document.getElementById('form_pengajuan_id').value;
            let url = id ? `<?= API_URL ?>/api/anggaran/${id}/update` : '<?= API_URL ?>/api/anggaran/store';

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            btn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify(dataPayload)
            }).then(r => r.json()).then(res => {
                if(res.success) {
                    Swal.fire('Berhasil', res.message, 'success').then(()=>location.reload());
                } else {
                    btn.innerHTML = 'Simpan Anggaran';
                    btn.disabled = false;
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

function deletePengajuan(id) {
    Swal.fire({
        title: 'Hapus Catatan Anggaran?',
        text: "Data rincian anggaran dan seluruh nota terkait akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/${id}/delete`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf }
            }).then(r => r.json()).then(res => {
                if(res.success) {
                    Swal.fire('Terhapus', res.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

// ==========================================
// TETAPKAN PLAFOND ANGGARAN
// ==========================================
let rItems = [];

function reviewPengajuan(p) {
    document.getElementById('r_pengajuan_id').value = p.id;
    document.getElementById('r_instansi').innerText = p.instansi;
    document.getElementById('r_bulan').innerText = p.bulan_hijriah;
    document.getElementById('r_catatan').value = p.catatan_admin || '';
    
    document.getElementById('r_totalAjuan').innerText = 'Rp ' + formatRupiah(p.total_ajuan);
    
    document.getElementById('modeLumpsum').checked = false;
    document.getElementById('r_totalLumpsum').value = p.total_disetujui > 0 ? p.total_disetujui : p.total_ajuan;
    toggleLumpsum();

    rItems = p.items;
    renderReviewItems();
    new bootstrap.Modal(document.getElementById('modalReview')).show();
}

function toggleLumpsum() {
    let isLump = document.getElementById('modeLumpsum').checked;
    document.getElementById('r_tableItems').style.display = isLump ? 'none' : 'block';
    document.getElementById('lumpsumContainer').style.display = isLump ? 'block' : 'none';
}

function renderReviewItems() {
    const tbody = document.getElementById('r_tbodyItems');
    tbody.innerHTML = '';
    let totalAcc = 0;
    rItems.forEach((item, idx) => {
        if(!item.nominal_disetujui || Number(item.nominal_disetujui) === 0) {
            item.nominal_disetujui = item.nominal_ajuan;
        }
        totalAcc += Number(item.nominal_disetujui);
        
        tbody.innerHTML += `
            <tr>
                <td><div class="fw-medium">${item.nama_item}</div>
                    <div class="small text-muted">${item.qty} ${item.satuan} @ Rp ${formatRupiah(item.harga_satuan)} ${item.bagian ? `(Bag. ${item.bagian})` : ''}</div>
                </td>
                <td><span class="text-secondary">Rp ${formatRupiah(item.nominal_ajuan)}</span></td>
                <td class="bg-warning bg-opacity-10 p-2">
                    <input type="number" class="form-control form-control-sm text-end border-warning shadow-sm" value="${item.nominal_disetujui}" oninput="updateAcc(${idx}, this.value)">
                </td>
            </tr>
        `;
    });
    document.getElementById('r_totalAcc').innerText = 'Rp ' + formatRupiah(totalAcc);
}

function updateAcc(idx, val) {
    rItems[idx].nominal_disetujui = val;
    let totalAcc = rItems.reduce((sum, it) => sum + Number(it.nominal_disetujui || 0), 0);
    document.getElementById('r_totalAcc').innerText = 'Rp ' + formatRupiah(totalAcc);
}

function approvePengajuan(status) {
    const id = document.getElementById('r_pengajuan_id').value;
    const catatan = document.getElementById('r_catatan').value;
    let isLumpsum = document.getElementById('modeLumpsum').checked;
    
    Swal.fire({
        title: status === 'disetujui' ? 'Simpan & Tetapkan Anggaran?' : 'Batalkan Rencana Anggaran?',
        icon: status === 'disetujui' ? 'success' : 'warning',
        showCancelButton: true,
        confirmButtonText: status === 'disetujui' ? 'Ya, Tetapkan' : 'Ya, Batalkan'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            
            let payload = { status, catatan_admin: catatan };
            if (status === 'disetujui') {
                if (isLumpsum) {
                    let total = document.getElementById('r_totalLumpsum').value;
                    if (!total || Number(total) <= 0) {
                        Swal.fire('Error', 'Nominal Total Anggaran tidak boleh kosong/nol', 'error');
                        return;
                    }
                    payload.total_lumpsum = total;
                } else {
                    payload.items = rItems;
                }
            }

            fetch(`<?= API_URL ?>/api/anggaran/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify(payload)
            }).then(r=>r.json()).then(res=>{
                if(res.success) {
                    Swal.fire('Berhasil', res.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            })
        }
    });
}

function viewDetail(p) {
    let isApproved = p.status === 'disetujui' || p.status === 'selesai' || p.status === 'dilaporkan';
    
    if (isApproved) {
        Swal.fire({
            title: 'Memuat Data Review...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(`<?= API_URL ?>/api/anggaran/${p.id}/nota`)
            .then(r => r.json())
            .then(res => {
                let notas = res.data || [];
                renderViewDetailHtml(p, isApproved, notas);
            })
            .catch(err => {
                renderViewDetailHtml(p, isApproved, []);
            });
    } else {
        renderViewDetailHtml(p, isApproved, []);
    }
}

function renderViewDetailHtml(p, isApproved, notas) {
    window.currentPrintP = p; 
    
    let itemHtml = '';
    p.items.forEach((it, i) => {
        itemHtml += `
        <tr>
            <td class="text-center text-muted" style="width: 40px">${i+1}</td>
            <td>
                <div class="fw-bold text-dark">${it.nama_item}</div>
                <div class="small text-muted">${it.qty} ${it.satuan} @ Rp ${formatRupiah(it.harga_satuan)} ${it.bagian ? `(Bag. ${it.bagian})` : ''}</div>
            </td>
            <td class="text-end text-secondary align-middle">Rp ${formatRupiah(it.nominal_ajuan)}</td>
            <td class="text-end fw-bold text-success align-middle">Rp ${formatRupiah(it.nominal_disetujui)}</td>
        </tr>`;
    });
    
    let stampHtml = isApproved 
        ? `<div class="d-flex align-items-center mb-4 p-3 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25 shadow-sm">
             <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px;">
                 <i class="bi bi-check2-all fs-3"></i>
             </div>
             <div>
                 <h6 class="fw-bold text-success mb-1 text-uppercase" style="letter-spacing: 1px;">Status: ${p.status.toUpperCase()}</h6>
                 <div class="text-muted small">Anggaran ini telah ditinjau dan disetujui pada <strong>${p.tanggal_disetujui ? p.tanggal_disetujui : '-'}</strong>.</div>
             </div>
           </div>` 
        : '';

    let notaHtml = '';
    let totalLaporan = 0;
    window.currentPrintNotas = notas || [];
    if (isApproved && notas.length > 0) {
        notaHtml += `
            <div class="mt-5 border-top pt-4">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-receipt me-2 text-primary"></i>LAPORAN REALISASI & NOTA</h5>
        `;

        let hasAnyBagian = notas.some(n => n.bagian && n.bagian.trim() !== '');
        let groupedNotas = {};
        
        if (hasAnyBagian) {
            notas.forEach(n => {
                let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
                if (!groupedNotas[b]) groupedNotas[b] = [];
                groupedNotas[b].push(n);
            });
        } else {
            groupedNotas['SEMUA'] = notas;
        }
        
        let keys = Object.keys(groupedNotas);
        if (hasAnyBagian) {
            keys = keys.sort((a, b) => {
                if (a === 'LAIN-LAIN') return 1;
                if (b === 'LAIN-LAIN') return -1;
                return a.localeCompare(b);
            });
        }
        
        keys.forEach(b => {
            if (hasAnyBagian) {
                let divTotal = groupedNotas[b].reduce((acc, n) => acc + Number(n.total_belanja), 0);
                notaHtml += `
                    <div class="d-flex justify-content-between align-items-center bg-light border-start border-primary border-4 p-3 rounded mb-3 mt-4">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase"><i class="bi bi-tags-fill text-primary me-2"></i>DIVISI / BAGIAN: <span class="text-primary">${b}</span></h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 fs-6">Subtotal: Rp ${formatRupiah(divTotal)}</span>
                    </div>
                `;
            }
            
            notaHtml += `<div class="row g-3">`;

            groupedNotas[b].forEach((n, idx) => {
                totalLaporan += Number(n.total_belanja);
                let nItemsHtml = '';
                if(n.items && n.items.length > 0) {
                    n.items.forEach(ni => {
                        nItemsHtml += `<li class="list-group-item d-flex justify-content-between px-3 py-2 border-light">
                                        <div><div class="fw-medium text-dark">${ni.nama_barang}</div><div class="text-muted" style="font-size:0.75rem">${ni.qty} ${ni.satuan || ''} x ${formatRupiah(ni.harga_satuan)}</div></div>
                                        <div class="fw-bold text-dark align-self-center">Rp ${formatRupiah(ni.subtotal)}</div>
                                     </li>`;
                    });
                }
                notaHtml += `
                    <div class="col-md-6">
                        <div class="card border border-secondary-subtle shadow-sm h-100 rounded-4 overflow-hidden">
                            <div class="card-header bg-light border-bottom border-secondary-subtle d-flex justify-content-between align-items-center py-3 px-3">
                                <div>
                                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;">No Nota</div>
                                    <div class="fw-bold">${n.nomor_nota || '-'}</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;">Tanggal</div>
                                    <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1">${n.tanggal}</div>
                                </div>
                            </div>
                            <div class="card-body p-0 bg-white">
                                <ul class="list-group list-group-flush small">
                                    ${nItemsHtml}
                                </ul>
                            </div>
                            <div class="card-footer bg-success bg-opacity-10 border-top border-success border-opacity-25 d-flex justify-content-between align-items-center py-3 px-3">
                                <span class="fw-bold text-success small text-uppercase" style="letter-spacing: 1px;">Subtotal</span>
                                <span class="fw-bold text-success fs-5">Rp ${formatRupiah(n.total_belanja)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            notaHtml += `</div>`;
        });
                
        notaHtml += `
                <div class="card bg-dark text-white mt-4 shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-wallet2 fs-4 text-white"></i>
                            </div>
                            <div>
                                <div class="text-white-50 small mb-1 text-uppercase fw-bold" style="letter-spacing: 1px;">Ringkasan Akhir</div>
                                <h4 class="mb-0 fw-bold">Total Laporan Realisasi</h4>
                            </div>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold text-warning">Rp ${formatRupiah(totalLaporan)}</h2>
                            <div class="${(p.total_disetujui - totalLaporan) < 0 ? 'text-danger bg-danger bg-opacity-10 px-2 py-1 rounded d-inline-block' : 'text-info'} small mt-2 fw-medium">
                                Sisa Anggaran: Rp ${formatRupiah(p.total_disetujui - totalLaporan)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (isApproved) {
        notaHtml = `
            <div class="mt-5 border-top pt-5 pb-4 text-center text-muted bg-light rounded-4 border border-secondary-subtle">
                <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                <h6 class="fw-bold mb-1">Belum Ada Laporan Realisasi</h6>
                <div class="small">Laporan nota belanja yang diinput akan muncul di sini.</div>
            </div>
        `;
    }

    Swal.fire({
        title: false,
        showConfirmButton: false,
        showCloseButton: true,
        width: isApproved ? 900 : 800,
        padding: 0,
        html: `
            <div class="text-start bg-white rounded-4 overflow-hidden position-relative">
                <div class="bg-primary p-4 text-white position-relative shadow-sm" style="z-index: 1;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1">${isApproved ? 'RINCIAN ANGGARAN OPERASIONAL' : 'RENCANA ANGGARAN'}</h3>
                            <div class="text-white-50 small">ID Dokumen: #ANGG-${p.id.toString().padStart(4, '0')}</div>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-0">${p.instansi}</h5>
                            <div class="text-white-50 small">Periode: ${p.bulan_hijriah}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-light p-3 border-bottom border-secondary-subtle d-flex flex-wrap justify-content-end gap-2 position-relative" style="z-index: 1;">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm fw-bold" onclick="printLaporanAnggaran(window.currentPrintP, window.currentPrintNotas)"><i class="bi bi-file-earmark-bar-graph me-1"></i>Cetak Laporan Detail</button>
                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold" onclick="printSuratPengajuan(window.currentPrintP)"><i class="bi bi-printer me-1"></i>Cetak Rencana Anggaran</button>
                </div>
                
                <div class="p-4 pt-4 position-relative" style="z-index: 1;">
                    ${stampHtml}
                    ${isApproved ? '<h5 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-text me-2 text-primary"></i>RINCIAN & PENETAPAN PAGU</h5>' : ''}
                    
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover mb-4" style="font-size: 0.9rem;">
                            <thead class="border-bottom border-secondary-subtle">
                                <tr class="text-muted small text-uppercase">
                                    <th class="text-center pb-2">No</th>
                                    <th class="pb-2">Rincian Kebutuhan</th>
                                    <th class="text-end pb-2">Nilai Rencana</th>
                                    <th class="text-end pb-2 text-success">Pagu Ditetapkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemHtml}
                            </tbody>
                            <tfoot class="border-top border-secondary-subtle">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold py-3">TOTAL KESELURUHAN :</td>
                                    <td class="text-end fw-bold text-secondary py-3">Rp ${formatRupiah(p.total_ajuan)}</td>
                                    <td class="text-end fw-bold text-success fs-5 py-3">Rp ${formatRupiah(p.total_disetujui)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    ${p.catatan_admin ? `
                    <div class="bg-warning bg-opacity-10 border-start border-warning border-4 p-3 rounded small mb-4 shadow-sm">
                        <div class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-info-circle me-1"></i>Catatan Anggaran:</div>
                        <div class="text-dark">${p.catatan_admin}</div>
                    </div>` : ''}

                    ${notaHtml}

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-5 border-top border-secondary-subtle pt-4" data-html2canvas-ignore="true">
                        <button class="btn btn-secondary rounded-pill px-5 shadow-sm fw-medium" onclick="Swal.close()">Tutup</button>
                    </div>
                </div>
            </div>
        `
    });
}

// ==========================================
// LAPOR NOTA
// ==========================================
let currentPengajuanId = null;
let currentNotaItems = [];
let currentPengajuan = null;
let loadedNotas = [];

function switchMobileNotaTab(tab) {
    const tabForm = document.getElementById('tabMobileNotaForm');
    const tabList = document.getElementById('tabMobileNotaList');
    const colForm = document.getElementById('colNotaForm');
    const colList = document.getElementById('colNotaList');
    
    if (!tabForm || !tabList || !colForm || !colList) return;
    
    if (tab === 'form') {
        tabForm.classList.add('active');
        tabList.classList.remove('active');
        colForm.classList.remove('d-none');
        colList.classList.add('d-none');
    } else {
        tabList.classList.add('active');
        tabForm.classList.remove('active');
        colList.classList.remove('d-none');
        colForm.classList.add('d-none');
    }
}

function resetNotaForm() {
    document.getElementById('n_nota_id').value = '';
    const titleEl = document.getElementById('lblNotaFormTitle');
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-plus-square me-1"></i> Tambah Nota Baru';
    
    let nextNum = (loadedNotas.length + 1).toString();
    document.getElementById('n_nomor').value = nextNum;
    document.getElementById('n_bagian_nota').value = '';
    document.getElementById('n_tanggal').value = '<?= date('Y-m-d') ?>';
    document.getElementById('n_file').value = '';
    
    document.getElementById('nb_nama').value = '';
    document.getElementById('nb_qty').value = 1;
    document.getElementById('nb_harga').value = '';
    document.getElementById('nb_satuan').value = '';
    document.getElementById('nb_bagian').value = '';
    
    currentNotaItems = [];
    renderNotaItems();
}

function laporNota(id) {
    currentPengajuanId = id;
    currentPengajuan = allPengajuans.find(p => p.id == id);
    currentNotaItems = [];
    document.getElementById('n_pengajuan_id').value = id;
    document.getElementById('n_nota_id').value = '';
    document.getElementById('n_nomor').value = '';
    document.getElementById('n_bagian_nota').value = '';
    document.getElementById('n_file').value = '';
    
    const titleEl = document.getElementById('lblNotaFormTitle');
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-plus-square me-1"></i> Tambah Nota Baru';
    
    let datalist = document.getElementById('listBagianNota');
    if (datalist) {
        datalist.innerHTML = '';
        let bagians = new Set();
        if (currentPengajuan && currentPengajuan.items) {
            currentPengajuan.items.forEach(it => {
                if (it.bagian && it.bagian.trim() !== '') bagians.add(it.bagian.trim());
            });
        }
        bagians.forEach(b => {
            let opt = document.createElement('option');
            opt.value = b;
            datalist.appendChild(opt);
        });
    }
    
    renderNotaItems();
    loadNotaList();
    
    if (window.innerWidth < 768) {
        switchMobileNotaTab('form');
    }
    
    new bootstrap.Modal(document.getElementById('modalNota')).show();
    setTimeout(() => {
        document.getElementById('n_nomor').focus();
    }, 500);
}

function editNota(id) {
    const nota = loadedNotas.find(n => n.id == id);
    if(!nota) return;
    
    document.getElementById('n_nota_id').value = nota.id;
    document.getElementById('n_nomor').value = nota.nomor_nota;
    document.getElementById('n_bagian_nota').value = nota.bagian || '';
    document.getElementById('n_tanggal').value = nota.tanggal;
    
    const titleEl = document.getElementById('lblNotaFormTitle');
    if (titleEl) titleEl.innerHTML = `<i class="bi bi-pencil-square me-1 text-warning"></i> Edit Nota #${nota.nomor_nota}`;
    
    currentNotaItems = nota.items.map(it => ({
        nama_barang: it.nama_barang,
        qty: it.qty,
        satuan: it.satuan,
        bagian: it.bagian,
        harga_satuan: it.harga_satuan
    }));
    
    renderNotaItems();
    
    if (window.innerWidth < 768) {
        switchMobileNotaTab('form');
    }
    
    document.getElementById('n_nomor').focus();
}

function notaInputKeydown(e, nextId, prevId) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (e.shiftKey) {
            if (prevId) {
                let el = document.getElementById(prevId);
                if (el) el.focus();
            }
        } else {
            if (nextId === 'ADD_BARANG') {
                addBarangNota();
            } else if (nextId) {
                let el = document.getElementById(nextId);
                if (el) el.focus();
            }
        }
    }
}

document.getElementById('modalNota')?.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        simpanNota();
    }
});

function addBarangNota() {
    const nama = document.getElementById('nb_nama').value;
    const qty = document.getElementById('nb_qty').value;
    const hrg = document.getElementById('nb_harga').value;
    const satuan = document.getElementById('nb_satuan').value;
    const bagian = document.getElementById('nb_bagian').value;
    if(!nama || !qty || !hrg) {
        Swal.fire({
            icon: 'warning',
            title: 'Lengkapi Data Barang',
            text: 'Nama barang, jumlah (qty), dan harga satuan wajib diisi.',
            timer: 1500,
            showConfirmButton: false
        });
        return;
    }
    
    currentNotaItems.push({ nama_barang: nama, qty: qty, harga_satuan: hrg, satuan: satuan, bagian: bagian });
    document.getElementById('nb_nama').value = '';
    document.getElementById('nb_qty').value = 1;
    document.getElementById('nb_harga').value = '';
    document.getElementById('nb_satuan').value = '';
    document.getElementById('nb_bagian').value = '';
    document.getElementById('nb_nama').focus();
    renderNotaItems();
}

function renderNotaItems() {
    const container = document.getElementById('tblBarangNota');
    container.innerHTML = '';
    let total = 0;
    currentNotaItems.forEach((it, idx) => {
        let sub = Number(it.qty) * Number(it.harga_satuan);
        total += sub;
        let metaParts = [];
        if (it.qty && it.harga_satuan) metaParts.push(`${it.qty}${it.satuan ? ' ' + it.satuan : ''} × Rp ${formatRupiah(it.harga_satuan)}`);
        if (it.bagian) metaParts.push(`Bag. ${escapeHtml(it.bagian)}`);
        container.innerHTML += `
            <div class="nota-barang-item">
                <div class="flex-grow-1 min-width-0">
                    <div class="item-name">${escapeHtml(it.nama_barang)}</div>
                    <div class="item-meta">${metaParts.join(' · ')}</div>
                </div>
                <span class="item-price">Rp ${formatRupiah(sub)}</span>
                <button class="item-del" onclick="removeBarangNota(${idx})" title="Hapus">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
    });
    document.getElementById('nb_total').innerText = 'Rp ' + formatRupiah(total);
}

function removeBarangNota(idx) {
    currentNotaItems.splice(idx, 1);
    renderNotaItems();
}

function loadNotaList() {
    const c = document.getElementById('notaListContainer');
    c.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat nota...</div>';
    
    fetch(`<?= API_URL ?>/api/anggaran/${currentPengajuanId}/nota`).then(r=>r.json()).then(res=>{
        const countEl = document.getElementById('mobileNotaCount');
        if (countEl) countEl.innerText = (res.data && Array.isArray(res.data)) ? res.data.length : 0;

        if(!res.data || res.data.length === 0) {
            c.innerHTML = `<div class="alert alert-light border text-center text-muted py-4"><i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>Belum ada laporan nota masuk</div>`;
            document.getElementById('n_nomor').value = '1';
            return;
        }
        
        loadedNotas = res.data;
        if (!document.getElementById('n_nota_id').value) {
            document.getElementById('n_nomor').value = (res.data.length + 1).toString();
        }
        let hasAnyBagian = res.data.some(n => n.bagian && n.bagian.trim() !== '');
        let groupedNotas = {};
        
        if (hasAnyBagian) {
            res.data.forEach(n => {
                let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
                if (!groupedNotas[b]) groupedNotas[b] = [];
                groupedNotas[b].push(n);
            });
        } else {
            groupedNotas['SEMUA'] = res.data;
        }
        
        let keys = Object.keys(groupedNotas);
        if (hasAnyBagian) {
            keys = keys.sort((a, b) => {
                if (a === 'LAIN-LAIN') return 1;
                if (b === 'LAIN-LAIN') return -1;
                return a.localeCompare(b);
            });
        }
        
        let htmlContent = '';
        keys.forEach(b => {
            if (hasAnyBagian) {
                let divTotal = groupedNotas[b].reduce((acc, n) => acc + Number(n.total_belanja), 0);
                htmlContent += `
                <div class="d-flex justify-content-between align-items-center bg-light border-start border-primary border-4 p-2 rounded mb-2 mt-3 shadow-xs">
                    <h6 class="mb-0 fw-bold text-dark text-uppercase" style="font-size:0.82rem; letter-spacing: 0.5px;"><i class="bi bi-tags-fill text-primary me-1.5"></i>${escapeHtml(b)}</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem">Rp ${formatRupiah(divTotal)}</span>
                </div>
                `;
            }
            
            groupedNotas[b].forEach(n => {
                let trs = '';
                n.items.forEach(it => {
                    trs += `<tr><td class="py-1 text-muted">${escapeHtml(it.nama_barang)} <span class="badge bg-light text-dark ms-1">${it.qty} ${escapeHtml(it.satuan || 'x')}</span> ${it.bagian ? `<span class="badge bg-light text-dark ms-1">Bag. ${escapeHtml(it.bagian)}</span>` : ''}</td><td class="py-1 text-end fw-medium">Rp ${formatRupiah(it.subtotal)}</td></tr>`;
                });
                
                htmlContent += `
                <div class="card border border-success-subtle shadow-xs rounded-3 overflow-hidden mb-2.5">
                    <div class="card-header bg-success bg-opacity-10 border-0 py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-1.5">
                            <i class="bi bi-receipt-cutoff text-success me-1"></i><strong class="text-dark">${escapeHtml(n.nomor_nota)}</strong>
                            ${!hasAnyBagian && n.bagian ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem">${escapeHtml(n.bagian)}</span>` : ''}
                            <span class="text-muted small ms-1">${n.tanggal}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 ms-auto">
                            <button class="btn btn-sm btn-white border shadow-xs text-warning py-1 px-2.5 rounded-pill" onclick="editNota(${n.id})" title="Edit Nota"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-white border shadow-xs text-primary py-1 px-2.5 rounded-pill" onclick="printNotaCard(this)" title="Cetak Nota"><i class="bi bi-printer"></i></button>
                            ${n.file_path ? `<a href="${n.file_path.startsWith('/uploads/') ? n.file_path : '<?= API_URL ?>/api/anggaran/view-nota/' + n.id}" target="_blank" class="btn btn-sm btn-white border shadow-xs text-primary py-1 px-2.5 rounded-pill" title="Lihat Bukti Foto"><i class="bi bi-image"></i></a>` : ''}
                            <button class="btn btn-sm btn-white border shadow-xs text-danger py-1 px-2.5 rounded-pill" onclick="deleteNota(${n.id})" title="Hapus Nota"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless m-0" style="font-size:0.84rem">
                            <tbody class="px-3 d-block pt-2 pb-2">${trs}</tbody>
                            <tfoot class="border-top bg-light d-block px-3 py-2">
                                <tr class="d-flex justify-content-between align-items-center w-100">
                                    <td class="fw-bold p-0 text-muted small">Total Nota:</td>
                                    <td class="fw-bold text-success p-0 fs-6">Rp ${formatRupiah(n.total_belanja)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>`;
            });
        });
        
        c.innerHTML = htmlContent;
    });
}

function simpanNota() {
    if(currentNotaItems.length === 0) {
        Swal.fire('Peringatan', 'Silakan masukkan minimal 1 rincian barang terlebih dahulu.', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('nota_id', document.getElementById('n_nota_id').value);
    formData.append('nomor_nota', document.getElementById('n_nomor').value);
    formData.append('bagian', document.getElementById('n_bagian_nota').value);
    formData.append('tanggal', document.getElementById('n_tanggal').value);
    formData.append('items', JSON.stringify(currentNotaItems));
    
    const file = document.getElementById('n_file').files[0];
    if(file) formData.append('file_nota', file);
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    
    const btn = document.getElementById('btnSimpanNota');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    btn.disabled = true;
    
    fetch(`<?= API_URL ?>/api/anggaran/${currentPengajuanId}/nota/store`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf },
        body: formData
    }).then(r=>r.json()).then(res=>{
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if(res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Nota Berhasil Disimpan',
                timer: 1200,
                showConfirmButton: false
            });
            
            resetNotaForm();
            loadNotaList();
            
            if (window.innerWidth < 768) {
                switchMobileNotaTab('list');
            }
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
    });
}

function deleteNota(id) {
    Swal.fire({
        title: 'Hapus Nota?',
        text: "Saldo akan dikembalikan.",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/nota/${id}/delete`, { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
            .then(r=>r.json()).then(res=>{
                if(res.success) loadNotaList();
            });
        }
    });
}

function tandaiDilaporkan(id) {
    Swal.fire({
        title: 'Tandai Sebagai Dilaporkan?',
        text: "Anda akan menandai anggaran ini sebagai telah dilaporkan ke ADM. Pastikan nota sudah Anda input.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tandai'
    }).then((res) => {
        if(res.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ status: 'dilaporkan' })
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire('Berhasil', 'Anggaran ditandai sebagai Dilaporkan!', 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

function tandaiSelesai(id) {
    Swal.fire({
        title: 'Selesaikan Anggaran Bulan Ini?',
        text: "Pastikan semua nota telah dilaporkan. Anggaran akan ditandai selesai.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesai'
    }).then((res) => {
        if(res.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ status: 'selesai' })
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire('Berhasil', 'Anggaran Selesai!', 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

document.getElementById('modalNota')?.addEventListener('hidden.bs.modal', function () {
    location.reload();
});

function printHtml(htmlContent, pondokKeyOverride) {
    let printArea = document.getElementById('printArea');
    
    if (printArea.parentNode !== document.body) {
        document.body.appendChild(printArea);
    }

    // Baca page_padding dari konfigurasi kanvas yang tersimpan
    // supaya tampilan print PERSIS sama dengan preview kanvas
    let pondokKeyForPrint = pondokKeyOverride || 'default';
    if (!pondokKeyOverride) {
        // Fallback: ambil dari select cfg_pondok di studio kanvas jika ada
        let pondokSelectEl = document.getElementById('cfg_pondok');
        if (pondokSelectEl && pondokSelectEl.value) {
            pondokKeyForPrint = pondokSelectEl.value;
        } else if (typeof instansiMap === 'object' && instansiMap !== null) {
            pondokKeyForPrint = Object.keys(instansiMap)[0] || 'default';
        }
    }
    let printCfg = getSavedKopConfig(pondokKeyForPrint);
    let pagePaddingPx = printCfg.page_padding !== undefined ? printCfg.page_padding : 8;
    // Gunakan px langsung untuk padding konten — @page margin dibuat 0 agar tidak ada
    // header/footer bawaan browser (judul halaman, URL, nomor halaman)
    // Top/bottom margin konten dikontrol lewat padding #printPageWrapper
    
    // Wrap content in A4-width constrained container
    printArea.innerHTML = `
        <div id="printPageWrapper" style="
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
            padding: 6mm ${pagePaddingPx}px 6mm ${pagePaddingPx}px;
            font-family: 'Times New Roman', Times, serif;
        ">
            ${htmlContent}
        </div>
    `;
    
    printArea.style.display = 'block';
    printArea.style.position = 'absolute';
    printArea.style.top = '0';
    printArea.style.left = '0';
    printArea.style.width = '100%';
    printArea.style.minHeight = '100vh';
    printArea.style.backgroundColor = '#e8e8e8';
    printArea.style.zIndex = '999999';
    printArea.style.padding = '20px 0';
    
    const tempStyle = document.createElement('style');
    tempStyle.id = 'tempPrintStyle';
    tempStyle.innerHTML = `
        @media print {
            @page {
                size: A4 portrait;
                /* margin: 0 — menghapus header/footer bawaan browser
                   (judul halaman, URL, nomor halaman, tanggal cetak) */
                margin: 0;
            }
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-family: 'Times New Roman', Times, serif !important;
            }
            body > *:not(#printArea):not(script):not(style) {
                display: none !important;
            }
            #printArea {
                display: block !important;
                position: static !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }
            #printPageWrapper {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                /* Padding konten sesuai setting kanvas — menggantikan @page margin */
                padding-top: 6mm !important;
                padding-bottom: 6mm !important;
                padding-left: ${pagePaddingPx}px !important;
                padding-right: ${pagePaddingPx}px !important;
                box-shadow: none !important;
                background: #ffffff !important;
                font-family: 'Times New Roman', Times, serif !important;
            }
            /* Paksa Times New Roman ke semua elemen dalam area cetak */
            #printPageWrapper * {
                font-family: 'Times New Roman', Times, serif !important;
            }
        }
        #printPageWrapper {
            box-shadow: 0 2px 16px rgba(0,0,0,0.18);
        }
    `;
    document.head.appendChild(tempStyle);
    
    setTimeout(() => {
        const imgs = printArea.querySelectorAll('img');
        let loadedCount = 0;
        
        function doPrint() {
            setTimeout(() => {
                window.print();
                printArea.style.display = 'none';
                printArea.innerHTML = '';
                printArea.style.padding = '';
                printArea.style.backgroundColor = '';
                if (document.getElementById('tempPrintStyle')) {
                    document.head.removeChild(tempStyle);
                }
            }, 200);
        }

        if (imgs.length === 0) {
            doPrint();
        } else {
            let fallbackTimeout = setTimeout(doPrint, 2500);
            
            imgs.forEach(img => {
                if (img.complete) {
                    loadedCount++;
                    if (loadedCount === imgs.length) {
                        clearTimeout(fallbackTimeout);
                        doPrint();
                    }
                } else {
                    img.addEventListener('load', () => {
                        loadedCount++;
                        if (loadedCount === imgs.length) {
                            clearTimeout(fallbackTimeout);
                            doPrint();
                        }
                    });
                    img.addEventListener('error', () => {
                        loadedCount++;
                        if (loadedCount === imgs.length) {
                            clearTimeout(fallbackTimeout);
                            doPrint();
                        }
                    });
                }
            });
        }
    }, 100);
}

function printNotaCard(btnEl) {
    const cardHtml = btnEl.closest('.card').outerHTML;
    printHtml(`
        <div class="p-4 bg-white" style="font-family: 'Times New Roman', Times, serif;">
            <h3 class="fw-bold mb-4 border-bottom pb-2">Bukti Laporan Pembelanjaan (Nota)</h3>
            ${cardHtml}
        </div>
    `);
}

function formatRupiahKoma(angka) {
    if (isNaN(angka) || angka === null || angka === undefined) return 'Rp0,00';
    let isNeg = Number(angka) < 0;
    let num = Math.round(Math.abs(Number(angka)));
    let str = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return (isNeg ? '-Rp' : 'Rp') + str + ',00';
}

function getFormattedPrintDate(dateStr) {
    let d = dateStr ? new Date(dateStr) : new Date();
    if (isNaN(d.getTime())) d = new Date();
    const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return hari[d.getDay()] + ', ' + d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
}

function getFormattedPrintDateId(dateStr) {
    return getFormattedPrintDate(dateStr);
}

// ==========================================
// DYNAMIC FULL-TOP HEADER KOP SURAT GENERATOR
// ==========================================
function getKopSuratHtml(p, isClassic) {
    let pondokKey = p.instansi || p.pondok || '';
    let targetInstansi = (typeof instansiMap === 'object' && instansiMap !== null) ? (instansiMap[pondokKey] || {}) : {};
    let kodeInstansi = targetInstansi.kode || targetInstansi.kode_instansi || pondokKey || '';
    let kepengurusan = targetInstansi.kepengurusan || targetInstansi.def_kepengurusan || 'Ponorogo';
    let emailInstansi = targetInstansi.email || 'plngontor@gmail.com';
    let uniqueId = 'kop_' + (p.id || Math.floor(Math.random() * 100000));
    
    let arabLocMap = {
        'Ponorogo': 'فونوروغو', 'Mantingan': 'مانتينجان', 'Kediri': 'كينديري',
        'Banyuwangi': 'بانيوانجي', 'Magelang': 'ماجيلانج',
        'G1': 'فونوروغو', 'GP': 'مانتينجان', 'G2': 'فونوروغو',
        'G3': 'كينديري', 'G4': 'بانيوانجي', 'G5': 'ماجيلانج'
    };
    let arabLoc = arabLocMap[kepengurusan] || arabLocMap[pondokKey] || 'فونوروغو';
    let englishLoc = 'GONTOR-' + kepengurusan.toUpperCase() + '-INDONESIA';

    let imgSrc = `<?= API_URL ?>/profil-instansi/kop-surat/view?kode=${encodeURIComponent(kodeInstansi)}&v=${new Date().getTime()}`;

    // Selalu baca konfigurasi yang tersimpan dari canvas — print HARUS mengikuti canvas
    let cfg = getSavedKopConfig(pondokKey);

    if (cfg.mode === 'none') {
        return `<div style="height: 5px;"></div>`;
    }

    // GUNAKAN PERSIS SETTING CANVAS — tidak ada override dari isClassic untuk posisi
    let kopWidth = cfg.width !== undefined ? cfg.width : 100;
    let kopMaxHeight = cfg.max_height !== undefined ? cfg.max_height : 140; // sesuai default kanvas
    let marginTop = cfg.margin_top !== undefined ? cfg.margin_top : 0;
    let marginBottom = cfg.margin_bottom !== undefined ? cfg.margin_bottom : 18; // sesuai default kanvas
    let alignVal = cfg.align || 'center';
    let alignStyle = alignVal === 'left' ? 'margin-left: 0; margin-right: auto;'
                   : alignVal === 'right' ? 'margin-left: auto; margin-right: 0;'
                   : 'margin-left: auto; margin-right: auto;';

    // Teks & warna dari canvas (custom atau default)
    let txtEn1 = cfg.txt_en1 || 'ADVISORY OF FOREIGN STUDENT';
    let txtEn2 = cfg.txt_en2 || 'DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL';
    let txtEn3 = cfg.txt_en3 || englishLoc;
    let txtAr1 = cfg.txt_ar1 || 'هيئة إشراف شؤون الطلاب الوافدين';
    let txtAr2 = cfg.txt_ar2 || ('معهد دار السلام كونتور ' + arabLoc);
    let txtAr3 = cfg.txt_ar3 || 'للتربية الإسلامية الحديثة';
    let txtFoot = cfg.txt_foot || ('Head Office : Solihin One Building Second Floor Room 203, Email: ' + emailInstansi);
    let borderColor = cfg.color_border || '#0056b3';
    let borderWidth = cfg.border_width || '1.5px';

    // Box kop surat (teks Inggris kiri, Arab kanan, pita bawah)
    let boxHtml = `
        <div id="${uniqueId}_fallback_box" style="width: ${kopWidth}%; ${alignStyle} border: ${borderWidth} solid ${borderColor}; margin-top: ${marginTop}px; margin-bottom: ${marginBottom}px; font-family: 'Times New Roman', Times, serif; background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box;">
            <div style="display: flex; justify-content: space-between; align-items: center; color: ${borderColor}; padding: 7px 12px 5px 12px;">
                <div style="text-align: left; line-height: 1.25;">
                    <div style="font-weight: bold; font-size: 16.5px; font-family: 'Times New Roman', Times, serif; letter-spacing: 0.2px; color: ${borderColor};">${txtEn1}</div>
                    <div style="font-size: 10.5px; font-weight: bold; font-family: 'Times New Roman', Times, serif; margin-top: 2px; color: ${borderColor};">${txtEn2}</div>
                    <div style="font-size: 10.5px; font-weight: bold; font-family: 'Times New Roman', Times, serif; margin-top: 1px; color: ${borderColor};">${txtEn3}</div>
                </div>
                <div style="text-align: right; line-height: 1.3;" dir="rtl">
                    <div style="font-weight: bold; font-size: 20px; font-family: 'Times New Roman', serif; color: ${borderColor};">${txtAr1}</div>
                    <div style="font-size: 12.5px; font-weight: bold; font-family: 'Times New Roman', serif; margin-top: 2px; color: ${borderColor};">${txtAr2}</div>
                    <div style="font-size: 12.5px; font-weight: bold; font-family: 'Times New Roman', serif; margin-top: 1px; color: ${borderColor};">${txtAr3}</div>
                </div>
            </div>
            <div style="background-color: ${borderColor}; color: white; text-align: center; padding: 2px 6px; font-style: italic; font-size: 10px; font-family: 'Times New Roman', Times, serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; letter-spacing: 0.2px;">
                ${txtFoot}
            </div>
        </div>
    `;

    // Render sesuai mode yang dipilih di canvas
    if (cfg.mode === 'box_only') {
        return boxHtml;
    } else if (cfg.mode === 'image_only') {
        return `
            <div style="width: 100%; text-align: ${alignVal}; margin-top: ${marginTop}px; margin-bottom: ${marginBottom}px; line-height: 0;">
                <img src="${imgSrc}" style="width: ${kopWidth}%; max-height: ${kopMaxHeight}px; object-fit: contain; object-position: top; display: inline-block;">
            </div>
        `;
    } else {
        // Mode Auto: Gambar dengan fallback ke box teks
        return `
            <div style="width: 100%; text-align: ${alignVal}; margin-top: ${marginTop}px; margin-bottom: ${marginBottom}px; line-height: 0;">
                <img src="${imgSrc}" style="width: ${kopWidth}%; max-height: ${kopMaxHeight}px; object-fit: contain; object-position: top; display: inline-block;" 
                     onerror="this.style.display='none'; document.getElementById('${uniqueId}_fallback_box').style.display='block';">
            </div>
            ${boxHtml.replace(`id="${uniqueId}_fallback_box" style="`, `id="${uniqueId}_fallback_box" style="display: none; `)}
        `;
    }
}

// ==========================================
// MODAL PEMILIH FORMAT CETAK (CHOOSER)
// ==========================================
function choosePrintFormat(title, onSelectFormat) {
    Swal.fire({
        title: `<div class="d-flex align-items-center justify-content-center gap-2 text-dark fs-5 fw-bold" style="font-family: 'Times New Roman', Times, serif;"><i class="bi bi-printer text-primary"></i>${title}</div>`,
        html: `
            <div class="text-start p-2">
                <div class="text-muted small mb-3 text-center">Silakan pilih format cetak dokumen:</div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="card h-100 border-2 border-primary border-opacity-50 p-3 rounded-4 shadow-sm text-center" 
                             style="cursor: pointer; transition: all 0.2s; background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 100%);" 
                             onmouseover="this.style.borderColor='#0056b3'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 16px rgba(0,86,179,0.15)'"
                             onmouseout="this.style.borderColor='rgba(13,110,253,0.5)'; this.style.transform='none'; this.style.boxShadow='none'"
                             onclick="selectPrintOption('classic')">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 52px; height: 52px;">
                                <i class="bi bi-file-earmark-font fs-3 text-primary"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" style="font-family: 'Times New Roman', Times, serif;">Format Standar (Buku Pedoman)</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mb-2">A4 - Times New Roman</span>
                            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Sesuai dokumen resmi: Kop surat standar, border tabel hitam tegas, font Times New Roman, dan tanggal Bahasa Indonesia.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card h-100 border-2 border-secondary border-opacity-25 p-3 rounded-4 shadow-sm text-center" 
                             style="cursor: pointer; transition: all 0.2s; background: #fff;" 
                             onmouseover="this.style.borderColor='#0d6efd'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 16px rgba(13,110,253,0.15)'"
                             onmouseout="this.style.borderColor='rgba(108,117,125,0.25)'; this.style.transform='none'; this.style.boxShadow='none'"
                             onclick="selectPrintOption('modern')">
                            <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 52px; height: 52px;">
                                <i class="bi bi-stars fs-3 text-dark"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Format Modern (Eksekutif)</h6>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle mb-2">A4 - Executive Layout</span>
                            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Tampilan kontemporer: Kop surat posisi & ukuran sama, kartu KPI ringkasan keuangan, dan layout modern.</p>
                        </div>
                    </div>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6c757d',
        customClass: {
            cancelButton: 'rounded-pill px-4'
        },
        width: 600,
        didOpen: () => {
            window.selectPrintOption = (format) => {
                Swal.close();
                onSelectFormat(format);
            };
        }
    });
}

// ==========================================
// 1. CETAK PENGAJUAN (CLASSIC & MODERN)
// ==========================================
function printSuratPengajuan(p, format = null) {
    if (!p) return;
    if (!format) {
        choosePrintFormat('Cetak Surat Pengajuan Anggaran', (selectedFormat) => {
            printSuratPengajuan(p, selectedFormat);
        });
        return;
    }

    if (format === 'classic') {
        printSuratPengajuanClassic(p);
    } else {
        printSuratPengajuanModern(p);
    }
}

function printSuratPengajuanClassic(p) {
    let dateFormatted = getFormattedPrintDate(p.tanggal_pengajuan);
    let bulanStr = (p.bulan_hijriah || '').toUpperCase();
    let kopHtml = getKopSuratHtml(p, true);

    let rowsHtml = '';
    let totalNominal = 0;

    (p.items || []).forEach((it, idx) => {
        let nominal = parseFloat(it.nominal_ajuan || (it.qty * it.harga_satuan) || 0);
        totalNominal += nominal;
        let satuan = it.satuan ? it.satuan.trim() : '';
        let harga = parseFloat(it.harga_satuan || 0);

        rowsHtml += `
            <tr style="border-bottom: 1px solid #000;">
                <td style="border: 1px solid #000; padding: 5px 4px; text-align: center; width: 45px;">${idx + 1}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: left;">${it.nama_item || ''}</td>
                <td style="border: 1px solid #000; padding: 5px 4px; text-align: center; width: 65px;">${it.qty || 1}</td>
                <td style="border: 1px solid #000; padding: 5px 4px; text-align: center; font-style: italic; width: 75px;">${satuan}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: center; width: 140px;">${formatRupiahKoma(harga)}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: center; width: 150px;">${formatRupiahKoma(nominal)}</td>
            </tr>
        `;
    });

    let totalKeseluruhan = p.total_ajuan ? parseFloat(p.total_ajuan) : totalNominal;

    let html = `
    <div style="width: 100%; font-family: 'Times New Roman', Times, serif; background: white; color: #000000; line-height: 1.25;">
        ${kopHtml}

        <!-- Judul Format Ajuan.pdf -->
        <div style="text-align: center; margin-top: 10px; margin-bottom: 14px;">
            <div style="font-family: 'Times New Roman', Times, serif; font-weight: bold; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                PENGAJUAN ANGGARAN OPERASIONAL
            </div>
            <div style="font-family: 'Times New Roman', Times, serif; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-top: 2px;">
                PEMBIMBING LUAR NEGERI BULAN ${bulanStr}
            </div>
            <div style="font-family: 'Times New Roman', Times, serif; font-style: italic; margin-top: 4px; font-size: 12.5px;">
                ${dateFormatted}
            </div>
        </div>

        <!-- Tabel Ajuan.pdf -->
        <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 13px; border: 1px solid #000; margin-bottom: 25px;">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="border: 1px solid #000; padding: 5px 4px; width: 45px; text-align: center; font-weight: bold;">No</th>
                    <th style="border: 1px solid #000; padding: 5px 10px; text-align: center; font-weight: bold;">Nama Barang</th>
                    <th style="border: 1px solid #000; padding: 5px 4px; width: 65px; text-align: center; font-weight: bold;">Jumlah</th>
                    <th style="border: 1px solid #000; padding: 5px 4px; width: 75px; text-align: center; font-weight: bold;">Satuan</th>
                    <th style="border: 1px solid #000; padding: 5px 10px; width: 140px; text-align: center; font-weight: bold;">Harga Barang</th>
                    <th style="border: 1px solid #000; padding: 5px 10px; width: 150px; text-align: center; font-weight: bold;">Total</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHtml}
            </tbody>
            <tfoot>
                <tr style="border-top: 1px solid #000; font-weight: bold;">
                    <td colspan="5" style="border: 1px solid #000; padding: 5px 10px; text-align: center;">Total Keseluruhan</td>
                    <td style="border: 1px solid #000; padding: 5px 10px; text-align: center; font-weight: bold;">${formatRupiahKoma(totalKeseluruhan)}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Tanda Tangan Sesuai Ajuan.pdf -->
        <div style="margin-top: 130px; display: flex; justify-content: space-between; font-family: 'Times New Roman', Times, serif; padding: 0 10px; page-break-inside: avoid;">
            <div style="width: 180px;">
                <div style="border-top: 1.5px solid #000; margin-bottom: 4px;"></div>
                <div style="font-weight: bold; font-size: 13px; text-align: left;">Koordinator Kamar</div>
            </div>
            <div style="width: 180px;">
                <div style="border-top: 1.5px solid #000; margin-bottom: 4px;"></div>
                <div style="font-weight: bold; font-size: 13px; text-align: left;">Staff ADM</div>
            </div>
        </div>
    </div>
    `;

    printHtml(html, p.instansi || p.pondok || null);
}

function printSuratPengajuanModern(p) {
    let dateStr = getFormattedPrintDate(p.tanggal_pengajuan);
    let targetInstansi = (typeof instansiMap === 'object' && instansiMap !== null) ? (instansiMap[p.instansi] || instansiMap[p.pondok] || {}) : {};
    let namaInstansi = targetInstansi.nama_instansi || ('Pembimbing Luar Negeri ' + (p.instansi || ''));
    let kopHtml = getKopSuratHtml(p, false);

    let hasAnyBagian = p.items && p.items.some(it => it.bagian && it.bagian.trim() !== '');
    let tbodyHtml = '';

    if (hasAnyBagian) {
        let groupedItems = {};
        p.items.forEach(it => {
            let b = (it.bagian && it.bagian.trim() !== '') ? it.bagian.trim() : 'LAIN-LAIN';
            if (!groupedItems[b]) groupedItems[b] = [];
            groupedItems[b].push(it);
        });
        
        let keys = Object.keys(groupedItems).sort((a, b) => {
            if (a === 'LAIN-LAIN') return 1;
            if (b === 'LAIN-LAIN') return -1;
            return a.localeCompare(b);
        });
        
        let globalIndex = 1;
        keys.forEach(b => {
            let divTotal = groupedItems[b].reduce((sum, it) => sum + parseFloat(it.nominal_ajuan || 0), 0);
            
            tbodyHtml += `
            <tr style="background-color: #e2e8f0; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <td colspan="6" style="padding: 7px 10px; text-align: left; font-weight: 800; color: #1e40af; border: 1px solid #64748b; font-size: 13px;">
                    <i class="bi bi-tag-fill me-1"></i> DIVISI / BAGIAN: ${b.toUpperCase()}
                </td>
            </tr>
            `;
            
            groupedItems[b].forEach(it => {
                tbodyHtml += `
                <tr style="border-bottom: 1px solid #94a3b8;">
                    <td style="padding: 7px 8px; border: 1px solid #94a3b8; color: #000; text-align: center;">${globalIndex++}</td>
                    <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: left; font-weight: 600; color: #000;">${it.nama_item}</td>
                    <td style="padding: 7px 8px; border: 1px solid #94a3b8; text-align: center; font-weight: 600; color: #000;">${it.qty}</td>
                    <td style="padding: 7px 8px; border: 1px solid #94a3b8; text-align: center; color: #334155; font-style: italic;">${it.satuan || '-'}</td>
                    <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: right; color: #000;">Rp ${formatRupiah(it.harga_satuan)}</td>
                    <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: right; font-weight: 700; color: #000;">Rp ${formatRupiah(it.nominal_ajuan)}</td>
                </tr>
                `;
            });
            
            tbodyHtml += `
            <tr style="background-color: #f1f5f9; border-bottom: 2px solid #64748b; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <td colspan="5" style="padding: 7px 10px; text-align: right; font-weight: 700; color: #1e293b; border: 1px solid #64748b;">Subtotal Divisi ${b}</td>
                <td style="padding: 7px 10px; text-align: right; font-weight: 800; color: #1e40af; border: 1px solid #64748b;">Rp ${formatRupiah(divTotal)}</td>
            </tr>
            `;
        });
    } else {
        (p.items || []).forEach((it, i) => {
            tbodyHtml += `
            <tr style="border-bottom: 1px solid #94a3b8;">
                <td style="padding: 7px 8px; border: 1px solid #94a3b8; color: #000; text-align: center;">${i + 1}</td>
                <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: left; font-weight: 600; color: #000;">${it.nama_item}</td>
                <td style="padding: 7px 8px; border: 1px solid #94a3b8; text-align: center; font-weight: 600; color: #000;">${it.qty}</td>
                <td style="padding: 7px 8px; border: 1px solid #94a3b8; text-align: center; color: #334155; font-style: italic;">${it.satuan || '-'}</td>
                <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: right; color: #000;">Rp ${formatRupiah(it.harga_satuan)}</td>
                <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: right; font-weight: 700; color: #000;">Rp ${formatRupiah(it.nominal_ajuan)}</td>
            </tr>
            `;
        });
    }

    let html = `
    <div style="width: 100%; font-family: 'Times New Roman', Times, serif; background: white; color: #000000;">
        ${kopHtml}

        <!-- Header Modern Card -->
        <div style="border: 1.5px solid #475569; border-radius: 8px; padding: 14px 18px; margin-bottom: 18px; background: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="background: #e0e7ff; color: #1e40af; border: 1px solid #93c5fd; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        Proposal Anggaran Operasional
                    </span>
                    <h2 style="font-weight: 800; margin: 8px 0 2px 0; font-size: 18px; color: #000000;">BULAN ${p.bulan_hijriah || ''}</h2>
                    <div style="font-size: 13px; color: #334155; font-weight: 500;">Instansi Pengaju: <strong style="color: #000000;">${namaInstansi}</strong></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 12.5px; color: #334155; font-weight: 600;">No. Ref: <span style="color: #0d6efd; font-weight: 800;">#ANGG-${p.id.toString().padStart(4, '0')}</span></div>
                    <div style="font-size: 12px; color: #334155; margin-top: 3px;"><i class="bi bi-calendar-event me-1"></i> ${dateStr}</div>
                </div>
            </div>
        </div>

        <!-- Table Modern -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; border: 1.5px solid #475569;">
            <thead>
                <tr style="background-color: #e2e8f0; color: #0f172a; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <th style="padding: 8px 6px; border: 1px solid #64748b; width: 5%; text-align: center; font-weight: bold;">No</th>
                    <th style="padding: 8px 10px; border: 1px solid #64748b; text-align: left; font-weight: bold;">Rincian Kebutuhan</th>
                    <th style="padding: 8px 6px; border: 1px solid #64748b; width: 8%; text-align: center; font-weight: bold;">Qty</th>
                    <th style="padding: 8px 6px; border: 1px solid #64748b; width: 12%; text-align: center; font-weight: bold;">Satuan</th>
                    <th style="padding: 8px 10px; border: 1px solid #64748b; text-align: right; width: 18%; font-weight: bold;">Harga Satuan</th>
                    <th style="padding: 8px 10px; border: 1px solid #64748b; text-align: right; width: 20%; font-weight: bold;">Total Nominal</th>
                </tr>
            </thead>
            <tbody>
                ${tbodyHtml}
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; border-top: 2px solid #475569; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <td colspan="5" style="padding: 10px 12px; font-weight: 800; text-align: right; border: 1px solid #64748b; color: #000000; text-transform: uppercase; letter-spacing: 0.5px;">TOTAL PERMOHONAN DANA</td>
                    <td style="padding: 10px 12px; font-weight: 900; text-align: right; font-size: 14.5px; border: 1px solid #64748b; color: #0d6efd;">Rp ${formatRupiah(p.total_ajuan)}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures (Sesuai Format Resmi) -->
        <div style="margin-top: 130px; display: flex; justify-content: space-between; font-family: 'Times New Roman', Times, serif; padding: 0 10px; page-break-inside: avoid;">
            <div style="width: 180px;">
                <div style="border-top: 1.5px solid #000; margin-bottom: 4px;"></div>
                <div style="font-weight: bold; font-size: 13px; text-align: left;">Koordinator Kamar</div>
            </div>
            <div style="width: 180px;">
                <div style="border-top: 1.5px solid #000; margin-bottom: 4px;"></div>
                <div style="font-weight: bold; font-size: 13px; text-align: left;">Staff ADM</div>
            </div>
        </div>
    </div>
    `;

    printHtml(html, p.instansi || p.pondok || null);
}

// ==========================================
// 2. CETAK LAPORAN (CLASSIC & MODERN)
// ==========================================
function printLaporanAnggaran(p, notas, format = null) {
    if (!p) return;
    if (!format) {
        choosePrintFormat('Cetak Laporan Anggaran Operasional', (selectedFormat) => {
            printLaporanAnggaran(p, notas, selectedFormat);
        });
        return;
    }

    if (format === 'classic') {
        printLaporanAnggaranClassic(p, notas);
    } else {
        printLaporanAnggaranModern(p, notas);
    }
}

function printLaporanAnggaranClassic(p, notas) {
    let bulanStr = (p.bulan_hijriah || '').toUpperCase();
    let kopHtml = getKopSuratHtml(p, true);

    let tablesHtml = '';
    let totalKeseluruhanLaporan = 0;

    (notas || []).forEach((n, i) => {
        let notaIdx = n.nomor_nota ? n.nomor_nota : (i + 1);
        let totalNota = 0;
        let rowsHtml = '';

        (n.items || []).forEach((it, idx) => {
            let subtotal = parseFloat(it.subtotal || (it.qty * it.harga_satuan) || 0);
            totalNota += subtotal;
            let qty = it.qty || 1;
            let harga = parseFloat(it.harga_satuan || 0);

            rowsHtml += `
                <tr style="border-bottom: 1px solid #000;">
                    <td style="border: 1px solid #000; padding: 4px 6px; text-align: center; width: 45px;">${idx + 1}</td>
                    <td style="border: 1px solid #000; padding: 4px 10px; text-align: left;">${it.nama_barang || ''}</td>
                    <td style="border: 1px solid #000; padding: 4px 6px; text-align: center; width: 65px;">${qty}</td>
                    <td style="border: 1px solid #000; padding: 4px 10px; text-align: center; width: 135px;">${formatRupiahKoma(harga)}</td>
                    <td style="border: 1px solid #000; padding: 4px 10px; text-align: center; width: 145px;">${formatRupiahKoma(subtotal)}</td>
                </tr>
            `;
        });

        totalKeseluruhanLaporan += totalNota;

        tablesHtml += `
            <div style="margin-bottom: 16px; page-break-inside: avoid;">
                <div style="font-family: 'Times New Roman', Times, serif; font-weight: bold; font-size: 13px; margin-bottom: 3px;">
                    Nota Ke- &nbsp;${notaIdx}
                </div>
                <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 13px; border: 1px solid #000;">
                    <thead>
                        <tr style="border-bottom: 1px solid #000;">
                            <th style="border: 1px solid #000; padding: 5px 4px; width: 45px; text-align: center; font-weight: bold;">No</th>
                            <th style="border: 1px solid #000; padding: 5px 10px; text-align: center; font-weight: bold;">Nama Barang</th>
                            <th style="border: 1px solid #000; padding: 5px 6px; width: 65px; text-align: center; font-weight: bold;">Jumlah</th>
                            <th style="border: 1px solid #000; padding: 5px 10px; width: 135px; text-align: center; font-weight: bold;">Harga Satuan</th>
                            <th style="border: 1px solid #000; padding: 5px 10px; width: 145px; text-align: center; font-weight: bold;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 1px solid #000; font-weight: bold;">
                            <td colspan="4" style="border: 1px solid #000; padding: 5px 10px; text-align: center;">Total Nota ${notaIdx}</td>
                            <td style="border: 1px solid #000; padding: 5px 10px; text-align: center; font-weight: bold;">${formatRupiahKoma(totalNota)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
    });

    let nominalACC = parseFloat(p.total_disetujui || 0);
    let sisa = nominalACC - totalKeseluruhanLaporan;

    let html = `
    <div style="width: 100%; font-family: 'Times New Roman', Times, serif; background: white; color: #000000; line-height: 1.25;">
        ${kopHtml}

        <!-- Judul Format Laporan.pdf -->
        <div style="text-align: center; margin-top: 10px; margin-bottom: 14px;">
            <div style="font-family: 'Times New Roman', Times, serif; font-weight: bold; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                LAPORAN ANGGARAN OPERASIONAL
            </div>
            <div style="font-family: 'Times New Roman', Times, serif; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-top: 2px;">
                PEMBIMBING LUAR NEGERI BULAN ${bulanStr}
            </div>
        </div>

        <!-- Tabel Per Nota Sesuai Laporan.pdf -->
        ${tablesHtml}

        <!-- Tabel Ringkasan Akhir 3 Kolom Sesuai Laporan.pdf -->
        <div style="margin-top: 15px; margin-bottom: 25px; page-break-inside: avoid;">
            <table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', Times, serif; font-size: 13px; border: 1px solid #000; text-align: center;">
                <thead>
                    <tr style="border-bottom: 1px solid #000; font-weight: bold;">
                        <th style="border: 1px solid #000; padding: 6px 10px; width: 33.33%;">Total Belanjaan</th>
                        <th style="border: 1px solid #000; padding: 6px 10px; width: 33.33%;">Anggaran Disetujui</th>
                        <th style="border: 1px solid #000; padding: 6px 10px; width: 33.33%;">Uang Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="font-weight: bold;">
                        <td style="border: 1px solid #000; padding: 6px 10px;">${formatRupiahKoma(totalKeseluruhanLaporan)}</td>
                        <td style="border: 1px solid #000; padding: 6px 10px;">${formatRupiahKoma(nominalACC)}</td>
                        <td style="border: 1px solid #000; padding: 6px 10px;">${sisa < 0 ? '-' : ''}${formatRupiahKoma(Math.abs(sisa))}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    `;

    printHtml(html, p.instansi || p.pondok || null);
}

function printLaporanAnggaranModern(p, notas) {
    let targetInstansi = (typeof instansiMap === 'object' && instansiMap !== null) ? (instansiMap[p.instansi] || instansiMap[p.pondok] || {}) : {};
    let namaInstansi = targetInstansi.nama_instansi || ('Pembimbing Luar Negeri ' + (p.instansi || ''));
    let kopHtml = getKopSuratHtml(p, false);

    let hasAnyBagian = notas && notas.some(n => n.bagian && n.bagian.trim() !== '');
    
    const renderNotaTable = (n, i, isGrouped) => {
        let totalNota = (n.items || []).reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0);
        let tbodyHtml = '';
        let globalIdx = 1;
        
        (n.items || []).forEach(it => {
            tbodyHtml += `
            <tr style="border-bottom: 1px solid #94a3b8;">
                <td style="padding: 7px 8px; border: 1px solid #94a3b8; color: #000; text-align: center;">${globalIdx++}</td>
                <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: left; font-weight: 600; color: #000;">${it.nama_barang}</td>
                <td style="padding: 7px 8px; border: 1px solid #94a3b8; text-align: center; font-weight: 600; color: #000;">${it.qty}</td>
                <td style="padding: 7px 8px; border: 1px solid #94a3b8; text-align: center; color: #334155; font-style: italic;">${it.satuan || '-'}</td>
                <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: right; color: #000;">Rp ${formatRupiah(it.harga_satuan)}</td>
                <td style="padding: 7px 10px; border: 1px solid #94a3b8; text-align: right; font-weight: 700; color: #000;">Rp ${formatRupiah(it.subtotal)}</td>
            </tr>
            `;
        });

        let badgeHtml = (!isGrouped && n.bagian) ? `<div style="background-color: #e2e8f0; border: 1.5px solid #64748b; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; color: #1e293b; -webkit-print-color-adjust: exact; print-color-adjust: exact;">DIVISI: <span style="color: #0d6efd; margin-left: 4px;">${n.bagian.toUpperCase()}</span></div>` : '';

        return `
        <div style="margin-bottom: 18px; page-break-inside: avoid;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <div style="font-weight: 800; font-size: 13.5px; color: #000000;"><i class="bi bi-receipt me-1 text-primary"></i> Nota Ke- ${n.nomor_nota || (i + 1)} <span style="color: #475569; font-size: 12px; font-weight: normal; margin-left: 8px;">(${n.tanggal || '-'})</span></div>
                ${badgeHtml}
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; border: 1.5px solid #475569;">
                <thead>
                    <tr style="background-color: #e2e8f0; color: #0f172a; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <th style="padding: 7px 6px; border: 1px solid #64748b; width: 5%; text-align: center; font-weight: bold;">No</th>
                        <th style="padding: 7px 10px; border: 1px solid #64748b; text-align: left; font-weight: bold;">Nama Barang</th>
                        <th style="padding: 7px 6px; border: 1px solid #64748b; width: 8%; text-align: center; font-weight: bold;">Jumlah</th>
                        <th style="padding: 7px 6px; border: 1px solid #64748b; width: 12%; text-align: center; font-weight: bold;">Satuan</th>
                        <th style="padding: 7px 10px; border: 1px solid #64748b; text-align: right; width: 18%; font-weight: bold;">Harga Satuan</th>
                        <th style="padding: 7px 10px; border: 1px solid #64748b; text-align: right; width: 20%; font-weight: bold;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${tbodyHtml}
                </tbody>
                <tfoot>
                    <tr style="background-color: #f1f5f9; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <td colspan="5" style="padding: 8px 12px; font-weight: 700; text-align: center; border: 1px solid #64748b; color: #000000;">Total Nota ${n.nomor_nota || (i + 1)}</td>
                        <td style="padding: 8px 12px; font-weight: 800; text-align: right; font-size: 13px; border: 1px solid #64748b; color: #0d6efd;">Rp ${formatRupiah(totalNota)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        `;
    };

    let allNotasHtml = '';
    let totalBelanjaan = (notas || []).reduce((acc, n) => acc + (n.items || []).reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0), 0);
    let nominalACC = parseFloat(p.total_disetujui || 0);
    let sisa = nominalACC - totalBelanjaan;

    if (!hasAnyBagian) {
        allNotasHtml = (notas || []).map((n, i) => renderNotaTable(n, i, false)).join('');
    } else {
        let groupedNotas = {};
        (notas || []).forEach(n => {
            let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
            if (!groupedNotas[b]) groupedNotas[b] = [];
            groupedNotas[b].push(n);
        });
        
        let keys = Object.keys(groupedNotas).sort((a, b) => {
            if (a === 'LAIN-LAIN') return 1;
            if (b === 'LAIN-LAIN') return -1;
            return a.localeCompare(b);
        });

        for (let b of keys) {
            let divisiTotal = 0;
            let originalIndexes = groupedNotas[b].map(n => notas.indexOf(n));
            
            let notasHtmlArray = groupedNotas[b].map((n, idx) => {
                divisiTotal += (n.items || []).reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0);
                return renderNotaTable(n, originalIndexes[idx], true);
            });
            
            allNotasHtml += `
            <div style="margin-top: 20px; margin-bottom: 20px;">
                <div style="background-color: #dbeafe; border: 1.5px solid #93c5fd; border-left: 5px solid #1d4ed8; border-radius: 6px; padding: 7px 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                    <h5 style="font-weight: 800; color: #1e40af; margin: 0; font-size: 13.5px; text-transform: uppercase;">
                        <i class="bi bi-tag-fill me-1"></i> DIVISI / BAGIAN: ${b}
                    </h5>
                    <span style="font-weight: 800; font-size: 13px; color: #1d4ed8;">Subtotal: Rp ${formatRupiah(divisiTotal)}</span>
                </div>
                ${notasHtmlArray.join('')}
            </div>
            `;
        }
    }

    let pct = nominalACC > 0 ? Math.min(100, Math.round((totalBelanjaan / nominalACC) * 100)) : 0;

    let html = `
    <div style="width: 100%; font-family: 'Times New Roman', Times, serif; background: white; color: #000000;">
        ${kopHtml}

        <!-- Header Modern Card -->
        <div style="border: 1.5px solid #475569; border-radius: 8px; padding: 14px 18px; margin-bottom: 18px; background: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        Laporan Realisasi & Pertanggungjawaban
                    </span>
                    <h2 style="font-weight: 800; margin: 8px 0 2px 0; font-size: 18px; color: #000000;">LAPORAN ANGGARAN BULAN ${p.bulan_hijriah || ''}</h2>
                    <div style="font-size: 13px; color: #334155; font-weight: 500;">Instansi: <strong style="color: #000000;">${namaInstansi}</strong></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 12.5px; color: #334155; font-weight: 600;">No. Ref: <span style="color: #0d6efd; font-weight: 800;">#ANGG-${p.id.toString().padStart(4, '0')}</span></div>
                    <div style="font-size: 12px; color: #334155; margin-top: 3px;">Total: <strong>${(notas || []).length} Nota Pembelian</strong></div>
                </div>
            </div>
        </div>

        <!-- KPI Cards Summary -->
        <div style="display: flex; gap: 12px; margin-bottom: 20px; page-break-inside: avoid;">
            <div style="flex: 1; border: 1.5px solid #64748b; border-radius: 8px; padding: 10px 12px; background: #f8fafc; text-align: center; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Plafond Disetujui (ACC)</div>
                <div style="font-size: 16px; font-weight: 800; color: #000000; margin-top: 3px;">Rp ${formatRupiah(nominalACC)}</div>
            </div>
            <div style="flex: 1; border: 1.5px solid #3b82f6; border-radius: 8px; padding: 10px 12px; background: #eff6ff; text-align: center; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <div style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px;">Total Realisasi Belanja</div>
                <div style="font-size: 16px; font-weight: 800; color: #1d4ed8; margin-top: 3px;">Rp ${formatRupiah(totalBelanjaan)} <span style="font-size: 11.5px; font-weight: 600;">(${pct}%)</span></div>
            </div>
            <div style="flex: 1; border: 1.5px solid ${sisa < 0 ? '#ef4444' : '#22c55e'}; border-radius: 8px; padding: 10px 12px; background: ${sisa < 0 ? '#fef2f2' : '#f0fdf4'}; text-align: center; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <div style="font-size: 11px; font-weight: 700; color: ${sisa < 0 ? '#b91c1c' : '#15803d'}; text-transform: uppercase; letter-spacing: 0.5px;">${sisa < 0 ? 'Kekurangan (Minus)' : 'Sisa Saldo Kas'}</div>
                <div style="font-size: 16px; font-weight: 800; color: ${sisa < 0 ? '#b91c1c' : '#15803d'}; margin-top: 3px;">${sisa < 0 ? '-' : ''}Rp ${formatRupiah(Math.abs(sisa))}</div>
            </div>
        </div>

        <!-- Rincian Nota Tables -->
        ${allNotasHtml}

        <!-- Signatures (Sesuai Format Resmi) -->
        <div style="margin-top: 130px; display: flex; justify-content: space-between; font-family: 'Times New Roman', Times, serif; padding: 0 10px; page-break-inside: avoid;">
            <div style="width: 180px;">
                <div style="border-top: 1.5px solid #000; margin-bottom: 4px;"></div>
                <div style="font-weight: bold; font-size: 13px; text-align: left;">Koordinator Kamar</div>
            </div>
            <div style="width: 180px;">
                <div style="border-top: 1.5px solid #000; margin-bottom: 4px;"></div>
                <div style="font-weight: bold; font-size: 13px; text-align: left;">Staff ADM</div>
            </div>
        </div>
    </div>
    `;

    printHtml(html, p.instansi || p.pondok || null);
}

function printSemuaNota() {
    printLaporanAnggaran(currentPengajuan, loadedNotas);
}

// ==========================================
// FILTER STATUS & PENCARIAN REAL-TIME
// ==========================================
let currentFilterStatus = 'all';

function filterAnggaranStatus(status, btnEl) {
    currentFilterStatus = status;
    
    // Update active state on tabs
    document.querySelectorAll('.anggaran-nav-tabs .btn-filter').forEach(btn => btn.classList.remove('active'));
    if (btnEl && btnEl.classList.contains('btn-filter')) {
        btnEl.classList.add('active');
    } else {
        // If clicked from top stat card, match tab
        const matchedTab = document.querySelector(`.anggaran-nav-tabs .btn-filter[onclick*="'${status}'"]`);
        if (matchedTab) matchedTab.classList.add('active');
    }
    
    // Highlight top stat cards
    document.querySelectorAll('.anggaran-stat-card').forEach(c => c.classList.remove('active-filter'));
    if (btnEl && btnEl.classList.contains('anggaran-stat-card')) {
        btnEl.classList.add('active-filter');
    }

    applyAnggaranFilters();
}

function searchAnggaranCards(keyword) {
    applyAnggaranFilters();
}

function applyAnggaranFilters() {
    const keyword = (document.getElementById('searchAnggaranInput')?.value || '').trim().toLowerCase();
    const cards = document.querySelectorAll('.anggaran-item-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const cStatus = card.getAttribute('data-status') || '';
        const cSearch = card.getAttribute('data-search') || '';

        const matchStatus = (currentFilterStatus === 'all' || cStatus === currentFilterStatus);
        const matchSearch = (!keyword || cSearch.includes(keyword));

        if (matchStatus && matchSearch) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    let noDataEl = document.getElementById('noFilterDataNotice');
    if (!noDataEl) {
        noDataEl = document.createElement('div');
        noDataEl.id = 'noFilterDataNotice';
        noDataEl.className = 'col-12 text-center py-5 text-muted';
        noDataEl.innerHTML = '<i class="bi bi-search fs-1 d-block mb-3"></i><h5>Tidak ada data anggaran yang cocok</h5><p class="small">Coba ubah filter status atau kata kunci pencarian Anda.</p>';
        document.getElementById('anggaranCardContainer')?.appendChild(noDataEl);
    }
    noDataEl.style.display = (visibleCount === 0 && cards.length > 0) ? '' : 'none';
}

// ==========================================
// WHATSAPP REMINDER SYSTEM
// ==========================================
let activeWhatsAppPengajuan = null;

function openWhatsAppReminderModal(p) {
    activeWhatsAppPengajuan = p;
    const instansiNama = p.instansi || 'Pondok';
    const bulan = p.bulan_hijriah || '-';
    const status = (p.status || '').toUpperCase();
    const ajuanRp = 'Rp ' + formatRupiah(p.total_ajuan || 0);
    const accRp = 'Rp ' + formatRupiah(p.total_disetujui || 0);
    const terpakaiRp = 'Rp ' + formatRupiah(p.total_digunakan || 0);
    const sisaRp = 'Rp ' + formatRupiah(p.sisa_anggaran || 0);
    const hariCair = p.hari_sejak_cair !== undefined ? p.hari_sejak_cair : 0;
    const hariAjuan = p.hari_sejak_ajuan !== undefined ? p.hari_sejak_ajuan : 0;

    // Contact phone lookup
    let targetPhone = '';
    if (typeof instansiMap === 'object' && instansiMap[p.instansi]) {
        targetPhone = instansiMap[p.instansi].phone || instansiMap[p.instansi].no_hp || '';
    }
    document.getElementById('wa_nomor_tujuan').value = targetPhone;

    let draftMsg = `*PENGINGAT ANGGARAN OPERASIONAL*\n`;
    draftMsg += `------------------------------------\n`;
    draftMsg += `Assalamu'alaikum Wr. Wb.\n\n`;
    draftMsg += `Mengingatkan terkait Anggaran Operasional *${instansiNama}* untuk Bulan *${bulan}*:\n\n`;
    draftMsg += `• ID Anggaran: *#ANGG-${String(p.id).padStart(4, '0')}*\n`;
    draftMsg += `• Status: *${status}*\n`;

    if (p.status === 'disetujui') {
        draftMsg += `• Pagu Ditetapkan: *${accRp}*\n`;
        draftMsg += `• Realisasi Saat Ini: *${terpakaiRp}*\n`;
        draftMsg += `• Sisa Anggaran: *${sisaRp}*\n`;
        if (hariCair > 0) {
            draftMsg += `• Durasi Aktif: *${hariCair} hari*\n`;
        }
        draftMsg += `\n*PENTING:* Mohon untuk segera melengkapi dan mengunggah bukti nota pembelanjaan / kuitansi operasional melalui sistem.\n`;
    } else if (p.status === 'diajukan') {
        draftMsg += `• Total Rencana: *${ajuanRp}*\n`;
        draftMsg += `• Tanggal Pencatatan: *${p.tanggal_pengajuan || '-'}*\n`;
        draftMsg += `\n*INFORMASI:* Rencana anggaran telah dicatat dan menunggu penetapan pagu operasional instansi.\n`;
    } else if (p.status === 'dilaporkan') {
        draftMsg += `• Total Pagu: *${accRp}*\n`;
        draftMsg += `• Realisasi Belanja: *${terpakaiRp}*\n`;
        draftMsg += `\n*INFORMASI:* Nota telah dilaporkan dan siap untuk diselesaikan.\n`;
    } else {
        draftMsg += `• Total Anggaran: *${accRp}*\n`;
    }

    draftMsg += `\nSilakan cek detail di: http://sipln/webapp/public/index.php/anggaran\n\n`;
    draftMsg += `Terima kasih.\n_Wassalamu'alaikum Wr. Wb._\n*Pengurus / Bendahara ${instansiNama}*`;

    document.getElementById('wa_pesan_text').value = draftMsg;

    new bootstrap.Modal(document.getElementById('modalWhatsAppReminder')).show();
}

function sendWhatsAppReminderDirect() {
    let rawPhone = document.getElementById('wa_nomor_tujuan')?.value.trim() || '';
    let msg = document.getElementById('wa_pesan_text')?.value || '';

    // Format phone
    let cleanPhone = rawPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '62' + cleanPhone.slice(1);
    }

    let waUrl = cleanPhone 
        ? `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(msg)}`
        : `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;

    window.open(waUrl, '_blank');
}

function copyWhatsAppReminderText() {
    const textEl = document.getElementById('wa_pesan_text');
    if (!textEl) return;
    textEl.select();
    navigator.clipboard.writeText(textEl.value).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Teks Disalin!',
            text: 'Draf pesan pengingat berhasil disalin ke clipboard.',
            timer: 1500,
            showConfirmButton: false
        });
    }).catch(() => {
        document.execCommand('copy');
        Swal.fire({
            icon: 'success',
            title: 'Teks Disalin!',
            text: 'Draf pesan pengingat berhasil disalin.',
            timer: 1500,
            showConfirmButton: false
        });
    });
}
</script>
