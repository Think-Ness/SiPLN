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
$totalLaki = 0;
$totalPerempuan = 0;
$totalAdaPaspor = 0;
$totalPindahan = 0;
$myKep = $_SESSION['def_kepengurusan'] ?? '';

foreach ($santris as $s) {
    $jk = strtolower(trim((string)($s['jenis_kelamin'] ?? '')));
    if (in_array($jk, ['l', 'laki-laki', 'laki - laki', 'pria', 'ikhwan', 'putra', 'cowok'])) {
        $totalLaki++;
    } elseif (in_array($jk, ['p', 'perempuan', 'wanita', 'akhwat', 'putri', 'cewek'])) {
        $totalPerempuan++;
    }
    if (!empty($s['no_paspor'])) {
        $totalAdaPaspor++;
    }
    $sKep = trim((string)($s['kepengurusan'] ?? ''));
    if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0) {
        $totalPindahan++;
    }
}
?>

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
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}
.inaktif-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
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
        flex-direction: column;
        width: 100%;
        gap: 8px;
    }
    .page-header-controls .btn {
        width: 100% !important;
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
<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.12);">
            <i class="bi bi-person-x-fill fs-4 text-danger"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Data Santri Inaktif</h4>
            <div class="text-muted small fw-medium mt-1">
                Daftar arsip santri tidak aktif &bull; Total: <strong class="text-danger"><?= $total ?></strong> santri
            </div>
        </div>
    </div>
    <?php if ($isSuperAdmin): ?>
    <div class="page-header-controls d-flex align-items-center gap-2">
        <button class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm d-none" id="btnBulkDelete" onclick="bulkHapus()">
            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="countSelected">0</span>)
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Stat Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-person-x"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Total Inaktif</div>
                    <div class="h4 mb-0 fw-bold text-danger inaktif-stat-number"><?= $totalInaktif ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-gender-male"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Santri Putra (L)</div>
                    <div class="h4 mb-0 fw-bold text-primary inaktif-stat-number"><?= $totalLaki ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-gender-female"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Santri Putri (P)</div>
                    <div class="h4 mb-0 fw-bold text-info inaktif-stat-number"><?= $totalPerempuan ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card inaktif-stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="inaktif-stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-passport"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Ada Paspor</div>
                    <div class="h4 mb-0 fw-bold text-warning inaktif-stat-number"><?= $totalAdaPaspor ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
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
                        <?php if ($isSuperAdmin): ?>
                        <th class="ps-3 text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                        <?php endif; ?>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Stambuk</th>
                        <th>Nama Santri</th>
                        <th>Kelas</th>
                        <th>Pondok</th>
                        <th>Kewarganegaraan</th>
                        <th>No Paspor</th>
                        <th>Exp Paspor</th>
                        <th class="text-center inaktif-sticky-action" style="min-width: 220px;">Aksi</th>
                    </tr>
                    <tr class="table-secondary column-filters">
                        <?php if ($isSuperAdmin): ?><th></th><?php endif; ?>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari stambuk..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari nama..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari kelas..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari pondok..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari negara..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari paspor..."></th>
                        <th><input type="text" class="form-control form-control-sm column-search" onkeyup="filterTable()" placeholder="Cari exp..."></th>
                        <th class="inaktif-sticky-action"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($santris)): ?>
                    <tr><td colspan="<?= $isSuperAdmin ? '10' : '9' ?>" class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success opacity-75"></i>
                        <span class="fw-semibold">Tidak ada santri inaktif ditemukan</span>
                        <div class="small text-muted mt-1">Semua data santri dalam kondisi aktif atau sesuai filter pencarian.</div>
                    </td></tr>
                <?php else: $no = 1; foreach ($santris as $s): ?>
                    <tr>
                        <?php if ($isSuperAdmin): ?>
                        <td class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input row-cb" value="<?= $s['kds'] ?>">
                        </td>
                        <?php endif; ?>
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
                        <td><span class="badge bg-secondary rounded-pill px-2"><?= htmlspecialchars($s['kelas']) ?></span></td>
                        <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($s['pondok']) ?></span></td>
                        <td><i class="bi bi-globe me-1 text-muted"></i><?= htmlspecialchars($s['kewarganegaraan']) ?></td>
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
                                <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-1 shadow-sm text-nowrap fw-medium" title="Lihat Detail Santri"
                                    onclick="lihatSantri(<?= $s['kds'] ?>)">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 shadow-sm text-nowrap fw-medium" title="Aktifkan Kembali Santri"
                                    onclick="reaktifkan(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Aktifkan
                                </button>
                                <?php if ($isSuperAdmin): ?>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 shadow-sm text-nowrap fw-medium" title="Hapus Permanen"
                                    onclick="hapusData(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                                <?php endif; ?>
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
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No. HP Ayah</label><input type="number" id="f_no_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No. HP Ibu</label><input type="number" id="f_no_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">No. HP Alternatif</label><input type="number" id="f_no_hp_alternatif" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_barang">
                            <div class="table-responsive" style="max-height: 140px;">
                                <table class="table table-sm table-bordered m-0">
                                    <thead class="table-light"><tr><th class="text-center" style="width: 40px;">#</th><th>Nama Barang</th><th class="text-center" style="width: 60px;">Jml</th></tr></thead>
                                    <tbody id="listBarang"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_rpaspor">
                            <div class="table-responsive" style="max-height: 140px;">
                                <table class="table table-sm table-bordered m-0"><thead class="table-light"><tr><th>No Paspor</th><th>Exp</th><th>Dok</th></tr></thead><tbody id="listRPaspor"></tbody></table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_ritas">
                            <div class="table-responsive" style="max-height: 140px;">
                                <table class="table table-sm table-bordered m-0"><thead class="table-light"><tr><th>No ITAS</th><th>Lvl</th><th>Exp</th><th>Dok</th></tr></thead><tbody id="listRITAS"></tbody></table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_berkas">
                            <div class="table-responsive" style="max-height: 140px;">
                                <table class="table table-sm table-bordered m-0"><thead class="table-light"><tr><th>Nama File Berkas</th><th class="text-center" style="width:70px;">Aksi</th></tr></thead><tbody id="listBerkas"></tbody></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Foto & Status -->
            <div class="col-12 col-lg-3 inaktif-modal-col">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body py-3 px-3 d-flex flex-column">
                        <div id="fotoContainer" class="border rounded-4 mb-3 flex-grow-1 d-flex align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative" style="min-height: 160px; border-width: 2px !important; border-style: dashed !important;">
                            <img id="imgPreview" src="" alt="Foto Santri" style="max-width:100%; max-height:160px; display:none; object-fit: contain;">
                            <i id="imgIcon" class="bi bi-person text-secondary" style="font-size: 5rem;"></i>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Status</label><select id="f_aktif" class="form-select form-select-sm bg-white border border-secondary-subtle" disabled><option value="1">Aktif</option><option value="0" selected>Inaktif</option></select></div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold mb-1">Pondok</label>
                                <input type="text" id="f_pondok" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled>
                            </div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Keberadaan Paspor</label><input type="text" id="f_keberadaan_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Ukuran Baju</label><input type="text" id="f_ukuran_baju" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Gender</label><select id="f_jenis_kelamin" class="form-select form-select-sm bg-white border border-secondary-subtle" disabled><option value="Laki-laki">L</option><option value="Perempuan">P</option></select></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Kepengurusan</label><input type="text" id="f_kepengurusan" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        
                        <!-- Kewarganegaraan -->
                        <div class="mt-auto"><label class="text-muted small fw-bold mb-1">Kewarganegaraan</label><input type="text" id="f_kewarganegaraan" class="form-control form-control-sm bg-light text-muted border border-secondary-subtle" readonly disabled></div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toastMsg" class="toast align-items-center text-white border-0 bg-success shadow-lg" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
function lihatSantri(kds) {
    const listBarang = document.getElementById('listBarang');
    const listRPaspor = document.getElementById('listRPaspor');
    const listRITAS = document.getElementById('listRITAS');
    const listBerkas = document.getElementById('listBerkas');
    
    // reset preview
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('imgIcon').style.display = 'block';
    
    ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
     'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
     'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
     'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
         const el = document.getElementById('f_' + id);
         if(el) el.value = '';
     });

    fetch('<?= API_URL ?>/api/santri/' + kds)
        .then(r => r.json())
        .then(data => {
            const s = data.santri || {};
            const p = data.paspor || {};
            const i = data.itas || {};
            const pl = data.paspor_lama || {};

            const set = (id, val) => { const el = document.getElementById('f_' + id); if(el) el.value = val || ''; };
            
            set('stambuk', s.stambuk); set('nama', s.nama); set('kelas', s.kelas); 
            set('rayon', s.rayon); set('pondok', s.pondok); set('negara', s.negara); 
            set('kewarganegaraan', s.kewarganegaraan); set('jenis_kelamin', s.jenis_kelamin);
            set('kepengurusan', s.kepengurusan); set('tempat_lahir', s.tempat_lahir);
            set('tanggal_lahir', s.tanggal_lahir); set('nama_ayah', s.nama_ayah);
            set('nama_ibu', s.nama_ibu); set('no_ayah', s.no_ayah); set('no_ibu', s.no_ibu);
            set('no_hp_alternatif', s.no_hp_alternatif); set('alamat', s.alamat);
            set('nik', s.no_sktt); set('no_ic', s.no_ic);
            set('keberadaan_paspor', s.keberadaan_paspor); set('ukuran_baju', s.ukuran_baju);

            if (p) { 
                set('no_paspor', p.no_paspor); set('tempat_paspor', p.tempat_keluaran); 
                set('tgl_paspor', p.tgl_keluaran); set('exp_paspor', p.exp_paspor); 
                const vp = document.getElementById('view_file_paspor');
                if (vp) {
                    if (p.path_file) {
                        vp.href = '<?= API_URL ?>/api/santri/doc/paspor/' + p.id;
                        vp.classList.remove('d-none');
                    } else {
                        vp.classList.add('d-none');
                    }
                }
            }
            if (pl) { set('no_paspor_lama', pl.no_paspor); }
            if (i) { 
                set('no_itas', i.no_itas); set('exp_itas', i.exp_itas); set('level_itas', i.level_itas); 
                const vi = document.getElementById('view_file_itas');
                if(vi) {
                    if (i.path_file) {
                        vi.href = '<?= API_URL ?>/api/santri/doc/itas/' + i.id;
                        vi.classList.remove('d-none');
                    } else {
                        vi.classList.add('d-none');
                    }
                }
            }

            if (listBarang) {
                listBarang.innerHTML = (data.barang || []).map((b, idx) => `
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td>${b.nama_barang}</td>
                        <td class="text-center">${b.jumlah_barang}</td>
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

            if (s.path_foto) {
                const imgPreview = document.getElementById('imgPreview');
                if(imgPreview) {
                    imgPreview.src = '<?= API_URL ?>/santri/' + kds + '/photo?v=' + new Date().getTime();
                    imgPreview.style.display = 'block';
                    document.getElementById('imgIcon').style.display = 'none';
                }
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
            fetch('<?= API_URL ?>/api/santri/' + kds + '/toggle-aktif', { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
                .then(r => r.json()).then(data => {
                    const t = document.getElementById('toastMsg');
                    t.className = 'toast align-items-center text-white border-0 shadow-lg ' + (data.success ? 'bg-success' : 'bg-danger');
                    document.getElementById('toastBody').textContent = data.message;
                    new bootstrap.Toast(t, {delay: 2500}).show();
                    if (data.success) setTimeout(() => location.reload(), 1000);
                }).catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem saat mengaktifkan santri.', 'error');
                });
        }
    });
}

<?php if ($isSuperAdmin): ?>
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
    const btn = document.getElementById('btnBulkDelete');
    if (btn) {
        document.getElementById('countSelected').textContent = count;
        if (count > 0) btn.classList.remove('d-none');
        else btn.classList.add('d-none');
    }
}

function hapusData(kds, nama) {
    Swal.fire({
        title: 'Hapus Permanen?',
        html: `Anda yakin ingin menghapus data santri <strong>"${nama}"</strong> secara permanen?<br><br><div class="alert alert-danger p-2 small text-start mb-0"><i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Peringatan Keras:</strong> Data yang dihapus tidak dapat dipulihkan kembali, termasuk seluruh berkas paspor, ITAS, dan riwayat JobDesk.</div>`,
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
        html: `Anda akan menghapus <strong>${selected.length}</strong> data santri inaktif secara permanen.<br><br><div class="alert alert-danger p-2 small text-start mb-0"><i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Peringatan Keras:</strong> Seluruh data santri terpilih beserta berkas dan histori riwayatnya akan dihapus permanen dan tidak bisa dikembalikan.</div>`,
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
<?php endif; ?>
</script>
