<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
/** @var WebView $this */
$this->setTitle('Pendaftaran CAPEL | Sistem Informasi');
?>
<style>
.capel-table-wrapper {
    max-height: 65vh;
    overflow: auto;
}
.capel-table-wrapper thead tr:nth-child(1) th {
    position: sticky;
    top: 0;
    z-index: 5;
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6;
}
.capel-table-wrapper thead tr:nth-child(2) th {
    position: sticky;
    top: 41px; /* approx height of first row */
    z-index: 5;
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6;
}
.sticky-right {
    position: sticky;
    right: 0;
    z-index: 4;
    background-color: white !important;
    box-shadow: -3px 0 5px rgba(0,0,0,0.05);
}
.capel-table-wrapper thead th.sticky-right {
    z-index: 6;
    background-color: #f8f9fa !important;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h3 class="fw-bold text-dark mb-1">Review Pendaftaran CAPEL</h3>
        <p class="text-muted small mb-0">Menunggu Persetujuan dari Pendaftaran</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($isSuperAdmin): ?>
        <select class="form-select fw-semibold rounded-pill px-3 shadow-sm" style="width: auto; min-width: 200px;" id="superAdminInstansi" onchange="window.location.href='?instansi_id=' + this.value;">
            <option value="">Semua Instansi (Statis)</option>
            <?php foreach ($instansiList as $inst): ?>
                <option value="<?= $inst['id'] ?>" <?= ($selectedInstansiId == $inst['id']) ? 'selected' : '' ?>><?= htmlspecialchars($inst['nama_instansi']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <?php if (!$isSuperAdmin || $selectedInstansiId): ?>
        <button class="btn btn-outline-secondary fw-semibold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAturTabel">
            <i class="bi bi-layout-text-window-reverse me-2"></i>Atur Tampilan Tabel
        </button>
        <button class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSync">
            <i class="bi bi-cloud-download me-2"></i>Tarik Data Baru
        </button>
        <?php endif; ?>
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
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="capelTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true" onclick="loadData('Pending')">Pending <span class="badge bg-danger rounded-pill ms-1" id="badgePending">0</span></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="false" onclick="loadData('Approved')">Disetujui</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false" onclick="loadData('Rejected')">Ditolak</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Pendaftaran Baru</h5>
                    <div>
                        <button class="btn btn-sm btn-danger fw-semibold shadow-sm d-none me-2" id="btnBulkDeletePending" onclick="bulkDelete('Pending')">
                            <i class="bi bi-trash me-1"></i>Hapus Terpilih (<span id="countSelectedPendingDel">0</span>)
                        </button>
                        <button class="btn btn-sm btn-success fw-semibold shadow-sm d-none" id="btnBulkApprove" onclick="bukaModalTerimaBulk()">
                            <i class="bi bi-check-all me-1"></i>Terima Terpilih (<span id="countSelected">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive capel-table-wrapper">
                    <table class="table table-hover align-middle">
                        <thead class="table-light" id="theadPending">
                            <!-- Populated via JS -->
                        </thead>
                        <tbody id="tbodyPending">
                            <tr><td colspan="10" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Disetujui</h5>
                    <div>
                        <button class="btn btn-sm btn-warning fw-semibold shadow-sm d-none me-2" id="btnBulkCancel" onclick="bulkCancel()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Batalkan (<span id="countSelectedApprovedCancel">0</span>)
                        </button>
                        <button class="btn btn-sm btn-danger fw-semibold shadow-sm d-none" id="btnBulkDeleteApproved" onclick="bulkDelete('Approved')">
                            <i class="bi bi-trash me-1"></i>Hapus (<span id="countSelectedApprovedDel">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive capel-table-wrapper">
                    <table class="table table-hover align-middle">
                        <thead class="table-light" id="theadApproved">
                            <!-- Populated via JS -->
                        </thead>
                        <tbody id="tbodyApproved"></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Daftar Ditolak</h5>
                    <button class="btn btn-sm btn-danger fw-semibold shadow-sm d-none" id="btnBulkDeleteRejected" onclick="bulkDelete('Rejected')">
                        <i class="bi bi-trash me-1"></i>Hapus (<span id="countSelectedRejectedDel">0</span>)
                    </button>
                </div>
                <div class="table-responsive capel-table-wrapper">
                    <table class="table table-hover align-middle">
                        <thead class="table-light" id="theadRejected">
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
    { name: 'Scan ID Paspor', keywords: ['scan id paspor', 'passport'] },
    { name: 'Scan IC Santri', keywords: ['scan ic (kartu identitas) santri', 'scan ic santri', 'kartu identitas santri', 'scan of id card'] },
    { name: 'Scan IC Ayah', keywords: ['scan ic ayah', 'father\'s scan'] },
    { name: 'Scan IC Ibu', keywords: ['scan ic ibu', 'mother\'s scan'] },
    { name: 'Surat Beranak', keywords: ['surat beranak', 'surat kelahiran', 'birth certificate'] },
    { name: 'Pas Foto', keywords: ['pas foto', 'pasfoto', 'photograph'] },
    { name: 'Curriculum Vitae', keywords: ['curriculum vitae', 'cv'] },
    { name: 'Sertifikat Vaksin', keywords: ['scan sertifikat vaksin', 'vaccine'] },
    { name: 'Ijazah / Rapor', keywords: ['scan ijazah', 'rapor', 'diploma', 'report card'] },
    { name: 'Surat Sehat', keywords: ['kesanggupan sehat', 'bebas penyakit menular', 'certificate of health'] },
    { name: 'Kesanggupan Biaya', keywords: ['kesanggupan biaya', 'financial capability'] },
    { name: 'Affidavit', keywords: ['affidavit'] },
    { name: 'Surat Pelajar Asing', keywords: ['pelajar asing'] }
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
            presentHtml += `<div class="mb-4 bg-white p-3 rounded shadow-sm border border-success border-opacity-50">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>${expected.name}</h6>`;
            foundLinks.forEach(link => {
                const fileId = extractDriveId(link.trim());
                if (fileId) {
                    presentHtml += `<iframe src="https://drive.google.com/file/d/${fileId}/preview" width="100%" height="450" class="border rounded mb-2" allow="autoplay"></iframe>`;
                } else {
                    presentHtml += `<a href="${link.trim()}" target="_blank" class="btn btn-sm btn-outline-primary mb-2"><i class="bi bi-box-arrow-up-right me-1"></i> Buka File</a>`;
                }
            });
            presentHtml += `</div>`;
        } else {
            missingHtml += `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-50 me-2 mb-2 px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i> ${expected.name}</span>`;
        }
    });

    if (missingHtml !== '') {
        missingHtml = `<div class="mb-4 p-3 rounded shadow-sm border border-danger border-opacity-50 bg-white">
                        <h6 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Berkas Kosong / Belum Dilampirkan:</h6>
                        <div>${missingHtml}</div>
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
            html += `<div class="mb-4 bg-white p-3 rounded shadow-sm border border-info border-opacity-50">
                        <h6 class="fw-bold text-info mb-3"><i class="bi bi-info-circle-fill me-2"></i>Dokumen Tambahan: ${item.key}</h6>`;
            links.forEach(link => {
                const fileId = extractDriveId(link.trim());
                if (fileId) {
                    html += `<iframe src="https://drive.google.com/file/d/${fileId}/preview" width="100%" height="450" class="border rounded mb-2" allow="autoplay"></iframe>`;
                } else {
                    html += `<a href="${link.trim()}" target="_blank" class="btn btn-sm btn-outline-primary mb-2"><i class="bi bi-box-arrow-up-right me-1"></i> Buka File</a>`;
                }
            });
            html += `</div>`;
        }
    }
    
    document.getElementById('berkasContainer').innerHTML = `<div class="bg-light p-3 rounded border">${html}</div>`;
    document.getElementById('namaSantriBerkas').textContent = data.nama_lengkap;
    new bootstrap.Modal(document.getElementById('modalLihatBerkas')).show();
}

function buildThead(status) {
    let checkId = 'chkAll' + status;
    let onchange = `toggleAll('${status}', this)`;
    
    let tr1 = `<tr>
        <th class="bg-light" style="width: 40px; position: sticky; left: 0; z-index: 2;"><input type="checkbox" class="form-check-input" id="${checkId}" onchange="${onchange}"></th>
        <th class="bg-light text-nowrap">Tanggal Submit</th>
        <th class="bg-light text-nowrap" style="position: sticky; left: 40px; z-index: 2;">Nama Lengkap</th>`;
        
    let tr2 = `<tr class="search-row bg-light">
        <th class="bg-light" style="position: sticky; left: 0; z-index: 2;"></th>
        <th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="1" placeholder="Cari..."></th>
        <th class="bg-light" style="position: sticky; left: 40px; z-index: 2;"><input type="text" class="form-control form-control-sm column-search" data-col="2" placeholder="Cari..."></th>`;
        
    let colIndex = 3;
    displayColumns.forEach(col => {
        tr1 += `<th class="bg-light text-nowrap">${col}</th>`;
        tr2 += `<th class="bg-light"><input type="text" class="form-control form-control-sm column-search" data-col="${colIndex}" placeholder="Cari..."></th>`;
        colIndex++;
    });
    
    tr1 += `<th class="bg-light text-nowrap sticky-right">${status === 'Pending' ? 'Aksi' : 'Status'}</th></tr>`;
    tr2 += `<th class="bg-light sticky-right"></th></tr>`;
    
    return tr1 + tr2;
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
        if (status === 'Pending') document.getElementById('badgePending').textContent = data.length;
        
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${displayColumns.length + 4}" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data pendaftaran</td></tr>`;
            return;
        }
        
        currentDataList = data;

        let html = '';
        data.forEach(d => {
            let dupBadge = '<span class="badge bg-success-subtle text-success">Aman</span>';
            if (d.potential_duplicates && d.potential_duplicates.length > 0) {
                dupBadge = `<span class="badge bg-danger" title="Ada ${d.potential_duplicates.length} data mirip di master santri"><i class="bi bi-exclamation-triangle"></i> Mirip</span>`;
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
                programBadge = '<span class="badge bg-primary mt-1">Program Penerimaan</span>';
            } else if (rawProgram.toLowerCase().includes('program persiapan') || rawProgram.toLowerCase().includes('penampungan')) {
                programBadge = '<span class="badge bg-warning text-dark mt-1">Program Persiapan</span>';
            } else if (rawProgram !== '-' && rawProgram.trim() !== '') {
                programBadge = `<span class="badge bg-secondary mt-1">${rawProgram}</span>`;
            } else {
                programBadge = `<span class="badge bg-light text-secondary border mt-1">Program Tidak Disebutkan</span>`;
            }

            // Instansi Badge (only for super admin)
            let instansiBadge = '';
            if (d.nama_instansi && <?= $isSuperAdmin ? 'true' : 'false' ?>) {
                instansiBadge = `<br><span class="badge bg-info text-dark mt-1 border border-info border-opacity-50"><i class="bi bi-building"></i> ${d.nama_instansi}</span>`;
            }

            let rowHtml = `<tr>
                <td class="bg-white" style="position: sticky; left: 0; z-index: 1;"><input type="checkbox" class="form-check-input chk-${status} row-cb" value="${d.id}" data-nama="${d.nama_lengkap}"></td>
                <td class="text-nowrap">${d.timestamp}</td>
                <td class="fw-bold bg-white text-nowrap" style="position: sticky; left: 40px; z-index: 1;">${d.nama_lengkap}<br>${programBadge}${instansiBadge}</td>`;

            displayColumns.forEach(col => {
                // If the user selected a default column like "Kewarganegaraan", we try to render the default smart extraction if the raw header is not found.
                // But wait, the displayColumns are EXACT headers from spreadsheetHeaders.
                // So we just fetch parsedData[col]
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
                    val = `<i class="bi bi-whatsapp"></i> ${h}<br><i class="bi bi-envelope"></i> ${e}`;
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
                            waLink += `<a href="https://wa.me/${cleanNo}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 mt-1 ms-1" title="Chat WhatsApp ${cleanNo}"><i class="bi bi-whatsapp"></i>${label}</a>`;
                        }
                    });
                }
            }

            if (status === 'Pending') {
                rowHtml += `<td class="text-nowrap sticky-right bg-white">
                        ${dupBadge}<br>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 mt-1" onclick="lihatBerkas(${d.id})"><i class="bi bi-folder-symlink"></i> Berkas</button>${waLink}
                        <button class="btn btn-sm btn-success rounded-pill px-3 py-1 mt-1 ms-1" onclick="bukaModalTerima(${d.id}, '${d.nama_lengkap.replace(/'/g, "\\'")}')">Terima</button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 mt-1 ms-1" onclick="tolak(${d.id})">Tolak</button>
                    </td>
                </tr>`;
            } else {
                rowHtml += `<td class="text-nowrap sticky-right bg-white">
                        <span class="badge bg-secondary mb-1">${d.status_approval}</span><br>
                        ${d.final_status_santri ? `<span class="badge bg-info text-dark">${d.final_status_santri}</span>` : ''}
                    </td>
                </tr>`;
            }
            html += rowHtml;
        });
        tbody.innerHTML = html;
        
        if (status === 'Pending') {
            attachCheckboxListeners('Pending', 'btnBulkApprove', 'countSelected', 'btnBulkDeletePending', 'countSelectedPendingDel');
        } else if (status === 'Approved') {
            attachCheckboxListeners('Approved', 'btnBulkCancel', 'countSelectedApprovedCancel', 'btnBulkDeleteApproved', 'countSelectedApprovedDel');
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

function attachCheckboxListeners(status, btnId1, countId1, btnId2, countId2) {
    const chkAll = document.getElementById('chkAll' + status);
    const chks = document.querySelectorAll('.chk-' + status);
    const btn1 = document.getElementById(btnId1);
    const count1 = document.getElementById(countId1);
    const btn2 = btnId2 ? document.getElementById(btnId2) : null;
    const count2 = countId2 ? document.getElementById(countId2) : null;

    const updateBtn = () => {
        const checked = document.querySelectorAll('.chk-' + status + ':checked');
        if (checked.length > 0) {
            if(btn1) btn1.classList.remove('d-none');
            if(btn2) btn2.classList.remove('d-none');
            if(count1) count1.textContent = checked.length;
            if(count2) count2.textContent = checked.length;
        } else {
            if(btn1) btn1.classList.add('d-none');
            if(btn2) btn2.classList.add('d-none');
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
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal mengeksekusi aksi.', 'error');
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
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => loadData('Pending'));

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
