<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $santris
 * @var int $total
 * @var string $search
 * @var UrlGeneratorInterface $urlGenerator
 */
$this->setTitle('Inaktif Data | Sistem Informasi');
$isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';

// Hitung metrik statistik ringkasan
$totalInaktif = count($santris);
$pondokStats = [];
$totalAdaPaspor = 0;
$totalPindahan = 0;
$myKep = $_SESSION['def_kepengurusan'] ?? '';

foreach ($santris as $s) {
    $p = trim((string)($s['pondok'] ?? ''));
    if ($p === '') {
        $p = 'Lainnya / Tanpa Pondok';
    }
    $pondokStats[$p] = ($pondokStats[$p] ?? 0) + 1;
    
    if (!empty($s['no_paspor'])) {
        $totalAdaPaspor++;
    }
    $sKep = trim((string)($s['kepengurusan'] ?? ''));
    if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0) {
        $totalPindahan++;
    }
}
arsort($pondokStats);
$totalPondok = count($pondokStats);
$santrisJson = json_encode($santris, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>

<!-- Offline Chart.js -->
<script src="<?= ASSET_URL ?>/assets/offline/js/chart.umd.min.js"></script>

<style>
/* Modern Responsive Styling for Inaktif Data */
:root {
    --inaktif-danger: #ef4444;
    --inaktif-danger-dark: #dc2626;
    --inaktif-primary: #3b82f6;
    --inaktif-success: #10b981;
    --inaktif-warning: #f59e0b;
}

.inaktif-stat-card {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    background: #ffffff;
}
.inaktif-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}
.inaktif-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

/* Segmented Navigation Tabs for Modal */
.inaktif-nav-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 6px;
    background: #f1f5f9;
    border-radius: 12px;
    border: none;
}
.inaktif-nav-tabs .nav-link {
    white-space: nowrap;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 6px 12px;
    color: #64748b;
    border: none !important;
    background: transparent;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}
.inaktif-nav-tabs .nav-link:hover:not(.active) {
    background: rgba(255, 255, 255, 0.6);
    color: #1e293b;
}
.inaktif-nav-tabs .nav-link.active {
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Table Wrapper & Sticky Columns */
.inaktif-table-wrapper {
    max-height: calc(100vh - 270px);
    min-height: 280px;
    overflow: auto;
    border-radius: 12px;
    position: relative;
    -webkit-overflow-scrolling: touch;
}
.inaktif-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.inaktif-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.inaktif-table-wrapper table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin-bottom: 0;
}
.inaktif-table-wrapper thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
}
.inaktif-table-wrapper thead tr:nth-child(1) th {
    position: sticky;
    top: 0;
    z-index: 10;
}
.inaktif-table-wrapper thead tr:nth-child(2) th {
    position: sticky;
    top: 41px;
    z-index: 10;
    padding: 6px 8px;
    background: #f1f5f9 !important;
}

/* ONLY Action column is sticky on the right */
.inaktif-sticky-action {
    position: sticky;
    right: 0;
    z-index: 8;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.inaktif-table-wrapper thead th.inaktif-sticky-action {
    z-index: 12;
    background-color: #f8fafc !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.inaktif-table-wrapper tbody td {
    padding: 10px 12px;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.inaktif-table-wrapper tbody tr:hover td {
    background-color: #f8fafc;
}
.inaktif-table-wrapper tbody tr:hover td.inaktif-sticky-action {
    background-color: #f8fafc !important;
}

/* Column Search Input */
.column-search {
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    padding: 4px 8px !important;
    font-size: 0.78rem !important;
    background: #ffffff !important;
    min-width: 90px;
    transition: all 0.2s ease;
}
.column-search:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
}

/* Action Button Group */
.inaktif-action-group {
    display: flex;
    flex-wrap: nowrap;
    gap: 4px;
    justify-content: center;
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
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;
        gap: 8px;
    }
    .inaktif-stat-card .card-body {
        padding: 0.85rem !important;
    }
    .inaktif-stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.15rem;
    }
    .inaktif-stat-number {
        font-size: 1.35rem !important;
    }
    .inaktif-table-wrapper {
        max-height: 52vh;
    }
    .inaktif-modal-col {
        margin-bottom: 12px;
    }
}
@media (max-width: 575.98px) {
    .inaktif-search-form {
        flex-direction: column;
    }
    .inaktif-search-form .col-md-5,
    .inaktif-search-form .col-md-2 {
        width: 100% !important;
    }
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-3 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 46px; height: 46px; background: rgba(239, 68, 68, 0.12);">
            <i class="bi bi-person-x-fill fs-4 text-danger"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Data Santri Inaktif</h4>
            <div class="text-muted small fw-medium mt-0.5">
                Daftar arsip santri tidak aktif &bull; Total: <strong class="text-danger"><?= $total ?></strong> santri
            </div>
        </div>
    </div>
    
    <!-- Mass Action Controls (Aktifkan Massal & Hapus Massal) -->
    <div class="page-header-controls d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm d-none d-inline-flex align-items-center gap-1.5" id="btnBulkReactivate" onclick="bulkReaktifkan()">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Aktifkan Terpilih (<span id="countSelectedReactivate">0</span>)</span>
        </button>
        <button class="btn btn-sm btn-danger rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm d-none d-inline-flex align-items-center gap-1.5" id="btnBulkDelete" onclick="bulkHapus()">
            <i class="bi bi-trash-fill"></i>
            <span>Hapus Terpilih (<span id="countSelectedDelete">0</span>)</span>
        </button>
    </div>
</div>

<!-- Stat Metric Cards -->
<div class="row g-3 mb-3">
    <!-- Card Total Inaktif -->
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-person-x-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Total Inaktif</div>
                    <div class="h4 mb-0 fw-bold text-danger inaktif-stat-number"><?= $totalInaktif ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Pondok Terdampak -->
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-building-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Pondok Terdampak</div>
                    <div class="h4 mb-0 fw-bold text-primary inaktif-stat-number"><?= $totalPondok ?> <span class="text-muted fw-normal" style="font-size:.75rem;">pondok</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Dokumen Paspor -->
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-passport"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Ada Data Paspor</div>
                    <div class="h4 mb-0 fw-bold text-warning inaktif-stat-number"><?= $totalAdaPaspor ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Santri Pindahan -->
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Santri Pindahan</div>
                    <div class="h4 mb-0 fw-bold text-info inaktif-stat-number"><?= $totalPindahan ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row: Distribusi Angkatan (Kelas) & Distribusi Pondok -->
<div class="row g-3 mb-3">
    <!-- Chart 1: Distribusi Angkatan (Tingkatan Kelas Sesuai Dashboard) -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark small d-flex align-items-center gap-2" style="font-size:.88rem;">
                    <i class="bi bi-mortarboard-fill text-warning"></i> Distribusi Angkatan (Tingkatan Kelas)
                </h6>
                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none small" style="font-size:.72rem;" onclick="resetFilterTable()"><i class="bi bi-arrow-clockwise me-1"></i>Reset Filter</button>
            </div>
            <div class="card-body px-3 px-md-4 pb-3 pt-1">
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="angkatanChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Distribusi Pondok -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark small d-flex align-items-center gap-2" style="font-size:.88rem;">
                    <i class="bi bi-building-fill text-primary"></i> Distribusi Pondok
                </h6>
                <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size:.68rem;"><?= $totalPondok ?> Pondok</span>
            </div>
            <div class="card-body px-3 px-md-4 pb-3 pt-1">
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="pondokChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-2.5 p-md-3">
        <form class="row g-2 align-items-center inaktif-search-form" method="GET">
            <div class="col-md-6 col-lg-5">
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="q" class="form-control border-0 shadow-none bg-white py-2" placeholder="Cari nama santri atau stambuk..." value="<?= htmlspecialchars($search) ?>" style="font-size: .85rem;">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold w-100 shadow-sm py-2">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="<?= rtrim(API_URL, '/') . $urlGenerator->generate('inaktif-data') ?>" class="btn btn-sm btn-light border rounded-pill px-4 fw-semibold w-100 text-secondary shadow-sm py-2">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="inaktif-table-wrapper">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="sticky-top shadow-sm">
                    <tr>
                        <th class="ps-3 text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll" title="Pilih Semua"></th>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Stambuk</th>
                        <th>Nama Santri</th>
                        <th>Kelas</th>
                        <th>Pondok</th>
                        <th><i class="bi bi-geo-alt me-1 text-primary"></i>Negara Asal</th>
                        <th>No Paspor</th>
                        <th>Exp Paspor</th>
                        <th class="text-center inaktif-sticky-action" style="min-width: 220px;">Aksi</th>
                    </tr>
                    <tr class="table-secondary column-filters">
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari stambuk..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari nama..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari kelas..." id="filterInputKelas"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari pondok..." id="filterInputPondok"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari negara asal..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari paspor..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari exp..."></th>
                        <th class="inaktif-sticky-action"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($santris)): ?>
                    <tr><td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success opacity-75"></i>
                        <span class="fw-semibold">Tidak ada santri inaktif ditemukan</span>
                        <div class="small text-muted mt-1">Semua data santri dalam kondisi aktif atau sesuai filter pencarian.</div>
                    </td></tr>
                <?php else: $no = 1; foreach ($santris as $s): ?>
                    <tr>
                        <td class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input row-cb" value="<?= $s['kds'] ?>">
                        </td>
                        <td class="ps-3 text-muted fw-semibold"><?= $no++ ?></td>
                        <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars((string)$s['stambuk']) ?></span></td>
                        <td class="fw-semibold">
                            <span class="text-dark"><?= htmlspecialchars($s['nama']) ?></span>
                            <?php 
                                $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                                if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0): 
                            ?>
                                <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem; padding: 2px 4px; border-radius: 4px;">Pindahan</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary rounded-pill px-2"><?= htmlspecialchars((string)($s['kelas'] ?? '-')) ?></span></td>
                        <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars((string)($s['pondok'] ?? '-')) ?></span></td>
                        <td><i class="bi bi-globe me-1 text-muted"></i><?= htmlspecialchars((string)($s['negara'] ?? '-')) ?></td>
                        <td><code><?= htmlspecialchars((string)($s['no_paspor'] ?? '-')) ?></code></td>
                        <td>
                            <?php if (!empty($s['exp_paspor']) && $s['exp_paspor'] !== '0000-00-00'): ?>
                                <span class="badge bg-light text-dark border"><i class="bi bi-calendar-event me-1 text-muted"></i><?= date('d-M-Y', strtotime($s['exp_paspor'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center inaktif-sticky-action">
                            <div class="inaktif-action-group">
                                <button class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 shadow-sm text-nowrap fw-medium d-inline-flex align-items-center gap-1" title="Lihat Detail Santri"
                                    onclick="lihatSantri(<?= $s['kds'] ?>)">
                                    <i class="bi bi-eye"></i><span>Detail</span>
                                </button>
                                <button class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 shadow-sm text-nowrap fw-medium d-inline-flex align-items-center gap-1" title="Aktifkan Kembali Santri"
                                    onclick="reaktifkan(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i><span>Aktifkan</span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 shadow-sm text-nowrap fw-medium d-inline-flex align-items-center gap-1" title="Hapus Permanen"
                                    onclick="hapusData(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-trash"></i><span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">Menampilkan <strong><?= count($santris) ?></strong> dari total <strong><?= $total ?></strong> data santri inaktif</span>
        <span class="badge bg-light text-muted border">Sistem Informasi Santri</span>
    </div>
</div>

<!-- Modal Detail Santri Inaktif -->
<div class="modal fade" id="santriModalInaktif" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header py-3 px-4 bg-light border-0">
        <h5 class="modal-title fw-bold text-dark mb-0" id="modalTitle">
            <i class="bi bi-person-lines-fill me-2 text-danger"></i>Detail Santri Inaktif
        </h5>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-light border rounded-pill px-4 shadow-sm fw-medium text-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-lg me-1"></i> Tutup
            </button>
        </div>
      </div>
      <div class="modal-body bg-light p-3 p-md-4" style="font-size: 0.85rem; max-height: calc(85vh - 70px); overflow-y: auto;">
        <input type="hidden" id="santriKds" value="" readonly disabled>
        <input type="hidden" id="f_hapus_foto" value="0" readonly disabled>
        <div class="row g-3">
            <!-- KOLOM KIRI: Biodata Lengkap -->
            <div class="col-12 col-lg-4 inaktif-modal-col">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3">
                        <h6 class="mb-0 small fw-bold text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i>Biodata Lengkap</h6>
                    </div>
                    <div class="card-body py-3 px-3 px-md-4">
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Regno (Stambuk)</label><input type="number" id="f_stambuk" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">NIK (SKTT)</label><input type="text" id="f_nik" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Nama Lengkap</label><input type="text" id="f_nama" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Kelas</label><input type="text" id="f_kelas" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Rayon</label><input type="text" id="f_rayon" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Negara Asal</label><input type="text" id="f_negara" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Tempat Lahir</label><input type="text" id="f_tempat_lahir" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Tanggal Lahir</label><input type="date" id="f_tanggal_lahir" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="mb-0"><label class="text-muted small fw-bold mb-1">Alamat</label><textarea id="f_alamat" class="form-control form-control-sm bg-white border border-secondary-subtle" rows="3" readonly disabled></textarea></div>
                    </div>
                </div>
            </div>

            <!-- KOLOM TENGAH: Paspor & Tabs -->
            <div class="col-12 col-lg-5 inaktif-modal-col">
                <!-- Tentang Paspor -->
                <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3">
                        <h6 class="mb-0 small fw-bold text-dark"><i class="bi bi-passport text-warning me-2"></i>Tentang Paspor</h6>
                    </div>
                    <div class="card-body py-3 px-3 px-md-4">
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No Paspor Baru</label><input type="text" id="f_no_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No Paspor Lama</label><input type="text" id="f_no_paspor_lama" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly placeholder="(Otomatis Riwayat)" readonly disabled></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-5"><label class="text-muted small fw-bold mb-1">Exp Paspor</label><input type="date" id="f_exp_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-5"><label class="text-muted small fw-bold mb-1">Exp ITAS</label><input type="date" id="f_exp_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-2"><label class="text-muted small fw-bold mb-1">Lvl</label><input type="number" id="f_level_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" min="0" readonly disabled></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No ITAS</label><input type="text" id="f_no_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No IC Santri</label><input type="text" id="f_no_ic" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="row g-2 mb-1 border-top pt-3">
                            <div class="col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="text-muted small fw-bold mb-0">File Paspor <a href="#" id="view_file_paspor" target="_blank" class="ms-1 small d-none text-primary" title="Lihat"><i class="bi bi-eye-fill"></i></a></label>
                                </div>
                                <input type="file" id="f_file_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" accept=".pdf,.jpg,.jpeg,.png" readonly disabled>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold mb-1">File ITAS <a href="#" id="view_file_itas" target="_blank" class="ms-1 small d-none text-primary" title="Lihat"><i class="bi bi-eye-fill"></i></a></label>
                                <input type="file" id="f_file_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" accept=".pdf,.jpg,.jpeg,.png" readonly disabled>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs Detail -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="min-height: 180px;">
                    <div class="card-header bg-white p-2 border-bottom-0">
                        <ul class="nav inaktif-nav-tabs" id="detailTabs">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#t_paspor"><i class="bi bi-card-text"></i> Paspor</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t_ortu"><i class="bi bi-people"></i> Orang Tua</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t_telp"><i class="bi bi-telephone"></i> No Telp</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t_barang"><i class="bi bi-box-seam"></i> Barang</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t_rpaspor"><i class="bi bi-clock-history"></i> R.Paspor</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t_ritas"><i class="bi bi-clock-history"></i> R.ITAS</a></li>
                            <li class="nav-item"><a class="nav-link text-primary fw-medium" data-bs-toggle="tab" href="#t_berkas"><i class="bi bi-folder-check"></i> Berkas</a></li>
                        </ul>
                    </div>
                    <div class="card-body py-3 px-3 px-md-4 tab-content">
                        <div class="tab-pane fade show active" id="t_paspor">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">Tempat Dikeluarkan</label><input type="text" id="f_tempat_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">Tanggal Dikeluarkan</label><input type="date" id="f_tgl_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_ortu">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">Nama Ayah</label><input type="text" id="f_nama_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">Nama Ibu</label><input type="text" id="f_nama_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_telp">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No HP Ayah</label><input type="text" id="f_no_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No HP Ibu</label><input type="text" id="f_no_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">No HP Alternatif</label><input type="text" id="f_no_hp_alternatif" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_barang">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" id="tableBarang">
                                    <thead class="table-light"><tr><th>Nama Barang</th><th>Jumlah</th><th>Kondisi</th></tr></thead>
                                    <tbody id="listBarang"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_rpaspor">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>No Paspor</th><th>Exp</th><th>File</th></tr></thead>
                                    <tbody id="listRPaspor"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_ritas">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>No ITAS</th><th>Lvl</th><th>Exp</th><th>File</th></tr></thead>
                                    <tbody id="listRITAS"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_berkas">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>Nama Berkas</th><th>Aksi</th></tr></thead>
                                    <tbody id="listBerkas"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Foto & Status -->
            <div class="col-12 col-lg-3 inaktif-modal-col">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3">
                        <h6 class="mb-0 small fw-bold text-dark"><i class="bi bi-image text-success me-2"></i>Foto Santri</h6>
                    </div>
                    <div class="card-body py-3 px-3 px-md-4 text-center d-flex flex-column align-items-center justify-content-center">
                        <div class="rounded-4 border p-2 bg-light shadow-sm mb-3" style="width: 140px; height: 180px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                            <img id="imgPreview" src="" alt="Foto Santri" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; display: none;">
                            <div id="imgIcon" class="text-secondary"><i class="bi bi-person-bounding-box fs-1"></i><div class="small mt-1">Tanpa Foto</div></div>
                        </div>
                        <div class="alert alert-danger p-2 small mb-0 rounded-3 text-start w-100">
                            <i class="bi bi-info-circle me-1"></i> Data dalam status <strong>Inaktif (Arsip)</strong>. Klik <strong>Aktifkan</strong> pada tabel jika santri ini telah kembali aktif belajar.
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <div id="statusToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert">
    <div class="d-flex">
      <div class="toast-body fw-medium" id="toastBody"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script>
const rawData = <?= $santrisJson ?>;

// Parse kelas/angkatan format
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
    'Kelas 5': 7, 'Kelas 6': 8, 'Capel Persiapan': 9,
    'Capel Penerimaan': 10, 'Pengabdian': 11,
    'Alumni': 12, 'Lainnya': 13
};

// Vibrant palette matching the dashboard screenshot
const angkatanColorMap = {
    'Kelas 1': '#3b82f6',
    'Kelas 1 Int': '#10b981',
    'Kelas 2': '#f59e0b',
    'Kelas 3': '#ef4444',
    'Kelas 3 Int': '#8b5cf6',
    'Kelas 4': '#f97316',
    'Kelas 5': '#06b6d4',
    'Kelas 6': '#84cc16',
    'Capel Persiapan': '#ec4899',
    'Capel Penerimaan': '#14b8a6',
    'Pengabdian': '#6366f1',
    'Alumni': '#64748b',
    'Lainnya': '#94a3b8'
};

const defaultColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#f97316','#06b6d4','#84cc16','#ec4899','#6366f1'];

function groupInaktifData(data, field) {
    const counts = {};
    data.forEach(item => {
        let val = item[field];
        if (field === 'angkatan') {
            val = parseKelas(item.kelas);
        } else {
            if (!val || val.trim() === '') val = '-';
            else val = val.trim();
        }
        counts[val] = (counts[val] || 0) + 1;
    });

    if (field === 'angkatan') {
        const sorted = Object.keys(counts).map(k => ({label: k, count: counts[k]}))
            .sort((a, b) => (classOrder[a.label] || 99) - (classOrder[b.label] || 99));
        return { 
            labels: sorted.map(i => i.label), 
            data: sorted.map(i => i.count),
            colors: sorted.map(i => angkatanColorMap[i.label] || '#3b82f6')
        };
    }

    const sorted = Object.keys(counts).map(k => ({label: k, count: counts[k]})).sort((a,b) => b.count - a.count);
    return { 
        labels: sorted.map(i => i.label), 
        data: sorted.map(i => i.count),
        colors: defaultColors 
    };
}

let angkatanChartInstance = null;
let pondokChartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    // 1. Inisialisasi Grafik Angkatan
    const angkatanAgg = groupInaktifData(rawData, 'angkatan');
    const angkatanCtx = document.getElementById('angkatanChart');
    if (angkatanCtx && typeof Chart !== 'undefined') {
        angkatanChartInstance = new Chart(angkatanCtx, {
            type: 'bar',
            data: {
                labels: angkatanAgg.labels,
                datasets: [{
                    label: 'Jumlah Santri',
                    data: angkatanAgg.data,
                    backgroundColor: angkatanAgg.colors,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.parsed.y + ' santri inaktif';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 2, precision: 0 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 25,
                            minRotation: 15,
                            font: { size: 11 }
                        }
                    }
                },
                onClick: (e, activeEls) => {
                    if (activeEls.length > 0) {
                        const idx = activeEls[0].index;
                        const label = angkatanAgg.labels[idx];
                        filterByKelas(label);
                    }
                }
            }
        });
    }

    // 2. Inisialisasi Grafik Pondok
    const pondokAgg = groupInaktifData(rawData, 'pondok');
    const pondokCtx = document.getElementById('pondokChart');
    if (pondokCtx && typeof Chart !== 'undefined') {
        pondokChartInstance = new Chart(pondokCtx, {
            type: 'doughnut',
            data: {
                labels: pondokAgg.labels,
                datasets: [{
                    data: pondokAgg.data,
                    backgroundColor: defaultColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' santri';
                            }
                        }
                    }
                },
                onClick: (e, activeEls) => {
                    if (activeEls.length > 0) {
                        const idx = activeEls[0].index;
                        const label = pondokAgg.labels[idx];
                        filterByPondok(label);
                    }
                }
            }
        });
    }
});

function filterByKelas(kelasName) {
    const inp = document.getElementById('filterInputKelas');
    if (inp) {
        inp.value = kelasName;
        filterTable();
        inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function filterByPondok(pondokName) {
    const inp = document.getElementById('filterInputPondok');
    if (inp) {
        inp.value = pondokName;
        filterTable();
        inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function resetFilterTable() {
    const inputs = document.querySelectorAll('.column-filters input');
    inputs.forEach(inp => inp.value = '');
    filterTable();
}

function lihatSantri(kds) {
    fetch('<?= API_URL ?>/santri/' + kds)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('Error', data.message || 'Gagal mengambil detail santri', 'error');
                return;
            }
            const s = data.santri;
            const p = data.paspor || {};
            const set = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val !== undefined && val !== null ? val : '';
            };

            set('santriKds', s.kds);
            set('f_stambuk', s.stambuk);
            set('f_nik', s.no_sktt || s.nik);
            set('f_nama', s.nama);
            set('f_kelas', s.kelas);
            set('f_rayon', s.rayon);
            set('f_negara', s.negara);
            set('f_tempat_lahir', s.tempat_lahir);
            set('f_tanggal_lahir', s.tanggal_lahir);
            set('f_alamat', s.alamat);

            set('f_no_paspor', p.no_paspor);
            set('f_no_paspor_lama', p.no_paspor_lama);
            set('f_exp_paspor', p.exp_paspor);
            set('f_exp_itas', p.exp_itas);
            set('f_level_itas', p.level_itas);
            set('f_no_itas', p.no_itas);
            set('f_no_ic', s.no_ic);

            set('f_tempat_paspor', p.tempat_paspor);
            set('f_tgl_paspor', p.tgl_paspor);
            set('f_nama_ayah', s.nama_ayah);
            set('f_nama_ibu', s.nama_ibu);
            set('f_no_ayah', s.no_ayah);
            set('f_no_ibu', s.no_ibu);
            set('f_no_hp_alternatif', s.no_hp_alternatif);

            const vPas = document.getElementById('view_file_paspor');
            if (vPas) {
                if (p.path_file_paspor) {
                    vPas.href = '<?= API_URL ?>/santri/' + kds + '/paspor-file';
                    vPas.classList.remove('d-none');
                } else {
                    vPas.classList.add('d-none');
                }
            }

            const vItas = document.getElementById('view_file_itas');
            if (vItas) {
                if (p.path_file_itas) {
                    vItas.href = '<?= API_URL ?>/santri/' + kds + '/itas-file';
                    vItas.classList.remove('d-none');
                } else {
                    vItas.classList.add('d-none');
                }
            }

            const listBarang = document.getElementById('listBarang');
            const listRPaspor = document.getElementById('listRPaspor');
            const listRITAS = document.getElementById('listRITAS');
            const listBerkas = document.getElementById('listBerkas');

            if (listBarang) {
                listBarang.innerHTML = (data.barang || []).map(b => `
                    <tr>
                        <td>${b.nama_barang}</td>
                        <td>${b.jumlah}</td>
                        <td>${b.kondisi || '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="3" class="text-center text-muted small py-2">Belum ada barang bawaan</td></tr>';
            }

            if (listRPaspor) {
                listRPaspor.innerHTML = (data.r_paspor || []).map(rp => `
                    <tr>
                        <td>${rp.no_paspor}</td>
                        <td>${rp.exp_paspor}</td>
                        <td>${rp.path_file ? `<a href="<?= API_URL ?>/api/santri/doc/paspor/${rp.id}" target="_blank" title="Lihat Dokumen" class="text-primary"><i class="bi bi-file-earmark-pdf-fill"></i></a>` : '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="3" class="text-center text-muted small py-2">Riwayat paspor kosong</td></tr>';
            }

            if (listRITAS) {
                listRITAS.innerHTML = (data.r_itas || []).map(ri => `
                    <tr>
                        <td>${ri.no_itas}</td>
                        <td>${ri.level_itas || '-'}</td>
                        <td>${ri.exp_itas}</td>
                        <td>${ri.path_file ? `<a href="<?= API_URL ?>/api/santri/doc/itas/${ri.id}" target="_blank" title="Lihat Dokumen" class="text-primary"><i class="bi bi-file-earmark-pdf-fill"></i></a>` : '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="4" class="text-center text-muted small py-2">Riwayat ITAS kosong</td></tr>';
            }

            if (listBerkas) {
                listBerkas.innerHTML = (data.berkas || []).map(b => `
                    <tr>
                        <td><div class="text-truncate" style="max-width: 180px;" title="${b.nama}">${b.nama}</div></td>
                        <td class="text-center">
                            <a href="${b.url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" title="Lihat"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="2" class="text-center text-muted small py-2">Belum ada berkas</td></tr>';
            }

            const imgPreview = document.getElementById('imgPreview');
            const imgIcon = document.getElementById('imgIcon');
            if (s.path_foto && imgPreview && imgIcon) {
                imgPreview.src = '<?= API_URL ?>/santri/' + kds + '/photo?v=' + new Date().getTime();
                imgPreview.style.display = 'block';
                imgIcon.style.display = 'none';
            } else if (imgPreview && imgIcon) {
                imgPreview.style.display = 'none';
                imgIcon.style.display = 'block';
            }
            
            const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('santriModalInaktif'));
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-person-lines-fill me-2 text-danger"></i>Detail Santri <span class="badge bg-danger ms-2 align-middle" style="font-size: 0.65rem; padding: 4px 6px; border-radius: 6px; vertical-align: text-top;"><i class="bi bi-x-circle me-1"></i>SANTRI INAKTIF</span>';
            myModal.show();
        })
        .catch(() => {
            Swal.fire('Error', 'Gagal memuat detail santri inaktif.', 'error');
        });
}

function filterTable() {
    const table = document.querySelector('.table-hover');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    const inputs = Array.from(table.querySelectorAll('.column-filters input'));
    const filters = inputs.map(inp => {
        return {
            val: inp.value.toLowerCase().trim(),
            cellIndex: inp.closest('th').cellIndex
        };
    });

    rows.forEach(row => {
        if(row.cells.length <= 1) return;
        
        let show = true;
        filters.forEach(f => {
            if (f.val) {
                const cellText = row.cells[f.cellIndex]?.textContent.toLowerCase() || '';
                if (!cellText.includes(f.val)) {
                    show = false;
                }
            }
        });
        row.style.display = show ? '' : 'none';
    });
}

function reaktifkan(kds, nama) {
    Swal.fire({
        title: 'Aktifkan Santri?',
        text: `Aktifkan kembali santri "${nama}" ke daftar data aktif?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, aktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/santri/' + kds + '/update-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ aktif: 1 })
            })
                .then(res => res.json())
                .then(data => {
                    const t = document.getElementById('statusToast');
                    document.getElementById('toastBody').textContent = data.message;
                    new bootstrap.Toast(t, {delay: 2500}).show();
                    if (data.success) setTimeout(() => location.reload(), 1000);
                }).catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem saat mengaktifkan santri.', 'error');
                });
        }
    });
}

// Drag to select logic for desktop & click for touch
let isDragging = false;
const checkboxes = document.querySelectorAll('.row-cb');

document.addEventListener('mousedown', (e) => {
    if (e.target.classList.contains('row-cb') || (e.target.closest('td') && e.target.closest('td').querySelector('.row-cb'))) {
        isDragging = true;
    }
});
document.addEventListener('mouseup', () => { isDragging = false; updateBulkAction(); });
document.addEventListener('mouseleave', () => { isDragging = false; updateBulkAction(); });

checkboxes.forEach(cb => {
    cb.addEventListener('mouseenter', function() {
        if (isDragging) {
            this.checked = !this.checked;
            updateBulkAction();
        }
    });
    cb.addEventListener('change', updateBulkAction);
});

const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
        updateBulkAction();
    });
}

function updateBulkAction() {
    const count = document.querySelectorAll('.row-cb:checked').length;
    const btnDel = document.getElementById('btnBulkDelete');
    const btnReact = document.getElementById('btnBulkReactivate');
    
    if (btnDel && btnReact) {
        document.getElementById('countSelectedDelete').textContent = count;
        document.getElementById('countSelectedReactivate').textContent = count;
        if (count > 0) {
            btnDel.classList.remove('d-none');
            btnReact.classList.remove('d-none');
        } else {
            btnDel.classList.add('d-none');
            btnReact.classList.add('d-none');
        }
    }
}

function bulkReaktifkan() {
    const selected = Array.from(document.querySelectorAll('.row-cb:checked')).map(cb => cb.value);
    if (selected.length === 0) return;

    Swal.fire({
        title: 'Aktifkan Massal?',
        html: `Aktifkan kembali <strong>${selected.length}</strong> data santri terpilih ke daftar santri aktif?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Aktifkan ${selected.length} Santri!`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang memproses...',
                text: 'Mohon tunggu proses pengaktifan data massal...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/inaktif-data/bulk-reaktifkan', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ kds: selected })
            }).then(r => r.json()).then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            }).catch(() => {
                Swal.fire('Gagal', 'Terjadi kesalahan saat mengaktifkan data massal.', 'error');
            });
        }
    });
}

function hapusData(kds, nama) {
    Swal.fire({
        title: 'Hapus Permanen?',
        html: `Anda yakin ingin menghapus data santri <strong>"${nama}"</strong> secara permanen?<br><br><div class="alert alert-danger p-2.5 small text-start mb-0 rounded-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Peringatan:</strong> Seluruh berkas paspor, ITAS, dan riwayat yang terkait dengan santri ini akan dihapus permanen.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Permanen!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang menghapus...',
                text: 'Mohon tunggu proses penghapusan data permanen...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/inaktif-data/' + kds + '/hard-delete', { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                }).catch(() => {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
                });
        }
    });
}

function bulkHapus() {
    const selected = Array.from(document.querySelectorAll('.row-cb:checked')).map(cb => cb.value);
    if (selected.length === 0) return;

    Swal.fire({
        title: 'Hapus Massal Permanen?',
        html: `Anda akan menghapus <strong>${selected.length}</strong> data santri inaktif terpilih secara permanen.<br><br><div class="alert alert-danger p-2.5 small text-start mb-0 rounded-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Peringatan:</strong> Seluruh data santri terpilih beserta berkas dan histori riwayatnya akan dihapus permanen.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ${selected.length} Data!`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang menghapus massal...',
                text: 'Mohon tunggu, jangan tutup halaman ini...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/inaktif-data/bulk-hard-delete', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ kds: selected })
            }).then(r => r.json()).then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            }).catch(() => {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data massal.', 'error');
            });
        }
    });
}
</script>
