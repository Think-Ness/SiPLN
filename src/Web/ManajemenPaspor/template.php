<?php
/** @var bool $isSuperAdmin */
?>

<style>
    /* Header & Action Buttons */
    .page-header-responsive .btn {
        transition: all 0.2s ease;
    }
    .page-header-responsive .btn:hover {
        transform: translateY(-2px);
    }

    /* Summary / Rekap Cards */
    .paspor-rekap-card {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 16px !important;
        background: #ffffff;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        user-select: none;
        position: relative;
        overflow: hidden;
    }
    .paspor-rekap-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06) !important;
    }
    .paspor-rekap-card.active-filter {
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2) !important;
    }
    .paspor-rekap-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Modern Column Search */
    .col-search, .log-search {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 0.75rem !important;
        padding: 4px 8px !important;
        transition: all 0.2s ease !important;
        background-color: #ffffff !important;
    }
    .col-search:focus, .log-search:focus {
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15) !important;
        outline: none !important;
    }

    /* Filter Pill Container */
    .filter-pill-container {
        display: flex;
        align-items: center;
        gap: 6px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 2px;
    }
    .filter-btn-pill {
        border-radius: 20px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        padding: 6px 14px !important;
        white-space: nowrap !important;
        transition: all 0.2s ease !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        color: #64748b !important;
        text-decoration: none !important;
    }
    .filter-btn-pill:hover {
        background: #f1f5f9 !important;
        color: #334155 !important;
    }
    .filter-btn-pill.active {
        background: #0ea5e9 !important;
        color: #ffffff !important;
        border-color: #0ea5e9 !important;
        box-shadow: 0 2px 6px rgba(14, 165, 233, 0.3) !important;
    }

    /* Responsive adjustments for Mobile */
    @media (max-width: 768px) {
        .page-header-responsive {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        .page-header-responsive > div:last-child {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            width: 100% !important;
        }
        .page-header-responsive .btn,
        .page-header-responsive .dropdown,
        .page-header-responsive .dropdown > .btn {
            width: 100% !important;
            justify-content: center !important;
        }
        .paspor-rekap-card .card-body {
            padding: 12px !important;
        }
        .paspor-rekap-icon {
            width: 40px !important;
            height: 40px !important;
            border-radius: 10px !important;
            margin-right: 8px !important;
        }
        .paspor-rekap-icon i {
            font-size: 1.15rem !important;
        }
        .paspor-rekap-card h4 {
            font-size: 1.2rem !important;
        }
        .paspor-rekap-card .rekap-label {
            font-size: 0.7rem !important;
        }
        .filter-toolbar-mobile {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }
        .bulk-action-mobile {
            width: 100% !important;
            display: flex !important;
            gap: 6px !important;
        }
        .bulk-action-mobile .btn {
            flex: 1 !important;
            font-size: 0.75rem !important;
            padding: 7px 10px !important;
            justify-content: center !important;
        }
        
        /* Modal on Mobile */
        #modalPinjam .modal-dialog,
        #modalKembali .modal-dialog {
            margin: 0.4rem;
            max-width: calc(100% - 0.8rem) !important;
        }
        #modalPinjam .modal-content,
        #modalKembali .modal-content {
            border-radius: 16px !important;
            max-height: 94vh !important;
        }
        #modalPinjam .modal-header,
        #modalKembali .modal-header {
            padding: 14px 16px !important;
        }
        #modalPinjam .modal-body,
        #modalKembali .modal-body {
            padding: 14px 16px !important;
        }
        #modalPinjam .modal-footer,
        #modalKembali .modal-footer {
            padding: 10px 16px 14px 16px !important;
            flex-direction: column !important;
            gap: 8px !important;
        }
        #modalPinjam .modal-footer .btn,
        #modalKembali .modal-footer .btn {
            width: 100% !important;
            justify-content: center !important;
            padding: 9px 16px !important;
        }
    }
</style>

<div class="px-2 py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-header-responsive">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-passport text-primary me-2"></i>Manajemen Paspor</h3>
            <p class="text-muted small mb-0">Pencatatan keluar-masuk dan status paspor santri secara terpadu.</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= API_URL ?>/api/manajemen-paspor/export?type=status&format=print" target="_blank" class="btn btn-outline-primary fw-semibold rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> Print Report
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-success fw-semibold rounded-pill px-4 shadow-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3">
                    <li><a class="dropdown-item py-2" href="<?= API_URL ?>/api/manajemen-paspor/export?type=status&format=xlsx" target="_blank"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Status Paspor</a></li>
                    <li><a class="dropdown-item py-2" href="<?= API_URL ?>/api/manajemen-paspor/export?type=log&format=xlsx" target="_blank"><i class="bi bi-file-earmark-text text-success me-2"></i>Riwayat Log</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary / Rekap Cards (2 Col on Mobile, 4 Col on Desktop) -->
    <div class="row g-3 mb-4" id="summaryCards">
        <!-- Total Paspor -->
        <div class="col-6 col-md-3">
            <div class="card paspor-rekap-card shadow-sm h-100 active-filter" id="cardFilterAll" onclick="setFilter('all')">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="paspor-rekap-icon bg-primary bg-opacity-10 text-primary me-3 shadow-sm">
                        <i class="bi bi-passport fs-4"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="text-muted small rekap-label fw-semibold text-truncate">TOTAL PASPOR</div>
                        <h4 class="fw-bold mb-0 text-dark" id="countTotal">-</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Di Kantor -->
        <div class="col-6 col-md-3">
            <div class="card paspor-rekap-card shadow-sm h-100" id="cardFilterDiKantor" onclick="setFilter('di_kantor')">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="paspor-rekap-icon bg-success bg-opacity-10 text-success me-3 shadow-sm">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="text-muted small rekap-label fw-semibold text-truncate">DI KANTOR</div>
                        <h4 class="fw-bold mb-0 text-success" id="countDiKantor">-</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sedang Keluar -->
        <div class="col-6 col-md-3">
            <div class="card paspor-rekap-card shadow-sm h-100" id="cardFilterKeluar" onclick="setFilter('keluar')">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="paspor-rekap-icon bg-warning bg-opacity-10 text-warning-emphasis me-3 shadow-sm">
                        <i class="bi bi-box-arrow-up-right fs-4"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="text-muted small rekap-label fw-semibold text-truncate">SEDANG KELUAR</div>
                        <h4 class="fw-bold mb-0 text-warning-emphasis" id="countKeluar">-</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terlambat Kembali -->
        <div class="col-6 col-md-3">
            <div class="card paspor-rekap-card shadow-sm h-100" id="cardFilterTerlambat" onclick="setFilter('terlambat')">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="paspor-rekap-icon bg-danger bg-opacity-10 text-danger me-3 shadow-sm">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="text-muted small rekap-label fw-semibold text-truncate">TERLAMBAT KEMBALI</div>
                        <h4 class="fw-bold mb-0 text-danger" id="countTerlambat">-</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: white;">
        <div class="card-body p-3 p-md-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills mb-3 gap-2 border-bottom pb-3" id="pasporTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold rounded-pill px-4" id="status-tab" data-bs-toggle="tab" data-bs-target="#statusTab" type="button" role="tab">
                        <i class="bi bi-list-check me-1"></i>Status Paspor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold rounded-pill px-4" id="log-tab" data-bs-toggle="tab" data-bs-target="#logTab" type="button" role="tab" onclick="loadLog()">
                        <i class="bi bi-clock-history me-1"></i>Riwayat Log
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab Status Paspor -->
                <div class="tab-pane fade show active" id="statusTab" role="tabpanel">
                    <!-- Filter Toolbar & Bulk Actions -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 filter-toolbar-mobile">
                        <div class="filter-pill-container">
                            <button type="button" class="filter-btn-pill active filter-btn" data-filter="all" onclick="setFilter('all')">Semua</button>
                            <button type="button" class="filter-btn-pill filter-btn" data-filter="di_kantor" onclick="setFilter('di_kantor')">Di Kantor</button>
                            <button type="button" class="filter-btn-pill filter-btn" data-filter="keluar" onclick="setFilter('keluar')">Sedang Keluar</button>
                            <button type="button" class="filter-btn-pill filter-btn" data-filter="terlambat" onclick="setFilter('terlambat')">Terlambat</button>
                        </div>
                        <div class="bulk-action-mobile">
                            <button class="btn btn-sm btn-warning text-dark fw-bold shadow-sm rounded-pill px-3 d-none me-1" id="btnBulkKembalikan" onclick="bulkKembalikan()">
                                <i class="bi bi-box-arrow-in-down me-1"></i>Kembalikan (<span id="countBulkKembali">0</span>)
                            </button>
                            <button class="btn btn-sm btn-primary fw-bold shadow-sm rounded-pill px-3 d-none" id="btnBulkPinjam" onclick="bulkPinjam()">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Pinjamkan (<span id="countBulkPinjam">0</span>)
                            </button>
                        </div>
                    </div>

                    <!-- Table Status Paspor -->
                    <div class="table-responsive rounded-3 border" style="max-height: 65vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 3;">
                                <tr>
                                    <th class="bg-light text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="chkAll" onchange="toggleAllCb(this)" title="Pilih Semua"></th>
                                    <th class="bg-light text-nowrap">Nama Santri</th>
                                    <th class="bg-light text-nowrap">Pondok</th>
                                    <th class="bg-light text-nowrap">Kelas</th>
                                    <th class="bg-light text-nowrap">Negara</th>
                                    <th class="bg-light text-nowrap">No Paspor</th>
                                    <th class="bg-light text-nowrap">Exp Paspor</th>
                                    <th class="bg-light text-nowrap">Exp ITAS</th>
                                    <th class="bg-light text-nowrap">Status</th>
                                    <th class="bg-light text-nowrap text-center" style="position: sticky; right: 0; z-index: 4; border-left: 1px solid #dee2e6; box-shadow: -2px 0 5px rgba(0,0,0,0.05);">Aksi</th>
                                </tr>
                                <tr class="search-row bg-light">
                                    <th class="bg-light"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="1" placeholder="Cari santri..." style="min-width: 120px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="2" placeholder="Pondok..." style="width: 80px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="3" placeholder="Kelas..." style="width: 70px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="4" placeholder="Negara..." style="width: 85px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="5" placeholder="No paspor..." style="width: 95px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="6" placeholder="Tgl..." style="width: 90px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="7" placeholder="Tgl..." style="width: 90px;"></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="8" placeholder="Status..." style="width: 90px;"></th>
                                    <th class="bg-light" style="position: sticky; right: 0; z-index: 4; border-left: 1px solid #dee2e6; box-shadow: -2px 0 5px rgba(0,0,0,0.05);"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyStatus">
                                <tr><td colspan="11" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data master paspor...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Riwayat Log -->
                <div class="tab-pane fade" id="logTab" role="tabpanel">
                    <div class="table-responsive rounded-3 border" style="max-height: 65vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 3;">
                                <tr>
                                    <th class="bg-light text-nowrap">Tanggal</th>
                                    <th class="bg-light text-nowrap">Nama Santri</th>
                                    <th class="bg-light text-nowrap">No Paspor</th>
                                    <th class="bg-light text-nowrap">Aksi</th>
                                    <th class="bg-light text-nowrap">Alasan</th>
                                    <th class="bg-light text-nowrap">Rencana Kembali</th>
                                    <th class="bg-light text-nowrap">Catatan</th>
                                    <th class="bg-light text-nowrap">Dicatat Oleh</th>
                                </tr>
                                <tr class="search-row bg-light">
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="0" placeholder="Tgl..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="1" placeholder="Cari santri..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="2" placeholder="No paspor..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="3" placeholder="Aksi..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="4" placeholder="Alasan..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="5" placeholder="Tgl..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="6" placeholder="Catatan..."></th>
                                    <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="7" placeholder="Petugas..."></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyLog">
                                <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary"></i>Klik tab ini untuk memuat riwayat.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pinjam Paspor -->
<div class="modal fade" id="modalPinjam" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom px-4 py-3">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 38px; height: 38px;">
                <i class="bi bi-box-arrow-up-right fs-5"></i>
            </div>
            <h5 class="modal-title fw-bold text-dark mb-0">Pinjam Paspor</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="text-muted small">Pemilik Dokumen:</div>
            <div class="fw-bold text-dark fs-6" id="namaPinjam">-</div>
            <div class="text-primary small font-monospace" id="noPasporPinjam">-</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Alasan Peminjaman <span class="text-danger">*</span></label>
            <select class="form-select border shadow-sm py-2" id="alasanPinjam" onchange="toggleAlasanCustom()">
                <option value="" disabled selected>-- Pilih Alasan Peminjaman --</option>
                <option value="Pulang">Pulang</option>
                <option value="Urusan Imigrasi">Urusan Imigrasi</option>
                <option value="Perpanjangan Paspor">Perpanjangan Paspor</option>
                <option value="Urusan Visa">Urusan Visa</option>
                <option value="Lainnya">Lainnya (Tulis Sendiri)</option>
            </select>
        </div>
        <div class="mb-3 d-none" id="alasanCustomContainer">
            <label class="form-label fw-bold text-dark small mb-1">Tulis Alasan Kustom</label>
            <input type="text" class="form-control border shadow-sm py-2" id="alasanCustom" placeholder="Misal: Keperluan kedutaan...">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Tanggal Rencana Kembali</label>
            <input type="date" class="form-control border shadow-sm py-2" id="tglRencanaKembali">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Catatan Tambahan <span class="text-muted fw-normal">(Opsional)</span></label>
            <textarea class="form-control border shadow-sm" id="catatanPinjam" rows="2" placeholder="Catatan tambahan jika diperlukan..."></textarea>
        </div>
      </div>
      <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border: none;" id="btnProsesPinjam" onclick="prosesPinjam()">
            <i class="bi bi-box-arrow-up-right me-1"></i>Konfirmasi Pinjam
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Kembalikan Paspor -->
<div class="modal fade" id="modalKembali" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom px-4 py-3">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 38px; height: 38px;">
                <i class="bi bi-box-arrow-in-down fs-5"></i>
            </div>
            <h5 class="modal-title fw-bold text-dark mb-0">Kembalikan Paspor</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="text-muted small">Kembalikan Paspor Milik:</div>
            <div class="fw-bold text-dark fs-6" id="namaKembali">-</div>
            <div class="text-success small font-monospace" id="noPasporKembali">-</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Catatan Pengembalian <span class="text-muted fw-normal">(Opsional)</span></label>
            <textarea class="form-control border shadow-sm" id="catatanKembali" rows="2" placeholder="Catatan pengembalian paspor..."></textarea>
        </div>
      </div>
      <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669); border: none;" id="btnProsesKembali" onclick="prosesKembali()">
            <i class="bi bi-box-arrow-in-down me-1"></i>Konfirmasi Kembali
        </button>
      </div>
    </div>
  </div>
</div>

<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>
<script>
let currentFilter = 'all';
let currentPinjamData = {};
let currentKembaliData = {};
let allStatusData = [];
let eventsAttached = false;

function formatDate(dateStr) {
    if (!dateStr || dateStr === '-' || dateStr === '0000-00-00') return '-';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    const d = date.getDate().toString().padStart(2, '0');
    const m = months[date.getMonth()];
    const y = date.getFullYear();
    return `${d}-${m}-${y}`;
}

function setFilter(f) {
    currentFilter = f;
    
    // Update Filter Pills
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.filter === f);
    });

    // Update Summary Card active highlights
    document.querySelectorAll('.paspor-rekap-card').forEach(c => c.classList.remove('active-filter'));
    if (f === 'all') document.getElementById('cardFilterAll')?.classList.add('active-filter');
    else if (f === 'di_kantor') document.getElementById('cardFilterDiKantor')?.classList.add('active-filter');
    else if (f === 'keluar') document.getElementById('cardFilterKeluar')?.classList.add('active-filter');
    else if (f === 'terlambat') document.getElementById('cardFilterTerlambat')?.classList.add('active-filter');

    loadStatus();
}

function loadStatus() {
    const tbody = document.getElementById('tbodyStatus');
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data master paspor...</td></tr>';

    fetch('<?= API_URL ?>/api/manajemen-paspor/list?filter=' + currentFilter, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(res => {
        // Update summary cards
        const s = res.summary;
        document.getElementById('countTotal').textContent = s.total;
        document.getElementById('countDiKantor').textContent = s.di_kantor;
        document.getElementById('countKeluar').textContent = s.keluar;
        document.getElementById('countTerlambat').textContent = s.terlambat;

        // Notification badge for terlambat on dashboard
        const terlambatCard = document.getElementById('countTerlambat').closest('.card');
        if (s.terlambat > 0) {
            terlambatCard.classList.add('border', 'border-danger', 'border-2');
            terlambatCard.style.animation = 'pulse 2s infinite';
        } else {
            terlambatCard.classList.remove('border', 'border-danger', 'border-2');
            terlambatCard.style.animation = '';
        }

        const data = res.data;
        allStatusData = data;

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>Tidak ada data paspor</td></tr>';
            return;
        }

        let html = '';
        const today = new Date().toISOString().split('T')[0];
        data.forEach(d => {
            let statusBadge = '';
            let actionBtn = '';
            const isKeluar = d.status_lokasi === 'keluar';
            const isTerlambat = isKeluar && d.tanggal_rencana_kembali && d.tanggal_rencana_kembali < today;

            if (isKeluar) {
                const alasanText = d.last_alasan || 'Tidak disebutkan';
                const sejak = d.last_tanggal_keluar ? formatDate(d.last_tanggal_keluar) : '?';
                if (isTerlambat) {
                    statusBadge = `<span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>TERLAMBAT</span>
                                   <div class="mt-1"><small class="text-danger fw-bold">Sejak: ${sejak}</small></div>
                                   <div><small class="text-danger">Rencana: ${formatDate(d.tanggal_rencana_kembali)}</small></div>
                                   <div><small class="text-muted">${alasanText}</small></div>`;
                } else {
                    statusBadge = `<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1"><i class="bi bi-box-arrow-up-right me-1"></i>Keluar</span>
                                   <div class="mt-1"><small class="text-muted">Sejak: ${sejak}</small></div>
                                   ${d.tanggal_rencana_kembali ? `<div><small class="text-muted">Kembali: ${formatDate(d.tanggal_rencana_kembali)}</small></div>` : ''}
                                   <div><small class="text-muted">${alasanText}</small></div>`;
                }
                actionBtn = `<button class="btn btn-sm btn-success rounded-pill px-3 fw-medium shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #10b981, #059669); border: none;" onclick="bukaModalKembali(${d.paspor_id}, ${d.kds}, '${escHtml(d.nama)}', '${d.no_paspor}')"><i class="bi bi-box-arrow-in-down"></i> Kembalikan</button>`;
            } else {
                statusBadge = `<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-1"><i class="bi bi-shield-check me-1"></i>Di Kantor</span>`;
                actionBtn = `<button class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-medium shadow-sm d-inline-flex align-items-center gap-1" onclick="bukaModalPinjam(${d.paspor_id}, ${d.kds}, '${escHtml(d.nama)}', '${d.no_paspor}')"><i class="bi bi-box-arrow-up-right"></i> Pinjamkan</button>`;
            }

            // Exp paspor warning
            let expBadge = d.exp_paspor ? `<span class="text-dark">${formatDate(d.exp_paspor)}</span>` : '<span class="text-muted">-</span>';
            if (d.exp_paspor) {
                const expDate = new Date(d.exp_paspor);
                const diffDays = Math.ceil((expDate - new Date()) / (1000*60*60*24));
                if (diffDays <= 0) {
                    expBadge = `<span class="text-danger fw-bold">${formatDate(d.exp_paspor)}</span><br><span class="badge bg-danger rounded-pill px-2 py-0" style="font-size:0.65rem;">Expired</span>`;
                } else if (diffDays <= 540) {
                    const textP = diffDays > 90 ? Math.floor(diffDays / 30) + ' bln lagi' : diffDays + ' hari lagi';
                    expBadge = `<span class="text-warning-emphasis fw-bold">${formatDate(d.exp_paspor)}</span><br><span class="badge bg-warning text-dark rounded-pill px-2 py-0" style="font-size:0.65rem;">${textP}</span>`;
                }
            }

            // Exp ITAS warning
            let expItasBadge = d.exp_itas ? `<span class="text-dark">${formatDate(d.exp_itas)}</span>` : '<span class="text-muted">-</span>';
            if (d.exp_itas) {
                const expDate = new Date(d.exp_itas);
                const diffDays = Math.ceil((expDate - new Date()) / (1000*60*60*24));
                if (diffDays <= 0) {
                    expItasBadge = `<span class="text-danger fw-bold">${formatDate(d.exp_itas)}</span><br><span class="badge bg-danger rounded-pill px-2 py-0" style="font-size:0.65rem;">Expired</span>`;
                } else if (diffDays <= 90) {
                    const textI = diffDays > 90 ? Math.floor(diffDays / 30) + ' bln lagi' : diffDays + ' hari lagi';
                    expItasBadge = `<span class="text-warning-emphasis fw-bold">${formatDate(d.exp_itas)}</span><br><span class="badge bg-warning text-dark rounded-pill px-2 py-0" style="font-size:0.65rem;">${textI}</span>`;
                }
            }

            html += `<tr>
                <td class="text-center"><input type="checkbox" class="form-check-input row-cb" value="${d.paspor_id}" data-kds="${d.kds}" data-nama="${escHtml(d.nama)}" data-nopaspor="${d.no_paspor}" data-status="${d.status_lokasi}"></td>
                <td class="fw-bold text-dark text-nowrap">${d.nama}</td>
                <td class="text-nowrap">${d.pondok || '-'}</td>
                <td class="text-nowrap">${d.kelas || '-'}</td>
                <td class="text-nowrap">${d.negara || '-'}</td>
                <td class="fw-semibold text-nowrap font-monospace">${d.no_paspor}</td>
                <td class="text-nowrap">${expBadge}</td>
                <td class="text-nowrap">${expItasBadge}</td>
                <td class="text-nowrap">${statusBadge}</td>
                <td class="text-nowrap text-center bg-white" style="position: sticky; right: 0; z-index: 1; border-left: 1px solid #dee2e6; box-shadow: -2px 0 5px rgba(0,0,0,0.05);">${actionBtn}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
        attachCheckboxListeners();
        if (!eventsAttached) {
            attachSearchFilters();
            eventsAttached = true;
        }
    })
    .catch(err => {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-4">Gagal memuat data master paspor.</td></tr>';
    });
}

function loadLog() {
    const tbody = document.getElementById('tbodyLog');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat riwayat log...</td></tr>';

    fetch('<?= API_URL ?>/api/manajemen-paspor/log', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary"></i>Belum ada riwayat log paspor</td></tr>';
            return;
        }

        let html = '';
        data.forEach(d => {
            const tipeBadge = d.tipe === 'keluar'
                ? '<span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="bi bi-box-arrow-up-right me-1"></i>KELUAR</span>'
                : '<span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-box-arrow-in-down me-1"></i>KEMBALI</span>';
            html += `<tr>
                <td class="text-nowrap">${formatDate(d.tanggal_aksi)}</td>
                <td class="fw-bold text-dark text-nowrap">${d.nama}</td>
                <td class="fw-semibold text-nowrap font-monospace">${d.no_paspor}</td>
                <td class="text-nowrap">${tipeBadge}</td>
                <td class="text-nowrap">${d.alasan || '-'}</td>
                <td class="text-nowrap">${d.tanggal_rencana_kembali ? formatDate(d.tanggal_rencana_kembali) : '-'}</td>
                <td>${d.catatan || '-'}</td>
                <td class="text-nowrap">${d.dicatat_oleh || '-'}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
    })
    .catch(err => {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat riwayat log.</td></tr>';
    });
}

// ======= Modal Functions =======
function bukaModalPinjam(pasporId, kds, nama, noPaspor) {
    currentPinjamData = { paspor_id: pasporId, kds: kds };
    document.getElementById('namaPinjam').textContent = nama;
    document.getElementById('noPasporPinjam').textContent = noPaspor;
    document.getElementById('alasanPinjam').selectedIndex = 0;
    document.getElementById('alasanCustom').value = '';
    document.getElementById('alasanCustomContainer').classList.add('d-none');
    document.getElementById('tglRencanaKembali').value = '';
    document.getElementById('catatanPinjam').value = '';
    new bootstrap.Modal(document.getElementById('modalPinjam')).show();
}

function bukaModalKembali(pasporId, kds, nama, noPaspor) {
    currentKembaliData = { paspor_id: pasporId, kds: kds };
    document.getElementById('namaKembali').textContent = nama;
    document.getElementById('noPasporKembali').textContent = noPaspor;
    document.getElementById('catatanKembali').value = '';
    new bootstrap.Modal(document.getElementById('modalKembali')).show();
}

function toggleAlasanCustom() {
    const val = document.getElementById('alasanPinjam').value;
    document.getElementById('alasanCustomContainer').classList.toggle('d-none', val !== 'Lainnya');
}

function prosesPinjam() {
    const alasan = document.getElementById('alasanPinjam').value;
    if (!alasan) { Swal.fire('Peringatan', 'Pilih alasan peminjaman!', 'warning'); return; }

    const btn = document.getElementById('btnProsesPinjam');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    fetch('<?= API_URL ?>/api/manajemen-paspor/pinjam', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'Accept': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content 
        },
        body: JSON.stringify({
            ...currentPinjamData,
            alasan: alasan,
            alasan_custom: document.getElementById('alasanCustom').value,
            tanggal_rencana_kembali: document.getElementById('tglRencanaKembali').value,
            catatan: document.getElementById('catatanPinjam').value
        })
    })
    .then(r => r.json())
    .then(res => {
        bootstrap.Modal.getInstance(document.getElementById('modalPinjam')).hide();
        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
            loadStatus();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(e => Swal.fire('Error', 'Gagal memproses peminjaman paspor.', 'error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-box-arrow-up-right me-1"></i>Konfirmasi Pinjam'; });
}

function prosesKembali() {
    const btn = document.getElementById('btnProsesKembali');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    fetch('<?= API_URL ?>/api/manajemen-paspor/kembalikan', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'Accept': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({
            ...currentKembaliData,
            catatan: document.getElementById('catatanKembali').value
        })
    })
    .then(r => r.json())
    .then(res => {
        bootstrap.Modal.getInstance(document.getElementById('modalKembali')).hide();
        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
            loadStatus();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(e => Swal.fire('Error', 'Gagal memproses pengembalian paspor.', 'error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-box-arrow-in-down me-1"></i>Konfirmasi Kembali'; });
}

// ======= Bulk Actions =======
function bulkPinjam() {
    const checked = document.querySelectorAll('.row-cb:checked');
    const items = Array.from(checked).filter(c => c.dataset.status !== 'keluar');
    if (items.length === 0) { Swal.fire('Info', 'Tidak ada paspor yang bisa dipinjamkan dari pilihan Anda (mungkin sudah keluar semua).', 'info'); return; }
    if (items.length === 1) {
        bukaModalPinjam(parseInt(items[0].value), parseInt(items[0].dataset.kds), items[0].dataset.nama, items[0].dataset.nopaspor);
        return;
    }
    // For multiple, use the same modal but with batch info
    currentPinjamData = { batch: items.map(c => ({ paspor_id: parseInt(c.value), kds: parseInt(c.dataset.kds) })) };
    document.getElementById('namaPinjam').textContent = items.length + ' Santri dipilih';
    document.getElementById('noPasporPinjam').textContent = items.map(c => c.dataset.nopaspor).join(', ');
    document.getElementById('alasanPinjam').selectedIndex = 0;
    document.getElementById('alasanCustom').value = '';
    document.getElementById('alasanCustomContainer').classList.add('d-none');
    document.getElementById('tglRencanaKembali').value = '';
    document.getElementById('catatanPinjam').value = '';
    new bootstrap.Modal(document.getElementById('modalPinjam')).show();
}

function bulkKembalikan() {
    const checked = document.querySelectorAll('.row-cb:checked');
    const items = Array.from(checked).filter(c => c.dataset.status === 'keluar');
    if (items.length === 0) { Swal.fire('Info', 'Tidak ada paspor yang bisa dikembalikan dari pilihan Anda.', 'info'); return; }

    Swal.fire({
        title: `Kembalikan ${items.length} Paspor?`,
        text: 'Semua paspor terpilih akan dicatat kembali ke kantor.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-in-down me-1"></i> Ya, Kembalikan Semua',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 shadow-lg' }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            const promises = items.map(c => fetch('<?= API_URL ?>/api/manajemen-paspor/kembalikan', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ paspor_id: parseInt(c.value), kds: parseInt(c.dataset.kds) })
            }).then(r => r.json()));

            Promise.all(promises).then(results => {
                const ok = results.filter(r => r.success).length;
                Swal.fire({ icon: 'success', title: 'Selesai!', text: `${ok} dari ${items.length} paspor berhasil dikembalikan.` });
                loadStatus();
            });
        }
    });
}

// Override prosesPinjam for batch mode
const origProsesPinjam = prosesPinjam;
prosesPinjam = function() {
    if (currentPinjamData.batch) {
        const alasan = document.getElementById('alasanPinjam').value;
        if (!alasan) { Swal.fire('Peringatan', 'Pilih alasan peminjaman!', 'warning'); return; }

        const btn = document.getElementById('btnProsesPinjam');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const payload = {
            alasan: alasan,
            alasan_custom: document.getElementById('alasanCustom').value,
            tanggal_rencana_kembali: document.getElementById('tglRencanaKembali').value,
            catatan: document.getElementById('catatanPinjam').value
        };

        const promises = currentPinjamData.batch.map(item =>
            fetch('<?= API_URL ?>/api/manajemen-paspor/pinjam', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ ...item, ...payload })
            }).then(r => r.json())
        );

        Promise.all(promises).then(results => {
            bootstrap.Modal.getInstance(document.getElementById('modalPinjam')).hide();
            const ok = results.filter(r => r.success).length;
            Swal.fire({ icon: 'success', title: 'Selesai!', text: `${ok} dari ${currentPinjamData.batch.length} paspor berhasil dipinjamkan.` });
            loadStatus();
        }).finally(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-box-arrow-up-right me-1"></i>Konfirmasi Pinjam'; });
    } else {
        origProsesPinjam();
    }
};

// ======= Search Filters =======
function attachSearchFilters() {
    const inputs = document.querySelectorAll('.col-search');
    const tbody = document.getElementById('tbodyStatus');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const filters = Array.from(inputs).map(inp => ({
                col: parseInt(inp.getAttribute('data-col')),
                val: inp.value.toLowerCase().trim()
            })).filter(f => f.val !== '');

            Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
                if (tr.querySelector('td[colspan]')) return;
                let match = true;
                filters.forEach(f => {
                    const td = tr.querySelectorAll('td')[f.col];
                    if (td && !td.textContent.toLowerCase().includes(f.val)) match = false;
                });
                tr.style.display = match ? '' : 'none';
                if (!match) {
                    const cb = tr.querySelector('.row-cb');
                    if (cb && cb.checked) { cb.checked = false; cb.dispatchEvent(new Event('change')); }
                }
            });
        });
    });
}

function attachLogSearchFilters() {
    const inputs = document.querySelectorAll('.log-search');
    const tbody = document.getElementById('tbodyLog');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const filters = Array.from(inputs).map(inp => ({
                col: parseInt(inp.getAttribute('data-col')),
                val: inp.value.toLowerCase().trim()
            })).filter(f => f.val !== '');

            Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
                if (tr.querySelector('td[colspan]')) return;
                let match = true;
                filters.forEach(f => {
                    const td = tr.querySelectorAll('td')[f.col];
                    if (td && !td.textContent.toLowerCase().includes(f.val)) match = false;
                });
                tr.style.display = match ? '' : 'none';
            });
        });
    });
}

// ======= Checkbox Helpers =======
function toggleAllCb(el) {
    document.querySelectorAll('.row-cb').forEach(cb => {
        if (cb.closest('tr').style.display !== 'none') {
            cb.checked = el.checked;
        }
    });
    updateBulkButtons();
}

function attachCheckboxListeners() {
    document.querySelectorAll('.row-cb').forEach(cb => {
        cb.addEventListener('change', updateBulkButtons);
    });
}

function updateBulkButtons() {
    const checked = document.querySelectorAll('.row-cb:checked');
    const hasDiKantor = Array.from(checked).some(c => c.dataset.status !== 'keluar');
    const hasKeluar = Array.from(checked).some(c => c.dataset.status === 'keluar');

    const btnPinjam = document.getElementById('btnBulkPinjam');
    const btnKembali = document.getElementById('btnBulkKembalikan');

    if (hasDiKantor) {
        btnPinjam.classList.remove('d-none');
        document.getElementById('countBulkPinjam').textContent = Array.from(checked).filter(c => c.dataset.status !== 'keluar').length;
    } else {
        btnPinjam.classList.add('d-none');
    }

    if (hasKeluar) {
        btnKembali.classList.remove('d-none');
        document.getElementById('countBulkKembali').textContent = Array.from(checked).filter(c => c.dataset.status === 'keluar').length;
    } else {
        btnKembali.classList.add('d-none');
    }
}

function escHtml(str) {
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ======= Init =======
document.addEventListener('DOMContentLoaded', () => {
    loadStatus();
    attachLogSearchFilters();
});
</script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.table tbody tr { user-select: none; }
</style>
