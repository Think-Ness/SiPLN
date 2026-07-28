<?php
/** @var bool $isSuperAdmin */
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-passport me-2"></i>Manajemen Paspor</h3>
        <p class="text-muted mb-0">Pencatatan keluar-masuk paspor santri</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= API_URL ?>/api/manajemen-paspor/export?type=status&format=print" target="_blank" class="btn btn-outline-primary fw-semibold rounded-pill px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i> Print Report
        </a>
        <div class="dropdown">
            <button class="btn btn-outline-success fw-semibold rounded-pill px-4 shadow-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="<?= API_URL ?>/api/manajemen-paspor/export?type=status&format=xlsx" target="_blank"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Status Paspor</a></li>
                <li><a class="dropdown-item" href="<?= API_URL ?>/api/manajemen-paspor/export?type=log&format=xlsx" target="_blank"><i class="bi bi-file-earmark-text text-success me-2"></i>Riwayat Log</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4" id="summaryCards">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3"><i class="bi bi-passport fs-4 text-primary"></i></div>
                <div><div class="text-muted small">Total Paspor</div><h4 class="fw-bold mb-0" id="countTotal">-</h4></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 cursor-pointer" onclick="setFilter('di_kantor')" style="cursor:pointer">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3"><i class="bi bi-shield-check fs-4 text-success"></i></div>
                <div><div class="text-muted small">Di Kantor</div><h4 class="fw-bold mb-0 text-success" id="countDiKantor">-</h4></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100" onclick="setFilter('keluar')" style="cursor:pointer">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3"><i class="bi bi-box-arrow-up-right fs-4 text-warning"></i></div>
                <div><div class="text-muted small">Sedang Keluar</div><h4 class="fw-bold mb-0 text-warning" id="countKeluar">-</h4></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100" onclick="setFilter('terlambat')" style="cursor:pointer">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3"><i class="bi bi-exclamation-triangle fs-4 text-danger"></i></div>
                <div><div class="text-muted small">Terlambat Kembali</div><h4 class="fw-bold mb-0 text-danger" id="countTerlambat">-</h4></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="pasporTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="status-tab" data-bs-toggle="tab" data-bs-target="#statusTab" type="button" role="tab">
                    <i class="bi bi-list-check me-1"></i>Status Paspor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="log-tab" data-bs-toggle="tab" data-bs-target="#logTab" type="button" role="tab" onclick="loadLog()">
                    <i class="bi bi-clock-history me-1"></i>Riwayat Log
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab Status Paspor -->
            <div class="tab-pane fade show active" id="statusTab" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active filter-btn" data-filter="all" onclick="setFilter('all')">Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="di_kantor" onclick="setFilter('di_kantor')">Di Kantor</button>
                        <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-filter="keluar" onclick="setFilter('keluar')">Sedang Keluar</button>
                        <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="terlambat" onclick="setFilter('terlambat')">Terlambat</button>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-warning fw-semibold shadow-sm d-none me-2" id="btnBulkKembalikan" onclick="bulkKembalikan()">
                            <i class="bi bi-box-arrow-in-down me-1"></i>Kembalikan (<span id="countBulkKembali">0</span>)
                        </button>
                        <button class="btn btn-sm btn-primary fw-semibold shadow-sm d-none" id="btnBulkPinjam" onclick="bulkPinjam()">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Pinjamkan (<span id="countBulkPinjam">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 3;">
                            <tr>
                                <th class="bg-light" style="width: 40px;"><input type="checkbox" class="form-check-input" id="chkAll" onchange="toggleAllCb(this)"></th>
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
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="1" placeholder="Cari..." style="min-width: 120px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="2" placeholder="Cari..." style="width: 70px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="3" placeholder="Cari..." style="width: 60px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="4" placeholder="Cari..." style="width: 80px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="5" placeholder="Cari..." style="width: 90px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="6" placeholder="Cari..." style="width: 90px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="7" placeholder="Cari..." style="width: 90px;"></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm col-search" data-col="8" placeholder="Cari..." style="width: 90px;"></th>
                                <th class="bg-light" style="position: sticky; right: 0; z-index: 4; border-left: 1px solid #dee2e6; box-shadow: -2px 0 5px rgba(0,0,0,0.05);"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyStatus">
                            <tr><td colspan="11" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Riwayat Log -->
            <div class="tab-pane fade" id="logTab" role="tabpanel">
                <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
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
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="0" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="1" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="2" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="3" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="4" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="5" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="6" placeholder="Cari..."></th>
                                <th class="bg-light"><input type="text" class="form-control form-control-sm log-search" data-col="7" placeholder="Cari..."></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLog">
                            <tr><td colspan="8" class="text-center text-muted py-5">Klik tab ini untuk memuat riwayat.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pinjam Paspor -->
<div class="modal fade" id="modalPinjam" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-up-right text-warning me-2"></i>Pinjam Paspor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Paspor milik: <strong id="namaPinjam"></strong><br><span class="text-muted" id="noPasporPinjam"></span></p>
        <div class="mb-3">
            <label class="form-label fw-semibold">Alasan Peminjaman <span class="text-danger">*</span></label>
            <select class="form-select" id="alasanPinjam" onchange="toggleAlasanCustom()">
                <option value="" disabled selected>-- Pilih Alasan --</option>
                <option value="Pulang">Pulang</option>
                <option value="Urusan Imigrasi">Urusan Imigrasi</option>
                <option value="Perpanjangan Paspor">Perpanjangan Paspor</option>
                <option value="Urusan Visa">Urusan Visa</option>
                <option value="Lainnya">Lainnya (Tulis Sendiri)</option>
            </select>
        </div>
        <div class="mb-3 d-none" id="alasanCustomContainer">
            <label class="form-label fw-semibold">Tulis Alasan</label>
            <input type="text" class="form-control" id="alasanCustom" placeholder="Misal: Keperluan kedutaan...">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Tanggal Rencana Kembali</label>
            <input type="date" class="form-control" id="tglRencanaKembali">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Tambahan <span class="text-muted">(Opsional)</span></label>
            <textarea class="form-control" id="catatanPinjam" rows="2" placeholder="Catatan tambahan jika diperlukan..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning px-4 fw-semibold" id="btnProsesPinjam" onclick="prosesPinjam()">
            <i class="bi bi-box-arrow-up-right me-1"></i>Konfirmasi Pinjam
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Kembalikan Paspor -->
<div class="modal fade" id="modalKembali" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-in-down text-success me-2"></i>Kembalikan Paspor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Kembalikan paspor milik: <strong id="namaKembali"></strong><br><span class="text-muted" id="noPasporKembali"></span></p>
        <div class="mb-3">
            <label class="form-label fw-semibold">Catatan <span class="text-muted">(Opsional)</span></label>
            <textarea class="form-control" id="catatanKembali" rows="2" placeholder="Catatan pengembalian..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success px-4 fw-semibold" id="btnProsesKembali" onclick="prosesKembali()">
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
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.filter === f);
    });
    loadStatus();
}

function loadStatus() {
    const tbody = document.getElementById('tbodyStatus');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat...</td></tr>';

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
            tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data paspor</td></tr>';
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
                    statusBadge = `<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>TERLAMBAT</span>
                                   <br><small class="text-danger">Sejak: ${sejak}</small>
                                   <br><small class="text-danger">Rencana: ${formatDate(d.tanggal_rencana_kembali)}</small>
                                   <br><small class="text-muted">${alasanText}</small>`;
                } else {
                    statusBadge = `<span class="badge bg-warning text-dark"><i class="bi bi-box-arrow-up-right me-1"></i>Keluar</span>
                                   <br><small class="text-muted">Sejak: ${sejak}</small>
                                   ${d.tanggal_rencana_kembali ? `<br><small class="text-muted">Kembali: ${formatDate(d.tanggal_rencana_kembali)}</small>` : ''}
                                   <br><small class="text-muted">${alasanText}</small>`;
                }
                actionBtn = `<button class="btn btn-sm btn-success rounded-pill px-3" onclick="bukaModalKembali(${d.paspor_id}, ${d.kds}, '${escHtml(d.nama)}', '${d.no_paspor}')"><i class="bi bi-box-arrow-in-down me-1"></i>Kembalikan</button>`;
            } else {
                statusBadge = `<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Di Kantor</span>`;
                actionBtn = `<button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="bukaModalPinjam(${d.paspor_id}, ${d.kds}, '${escHtml(d.nama)}', '${d.no_paspor}')"><i class="bi bi-box-arrow-up-right me-1"></i>Pinjamkan</button>`;
            }

            // Exp paspor warning
            let expBadge = d.exp_paspor ? formatDate(d.exp_paspor) : '-';
            if (d.exp_paspor) {
                const expDate = new Date(d.exp_paspor);
                const diffDays = Math.ceil((expDate - new Date()) / (1000*60*60*24));
                if (diffDays <= 0) {
                    expBadge = `<span class="text-danger fw-bold">${formatDate(d.exp_paspor)}</span><br><small class="badge bg-danger">Expired!</small>`;
                } else if (diffDays <= 540) {
                    const textP = diffDays > 90 ? Math.floor(diffDays / 30) + ' bln lagi' : diffDays + ' hari lagi';
                    expBadge = `<span class="text-warning fw-bold">${formatDate(d.exp_paspor)}</span><br><small class="badge bg-warning text-dark">${textP}</small>`;
                }
            }

            // Exp ITAS warning
            let expItasBadge = d.exp_itas ? formatDate(d.exp_itas) : '-';
            if (d.exp_itas) {
                const expDate = new Date(d.exp_itas);
                const diffDays = Math.ceil((expDate - new Date()) / (1000*60*60*24));
                if (diffDays <= 0) {
                    expItasBadge = `<span class="text-danger fw-bold">${formatDate(d.exp_itas)}</span><br><small class="badge bg-danger">Expired!</small>`;
                } else if (diffDays <= 90) {
                    const textI = diffDays > 90 ? Math.floor(diffDays / 30) + ' bln lagi' : diffDays + ' hari lagi';
                    expItasBadge = `<span class="text-warning fw-bold">${formatDate(d.exp_itas)}</span><br><small class="badge bg-warning text-dark">${textI}</small>`;
                }
            }

            html += `<tr style="cursor: pointer;">
                <td><input type="checkbox" class="form-check-input row-cb" value="${d.paspor_id}" data-kds="${d.kds}" data-nama="${escHtml(d.nama)}" data-nopaspor="${d.no_paspor}" data-status="${d.status_lokasi}"></td>
                <td class="fw-semibold text-nowrap">${d.nama}</td>
                <td class="text-nowrap">${d.pondok || '-'}</td>
                <td class="text-nowrap">${d.kelas || '-'}</td>
                <td class="text-nowrap">${d.negara || '-'}</td>
                <td class="fw-medium text-nowrap">${d.no_paspor}</td>
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
        tbody.innerHTML = '<tr><td colspan="11" class="text-center text-danger py-4">Gagal memuat data</td></tr>';
    });
}

function loadLog() {
    const tbody = document.getElementById('tbodyLog');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat...</td></tr>';

    fetch('<?= API_URL ?>/api/manajemen-paspor/log', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-clock-history fs-1 d-block mb-2"></i>Belum ada riwayat</td></tr>';
            return;
        }

        let html = '';
        data.forEach(d => {
            const tipeBadge = d.tipe === 'keluar'
                ? '<span class="badge bg-warning text-dark"><i class="bi bi-box-arrow-up-right me-1"></i>KELUAR</span>'
                : '<span class="badge bg-success"><i class="bi bi-box-arrow-in-down me-1"></i>KEMBALI</span>';
            html += `<tr>
                <td class="text-nowrap">${formatDate(d.tanggal_aksi)}</td>
                <td class="fw-semibold text-nowrap">${d.nama}</td>
                <td class="fw-medium text-nowrap">${d.no_paspor}</td>
                <td class="text-nowrap">${tipeBadge}</td>
                <td class="text-nowrap">${d.alasan || '-'}</td>
                <td class="text-nowrap">${d.tanggal_rencana_kembali ? formatDate(d.tanggal_rencana_kembali) : '-'}</td>
                <td>${d.catatan || '-'}</td>
                <td class="text-nowrap">${d.dicatat_oleh || '-'}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
        // Search filters for log are now attached globally once.
    })
    .catch(err => {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat riwayat</td></tr>';
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
            Swal.fire('Berhasil!', res.message, 'success');
            loadStatus();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(e => Swal.fire('Error', 'Gagal memproses.', 'error'))
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
            Swal.fire('Berhasil!', res.message, 'success');
            loadStatus();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(e => Swal.fire('Error', 'Gagal memproses.', 'error'))
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
        confirmButtonColor: '#198754',
        confirmButtonText: 'Ya, Kembalikan Semua'
    }).then(result => {
        if (result.isConfirmed) {
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
                Swal.fire('Selesai!', `${ok} dari ${items.length} paspor berhasil dikembalikan.`, 'success');
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
            Swal.fire('Selesai!', `${ok} dari ${currentPinjamData.batch.length} paspor berhasil dipinjamkan.`, 'success');
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
    attachLogSearchFilters(); // Attach once since header is static
});
</script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.table tbody tr { user-select: none; }
</style>
