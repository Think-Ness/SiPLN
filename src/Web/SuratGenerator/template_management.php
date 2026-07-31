<?php
/**
 * @var \Yiisoft\View\WebView $this
 * @var array $folderList
 * @var string $role
 */
$this->setTitle('Kelola Template Surat | Sistem Informasi');
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing:-.5px;">Kelola Template Surat</h4>
            <div class="text-muted small fw-medium mt-1">Unggah dan kelola template Microsoft Word (.docx) per instansi tujuan</div>
        </div>
        <div class="d-flex gap-2 mt-3 mt-sm-0">
            <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahTemplate">
                <i class="bi bi-cloud-arrow-up"></i>
                <span class="fw-medium">Tambah Template</span>
            </button>
        </div>
    </div>

    <!-- Panduan Bookmark & Tag Variabel -->
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex gap-3 align-items-start">
        <div class="bg-white text-info rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
            <i class="bi bi-tags-fill fs-5"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-2">Panduan Variabel Otomatis (Tag & Bookmark)</h6>
            <p class="mb-2 text-dark small">Sistem mendukung dua cara untuk menempatkan data otomatis di Microsoft Word:</p>
            <ul class="mb-2 text-dark small ps-3">
                <li><strong>Metode Tag (Disarankan):</strong> Ketik langsung di teks Word dengan format <code>${NamaVariabel}</code> (Contoh: <code>${Tanggal_Buat}</code>).</li>
                <li><strong>Metode Bookmark:</strong> Gunakan fitur Bookmark (Insert → Bookmark) dengan nama variabel.</li>
            </ul>
            <p class="mb-1 text-dark small fw-bold">Daftar Variabel Sistem yang Tersedia:</p>
            <div class="row g-2 small text-dark mt-2">
                <div class="col-md-4">
                    <div class="fw-bold text-primary mb-1">Header Surat</div>
                    <ul class="mb-0">
                        <li><code>NO</code> : Nomor Surat (angka urut)</li>
                        <li><code>Bln_Romawi</code> : Bulan dalam Romawi</li>
                        <li><code>Tahun_Buat</code> : Tahun Surat</li>
                        <li><code>Tanggal_Buat</code> : Tanggal Surat (Indo)</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <div class="fw-bold text-primary mb-1">Isi Surat</div>
                    <ul class="mb-0">
                        <li><code>Hal</code> : Perihal Surat</li>
                        <li><code>Isi</code> : Konten Surat</li>
                        <li><code>Kepada</code> : Tujuan Surat</li>
                        <li><code>Tempat</code> : Tempat Tujuan</li>
                        <li><code>JML_LAMPIRAN</code> : Jumlah Lampiran</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <div class="fw-bold text-primary mb-1">Data Santri (Perseorangan)</div>
                    <ul class="mb-0">
                        <li><code>Nama_Santri</code> : Nama Lengkap</li>
                        <li><code>Tempat_Lahir</code> : Tempat Lahir</li>
                        <li><code>Tgl_Lahir</code> : Tanggal Lahir</li>
                        <li><code>Kewarganegaraan</code> : Kewarganegaraan</li>
                        <li><code>No_Paspor</code> : Nomor Paspor</li>
                        <li><code>Tgl_Berlaku</code> : Exp. Paspor</li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-info opacity-25 my-3">
            <h6 class="fw-bold mb-2">Panduan Data Collection (Tabel Dinamis)</h6>
            <p class="mb-2 text-dark small">Gunakan fitur ini jika surat Anda membutuhkan input berupa daftar/tabel (contoh: Daftar Undangan, Daftar Pinjaman, dll).</p>
            <ol class="small text-dark mb-0 ps-3">
                <li class="mb-1">Di <strong>Microsoft Word</strong>, buat tabel seperti biasa. Pada baris isi data (bukan header), ketikkan variabel macro di sel pertama: <code>${NamaCollection}</code> (Contoh: <code>${Daftar_Undangan}</code>).</li>
                <li class="mb-1">Kolom lainnya di baris yang sama diisi dengan variabel kolom (Contoh: <code>${Nama_Lengkap}</code>, <code>${Jabatan}</code>).</li>
                <li class="mb-1">Di sistem (saat Upload Template), centang opsi <strong>Menggunakan Data Collection</strong>.</li>
                <li class="mb-1">Isi <strong>Nama Collection</strong> persis seperti variabel di sel pertama Word tadi (Contoh: <code>Daftar_Undangan</code>).</li>
                <li class="mb-1">Tambahkan nama-nama kolom sesuai variabel yang Anda tulis di Word (Contoh: <code>Nama_Lengkap</code>, <code>Jabatan</code>).</li>
                <li>Saat proses <strong>Generate Surat</strong> (Langkah 3), formulir akan muncul otomatis untuk mengisi baris-baris ini dan sistem akan menggandakan tabel di Word secara instan!</li>
            </ol>

            <div class="mt-3 small text-muted border-top border-info-subtle pt-2">
                <em>* Catatan: Untuk template <strong>Banyak Orang (Sekaligus)</strong> tipe klasik, data santri otomatis dilampirkan sebagai halaman baru di akhir dokumen.</em>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableTemplates">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-secondary" style="width: 50px">No</th>
                            <th class="fw-semibold text-secondary">Nama Template</th>
                            <th class="fw-semibold text-secondary">Peruntukan</th>
                            <th class="fw-semibold text-secondary">File</th>
                            <th class="fw-semibold text-secondary text-end pe-4" style="width: 100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Template -->
<div class="modal fade" id="modalTambahTemplate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="formTambahTemplate">
                <div class="modal-header bg-light border-bottom-0 p-4">
                    <h5 class="modal-title fw-bold">Upload Template Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="nama_template" placeholder="Misal: Surat Perizinan" required>
                        <div class="form-text small mt-1">Akan menjadi nama file: <code>Surat_Perizinan_Satu_Orang.docx</code></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Instansi Tujuan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-select rounded-start-3" name="instansi_tujuan_select" id="selInstansiTujuan" onchange="onInstansiSelectChange()">
                                <option value="">-- Pilih Instansi Tujuan --</option>
                                <?php foreach ($folderList as $folder): ?>
                                    <option value="<?= htmlspecialchars($folder) ?>"><?= htmlspecialchars(str_replace('_', ' ', $folder)) ?></option>
                                <?php endforeach; ?>
                                <option value="__baru__">+ Tambah Instansi Baru...</option>
                            </select>
                        </div>
                        <input type="text" class="form-control rounded-3 mt-2 d-none" name="instansi_tujuan_baru" id="inputInstansiBaru" placeholder="Ketik nama instansi tujuan baru (misal: Pengasuhan)">
                        <input type="hidden" name="instansi_tujuan" id="hiddenInstansiTujuan">
                        <div class="form-text small mt-1">Folder penyimpanan: <code>Surat_Menyurat/<span id="previewFolder">...</span>/</code></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Peruntukan Surat <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" name="peruntukan" required>
                            <option value="perseorangan">Satu Orang (Perseorangan)</option>
                            <option value="sekaligus">Banyak Orang (Sekaligus / Tabel)</option>
                        </select>
                        <div class="form-text small mt-1">Pilih "Banyak Orang" jika isi suratnya berupa tabel daftar nama.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">File Template Word (.docx) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control rounded-3" name="file" accept=".docx" required>
                        <div class="form-text small mt-1 text-primary"><i class="bi bi-info-circle"></i> Hanya menerima file ekstensi .docx. Pastikan sudah menggunakan Bookmark.</div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="cbOverwrite">
                            <label class="form-check-label small" for="cbOverwrite">Timpa file jika nama template dan peruntukan sama (Update/Edit)</label>
                        </div>
                    </div>

                    <!-- Dynamic Data Collection Section -->
                    <div class="mb-3 border rounded-3 p-3 bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="cbUseDataCollection" onchange="toggleDataCollection()">
                            <label class="form-check-label fw-medium text-secondary" for="cbUseDataCollection">Menggunakan Data Collection (Tabel Dinamis)</label>
                        </div>
                        <div class="form-text small mb-2">Aktifkan jika template membutuhkan input data berupa tabel/daftar (contoh: Daftar Undangan, Daftar Barang).</div>
                        
                        <div id="dataCollectionSection" class="d-none mt-3">
                            <div id="collectionContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addCollection()">
                                <i class="bi bi-plus-circle"></i> Tambah Collection
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Custom Inputs Section -->
                    <div class="mb-3 border rounded-3 p-3 bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="cbUseCustomInputs" onchange="toggleCustomInputs()">
                            <label class="form-check-label fw-medium text-secondary" for="cbUseCustomInputs">Membutuhkan Input Kustom (Variabel Tambahan)</label>
                        </div>
                        <div class="form-text small mb-2">Aktifkan jika template membutuhkan input teks khusus selain data standar (contoh: Waktu, Tempat Acara, Nama Pemateri). Gunakan Bookmark atau tag <code>\${NamaVariabel}</code> di Word.</div>
                        
                        <div id="customInputsSection" class="d-none mt-3">
                            <div id="customInputContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addCustomInput()">
                                <i class="bi bi-plus-circle"></i> Tambah Input Kustom
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnUpload">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="uploadSpinner"></span>
                        Upload & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_URL = '<?= API_URL ?>';
const ASSET_URL = '<?= ASSET_URL ?>';

document.addEventListener('DOMContentLoaded', () => {
    loadTemplates();

    const form = document.getElementById('formTambahTemplate');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('btnUpload');
        const spinner = document.getElementById('uploadSpinner');
        
        // Resolve instansi_tujuan value
        const sel = document.getElementById('selInstansiTujuan');
        const baruInput = document.getElementById('inputInstansiBaru');
        const hidden = document.getElementById('hiddenInstansiTujuan');
        
        if (sel.value === '__baru__') {
            hidden.value = baruInput.value.trim();
        } else {
            hidden.value = sel.value;
        }
        
        if (!hidden.value) {
            Swal.fire('Peringatan', 'Instansi Tujuan wajib dipilih atau diisi.', 'warning');
            return;
        }
        
        btn.disabled = true;
        spinner.classList.remove('d-none');
        
        try {
            const formData = new FormData(form);
            // Override instansi_tujuan with resolved value
            formData.set('instansi_tujuan', hidden.value);
            // Remove helper fields
            formData.delete('instansi_tujuan_select');
            formData.delete('instansi_tujuan_baru');
            
            const collections = getCollectionJSON();
            if (collections) {
                formData.set('json_data_collection', JSON.stringify(collections));
            }
            
            const customInputs = getCustomInputsJSON();
            if (customInputs) {
                formData.set('json_custom_inputs', JSON.stringify(customInputs));
            }
            
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(API_URL + '/api/surat/templates/upload', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf },
                body: formData
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            
            Swal.fire('Berhasil', data.message, 'success');
            form.reset();
            document.getElementById('inputInstansiBaru').classList.add('d-none');
            bootstrap.Modal.getInstance(document.getElementById('modalTambahTemplate')).hide();
            loadTemplates();
        } catch(err) {
            Swal.fire('Gagal', err.message, 'error');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
    
    // Event delegation for toggling groups
    document.querySelector('#tableTemplates').addEventListener('click', function(e) {
        const headerRow = e.target.closest('.group-header');
        if (!headerRow) return;
        
        const groupId = headerRow.getAttribute('data-group-id');
        const rows = document.querySelectorAll('.' + groupId);
        const icon = document.getElementById('icon-' + groupId);
        
        if (!rows || rows.length === 0) return;
        
        const isCurrentlyHidden = rows[0].classList.contains('d-none');
        
        rows.forEach(row => {
            if (isCurrentlyHidden) {
                row.classList.remove('d-none');
            } else {
                row.classList.add('d-none');
            }
        });
        
        if (icon) {
            if (isCurrentlyHidden) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            } else {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
        }
    });
});

function onInstansiSelectChange() {
    const sel = document.getElementById('selInstansiTujuan');
    const baruInput = document.getElementById('inputInstansiBaru');
    const preview = document.getElementById('previewFolder');
    
    if (sel.value === '__baru__') {
        baruInput.classList.remove('d-none');
        baruInput.required = true;
        baruInput.focus();
        baruInput.addEventListener('input', () => {
            preview.textContent = baruInput.value.trim() ? baruInput.value.trim().replace(/[^a-zA-Z0-9_\-]/g, '_') : '...';
        });
        preview.textContent = '...';
    } else {
        baruInput.classList.add('d-none');
        baruInput.required = false;
        baruInput.value = '';
        preview.textContent = sel.value ? sel.value.replace(/[^a-zA-Z0-9_\-]/g, '_') : '...';
    }
}

// Data Collection JS Logic
let collectionCount = 0;

function toggleDataCollection() {
    const isChecked = document.getElementById('cbUseDataCollection').checked;
    const sec = document.getElementById('dataCollectionSection');
    if (isChecked) {
        sec.classList.remove('d-none');
        if (document.querySelectorAll('.collection-item').length === 0) addCollection();
    } else {
        sec.classList.add('d-none');
    }
}

function addCollection(name = '', columns = []) {
    collectionCount++;
    const id = collectionCount;
    
    let colFieldsHtml = '';
    if (columns && columns.length > 0) {
        columns.forEach(colName => {
            colFieldsHtml += `
            <div class="d-flex gap-2 column-field-row">
                <input type="text" class="form-control form-control-sm col-field-input" placeholder="Nama Kolom (misal: Nama Lengkap)" value="${escHtml(colName)}" required>
                <button type="button" class="btn btn-sm btn-light text-danger" onclick="if(this.parentElement.parentElement.children.length>1) this.parentElement.remove()"><i class="bi bi-trash"></i></button>
            </div>`;
        });
    } else {
        colFieldsHtml = `
        <div class="d-flex gap-2 column-field-row">
            <input type="text" class="form-control form-control-sm col-field-input" placeholder="Nama Kolom (misal: Nama Lengkap)" required>
            <button type="button" class="btn btn-sm btn-light text-danger" onclick="if(this.parentElement.parentElement.children.length>1) this.parentElement.remove()"><i class="bi bi-trash"></i></button>
        </div>`;
    }

    const html = `
    <div class="collection-item border bg-white p-3 rounded-3 mb-3 position-relative shadow-sm" id="col_${id}">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="removeCollection(${id})" aria-label="Close"></button>
        <div class="mb-2 pe-4">
            <label class="form-label small fw-bold text-dark">Nama Collection (Macro/Bookmark) <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm col-name-input border-secondary" placeholder="Contoh: Daftar_Undangan" value="${escHtml(name)}" required>
            <div class="form-text" style="font-size:0.65rem">Di Word, tabel harus memiliki variabel row <code>\${NamaCollection}</code> di kolom pertamanya.</div>
        </div>
        <div>
            <label class="form-label small fw-bold mb-1 text-dark">Daftar Kolom</label>
            <div id="colFields_${id}" class="d-flex flex-column gap-2 mb-2">
                ${colFieldsHtml}
            </div>
            <button type="button" class="btn btn-sm btn-light border" style="font-size:0.75rem" onclick="addColumnField(${id})"><i class="bi bi-plus"></i> Tambah Kolom</button>
        </div>
    </div>`;
    document.getElementById('collectionContainer').insertAdjacentHTML('beforeend', html);
}

function removeCollection(id) {
    document.getElementById(`col_${id}`).remove();
    if (document.querySelectorAll('.collection-item').length === 0) {
        document.getElementById('cbUseDataCollection').checked = false;
        toggleDataCollection();
    }
}

function addColumnField(id) {
    const html = `
    <div class="d-flex gap-2 column-field-row">
        <input type="text" class="form-control form-control-sm col-field-input" placeholder="Nama Kolom" required>
        <button type="button" class="btn btn-sm btn-light text-danger" onclick="this.parentElement.remove()"><i class="bi bi-trash"></i></button>
    </div>`;
    document.getElementById(`colFields_${id}`).insertAdjacentHTML('beforeend', html);
}

function getCollectionJSON() {
    if (!document.getElementById('cbUseDataCollection').checked) return null;
    const collections = [];
    document.querySelectorAll('.collection-item').forEach(item => {
        const name = item.querySelector('.col-name-input').value.trim();
        const columns = [];
        item.querySelectorAll('.col-field-input').forEach(inp => {
            if (inp.value.trim()) columns.push(inp.value.trim());
        });
        if (name && columns.length > 0) {
            collections.push({ name, columns });
        }
    });
    return collections.length > 0 ? collections : null;
}

// Custom Inputs JS Logic
let customInputCount = 0;

function toggleCustomInputs() {
    const isChecked = document.getElementById('cbUseCustomInputs').checked;
    const sec = document.getElementById('customInputsSection');
    if (isChecked) {
        sec.classList.remove('d-none');
        if (document.querySelectorAll('.custom-input-item').length === 0) addCustomInput();
    } else {
        sec.classList.add('d-none');
    }
}

function addCustomInput(name = '', label = '', type = 'text') {
    customInputCount++;
    const id = customInputCount;
    
    const html = `
    <div class="custom-input-item border bg-white p-3 rounded-3 mb-3 position-relative shadow-sm" id="ci_${id}">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="removeCustomInput(${id})" aria-label="Close"></button>
        <div class="row g-2 pe-4">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-dark">Nama Variabel/Bookmark <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm ci-name-input border-secondary" placeholder="Contoh: Waktu" value="${escHtml(name)}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-dark">Label Form <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm ci-label-input border-secondary" placeholder="Contoh: Jam Acara" value="${escHtml(label)}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-dark">Tipe Input</label>
                <select class="form-select form-select-sm ci-type-input border-secondary">
                    <option value="text" ${type==='text'?'selected':''}>Teks Singkat</option>
                    <option value="textarea" ${type==='textarea'?'selected':''}>Teks Panjang</option>
                    <option value="date" ${type==='date'?'selected':''}>Tanggal</option>
                </select>
            </div>
        </div>
    </div>`;
    document.getElementById('customInputContainer').insertAdjacentHTML('beforeend', html);
}

function removeCustomInput(id) {
    document.getElementById(`ci_${id}`).remove();
    if (document.querySelectorAll('.custom-input-item').length === 0) {
        document.getElementById('cbUseCustomInputs').checked = false;
        toggleCustomInputs();
    }
}

function getCustomInputsJSON() {
    if (!document.getElementById('cbUseCustomInputs').checked) return null;
    const inputs = [];
    document.querySelectorAll('.custom-input-item').forEach(item => {
        const name = item.querySelector('.ci-name-input').value.trim();
        const label = item.querySelector('.ci-label-input').value.trim();
        const type = item.querySelector('.ci-type-input').value;
        if (name && label) {
            inputs.push({ name, label, type });
        }
    });
    return inputs.length > 0 ? inputs : null;
}


async function loadTemplates() {
    try {
        const res = await fetch(API_URL + '/api/surat/templates');
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        
        const tbody = document.querySelector('#tableTemplates tbody');
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-folder2-open me-2"></i>Belum ada template. Klik "Tambah Template" untuk mengunggah.</td></tr>';
            return;
        }
        
        let html = '';
        let currentInstansi = null;
        let globalNo = 1;
        
        data.data.forEach((t, i) => {
            const instansi = t.instansi_tujuan;
            const groupId = 'group-' + instansi.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
            
            if (instansi !== currentInstansi) {
                html += `
                <tr class="table-secondary text-dark group-header" data-group-id="${groupId}" style="cursor:pointer; transition: background 0.2s;" onmouseover="this.classList.add('bg-light')" onmouseout="this.classList.remove('bg-light')">
                    <td colspan="5" class="ps-4 py-3 fw-semibold">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-chevron-right me-3 text-muted transition-all" id="icon-${groupId}"></i>
                            <i class="bi bi-folder2-open me-2 text-primary"></i> 
                            <span>${escHtml(instansi)}</span>
                            <span class="badge bg-secondary ms-2" id="count-${groupId}">0</span>
                        </div>
                    </td>
                </tr>`;
                currentInstansi = instansi;
                globalNo = 1;
            }
            
            const peruntukanBadge = t.peruntukan === 'sekaligus' 
                ? '<span class="badge bg-info-subtle text-info">Banyak Orang</span>'
                : '<span class="badge bg-success-subtle text-success">Satu Orang</span>';
            const fileName = t.file_path.split('/').pop();
            
            const jsonAttr = t.json_data_collection ? t.json_data_collection.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';
            const ciAttr = t.json_custom_inputs ? t.json_custom_inputs.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';
            
            html += `<tr class="${groupId} d-none">
                <td class="ps-4 text-muted">${globalNo++}</td>
                <td class="fw-semibold">${escHtml(t.nama_template)}</td>
                <td>${peruntukanBadge}</td>
                <td><code class="small">${escHtml(fileName)}</code></td>
                <td class="text-end pe-4 text-nowrap">
                    <button class="btn btn-sm btn-outline-primary rounded-circle" onclick="openTemplate(this, ${t.id})" title="Buka di MS Word">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning rounded-circle ms-1" onclick="editTemplate(${t.id}, '${escHtml(t.nama_template)}', '${escHtml(t.instansi_tujuan)}', '${t.peruntukan}', this.dataset.json, this.dataset.ci)" data-json="${jsonAttr}" data-ci="${ciAttr}" title="Edit / Timpa Template">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger rounded-circle ms-1" onclick="deleteTemplate(${t.id}, '${escHtml(t.nama_template)}')" title="Hapus Template">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
        
        // Update group counts
        const badges = document.querySelectorAll('[id^="count-group-"]');
        badges.forEach(badge => {
            const groupId = badge.id.replace('count-', '');
            const count = document.querySelectorAll('.' + groupId).length;
            badge.textContent = count + ' Template';
        });
    } catch(err) {
        document.querySelector('#tableTemplates tbody').innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">${err.message}</td></tr>`;
    }
}

// inline toggleGroup function removed, replaced by event delegation

function editTemplate(id, nama, instansi, peruntukan, jsonStr, customInputsStr) {
    const form = document.getElementById('formTambahTemplate');
    form.reset();
    
    form.elements['nama_template'].value = nama;
    form.elements['peruntukan'].value = peruntukan;
    
    const sel = document.getElementById('selInstansiTujuan');
    const options = Array.from(sel.options).map(o => o.value);
    if (options.includes(instansi)) {
        sel.value = instansi;
        document.getElementById('inputInstansiBaru').classList.add('d-none');
    } else {
        sel.value = '__baru__';
        document.getElementById('inputInstansiBaru').value = instansi;
        document.getElementById('inputInstansiBaru').classList.remove('d-none');
    }
    document.getElementById('previewFolder').textContent = instansi || '...';
    
    // Check overwrite explicitly since this is Edit mode
    document.getElementById('cbOverwrite').checked = true;
    
    // Reset Data Collection UI
    document.getElementById('cbUseDataCollection').checked = false;
    document.getElementById('dataCollectionSection').classList.add('d-none');
    const container = document.getElementById('collectionContainer');
    container.innerHTML = '';
    
    if (jsonStr) {
        try {
            const dataCollection = JSON.parse(jsonStr);
            if (dataCollection && dataCollection.length > 0) {
                document.getElementById('cbUseDataCollection').checked = true;
                document.getElementById('dataCollectionSection').classList.remove('d-none');
                
                dataCollection.forEach(coll => {
                    addCollection(coll.name, coll.columns);
                });
            }
        } catch(e) {
            console.error("Error parsing json data collection", e);
        }
    }
    
    // Reset Custom Inputs UI
    document.getElementById('cbUseCustomInputs').checked = false;
    document.getElementById('customInputsSection').classList.add('d-none');
    document.getElementById('customInputContainer').innerHTML = '';
    
    if (customInputsStr) {
        try {
            const ci = JSON.parse(customInputsStr);
            if (ci && ci.length > 0) {
                document.getElementById('cbUseCustomInputs').checked = true;
                document.getElementById('customInputsSection').classList.remove('d-none');
                ci.forEach(c => addCustomInput(c.name, c.label, c.type));
            }
        } catch(e) {
            console.error("Error parsing json custom inputs", e);
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalTambahTemplate'));
    modal.show();
}

async function openTemplate(btn, id) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    try {
        const res = await fetch(`${API_URL}/api/surat/templates/${id}/open`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        // Toast message maybe?
        console.log('Opened in Word');
    } catch (e) {
        Swal.fire('Gagal Membuka Template', e.message, 'error');
    } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}

async function deleteTemplate(id, name) {
    const result = await Swal.fire({
        title: 'Hapus Template?',
        html: `Template <strong>${name}</strong> akan dihapus beserta file Word-nya.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    if (!result.isConfirmed) return;
    
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch(API_URL + '/api/surat/templates/' + id + '/delete', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrf }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        Swal.fire('Berhasil', data.message, 'success');
        loadTemplates();
    } catch(err) {
        Swal.fire('Gagal', err.message, 'error');
    }
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
</script>
