<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var float $totalSurplus
 * @var float $piutangSantri
 * @var float $hutangInstansi
 * @var string $chartLabels
 * @var string $chartSurplusData
 * @var string $chartPenerimaanData
 * @var array $transactions
 * @var array $piutangSantriDetails
 * @var array $hutangInstansiDetails
 * @var array $summaryPerJobdesk
 * @var array $instansiInfo
 * @var float $totalPengeluaran
 * @var float $saldoOperasional
 * @var array $pengeluaranList
 * @var array $pengeluaranPerKategori
 * @var array $pengeluaranBulanan
 * @var \App\Shared\ApplicationParams $applicationParams
 */

$this->setTitle('Operasional Birokrasi | ' . $applicationParams->name);

$formatRupiah = function($num) {
    return 'Rp ' . number_format((float)$num, 0, ',', '.');
};
?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= ASSET_URL ?>/assets/offline/css/dataTables.bootstrap5.min.css"/>

<style>
/* ===== PRINT STYLES ===== */
@media print {
    body * { visibility: hidden !important; }
    #printKwitansiArea, #printKwitansiArea * { visibility: visible !important; }
    #printKwitansiArea { position:fixed; left:0; top:0; width:100%; z-index:99999; background:#fff; padding:20px; }
    #printRekapArea, #printRekapArea * { visibility: visible !important; }
    #printRekapArea { position:fixed; left:0; top:0; width:100%; z-index:99999; background:#fff; padding:20px; }
    .modal, .swal2-container { display: none !important; }
}

/* ===== CARD HOVER ===== */
.card-keuangan {
    transition: transform .2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow .2s ease;
    cursor: default;
    border-radius: 14px;
}
.card-keuangan:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0,0,0,.1) !important;
}
.card-keuangan.clickable {
    cursor: pointer;
}
.card-keuangan.clickable:active {
    transform: scale(0.98);
}

/* ===== FILTER & REKAP SECTION ===== */
.filter-jobdesk-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.03);
}
.filter-jobdesk-section .section-title {
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ===== MICRO SUMMARY CARDS (NO OVERLAP) ===== */
.micro-stat-card {
    border-radius: 12px;
    padding: 10px 12px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: left;
    transition: transform .15s ease, box-shadow .15s ease;
    min-height: 80px;
    position: relative;
    overflow: hidden;
}
.micro-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,.12);
}
.micro-stat-card .card-top-label {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    opacity: .88;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.micro-stat-card .card-main-val {
    font-size: .95rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 4px 0 2px 0;
    word-break: break-word;
}
.micro-stat-card .card-sub-note {
    font-size: .63rem;
    opacity: .82;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ===== CHART CONTAINER ===== */
.chart-container-responsive {
    position: relative;
    height: 260px;
    width: 100%;
}

/* ===== TABLE & STICKY ACTION ===== */
.keuangan-table-wrapper {
    max-height: calc(100vh - 280px);
    min-height: 280px;
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    position: relative;
    border-radius: 12px;
}
.keuangan-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.keuangan-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.keuangan-table-wrapper table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 820px;
    width: 100%;
    margin-bottom: 0;
}
.keuangan-table-wrapper thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 10px 12px;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Sticky Action Column on the right */
.keuangan-sticky-action {
    position: sticky;
    right: 0;
    z-index: 8;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.keuangan-table-wrapper thead th.keuangan-sticky-action {
    position: sticky;
    top: 0;
    right: 0;
    z-index: 12;
    background-color: #f8fafc !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.keuangan-table-wrapper tbody td {
    padding: 10px 12px;
    font-size: 0.82rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.keuangan-table-wrapper tbody tr:hover td {
    background-color: #f8fafc;
}
.keuangan-table-wrapper tbody tr:hover td.keuangan-sticky-action {
    background-color: #f8fafc !important;
}

/* Page Header Controls */
.page-header-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.page-header-controls .btn {
    font-size: 0.78rem;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    border-radius: 20px;
}

/* ===== CICILAN PROGRESS ===== */
.progress-cicilan { height: 6px; border-radius: 4px; background: #e9ecef; }
.badge-cicil { font-size: .63rem; }

/* ===== DRAG SELECT HINT ===== */
.drag-select-hint { font-size: .68rem; color: #9fa8b3; font-style: italic; }

/* ===== RESPONSIVE OVERRIDES ===== */
@media (max-width: 991.98px) {
    .page-header-responsive {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px;
    }
    .page-header-controls {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }
    .page-header-controls .btn {
        width: 100%;
        padding: 6px 10px;
        font-size: 0.75rem;
    }
    .card-keuangan {
        border-radius: 12px;
    }
    .filter-jobdesk-section {
        padding: 14px 16px;
    }
    .chart-container-responsive {
        height: 220px;
    }
    .keuangan-table-wrapper {
        max-height: 52vh;
    }
}
@media (max-width: 575.98px) {
    .filter-jobdesk-section {
        padding: 12px;
    }
    .micro-stat-card {
        min-height: 74px;
        padding: 8px 10px;
    }
    .micro-stat-card .card-main-val {
        font-size: .88rem;
    }
}
</style>

<!-- Hidden Print Areas -->
<div id="printKwitansiArea" style="display:none;"></div>
<div id="printRekapArea" style="display:none;"></div>

<div class="container-fluid px-0">

    <!-- === HEADER === -->
    <div class="d-flex align-items-center justify-content-between mb-3 page-header-responsive">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 38px; height: 38px; background: rgba(13, 110, 253, 0.1);">
                <i class="bi bi-graph-up-arrow text-primary fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -.3px;">Dashboard Operasional Birokrasi</h5>
                <p class="text-muted small mb-0" style="font-size: .75rem;">Analitik finansial &amp; rekap transaksi Job Desk</p>
            </div>
        </div>
        <div class="page-header-controls">
            <a href="<?= API_URL ?>/job-desk/pengeluaran-operasional" class="btn btn-sm btn-outline-primary fw-semibold">
                <i class="bi bi-receipt-cutoff me-1"></i> Pengeluaran
            </a>
            <a href="<?= API_URL ?>/job-desk" class="btn btn-sm btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Ke Job Desk
            </a>
        </div>
    </div>

    <!-- === FINANCIAL CARDS (COMPACT) === -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:white;">
                <div class="card-body p-2.5 p-md-3 position-relative d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="opacity-80 fw-semibold text-uppercase" style="letter-spacing:.05em; font-size:.65rem;">Pemasukan Operasional</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-0.5" style="font-size: .65rem;"><i class="bi bi-wallet2"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.2rem;"><?= $formatRupiah($totalSurplus) ?></h4>
                    </div>
                    <div class="small opacity-80 mt-1 text-truncate" style="font-size:.68rem;"><i class="bi bi-check2-circle me-1"></i>Dari transaksi lunas</div>
                    <i class="bi bi-wallet2 position-absolute opacity-10" style="font-size:3.5rem;right:8px;bottom:-6px;pointer-events:none;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan clickable h-100"
                 style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color:white;"
                 onclick="new bootstrap.Modal(document.getElementById('piutangModal')).show()"
                 title="Klik untuk melihat / bayar cicilan">
                <div class="card-body p-2.5 p-md-3 position-relative d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="opacity-80 fw-semibold text-uppercase text-truncate" style="letter-spacing:.05em; font-size:.65rem;">Piutang Santri</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-1.5 py-0.5" style="font-size: .62rem;"><i class="bi bi-box-arrow-up-right"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.2rem;"><?= $formatRupiah($piutangSantri) ?></h4>
                    </div>
                    <div class="small opacity-80 mt-1 text-truncate" style="font-size:.68rem;"><i class="bi bi-hand-index-thumb me-1"></i>Klik bayar/cicil</div>
                    <i class="bi bi-person-down position-absolute opacity-10" style="font-size:3.5rem;right:8px;bottom:-6px;pointer-events:none;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan clickable h-100"
                 style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color:white;"
                 onclick="new bootstrap.Modal(document.getElementById('hutangInstansiModal')).show()"
                 title="Klik untuk melihat / lunasi instansi">
                <div class="card-body p-2.5 p-md-3 position-relative d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="opacity-80 fw-semibold text-uppercase text-truncate" style="letter-spacing:.05em; font-size:.65rem;">Hutang Instansi</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-1.5 py-0.5" style="font-size: .62rem;"><i class="bi bi-box-arrow-up-right"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.2rem;"><?= $formatRupiah($hutangInstansi) ?></h4>
                    </div>
                    <div class="small opacity-80 mt-1 text-truncate" style="font-size:.68rem;"><i class="bi bi-hand-index-thumb me-1"></i>Klik untuk bayar</div>
                    <i class="bi bi-bank2 position-absolute opacity-10" style="font-size:3.5rem;right:8px;bottom:-6px;pointer-events:none;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- === FILTER KUMULATIF PER JOBDESK (2 COLUMNS PER ROW IN MOBILE, NO OVERLAP) === -->
    <div class="filter-jobdesk-section mb-3">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div class="section-title">
                <i class="bi bi-funnel-fill text-primary"></i>
                Filter &amp; Rekap Per Jenis Jobdesk
            </div>
            <div class="d-flex gap-1.5 align-items-center flex-wrap">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" style="font-size:.75rem;" onclick="resetFilterJobdesk()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                </button>
            </div>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1" style="font-size:.72rem;">Pilih Jenis Job Desk / Proses</label>
                <select class="form-select form-select-sm rounded-3" id="filterJobdesk" onchange="applyFilterJobdesk()" style="font-size:.8rem;">
                    <option value="">— Semua Jenis Proses / Jobdesk —</option>
                    <?php foreach ($summaryPerJobdesk as $s): ?>
                        <option value="<?= $s['process_id'] ?>" data-info='<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>'>
                            <?= htmlspecialchars($s['nama_proses']) ?> (<?= $s['jumlah_kasus'] ?> kasus)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1" style="font-size:.72rem;">Pilihan Kop Cetak Rekap</label>
                <select id="printInstansiSelect" class="form-select form-select-sm rounded-3" style="font-size:.8rem;">
                    <option value="">-- Gunakan Kop Default --</option>
                    <?php if (!empty($semuaInstansi)): ?>
                        <?php foreach ($semuaInstansi as $inst): ?>
                            <option value="<?= htmlspecialchars(json_encode($inst), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($inst['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex align-items-end">
                <button class="btn btn-sm btn-primary rounded-3 w-100 py-1.5 fw-bold" style="font-size:.78rem;" onclick="printRekapSantri()">
                    <i class="bi bi-printer me-1"></i>Print Rekap Piutang
                </button>
            </div>
        </div>

        <!-- 6 Summary Cards (2 cols per row on mobile, 3 on tablet, 6 on desktop) -->
        <?php
        $allKasus      = array_sum(array_column($summaryPerJobdesk, 'jumlah_kasus'));
        $allSantriTotal= array_sum(array_column($summaryPerJobdesk, 'total_nominal_santri'));
        $allLunasSantri= array_sum(array_column($summaryPerJobdesk, 'total_lunas_santri'));
        $allBelumSantri= array_sum(array_column($summaryPerJobdesk, 'total_belum_santri'));
        $allInstansi   = array_sum(array_column($summaryPerJobdesk, 'total_nominal_instansi'));
        $allLunasInst  = array_sum(array_column($summaryPerJobdesk, 'total_lunas_instansi'));
        $allBelumInst  = array_sum(array_column($summaryPerJobdesk, 'total_belum_instansi'));
        $allSelisih    = array_sum(array_column($summaryPerJobdesk, 'total_selisih'));
        ?>
        <div class="row g-2" id="summaryJobdeskCards">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="micro-stat-card shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color:white;">
                    <div class="card-top-label">
                        <span>Total Kasus</span>
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div class="card-main-val" id="sv_kasus"><?= $allKasus ?></div>
                    <div class="card-sub-note">Job Desk Aktif</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="micro-stat-card shadow-sm" style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); color:white;">
                    <div class="card-top-label">
                        <span>Tagihan Santri</span>
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="card-main-val" id="sv_santri"><?= $formatRupiah($allSantriTotal) ?></div>
                    <div class="card-sub-note" id="sv_lunas_santri">Lunas: <?= $formatRupiah($allLunasSantri) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="micro-stat-card shadow-sm" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); color:white;">
                    <div class="card-top-label">
                        <span>Piutang Santri</span>
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="card-main-val" id="sv_belum_santri"><?= $formatRupiah($allBelumSantri) ?></div>
                    <div class="card-sub-note">Belum Dibayar</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="micro-stat-card shadow-sm" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); color:white;">
                    <div class="card-top-label">
                        <span>Ke Instansi</span>
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="card-main-val" id="sv_instansi"><?= $formatRupiah($allInstansi) ?></div>
                    <div class="card-sub-note" id="sv_lunas_instansi">Lunas: <?= $formatRupiah($allLunasInst) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="micro-stat-card shadow-sm" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color:white;">
                    <div class="card-top-label">
                        <span>Hutang Instansi</span>
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <div class="card-main-val" id="sv_belum_instansi"><?= $formatRupiah($allBelumInst) ?></div>
                    <div class="card-sub-note">Belum Disetor</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="micro-stat-card shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); color:white;">
                    <div class="card-top-label">
                        <span>Uang Operasional</span>
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="card-main-val" id="sv_selisih"><?= $formatRupiah($allSelisih) ?></div>
                    <div class="card-sub-note">Masuk Kas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- === CHART (Spacious Responsive Canvas) === -->
    <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="fw-bold text-dark small d-flex align-items-center gap-1.5" style="font-size:.82rem;">
                    <i class="bi bi-bar-chart-line-fill text-primary"></i> Tren Uang Operasional &amp; Penerimaan
                </span>
                <span class="text-muted" style="font-size:.7rem;">12 bulan terakhir — data real-time</span>
            </div>
            <div class="d-flex gap-1.5">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 active" id="chartTypeLine" onclick="switchChart('line')" style="font-size:.72rem;">
                    <i class="bi bi-graph-up me-1"></i>Garis
                </button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5" id="chartTypeBar" onclick="switchChart('bar')" style="font-size:.72rem;">
                    <i class="bi bi-bar-chart me-1"></i>Batang
                </button>
            </div>
        </div>
        <div class="card-body px-2 px-md-3 pb-3 pt-1">
            <div class="chart-container-responsive">
                <canvas id="keuanganChart"></canvas>
            </div>
        </div>
    </div>

    <!-- === TRANSACTION TABLE (COMPACT WITH STICKY ACTION) === -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-3 py-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark small d-flex align-items-center gap-1.5" style="font-size:.82rem;">
                <i class="bi bi-table text-primary"></i> Daftar Lengkap Transaksi Job Desk
            </span>
            <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" id="filterActiveLabel" style="display:none; font-size:.7rem;">
                <i class="bi bi-funnel-fill me-1"></i><span id="filterActiveName">Filter aktif</span>
            </div>
        </div>
        <div class="card-body p-2.5 p-md-3">
            <div class="keuangan-table-wrapper border rounded-3">
                <table class="table table-hover align-middle mb-0" id="txTable" style="width:100%">
                    <thead>
                        <tr>
                            <th class="ps-3" style="min-width: 170px;">Data Santri</th>
                            <th style="min-width: 150px;">Proses Job Desk</th>
                            <th style="min-width: 150px;">Pembayaran Santri</th>
                            <th style="min-width: 150px;">Pembayaran Instansi</th>
                            <th style="min-width: 130px;">Uang Operasional</th>
                            <th style="min-width: 120px;">Tanggal Update</th>
                            <th class="pe-3 text-center keuangan-sticky-action" style="width: 80px;">Aksi</th>
                        </tr>
                        <tr class="search-row bg-light border-bottom">
                            <th class="ps-3 py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari santri..." style="font-size:.72rem;"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari proses..." id="txFilterProses" style="font-size:.72rem;"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari (cth: lunas)..." style="font-size:.72rem;"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari (cth: belum)..." style="font-size:.72rem;"></th>
                            <th class="py-1.5"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari tanggal..." style="font-size:.72rem;"></th>
                            <th class="pe-3 py-1.5 keuangan-sticky-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr class="<?= ($tx['status_bayar_santri'] === 'belum') ? 'table-warning' : '' ?>">
                            <td class="ps-3">
                                <div class="fw-bold text-dark" style="font-size:.82rem;"><?= htmlspecialchars($tx['nama']) ?></div>
                                <div class="text-muted" style="font-size:.68rem;">KDS: <?= htmlspecialchars($tx['kds']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size:.68rem;"><?= htmlspecialchars($tx['nama_proses']) ?></span>
                                <div class="text-muted mt-0.5" style="font-size:.65rem;">Case #<?= $tx['case_id'] ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size:.8rem;"><?= $formatRupiah((float)$tx['nominal_santri']) ?></div>
                                <?php if ($tx['status_bayar_santri'] === 'lunas'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-0.5" style="font-size:.62rem;"><i class="bi bi-check2"></i> Lunas <?= $tx['tgl_bayar_santri'] ? '('.date('d/m/y', strtotime($tx['tgl_bayar_santri'])).')' : '' ?></span>
                                <?php else: ?>
                                    <?php $totC = (float)($tx['total_cicilan'] ?? 0); $jmlC = (int)($tx['jumlah_cicilan'] ?? 0); ?>
                                    <?php if ($jmlC > 0): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill mt-0.5 badge-cicil"><i class="bi bi-arrow-repeat me-1"></i>Cicil <?= $jmlC ?>x (<?= $formatRupiah($totC) ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill mt-0.5 fw-bold" style="font-size:.62rem;">Belum Bayar</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size:.8rem;"><?= $formatRupiah((float)$tx['nominal_instansi']) ?></div>
                                <?php if ($tx['status_bayar_instansi'] === 'lunas'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-0.5" style="font-size:.62rem;"><i class="bi bi-check2"></i> Lunas <?= $tx['tgl_bayar_instansi'] ? '('.date('d/m/y', strtotime($tx['tgl_bayar_instansi'])).')' : '' ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mt-0.5" style="font-size:.62rem;">Belum Dibayar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $isLunasSemua = ($tx['status_bayar_santri']==='lunas' && $tx['status_bayar_instansi']==='lunas');
                                $surplus = (float)$tx['selisih_operasional'];
                                ?>
                                <div class="fw-bold <?= $isLunasSemua ? 'text-success' : 'text-muted' ?>" style="font-size:.82rem;">
                                    <?= $isLunasSemua ? '+' : '' ?><?= $formatRupiah($surplus) ?>
                                </div>
                                <?php if (!$isLunasSemua): ?><div class="text-muted" style="font-size:.62rem;">(Belum valid)</div><?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:.74rem;"><?= date('d M Y H:i', strtotime($tx['updated_at'])) ?></td>
                            <td class="pe-3 text-center keuangan-sticky-action">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border rounded-pill shadow-sm dropdown-toggle py-0.5 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:.75rem;">
                                        <i class="bi bi-gear"></i> Aksi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: .82rem;">
                                        <li><a class="dropdown-item py-1.5" href="<?= API_URL ?>/job-desk/<?= $tx['case_id'] ?>"><i class="bi bi-eye text-primary me-2"></i>Lihat Detail</a></li>
                                        <li><button class="dropdown-item py-1.5" onclick="printKwitansiRow(<?= $tx['case_id'] ?>, '<?= htmlspecialchars(addslashes($tx['nama'])) ?>', '<?= htmlspecialchars(addslashes($tx['kds'])) ?>', '<?= htmlspecialchars(addslashes($tx['nama_proses'])) ?>', <?= (float)$tx['nominal_santri'] ?>, <?= (float)($tx['total_cicilan'] ?? 0) ?>)"><i class="bi bi-receipt text-secondary me-2"></i>Cetak Kwitansi</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ===== MODAL PIUTANG SANTRI ===== -->
<div class="modal fade" id="piutangModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning border-0 text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-down me-2"></i>Daftar Santri Belum / Cicil Bayar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="lunasiMasal()">
                            <i class="bi bi-check-all me-1"></i>Lunasi Terpilih
                        </button>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="printRekapSantri()">
                            <i class="bi bi-printer me-1"></i>Print Rekap
                        </button>
                    </div>
                    <span class="drag-select-hint"><i class="bi bi-mouse2 me-1"></i>Drag untuk pilih banyak sekaligus</span>
                </div>
                <div class="table-responsive bg-white rounded-3 p-3 shadow-sm">
                    <table class="table table-hover align-middle mb-0" id="piutangTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;" class="text-center"><input type="checkbox" class="form-check-input" id="checkAllPiutang"></th>
                                <th>KDS</th><th>Nama Santri</th><th>Proses / Case ID</th>
                                <th>Tagihan &amp; Cicilan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($piutangSantriDetails as $p):
                                $totC = (float)($p['total_cicilan'] ?? 0);
                                $jmlC = (int)($p['jumlah_cicilan'] ?? 0);
                                $nom  = (float)$p['nominal_santri'];
                                $sisa = $nom - $totC;
                                $pct  = $nom > 0 ? min(100, ($totC/$nom)*100) : 0;
                            ?>
                            <tr id="row-payment-<?= $p['case_id'] ?>">
                                <td class="text-center"><input type="checkbox" class="form-check-input cb-piutang" value="<?= $p['case_id'] ?>"></td>
                                <td><span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= htmlspecialchars($p['kds']) ?></span></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama']) ?></td>
                                <td>
                                    <div style="font-size:.82rem;"><?= htmlspecialchars($p['nama_proses']) ?></div>
                                    <div class="text-muted" style="font-size:.68rem;">#<?= $p['case_id'] ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger mb-1"><?= $formatRupiah($nom) ?></div>
                                    <?php if ($jmlC > 0): ?>
                                    <div class="progress progress-cicilan mb-1">
                                        <div class="progress-bar bg-info" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between" style="font-size:.65rem;">
                                        <span class="text-info fw-semibold">Dibayar: <?= $formatRupiah($totC) ?></span>
                                        <span class="text-muted">Sisa: <?= $formatRupiah($sisa) ?></span>
                                    </div>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.6rem;">Belum ada cicilan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-gear"></i> Aksi
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: .85rem;">
                                            <li><a class="dropdown-item py-2" href="<?= API_URL ?>/job-desk/<?= $p['case_id'] ?>"><i class="bi bi-eye text-primary me-2"></i>Lihat Detail</a></li>
                                            <li><button class="dropdown-item py-2" onclick="openCicilanModal(<?= $p['case_id'] ?>, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', <?= $nom ?>, <?= $totC ?>, '<?= htmlspecialchars(addslashes($p['kds'])) ?>', '<?= htmlspecialchars(addslashes($p['nama_proses'])) ?>')"><i class="bi bi-cash-coin text-info me-2"></i>Bayar Cicilan</button></li>
                                            <li><button class="dropdown-item py-2" onclick="lunasiSantri(<?= $p['case_id'] ?>)"><i class="bi bi-check-circle text-success me-2"></i>Lunasi Sekaligus</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item py-2" onclick="printKwitansiRow(<?= $p['case_id'] ?>, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', '<?= htmlspecialchars(addslashes($p['kds'])) ?>', '<?= htmlspecialchars(addslashes($p['nama_proses'])) ?>', <?= $nom ?>, <?= $totC ?>)"><i class="bi bi-receipt text-secondary me-2"></i>Cetak Kwitansi</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL HUTANG INSTANSI ===== -->
<div class="modal fade" id="hutangInstansiModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                <h5 class="modal-title fw-bold"><i class="bi bi-bank2 me-2"></i>Hutang ke Instansi (Belum Dibayar)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3 d-flex gap-2 align-items-center justify-content-between">
                    <button class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-sm" onclick="lunasiInstansiMasal()">
                        <i class="bi bi-check-all me-1"></i>Lunasi Terpilih ke Instansi
                    </button>
                    <span class="drag-select-hint"><i class="bi bi-mouse2 me-1"></i>Drag untuk pilih banyak</span>
                </div>
                <div class="table-responsive bg-white rounded-3 p-3 shadow-sm">
                    <table class="table table-hover align-middle mb-0" id="hutangInstansiTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px" class="text-center"><input type="checkbox" class="form-check-input" id="checkAllHutang"></th>
                                <th>KDS</th><th>Nama Santri</th><th>Proses</th>
                                <th>Nominal ke Instansi</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hutangInstansiDetails as $h): ?>
                            <tr id="row-instansi-<?= $h['case_id'] ?>">
                                <td class="text-center"><input type="checkbox" class="form-check-input cb-hutang" value="<?= $h['case_id'] ?>"></td>
                                <td><span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= htmlspecialchars($h['kds']) ?></span></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($h['nama_santri']) ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2"><?= htmlspecialchars($h['nama_proses']) ?></span>
                                    <div class="text-muted mt-1" style="font-size:.68rem;">#<?= $h['case_id'] ?></div>
                                </td>
                                <td><div class="fw-bold text-danger"><?= $formatRupiah((float)$h['nominal_instansi']) ?></div></td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-gear"></i> Aksi
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: .85rem;">
                                            <li><a class="dropdown-item py-2" href="<?= API_URL ?>/job-desk/<?= $h['case_id'] ?>"><i class="bi bi-eye text-primary me-2"></i>Lihat Detail</a></li>
                                            <li><button class="dropdown-item py-2" onclick="lunasiInstansi(<?= $h['case_id'] ?>)"><i class="bi bi-check-circle text-danger me-2"></i>Lunasi Instansi</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CICILAN ===== -->
<div class="modal fade" id="cicilanModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #0dcaf0 0%, #0a9aba 100%);">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cash-coin me-2"></i>Input Cicilan Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="cicil_case_id">
                <input type="hidden" id="cicil_nama_hidden">
                <input type="hidden" id="cicil_kds_hidden">
                <input type="hidden" id="cicil_proses_hidden">
                <!-- Info Santri -->
                <div class="card border-0 bg-white shadow-sm rounded-4 mb-4">
                    <div class="card-body px-4 py-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted small">Nama Santri</div>
                                <div class="fw-bold text-dark" id="cicil_nama_santri">-</div>
                                <div class="text-muted small mt-1" id="cicil_kds_display">-</div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="text-muted small">Total Tagihan</div>
                                <div class="fw-bold text-danger fs-5" id="cicil_total_tagihan">-</div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted small">Sudah Dibayar</div>
                                <div class="fw-semibold text-info" id="cicil_sudah_dibayar">-</div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="text-muted small">Sisa Tagihan</div>
                                <div class="fw-bold text-warning" id="cicil_sisa">-</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="progress" style="height:8px;border-radius:4px;">
                                <div class="progress-bar bg-info" id="cicil_progress_bar" style="width:0%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1" style="font-size:.65rem;">
                                <span class="text-info" id="cicil_pct_label">0%</span>
                                <span class="text-muted" id="cicil_sisa_label">Sisa 100%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Riwayat Cicilan -->
                <div id="cicilRiwayatContainer" style="display:none;" class="mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-clock-history me-2 text-info"></i>Riwayat Cicilan Sebelumnya</h6>
                    <div class="table-responsive bg-white rounded-3 shadow-sm">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>Tgl Bayar</th><th>Nominal</th><th>Catatan</th></tr></thead>
                            <tbody id="cicilRiwayatBody"></tbody>
                        </table>
                    </div>
                </div>
                <!-- Form Cicilan Baru -->
                <div class="card border-0 bg-white shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Cicilan Baru</h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold">Nominal Cicilan (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" id="cicil_nominal" class="form-control" placeholder="Masukkan nominal">
                                </div>
                                <div class="small text-muted mt-1">Maks: <span id="cicil_maks_text">-</span></div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold">Tanggal Bayar</label>
                                <input type="date" id="cicil_tgl" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12">
                                <label class="text-muted small fw-bold">Catatan (Opsional)</label>
                                <input type="text" id="cicil_catatan" class="form-control" placeholder="Misal: Via transfer BRI, dll.">
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2 flex-wrap" id="cicilQuickBtns"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white px-4 py-3 d-flex justify-content-between">
                <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary rounded-pill px-4" onclick="simpanCicilan(false)">
                        <i class="bi bi-floppy me-1"></i>Simpan
                    </button>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold" onclick="simpanCicilan(true)">
                        <i class="bi bi-receipt me-1"></i>Simpan &amp; Kwitansi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== SCRIPTS ===== -->
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/chart.umd.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>

<script>
const summaryJobdesk = <?= json_encode($summaryPerJobdesk) ?>;
const instansiInfo = <?= json_encode($instansiInfo ?? []) ?>;

function fRp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(n)||0)); }

// =============================================
// CHART.JS — Modern Light Mode
// =============================================
let keuanganChart;
let _chartType = 'line';

document.addEventListener('DOMContentLoaded', function() {
    buildChart('line');

    // DataTables
    let txTable = window.txTableInstance = $('#txTable').DataTable({
        "order": [],
        "pageLength": 10,
        "language": { "search": "Cari:", "lengthMenu": "Tampilkan _MENU_", "info": "_START_–_END_ dari _TOTAL_", "paginate": { "first": "«", "last": "»", "next": "›", "previous": "‹" } },
        "dom": "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
    txTable.columns().every(function() {
        let that = this;
        $('input', this.header()).on('keyup change clear', function() {
            if (that.search() !== this.value) that.search(this.value).draw();
        });
    });
    $('.search-row input').on('click', function(e) { e.stopPropagation(); });

    $('#piutangTable').DataTable({ "pageLength": 10, "columnDefs": [{"orderable":false,"targets":[0,5]}], "order":[[2,"asc"]] });
    $('#hutangInstansiTable').DataTable({ "pageLength": 10, "columnDefs": [{"orderable":false,"targets":[0,5]}], "order":[[4,"desc"]] });

    // Check-all
    $('#checkAllPiutang').on('change', function() { $('.cb-piutang').prop('checked', $(this).prop('checked')); });
    $('#checkAllHutang').on('change', function() { $('.cb-hutang').prop('checked', $(this).prop('checked')); });

    // Drag-to-select for piutang checkboxes
    initDragSelect('.cb-piutang');
    initDragSelect('.cb-hutang');
});

function buildChart(type) {
    _chartType = type;
    const ctx = document.getElementById('keuanganChart').getContext('2d');
    const labels = <?= $chartLabels ?>;
    const surplusData = <?= $chartSurplusData ?>;
    const penerimaanData = <?= $chartPenerimaanData ?>;

    if (keuanganChart) keuanganChart.destroy();

    // Update toggle buttons
    document.getElementById('chartTypeLine').classList.toggle('active', type === 'line');
    document.getElementById('chartTypeBar').classList.toggle('active', type === 'bar');

    keuanganChart = new Chart(ctx, {
        type: type === 'line' ? 'line' : 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Penerimaan dr Santri (Rp)',
                    data: penerimaanData,
                    backgroundColor: type === 'line' ? 'rgba(253,126,20,0.1)' : 'rgba(253,126,20,0.6)',
                    borderColor: '#fd7e14',
                    borderWidth: type === 'line' ? 2 : 0,
                    borderRadius: type === 'bar' ? 6 : 0,
                    pointBackgroundColor: '#fd7e14',
                    pointRadius: type === 'line' ? 4 : 0,
                    pointHoverRadius: 6,
                    fill: type === 'line',
                    tension: 0.4,
                    order: 2,
                    yAxisID: 'y'
                },
                {
                    label: 'Uang Operasional (Rp)',
                    data: surplusData,
                    backgroundColor: type === 'line' ? 'rgba(25,135,84,0.08)' : 'rgba(25,135,84,0.65)',
                    borderColor: '#198754',
                    borderWidth: type === 'line' ? 2.5 : 0,
                    borderRadius: type === 'bar' ? 6 : 0,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#198754',
                    pointBorderWidth: 2,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: 7,
                    fill: type === 'line',
                    tension: 0.4,
                    order: 1,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 8, padding: 20, font: { family: 'Inter, sans-serif', size: 12 }, color: '#495057' }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#1a2035',
                    bodyColor: '#495057',
                    borderColor: '#dee2e6',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    callbacks: {
                        label: function(ctx) {
                            let label = ctx.dataset.label || '';
                            if (label) label += ': ';
                            if (ctx.parsed.y !== null) label += new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 }).format(ctx.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#6c757d', font: { size: 11 } },
                    border: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0', drawBorder: false },
                    ticks: {
                        color: '#6c757d', font: { size: 11 },
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000).toFixed(v%1000000===0?0:1) + ' Jt';
                            if (v >= 1000) return (v/1000).toFixed(0) + ' Rb';
                            return v;
                        }
                    },
                    border: { display: false }
                }
            },
            animation: { duration: 500, easing: 'easeInOutQuart' }
        }
    });
}

window.switchChart = function(type) { buildChart(type); };

// =============================================
// DRAG-TO-SELECT CHECKBOXES
// =============================================
function initDragSelect(selector) {
    let isDragging = false, dragState = true, startTr = null;

    document.addEventListener('mousedown', function(e) {
        const cb = e.target.closest('tr')?.querySelector(selector);
        if (!cb) return;
        if (e.target === cb) return; // let normal click handle it
        isDragging = true;
        dragState = !cb.checked;
        cb.checked = dragState;
        cb.dispatchEvent(new Event('change', {bubbles:true}));
        startTr = e.target.closest('tr');
        e.preventDefault();
    });

    document.addEventListener('mouseover', function(e) {
        if (!isDragging) return;
        const tr = e.target.closest('tr');
        if (!tr) return;
        const cb = tr.querySelector(selector);
        if (cb && cb.checked !== dragState) {
            cb.checked = dragState;
            cb.dispatchEvent(new Event('change', {bubbles:true}));
        }
    });

    document.addEventListener('mouseup', function() { isDragging = false; startTr = null; });
}

// =============================================
// FILTER SUMMARY PER JOBDESK → ALSO FILTERS TABLE
// =============================================
function applyFilterJobdesk() {
    const sel = document.getElementById('filterJobdesk');
    const processId = sel.value;
    const label = sel.options[sel.selectedIndex]?.text || '';

    let data;
    if (!processId) {
        data = {
            jumlah_kasus: summaryJobdesk.reduce((s,r)=>s+parseInt(r.jumlah_kasus||0),0),
            total_nominal_santri: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_nominal_santri||0),0),
            total_lunas_santri: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_lunas_santri||0),0),
            total_belum_santri: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_belum_santri||0),0),
            total_nominal_instansi: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_nominal_instansi||0),0),
            total_lunas_instansi: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_lunas_instansi||0),0),
            total_belum_instansi: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_belum_instansi||0),0),
            total_selisih: summaryJobdesk.reduce((s,r)=>s+parseFloat(r.total_selisih||0),0),
        };
    } else {
        const opt = sel.options[sel.selectedIndex];
        data = JSON.parse(opt.getAttribute('data-info'));
    }

    // Update summary cards
    document.getElementById('sv_kasus').textContent = data.jumlah_kasus;
    document.getElementById('sv_santri').textContent = fRp(data.total_nominal_santri);
    document.getElementById('sv_lunas_santri').textContent = 'Lunas: ' + fRp(data.total_lunas_santri);
    document.getElementById('sv_belum_santri').textContent = fRp(data.total_belum_santri);
    document.getElementById('sv_instansi').textContent = fRp(data.total_nominal_instansi);
    document.getElementById('sv_lunas_instansi').textContent = 'Lunas: ' + fRp(data.total_lunas_instansi);
    document.getElementById('sv_belum_instansi').textContent = fRp(data.total_belum_instansi);
    document.getElementById('sv_selisih').textContent = fRp(data.total_selisih);

    // Filter table by proses name
    const filterLabel = document.getElementById('filterActiveLabel');
    const filterName = document.getElementById('filterActiveName');
    if (window.txTableInstance) {
        const prosesFilter = processId ? label.replace(/\s*\(\d+ kasus\)$/, '') : '';
        window.txTableInstance.column(1).search(prosesFilter).draw();
        if (processId) {
            filterLabel.style.display = '';
            filterName.textContent = prosesFilter;
        } else {
            filterLabel.style.display = 'none';
        }
    }
}

function resetFilterJobdesk() {
    document.getElementById('filterJobdesk').value = '';
    applyFilterJobdesk();
}

// =============================================
// PELUNASAN SANTRI
// =============================================
function executePelunasan(caseIds, type = 'santri') {
    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    const payload = { case_ids: caseIds, type, tgl_bayar: new Date().toISOString().split('T')[0] };
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('<?= API_URL ?>/api/job-desk/payment/bulk-update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        body: JSON.stringify(payload)
    }).then(r=>r.json()).then(data => {
        if (data.status==='success'||data.success) {
            Swal.fire({ title:'Berhasil!', text:'Pelunasan berhasil dicatat.', icon:'success', timer:1500, showConfirmButton:false }).then(()=>location.reload());
        } else {
            Swal.fire('Error', data.message||'Terjadi kesalahan.', 'error');
        }
    }).catch(()=>Swal.fire('Error','Gagal terhubung ke server.','error'));
}

window.lunasiSantri = function(caseId) {
    Swal.fire({ title:'Konfirmasi Pelunasan', text:'Tandai santri ini LUNAS sekaligus?', icon:'question', showCancelButton:true, confirmButtonColor:'#198754', confirmButtonText:'Ya, Lunas!' })
        .then(r=>{ if(r.isConfirmed) executePelunasan([caseId]); });
};

window.lunasiMasal = function() {
    let sel = [];
    $('.cb-piutang:checked').each(function(){ sel.push(parseInt($(this).val())); });
    if (!sel.length) { Swal.fire('Pilih Data','Pilih minimal 1 santri.','warning'); return; }
    Swal.fire({ title:'Konfirmasi', text:`Lunasi ${sel.length} santri terpilih sekaligus?`, icon:'question', showCancelButton:true, confirmButtonColor:'#198754', confirmButtonText:'Ya, Lunasi!' })
        .then(r=>{ if(r.isConfirmed) executePelunasan(sel); });
};

window.lunasiInstansi = function(caseId) {
    Swal.fire({ title:'Konfirmasi', text:'Tandai hutang ke instansi ini LUNAS?', icon:'question', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Ya, Lunas!' })
        .then(r=>{ if(r.isConfirmed) executePelunasan([caseId],'instansi'); });
};

window.lunasiInstansiMasal = function() {
    let sel = [];
    $('.cb-hutang:checked').each(function(){ sel.push(parseInt($(this).val())); });
    if (!sel.length) { Swal.fire('Pilih Data','Pilih minimal 1 item.','warning'); return; }
    Swal.fire({ title:'Konfirmasi', text:`Lunasi hutang ke instansi untuk ${sel.length} item?`, icon:'question', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Ya, Lunasi!' })
        .then(r=>{ if(r.isConfirmed) executePelunasan(sel,'instansi'); });
};

// =============================================
// CICILAN
// =============================================
let _cicilData = {};

window.openCicilanModal = function(caseId, nama, nomSantri, totCicilan, kds, namaProses) {
    document.getElementById('cicil_case_id').value = caseId;
    document.getElementById('cicil_nama_hidden').value = nama;
    document.getElementById('cicil_kds_hidden').value = kds || '';
    document.getElementById('cicil_proses_hidden').value = namaProses || '';
    document.getElementById('cicil_nama_santri').textContent = nama;
    document.getElementById('cicil_kds_display').textContent = 'KDS: ' + (kds||'-') + ' | ' + (namaProses||'');
    document.getElementById('cicil_total_tagihan').textContent = fRp(nomSantri);
    const sisa = nomSantri - totCicilan;
    document.getElementById('cicil_sudah_dibayar').textContent = fRp(totCicilan);
    document.getElementById('cicil_sisa').textContent = fRp(sisa);
    document.getElementById('cicil_maks_text').textContent = fRp(sisa);
    document.getElementById('cicil_nominal').value = '';
    document.getElementById('cicil_nominal').max = sisa;
    document.getElementById('cicil_catatan').value = '';
    document.getElementById('cicil_tgl').value = new Date().toISOString().split('T')[0];
    const pct = nomSantri > 0 ? Math.min(100, totCicilan/nomSantri*100) : 0;
    document.getElementById('cicil_progress_bar').style.width = pct + '%';
    document.getElementById('cicil_pct_label').textContent = Math.round(pct) + '% terbayar';
    document.getElementById('cicil_sisa_label').textContent = 'Sisa ' + Math.round(100-pct) + '%';
    _cicilData = { caseId, nama, nomSantri, totCicilan, sisa, kds, namaProses };

    // Quick amount buttons
    const qb = document.getElementById('cicilQuickBtns');
    qb.innerHTML = '';
    [25,50,75,100].forEach(p => {
        const amt = Math.round(sisa * p / 100);
        if (amt <= 0) return;
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-outline-info rounded-pill';
        btn.innerHTML = `${p}% <span class="text-dark fw-semibold">(${fRp(amt)})</span>`;
        btn.onclick = () => { document.getElementById('cicil_nominal').value = amt; };
        qb.appendChild(btn);
    });

    // Load riwayat
    fetch(`<?= API_URL ?>/api/job-desk/payment/${caseId}/installments`)
        .then(r=>r.json()).then(res=>{
            if (res.success && res.installments?.length > 0) {
                document.getElementById('cicilRiwayatContainer').style.display = 'block';
                const tbody = document.getElementById('cicilRiwayatBody');
                tbody.innerHTML = res.installments.map(i=>`
                    <tr>
                        <td>${i.tgl_bayar}</td>
                        <td class="fw-semibold text-info">${fRp(i.nominal)}</td>
                        <td class="text-muted small">${i.catatan||'-'}</td>
                    </tr>`).join('');
            } else {
                document.getElementById('cicilRiwayatContainer').style.display = 'none';
            }
        }).catch(()=>{});

    // Hide piutang modal temporarily if it's open, to avoid backdrop issues
    const piutangModalEl = document.getElementById('piutangModal');
    if (piutangModalEl.classList.contains('show')) {
        bootstrap.Modal.getInstance(piutangModalEl).hide();
        // optionally, we could re-open it when cicilanModal is closed, but reloading is simpler
    }

    const cicilanModal = new bootstrap.Modal(document.getElementById('cicilanModal'));
    cicilanModal.show();
    document.getElementById('cicilanModal').addEventListener('shown.bs.modal', () => {
        document.getElementById('cicil_nominal').focus();
    }, {once: true});
};

window.simpanCicilan = function(andPrint = false) {
    const caseId = document.getElementById('cicil_case_id').value;
    const nominal = parseFloat(document.getElementById('cicil_nominal').value||0);
    const tglBayar = document.getElementById('cicil_tgl').value;
    const catatan = document.getElementById('cicil_catatan').value;

    if (!nominal || nominal <= 0) { Swal.fire('Peringatan','Masukkan nominal cicilan yang valid.','warning'); return; }
    if (nominal > _cicilData.sisa + 0.01) { Swal.fire('Peringatan',`Melebihi sisa tagihan (${fRp(_cicilData.sisa)}).`,'warning'); return; }

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    Swal.fire({ title:'Menyimpan...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

    fetch(`<?= API_URL ?>/api/job-desk/payment/${caseId}/cicil`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-Token':csrf },
        body: JSON.stringify({ nominal, tgl_bayar: tglBayar, catatan })
    }).then(r=>r.json()).then(res=>{
        if (res.success) {
            if (andPrint) {
                Swal.close();
                // Close modal then print
                const modalEl = document.getElementById('cicilanModal');
                bootstrap.Modal.getInstance(modalEl)?.hide();
                setTimeout(()=>{
                    doPrintKwitansi({
                        caseId, nama: _cicilData.nama, kds: _cicilData.kds,
                        namaProses: _cicilData.namaProses,
                        nominal, tglBayar, catatan,
                        sisaTagihan: res.sisa_tagihan, isLunas: res.is_lunas,
                        totalTagihan: _cicilData.nomSantri
                    });
                    setTimeout(()=>location.reload(), 3000);
                }, 400);
            } else {
                Swal.fire({ icon:'success', title: res.is_lunas ? 'Lunas!' : 'Cicilan Dicatat', text:res.message, timer:1800, showConfirmButton:false })
                    .then(()=>location.reload());
            }
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).catch(()=>Swal.fire('Error','Gagal terhubung ke server.','error'));
};

// =============================================
// KWITANSI — FIXED PRINT (uses printRekapArea approach)
// =============================================
window.printKwitansiRow = function(caseId, nama, kds, namaProses, nomSantri, totCicilan) {
    const sisa = nomSantri - totCicilan;
    doPrintKwitansi({ caseId, nama, kds, namaProses, nominal: nomSantri, tglBayar: new Date().toLocaleDateString('id-ID'), catatan:'', sisaTagihan: sisa, isLunas: (sisa <= 0), totalTagihan: nomSantri });
};

function doPrintKwitansi(data) {
    const noKwt = 'KWT-' + String(data.caseId).padStart(5,'0') + '-' + new Date().getFullYear();
    const instNama = instansiInfo.nama_instansi || 'Sistem Informasi';
    const instAlamat = instansiInfo.alamat || '';
    const instPhone = instansiInfo.phone || '';

    const html = `
    <div style="font-family:'Courier New',monospace;max-width:420px;margin:20px auto;border:2px solid #1a2035;border-radius:8px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.15);">
        <!-- Header Kop -->
        <div style="background:#1a2035;color:white;padding:16px 20px;text-align:center;">
            <div style="font-size:15px;font-weight:bold;letter-spacing:1px;">${instNama}</div>
            ${instAlamat ? `<div style="font-size:10px;opacity:.75;margin-top:3px;">${instAlamat}</div>` : ''}
            ${instPhone ? `<div style="font-size:10px;opacity:.75;">Telp: ${instPhone}</div>` : ''}
            <div style="margin-top:10px;border-top:1px solid rgba(255,255,255,.3);padding-top:8px;font-size:13px;letter-spacing:3px;font-weight:bold;">KWITANSI PEMBAYARAN</div>
        </div>
        <!-- Body -->
        <div style="padding:16px 20px;background:white;">
            <table style="width:100%;font-size:11px;border-collapse:collapse;margin-bottom:12px;">
                <tr><td style="padding:3px 0;width:38%;color:#666;">No. Kwitansi</td><td style="font-weight:bold;">: ${noKwt}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">Tanggal Bayar</td><td>: ${data.tglBayar}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">Nama Santri</td><td style="font-weight:bold;">: ${data.nama}</td></tr>
                ${data.kds ? `<tr><td style="padding:3px 0;color:#666;">KDS</td><td>: ${data.kds}</td></tr>` : ''}
                ${data.namaProses ? `<tr><td style="padding:3px 0;color:#666;">Jenis Proses</td><td>: ${data.namaProses}</td></tr>` : ''}
                <tr><td style="padding:3px 0;color:#666;">Case ID</td><td>: #${data.caseId}</td></tr>
            </table>
            <!-- Nominal -->
            <div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:12px;text-align:center;margin-bottom:12px;">
                <div style="font-size:10px;color:#6c757d;margin-bottom:4px;text-transform:uppercase;letter-spacing:.08em;">Jumlah Dibayar</div>
                <div style="font-size:24px;font-weight:bold;color:#1a2035;">${fRp(data.nominal)}</div>
                ${data.catatan ? `<div style="font-size:10px;color:#6c757d;margin-top:4px;">Ket: ${data.catatan}</div>` : ''}
            </div>
            <!-- Status -->
            ${data.sisaTagihan > 0
                ? `<div style="background:#fff3cd;border:1px solid #ffc107;padding:8px 12px;border-radius:6px;font-size:11px;margin-bottom:12px;">
                    <strong>Sisa Tagihan: ${fRp(data.sisaTagihan)}</strong><br>
                    <span style="color:#856404;">Total tagihan: ${fRp(data.totalTagihan)}</span>
                   </div>`
                : `<div style="background:#d1e7dd;border:1px solid #198754;padding:8px 12px;border-radius:6px;font-size:11px;margin-bottom:12px;text-align:center;">
                    <strong>LUNAS — Tidak ada sisa tagihan</strong>
                   </div>`
            }
            <div style="text-align:center;margin-top:10px;font-size:9px;color:#aaa;">Dicetak: ${new Date().toLocaleString('id-ID')}</div>
        </div>
    </div>`;

    const area = document.getElementById('printKwitansiArea');
    area.innerHTML = html;
    document.body.appendChild(area);
    area.style.display = 'block';

    // Remove existing print style if any
    document.getElementById('kwitansi-print-style')?.remove();
    const ps = document.createElement('style');
    ps.id = 'kwitansi-print-style';
    ps.innerHTML = `@media print { body > * { display:none !important; } #printKwitansiArea { display:block !important; position:fixed;left:0;top:0;width:100%;z-index:99999;background:white; } }`;
    document.head.appendChild(ps);

    window.print();
    setTimeout(()=>{
        area.style.display = 'none';
        ps.remove();
    }, 1500);
}

// =============================================
// PRINT REKAPITULASI DENGAN KOP INSTANSI
// =============================================
window.printRekapSantri = function() {
    let allPiutang = <?= json_encode($piutangSantriDetails) ?>;
    let instNama = instansiInfo.nama_instansi || 'Sistem Informasi';
    let instAlamat = instansiInfo.alamat || '';
    let instPhone = instansiInfo.phone || '';

    let instKode = instansiInfo.kode || '';
    let instKop = instansiInfo.kop_surat || '';

    // If super admin selected an instansi
    const instSelect = document.getElementById('printInstansiSelect');
    if (instSelect && instSelect.value) {
        try {
            const selInst = JSON.parse(instSelect.value);
            instNama = selInst.nama_instansi || instNama;
            instAlamat = selInst.alamat || instAlamat;
            instPhone = selInst.phone || instPhone;
            instKode = selInst.kode || instKode;
            instKop = selInst.kop_surat || instKop;
        } catch(e) {}
    }

    // Filter by selected jobdesk if any
    const filterId = document.getElementById('filterJobdesk').value;
    let groupedData = {};

    if (filterId) {
        allPiutang = allPiutang.filter(p => p.process_id == filterId);
        groupedData[allPiutang.length ? allPiutang[0].nama_proses : 'Filter Jobdesk'] = allPiutang;
    } else {
        allPiutang.forEach(p => {
            const jp = p.nama_proses || 'Lainnya';
            if(!groupedData[jp]) groupedData[jp] = [];
            groupedData[jp].push(p);
        });
    }

    let rowsHtml = '';
    let grandTotal = 0;

    Object.keys(groupedData).forEach(groupName => {
        const groupItems = groupedData[groupName];
        if (groupItems.length === 0) return;
        
        if (!filterId) {
            rowsHtml += `<tr style="background:#f8f9fa;"><td colspan="6" style="padding:10px 12px; font-weight:bold; color:#1a2035; border:1px solid #e9ecef; font-size:12px; text-transform: uppercase; letter-spacing: 0.5px;">${groupName}</td></tr>`;
        }
        
        let groupTotal = 0;
        groupItems.forEach((p, i) => {
            const nom = parseFloat(p.nominal_santri||0);
            const cic = parseFloat(p.total_cicilan||0);
            const sisa = nom - cic;
            groupTotal += nom;
            
            rowsHtml += `<tr>
                <td style="padding:8px 12px;border:1px solid #e9ecef;text-align:center;">${i+1}</td>
                <td style="padding:8px 12px;border:1px solid #e9ecef;">
                    <div style="font-size:10px;color:#666;margin-bottom:2px;">KDS: ${p.kds}</div>
                    <div style="font-weight:600;font-size:12px;">${p.nama}</div>
                </td>
                <td style="padding:8px 12px;border:1px solid #e9ecef;text-align:right;">${fRp(nom)}</td>
                <td style="padding:8px 12px;border:1px solid #e9ecef;text-align:right;">${cic>0?fRp(cic):'-'}</td>
                <td style="padding:8px 12px;border:1px solid #e9ecef;text-align:right;font-weight:bold;color:${sisa>0?'#dc3545':'#198754'};">${fRp(sisa)}</td>
                <td style="padding:8px 12px;border:1px solid #e9ecef;text-align:center;">
                    ${p.jumlah_cicilan>0 ? `<span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:10px;">${p.jumlah_cicilan}x Cicilan</span>` : `<span style="background:#fef3c7;color:#b45309;padding:2px 6px;border-radius:4px;font-size:10px;">Belum</span>`}
                </td>
            </tr>`;
        });
        grandTotal += groupTotal;
        
        if (!filterId) {
            rowsHtml += `<tr><td colspan="2" style="padding:8px 12px;border:1px solid #e9ecef;text-align:right;font-weight:bold;font-size:10px;color:#6c757d;">Subtotal ${groupName}</td><td style="padding:8px 12px;border:1px solid #e9ecef;text-align:right;font-weight:bold;">${fRp(groupTotal)}</td><td colspan="3" style="border:1px solid #e9ecef;"></td></tr>`;
        }
    });

    let kopHtml = '';
    if (instKop) {
        kopHtml = `
        <div style="width:100%; margin-bottom:16px;">
            <img src="<?= API_URL ?>/profil-instansi/kop-surat/view?kode=${encodeURIComponent(instKode)}&v=${new Date().getTime()}" style="width:100%; height:auto; display:block;" alt="Kop Surat">
        </div>`;
    } else {
        kopHtml = `
        <div style="display:flex;align-items:center;border-bottom:3px solid #1a2035;padding:16px 30px;margin-bottom:24px;">
            <div style="flex:1;text-align:center;">
                <h1 style="font-size:22px;font-weight:800;margin:0 0 4px 0;letter-spacing:-0.5px;">${instNama}</h1>
                ${instAlamat ? `<div style="font-size:12px;color:#64748b;line-height:1.4;">${instAlamat}</div>` : ''}
                ${instPhone ? `<div style="font-size:12px;color:#64748b;line-height:1.4;">Telp: ${instPhone}</div>` : ''}
            </div>
        </div>`;
    }

    const html = `
    <div style="font-family:'Inter', Arial, sans-serif; max-width:1000px; margin:0 auto; color:#1a2035;">
        ${kopHtml}
        
        <div style="padding: 0 30px 30px 30px;">
            <div style="text-align:center;margin-bottom:24px;">
                <h2 style="font-size:16px;font-weight:800;margin:0 0 6px 0;text-transform:uppercase;letter-spacing:1px;">REKAPITULASI PIUTANG SANTRI</h2>
                <p style="font-size:12px;color:#64748b;margin:0;">Status Pembayaran: Belum Lunas / Cicilan</p>
                <p style="font-size:12px;color:#64748b;margin:4px 0 0 0;">Dicetak pada: ${new Date().toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'})}</p>
            </div>

            <table style="width:100%;border-collapse:collapse;font-size:11.5px;margin-bottom:30px;box-shadow:0 0 0 1px #e2e8f0;border-radius:8px;overflow:hidden;">
            <thead>
                <tr style="background:#f1f5f9;border-bottom:2px solid #cbd5e1;">
                    <th style="padding:12px;text-align:center;color:#475569;font-weight:700;width:40px;">No</th>
                    <th style="padding:12px;text-align:left;color:#475569;font-weight:700;">Data Santri</th>
                    <th style="padding:12px;text-align:right;color:#475569;font-weight:700;">Total Tagihan</th>
                    <th style="padding:12px;text-align:right;color:#475569;font-weight:700;">Telah Dibayar</th>
                    <th style="padding:12px;text-align:right;color:#475569;font-weight:700;">Sisa Tagihan</th>
                    <th style="padding:12px;text-align:center;color:#475569;font-weight:700;width:90px;">Status</th>
                </tr>
            </thead>
            <tbody>${rowsHtml}</tbody>
            <tfoot>
                <tr style="background:#1e293b;color:white;">
                    <td colspan="2" style="padding:12px;text-align:right;font-size:13px;font-weight:700;letter-spacing:1px;">TOTAL KESELURUHAN PIUTANG:</td>
                    <td style="padding:12px;text-align:right;font-size:14px;font-weight:800;">${fRp(grandTotal)}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>`;

    const area = document.getElementById('printRekapArea');
    area.innerHTML = html;
    document.body.appendChild(area);
    area.style.display = 'block';

    document.getElementById('rekap-print-style')?.remove();
    const ps = document.createElement('style');
    ps.id = 'rekap-print-style';
    ps.innerHTML = `@media print { body > * { display:none !important; } #printRekapArea { display:block !important; position:fixed;left:0;top:0;width:100%;z-index:99999;background:white; } }`;
    document.head.appendChild(ps);

    const finishPrint = () => {
        window.print();
        setTimeout(()=>{
            area.style.display = 'none';
            ps.remove();
        }, 1500);
    };

    const img = area.querySelector('img');
    if (img) {
        if (img.complete) {
            finishPrint();
        } else {
            img.onload = finishPrint;
            img.onerror = finishPrint;
        }
    } else {
        finishPrint();
    }
};
</script>
