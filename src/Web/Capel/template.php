<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
/** @var WebView $this */
$this->setTitle('Pendaftaran CAPEL | Sistem Informasi');
?>
<style>
/* Modern Responsive Styling for Pendaftaran CAPEL */
:root {
    --capel-primary: #3b82f6;
    --capel-primary-dark: #2563eb;
    --capel-success: #10b981;
    --capel-warning: #f59e0b;
    --capel-danger: #ef4444;
}

.capel-stat-card {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    background: #ffffff;
}
.capel-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}
.capel-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

/* Segmented Navigation Tabs */
.capel-nav-tabs {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    gap: 8px;
    padding: 6px;
    background: #f1f5f9;
    border-radius: 14px;
    border: none;
}
.capel-nav-tabs::-webkit-scrollbar {
    height: 3px;
}
.capel-nav-tabs::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.capel-nav-tabs .nav-link {
    white-space: nowrap;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 8px 16px;
    color: #64748b;
    border: none !important;
    background: transparent;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}
.capel-nav-tabs .nav-link:hover:not(.active) {
    background: rgba(255, 255, 255, 0.6);
    color: #1e293b;
}
.capel-nav-tabs .nav-link.active {
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.capel-nav-tabs .nav-link#pending-tab.active {
    color: #2563eb !important;
}
.capel-nav-tabs .nav-link#approved-tab.active {
    color: #059669 !important;
}
.capel-nav-tabs .nav-link#rejected-tab.active {
    color: #dc2626 !important;
}

/* Table Wrapper & Sticky Columns */
.capel-table-wrapper {
    max-height: 62vh;
    overflow: auto;
    border-radius: 12px;
    position: relative;
    -webkit-overflow-scrolling: touch;
}
.capel-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.capel-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.capel-table-wrapper table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin-bottom: 0;
}
.capel-table-wrapper thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
}
.capel-table-wrapper thead tr:nth-child(1) th {
    position: sticky;
    top: 0;
    z-index: 10;
}
.capel-table-wrapper thead tr:nth-child(2) th {
    position: sticky;
    top: 41px;
    z-index: 10;
    padding: 6px 8px;
    background: #f1f5f9 !important;
}
.capel-sticky-action {
    position: sticky;
    right: 0;
    z-index: 8;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.capel-table-wrapper thead th.capel-sticky-action {
    z-index: 12;
    background-color: #f8fafc !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.capel-table-wrapper tbody td {
    padding: 12px;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.capel-table-wrapper tbody tr:hover td {
    background-color: #f8fafc;
}

/* Search Row Inputs */
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

/* Action button tags */
.capel-action-group {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
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
    .page-header-controls .btn, 
    .page-header-controls .form-select {
        width: 100% !important;
    }
    .capel-stat-card .card-body {
        padding: 0.85rem !important;
    }
    .capel-stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.15rem;
    }
    .capel-stat-number {
        font-size: 1.4rem !important;
    }
    .capel-table-wrapper {
        max-height: 52vh;
    }
    .capel-tab-action-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px;
    }
    .capel-tab-action-buttons {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .capel-tab-action-buttons .btn {
        flex: 1 1 auto;
    }
    .preview-iframe-berkas {
        height: 320px !important;
    }
}
@media (min-width: 992px) {
    .preview-iframe-berkas {
        height: 460px;
    }
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary"></i> Review Pendaftaran CAPEL
        </h3>
        <p class="text-muted small mb-0">Verifikasi, persetujuan, dan sinkronisasi data calon pelajar baru</p>
    </div>
    <div class="d-flex gap-2 page-header-controls flex-wrap">
        <?php if ($isSuperAdmin): ?>
        <select class="form-select fw-semibold rounded-pill px-3 shadow-sm" style="width: auto; min-width: 200px;" id="superAdminInstansi" onchange="window.location.href='?instansi_id=' + this.value;">
            <option value="">Semua Instansi (Statis)</option>
            <?php foreach ($instansiList as $inst): ?>
                <option value="<?= $inst['id'] ?>" <?= ($selectedInstansiId == $inst['id']) ? 'selected' : '' ?>><?= htmlspecialchars($inst['nama_instansi']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <?php if (!$isSuperAdmin || $selectedInstansiId): ?>
        <button class="btn btn-outline-secondary fw-semibold rounded-pill px-3 py-2 shadow-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modalAturTabel">
            <i class="bi bi-layout-text-window-reverse me-2"></i>Atur Kolom Tabel
        </button>
        <button class="btn btn-primary fw-semibold rounded-pill px-4 py-2 shadow-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modalSync">
            <i class="bi bi-cloud-download me-2"></i>Tarik Data Baru
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="card capel-stat-card shadow-sm h-100 border-0">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:0.8px;">Menunggu Review</span>
                    <h3 class="fw-bold text-primary capel-stat-number mb-0 mt-1" id="statCardPending">0</h3>
                    <small class="text-muted" style="font-size:0.72rem;">Pendaftar baru</small>
                </div>
                <div class="capel-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card capel-stat-card shadow-sm h-100 border-0">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:0.8px;">Disetujui</span>
                    <h3 class="fw-bold text-success capel-stat-number mb-0 mt-1" id="statCardApproved">0</h3>
                    <small class="text-muted" style="font-size:0.72rem;">Diterima sebagai santri</small>
                </div>
                <div class="capel-stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card capel-stat-card shadow-sm h-100 border-0">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:0.8px;">Ditolak</span>
                    <h3 class="fw-bold text-danger capel-stat-number mb-0 mt-1" id="statCardRejected">0</h3>
                    <small class="text-muted" style="font-size:0.72rem;">Tidak memenuhi syarat</small>
                </div>
                <div class="capel-stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sync Data -->
<div class="modal fade" id="modalSync" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Tarik Data dari Google Spreadsheet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">ID Spreadsheet</label>
            <input type="text" id="syncSpreadsheetId" class="form-control" value="<?= htmlspecialchars($defaultSpreadsheetId ?? '') ?>" placeholder="Misal: 1BxiMVs0XRYFgPN_DQ...">
            <div class="form-text">ID bisa ditemukan pada URL Spreadsheet Anda.</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama / Range Sheet</label>
            <input type="text" id="syncRange" class="form-control" value="<?= htmlspecialchars($defaultSpreadsheetRange ?? 'Form Responses 1') ?>">
            <div class="form-text">Nama tab di bagian bawah Spreadsheet Anda.</div>
        </div>
        <div class="d-grid mb-3">
            <button type="button" class="btn btn-outline-secondary" id="btnCekHeader">
                <i class="bi bi-search me-2"></i>Cek & Atur Pemetaan Kolom
            </button>
        </div>

        <div id="mappingSection" class="d-none border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
            <h6 class="fw-bold mb-3"><i class="bi bi-layout-three-columns me-2"></i>Pemetaan Kolom (Wajib)</h6>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Email Pendaftar</label>
                    <select id="mapEmail" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Nama Calon Santri</label>
                    <select id="mapNama" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Tanggal Lahir</label>
                    <select id="mapTglLahir" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Kewarganegaraan</label>
                    <select id="mapKwn" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Tempat Lahir</label>
                    <select id="mapTempatLahir" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Nama Ayah</label>
                    <select id="mapAyah" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Nama Ibu</label>
                    <select id="mapIbu" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom No HP / WA</label>
                    <select id="mapNoHp" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Alamat Domisili</label>
                    <select id="mapAlamat" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom No Paspor (Opsional)</label>
                    <select id="mapNoPaspor" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Exp Paspor (Opsional)</label>
                    <select id="mapExpPaspor" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Tempat Dikeluarkan Paspor (Opsional)</label>
                    <select id="mapTempatPaspor" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small fw-semibold">Kolom Tgl Dikeluarkan Paspor (Opsional)</label>
                    <select id="mapTglKeluaranPaspor" class="form-select form-select-sm"></select>
                </div>
                <div class="col-md-12 mb-2">
                    <label class="form-label small fw-semibold">Kolom Pilihan Program (Opsional)</label>
                    <select id="mapProgram" class="form-select form-select-sm"></select>
                </div>
            </div>
            <div class="form-text text-success mt-2"><i class="bi bi-check-circle-fill me-1"></i>Sisa kolom lainnya akan otomatis disimpan sebagai dokumen pendukung.</div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary px-4 d-none" id="btnProsesSync">Mulai Sinkronisasi</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Atur Tampilan Tabel -->
<div class="modal fade" id="modalAturTabel" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Atur Tampilan Kolom Tabel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Pilih kolom dari Google Spreadsheet Anda yang ingin ditampilkan pada tabel utama.</p>
        <div id="colCheckboxes" class="border rounded p-3 bg-light">
            <!-- Checkboxes will be dynamically populated here -->
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary px-4" id="btnSimpanKolom">Simpan Tampilan</button>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-md-4">
        <ul class="nav capel-nav-tabs mb-4" id="capelTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true" onclick="loadData('Pending')">
                    <i class="bi bi-hourglass-split"></i> Pending 
                    <span class="badge bg-danger rounded-pill ms-1" id="badgePending">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="false" onclick="loadData('Approved')">
                    <i class="bi bi-check2-circle"></i> Disetujui 
                    <span class="badge bg-success rounded-pill ms-1" id="badgeApproved">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false" onclick="loadData('Rejected')">
                    <i class="bi bi-x-circle"></i> Ditolak 
                    <span class="badge bg-secondary rounded-pill ms-1" id="badgeRejected">0</span>
                </button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <div class="d-flex justify-content-between align-items-center mb-3 capel-tab-action-header">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus text-primary"></i> Daftar Pendaftaran Baru
                        </h5>
                        <small class="text-muted">Data pendaftar masuk yang belum diverifikasi</small>
                    </div>
                    <div class="capel-tab-action-buttons">
                        <button class="btn btn-sm btn-outline-danger fw-semibold rounded-pill shadow-sm d-none" id="btnBulkDeletePending" onclick="bulkDelete('Pending')">
                            <i class="bi bi-trash me-1"></i>Hapus Terpilih (<span id="countSelectedPendingDel">0</span>)
                        </button>
                        <button class="btn btn-sm btn-success fw-semibold rounded-pill shadow-sm d-none" id="btnBulkApprove" onclick="bukaModalTerimaBulk()">
                            <i class="bi bi-check-all me-1"></i>Terima Terpilih (<span id="countSelected">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive capel-table-wrapper">
                    <table class="table table-hover align-middle">
                        <thead id="theadPending">
                            <!-- Populated via JS -->
                        </thead>
                        <tbody id="tbodyPending">
                            <tr><td colspan="10" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <div class="d-flex justify-content-between align-items-center mb-3 capel-tab-action-header">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-person-check text-success"></i> Daftar Disetujui
                        </h5>
                        <small class="text-muted">Pendaftar yang telah diterima sebagai santri</small>
                    </div>
                    <div class="capel-tab-action-buttons">
                        <button class="btn btn-sm btn-primary fw-semibold rounded-pill shadow-sm d-none" id="btnBulkReunduh" onclick="bulkReunduh()">
                            <i class="bi bi-cloud-arrow-down me-1"></i>Re-unduh Berkas (<span id="countSelectedApprovedReunduh">0</span>)
                        </button>
                        <button class="btn btn-sm btn-warning text-dark fw-semibold rounded-pill shadow-sm d-none" id="btnBulkCancel" onclick="bulkCancel()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Batalkan (<span id="countSelectedApprovedCancel">0</span>)
                        </button>
                        <button class="btn btn-sm btn-outline-danger fw-semibold rounded-pill shadow-sm d-none" id="btnBulkDeleteApproved" onclick="bulkDelete('Approved')">
                            <i class="bi bi-trash me-1"></i>Hapus (<span id="countSelectedApprovedDel">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive capel-table-wrapper">
                    <table class="table table-hover align-middle">
                        <thead id="theadApproved">
                            <!-- Populated via JS -->
                        </thead>
                        <tbody id="tbodyApproved"></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                <div class="d-flex justify-content-between align-items-center mb-3 capel-tab-action-header">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-person-x text-danger"></i> Daftar Ditolak
                        </h5>
                        <small class="text-muted">Pendaftar yang tidak memenuhi persyaratan</small>
                    </div>
                    <div class="capel-tab-action-buttons">
                        <button class="btn btn-sm btn-outline-danger fw-semibold rounded-pill shadow-sm d-none" id="btnBulkDeleteRejected" onclick="bulkDelete('Rejected')">
                            <i class="bi bi-trash me-1"></i>Hapus (<span id="countSelectedRejectedDel">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive capel-table-wrapper">
                    <table class="table table-hover align-middle">
                        <thead id="theadRejected">
                            <!-- Populated via JS -->
                        </thead>
                        <tbody id="tbodyRejected"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Terima -->
<div class="modal fade" id="modalTerima" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Terima Calon Pelajar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Terima untuk: <strong id="namaCapelTerima"></strong></p>
        <div id="defaultProgramContainer">
            <div class="alert alert-info py-2 mb-3">
                <i class="bi bi-info-circle me-1"></i> Sistem akan mendeteksi program dari data pendaftaran. Jika data program tidak ditemukan, sistem akan menggunakan pilihan default di bawah ini:
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="tipeCapel" id="tipeSyawwal" value="Program Penerimaan" checked>
              <label class="form-check-label fw-semibold" for="tipeSyawwal">Default: Program Penerimaan</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tipeCapel" id="tipePenampungan" value="Program Persiapan">
              <label class="form-check-label fw-semibold" for="tipePenampungan">Default: Program Persiapan</label>
            </div>
        </div>
        <hr>
        <?php if ($isSuperAdmin): ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Instansi Penempatan</label>
            <select class="form-select" id="instansiPilihan">
                <?php foreach ($instansiList as $inst): ?>
                    <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['nama_instansi']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Nilai default (Pondok, Kepengurusan) akan mengikuti instansi ini.</div>
        </div>
        <hr>
        <?php endif; ?>
        <p class="text-muted small mb-0"><i class="bi bi-info-circle"></i> Sistem akan secara otomatis mendownload berkas lampiran GDrive mereka di latar belakang.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success px-4" id="btnProsesTerima">Konfirmasi Terima</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Lihat Berkas -->
<div class="modal fade" id="modalLihatBerkas" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Daftar Berkas: <span id="namaSantriBerkas" class="text-primary"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3">
        <div id="berkasContainer"></div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>
<script>
let currentDraftIds = [];
let currentDataList = [];

function extractDriveId(url) {
    const match = url.match(/id=([a-zA-Z0-9_-]+)/) || url.match(/d\/([a-zA-Z0-9_-]+)/);
    return match ? match[1] : null;
}

const expectedFiles = [
    { name: 'Scan ID Paspor', keywords: ['scan id paspor', 'paspor', 'passport'] },
    { name: 'Scan IC Santri', keywords: ['scan ic (kartu identitas) santri', 'scan ic santri', 'kartu identitas santri', 'scan of id card', 'ktp santri', 'ic santri', 'kartu identitas', 'identity card'] },
    { name: 'Scan IC Ayah', keywords: ['scan ic ayah', 'father\'s scan of id card', 'father\'s scan', 'ktp ayah', 'ic ayah', 'identitas ayah'] },
    { name: 'Scan IC Ibu', keywords: ['scan ic ibu', 'mother\'s scan of id card', 'mother\'s scan', 'ktp ibu', 'ic ibu', 'identitas ibu'] },
    { name: 'Surat Beranak', keywords: ['surat beranak', 'surat kelahiran', 'birth certificate', 'akta lahir', 'akta kelahiran'] },
    { name: 'Pas Foto', keywords: ['pas foto', 'pasfoto', 'recent photograph', 'photograph', 'foto', 'photo'] },
    { name: 'Curriculum Vitae', keywords: ['curriculum vitae', 'cv', 'riwayat hidup'] },
    { name: 'Sertifikat Vaksin', keywords: ['scan sertifikat vaksin', 'sertifikat vaksin', 'kartu vaksin', 'vaccine', 'vaksin'] },
    { name: 'Asuransi Kesehatan', keywords: ['asuransi kesehatan', 'health insurance', 'asuransi', 'medical insurance'] },
    { name: 'Ijazah / Rapor', keywords: ['scan ijazah', 'rapor terakhir', 'diploma', 'report card', 'ijazah', 'rapor', 'transkrip', 'skl', 'skhun', 'certificate of education'] },
    { name: 'Surat Sehat', keywords: ['kesanggupan sehat', 'surat sehat', 'bebas penyakit menular', 'certificate of health', 'surat keterangan sehat', 'medical check up'] },
    { name: 'Kesanggupan Biaya', keywords: ['kesanggupan biaya', 'financial capability', 'surat kesanggupan', 'financial statement', 'pernyataan biaya'] },
    { name: 'Affidavit', keywords: ['affidavit', 'kewarganegaraan ganda'] },
    { name: 'Surat Pelajar Asing', keywords: ['pelajar asing', 'izin belajar', 'rekomendasi kementerian'] },
    { name: 'Kartu Keluarga', keywords: ['kartu keluarga', 'kk', 'family card'] },
    { name: 'Surat Rekomendasi', keywords: ['rekomendasi', 'surat rekomendasi', 'recommendation letter'] },
    { name: 'Surat Pernyataan', keywords: ['pernyataan', 'surat pernyataan', 'statement letter'] },
    { name: 'ITAS / Visa', keywords: ['itas', 'visa', 'izin tinggal', 'kitas'] }
];

function lihatBerkas(id) {
    const data = currentDataList.find(x => x.id == id);
    if (!data) return;
    
    let html = '';
    let parsedData = {};
    try { parsedData = JSON.parse(data.data_json || '{}'); } catch(e) {}
    
    const normalizedData = [];
    for (const [key, val] of Object.entries(parsedData)) {
        normalizedData.push({ key: key, lowerKey: key.toLowerCase(), val: val });
    }

    let missingHtml = '';
    let presentHtml = '';

    expectedFiles.forEach(expected => {
        let foundLinks = [];
        for (const item of normalizedData) {
            for (const kw of expected.keywords) {
                if (item.lowerKey.includes(kw) && typeof item.val === 'string' && item.val.includes('drive.google.com')) {
                    foundLinks = item.val.split(',');
                    break;
                }
            }
            if (foundLinks.length > 0) break;
        }
        
        if (foundLinks.length > 0) {
            presentHtml += `<div class="mb-3 bg-white p-3 rounded-4 shadow-sm border border-success border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-success mb-0"><i class="bi bi-check-circle-fill me-2"></i>${expected.name}</h6>
                        </div>`;
            foundLinks.forEach(link => {
                const fileId = extractDriveId(link.trim());
                if (fileId) {
                    presentHtml += `<div class="position-relative mb-2">
                        <div class="d-flex justify-content-end mb-1">
                            <a href="https://drive.google.com/file/d/${fileId}/view" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" style="font-size:0.75rem;"><i class="bi bi-box-arrow-up-right me-1"></i>Tab Baru</a>
                        </div>
                        <iframe src="https://drive.google.com/file/d/${fileId}/preview" width="100%" class="preview-iframe-berkas border rounded-3 w-100 shadow-sm" allow="autoplay"></iframe>
                    </div>`;
                } else {
                    presentHtml += `<a href="${link.trim()}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill mb-2"><i class="bi bi-box-arrow-up-right me-1"></i> Buka File</a>`;
                }
            });
            presentHtml += `</div>`;
        } else {
            missingHtml += `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 me-2 mb-2 px-3 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> ${expected.name}</span>`;
        }
    });

    if (missingHtml !== '') {
        missingHtml = `<div class="mb-3 p-3 rounded-4 shadow-sm border border-danger border-opacity-25 bg-white">
                        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Berkas Kosong / Belum Dilampirkan:</h6>
                        <div class="d-flex flex-wrap">${missingHtml}</div>
                       </div>`;
    }

    html += missingHtml + presentHtml;
    
    for (const item of normalizedData) {
        let isExpected = false;
        for (const expected of expectedFiles) {
            for (const kw of expected.keywords) {
                if (item.lowerKey.includes(kw)) {
                    isExpected = true;
                    break;
                }
            }
            if (isExpected) break;
        }
        
        if (!isExpected && typeof item.val === 'string' && item.val.includes('drive.google.com')) {
            const links = item.val.split(',');
            html += `<div class="mb-3 bg-white p-3 rounded-4 shadow-sm border border-info border-opacity-25">
                        <h6 class="fw-bold text-info mb-2"><i class="bi bi-info-circle-fill me-2"></i>Dokumen Tambahan: ${item.key}</h6>`;
            links.forEach(link => {
                const fileId = extractDriveId(link.trim());
                if (fileId) {
                    html += `<div class="position-relative mb-2">
                        <div class="d-flex justify-content-end mb-1">
                            <a href="https://drive.google.com/file/d/${fileId}/view" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" style="font-size:0.75rem;"><i class="bi bi-box-arrow-up-right me-1"></i>Tab Baru</a>
                        </div>
                        <iframe src="https://drive.google.com/file/d/${fileId}/preview" width="100%" class="preview-iframe-berkas border rounded-3 w-100 shadow-sm" allow="autoplay"></iframe>
                    </div>`;
                } else {
                    html += `<a href="${link.trim()}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill mb-2"><i class="bi bi-box-arrow-up-right me-1"></i> Buka File</a>`;
                }
            });
            html += `</div>`;
        }
    }
    
    document.getElementById('berkasContainer').innerHTML = `<div class="p-2">${html}</div>`;
    document.getElementById('namaSantriBerkas').textContent = data.nama_lengkap;
    new bootstrap.Modal(document.getElementById('modalLihatBerkas')).show();
}

function buildThead(status) {
    let checkId = 'chkAll' + status;
    let onchange = `toggleAll('${status}', this)`;
    
    let tr1 = `<tr>
        <th style="width: 40px;" class="text-center"><input type="checkbox" class="form-check-input" id="${checkId}" onchange="${onchange}"></th>
        <th class="text-nowrap">Tanggal Submit</th>
        <th class="text-nowrap">Nama Lengkap</th>`;
        
    let tr2 = `<tr class="search-row">
        <th></th>
        <th><input type="text" class="form-control form-control-sm column-search" data-col="1" placeholder="Cari..."></th>
        <th><input type="text" class="form-control form-control-sm column-search" data-col="2" placeholder="Cari..."></th>`;
        
    let colIndex = 3;
    displayColumns.forEach(col => {
        tr1 += `<th class="text-nowrap">${col}</th>`;
        tr2 += `<th><input type="text" class="form-control form-control-sm column-search" data-col="${colIndex}" placeholder="Cari..."></th>`;
        colIndex++;
    });
    
    tr1 += `<th class="text-nowrap capel-sticky-action">${status === 'Pending' ? 'Aksi' : 'Status & Aksi'}</th></tr>`;
    tr2 += `<th class="capel-sticky-action"></th></tr>`;
    
    return tr1 + tr2;
}

function refreshAllMetrics() {
    ['Pending', 'Approved', 'Rejected'].forEach(st => {
        let u = '<?= API_URL ?>/api/capel/list?status=' + st;
        <?php if ($isSuperAdmin && $selectedInstansiId): ?>
        u += '&instansi_id=<?= $selectedInstansiId ?>';
        <?php endif; ?>
        fetch(u, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                const b = document.getElementById('badge' + st);
                const s = document.getElementById('statCard' + st);
                if (b) b.textContent = Array.isArray(d) ? d.length : 0;
                if (s) s.textContent = Array.isArray(d) ? d.length : 0;
            }).catch(()=>{});
    });
}

function loadData(status) {
    const thead = document.getElementById('thead' + status);
    const tbody = document.getElementById('tbody' + status);
    
    // Generate thead dynamically
    thead.innerHTML = buildThead(status);
    
    tbody.innerHTML = `<tr><td colspan="${displayColumns.length + 4}" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...</td></tr>`;
    
    let fetchUrl = '<?= API_URL ?>/api/capel/list?status=' + status;
    <?php if ($isSuperAdmin && $selectedInstansiId): ?>
    fetchUrl += '&instansi_id=<?= $selectedInstansiId ?>';
    <?php endif; ?>

    fetch(fetchUrl, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const count = Array.isArray(data) ? data.length : 0;
        if (status === 'Pending') {
            document.getElementById('badgePending').textContent = count;
            document.getElementById('statCardPending').textContent = count;
        } else if (status === 'Approved') {
            document.getElementById('badgeApproved').textContent = count;
            document.getElementById('statCardApproved').textContent = count;
        } else if (status === 'Rejected') {
            document.getElementById('badgeRejected').textContent = count;
            document.getElementById('statCardRejected').textContent = count;
        }
        
        if (count === 0) {
            tbody.innerHTML = `<tr><td colspan="${displayColumns.length + 4}" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>Tidak ada data pendaftaran ${status}</td></tr>`;
            return;
        }
        
        currentDataList = data;

        let html = '';
        data.forEach(d => {
            let dupBadge = '<span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1"><i class="bi bi-shield-check me-1"></i>Aman</span>';
            if (d.potential_duplicates && d.potential_duplicates.length > 0) {
                dupBadge = `<span class="badge bg-danger rounded-pill px-2 py-1" title="Ada ${d.potential_duplicates.length} data mirip di master santri"><i class="bi bi-exclamation-triangle"></i> Mirip (${d.potential_duplicates.length})</span>`;
            }

            let parsedData = {};
            try { parsedData = JSON.parse(d.data_json); } catch(e) {}
            
            // Program Capel Badge
            const findVal = function(keys) {
                for (let k in parsedData) {
                    for (let key of keys) {
                        if (k.toLowerCase().includes(key) && parsedData[k] && parsedData[k].trim() !== '') {
                            return parsedData[k].trim();
                        }
                    }
                }
                return '-';
            };
            
            let programBadge = '';
            let rawProgram = (defaultMapping && defaultMapping.col_program && parsedData[defaultMapping.col_program]) 
                            ? parsedData[defaultMapping.col_program].trim() 
                            : findVal(['calon pelajar', 'program capel', 'pilihan program']);
                            
            if (rawProgram.toLowerCase().includes('program penerimaan') || rawProgram.toLowerCase().includes('syawwal')) {
                programBadge = '<span class="badge bg-primary rounded-pill mt-1">Program Penerimaan</span>';
            } else if (rawProgram.toLowerCase().includes('program persiapan') || rawProgram.toLowerCase().includes('penampungan')) {
                programBadge = '<span class="badge bg-warning text-dark rounded-pill mt-1">Program Persiapan</span>';
            } else if (rawProgram !== '-' && rawProgram.trim() !== '') {
                programBadge = `<span class="badge bg-secondary rounded-pill mt-1">${rawProgram}</span>`;
            } else {
                programBadge = `<span class="badge bg-light text-secondary border rounded-pill mt-1">Program Belum Diatur</span>`;
            }

            // Instansi Badge (only for super admin)
            let instansiBadge = '';
            if (d.nama_instansi && <?= $isSuperAdmin ? 'true' : 'false' ?>) {
                instansiBadge = `<br><span class="badge bg-info text-dark mt-1 border border-info border-opacity-50 rounded-pill"><i class="bi bi-building me-1"></i>${d.nama_instansi}</span>`;
            }

            let rowHtml = `<tr>
                <td class="text-center"><input type="checkbox" class="form-check-input chk-${status} row-cb" value="${d.id}" data-nama="${d.nama_lengkap}"></td>
                <td class="text-nowrap text-muted" style="font-size:0.8rem;">${d.timestamp}</td>
                <td class="fw-bold text-nowrap">${d.nama_lengkap}<br>${programBadge}${instansiBadge}</td>`;

            displayColumns.forEach(col => {
                let val = parsedData[col] !== undefined ? parsedData[col] : '';
                
                // Keep backward compatibility for the fallback default set
                if (col === 'Kewarganegaraan' && val === '') {
                    val = (defaultMapping && defaultMapping.col_kwn && parsedData[defaultMapping.col_kwn]) ? parsedData[defaultMapping.col_kwn] : findVal(['kewarganegaraan', 'nationality']);
                } else if (col === 'Tempat, Tgl Lahir' && val === '') {
                    const tmpt = (defaultMapping && defaultMapping.col_tempatlahir && parsedData[defaultMapping.col_tempatlahir]) ? parsedData[defaultMapping.col_tempatlahir] : findVal(['tempat lahir', 'place of birth']);
                    val = tmpt + ',<br>' + (d.tanggal_lahir || '-');
                } else if (col === 'Orang Tua' && val === '') {
                    const a = (defaultMapping && defaultMapping.col_ayah && parsedData[defaultMapping.col_ayah]) ? parsedData[defaultMapping.col_ayah] : findVal(['nama lengkap ayah', 'nama ayah', 'ayah', 'father', 'father name']);
                    const i = (defaultMapping && defaultMapping.col_ibu && parsedData[defaultMapping.col_ibu]) ? parsedData[defaultMapping.col_ibu] : findVal(['nama lengkap ibu', 'nama ibu', 'ibu', 'mother', 'mother name']);
                    val = `A: ${a}<br>I: ${i}`;
                } else if (col === 'Kontak' && val === '') {
                    const e = (defaultMapping && defaultMapping.col_email && parsedData[defaultMapping.col_email]) ? parsedData[defaultMapping.col_email] : findVal(['email address', 'email']);
                    const h = (defaultMapping && defaultMapping.col_nohp && parsedData[defaultMapping.col_nohp]) ? parsedData[defaultMapping.col_nohp] : findVal(['nomor hp wali', 'nomor hp', 'wa aktif', 'phone', 'telepon']);
                    val = `<i class="bi bi-whatsapp text-success"></i> ${h}<br><i class="bi bi-envelope text-primary"></i> ${e}`;
                } else if (col === 'Info Paspor' && val === '') {
                    const p = findVal(['nomor id paspor', 'identity number']).toUpperCase();
                    const exp = findVal(['tanggal berakhir paspor', 'date of expiry']);
                    val = `<span class="fw-medium">${p}</span><br><small class="text-muted text-nowrap">Exp: ${exp}</small>`;
                } else if (col === 'Alamat' && val === '') {
                    const a = (defaultMapping && defaultMapping.col_alamat && parsedData[defaultMapping.col_alamat]) ? parsedData[defaultMapping.col_alamat] : findVal(['alamat rumah', 'alamat']);
                    val = `<div style="min-width: 150px; max-height: 3.5rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="${a}">${a}</div>`;
                }

                rowHtml += `<td>${val}</td>`;
            });
            let waLink = '';
            const noHpRaw = (defaultMapping && defaultMapping.col_nohp && parsedData[defaultMapping.col_nohp]) ? parsedData[defaultMapping.col_nohp] : findVal(['nomor hp wali', 'nomor hp', 'wa aktif', 'phone', 'telepon']);
            if (noHpRaw && noHpRaw !== '-' && noHpRaw.trim() !== '') {
                const matches = noHpRaw.match(/\+?\d[\d\-\s]{8,}/g);
                if (matches) {
                    matches.forEach((m, idx) => {
                        let cleanNo = m.replace(/[^\d+]/g, '');
                        if (cleanNo.startsWith('0')) cleanNo = '62' + cleanNo.substring(1);
                        cleanNo = cleanNo.replace('+', '');
                        if (cleanNo.length >= 9) {
                            let label = matches.length > 1 ? ` WA ${idx + 1}` : ` WA`;
                            waLink += `<a href="https://wa.me/${cleanNo}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 shadow-sm" title="Chat WhatsApp ${cleanNo}"><i class="bi bi-whatsapp"></i>${label}</a>`;
                        }
                    });
                }
            }

            if (status === 'Pending') {
                rowHtml += `<td class="text-nowrap capel-sticky-action">
                        <div class="mb-1">${dupBadge}</div>
                        <div class="capel-action-group">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 shadow-sm" onclick="lihatBerkas(${d.id})"><i class="bi bi-folder2-open me-1"></i>Berkas</button>
                            ${waLink}
                            <button class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm" onclick="bukaModalTerima(${d.id}, '${d.nama_lengkap.replace(/'/g, "\\'")}')"><i class="bi bi-check-lg me-1"></i>Terima</button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 shadow-sm" onclick="tolak(${d.id})"><i class="bi bi-x-lg me-1"></i>Tolak</button>
                        </div>
                    </td>
                </tr>`;
            } else if (status === 'Approved') {
                rowHtml += `<td class="text-nowrap capel-sticky-action">
                        <span class="badge bg-success mb-1 px-2 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i>${d.status_approval}</span><br>
                        ${d.final_status_santri ? `<span class="badge bg-info text-dark mb-1 px-2 py-1 rounded-pill">${d.final_status_santri}</span><br>` : ''}
                        <div class="capel-action-group mt-1">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 shadow-sm" onclick="reunduhBerkas(${d.id})" title="Re-unduh Berkas"><i class="bi bi-cloud-arrow-down me-1"></i>Re-unduh</button>
                        </div>
                    </td>
                </tr>`;
            } else {
                rowHtml += `<td class="text-nowrap capel-sticky-action">
                        <span class="badge bg-secondary mb-1 px-2 py-1 rounded-pill">${d.status_approval}</span><br>
                        ${d.final_status_santri ? `<span class="badge bg-info text-dark px-2 py-1 rounded-pill">${d.final_status_santri}</span>` : ''}
                    </td>
                </tr>`;
            }
            html += rowHtml;
        });
        tbody.innerHTML = html;
        
        if (status === 'Pending') {
            attachCheckboxListeners('Pending', 'btnBulkApprove', 'countSelected', 'btnBulkDeletePending', 'countSelectedPendingDel');
        } else if (status === 'Approved') {
            attachCheckboxListeners('Approved', 'btnBulkReunduh', 'countSelectedApprovedReunduh', 'btnBulkCancel', 'countSelectedApprovedCancel', 'btnBulkDeleteApproved', 'countSelectedApprovedDel');
        } else if (status === 'Rejected') {
            attachCheckboxListeners('Rejected', 'btnBulkDeleteRejected', 'countSelectedRejectedDel');
        }
        
        // Apply local filtering
        attachSearchFilters(status);
    })
    .catch(err => {
        tbody.innerHTML = `<tr><td colspan="${displayColumns.length + 4}" class="text-center text-danger py-4">Gagal memuat data</td></tr>`;
    });
}

function attachCheckboxListeners(status, ...btnPairs) {
    const chkAll = document.getElementById('chkAll' + status);
    const chks = document.querySelectorAll('.chk-' + status);

    const updateBtn = () => {
        const checked = document.querySelectorAll('.chk-' + status + ':checked');
        const count = checked.length;
        for (let i = 0; i < btnPairs.length; i += 2) {
            const btn = btnPairs[i] ? document.getElementById(btnPairs[i]) : null;
            const cnt = btnPairs[i+1] ? document.getElementById(btnPairs[i+1]) : null;
            if (btn) {
                if (count > 0) {
                    btn.classList.remove('d-none');
                    if (cnt) cnt.textContent = count;
                } else {
                    btn.classList.add('d-none');
                }
            }
        }
    };

    if(chkAll) {
        chkAll.checked = false;
        chkAll.addEventListener('change', function() {
            chks.forEach(c => {
                if (c.closest('tr').style.display !== 'none') {
                    c.checked = this.checked;
                }
            });
            updateBtn();
        });
    }

    chks.forEach(c => c.addEventListener('change', updateBtn));
    updateBtn();
}

function attachSearchFilters(status) {
    const tabPane = document.getElementById(status.toLowerCase());
    if (!tabPane) return;
    
    const searchInputs = tabPane.querySelectorAll('.column-search');
    const tbody = document.getElementById('tbody' + status);
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const filters = Array.from(searchInputs).map(inp => ({
                col: parseInt(inp.getAttribute('data-col')),
                val: inp.value.toLowerCase().trim()
            })).filter(f => f.val !== '');
            
            Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
                if(tr.querySelector('td[colspan]')) return; // skip loading/empty rows
                
                let isMatch = true;
                filters.forEach(f => {
                    const td = tr.querySelectorAll('td')[f.col];
                    if (td) {
                        const cellText = td.textContent.toLowerCase();
                        if (!cellText.includes(f.val)) {
                            isMatch = false;
                        }
                    }
                });
                
                tr.style.display = isMatch ? '' : 'none';
                // Uncheck hidden rows
                if (!isMatch) {
                    const cb = tr.querySelector('.chk-' + status);
                    if (cb && cb.checked) {
                        cb.checked = false;
                        cb.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
    });
}

function bulkDelete(status) {
    const checked = document.querySelectorAll('.chk-' + status + ':checked');
    if (checked.length === 0) return;
    
    const ids = Array.from(checked).map(c => c.value);
    
    Swal.fire({
        title: 'Hapus ' + ids.length + ' Data?',
        text: status === 'Approved' ? "PERINGATAN: Karena ini sudah disetujui, maka data Santri yang terbuat (termasuk Berkas dan Paspor) akan DICABUT / DIHAPUS agar sistem kembali bersih!" : "Data ini akan dihapus permanen dari antrian.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus Permanen'
    }).then((result) => {
        if (result.isConfirmed) {
            executeBulkAction('<?= API_URL ?>/api/capel/bulk-delete', ids, status, 'Dihapus!');
        }
    });
}

function bulkCancel() {
    const checked = document.querySelectorAll('.chk-Approved:checked');
    if (checked.length === 0) return;
    
    const ids = Array.from(checked).map(c => c.value);
    
    Swal.fire({
        title: 'Batalkan ' + ids.length + ' Persetujuan?',
        text: "Data Santri yang terbuat akan DICABUT, dan pendaftar ini akan dikembalikan ke tab Pending.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        confirmButtonText: 'Ya, Batalkan'
    }).then((result) => {
        if (result.isConfirmed) {
            executeBulkAction('<?= API_URL ?>/api/capel/bulk-cancel', ids, 'Approved', 'Dibatalkan!', 'Pending');
        }
    });
}

function executeBulkAction(url, ids, currentStatus, successTitle, reloadStatus = null) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            Swal.fire(successTitle, res.message, 'success');
            loadData(currentStatus);
            if (reloadStatus) loadData(reloadStatus);
            refreshAllMetrics();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal mengeksekusi aksi.', 'error');
    });
}

function reunduhBerkas(id) {
    Swal.fire({
        title: 'Re-unduh Berkas?',
        text: "Sistem akan mengunduh ulang foto, paspor, itas, dan berkas pendaftaran calon santri ini langsung ke penyimpanan server.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: '<i class="bi bi-cloud-arrow-down me-1"></i>Ya, Re-unduh Sekarang',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mendaftarkan berkas ke antrean unduhan',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('<?= API_URL ?>/api/capel/reunduh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ ids: [id] })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire('Berhasil!', res.message || 'Antrean re-unduh telah dibuat. Pengunduhan berjalan di latar belakang.', 'success');
                } else {
                    Swal.fire('Error', res.message || 'Gagal memulai re-unduh.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Gagal memproses re-unduh berkas.', 'error');
            });
        }
    });
}

function bulkReunduh() {
    const checked = document.querySelectorAll('.chk-Approved:checked');
    if (checked.length === 0) return;

    const ids = Array.from(checked).map(c => c.value);

    Swal.fire({
        title: 'Re-unduh ' + ids.length + ' Santri Terpilih?',
        text: "Sistem akan mengunduh ulang seluruh berkas santri terpilih ke folder penyimpanan server.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: '<i class="bi bi-cloud-arrow-down me-1"></i>Ya, Re-unduh Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mendaftarkan berkas ke antrean unduhan',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('<?= API_URL ?>/api/capel/reunduh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire('Berhasil!', res.message || 'Antrean re-unduh telah dibuat. Pengunduhan berjalan di latar belakang.', 'success');
                } else {
                    Swal.fire('Error', res.message || 'Gagal memulai re-unduh.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Gagal memproses re-unduh berkas.', 'error');
            });
        }
    });
}

function hasMissingProgram(ids) {
    for (let id of ids) {
        const data = currentDataList.find(x => x.id == id);
        if (data && data.data_json) {
            let parsedData = {};
            try { parsedData = JSON.parse(data.data_json); } catch(e) {}
            
            let tipeVal = '';
            for (let k in parsedData) {
                if (k.toLowerCase().includes('calon pelajar') || k.toLowerCase().includes('program capel') || k.toLowerCase().includes('pilihan program')) {
                    tipeVal = String(parsedData[k]).toLowerCase();
                    break;
                }
            }
            if (!tipeVal.includes('persiapan') && !tipeVal.includes('penampungan') && !tipeVal.includes('penerimaan') && !tipeVal.includes('syawwal')) {
                return true;
            }
        } else {
            return true;
        }
    }
    return false;
}

function bukaModalTerima(id, nama) {
    currentDraftIds = [id];
    document.getElementById('namaCapelTerima').textContent = nama;
    
    if (hasMissingProgram(currentDraftIds)) {
        document.getElementById('defaultProgramContainer').style.display = 'block';
    } else {
        document.getElementById('defaultProgramContainer').style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('modalTerima')).show();
}

function bukaModalTerimaBulk() {
    const checked = document.querySelectorAll('.chk-Pending:checked');
    if (checked.length === 0) return;
    
    currentDraftIds = Array.from(checked).map(c => c.value);
    const names = Array.from(checked).map(c => c.dataset.nama).join(', ');
    
    document.getElementById('namaCapelTerima').textContent = checked.length + ' Pendaftar: ' + (names.length > 50 ? names.substring(0, 50) + '...' : names);
    
    if (hasMissingProgram(currentDraftIds)) {
        document.getElementById('defaultProgramContainer').style.display = 'block';
    } else {
        document.getElementById('defaultProgramContainer').style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('modalTerima')).show();
}

document.getElementById('btnProsesTerima').addEventListener('click', function() {
    if (currentDraftIds.length === 0) return;
    const btn = this;
    const tipe = document.querySelector('input[name="tipeCapel"]:checked').value;
    
    let instansiId = null;
    const instSelect = document.getElementById('instansiPilihan');
    if (instSelect) {
        instansiId = instSelect.value;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    fetch('<?= API_URL ?>/api/capel/bulk-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({ ids: currentDraftIds, tipe_capel: tipe, instansi_id: instansiId })
    })
    .then(r => r.json())
    .then(res => {
        bootstrap.Modal.getInstance(document.getElementById('modalTerima')).hide();
        if(res.success) {
            Swal.fire('Berhasil!', res.message || 'Pendaftaran diterima.', 'success');
            loadData('Pending');
            refreshAllMetrics();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal memproses data.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Konfirmasi Terima';
    });
});

function tolak(id) {
    Swal.fire({
        title: 'Tolak Pendaftaran?',
        text: "Data ini akan diabaikan dan tidak dimasukkan ke Data Santri.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Tolak'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= API_URL ?>/api/capel/' + id + '/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                }
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire('Ditolak!', 'Pendaftaran berhasil ditolak.', 'success');
                    loadData('Pending');
                    refreshAllMetrics();
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadData('Pending');
    refreshAllMetrics();
});

let defaultMapping = <?= $defaultMappingData ? $defaultMappingData : 'null' ?>;
let spreadsheetHeaders = <?= $spreadsheetHeaders ? $spreadsheetHeaders : '[]' ?>;
let displayColumns = <?= $displayColumns ? $displayColumns : '[]' ?>;

if (!Array.isArray(displayColumns) || displayColumns.length === 0) {
    displayColumns = ['Kewarganegaraan', 'Tempat, Tgl Lahir', 'Orang Tua', 'Kontak', 'Info Paspor', 'Alamat'];
}

function initAturTabel() {
    const container = document.getElementById('colCheckboxes');
    if (!container) return;
    
    if (spreadsheetHeaders.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3"><i class="bi bi-info-circle me-1"></i>Belum ada data header. Silakan lakukan Sinkronisasi terlebih dahulu.</div>';
        document.getElementById('btnSimpanKolom').disabled = true;
        return;
    }
    
    let html = '<div class="row">';
    spreadsheetHeaders.forEach(h => {
        const isChecked = displayColumns.includes(h) ? 'checked' : '';
        html += `
        <div class="col-md-6 mb-2">
            <div class="form-check">
                <input class="form-check-input col-selector-cb" type="checkbox" value="${h}" id="chkCol_${h.replace(/[^a-zA-Z0-9]/g,'')}" ${isChecked}>
                <label class="form-check-label" for="chkCol_${h.replace(/[^a-zA-Z0-9]/g,'')}">
                    ${h}
                </label>
            </div>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

document.getElementById('modalAturTabel').addEventListener('show.bs.modal', initAturTabel);

document.getElementById('btnSimpanKolom').addEventListener('click', function() {
    const checked = Array.from(document.querySelectorAll('.col-selector-cb:checked')).map(cb => cb.value);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const btn = this;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    
    fetch('<?= API_URL ?>/api/capel/save-display-columns', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ 
            columns: checked,
            instansi_id: <?= $selectedInstansiId ?? '0' ?> 
        })
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            displayColumns = checked;
            bootstrap.Modal.getInstance(document.getElementById('modalAturTabel')).hide();
            Swal.fire('Berhasil', 'Tampilan kolom berhasil diperbarui', 'success');
            // Reload all current tabs
            loadData('Pending');
            loadData('Approved');
            loadData('Rejected');
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Simpan Tampilan';
    });
});

document.getElementById('btnCekHeader').addEventListener('click', function() {
    const btn = this;
    const spreadsheetId = document.getElementById('syncSpreadsheetId').value.trim();
    const range = document.getElementById('syncRange').value.trim();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!spreadsheetId || !range) {
        Swal.fire('Error', 'ID Spreadsheet dan Range tidak boleh kosong', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengambil...';

    fetch('<?= API_URL ?>/api/capel/sync-headers', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ 
            spreadsheet_id: spreadsheetId, 
            sheet_range: range,
            instansi_id: <?= $selectedInstansiId ?? '0' ?>
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const headers = res.headers;
            const sels = ['mapEmail', 'mapNama', 'mapTglLahir', 'mapKwn', 'mapTempatLahir', 'mapAyah', 'mapIbu', 'mapNoHp', 'mapAlamat', 'mapNoPaspor', 'mapExpPaspor', 'mapTempatPaspor', 'mapTglKeluaranPaspor', 'mapProgram'];
            
            sels.forEach(selId => {
                const el = document.getElementById(selId);
                el.innerHTML = '<option value="">-- Pilih Kolom --</option>';
                headers.forEach(h => {
                    const opt = document.createElement('option');
                    opt.value = h;
                    opt.textContent = h;
                    el.appendChild(opt);
                });
            });

            // Pre-select if we have defaultMapping
            if (defaultMapping) {
                document.getElementById('mapEmail').value = defaultMapping.col_email || '';
                document.getElementById('mapNama').value = defaultMapping.col_nama || '';
                document.getElementById('mapTglLahir').value = defaultMapping.col_tgllahir || '';
                document.getElementById('mapKwn').value = defaultMapping.col_kwn || '';
                document.getElementById('mapTempatLahir').value = defaultMapping.col_tempatlahir || '';
                document.getElementById('mapAyah').value = defaultMapping.col_ayah || '';
                document.getElementById('mapIbu').value = defaultMapping.col_ibu || '';
                document.getElementById('mapNoHp').value = defaultMapping.col_nohp || '';
                document.getElementById('mapAlamat').value = defaultMapping.col_alamat || '';
                document.getElementById('mapNoPaspor').value = defaultMapping.col_no_paspor || '';
                document.getElementById('mapExpPaspor').value = defaultMapping.col_exp_paspor || '';
                document.getElementById('mapTempatPaspor').value = defaultMapping.col_tempat_paspor || '';
                document.getElementById('mapTglKeluaranPaspor').value = defaultMapping.col_tgl_keluaran_paspor || '';
                document.getElementById('mapProgram').value = defaultMapping.col_program || '';
            } else {
                // Auto-guess to be helpful
                headers.forEach(h => {
                    const lh = h.toLowerCase();
                    if (lh.includes('email') && !lh.includes('bila ada')) {
                        if(!document.getElementById('mapEmail').value) document.getElementById('mapEmail').value = h;
                    }
                    if (lh.includes('nama lengkap') || lh.includes('full name')) {
                        if(!lh.includes('ayah') && !lh.includes('ibu') && !lh.includes('father') && !lh.includes('mother')) {
                            document.getElementById('mapNama').value = h;
                        }
                    }
                    if (lh.includes('tanggal lahir') || lh.includes('tgl lahir') || lh.includes('date of birth')) {
                        if(!lh.includes('ayah') && !lh.includes('ibu')) {
                            document.getElementById('mapTglLahir').value = h;
                        }
                    }
                    if (lh.includes('kewarganegaraan') || lh.includes('nationality')) {
                        document.getElementById('mapKwn').value = h;
                    }
                    if (lh.includes('tempat lahir') || lh.includes('place of birth')) {
                        document.getElementById('mapTempatLahir').value = h;
                    }
                    if ((lh.includes('ayah') || lh.includes('father')) && (lh.includes('nama') || lh.includes('name'))) {
                        document.getElementById('mapAyah').value = h;
                    }
                    if ((lh.includes('ibu') || lh.includes('mother')) && (lh.includes('nama') || lh.includes('name'))) {
                        document.getElementById('mapIbu').value = h;
                    }
                    if (lh.includes('hp') || lh.includes('wa') || lh.includes('phone') || lh.includes('telepon')) {
                        document.getElementById('mapNoHp').value = h;
                    }
                    if (lh.includes('alamat') || lh.includes('address')) {
                        document.getElementById('mapAlamat').value = h;
                    }
                    if (lh.includes('no paspor') || lh.includes('nomor paspor') || lh.includes('passport number')) {
                        document.getElementById('mapNoPaspor').value = h;
                    }
                    if (lh.includes('exp paspor') || lh.includes('berakhir') || lh.includes('expiry') || lh.includes('expired')) {
                        document.getElementById('mapExpPaspor').value = h;
                    }
                    if ((lh.includes('tempat') || lh.includes('place')) && (lh.includes('keluar') || lh.includes('paspor') || lh.includes('issue'))) {
                        if (!document.getElementById('mapTempatPaspor').value) document.getElementById('mapTempatPaspor').value = h;
                    }
                    if ((lh.includes('tanggal') || lh.includes('tgl') || lh.includes('date')) && (lh.includes('keluar') || lh.includes('paspor') || lh.includes('issue'))) {
                        if (!document.getElementById('mapTglKeluaranPaspor').value) document.getElementById('mapTglKeluaranPaspor').value = h;
                    }
                    if (lh.includes('program') || lh.includes('pilihan')) {
                        document.getElementById('mapProgram').value = h;
                    }
                });
            }

            document.getElementById('mappingSection').classList.remove('d-none');
            document.getElementById('btnProsesSync').classList.remove('d-none');
        } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search me-2"></i>Cek & Atur Pemetaan Kolom';
    });
});

// Auto click check header if there is default mapping and the modal opens
document.getElementById('modalSync').addEventListener('show.bs.modal', function () {
    const spreadsheetId = document.getElementById('syncSpreadsheetId').value.trim();
    if (spreadsheetId && defaultMapping) {
        setTimeout(() => document.getElementById('btnCekHeader').click(), 300);
    }
});
document.getElementById('btnProsesSync').addEventListener('click', function() {
    const btn = this;
    const spreadsheetId = document.getElementById('syncSpreadsheetId').value.trim();
    const range = document.getElementById('syncRange').value.trim();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const colEmail = document.getElementById('mapEmail').value;
    const colNama = document.getElementById('mapNama').value;
    const colTglLahir = document.getElementById('mapTglLahir').value;
    const colKwn = document.getElementById('mapKwn').value;
    const colTempatLahir = document.getElementById('mapTempatLahir').value;
    const colAyah = document.getElementById('mapAyah').value;
    const colIbu = document.getElementById('mapIbu').value;
    const colNoHp = document.getElementById('mapNoHp').value;
    const colAlamat = document.getElementById('mapAlamat').value;
    const colNoPaspor = document.getElementById('mapNoPaspor').value;
    const colExpPaspor = document.getElementById('mapExpPaspor').value;
    const colTempatPaspor = document.getElementById('mapTempatPaspor').value;
    const colTglKeluaranPaspor = document.getElementById('mapTglKeluaranPaspor').value;
    const colProgram = document.getElementById('mapProgram').value;

    if (!spreadsheetId || !range) {
        Swal.fire('Error', 'ID Spreadsheet dan Range tidak boleh kosong', 'warning');
        return;
    }
    
    if (!colEmail || !colNama || !colTglLahir) {
        Swal.fire('Error', 'Silakan petakan minimal kolom Email, Nama, dan Tanggal Lahir.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyinkronkan...';

    fetch('<?= API_URL ?>/api/capel/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({ 
            spreadsheet_id: spreadsheetId, 
            sheet_range: range,
            col_email: colEmail,
            col_nama: colNama,
            col_tgllahir: colTglLahir,
            col_kwn: colKwn,
            col_tempatlahir: colTempatLahir,
            col_ayah: colAyah,
            col_ibu: colIbu,
            col_nohp: colNoHp,
            col_alamat: colAlamat,
            col_no_paspor: colNoPaspor,
            col_exp_paspor: colExpPaspor,
            col_tempat_paspor: colTempatPaspor,
            col_tgl_keluaran_paspor: colTglKeluaranPaspor,
            col_program: colProgram,
            instansi_id: <?= $selectedInstansiId ?? '0' ?>
        })
    })
    .then(r => r.json())
    .then(res => {
        bootstrap.Modal.getInstance(document.getElementById('modalSync')).hide();
        if (res.success) {
            Swal.fire('Selesai!', res.message, 'success');
            loadData('Pending');
            refreshAllMetrics();
        } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat menarik data', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal menghubungi server', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Mulai Sinkronisasi';
    });
});
</script>
