<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var array $santris
 * @var array $pondokList
 * @var array $kelasList
 * @var array $negaraList
 * @var array $kepengurusanList
 * @var array $expItasList
 * @var array $expPasporList
 * @var array $params
 */

$this->setTitle('Menu Print Data Santri Luar Negeri');

// Hitung statistik ringkasan dokumen
$totalData = count($santris);
$totalExpired = 0;
$totalUrgent = 0;
$totalValid = 0;
$myKep = $_SESSION['def_kepengurusan'] ?? '';

$today = new DateTime();
$today->setTime(0, 0, 0);

foreach ($santris as $s) {
    $expPStr = $s['exp_paspor'] ?? '';
    $expIStr = $s['exp_itas'] ?? '';
    
    $expP = (!empty($expPStr) && $expPStr !== '0000-00-00') ? new DateTime($expPStr) : null;
    if ($expP) $expP->setTime(0, 0, 0);
    $expI = (!empty($expIStr) && $expIStr !== '0000-00-00') ? new DateTime($expIStr) : null;
    if ($expI) $expI->setTime(0, 0, 0);
    
    $diffDaysP = $expP ? (int) $today->diff($expP)->format('%r%a') : null;
    $diffDaysI = $expI ? (int) $today->diff($expI)->format('%r%a') : null;
    
    // Status Expired: Paspor / ITAS sudah melewati batas masa berlaku
    $isExp = ($expP && $diffDaysP <= 0) || ($expI && $diffDaysI <= 0);
    // Status Mendekati Expired: Paspor <= 540 hari (18 bulan) atau ITAS <= 90 hari (3 bulan)
    $isUrg = (!$isExp) && (($expP && $diffDaysP <= 540) || ($expI && $diffDaysI <= 90));
    
    if ($isExp) {
        $totalExpired++;
    } elseif ($isUrg) {
        $totalUrgent++;
    } else {
        // Status Dokumen Aman/Valid: Masa berlaku aktif di atas ambang peringatan
        $totalValid++;
    }
}

// Hitung jumlah filter aktif
$activeFilterCount = 0;
foreach (['pondok', 'negara', 'kepengurusan', 'kelas', 'itas', 'paspor'] as $key) {
    if (!empty($params[$key])) {
        $activeFilterCount++;
    }
}
?>

<style>
/* Modern Responsive Styling for Master Print */
:root {
    --print-primary: #6f42c1;
    --print-primary-dark: #59359a;
}

.print-stat-card {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.85);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    background: #ffffff;
}
.print-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}
.print-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

/* Collapsible Filter Header */
.filter-toggle-header {
    cursor: pointer;
    transition: background-color 0.2s ease;
    user-select: none;
}
.filter-toggle-header:hover {
    background-color: #f8fafc !important;
}
#filterChevronIcon {
    transition: transform 0.3s ease;
}
.filter-toggle-header[aria-expanded="true"] #filterChevronIcon {
    transform: rotate(180deg);
}

/* Table & Full Scrolling */
.print-table-wrapper {
    max-height: calc(100vh - 300px);
    min-height: 280px;
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    position: relative;
    border-radius: 12px;
}
.print-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.print-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.print-table-wrapper table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 820px; /* Jaminan ruang scroll horizontal leluasa di layar HP */
    width: 100%;
    margin-bottom: 0;
}
.print-table-wrapper thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 11px 12px;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* ONLY Checkbox column is sticky on the right */
.print-sticky-action {
    position: sticky;
    right: 0;
    z-index: 8;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.print-table-wrapper thead th.print-sticky-action {
    position: sticky;
    top: 0;
    right: 0;
    z-index: 12;
    background-color: #f8fafc !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.print-table-wrapper tbody td {
    padding: 10px 12px;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.print-table-wrapper tbody tr:hover td {
    background-color: #f8fafc;
}
.print-table-wrapper tbody tr:hover td.print-sticky-action {
    background-color: #f8fafc !important;
}

/* Template Button */
.template-btn {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid #e2e8f0;
}
.template-btn:hover {
    background: #fdfcff !important;
    border-color: #6f42c1 !important;
    color: #6f42c1 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px -2px rgba(111, 66, 193, 0.12) !important;
}

#printLayoutWrapper { transition: all 0.3s ease-in-out; }
#mainCol { transition: width 0.3s ease-in-out; }
#rightCol { transition: width 0.3s ease-in-out, opacity 0.3s ease-in-out, padding 0.3s ease-in-out; }

#printLayoutWrapper.panel-collapsed #mainCol { width: 100%; }
#printLayoutWrapper.panel-collapsed #rightCol {
    display: none;
}

/* Responsive Overrides */
@media (max-width: 991.98px) {
    .page-header-responsive {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px;
    }
    .page-header-controls {
        width: 100%;
    }
    .page-header-controls .btn {
        width: 100% !important;
    }
    .print-stat-card .card-body {
        padding: 0.85rem !important;
    }
    .print-stat-icon {
        width: 38px;
        height: 38px;
        font-size: 1.15rem;
    }
    .print-stat-number {
        font-size: 1.3rem !important;
    }
    .print-table-wrapper {
        max-height: 52vh;
    }
    .print-action-bar {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
    }
    .print-action-bar .d-flex {
        width: 100%;
    }
    .print-action-bar .btn {
        flex: 1 1 auto;
    }
    #rightCol {
        margin-top: 16px;
    }
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: rgba(111, 66, 193, 0.12);">
            <i class="bi bi-printer-fill fs-4" style="color: #6f42c1;"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Menu Print Data Santri Luar Negeri</h4>
            <div class="text-muted small fw-medium mt-1">Cetak & Export data santri dengan berbagai format laporan resmi</div>
        </div>
    </div>
    <div class="page-header-controls">
        <button id="toggleRightSidebarBtn" class="btn btn-outline-primary rounded-pill px-4 fw-semibold shadow-sm bg-white" type="button" style="transition: all 0.3s ease;">
            <i class="bi bi-layout-sidebar-reverse me-2"></i> <span class="fw-semibold">Panel Print & Template</span>
        </button>
    </div>
</div>

<!-- Stat Metric Cards (4-Tier Health Breakdown) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card print-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="print-stat-icon" style="background: rgba(111, 66, 193, 0.12); color: #6f42c1;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Total Santri</div>
                    <div class="h4 mb-0 fw-bold print-stat-number" style="color: #6f42c1;"><?= $totalData ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card print-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="print-stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Dokumen Aman / Valid</div>
                    <div class="h4 mb-0 fw-bold text-success print-stat-number"><?= $totalValid ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card print-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="print-stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Mendekati Expired</div>
                    <div class="h4 mb-0 fw-bold text-warning print-stat-number"><?= $totalUrgent ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card print-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="print-stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Dokumen Expired</div>
                    <div class="h4 mb-0 fw-bold text-danger print-stat-number"><?= $totalExpired ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="" id="filterForm">
    <div class="row gx-4" id="printLayoutWrapper">
        <script>if(localStorage.getItem('rightSidebarState') === 'collapsed') document.getElementById('printLayoutWrapper').classList.add('panel-collapsed');</script>
        
        <!-- Main Content (Table & Collapsible Filter) -->
        <div class="col-lg-9" id="mainCol">
            <!-- Collapsible Filter Section (Default: Collapsed / Ditutup) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white p-3 p-md-4 d-flex justify-content-between align-items-center filter-toggle-header" data-bs-toggle="collapse" data-bs-target="#filterCollapseSection" aria-expanded="false" aria-controls="filterCollapseSection">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-funnel-fill"></i>
                        </div>
                        <div>
                            <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Filter Kriteria Data</span>
                            <?php if ($activeFilterCount > 0): ?>
                                <span class="badge bg-primary rounded-pill ms-2"><?= $activeFilterCount ?> Filter Aktif</span>
                            <?php else: ?>
                                <span class="text-muted small ms-2 d-none d-sm-inline">(Klik untuk buka / tutup)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($activeFilterCount > 0): ?>
                            <a href="<?= API_URL ?>/master-print/menu-print" class="btn btn-sm btn-light border rounded-pill px-3 text-danger fw-semibold" onclick="event.stopPropagation();">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>
                        <?php endif; ?>
                        <div class="btn btn-sm btn-light rounded-circle shadow-sm border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;">
                            <i class="bi bi-chevron-down" id="filterChevronIcon"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Collapse Container (Default: Collapsed / Ditutup) -->
                <div class="collapse" id="filterCollapseSection">
                    <div class="card-body p-3 p-md-4 bg-light bg-opacity-50 border-top">
                        <!-- Filters Grid Responsive -->
                        <div class="row g-2 g-md-3">
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .72rem;">PONDOK</label>
                                <select name="pondok" class="form-select form-select-sm border shadow-sm rounded-3 bg-white py-2" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Pondok</option>
                                    <?php foreach ($pondokList as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['pondok'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .72rem;">NEGARA</label>
                                <select name="negara" class="form-select form-select-sm border shadow-sm rounded-3 bg-white py-2" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Negara</option>
                                    <?php foreach ($negaraList as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['negara'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .72rem;">KEPENGURUSAN</label>
                                <select name="kepengurusan" class="form-select form-select-sm border shadow-sm rounded-3 bg-white py-2" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Kepengurusan</option>
                                    <?php foreach ($kepengurusanList as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['kepengurusan'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .72rem;">KELAS</label>
                                <select name="kelas" class="form-select form-select-sm border shadow-sm rounded-3 bg-white py-2" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach ($kelasList as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['kelas'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .72rem;">EXP ITAS</label>
                                <select name="itas" class="form-select form-select-sm border shadow-sm rounded-3 bg-white py-2" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua ITAS</option>
                                    <?php foreach ($expItasList as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['itas'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .72rem;">EXP PASPOR</label>
                                <select name="paspor" class="form-select form-select-sm border shadow-sm rounded-3 bg-white py-2" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Paspor</option>
                                    <?php foreach ($expPasporList as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['paspor'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</form>

            <!-- Table Card & Actions -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-md-4">
                    <!-- Actions & Instant Search Toolbar Responsive -->
                    <div class="d-flex justify-content-between align-items-center mb-3 print-action-bar">
                        <!-- Instant Search Bar -->
                        <div style="min-width: 240px; max-width: 320px;" class="w-100">
                            <div class="input-group input-group-sm border shadow-sm rounded-pill overflow-hidden bg-white">
                                <span class="input-group-text bg-white border-0 text-secondary ps-3 pe-1"><i class="bi bi-search"></i></span>
                                <input type="text" id="instantSearch" class="form-control border-0 shadow-none py-2" placeholder="Cari santri di tabel..." onkeyup="instantSearchTable(this.value)">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-light text-success border rounded-pill px-3 shadow-sm fw-semibold" onclick="checkAll(true)">
                                <i class="bi bi-check-all me-1"></i> Cek Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-light text-danger border rounded-pill px-3 shadow-sm fw-semibold" onclick="checkAll(false)">
                                <i class="bi bi-square me-1"></i> Batal Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-semibold" onclick="submitExport('umum', 'excel')">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold" onclick="submitExport('umum', 'pdf')">
                                <i class="bi bi-printer me-1"></i> PDF
                            </button>
                        </div>
                    </div>

                    <!-- Export Form & Smooth Scrollable Table -->
                    <form id="exportForm" method="POST" action="<?= API_URL ?>/master-print/export">
                        <input type="hidden" name="_csrf" value="<?= $csrf ?? '' ?>">
                        <input type="hidden" name="reportType" id="reportTypeInput" value="umum">
                        <input type="hidden" name="outputType" id="outputTypeInput" value="excel">
                        <input type="hidden" name="export_columns" id="exportColumnsInput">
                        
                        <!-- Hidden inputs for Absen Anggota -->
                        <input type="hidden" name="absen_judul" id="absenJudulInput">
                        <input type="hidden" name="absen_frekuensi" id="absenFrekuensiInput">
                        <input type="hidden" name="absen_kolom" id="absenKolomInput">
                        <input type="hidden" name="absen_instansi" id="absenInstansiInput">
                        <input type="hidden" name="absen_orientasi" id="absenOrientasiInput">
                        <input type="hidden" name="absen_tampil_kop" id="absenTampilKopInput">
                        <input type="hidden" name="absen_sort_keys" id="absenSortKeysInput">
                        
                        <div class="print-table-wrapper border rounded-4">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="min-width: 120px;">No Paspor</th>
                                        <th style="min-width: 170px;">Nama Santri</th>
                                        <th style="min-width: 80px;">Kelas</th>
                                        <th style="min-width: 110px;">Negara</th>
                                        <th style="min-width: 90px;">Rayon</th>
                                        <th style="min-width: 130px;">Exp Paspor</th>
                                        <th style="min-width: 130px;">Exp ITAS</th>
                                        <th style="min-width: 60px;">Lvl</th>
                                        <th class="text-center print-sticky-action" style="width: 60px;">Pilih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($santris)): ?>
                                        <tr><td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                            Tidak ada data santri ditemukan sesuai filter.
                                        </td></tr>
                                    <?php else: ?>
                                        <?php foreach ($santris as $s): 
                                            $expPStr = $s['exp_paspor'] ?? '';
                                            $expIStr = $s['exp_itas'] ?? '';
                                            
                                            $expP  = (!empty($expPStr) && $expPStr !== '0000-00-00') ? new DateTime($expPStr) : null;
                                            if ($expP) $expP->setTime(0, 0, 0);
                                            $expI  = (!empty($expIStr) && $expIStr !== '0000-00-00') ? new DateTime($expIStr) : null;
                                            if ($expI) $expI->setTime(0, 0, 0);
                                            
                                            $diffDaysP = $expP ? (int) $today->diff($expP)->format('%r%a') : null;
                                            $diffDaysI = $expI ? (int) $today->diff($expI)->format('%r%a') : null;
                                            
                                            $isExpiredP = $expP && $diffDaysP <= 0;
                                            $isUrgentP = $isExpiredP || ($diffDaysP !== null && $diffDaysP <= 540);
                                            
                                            $isExpiredI = $expI && $diffDaysI <= 0;
                                            $isUrgentI = $isExpiredI || ($diffDaysI !== null && $diffDaysI <= 90);
                                            
                                            $fmtP = $expP ? $expP->format('d-M-Y') : '-';
                                            $fmtI = $expI ? $expI->format('d-M-Y') : '-';
                                            
                                            $rowClass = ($isExpiredP || $isExpiredI) ? 'table-danger' : (($isUrgentP || $isUrgentI) ? 'table-warning' : '');
                                        ?>
                                            <tr class="<?= $rowClass ?>">
                                                <td class="ps-3"><code><?= htmlspecialchars((string)($s['no_paspor'] ?? '-')) ?></code></td>
                                                <td class="fw-semibold text-dark">
                                                    <?= htmlspecialchars((string)$s['nama']) ?>
                                                    <?php 
                                                        $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                                                        if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0): 
                                                    ?>
                                                        <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem; padding: 2px 4px; border-radius: 4px;">Pindahan</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-secondary rounded-pill"><?= htmlspecialchars((string)$s['kelas']) ?></span></td>
                                                <td><i class="bi bi-globe me-1 text-muted"></i><?= htmlspecialchars((string)$s['negara']) ?></td>
                                                <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars((string)$s['rayon']) ?></span></td>
                                                <td class="<?= $isExpiredP ? 'text-danger fw-bold' : ($isUrgentP ? 'text-warning fw-bold' : '') ?>">
                                                    <?= $fmtP ?>
                                                    <?php if ($isExpiredP): ?>
                                                        <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>EXPIRED</span>
                                                    <?php elseif ($isUrgentP): ?>
                                                        <?php $textP = $diffDaysP > 90 ? floor($diffDaysP / 30) . ' bln lagi' : $diffDaysP . ' hari lagi'; ?>
                                                        <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textP ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="<?= $isExpiredI ? 'text-danger fw-bold' : ($isUrgentI ? 'text-warning fw-bold' : '') ?>">
                                                    <?= $fmtI ?>
                                                    <?php if ($isExpiredI): ?>
                                                        <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>EXPIRED</span>
                                                    <?php elseif ($isUrgentI): ?>
                                                        <?php $textI = $diffDaysI > 90 ? floor($diffDaysI / 30) . ' bln lagi' : $diffDaysI . ' hari lagi'; ?>
                                                        <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars((string)$s['lvl']) ?></span></td>
                                                <td class="text-center print-sticky-action">
                                                    <input class="form-check-input kds-checkbox border-secondary" type="checkbox" name="kds[]" value="<?= htmlspecialchars((string)$s['kds']) ?>" style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-secondary small">Total Data Sesuai Filter:</span>
                                <span class="badge bg-dark rounded-pill"><?= count($santris) ?></span>
                            </div>
                            <span class="text-muted small">Ceklis data santri di atas untuk diproses pada Template Laporan</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar (Report Templates) -->
        <div class="col-lg-3" id="rightCol">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i> Template Laporan
                    </h6>
                    <div class="text-muted small mt-1">Pilih format dokumen yang akan dicetak</div>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3 pt-2">
                    <button type="button" class="btn btn-light border w-100 text-start py-3 px-3 fw-medium shadow-sm rounded-4 text-secondary template-btn" onclick="submitExport('formulir_pendataan')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-file-earmark-person fs-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">Formulir Pendataan</div>
                                <div class="text-muted small text-truncate">Format biodata per santri</div>
                            </div>
                        </div>
                    </button>
                    
                    <button type="button" class="btn btn-light border w-100 text-start py-3 px-3 fw-medium shadow-sm rounded-4 text-secondary template-btn" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-card-checklist fs-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">Absen Anggota</div>
                                <div class="text-muted small text-truncate">Daftar hadir & kolom ceklis</div>
                            </div>
                        </div>
                    </button>
                    
                    <button type="button" class="btn btn-light border w-100 text-start py-3 px-3 fw-medium shadow-sm rounded-4 text-secondary template-btn" data-bs-toggle="modal" data-bs-target="#modalUkuranBaju">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-person-lines-fill fs-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">Ukuran Baju</div>
                                <div class="text-muted small text-truncate">Rekap ukuran seragam santri</div>
                            </div>
                        </div>
                    </button>
                    
                    <button type="button" class="btn btn-light border w-100 text-start py-3 px-3 fw-medium shadow-sm rounded-4 text-secondary template-btn" data-bs-toggle="modal" data-bs-target="#modalFotoPersonal">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-person-bounding-box fs-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">Foto Personal 3x4</div>
                                <div class="text-muted small text-truncate">Cetak lembar pasfoto 3x4</div>
                            </div>
                        </div>
                    </button>
                    
                    <button type="button" class="btn btn-light border w-100 text-start py-3 px-3 fw-medium shadow-sm rounded-4 text-secondary template-btn" onclick="submitExport('laporan_vertikal')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-layout-text-sidebar-reverse fs-5"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">Laporan Vertikal</div>
                                <div class="text-muted small text-truncate">Rekapitulasi kolom potret</div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- Modal Absen Anggota -->
<div class="modal fade" id="modalAbsen" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-card-checklist text-success me-2"></i> Konfigurasi Absensi Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                <?php if ($isSuperAdmin ?? false): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">PILIH INSTANSI (UNTUK KOP SURAT)</label>
                    <select id="modalAbsenInstansi" class="form-select rounded-3">
                        <option value="">-- Gunakan Default / Pusat --</option>
                        <?php foreach ($instansiList ?? [] as $inst): ?>
                            <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">JUDUL LAPORAN</label>
                    <input type="text" id="modalAbsenJudul" class="form-control rounded-3" value="Daftar Hadir Anggota" placeholder="Contoh: Daftar Hadir Kelas 5">
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">PILIH KOLOM INFORMASI (NO DAN NAMA AKAN SELALU DITAMPILKAN)</label>
                    <div class="row g-2 g-md-3" id="checkbox-columns-container">
                        <?php 
                        $kategoriKolom = [
                            'Akademik & Penempatan' => [
                                'Stambuk' => 'stambuk',
                                'Kelas' => 'kelas',
                                'Rayon' => 'rayon',
                                'Pondok/Asrama' => 'pondok',
                                'Status Santri' => 'status_santri',
                            ],
                            'Biodata & Keluarga' => [
                                'Tempat Lahir' => 'tempat_lahir',
                                'Tanggal Lahir' => 'tanggal_lahir',
                                'Jenis Kelamin' => 'jenis_kelamin',
                                'Ukuran Baju' => 'ukuran_baju',
                                'Nama Ayah' => 'nama_ayah',
                                'Nama Ibu' => 'nama_ibu',
                                'No HP (Ortu/Alt)' => 'no_hp',
                                'Alamat' => 'alamat',
                            ],
                            'Identitas & Kenegaraan' => [
                                'Asal Negara' => 'negara',
                                'Kewarganegaraan' => 'kewarganegaraan',
                                'No IC' => 'no_ic',
                                'No SKTT' => 'no_sktt',
                            ],
                            'Dokumen Paspor' => [
                                'Kondisi Paspor' => 'keberadaan_paspor',
                                'No Paspor' => 'no_paspor',
                                'Exp Paspor' => 'exp_paspor',
                            ],
                            'Dokumen ITAS' => [
                                'No ITAS' => 'no_itas',
                                'Level ITAS' => 'level_itas',
                                'Exp ITAS' => 'exp_itas',
                            ],
                        ];
                        foreach($kategoriKolom as $kategori => $koloms): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-none border rounded-3 bg-white">
                                <div class="card-header py-2 bg-light border-bottom fw-bold text-secondary" style="font-size:0.8rem;">
                                    <?= htmlspecialchars($kategori) ?>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex flex-column gap-1">
                                        <?php foreach($koloms as $label => $val): ?>
                                        <div class="form-check m-0">
                                            <input class="form-check-input absen-col-cb" type="checkbox" value="<?= $val ?>" id="cb_<?= $val ?>" <?= in_array($val, ['kelas', 'rayon']) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-truncate w-100" style="font-size:0.82rem; cursor: pointer;" for="cb_<?= $val ?>" title="<?= htmlspecialchars($label) ?>">
                                                <?= htmlspecialchars($label) ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">URUTAN KOLOM & SORTING DATA</label>
                    <div class="text-muted small mb-2" style="font-size: 0.78rem;">
                        <i class="bi bi-info-circle text-primary"></i> <b>Geser (Drag & Drop)</b> chip kolom untuk memindahkan posisinya.<br>
                        <i class="bi bi-info-circle text-primary"></i> <b>Klik Ganda (Double Click / Tap Ganda)</b> pada chip untuk urutan sorting prioritas 1, 2, 3.
                    </div>
                    <div id="sortable-columns-container" class="d-flex flex-wrap gap-2 p-3 border rounded-3 bg-light" style="min-height: 60px;">
                        <!-- Chips will be appended here by JS -->
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small text-muted">ORIENTASI KERTAS</label>
                        <select id="modalAbsenOrientasi" class="form-select rounded-3">
                            <option value="landscape">Lanskap (Mendatar)</option>
                            <option value="portrait">Potret (Tegak)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small text-muted">JUMLAH KOLOM KOSONG (CEKLIS / ABSEN)</label>
                        <input type="number" id="modalAbsenFrekuensi" class="form-control rounded-3" value="14" min="1" max="50">
                    </div>
                </div>

                <div class="mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalAbsenTampilKop" checked>
                        <label class="form-check-label fw-bold" for="modalAbsenTampilKop">Tampilkan Kop Surat Resmi</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" onclick="generateAbsen('excel')"><i class="bi bi-file-excel me-1"></i> Export Excel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" onclick="generateAbsen('html')"><i class="bi bi-printer me-1"></i> Print Absen</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ukuran Baju -->
<div class="modal fade" id="modalUkuranBaju" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-person-lines-fill text-info me-2"></i> Pengaturan Rekap Ukuran Baju</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-white p-3 p-md-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">JUDUL LAPORAN</label>
                    <input type="text" id="modalBajuJudul" class="form-control rounded-3" value="Laporan Rekapitulasi Ukuran Baju Santri">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">ORIENTASI KERTAS</label>
                    <select id="modalBajuOrientasi" class="form-select rounded-3">
                        <option value="portrait">Potret (Tegak)</option>
                        <option value="landscape">Lanskap (Mendatar)</option>
                    </select>
                </div>
                <div class="mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalBajuTampilKop" checked>
                        <label class="form-check-label fw-bold" for="modalBajuTampilKop">Tampilkan Kop Surat Resmi</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" onclick="generateBaju('excel')"><i class="bi bi-file-excel me-1"></i> Export Excel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" onclick="generateBaju('html')"><i class="bi bi-printer me-1"></i> Print Preview</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto Personal -->
<div class="modal fade" id="modalFotoPersonal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-person-bounding-box text-warning me-2"></i> Pengaturan Cetak Pasfoto 3x4</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-white p-3 p-md-4">
                <div class="alert alert-info py-2 rounded-3" style="font-size: 0.85rem;">
                    <i class="bi bi-info-circle me-1"></i> Ukuran cetak baku foto adalah <strong>3x4 cm</strong> pada lembar kertas A4.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">JUMLAH FOTO PER BARIS</label>
                    <div class="input-group">
                        <input type="number" id="modalFotoCols" class="form-control rounded-start-3" value="5" min="1" max="6">
                        <span class="input-group-text rounded-end-3">Foto / Baris</span>
                    </div>
                    <div class="form-text mt-2 text-muted" style="font-size: 0.78rem;">
                        <strong>Rekomendasi:</strong> Standar kertas A4 Potret adalah <strong>5 foto per baris</strong> agar pas dengan margin potong tepi.
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalFotoNama" checked>
                        <label class="form-check-label fw-bold" for="modalFotoNama">Tampilkan Nama & Stambuk di Bawah Foto</label>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalFotoGaris" checked>
                        <label class="form-check-label fw-bold" for="modalFotoGaris">Tampilkan Garis Potong (Cut Line)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" onclick="generateFoto('zip')"><i class="bi bi-file-zip me-1"></i> Unduh ZIP</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" onclick="generateFoto('html')"><i class="bi bi-printer me-1"></i> Print Preview</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Export Excel -->
<div class="modal fade" id="modalExportExcel" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i> Pilih Kolom Export Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-3 p-md-4" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <span class="text-muted small">Tentukan kolom yang akan disertakan dalam lembar Excel:</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="document.querySelectorAll('.excel-col-cb').forEach(c => c.checked=true)"><i class="bi bi-check-all"></i> Pilih Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="document.querySelectorAll('.excel-col-cb').forEach(c => c.checked=false)"><i class="bi bi-square"></i> Kosongkan</button>
                    </div>
                </div>
                <div class="row g-2">
                    <?php 
                    $excelCols = [
                        'stambuk' => 'Stambuk / Regno', 'nama' => 'Nama Santri', 'kelas' => 'Kelas', 
                        'status_santri' => 'Status Santri', 'rayon' => 'Rayon', 'negara' => 'Daerah / Negara',
                        'kewarganegaraan' => 'Kewarganegaraan', 'jenis_kelamin' => 'Jenis Kelamin',
                        'tempat_lahir' => 'Tempat Lahir', 'tanggal_lahir' => 'Tanggal Lahir', 'pondok' => 'Pondok',
                        'kepengurusan' => 'Kepengurusan', 'nama_ayah' => 'Nama Ayah', 'nama_ibu' => 'Nama Ibu',
                        'no_ayah' => 'No Telepon Ayah', 'no_ibu' => 'No Telepon Ibu', 'no_hp_alternatif' => 'No Alternatif / Wali',
                        'alamat' => 'Alamat Lengkap', 'no_ic' => 'No IC Santri', 'no_sktt' => 'No SKTT',
                        'ukuran_baju' => 'Ukuran Baju', 'keberadaan_paspor' => 'Keberadaan Paspor',
                        'paspor_baru_no' => 'Paspor Baru (No)', 'paspor_baru_exp' => 'Paspor Baru (Exp)',
                        'paspor_lama_no' => 'Paspor Lama (No)', 'itas_no' => 'ITAS (No)', 
                        'itas_level' => 'ITAS (Level)', 'itas_exp' => 'ITAS (Exp)',
                        'barang_atm' => 'ATM / Bank', 'barang_hp' => 'Handphone', 'barang_lain' => 'Barang Terlarang Lainnya'
                    ];
                    foreach ($excelCols as $key => $label): 
                    ?>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="form-check bg-white border rounded-3 p-2 shadow-sm mb-1 d-flex align-items-center">
                            <input class="form-check-input excel-col-cb ms-1 me-2" type="checkbox" value="<?= $key ?>" id="col_<?= $key ?>" checked>
                            <label class="form-check-label flex-grow-1 text-truncate" for="col_<?= $key ?>" style="font-size:0.82rem; cursor:pointer;" title="<?= $label ?>">
                                <?= $label ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" onclick="processExcelExport()"><i class="bi bi-file-excel me-1"></i> Proses Export Excel</button>
            </div>
        </div>
    </div>
</div>

<script>
function instantSearchTable(query) {
    const filter = (query || '').toUpperCase().trim();
    const table = document.querySelector('.print-table-wrapper table');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const trs = tbody.getElementsByTagName("tr");
    
    for (let i = 0; i < trs.length; i++) {
        const tds = trs[i].getElementsByTagName("td");
        if (tds.length <= 1) continue;
        
        if (!filter) {
            trs[i].style.display = "";
            continue;
        }
        
        let match = false;
        for (let j = 0; j < tds.length - 1; j++) {
            const txtValue = tds[j].textContent || tds[j].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        trs[i].style.display = match ? "" : "none";
    }
}

function generateFoto(outType = 'html') {
    const checked = document.querySelectorAll('input[name="kds[]"]:checked').length;
    if (checked === 0) {
        Swal.fire({icon: 'warning', title: 'Perhatian', text: 'Silakan pilih minimal satu data santri di tabel.', confirmButtonColor: '#0d6efd'});
        return;
    }
    
    const cols = document.getElementById('modalFotoCols').value;
    document.getElementById('absenFrekuensiInput').value = cols;
    
    const showNama = document.getElementById('modalFotoNama').checked ? '1' : '0';
    const showGaris = document.getElementById('modalFotoGaris').checked ? '1' : '0';
    document.getElementById('absenKolomInput').value = showNama + ',' + showGaris;

    const modalEl = document.getElementById('modalFotoPersonal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    
    submitExport('foto_personal', outType);
}

function generateBaju(outType = 'html') {
    const checked = document.querySelectorAll('input[name="kds[]"]:checked').length;
    if (checked === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan pilih minimal satu data santri di tabel sebelum membuat laporan.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    const judulEl = document.getElementById('modalBajuJudul');
    if (judulEl) document.getElementById('absenJudulInput').value = judulEl.value;
    
    const orientasiEl = document.getElementById('modalBajuOrientasi');
    if (orientasiEl) document.getElementById('absenOrientasiInput').value = orientasiEl.value;
    
    const tampilKopEl = document.getElementById('modalBajuTampilKop');
    if (tampilKopEl) document.getElementById('absenTampilKopInput').value = tampilKopEl.checked ? '1' : '0';

    const modalEl = document.getElementById('modalUkuranBaju');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    
    submitExport('ukuran_baju', outType);
}

function generateAbsen(outType = 'html') {
    const checked = document.querySelectorAll('input[name="kds[]"]:checked').length;
    if (checked === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan pilih minimal satu data santri di tabel sebelum membuat absensi.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    const judulEl = document.getElementById('modalAbsenJudul');
    if (judulEl) document.getElementById('absenJudulInput').value = judulEl.value;
    
    const chips = Array.from(document.getElementById('sortable-columns-container').children);
    const cols = chips.map(chip => chip.dataset.val);
    document.getElementById('absenKolomInput').value = cols.join(',');
    
    const frekEl = document.getElementById('modalAbsenFrekuensi');
    if(frekEl) document.getElementById('absenFrekuensiInput').value = frekEl.value;
    
    const instansiSelect = document.getElementById('modalAbsenInstansi');
    if (instansiSelect) document.getElementById('absenInstansiInput').value = instansiSelect.value;
    
    const orientasiEl = document.getElementById('modalAbsenOrientasi');
    if (orientasiEl) document.getElementById('absenOrientasiInput').value = orientasiEl.value;
    
    const tampilKopEl = document.getElementById('modalAbsenTampilKop');
    if (tampilKopEl) document.getElementById('absenTampilKopInput').value = tampilKopEl.checked ? '1' : '0';

    document.getElementById('absenSortKeysInput').value = window.absenSortKeys ? window.absenSortKeys.join(',') : '';

    const modalEl = document.getElementById('modalAbsen');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    
    submitExport('absen_anggota', outType);
}

// Drag and drop column ordering logic
document.addEventListener('DOMContentLoaded', function() {
    const cbContainer = document.getElementById('checkbox-columns-container');
    const sortContainer = document.getElementById('sortable-columns-container');
    if (!cbContainer || !sortContainer) return;
    
    const checkboxes = cbContainer.querySelectorAll('.absen-col-cb');
    window.absenSortKeys = [];
    
    function renderSortBadges() {
        const chips = Array.from(sortContainer.children);
        chips.forEach(chip => {
            const val = chip.dataset.val;
            const idx = window.absenSortKeys.indexOf(val);
            
            const existingBadge = chip.querySelector('.sort-badge');
            if (existingBadge) existingBadge.remove();
            
            if (idx > -1) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-warning text-dark ms-2 sort-badge';
                badge.style.fontSize = '0.7rem';
                badge.innerText = 'Sort ' + (idx + 1);
                chip.appendChild(badge);
                chip.classList.replace('bg-primary', 'bg-success');
            } else {
                chip.classList.replace('bg-success', 'bg-primary');
            }
        });
    }
    
    function updateSortableChips() {
        const existingChips = Array.from(sortContainer.children).map(c => c.dataset.val);
        
        checkboxes.forEach(cb => {
            const val = cb.value;
            const label = cb.nextElementSibling.innerText.trim();
            const exists = existingChips.includes(val);
            
            if (cb.checked && !exists) {
                const chip = document.createElement('div');
                chip.className = 'badge bg-primary fs-6 py-2 px-3 fw-normal cursor-move user-select-none shadow-sm rounded-pill';
                chip.style.cursor = 'move';
                chip.draggable = true;
                chip.dataset.val = val;
                chip.innerHTML = '<i class="bi bi-grip-vertical me-1 opacity-75"></i><span>' + label + '</span>';
                
                chip.addEventListener('dragstart', handleDragStart);
                chip.addEventListener('dragover', handleDragOver);
                chip.addEventListener('drop', handleDrop);
                chip.addEventListener('dragend', handleDragEnd);
                
                chip.addEventListener('dblclick', function() {
                    const v = this.dataset.val;
                    const idx = window.absenSortKeys.indexOf(v);
                    if (idx > -1) {
                        window.absenSortKeys.splice(idx, 1);
                    } else {
                        if (window.absenSortKeys.length >= 3) {
                            Swal.fire('Info', 'Maksimal 3 urutan sorting yang diperbolehkan.', 'info');
                            return;
                        }
                        window.absenSortKeys.push(v);
                    }
                    renderSortBadges();
                });
                
                sortContainer.appendChild(chip);
            } else if (!cb.checked && exists) {
                const toRemove = sortContainer.querySelector(`[data-val="${val}"]`);
                if (toRemove) toRemove.remove();
                
                const sIdx = window.absenSortKeys.indexOf(val);
                if (sIdx > -1) window.absenSortKeys.splice(sIdx, 1);
            }
        });
        renderSortBadges();
    }
    
    let draggedItem = null;
    function handleDragStart(e) {
        draggedItem = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        this.classList.add('opacity-50');
    }
    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }
    function handleDrop(e) {
        e.stopPropagation();
        if (draggedItem !== this) {
            const children = Array.from(sortContainer.children);
            const draggedIdx = children.indexOf(draggedItem);
            const targetIdx = children.indexOf(this);
            if (draggedIdx < targetIdx) {
                this.parentNode.insertBefore(draggedItem, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedItem, this);
            }
        }
        return false;
    }
    function handleDragEnd(e) {
        this.classList.remove('opacity-50');
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSortableChips);
    });
    
    updateSortableChips();
});

function checkAll(check) {
    const checkboxes = document.querySelectorAll('.kds-checkbox');
    checkboxes.forEach(cb => {
        if (cb.closest('tr').style.display !== 'none') {
            cb.checked = check;
        }
    });
}

function submitExport(reportType, outputOverride = null) {
    const outputType = outputOverride || 'excel';
    
    const checked = document.querySelectorAll('.kds-checkbox:checked').length;
    if (checked === 0 && reportType !== 'formulir_pendataan') {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan pilih minimal satu data santri di tabel untuk di-print/export.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    if (reportType === 'umum' && outputType === 'excel') {
        const modal = new bootstrap.Modal(document.getElementById('modalExportExcel'));
        modal.show();
        return;
    }

    executeExport(reportType, outputType);
}

function processExcelExport() {
    const checkboxes = document.querySelectorAll('.excel-col-cb:checked');
    if (checkboxes.length === 0) {
        Swal.fire({icon: 'warning', title: 'Perhatian', text: 'Pilih minimal satu kolom untuk di-export.', confirmButtonColor: '#0d6efd'});
        return;
    }
    const cols = Array.from(checkboxes).map(cb => cb.value).join(',');
    document.getElementById('exportColumnsInput').value = cols;
    
    const modalEl = document.getElementById('modalExportExcel');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    
    executeExport('umum', 'excel');
}

function executeExport(reportType, outputType) {
    document.getElementById('reportTypeInput').value = reportType;
    document.getElementById('outputTypeInput').value = outputType;
    document.getElementById('exportForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleRightSidebarBtn');
    const wrapper = document.getElementById('printLayoutWrapper');
    
    if (toggleBtn && wrapper) {
        const updateBtnStyle = () => {
            if (wrapper.classList.contains('panel-collapsed')) {
                toggleBtn.classList.replace('btn-outline-primary', 'btn-primary');
                toggleBtn.classList.remove('bg-white');
            } else {
                toggleBtn.classList.replace('btn-primary', 'btn-outline-primary');
                toggleBtn.classList.add('bg-white');
            }
        };
        updateBtnStyle();
        
        toggleBtn.addEventListener('click', function() {
            wrapper.classList.toggle('panel-collapsed');
            const isCollapsed = wrapper.classList.contains('panel-collapsed');
            localStorage.setItem('rightSidebarState', isCollapsed ? 'collapsed' : 'expanded');
            updateBtnStyle();
        });
    }
});
</script>
