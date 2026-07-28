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
.card-keuangan { transition: transform .2s, box-shadow .2s; cursor: default; }
.card-keuangan:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.12) !important; }
.card-keuangan.clickable { cursor: pointer; }

/* ===== FILTER SUMMARY - MATCH EXISTING UI STYLE ===== */
.filter-jobdesk-section {
    background: white;
    border: 1px solid #e8ecf0;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
.filter-jobdesk-section .section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.summary-stat-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 14px 16px;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}
.summary-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    border-radius: 2px 0 0 2px;
}
.summary-stat-card.accent-blue::before { background: #0d6efd; }
.summary-stat-card.accent-orange::before { background: #fd7e14; }
.summary-stat-card.accent-danger::before { background: #dc3545; }
.summary-stat-card.accent-teal::before { background: #20c997; }
.summary-stat-card.accent-red::before { background: #dc3545; }
.summary-stat-card.accent-green::before { background: #198754; }
.summary-stat-card:hover { background: #f0f4ff; border-color: #c9d4f7; }
.summary-stat-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9fa8b3; margin-bottom: 4px; }
.summary-stat-value { font-size: .95rem; font-weight: 700; color: #1a2035; }
.summary-stat-sub { font-size: .65rem; color: #9fa8b3; margin-top: 3px; }

/* ===== CICILAN PROGRESS ===== */
.progress-cicilan { height: 7px; border-radius: 4px; background: #e9ecef; }
.badge-cicil { font-size: .63rem; }

/* ===== DRAG SELECT HINT ===== */
.drag-select-hint { font-size: .68rem; color: #9fa8b3; font-style: italic; }
</style>

<!-- Hidden Print Areas -->
<div id="printKwitansiArea" style="display:none;"></div>
<div id="printRekapArea" style="display:none;"></div>

<div class="container-fluid px-0">

    <!-- === HEADER === -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Dashboard Operasional Birokrasi</h4>
            <p class="text-muted small mb-0">Analitik finansial terpadu dari pengajuan proses Job Desk</p>
        </div>
        <a href="<?= API_URL ?>/job-desk" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Ke Job Desk
        </a>
    </div>

    <!-- === FINANCIAL CARDS === -->
    <div class="row g-3 mb-4">
        <div class="col-lg col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-keuangan" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); color:white;">
                <div class="card-body p-3 position-relative">
                    <div class="mb-1 opacity-75 fw-semibold small text-uppercase" style="letter-spacing:.06em; font-size:.65rem;">Pemasukan Operasional</div>
                    <h4 class="fw-bolder mb-1" style="font-size:1.15rem;"><?= $formatRupiah($totalSurplus) ?></h4>
                    <div class="small opacity-75" style="font-size:.65rem;"><i class="bi bi-info-circle me-1"></i>Dari transaksi lunas</div>
                    <i class="bi bi-wallet2 position-absolute opacity-10" style="font-size:4.5rem;right:-10px;bottom:-15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-keuangan clickable"
                 style="background: linear-gradient(135deg, #fd7e14 0%, #d9640b 100%); color:white;"
                 onclick="new bootstrap.Modal(document.getElementById('piutangModal')).show()">
                <div class="card-body p-3 position-relative">
                    <div class="mb-1 opacity-75 fw-semibold small text-uppercase" style="letter-spacing:.06em; font-size:.65rem;">Piutang Santri <i class="bi bi-box-arrow-up-right ms-1"></i></div>
                    <h4 class="fw-bolder mb-1" style="font-size:1.15rem;"><?= $formatRupiah($piutangSantri) ?></h4>
                    <div class="small opacity-75" style="font-size:.65rem;"><i class="bi bi-info-circle me-1"></i>Klik untuk bayar / cicil</div>
                    <i class="bi bi-person-down position-absolute opacity-10" style="font-size:4.5rem;right:-10px;bottom:-15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-keuangan clickable"
                 style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color:white;"
                 onclick="new bootstrap.Modal(document.getElementById('hutangInstansiModal')).show()">
                <div class="card-body p-3 position-relative">
                    <div class="mb-1 opacity-75 fw-semibold small text-uppercase" style="letter-spacing:.06em; font-size:.65rem;">Hutang Instansi <i class="bi bi-box-arrow-up-right ms-1"></i></div>
                    <h4 class="fw-bolder mb-1" style="font-size:1.15rem;"><?= $formatRupiah($hutangInstansi) ?></h4>
                    <div class="small opacity-75" style="font-size:.65rem;"><i class="bi bi-info-circle me-1"></i>Klik untuk bayar</div>
                    <i class="bi bi-bank2 position-absolute opacity-10" style="font-size:4.5rem;right:-10px;bottom:-15px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- === FILTER KUMULATIF PER JOBDESK (UI matching existing style) === -->
    <div class="filter-jobdesk-section mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="section-title">
                <i class="bi bi-funnel-fill text-primary"></i>
                Filter &amp; Summary Per Jenis Jobdesk
            </div>
            <div class="d-flex gap-2 align-items-center">
                <?php if (!empty($semuaInstansi)): ?>
                <select id="printInstansiSelect" class="form-select form-select-sm rounded-pill" style="width: auto; font-size:.7rem;">
                    <option value="">-- Gunakan Kop Default --</option>
                    <?php foreach ($semuaInstansi as $inst): ?>
                        <option value="<?= htmlspecialchars(json_encode($inst), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($inst['nama_instansi']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="printRekapSantri()">
                    <i class="bi bi-printer me-1"></i>Print Rekap Piutang
                </button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetFilterJobdesk()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                </button>
            </div>
        </div>
        <div class="row g-3 mb-4 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Jenis Proses / Jobdesk</label>
                <select class="form-select" id="filterJobdesk" onchange="applyFilterJobdesk()">
                    <option value="">— Semua Jenis Proses —</option>
                    <?php foreach ($summaryPerJobdesk as $s): ?>
                        <option value="<?= $s['process_id'] ?>" data-info='<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>'>
                            <?= htmlspecialchars($s['nama_proses']) ?> (<?= $s['jumlah_kasus'] ?> kasus)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Pilih jenis proses untuk melihat rekapitulasi kumulatif dan memfilter tabel transaksi di bawah.</div>
            </div>
        </div>
        <!-- Summary Cards -->
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
            <div class="col-6 col-md-2">
                <div class="bg-primary text-white rounded-3 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center shadow-sm" style="min-height:70px;">
                    <div class="opacity-75 fw-semibold mb-1" style="font-size:.6rem;text-transform:uppercase;letter-spacing:.5px;">Total Kasus</div>
                    <div class="fw-bold mb-0 lh-1" id="sv_kasus" style="font-size:1.1rem;"><?= $allKasus ?></div>
                    <div class="opacity-75 mt-1" style="font-size:.6rem;">job desk</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="bg-info text-white rounded-3 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center shadow-sm">
                    <div class="opacity-75 fw-semibold mb-1" style="font-size:.6rem;text-transform:uppercase;letter-spacing:.5px;">Tagihan Santri</div>
                    <div class="fw-bold mb-0 lh-1" id="sv_santri" style="font-size:.85rem;"><?= $formatRupiah($allSantriTotal) ?></div>
                    <div class="opacity-75 mt-1" id="sv_lunas_santri" style="font-size:.6rem;">Lunas: <?= $formatRupiah($allLunasSantri) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="bg-danger text-white rounded-3 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center shadow-sm">
                    <div class="opacity-75 fw-semibold mb-1" style="font-size:.6rem;text-transform:uppercase;letter-spacing:.5px;">Piutang Santri</div>
                    <div class="fw-bold mb-0 lh-1" id="sv_belum_santri" style="font-size:.85rem;"><?= $formatRupiah($allBelumSantri) ?></div>
                    <div class="opacity-75 mt-1" style="font-size:.6rem;">belum dibayar</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="bg-secondary text-white rounded-3 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center shadow-sm">
                    <div class="opacity-75 fw-semibold mb-1" style="font-size:.6rem;text-transform:uppercase;letter-spacing:.5px;">Ke Instansi</div>
                    <div class="fw-bold mb-0 lh-1" id="sv_instansi" style="font-size:.85rem;"><?= $formatRupiah($allInstansi) ?></div>
                    <div class="opacity-75 mt-1" id="sv_lunas_instansi" style="font-size:.6rem;">Lunas: <?= $formatRupiah($allLunasInst) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="bg-warning text-dark rounded-3 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center shadow-sm">
                    <div class="opacity-75 fw-semibold mb-1" style="font-size:.6rem;text-transform:uppercase;letter-spacing:.5px;">Hutang Instansi</div>
                    <div class="fw-bold mb-0 lh-1" id="sv_belum_instansi" style="font-size:.85rem;"><?= $formatRupiah($allBelumInst) ?></div>
                    <div class="opacity-75 mt-1" style="font-size:.6rem;">belum disetor</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="bg-success text-white rounded-3 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center shadow-sm">
                    <div class="opacity-75 fw-semibold mb-1" style="font-size:.6rem;text-transform:uppercase;letter-spacing:.5px;">Uang Opr.</div>
                    <div class="fw-bold mb-0 lh-1" id="sv_selisih" style="font-size:.85rem;"><?= $formatRupiah($allSelisih) ?></div>
                    <div class="opacity-75 mt-1" style="font-size:.6rem;">masuk kas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- === CHART (Modern, Light Mode) === -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Tren Uang Operasional &amp; Penerimaan</h6>
                    <div class="text-muted small mt-1">12 bulan terakhir — data real-time</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 active" id="chartTypeLine" onclick="switchChart('line')">
                        <i class="bi bi-graph-up me-1"></i>Garis
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="chartTypeBar" onclick="switchChart('bar')">
                        <i class="bi bi-bar-chart me-1"></i>Batang
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            <canvas id="keuanganChart" height="70"></canvas>
        </div>
    </div>

    <!-- === TRANSACTION TABLE (with filter from jobdesk) === -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-primary me-2"></i>Daftar Lengkap Transaksi Job Desk</h6>
            <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2" id="filterActiveLabel" style="display:none;">
                <i class="bi bi-funnel-fill me-1"></i><span id="filterActiveName">Filter aktif</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="txTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 border-0">Data Santri</th>
                            <th class="border-0">Proses Job Desk</th>
                            <th class="border-0">Pembayaran Santri</th>
                            <th class="border-0">Pembayaran Instansi</th>
                            <th class="border-0">Uang Operasional</th>
                            <th class="border-0">Tanggal Update</th>
                            <th class="pe-3 border-0 text-end">Aksi</th>
                        </tr>
                        <tr class="search-row bg-light border-bottom">
                            <th class="ps-3 py-2"><input type="text" class="form-control form-control-sm" placeholder="Cari santri..."></th>
                            <th class="py-2"><input type="text" class="form-control form-control-sm" placeholder="Cari proses..." id="txFilterProses"></th>
                            <th class="py-2"><input type="text" class="form-control form-control-sm" placeholder="Cari (cth: lunas)..."></th>
                            <th class="py-2"><input type="text" class="form-control form-control-sm" placeholder="Cari (cth: belum)..."></th>
                            <th class="py-2"></th>
                            <th class="py-2"><input type="text" class="form-control form-control-sm" placeholder="Cari tanggal..."></th>
                            <th class="pe-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr class="<?= ($tx['status_bayar_santri'] === 'belum') ? 'table-warning' : '' ?>">
                            <td class="ps-3">
                                <div class="fw-bold text-dark" style="font-size:.85rem;"><?= htmlspecialchars($tx['nama']) ?></div>
                                <div class="text-muted small">KDS: <?= htmlspecialchars($tx['kds']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1"><?= htmlspecialchars($tx['nama_proses']) ?></span>
                                <div class="text-muted mt-1" style="font-size:.7rem;">Case #<?= $tx['case_id'] ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size:.8rem;"><?= $formatRupiah((float)$tx['nominal_santri']) ?></div>
                                <?php if ($tx['status_bayar_santri'] === 'lunas'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1" style="font-size:.62rem;"><i class="bi bi-check2"></i> Lunas <?= $tx['tgl_bayar_santri'] ? '('.date('d/m/y', strtotime($tx['tgl_bayar_santri'])).')' : '' ?></span>
                                <?php else: ?>
                                    <?php $totC = (float)($tx['total_cicilan'] ?? 0); $jmlC = (int)($tx['jumlah_cicilan'] ?? 0); ?>
                                    <?php if ($jmlC > 0): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill mt-1 badge-cicil"><i class="bi bi-arrow-repeat me-1"></i>Cicilan <?= $jmlC ?>x (<?= $formatRupiah($totC) ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill mt-1 fw-bold" style="font-size:.62rem;">Belum Bayar</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size:.8rem;"><?= $formatRupiah((float)$tx['nominal_instansi']) ?></div>
                                <?php if ($tx['status_bayar_instansi'] === 'lunas'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1" style="font-size:.62rem;"><i class="bi bi-check2"></i> Lunas <?= $tx['tgl_bayar_instansi'] ? '('.date('d/m/y', strtotime($tx['tgl_bayar_instansi'])).')' : '' ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mt-1" style="font-size:.62rem;">Belum Dibayar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $isLunasSemua = ($tx['status_bayar_santri']==='lunas' && $tx['status_bayar_instansi']==='lunas');
                                $surplus = (float)$tx['selisih_operasional'];
                                ?>
                                <div class="fw-bold <?= $isLunasSemua ? 'text-success' : 'text-muted' ?>" style="font-size:.85rem;">
                                    <?= $isLunasSemua ? '+' : '' ?><?= $formatRupiah($surplus) ?>
                                </div>
                                <?php if (!$isLunasSemua): ?><div class="text-muted" style="font-size:.63rem;">(Belum valid)</div><?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:.78rem;"><?= date('d M Y H:i', strtotime($tx['updated_at'])) ?></td>
                            <td class="pe-3 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i> Aksi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: .85rem;">
                                        <li><a class="dropdown-item py-2" href="<?= API_URL ?>/job-desk/<?= $tx['case_id'] ?>"><i class="bi bi-eye text-primary me-2"></i>Lihat Detail</a></li>
                                        <li><button class="dropdown-item py-2" onclick="printKwitansiRow(<?= $tx['case_id'] ?>, '<?= htmlspecialchars(addslashes($tx['nama'])) ?>', '<?= htmlspecialchars(addslashes($tx['kds'])) ?>', '<?= htmlspecialchars(addslashes($tx['nama_proses'])) ?>', <?= (float)$tx['nominal_santri'] ?>, <?= (float)($tx['total_cicilan'] ?? 0) ?>)"><i class="bi bi-receipt text-secondary me-2"></i>Cetak Kwitansi</button></li>
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
