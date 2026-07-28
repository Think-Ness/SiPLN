<?php
declare(strict_types=1);
use Yiisoft\View\WebView;

/** @var WebView $this */
/** @var array $dbFields */
$this->setTitle('Import Data dari Excel | Sistem Informasi');
?>

<style>
/* ============ WIZARD STEPPER ============ */
.wizard-steps { display:flex; gap:0; margin-bottom:32px; position:relative; }
.wizard-steps::before { content:''; position:absolute; top:22px; left:60px; right:60px; height:3px; background:#e2e8f0; z-index:0; border-radius:2px; }
.wizard-step { flex:1; text-align:center; position:relative; z-index:1; }
.wizard-step .step-circle {
    width:44px; height:44px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
    font-weight:700; font-size:16px; border:3px solid #e2e8f0; background:#fff; color:#94a3b8;
    transition: all .3s ease; margin-bottom:8px;
}
.wizard-step.active .step-circle { border-color:#3b82f6; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 4px 15px rgba(59,130,246,.35); }
.wizard-step.done .step-circle { border-color:#22c55e; background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; }
.wizard-step .step-label { font-size:.75rem; font-weight:600; color:#94a3b8; }
.wizard-step.active .step-label { color:#3b82f6; }
.wizard-step.done .step-label { color:#22c55e; }

/* ============ UPLOAD ZONE ============ */
.upload-zone {
    border:2px dashed #cbd5e1; border-radius:16px; padding:48px 32px; text-align:center;
    cursor:pointer; transition:all .3s ease; background:#fafbfc;
}
.upload-zone:hover, .upload-zone.dragover { border-color:#3b82f6; background:#eff6ff; }
.upload-zone .upload-icon { font-size:3.5rem; color:#94a3b8; margin-bottom:12px; transition:color .3s; }
.upload-zone:hover .upload-icon { color:#3b82f6; }

/* ============ MAPPING TABLE ============ */
.mapping-table th { font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; color:#64748b; font-weight:700; }
.mapping-table td { vertical-align:middle; }
.mapping-arrow { font-size:1.2rem; color:#94a3b8; }
.excel-col-preview { background:#fef3c7; padding:2px 8px; border-radius:6px; font-size:.78rem; font-weight:600; color:#92400e; display:inline-block; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.required-check { width:18px; height:18px; cursor:pointer; accent-color:#ef4444; }

/* ============ PREVIEW TABLE ============ */
.preview-table { font-size:.78rem; }
.preview-table thead th { position:sticky; top:0; background:#f1f5f9; z-index:2; font-size:.7rem; text-transform:uppercase; letter-spacing:.5px; }
.preview-row-error { background:#fef2f2 !important; }
.preview-row-dup { background:#fffbeb !important; }

/* ============ RESULT ============ */
.result-stat { text-align:center; padding:16px; border-radius:12px; }
.result-stat .stat-num { font-size:2rem; font-weight:800; line-height:1; }
.result-stat .stat-label { font-size:.72rem; text-transform:uppercase; letter-spacing:1px; margin-top:4px; }

/* ============ MISC ============ */
.step-panel { display:none; animation: fadeInUp .35s ease; }
.step-panel.active { display:block; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }
.badge-group { font-size:.65rem; padding:2px 8px; border-radius:4px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
</style>

<!-- ===== PAGE HEADER ===== -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:45px;height:45px;background:#0d6efd15;">
            <i class="bi bi-file-earmark-spreadsheet fs-5 text-primary"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing:-.5px;">Import Data dari Excel</h4>
            <div class="text-muted small fw-medium mt-1">Upload file Excel, mapping kolom, preview &amp; import</div>
        </div>
    </div>
    <a href="<?= API_URL ?>/master-data" class="btn btn-light border rounded-pill px-4 fw-medium shadow-sm text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Master Data
    </a>
</div>

<!-- ===== WIZARD STEPPER ===== -->
<div class="wizard-steps mb-4">
    <div class="wizard-step active" id="ws1">
        <div class="step-circle">1</div>
        <div class="step-label">Upload File</div>
    </div>
    <div class="wizard-step" id="ws2">
        <div class="step-circle">2</div>
        <div class="step-label">Mapping Kolom</div>
    </div>
    <div class="wizard-step" id="ws3">
        <div class="step-circle">3</div>
        <div class="step-label">Preview Data</div>
    </div>
    <div class="wizard-step" id="ws4">
        <div class="step-circle">4</div>
        <div class="step-label">Hasil Import</div>
    </div>
</div>

<!-- ===== STEP 1: UPLOAD ===== -->
<div class="step-panel active" id="step1">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <div class="upload-zone" id="uploadZone"
                 onclick="document.getElementById('excelFileInput').click()"
                 ondragover="event.preventDefault(); this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="event.preventDefault(); this.classList.remove('dragover'); handleFileDrop(event)">
                <i class="bi bi-cloud-arrow-up upload-icon d-block"></i>
                <h5 class="fw-bold text-dark mb-2">Klik atau Drag &amp; Drop File Excel</h5>
                <p class="text-muted mb-3" style="font-size:.85rem;">Mendukung format <code>.xlsx</code>, <code>.xls</code>, dan <code>.csv</code></p>
                <span class="badge bg-light text-secondary border px-3 py-2" style="font-size:.78rem;">
                    <i class="bi bi-info-circle me-1"></i> Baris pertama harus berisi nama kolom (header)
                </span>
            </div>
            <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" class="d-none" onchange="handleFileSelect(this)">
            
            <div id="fileInfo" class="d-none mt-4">
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
                    <i class="bi bi-file-earmark-excel fs-3 text-success"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark" id="fileName">-</div>
                        <div class="text-muted small" id="fileRowCount">-</div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="resetUpload()">
                        <i class="bi bi-x-lg me-1"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== STEP 2: MAPPING ===== -->
<div class="step-panel" id="step2">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right text-primary me-2"></i>Mapping Kolom Excel ke Field Database</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="autoMatchColumns()">
                    <i class="bi bi-magic me-1"></i>Auto Match
                </button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="clearAllMapping()">
                    <i class="bi bi-eraser me-1"></i>Reset Semua
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <div id="sheetSelectionWrapper" class="d-none mb-4 p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-dark mb-2"><i class="bi bi-layers me-2 text-primary"></i>Pilih Sheet Excel</label>
                <select id="sheetSelector" class="form-select border shadow-sm" onchange="reloadSheetData(this.value)">
                </select>
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>File Excel Anda memiliki lebih dari satu sheet. Silakan pilih sheet mana yang berisi data santri.</div>
            </div>

            <div class="alert alert-info border-0 rounded-3 mb-4" style="font-size:.82rem;">
                <i class="bi bi-lightbulb me-1"></i>
                <strong>Tips:</strong> Pilih field database yang sesuai untuk setiap kolom Excel. Centang kolom <strong>"Wajib"</strong> untuk menandai field yang harus terisi. Kolom yang tidak dipetakan akan diabaikan.
            </div>

            <!-- Duplicate Mode -->
            <div class="mb-4 p-3 bg-light rounded-3 border">
                <div class="fw-bold small text-dark mb-2"><i class="bi bi-shield-check text-primary me-1"></i>Jika Stambuk / Nama Sudah Ada di Database:</div>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="dupMode" id="dupSkip" value="skip" checked>
                        <label class="form-check-label small fw-medium" for="dupSkip">Lewati (Skip)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="dupMode" id="dupUpdate" value="update">
                        <label class="form-check-label small fw-medium" for="dupUpdate">Perbarui (Update)</label>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table mapping-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Kolom Excel</th>
                            <th style="width:50px;" class="text-center"></th>
                            <th>Field Database</th>
                            <th style="width:60px;" class="text-center">Wajib</th>
                            <th>Sample Data</th>
                        </tr>
                    </thead>
                    <tbody id="mappingBody"></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-light border rounded-pill px-4 fw-medium shadow-sm" onclick="goToStep(1)">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </button>
                <button class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" onclick="generatePreview()">
                    Preview Data <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== STEP 3: PREVIEW ===== -->
<div class="step-panel" id="step3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-primary me-2"></i>Preview Data Import</h6>
            <div class="d-flex gap-3 align-items-center">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2" id="previewValidCount">0 Valid</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2" id="previewErrorCount">0 Error</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2" id="previewDupCount">0 Duplikat</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive" style="max-height:450px; overflow-y:auto;">
                <table id="previewTable" class="table preview-table table-hover align-middle mb-0" style="width:100%">
                    <thead id="previewHead"></thead>
                    <tbody id="previewBody"></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-light border rounded-pill px-4 fw-medium shadow-sm" onclick="goToStep(2)">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Mapping
                </button>
                <button class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" id="btnImport" onclick="executeImport()">
                    <i class="bi bi-download me-1"></i>Import Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== STEP 4: RESULT ===== -->
<div class="step-panel" id="step4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <div id="resultIcon" class="mb-3"></div>
            <h4 class="fw-bold text-dark mb-4" id="resultTitle">Import Selesai</h4>
            <div class="row justify-content-center g-3 mb-4" id="resultStats"></div>
            <div id="resultErrors" class="text-start mb-4" style="display:none;"></div>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= API_URL ?>/master-data" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-people me-1"></i>Lihat Data Santri
                </a>
                <button class="btn btn-outline-primary rounded-pill px-4 fw-medium shadow-sm" onclick="resetAll()">
                    <i class="bi bi-arrow-repeat me-1"></i>Import Lagi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden print/preview area -->
<div id="printRekapArea" style="display:none;"></div>

<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>
<script>
// ========== STATE ==========
const DB_FIELDS = <?= json_encode($dbFields) ?>;
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let excelHeaders = [];
let excelRows = [];
let currentStep = 1;

// ========== STEP NAVIGATION ==========
function goToStep(n) {
    currentStep = n;
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    document.querySelectorAll('.wizard-step').forEach((el, i) => {
        el.classList.remove('active', 'done');
        if (i + 1 < n) el.classList.add('done');
        if (i + 1 === n) el.classList.add('active');
    });
}

// ========== STEP 1: UPLOAD ==========
function handleFileDrop(e) {
    const files = e.dataTransfer.files;
    if (files.length) processFile(files[0]);
}

function handleFileSelect(input) {
    if (input.files.length) processFile(input.files[0]);
}

function processFile(file) {
    const validExts = ['xlsx', 'xls', 'csv'];
    const ext = file.name.split('.').pop().toLowerCase();
    if (!validExts.includes(ext)) {
        Swal.fire('Format Salah', 'Gunakan file .xlsx, .xls, atau .csv', 'error');
        return;
    }

    const fd = new FormData();
    fd.append('excel_file', file);

    Swal.fire({ title:'Membaca file...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

    fetch('<?= API_URL ?>/api/import-excel/parse', { method:'POST', body:fd, headers: { 'X-CSRF-Token': csrfToken } })
    .then(r => r.json())
    .then(res => {
        Swal.close();
        if (!res.success) {
            Swal.fire('Gagal', res.message, 'error');
            return;
        }
        excelHeaders = res.headers;
        excelRows = res.rows;

        document.getElementById('fileName').textContent = res.fileName;
        document.getElementById('fileRowCount').textContent = res.totalRows + ' baris data ditemukan';
        document.getElementById('fileInfo').classList.remove('d-none');
        
        const sheetWrapper = document.getElementById('sheetSelectionWrapper');
        if (res.sheetNames && res.sheetNames.length > 1) {
            const ss = document.getElementById('sheetSelector');
            ss.innerHTML = res.sheetNames.map((name, idx) => `<option value="${idx}" ${idx === res.currentSheetIndex ? 'selected' : ''}>${name}</option>`).join('');
            sheetWrapper.classList.remove('d-none');
        } else {
            sheetWrapper.classList.add('d-none');
        }

        buildMappingTable();
        setTimeout(() => goToStep(2), 400);
    })
    .catch(() => { Swal.close(); Swal.fire('Error', 'Gagal menghubungi server.', 'error'); });
}

function reloadSheetData(sheetIndex) {
    Swal.fire({ title:'Memuat Sheet...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
    
    const fd = new FormData();
    fd.append('sheet_index', sheetIndex);
    
    fetch('<?= API_URL ?>/api/import-excel/parse', { method:'POST', body:fd, headers: { 'X-CSRF-Token': csrfToken } })
    .then(r => r.json())
    .then(res => {
        Swal.close();
        if (!res.success) {
            Swal.fire('Gagal', res.message, 'error');
            return;
        }
        excelHeaders = res.headers;
        excelRows = res.rows;
        document.getElementById('fileRowCount').textContent = res.totalRows + ' baris data ditemukan';
        buildMappingTable();
    })
    .catch(() => { Swal.close(); Swal.fire('Error', 'Gagal memuat sheet dari server.', 'error'); });
}

function resetUpload() {
    excelHeaders = [];
    excelRows = [];
    document.getElementById('excelFileInput').value = '';
    document.getElementById('fileInfo').classList.add('d-none');
}

// ========== STEP 2: MAPPING ==========
function buildMappingTable() {
    const tbody = document.getElementById('mappingBody');
    let html = '';

    excelHeaders.forEach((header, idx) => {
        const sampleVals = excelRows.slice(0, 3).map(r => r[idx] || '').filter(v => v !== '');
        const sampleText = sampleVals.join(', ') || '<span class="text-muted fst-italic">-kosong-</span>';

        html += `<tr>
            <td class="text-muted fw-bold">${idx + 1}</td>
            <td><span class="excel-col-preview" title="${escHtml(header)}">${escHtml(header)}</span></td>
            <td class="text-center"><i class="bi bi-arrow-right mapping-arrow"></i></td>
            <td>
                <select class="form-select form-select-sm mapping-select border shadow-sm" id="map_${idx}" style="max-width:280px;">
                    <option value="">-- Jangan Import --</option>
                    ${buildFieldOptions()}
                </select>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input required-check" id="req_${idx}">
            </td>
            <td><span class="text-muted" style="font-size:.75rem;">${sampleText}</span></td>
        </tr>`;
    });

    tbody.innerHTML = html;
    autoMatchColumns();
}

function buildFieldOptions() {
    let groups = {};
    DB_FIELDS.forEach(f => {
        if (!groups[f.group]) groups[f.group] = [];
        groups[f.group].push(f);
    });
    let html = '';
    Object.keys(groups).forEach(g => {
        html += `<optgroup label="${g}">`;
        groups[g].forEach(f => {
            html += `<option value="${f.key}">${f.label}</option>`;
        });
        html += `</optgroup>`;
    });
    return html;
}

function autoMatchColumns() {
    const aliases = {
        'stambuk': ['stambuk','regno','reg no','nomor induk','nim','nis','no induk'],
        'nama': ['nama','name','nama lengkap','full name','nama santri'],
        'kelas': ['kelas','class','grade'],
        'rayon': ['rayon','region'],
        'negara': ['negara','negara asal','country','nationality','kewarganegaraan'],
        'tempat_lahir': ['tempat lahir','tempat_lahir','place of birth','pob'],
        'tanggal_lahir': ['tanggal lahir','tanggal_lahir','tgl lahir','date of birth','dob','ttl'],
        'jenis_kelamin': ['jenis kelamin','jk','gender','sex','l/p'],
        'alamat': ['alamat','address'],
        'pondok': ['pondok','pondok pesantren','asrama'],
        'kepengurusan': ['kepengurusan','instansi','organisasi','kep'],
        'no_sktt': ['nik','no nik','sktt','no sktt','no_sktt'],
        'no_ic': ['no ic','no_ic','ic','nomor ic'],
        'ukuran_baju': ['ukuran baju','ukuran','size','baju'],
        'nama_ayah': ['nama ayah','ayah','father','father name'],
        'nama_ibu': ['nama ibu','ibu','mother','mother name'],
        'no_ayah': ['no ayah','no hp ayah','hp ayah','telp ayah'],
        'no_ibu': ['no ibu','no hp ibu','hp ibu','telp ibu'],
        'no_hp_alternatif': ['no hp alternatif','hp alternatif','no alt','hp lain'],
        'no_paspor': ['no paspor','no_paspor','passport no','passport number','paspor'],
        'exp_paspor': ['exp paspor','exp_paspor','expired paspor','passport expiry'],
        'tempat_paspor': ['tempat dikeluarkan','tempat paspor','issued at','place of issue'],
        'tgl_paspor': ['tgl dikeluarkan','tgl paspor','date of issue','issued date'],
        'no_itas': ['no itas','no_itas','itas number','itas'],
        'exp_itas': ['exp itas','exp_itas','expired itas','itas expiry'],
        'level_itas': ['level itas','level_itas','lvl itas','lvl'],
        'keberadaan_paspor': ['keberadaan paspor','keberadaan_paspor','paspor status'],
    };

    const usedFields = new Set();

    excelHeaders.forEach((header, idx) => {
        const sel = document.getElementById('map_' + idx);
        const hLower = header.toLowerCase().trim();

        for (const [fieldKey, aliasList] of Object.entries(aliases)) {
            if (usedFields.has(fieldKey)) continue;
            for (const alias of aliasList) {
                if (hLower === alias || hLower.includes(alias) || alias.includes(hLower)) {
                    sel.value = fieldKey;
                    usedFields.add(fieldKey);

                    // Auto-check required for stambuk & nama
                    if (fieldKey === 'stambuk' || fieldKey === 'nama') {
                        document.getElementById('req_' + idx).checked = true;
                    }
                    break;
                }
            }
            if (usedFields.has(fieldKey)) break;
        }
    });
}

function clearAllMapping() {
    excelHeaders.forEach((_, idx) => {
        document.getElementById('map_' + idx).value = '';
        document.getElementById('req_' + idx).checked = false;
    });
}

// ========== STEP 3: PREVIEW ==========
function generatePreview() {
    const mapping = {};
    const requiredFields = [];
    const mappedDbFields = [];

    excelHeaders.forEach((_, idx) => {
        const sel = document.getElementById('map_' + idx);
        const req = document.getElementById('req_' + idx);
        if (sel.value) {
            mapping[idx] = sel.value;
            const fieldInfo = DB_FIELDS.find(f => f.key === sel.value);
            mappedDbFields.push({ idx, key: sel.value, label: fieldInfo?.label || sel.value });
            if (req.checked) requiredFields.push(sel.value);
        }
    });

    if (mappedDbFields.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal 1 kolom untuk di-mapping.', 'warning');
        return;
    }

    // Build preview header
    let headHtml = '<tr class="table-light text-muted small"><th class="ps-3" style="width:40px;">#</th>';
    mappedDbFields.forEach(f => {
        const isReq = requiredFields.includes(f.key);
        headHtml += `<th>${f.label} ${isReq ? '<span class="text-danger">*</span>' : ''}</th>`;
    });
    headHtml += '<th class="text-center" style="width:80px;">Status</th></tr>';
    
    // Add filter row
    headHtml += '<tr class="filter-row bg-light"><th></th>';
    let colIdx = 1;
    mappedDbFields.forEach(f => {
        headHtml += `<th><input type="text" class="form-control form-control-sm dt-search-preview" data-col="${colIdx}" placeholder="Cari..."></th>`;
        colIdx++;
    });
    headHtml += `<th></th></tr>`;

    document.getElementById('previewHead').innerHTML = headHtml;

    // Build preview rows
    let bodyHtml = '';
    let validCount = 0, errorCount = 0, dupCount = 0;

    excelRows.forEach((row, ri) => {
        let rowClass = '';
        let statusBadge = '<span class="badge bg-success-subtle text-success rounded-pill px-2">OK</span>';
        let hasError = false;

        // Check required fields
        for (const rf of requiredFields) {
            const rfColIdx = parseInt(Object.keys(mapping).find(k => mapping[k] === rf));
            if (isNaN(rfColIdx) || !row[rfColIdx] || row[rfColIdx].trim() === '') {
                hasError = true;
                break;
            }
        }

        if (hasError) {
            rowClass = 'preview-row-error';
            statusBadge = '<span class="badge bg-danger-subtle text-danger rounded-pill px-2">Error</span>';
            errorCount++;
        } else {
            validCount++;
        }

        bodyHtml += `<tr class="${rowClass}">`;
        bodyHtml += `<td class="ps-3 text-muted fw-bold">${ri + 1}</td>`;
        mappedDbFields.forEach(f => {
            let val = row[f.idx] || '';
            if (f.key === 'stambuk') {
                val = val.replace(/[^0-9]/g, '');
            }
            const isEmpty = val.trim() === '';
            const isRequired = requiredFields.includes(f.key);
            const cellClass = (isEmpty && isRequired) ? 'text-danger fw-bold' : '';
            bodyHtml += `<td class="${cellClass}">${isEmpty ? '<em class="text-muted">-kosong-</em>' : escHtml(val)}</td>`;
        });
        bodyHtml += `<td class="text-center">${statusBadge}</td>`;
        bodyHtml += '</tr>';
    });

    document.getElementById('previewBody').innerHTML = bodyHtml;
    document.getElementById('previewValidCount').textContent = validCount + ' Valid';
    document.getElementById('previewErrorCount').textContent = errorCount + ' Error';
    document.getElementById('previewDupCount').textContent = dupCount + ' Duplikat';

    // Store mapping for execute
    window._importMapping = mapping;
    window._importRequired = requiredFields;

    goToStep(3);

    // Initialize DataTable after step 3 is visible
    setTimeout(() => {
        if ($.fn.DataTable.isDataTable('#previewTable')) {
            $('#previewTable').DataTable().destroy();
        }
        window.previewTableInst = $('#previewTable').DataTable({
            pageLength: 20,
            lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "Semua"]],
            dom: "<'row mb-3 pt-3 px-3 align-items-center'<'col-sm-12 col-md-6 d-flex align-items-center gap-3'l B><'col-sm-12 col-md-6 px-4 text-end'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row px-3 pb-3'<'col-sm-12 col-md-5 mt-2'i><'col-sm-12 col-md-7 mt-2'p>>",
            language: {
                search: "🔍 Pencarian Cepat:",
                lengthMenu: "Tampil _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ baris",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
            },
            orderCellsTop: true,
            initComplete: function() {
                $('.dt-search-preview').on('keyup change clear', function() {
                    var col = $(this).data('col');
                    window.previewTableInst.column(col).search(this.value).draw();
                });
            }
        });
    }, 100);
}

// ========== STEP 4: EXECUTE ==========
function executeImport() {
    const dupMode = document.querySelector('input[name="dupMode"]:checked')?.value || 'skip';

    Swal.fire({
        title: 'Konfirmasi Import',
        html: `Anda akan mengimport <strong>${excelRows.length}</strong> baris data.<br>Duplikat: <strong>${dupMode === 'skip' ? 'Lewati' : 'Perbarui'}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Import!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#22c55e',
    }).then(result => {
        if (!result.isConfirmed) return;

        document.getElementById('btnImport').disabled = true;
        Swal.fire({ title:'Sedang mengimport...', html:'Mohon tunggu, jangan tutup halaman.', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

        fetch('<?= API_URL ?>/api/import-excel/execute', {
            method:'POST',
            headers:{'Content-Type':'application/json', 'X-CSRF-Token': csrfToken},
            body: JSON.stringify({
                mapping: window._importMapping,
                requiredFields: window._importRequired,
                duplicateMode: dupMode,
            })
        })
        .then(r => r.json())
        .then(res => {
            Swal.close();
            if (!res.success) {
                Swal.fire('Gagal', res.message, 'error');
                document.getElementById('btnImport').disabled = false;
                return;
            }
            showResults(res.stats);
        })
        .catch(() => {
            Swal.close();
            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
            document.getElementById('btnImport').disabled = false;
        });
    });
}

function showResults(stats) {
    const total = stats.success + stats.updated + stats.skipped + stats.failed;
    const successRate = total > 0 ? Math.round(((stats.success + stats.updated) / total) * 100) : 0;

    document.getElementById('resultIcon').innerHTML = successRate >= 80
        ? '<i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>'
        : '<i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:4rem;"></i>';

    document.getElementById('resultTitle').textContent = successRate >= 80
        ? 'Import Berhasil!' : 'Import Selesai dengan Catatan';

    document.getElementById('resultStats').innerHTML = `
        <div class="col-auto"><div class="result-stat bg-success-subtle border border-success-subtle rounded-4 px-4">
            <div class="stat-num text-success">${stats.success}</div><div class="stat-label text-success">Baru</div>
        </div></div>
        <div class="col-auto"><div class="result-stat bg-primary-subtle border border-primary-subtle rounded-4 px-4">
            <div class="stat-num text-primary">${stats.updated}</div><div class="stat-label text-primary">Diperbarui</div>
        </div></div>
        <div class="col-auto"><div class="result-stat bg-warning-subtle border border-warning-subtle rounded-4 px-4">
            <div class="stat-num text-warning">${stats.skipped}</div><div class="stat-label text-warning">Dilewati</div>
        </div></div>
        <div class="col-auto"><div class="result-stat bg-danger-subtle border border-danger-subtle rounded-4 px-4">
            <div class="stat-num text-danger">${stats.failed}</div><div class="stat-label text-danger">Gagal</div>
        </div></div>
    `;

    if (stats.errors && stats.errors.length > 0) {
        const errDiv = document.getElementById('resultErrors');
        errDiv.style.display = 'block';
        let errHtml = '<div class="alert alert-danger border-0 rounded-3" style="max-height:200px;overflow-y:auto;font-size:.82rem;"><strong><i class="bi bi-exclamation-triangle me-1"></i>Detail Error:</strong><ul class="mb-0 mt-2">';
        stats.errors.slice(0, 20).forEach(e => errHtml += `<li>${escHtml(e)}</li>`);
        if (stats.errors.length > 20) errHtml += `<li class="text-muted">...dan ${stats.errors.length - 20} error lainnya</li>`;
        errHtml += '</ul></div>';
        errDiv.innerHTML = errHtml;
    }

    goToStep(4);
}

function resetAll() {
    excelHeaders = [];
    excelRows = [];
    document.getElementById('excelFileInput').value = '';
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('mappingBody').innerHTML = '';
    document.getElementById('previewHead').innerHTML = '';
    document.getElementById('previewBody').innerHTML = '';
    document.getElementById('resultStats').innerHTML = '';
    document.getElementById('resultErrors').style.display = 'none';
    document.getElementById('btnImport').disabled = false;
    goToStep(1);
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
</script>
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/dataTables.bootstrap5.min.css">
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>
