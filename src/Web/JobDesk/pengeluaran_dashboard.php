<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var float $totalPemasukan
 * @var float $totalPengeluaran
 * @var float $saldoOperasional
 * @var array $pengeluaranList
 * @var array $pengeluaranPerKategori
 * @var array $pengeluaranBulanan
 * @var string $bulananLabels
 * @var string $bulananPengeluaran
 * @var string $bulananPemasukan
 * @var array $kategoris
 * @var array $kategoriColors
 * @var array $kategoriIcons
 * @var array $instansiInfo
 * @var array $semuaInstansi
 * @var \App\Shared\ApplicationParams $applicationParams
 */

$this->setTitle('Pengeluaran Operasional | ' . $applicationParams->name);

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
    #printArea, #printArea * { visibility: visible !important; }
    #printArea { position: fixed; left: 0; top: 0; width: 100%; z-index: 99999; background: #fff; padding: 20px; }
    .modal, .swal2-container { display: none !important; }
}

/* ===== MODERN CARDS ===== */
.card-keuangan {
    transition: transform .2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow .2s ease;
    cursor: default;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.15);
}
.card-keuangan:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(0,0,0,.12) !important;
}

/* ===== CATEGORY CHIPS ===== */
.kategori-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 600;
    color: white;
    transition: all .18s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    cursor: pointer;
    user-select: none;
    border: 1px solid rgba(255,255,255,0.2);
}
.kategori-chip:hover {
    opacity: .92;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.kategori-chip.active {
    box-shadow: 0 0 0 3px #0d6efd, 0 4px 12px rgba(0,0,0,0.15);
}

.kategori-manage-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 8px;
    font-size: .84rem;
    transition: background .15s;
}
.kategori-manage-item:hover {
    background: #f1f5f9;
}
.kategori-manage-item .color-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* ===== QUICK FILTER PILLS ===== */
.quick-filter-pills {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.quick-filter-pills::-webkit-scrollbar { display: none; }
.btn-quick-filter {
    font-size: 0.74rem;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    white-space: nowrap;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    transition: all 0.15s ease;
    cursor: pointer;
}
.btn-quick-filter:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}
.btn-quick-filter.active {
    background: #0d6efd !important;
    color: #ffffff !important;
    border-color: #0d6efd !important;
    box-shadow: 0 2px 6px rgba(13,110,253,0.3);
}

/* ===== CHART CONTAINER ===== */
.chart-container-responsive {
    position: relative;
    height: 240px;
    width: 100%;
}

/* ===== TABLE & STICKY ACTION ===== */
.pengeluaran-table-wrapper {
    max-height: calc(100vh - 280px);
    min-height: 280px;
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    position: relative;
    border-radius: 14px;
    background: #ffffff;
}
.pengeluaran-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.pengeluaran-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.pengeluaran-table-wrapper table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 860px;
    width: 100%;
    margin-bottom: 0;
}
.pengeluaran-table-wrapper thead th {
    background: #f8fafc !important;
    border-bottom: 2px solid #e2e8f0;
    color: #334155;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 12px 14px;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Sticky Action Column on the right */
.pengeluaran-sticky-action {
    position: sticky;
    right: 0;
    z-index: 8;
    background-color: #ffffff !important;
    box-shadow: -4px 0 10px -2px rgba(0,0,0,0.06);
    min-width: 130px;
    width: 130px;
}
.pengeluaran-table-wrapper thead th.pengeluaran-sticky-action {
    position: sticky;
    top: 0;
    right: 0;
    z-index: 15;
    background-color: #f8fafc !important;
    box-shadow: -4px 0 10px -2px rgba(0,0,0,0.06);
}
.pengeluaran-table-wrapper tbody td {
    padding: 11px 14px;
    font-size: 0.82rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.pengeluaran-table-wrapper tbody tr:hover td {
    background-color: #f8fafc;
}
.pengeluaran-table-wrapper tbody tr:hover td.pengeluaran-sticky-action {
    background-color: #f8fafc !important;
}

/* Page Header Controls */
.page-header-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}
.page-header-controls .btn {
    font-size: 0.8rem;
    padding: 7px 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
    border-radius: 20px;
    line-height: 1.4;
}
.page-header-controls .btn i {
    font-size: 0.92rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
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
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .page-header-controls .btn {
        width: 100%;
        padding: 8px 12px;
        font-size: 0.78rem;
    }
    .chart-container-responsive {
        height: 200px;
    }
    .pengeluaran-table-wrapper {
        max-height: 55vh;
    }
}
@media (max-width: 480px) {
    .page-header-controls {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Hidden Print Area -->
<div id="printArea" style="display:none;"></div>

<div class="container-fluid px-0">

    <!-- === HEADER === -->
    <div class="d-flex align-items-center justify-content-between mb-3 page-header-responsive">
        <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                <i class="bi bi-receipt-cutoff fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -.3px;">Pengeluaran Operasional Birokrasi</h5>
                <p class="text-muted small mb-0" style="font-size: .75rem;">Pencatatan realisasi belanja, rincian biaya dinas, &amp; monitoring kas</p>
            </div>
        </div>
        <div class="page-header-controls">
            <button class="btn btn-sm btn-primary fw-semibold shadow-xs" onclick="showFormPengeluaran()">
                <i class="bi bi-plus-lg"></i>
                <span>Catat Pengeluaran</span>
            </button>
            <button class="btn btn-sm btn-outline-info fw-semibold shadow-xs" onclick="showKategoriManager()">
                <i class="bi bi-tags"></i>
                <span>Kelola Kategori</span>
            </button>
            <button class="btn btn-sm btn-outline-primary fw-semibold shadow-xs" onclick="printLaporanPengeluaran()">
                <i class="bi bi-printer"></i>
                <span>Cetak Laporan</span>
            </button>
            <button class="btn btn-sm btn-outline-secondary fw-semibold shadow-xs" onclick="exportPengeluaran()">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <span>Export CSV</span>
            </button>
            <a href="<?= API_URL ?>/job-desk/keuangan" class="btn btn-sm btn-outline-secondary fw-semibold shadow-xs">
                <i class="bi bi-arrow-left"></i>
                <span>Dashboard Keuangan</span>
            </a>
        </div>
    </div>

    <!-- === FINANCIAL OVERVIEW CARDS (HANYA ICON BESAR) === -->
    <div class="row g-2.5 mb-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:white;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="min-height: 105px;">
                    <div>
                        <div class="opacity-85 fw-bold text-uppercase mb-1" style="letter-spacing:.05em; font-size:.68rem;">Pemasukan Operasional</div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.35rem;"><?= $formatRupiah($totalPemasukan) ?></h4>
                    </div>
                    <div class="small opacity-85 mt-2 text-truncate" style="font-size:.72rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>Selisih Job Desk (Santri Lunas)
                    </div>
                    <i class="bi bi-wallet2 position-absolute" style="font-size:4rem; right:14px; bottom:2px; opacity:0.18; pointer-events:none;"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color:white;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="min-height: 105px;">
                    <div>
                        <div class="opacity-85 fw-bold text-uppercase mb-1" style="letter-spacing:.05em; font-size:.68rem;">Total Pengeluaran</div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.35rem;"><?= $formatRupiah($totalPengeluaran) ?></h4>
                    </div>
                    <div class="small opacity-85 mt-2 text-truncate" style="font-size:.72rem;">
                        <i class="bi bi-card-checklist me-1"></i><?= count($pengeluaranList) ?> transaksi pengeluaran tercatat
                    </div>
                    <i class="bi bi-receipt position-absolute" style="font-size:4rem; right:14px; bottom:2px; opacity:0.18; pointer-events:none;"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, <?= $saldoOperasional >= 0 ? '#3b82f6 0%, #1d4ed8 100%' : '#f59e0b 0%, #d97706 100%' ?>); color:white;">
                <div class="card-body p-3.5 position-relative d-flex flex-column justify-content-between" style="min-height: 105px;">
                    <div>
                        <div class="opacity-85 fw-bold text-uppercase mb-1" style="letter-spacing:.05em; font-size:.68rem;">Sisa Saldo Kas</div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.35rem;"><?= $formatRupiah($saldoOperasional) ?></h4>
                    </div>
                    <div class="small opacity-85 mt-2 text-truncate" style="font-size:.72rem;">
                        <i class="bi bi-calculator me-1"></i>Pemasukan &minus; Pengeluaran
                    </div>
                    <i class="bi bi-safe2 position-absolute" style="font-size:4rem; right:14px; bottom:2px; opacity:0.18; pointer-events:none;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- === BREAKDOWN PER KATEGORI === -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3 p-md-3.5">
            <div class="d-flex align-items-center justify-content-between mb-2.5 flex-wrap gap-2">
                <span class="fw-bold text-dark small d-flex align-items-center gap-2" style="font-size:.84rem;">
                    <i class="bi bi-pie-chart-fill text-primary"></i> Rincian Pengeluaran Per Kategori
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size:.7rem;"><?= count($pengeluaranPerKategori) ?> Kategori Aktif</span>
                    <button class="btn btn-sm btn-link text-primary p-0 text-decoration-none small fw-semibold" style="font-size:.75rem;" onclick="resetCategoryFilter()">Reset Filter Kategori</button>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2" id="kategoriChipsContainer">
                <?php if (empty($pengeluaranPerKategori)): ?>
                    <span class="text-muted small py-1" style="font-size:.78rem;"><em>Belum ada transaksi pengeluaran tercatat</em></span>
                <?php else: ?>
                    <?php foreach ($pengeluaranPerKategori as $pk): ?>
                        <div class="kategori-chip" data-kategori="<?= htmlspecialchars($pk['kategori']) ?>" style="background: <?= $kategoriColors[$pk['kategori']] ?? '#6c757d' ?>;" onclick="filterByCategory('<?= htmlspecialchars(addslashes($pk['kategori'])) ?>', this)">
                            <i class="bi <?= $kategoriIcons[$pk['kategori']] ?? 'bi-tag' ?>"></i>
                            <span><?= htmlspecialchars($pk['kategori']) ?>: <?= $formatRupiah((float)$pk['total']) ?></span>
                            <span class="badge bg-white bg-opacity-25 rounded-pill ms-0.5"><?= $pk['jumlah'] ?>x</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- === CHART TREND ANALYTICS (LINE / BAR SWITCHER) === -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="fw-bold text-dark small d-flex align-items-center gap-2" style="font-size:.86rem;">
                    <i class="bi bi-graph-up-arrow text-primary"></i> Tren Finansial Bulanan (Pemasukan vs Pengeluaran)
                </span>
                <span class="text-muted" style="font-size:.72rem;">Perbandingan realisasi kas operasional 6-12 bulan terakhir</span>
            </div>
            <div class="d-flex gap-1.5">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 active d-inline-flex align-items-center gap-1.5" id="chartTypeBar" onclick="switchPengeluaranChart('bar')" style="font-size:.74rem;">
                    <i class="bi bi-bar-chart-fill"></i><span>Batang</span>
                </button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5" id="chartTypeLine" onclick="switchPengeluaranChart('line')" style="font-size:.74rem;">
                    <i class="bi bi-graph-up"></i><span>Garis</span>
                </button>
            </div>
        </div>
        <div class="card-body px-2 px-md-4 pb-3 pt-1">
            <div class="chart-container-responsive">
                <canvas id="pengeluaranChart"></canvas>
            </div>
        </div>
    </div>

    <!-- === TABLE PENGELUARAN === -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-3 px-md-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="fw-bold text-dark small d-flex align-items-center gap-2" style="font-size:.9rem;">
                    <i class="bi bi-table text-primary fs-5"></i> Riwayat &amp; Buku Kas Pengeluaran Operasional
                </span>
                <span class="text-muted" style="font-size:.73rem;">Daftar seluruh transaksi belanja operasional birokrasi beserta bukti nota</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Quick Filter Pills -->
                <div class="quick-filter-pills">
                    <button type="button" class="btn-quick-filter active" onclick="filterTableQuick('all', this)"><i class="bi bi-grid-fill"></i><span>Semua</span></button>
                    <button type="button" class="btn-quick-filter" onclick="filterTableQuick('this_month', this)"><i class="bi bi-calendar-check text-primary"></i><span>Bulan Ini</span></button>
                    <button type="button" class="btn-quick-filter" onclick="filterTableQuick('has_nota', this)"><i class="bi bi-image text-success"></i><span>Ada Bukti Nota</span></button>
                </div>
                <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" id="filterPengeluaranLabel" style="display:none; font-size:.72rem;">
                    <i class="bi bi-funnel-fill"></i><span id="filterPengeluaranName">Filter aktif</span>
                </div>
            </div>
        </div>
        <div class="card-body p-2.5 p-md-3">
            <div class="pengeluaran-table-wrapper border rounded-3">
                <table class="table table-hover align-middle mb-0" id="pengeluaranTable" style="width:100%">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width:45px;"><i class="bi bi-hash"></i></th>
                            <th style="min-width: 120px;"><i class="bi bi-calendar3 me-1"></i>Tanggal</th>
                            <th style="min-width: 220px;"><i class="bi bi-card-text me-1"></i>Keterangan Belanja</th>
                            <th style="min-width: 140px;"><i class="bi bi-tags me-1"></i>Kategori</th>
                            <th style="min-width: 140px;"><i class="bi bi-cash me-1"></i>Nominal (Rp)</th>
                            <th style="min-width: 90px;"><i class="bi bi-paperclip me-1"></i>Bukti Nota</th>
                            <th style="min-width: 120px;"><i class="bi bi-person me-1"></i>Dicatat Oleh</th>
                            <th class="pe-3 text-center pengeluaran-sticky-action" style="min-width: 130px; width: 130px;"><i class="bi bi-tools me-1"></i>Aksi</th>
                        </tr>
                        <tr class="search-row bg-light border-bottom">
                            <th class="ps-3 py-1.5"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari tgl..." id="pgFilterTanggal" style="font-size:.72rem;"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari keterangan..." id="pgFilterKet" style="font-size:.72rem;"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari kategori..." id="pgFilterKat" style="font-size:.72rem;"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari nominal..." style="font-size:.72rem;"></th>
                            <th class="py-1.5"></th>
                            <th class="py-1.5"><input type="text" class="form-control form-control-sm" placeholder="Cari user..." style="font-size:.72rem;"></th>
                            <th class="pe-3 py-1.5 pengeluaran-sticky-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pengeluaranList as $idx => $pg): ?>
                        <tr>
                            <td class="ps-3 text-muted small fw-semibold"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size:.82rem;"><?= date('d M Y', strtotime($pg['tanggal'])) ?></div>
                                <div class="text-muted" style="font-size:.68rem;"><?= date('H:i', strtotime($pg['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark" style="font-size:.84rem; max-width:320px; white-space:pre-wrap;"><?= htmlspecialchars($pg['keterangan']) ?></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1 text-white shadow-xs" style="font-size:.72rem; background:<?= $kategoriColors[$pg['kategori']] ?? '#6c757d' ?>;">
                                    <i class="bi <?= $kategoriIcons[$pg['kategori']] ?? 'bi-tag' ?> me-1"></i><?= htmlspecialchars($pg['kategori']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-danger" style="font-size:.86rem;">-<?= $formatRupiah((float)$pg['nominal']) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($pg['foto_nota'])): ?>
                                    <?php 
                                        $notaUrl = str_starts_with($pg['foto_nota'], '/uploads/') ? $pg['foto_nota'] : API_URL . '/api/job-desk/pengeluaran/view-nota/' . $pg['id'];
                                        $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $pg['foto_nota']); 
                                    ?>
                                    <?php if ($isImg): ?>
                                        <img src="<?= htmlspecialchars($notaUrl) ?>" alt="Nota" 
                                             style="width:38px;height:38px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;cursor:pointer;transition:transform .18s;"
                                             onmouseover="this.style.transform='scale(1.15)'" 
                                             onmouseout="this.style.transform='scale(1)'"
                                             onclick="showNotaPreview('<?= htmlspecialchars($notaUrl) ?>')"
                                             title="Klik untuk melihat foto kuitansi">
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size:0.72rem;" title="Buka berkas PDF">
                                            <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>PDF
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small" style="font-size:0.72rem;"><em>Tanpa Nota</em></span>
                                <?php endif; ?>
                            </td>
                            <td><div class="text-muted small"><span class="badge bg-light text-secondary border rounded-pill px-2"><?= htmlspecialchars($pg['created_by'] ?? '-') ?></span></div></td>
                            <td class="pe-3 text-center pengeluaran-sticky-action">
                                <div class="d-flex gap-1.5 justify-content-center">
                                    <button class="btn btn-sm btn-outline-warning rounded-pill py-1 px-2.5 shadow-xs fw-semibold" title="Edit Transaksi" 
                                            onclick="editPengeluaran(<?= $pg['id'] ?>, '<?= htmlspecialchars(addslashes($pg['keterangan']), ENT_QUOTES) ?>', <?= (float)$pg['nominal'] ?>, '<?= htmlspecialchars(addslashes($pg['kategori'])) ?>', '<?= $pg['tanggal'] ?>', '<?= htmlspecialchars(addslashes($pg['foto_nota'] ?? '')) ?>')" style="font-size: 0.73rem;">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill py-1 px-2 shadow-xs" title="Hapus Transaksi" onclick="hapusPengeluaran(<?= $pg['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<!-- ===== MODAL NOTA PREVIEW ===== -->
<div class="modal fade" id="notaPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark">
            <div class="modal-header border-0 bg-dark text-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-image me-2 text-warning"></i>Preview Bukti Nota Transaksi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center bg-dark">
                <img id="notaPreviewImg" src="" alt="Nota" style="max-width:100%; max-height:75vh; object-fit:contain; border-radius: 8px;">
            </div>
            <div class="modal-footer border-0 bg-dark py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <a id="notaPreviewDownload" href="" target="_blank" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium shadow-sm">
                    <i class="bi bi-download me-1"></i>Unduh Berkas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL FORM PENGELUARAN ===== -->
<div class="modal fade" id="formPengeluaranModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold mb-0" id="formPengeluaranTitle"><i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPengeluaran" enctype="multipart/form-data">
                <div class="modal-body p-3 p-md-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                    <input type="hidden" id="pg_edit_id" value="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem;">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3" id="pg_tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem;">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="pg_kategori" required>
                            <?php foreach ($kategoris as $kat): ?>
                            <option value="<?= htmlspecialchars($kat['nama']) ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem;">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                            <input type="text" class="form-control rounded-end-3 font-monospace fw-bold fs-6" id="pg_nominal" required placeholder="Contoh: 150000"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatNominalPreview(this);">
                        </div>
                        <div id="pg_nominal_preview" class="small text-primary mt-1 fw-bold" style="font-size:0.8rem;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem;">Keterangan / Rincian Belanja <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" id="pg_keterangan" rows="3" required placeholder="Contoh: Beli kertas HVS 2 rim, biaya transport antar dokumen ke imigrasi..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem;">Foto Bukti Nota / Kuitansi (Opsional)</label>
                        <input type="file" class="form-control rounded-3" id="pg_foto_nota" accept="image/*,.pdf">
                        <div class="small text-muted mt-1" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1"></i>Format JPG, PNG, WebP, PDF (maksimal 5MB)</div>
                        <div id="pg_foto_preview" class="mt-2" style="display:none;">
                            <img id="pg_foto_preview_img" src="" style="max-height:120px; border-radius:8px; border:2px solid #e9ecef;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" id="btnSimpanPengeluaran">
                        <i class="bi bi-save me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL KELOLA KATEGORI ===== -->
<div class="modal fade" id="kategoriModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-info text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold mb-0 text-white"><i class="bi bi-tags me-2"></i>Kelola Kategori Pengeluaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                <div id="kategoriList">
                    <?php foreach ($kategoris as $k): ?>
                    <div class="kategori-manage-item" data-id="<?= $k['id'] ?>">
                        <div class="color-dot" style="background: <?= htmlspecialchars($k['warna']) ?>;"></div>
                        <i class="bi <?= htmlspecialchars($k['icon']) ?> text-muted"></i>
                        <span class="fw-semibold flex-grow-1 text-dark small"><?= htmlspecialchars($k['nama']) ?></span>
                        <button class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill border-0" title="Hapus Kategori" onclick="hapusKategori(<?= $k['id'] ?>, '<?= htmlspecialchars(addslashes($k['nama'])) ?>')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr class="my-3 opacity-25">
                <div class="d-flex gap-2 align-items-end flex-wrap">
                    <div class="flex-grow-1" style="min-width: 160px;">
                        <label class="form-label small fw-bold text-muted mb-1">Tambah Kategori Baru</label>
                        <input type="text" class="form-control form-control-sm rounded-3" id="newKategoriNama" placeholder="Nama kategori...">
                    </div>
                    <div style="width: 50px;">
                        <label class="form-label small fw-bold text-muted mb-1">Warna</label>
                        <input type="color" class="form-control form-control-sm form-control-color p-0 border-0 rounded-3" id="newKategoriWarna" value="#0d6efd" style="height:31px;">
                    </div>
                    <button class="btn btn-sm btn-info text-white rounded-pill px-3 py-1 fw-semibold" onclick="tambahKategori()">
                        <i class="bi bi-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light p-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4 w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/chart.umd.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>

<script>
let pengeluaranChart;
let _currentChartType = 'bar';

const chartLabels = <?= $bulananLabels ?>;
const chartPengeluaran = <?= $bulananPengeluaran ?>;
const chartPemasukan = <?= $bulananPemasukan ?>;
const instansiInfo = <?= json_encode($instansiInfo ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    buildPengeluaranChart('bar');

    // DataTables Initialization
    const tableEl = $('#pengeluaranTable');
    if (tableEl.length) {
        let dt = window.pgTableInstance = tableEl.DataTable({
            order: [[1, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "Semua"]],
            language: { 
                search: "Cari:", 
                lengthMenu: "Tampil _MENU_ data", 
                info: "_START_–_END_ dari _TOTAL_ transaksi", 
                paginate: { first: "«", last: "»", next: "›", previous: "‹" } 
            },
            dom: "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            columnDefs: [{ orderable: false, targets: [5, 7] }]
        });

        // Search per column
        dt.columns().every(function() {
            let that = this;
            $('input', this.header()).on('keyup change clear', function() {
                if (that.search() !== this.value) that.search(this.value).draw();
            });
        });
        $('.search-row input').on('click', function(e) { e.stopPropagation(); });
    }
});

// =============================================
// CHART.JS DUAL DATASET (PEMASUKAN VS PENGELUARAN)
// =============================================
function buildPengeluaranChart(type) {
    _currentChartType = type;
    const canvas = document.getElementById('pengeluaranChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    if (pengeluaranChart) pengeluaranChart.destroy();

    document.getElementById('chartTypeBar')?.classList.toggle('active', type === 'bar');
    document.getElementById('chartTypeLine')?.classList.toggle('active', type === 'line');

    pengeluaranChart = new Chart(ctx, {
        type: type === 'line' ? 'line' : 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Pemasukan Operasional (Rp)',
                    data: chartPemasukan,
                    backgroundColor: type === 'line' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: type === 'line' ? 2.5 : 0,
                    borderRadius: type === 'bar' ? 6 : 0,
                    pointBackgroundColor: '#10b981',
                    pointRadius: type === 'line' ? 4 : 0,
                    fill: type === 'line',
                    tension: 0.35,
                },
                {
                    label: 'Pengeluaran Operasional (Rp)',
                    data: chartPengeluaran,
                    backgroundColor: type === 'line' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(239, 68, 68, 0.75)',
                    borderColor: '#ef4444',
                    borderWidth: type === 'line' ? 2.5 : 0,
                    borderRadius: type === 'bar' ? 6 : 0,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: type === 'line' ? 4 : 0,
                    fill: type === 'line',
                    tension: 0.35,
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
                    labels: { usePointStyle: true, boxWidth: 8, padding: 15, font: { family: 'Inter, sans-serif', size: 12 }, color: '#475569' }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#0f172a',
                    bodyColor: '#334155',
                    borderColor: '#cbd5e1',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4,
                    callbacks: {
                        label: function(ctx) {
                            let label = ctx.dataset.label || '';
                            if (label) label += ': ';
                            if (ctx.parsed.y !== null) label += 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000).toFixed(v%1000000===0?0:1) + ' Jt';
                            if (v >= 1000) return (v/1000).toFixed(0) + ' Rb';
                            return v;
                        }
                    }
                }
            }
        }
    });
}

window.switchPengeluaranChart = function(type) { buildPengeluaranChart(type); };

// =============================================
// FILTER HANDLERS
// =============================================
function filterTableQuick(type, btnEl) {
    $('.btn-quick-filter').removeClass('active');
    if (btnEl) $(btnEl).addClass('active');

    const filterLabel = document.getElementById('filterPengeluaranLabel');
    const filterName = document.getElementById('filterPengeluaranName');

    if (window.pgTableInstance) {
        if (type === 'all') {
            window.pgTableInstance.search('').columns().search('').draw();
            filterLabel.style.display = 'none';
        } else if (type === 'this_month') {
            const currentMonthYear = new Date().toLocaleString('id-ID', { month: 'short', year: 'numeric' });
            window.pgTableInstance.column(1).search(currentMonthYear).draw();
            filterLabel.style.display = '';
            filterName.textContent = 'Bulan Ini (' + currentMonthYear + ')';
        } else if (type === 'has_nota') {
            window.pgTableInstance.column(5).search('img|pdf', true, false).draw();
            filterLabel.style.display = '';
            filterName.textContent = 'Ada Bukti Nota';
        }
    }
}

function filterByCategory(kat, el) {
    $('.kategori-chip').removeClass('active');
    if (el) $(el).addClass('active');

    const filterLabel = document.getElementById('filterPengeluaranLabel');
    const filterName = document.getElementById('filterPengeluaranName');

    if (window.pgTableInstance) {
        window.pgTableInstance.column(3).search(kat).draw();
        filterLabel.style.display = '';
        filterName.textContent = 'Kategori: ' + kat;
    }
}

function resetCategoryFilter() {
    $('.kategori-chip').removeClass('active');
    const filterLabel = document.getElementById('filterPengeluaranLabel');
    if (window.pgTableInstance) {
        window.pgTableInstance.column(3).search('').draw();
        filterLabel.style.display = 'none';
    }
}

// =============================================
// FORM & MODAL ACTIONS
// =============================================
function formatNominalPreview(el) {
    const val = parseInt(el.value) || 0;
    document.getElementById('pg_nominal_preview').textContent = val > 0 ? 'Terbilang: Rp ' + val.toLocaleString('id-ID') : '';
}

function showNotaPreview(src) {
    document.getElementById('notaPreviewImg').src = src;
    document.getElementById('notaPreviewDownload').href = src;
    new bootstrap.Modal(document.getElementById('notaPreviewModal')).show();
}

function showFormPengeluaran() {
    document.getElementById('pg_edit_id').value = '';
    document.getElementById('formPengeluaranTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran Baru';
    document.getElementById('formPengeluaran').reset();
    document.getElementById('pg_tanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('pg_nominal_preview').textContent = '';
    document.getElementById('pg_foto_preview').style.display = 'none';
    new bootstrap.Modal(document.getElementById('formPengeluaranModal')).show();
}

function editPengeluaran(id, keterangan, nominal, kategori, tanggal, fotoNota) {
    document.getElementById('pg_edit_id').value = id;
    document.getElementById('formPengeluaranTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Pengeluaran #' + id;
    document.getElementById('pg_tanggal').value = tanggal;
    document.getElementById('pg_kategori').value = kategori;
    document.getElementById('pg_nominal').value = nominal;
    document.getElementById('pg_keterangan').value = keterangan;
    formatNominalPreview(document.getElementById('pg_nominal'));
    if (fotoNota && /\.(jpg|jpeg|png|webp)$/i.test(fotoNota)) {
        document.getElementById('pg_foto_preview').style.display = 'block';
        document.getElementById('pg_foto_preview_img').src = fotoNota.startsWith('/uploads/') ? fotoNota : '<?= API_URL ?>/api/job-desk/pengeluaran/view-nota/' + id;
    } else {
        document.getElementById('pg_foto_preview').style.display = 'none';
    }
    new bootstrap.Modal(document.getElementById('formPengeluaranModal')).show();
}

document.getElementById('pg_foto_nota')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('pg_foto_preview').style.display = 'block'; document.getElementById('pg_foto_preview_img').src = e.target.result; };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('pg_foto_preview').style.display = 'none';
    }
});

document.getElementById('formPengeluaran')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const editId = document.getElementById('pg_edit_id').value;
    const isEdit = editId !== '';
    const url = isEdit ? '<?= API_URL ?>/api/job-desk/pengeluaran/' + editId + '/update' : '<?= API_URL ?>/api/job-desk/pengeluaran/create';
    const fd = new FormData();
    fd.append('tanggal', document.getElementById('pg_tanggal').value);
    fd.append('kategori', document.getElementById('pg_kategori').value);
    fd.append('nominal', document.getElementById('pg_nominal').value);
    fd.append('keterangan', document.getElementById('pg_keterangan').value);
    const fotoFile = document.getElementById('pg_foto_nota').files[0];
    if (fotoFile) {
        if (fotoFile.size > 5 * 1024 * 1024) { Swal.fire('File Terlalu Besar', 'Maksimal ukuran foto 5MB.', 'warning'); return; }
        fd.append('foto_nota', fotoFile);
    }
    const btn = document.getElementById('btnSimpanPengeluaran');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch(url, { method: 'POST', headers: csrfToken ? { 'X-CSRF-Token': csrfToken } : {}, body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) { 
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false }).then(() => location.reload()); 
        } else { 
            Swal.fire('Gagal', res.message || 'Error', 'error'); 
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Transaksi'; 
        }
    }).catch(() => { 
        Swal.fire('Error', 'Koneksi gagal.', 'error'); 
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Transaksi'; 
    });
});

function hapusPengeluaran(id) {
    Swal.fire({ 
        title: 'Hapus Transaksi?', 
        text: 'Data pengeluaran ini akan dihapus permanen.', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: 'Ya, Hapus!', 
        confirmButtonColor: '#dc3545', 
        cancelButtonText: 'Batal',
        customClass: { popup: 'shadow-lg rounded-4' } 
    }).then(result => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/job-desk/pengeluaran/' + id + '/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken || '', 'Content-Type': 'application/json' } })
            .then(r => r.json()).then(res => {
                if (res.success) { Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1200, showConfirmButton: false }).then(() => location.reload()); }
                else Swal.fire('Gagal', res.message, 'error');
            }).catch(() => Swal.fire('Error', 'Koneksi gagal.', 'error'));
        }
    });
}

function exportPengeluaran() {
    const rows = [['No', 'Tanggal', 'Kategori', 'Keterangan', 'Nominal (Rp)', 'Dicatat Oleh']];
    document.querySelectorAll('#pengeluaranTable tbody tr').forEach((tr, idx) => {
        const cells = tr.querySelectorAll('td');
        if (cells.length >= 7) {
            rows.push([
                idx + 1,
                cells[1]?.textContent.trim().split('\n')[0] || '',
                cells[3]?.textContent.trim() || '',
                cells[2]?.textContent.trim() || '',
                cells[4]?.textContent.trim().replace(/[^0-9]/g, '') || '',
                cells[6]?.textContent.trim() || ''
            ]);
        }
    });
    const csv = rows.map(r => r.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
    const blob = new Blob(['\uFEFF'+csv], { type:'text/csv;charset=utf-8;' });
    const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = 'Laporan_Pengeluaran_' + new Date().toISOString().slice(0,10) + '.csv'; link.click();
}

// =============================================
// PRINT LAPORAN PENGELUARAN LENGKAP
// =============================================
function printLaporanPengeluaran() {
    let rowsHtml = '';
    let totalNom = 0;
    let idx = 1;

    document.querySelectorAll('#pengeluaranTable tbody tr').forEach((tr) => {
        const cells = tr.querySelectorAll('td');
        if (cells.length >= 7) {
            const tgl = cells[1]?.textContent.trim().split('\n')[0] || '';
            const ket = cells[2]?.textContent.trim() || '';
            const kat = cells[3]?.textContent.trim() || '';
            const nomStr = cells[4]?.textContent.trim().replace(/[^0-9]/g, '') || '0';
            const nomVal = parseInt(nomStr) || 0;
            const user = cells[6]?.textContent.trim() || '';
            totalNom += nomVal;

            rowsHtml += `
                <tr>
                    <td style="text-align:center; padding: 6px; border: 1px solid #000;">${idx++}</td>
                    <td style="padding: 6px; border: 1px solid #000; white-space:nowrap;">${tgl}</td>
                    <td style="padding: 6px; border: 1px solid #000;"><strong>${ket}</strong></td>
                    <td style="padding: 6px; border: 1px solid #000;">${kat}</td>
                    <td style="padding: 6px; border: 1px solid #000; text-align:right;">Rp ${nomVal.toLocaleString('id-ID')}</td>
                    <td style="padding: 6px; border: 1px solid #000; text-align:center;">${user}</td>
                </tr>
            `;
        }
    });

    const instansiName = instansiInfo?.nama_instansi || 'BIROKRASI OPERASIONAL';
    const instansiAlamat = instansiInfo?.alamat || '';

    const html = `
        <div style="font-family: 'Times New Roman', Times, serif; color: #000; padding: 10px;">
            <div style="text-align: center; border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 15px;">
                <h3 style="margin: 0; font-size: 18pt; text-transform: uppercase; letter-spacing: 1px;">${instansiName}</h3>
                <div style="font-size: 10pt; margin-top: 3px;">${instansiAlamat}</div>
                <h4 style="margin: 10px 0 0 0; font-size: 14pt; text-decoration: underline;">LAPORAN PENGELUARAN OPERASIONAL</h4>
                <div style="font-size: 10pt; margin-top: 2px;">Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})}</div>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #000; padding: 8px; width: 35px; text-align: center;">NO</th>
                        <th style="border: 1px solid #000; padding: 8px; width: 100px;">TANGGAL</th>
                        <th style="border: 1px solid #000; padding: 8px;">KETERANGAN PENGELUARAN</th>
                        <th style="border: 1px solid #000; padding: 8px; width: 130px;">KATEGORI</th>
                        <th style="border: 1px solid #000; padding: 8px; width: 120px; text-align: right;">NOMINAL (RP)</th>
                        <th style="border: 1px solid #000; padding: 8px; width: 100px; text-align: center;">PETUGAS</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td colspan="4" style="border: 1px solid #000; padding: 8px; text-align: right;">TOTAL PENGELUARAN :</td>
                        <td style="border: 1px solid #000; padding: 8px; text-align: right;">Rp ${totalNom.toLocaleString('id-ID')}</td>
                        <td style="border: 1px solid #000; padding: 8px;"></td>
                    </tr>
                </tfoot>
            </table>

            <div style="display: flex; justify-content: space-between; margin-top: 40px; font-size: 11pt;">
                <div style="text-align: center; width: 220px;">
                    <div>Mengetahui,</div>
                    <div style="font-weight: bold;">Pimpinan Instansi</div>
                    <div style="height: 60px;"></div>
                    <div style="border-bottom: 1px solid #000; font-weight: bold;">( ..................................... )</div>
                </div>
                <div style="text-align: center; width: 220px;">
                    <div>Petugas Keuangan,</div>
                    <div style="font-weight: bold;">Staff Administrasi</div>
                    <div style="height: 60px;"></div>
                    <div style="border-bottom: 1px solid #000; font-weight: bold;">( <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Petugas') ?> )</div>
                </div>
            </div>
        </div>
    `;

    const printArea = document.getElementById('printArea');
    printArea.innerHTML = html;
    window.print();
}

// =============================================
// KATEGORI MANAGEMENT
// =============================================
function showKategoriManager() {
    new bootstrap.Modal(document.getElementById('kategoriModal')).show();
}

function tambahKategori() {
    const nama = document.getElementById('newKategoriNama').value.trim();
    const warna = document.getElementById('newKategoriWarna').value;
    if (!nama) { Swal.fire('Oops', 'Nama kategori wajib diisi.', 'warning'); return; }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/job-desk/pengeluaran/kategori/create', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken || '', 'Content-Type': 'application/json' },
        body: JSON.stringify({ nama, warna })
    }).then(r => r.json()).then(res => {
        if (res.success) { Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Kategori ditambahkan.', timer: 1500, showConfirmButton: false }).then(() => location.reload()); }
        else Swal.fire('Gagal', res.message, 'error');
    }).catch(() => Swal.fire('Error', 'Koneksi gagal.', 'error'));
}

function hapusKategori(id, nama) {
    Swal.fire({ 
        title: 'Hapus Kategori?', 
        html: 'Kategori <strong>' + nama + '</strong> akan dihapus.', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: 'Ya, Hapus!', 
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/job-desk/pengeluaran/kategori/' + id + '/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken || '', 'Content-Type': 'application/json' } })
            .then(r => r.json()).then(res => {
                if (res.success) { Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1500, showConfirmButton: false }).then(() => location.reload()); }
                else Swal.fire('Gagal', res.message, 'error');
            }).catch(() => Swal.fire('Error', 'Koneksi gagal.', 'error'));
        }
    });
}
</script>
