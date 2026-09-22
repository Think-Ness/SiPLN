<?php
/**
 * @var \Yiisoft\View\WebView $this
 * @var array $folderList
 * @var string $role
 */
$this->setTitle('Kelola Template Surat | Sistem Informasi');
?>
<style>
/* Surat Templates Custom Styling & Mobile Responsiveness */
.guide-card {
    border: 1px solid rgba(13, 202, 240, 0.25);
    background: linear-gradient(180deg, #f0faff 0%, #ffffff 100%);
}
.var-tag {
    cursor: pointer;
    background: #e7f1ff;
    color: #0d6efd;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: var(--bs-font-monospace);
    font-size: 0.82em;
    font-weight: 600;
    transition: all 0.15s ease;
    display: inline-block;
    border: 1px solid rgba(13, 110, 253, 0.15);
}
.var-tag:hover {
    background: #0d6efd;
    color: #ffffff;
    transform: translateY(-1px);
}
.group-header {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease;
}
.group-header:hover {
    background-color: #eef2f6 !important;
}
.group-header .chevron-icon {
    transition: transform 0.25s ease;
}
.btn-action-sm {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}
.path-folder-badge {
    max-width: 260px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
@media (max-width: 767.98px) {
    .header-action-container {
        flex-direction: column;
        align-items: stretch !important;
        gap: 12px;
    }
    .header-action-container .btn-primary {
        width: 100%;
        justify-content: center;
    }
    .table thead {
        font-size: 0.78rem;
    }
    .table td, .table th {
        padding: 0.6rem 0.4rem;
    }
    .path-folder-badge {
        max-width: 120px;
    }
    .template-action-group {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }
    .modal-dialog {
        margin: 0.5rem;
    }
    .collection-item, .custom-input-item {
        padding: 0.75rem !important;
    }
}
@media (max-width: 575.98px) {
    .path-folder-badge {
        max-width: 90px;
    }
}
</style>

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mb-md-4 header-action-container">
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing:-.5px;">
                <i class="bi bi-file-earmark-word text-primary me-2"></i>Kelola Template Surat
            </h4>
            <div class="text-muted small fw-medium mt-1">Unggah dan kelola template Microsoft Word (.docx) per instansi tujuan</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm rounded-3 py-2 px-3" data-bs-toggle="modal" data-bs-target="#modalTambahTemplate" onclick="openAddTemplateModal()">
                <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                <span class="fw-semibold">Tambah Template</span>
            </button>
        </div>
    </div>

    <!-- Panduan Bookmark & Tag Variabel (Collapsible Card) -->
    <div class="card guide-card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-transparent border-0 p-3 p-md-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;">
                    <i class="bi bi-tags-fill fs-6"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Panduan Variabel Otomatis (Tag & Bookmark)</h6>
                    <span class="text-muted small">Klik tag variabel di bawah untuk menyalin secara instan</span>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#panduanContent" aria-expanded="false" id="btnTogglePanduan">
                <i class="bi bi-chevron-down me-1" id="iconTogglePanduan"></i>
                <span id="textTogglePanduan">Buka Panduan</span>
            </button>
        </div>

        <div class="collapse" id="panduanContent">
            <div class="card-body p-3 p-md-4 pt-3">
                <div class="alert alert-light border border-info-subtle rounded-3 mb-3 p-3 small text-dark">
                    <p class="mb-2 fw-semibold"><i class="bi bi-info-circle text-info me-1"></i> Sistem mendukung 2 cara penempatan data otomatis di Microsoft Word:</p>
                    <ul class="mb-0 ps-3">
                        <li class="mb-1"><strong>Metode Tag (Disarankan):</strong> Ketik langsung di teks Word dengan format <code>${NamaVariabel}</code> (Contoh: <code>${Tanggal_Buat}</code>).</li>
                        <li><strong>Metode Bookmark:</strong> Gunakan fitur Bookmark (Insert → Bookmark) dengan nama variabel yang sama.</li>
                    </ul>
                </div>

                <div class="row g-3 small text-dark">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-white rounded-3 border border-light-subtle shadow-sm h-100">
                            <div class="fw-bold text-primary mb-2 pb-1 border-bottom d-flex align-items-center gap-1">
                                <i class="bi bi-card-heading"></i> Header Surat
                            </div>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                                <li><span class="var-tag" onclick="copyTag('${NO}')">${NO}</span> <span class="text-muted">: Nomor Surat</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Bln_Romawi}')">${Bln_Romawi}</span> <span class="text-muted">: Bulan Romawi</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Tahun_Buat}')">${Tahun_Buat}</span> <span class="text-muted">: Tahun Surat</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Tanggal_Buat}')">${Tanggal_Buat}</span> <span class="text-muted">: Tgl Surat Indo</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-white rounded-3 border border-light-subtle shadow-sm h-100">
                            <div class="fw-bold text-primary mb-2 pb-1 border-bottom d-flex align-items-center gap-1">
                                <i class="bi bi-file-text"></i> Isi Surat
                            </div>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                                <li><span class="var-tag" onclick="copyTag('${Hal}')">${Hal}</span> <span class="text-muted">: Perihal Surat</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Isi}')">${Isi}</span> <span class="text-muted">: Konten / Uraian</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Kepada}')">${Kepada}</span> <span class="text-muted">: Tujuan Surat</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Tempat}')">${Tempat}</span> <span class="text-muted">: Tempat Tujuan</span></li>
                                <li><span class="var-tag" onclick="copyTag('${JML_LAMPIRAN}')">${JML_LAMPIRAN}</span> <span class="text-muted">: Jml Lampiran</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-white rounded-3 border border-light-subtle shadow-sm h-100">
                            <div class="fw-bold text-primary mb-2 pb-1 border-bottom d-flex align-items-center gap-1">
                                <i class="bi bi-person-lines-fill"></i> Data Santri (Perseorangan)
                            </div>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                                <li><span class="var-tag" onclick="copyTag('${Nama_Santri}')">${Nama_Santri}</span> <span class="text-muted">: Nama Lengkap</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Tempat_Lahir}')">${Tempat_Lahir}</span> <span class="text-muted">: Tempat Lahir</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Tgl_Lahir}')">${Tgl_Lahir}</span> <span class="text-muted">: Tanggal Lahir</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Kewarganegaraan}')">${Kewarganegaraan}</span> <span class="text-muted">: Kewarganegaraan</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Negara_Asal}')">${Negara_Asal}</span> <span class="text-muted">: Negara Asal</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Alamat_Santri}')">${Alamat_Santri}</span> <span class="text-muted">: Alamat</span></li>
                                <li><span class="var-tag" onclick="copyTag('${No_Paspor}')">${No_Paspor}</span> <span class="text-muted">: Nomor Paspor</span></li>
                                <li><span class="var-tag" onclick="copyTag('${Tgl_Berlaku}')">${Tgl_Berlaku}</span> <span class="text-muted">: Exp. Paspor</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <hr class="border-info opacity-25 my-3">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white rounded-3 border border-light-subtle h-100">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-table text-info me-1"></i> Data Collection (Tabel Dinamis)</h6>
                            <p class="mb-2 text-dark small">Gunakan jika surat membutuhkan input tabel berulang (misal: Daftar Undangan, Daftar Rombongan):</p>
                            <ol class="small text-muted mb-0 ps-3">
                                <li class="mb-1">Di Word, buat tabel. Pada baris data, isi sel pertama dengan tag: <code>${NamaCollection}</code>.</li>
                                <li class="mb-1">Kolom lainnya diisi dengan tag kolom (misal: <code>${Nama_Lengkap}</code>, <code>${Jabatan}</code>).</li>
                                <li class="mb-1">Di sistem, aktifkan <strong>Menggunakan Data Collection</strong> saat upload.</li>
                                <li>Saat generate surat, form tabel dinamis akan otomatis muncul!</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white rounded-3 border border-light-subtle h-100">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-input-cursor-text text-info me-1"></i> Input Kustom (Variabel Tambahan)</h6>
                            <p class="mb-2 text-dark small">Gunakan jika surat membutuhkan isian spesifik tambahan (misal: Waktu, Lokasi Acara):</p>
                            <ol class="small text-muted mb-0 ps-3">
                                <li class="mb-1">Di Word, ketik tag yang Anda inginkan (misal: <code>${Waktu}</code>, <code>${Lokasi}</code>).</li>
                                <li class="mb-1">Di sistem, aktifkan <strong>Membutuhkan Input Kustom</strong> saat upload.</li>
                                <li class="mb-1">Tambahkan input dengan nama variabel yang sama persis.</li>
                                <li>Saat generate surat, form input sesuai tipe (teks/panjang/tanggal) akan otomatis muncul.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="mt-3 small text-muted border-top border-info-subtle pt-2">
                    <em>* Catatan: Untuk template <strong>Banyak Orang (Sekaligus)</strong> tipe klasik, data santri otomatis dilampirkan sebagai halaman baru di akhir dokumen.</em>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableTemplates">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 ps-md-4 fw-semibold text-secondary" style="width: 45px">No</th>
                            <th class="fw-semibold text-secondary">Nama Template</th>
                            <th class="fw-semibold text-secondary">Peruntukan</th>
                            <th class="fw-semibold text-secondary">File</th>
                            <th class="fw-semibold text-secondary">Path Folder</th>
                            <th class="fw-semibold text-secondary text-end pe-3 pe-md-4" style="width: 140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data template...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Template -->
<div class="modal fade" id="modalTambahTemplate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="formTambahTemplate">
                <input type="hidden" name="template_id" id="hiddenTemplateId" value="">
                <div class="modal-header bg-light border-bottom-0 p-3 p-md-4 pb-2">
                    <h5 class="modal-title fw-bold" id="modalTitle">Upload Template Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4 pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="nama_template" id="inpNamaTemplate" placeholder="Misal: Surat Perizinan" required>
                        <div class="form-text small mt-1">Akan menjadi nama file: <code>Surat_Perizinan_Satu_Orang.docx</code></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Instansi Tujuan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-select rounded-3" name="instansi_tujuan_select" id="selInstansiTujuan" onchange="onInstansiSelectChange()">
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
                        <select class="form-select rounded-3" name="peruntukan" id="selPeruntukan" required>
                            <option value="perseorangan">Satu Orang (Perseorangan)</option>
                            <option value="sekaligus">Banyak Orang (Sekaligus / Tabel)</option>
                        </select>
                        <div class="form-text small mt-1">Pilih "Banyak Orang" jika isi suratnya berupa tabel daftar nama.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">File Template Word (.docx) <span class="text-danger" id="fileRequiredStar">*</span></label>
                        <input type="file" class="form-control rounded-3" name="file" id="fileTemplate" accept=".docx" required>
                        <div class="form-text small mt-1 text-primary" id="fileHelp"><i class="bi bi-info-circle"></i> Hanya menerima file ekstensi .docx.</div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="cbOverwrite">
                            <label class="form-check-label small" for="cbOverwrite">Timpa file jika nama template dan peruntukan sama (Update/Edit)</label>
                        </div>
                    </div>

                    <!-- Dynamic Data Collection Section -->
                    <div class="mb-3 border rounded-3 p-3 bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="cbUseDataCollection" onchange="toggleDataCollection()">
                            <label class="form-check-label fw-semibold text-secondary" for="cbUseDataCollection">Menggunakan Data Collection (Tabel Dinamis)</label>
                        </div>
                        <div class="form-text small mb-2">Aktifkan jika template membutuhkan input data berupa tabel/daftar (contoh: Daftar Undangan, Daftar Barang).</div>
                        
                        <div id="dataCollectionSection" class="d-none mt-3">
                            <div id="collectionContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1 rounded-pill" onclick="addCollection()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Collection
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Custom Inputs Section -->
                    <div class="mb-3 border rounded-3 p-3 bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="cbUseCustomInputs" onchange="toggleCustomInputs()">
                            <label class="form-check-label fw-semibold text-secondary" for="cbUseCustomInputs">Membutuhkan Input Kustom (Variabel Tambahan)</label>
                        </div>
                        <div class="form-text small mb-2">Aktifkan jika template membutuhkan input teks khusus selain data standar (contoh: Waktu, Tempat Acara).</div>
                        
                        <div id="customInputsSection" class="d-none mt-3">
                            <div id="customInputContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1 rounded-pill" onclick="addCustomInput()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Input Kustom
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 p-3">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnUpload">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="uploadSpinner"></span>
                        <span id="btnUploadText">Upload & Simpan</span>
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

    // Setup guide accordion button listener
    const panduanCollapse = document.getElementById('panduanContent');
    const textToggle = document.getElementById('textTogglePanduan');
    const iconToggle = document.getElementById('iconTogglePanduan');
    if (panduanCollapse) {
        panduanCollapse.addEventListener('show.bs.collapse', () => {
            textToggle.textContent = 'Tutup Panduan';
            iconToggle.classList.remove('bi-chevron-down');
            iconToggle.classList.add('bi-chevron-up');
        });
        panduanCollapse.addEventListener('hide.bs.collapse', () => {
            textToggle.textContent = 'Buka Panduan';
            iconToggle.classList.remove('bi-chevron-up');
            iconToggle.classList.add('bi-chevron-down');
        });
    }

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
            } else {
                formData.set('json_data_collection', '');
            }
            
            const customInputs = getCustomInputsJSON();
            if (customInputs) {
                formData.set('json_custom_inputs', JSON.stringify(customInputs));
            } else {
                formData.set('json_custom_inputs', '');
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
            const modalEl = document.getElementById('modalTambahTemplate');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            loadTemplates();
        } catch(err) {
            Swal.fire('Gagal', err.message, 'error');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
    
    // Event delegation for toggling folder groups
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

function openAddTemplateModal() {
    const form = document.getElementById('formTambahTemplate');
    form.reset();
    document.getElementById('hiddenTemplateId').value = '';
    document.getElementById('modalTitle').textContent = 'Upload Template Baru';
    document.getElementById('btnUploadText').textContent = 'Upload & Simpan';
    document.getElementById('fileHelp').innerHTML = '<i class="bi bi-info-circle"></i> Hanya menerima file ekstensi .docx.';
    document.getElementById('fileTemplate').setAttribute('required', 'required');
    document.getElementById('fileRequiredStar').classList.remove('d-none');
    document.getElementById('cbOverwrite').checked = false;
    document.getElementById('inputInstansiBaru').classList.add('d-none');
    document.getElementById('previewFolder').textContent = '...';

    // Clear dynamic sections
    document.getElementById('cbUseDataCollection').checked = false;
    document.getElementById('dataCollectionSection').classList.add('d-none');
    document.getElementById('collectionContainer').innerHTML = '';

    document.getElementById('cbUseCustomInputs').checked = false;
    document.getElementById('customInputsSection').classList.add('d-none');
    document.getElementById('customInputContainer').innerHTML = '';
}

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
            <div class="d-flex gap-2 column-field-row mb-1">
                <input type="text" class="form-control form-control-sm col-field-input rounded-2" placeholder="Nama Kolom (misal: Nama Lengkap)" value="${escHtml(colName)}" required>
                <button type="button" class="btn btn-sm btn-outline-danger px-2 rounded-2" onclick="if(this.parentElement.parentElement.children.length>1) this.parentElement.remove()" title="Hapus Kolom"><i class="bi bi-trash"></i></button>
            </div>`;
        });
    } else {
        colFieldsHtml = `
        <div class="d-flex gap-2 column-field-row mb-1">
            <input type="text" class="form-control form-control-sm col-field-input rounded-2" placeholder="Nama Kolom (misal: Nama Lengkap)" required>
            <button type="button" class="btn btn-sm btn-outline-danger px-2 rounded-2" onclick="if(this.parentElement.parentElement.children.length>1) this.parentElement.remove()" title="Hapus Kolom"><i class="bi bi-trash"></i></button>
        </div>`;
    }

    const html = `
    <div class="collection-item border bg-white p-3 rounded-3 mb-3 position-relative shadow-sm" id="col_${id}">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="removeCollection(${id})" aria-label="Close"></button>
        <div class="mb-2 pe-4">
            <label class="form-label small fw-bold text-dark mb-1">Nama Collection (Macro/Tag) <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm col-name-input border-secondary-subtle rounded-2" placeholder="Contoh: Daftar_Undangan" value="${escHtml(name)}" required>
            <div class="form-text" style="font-size:0.7rem">Di Word, tabel harus memiliki tag <code>\${NamaCollection}</code> di kolom pertama.</div>
        </div>
        <div>
            <label class="form-label small fw-bold mb-1 text-dark">Daftar Kolom</label>
            <div id="colFields_${id}" class="d-flex flex-column gap-1 mb-2">
                ${colFieldsHtml}
            </div>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" style="font-size:0.75rem" onclick="addColumnField(${id})"><i class="bi bi-plus me-1"></i>Tambah Kolom</button>
        </div>
    </div>`;
    document.getElementById('collectionContainer').insertAdjacentHTML('beforeend', html);
}

function removeCollection(id) {
    const el = document.getElementById(`col_${id}`);
    if (el) el.remove();
    if (document.querySelectorAll('.collection-item').length === 0) {
        document.getElementById('cbUseDataCollection').checked = false;
        toggleDataCollection();
    }
}

function addColumnField(id) {
    const html = `
    <div class="d-flex gap-2 column-field-row mb-1">
        <input type="text" class="form-control form-control-sm col-field-input rounded-2" placeholder="Nama Kolom" required>
        <button type="button" class="btn btn-sm btn-outline-danger px-2 rounded-2" onclick="this.parentElement.remove()" title="Hapus Kolom"><i class="bi bi-trash"></i></button>
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
            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-dark mb-1">Nama Variabel/Tag <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm ci-name-input border-secondary-subtle rounded-2" placeholder="Contoh: Waktu" value="${escHtml(name)}" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">Label Form <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm ci-label-input border-secondary-subtle rounded-2" placeholder="Contoh: Jam Acara" value="${escHtml(label)}" required>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">Tipe Input</label>
                <select class="form-select form-select-sm ci-type-input border-secondary-subtle rounded-2">
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
    const el = document.getElementById(`ci_${id}`);
    if (el) el.remove();
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
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-folder2-open me-2 fs-5 d-block mb-1"></i>Belum ada template. Klik "Tambah Template" untuk mengunggah.</td></tr>';
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
                <tr class="table-secondary text-dark group-header" data-group-id="${groupId}">
                    <td colspan="6" class="ps-3 ps-md-4 py-2 py-md-3 fw-semibold">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-chevron-right me-2 text-muted fs-6" id="icon-${groupId}"></i>
                                <i class="bi bi-folder-fill me-2 text-primary"></i> 
                                <span class="fw-bold">${escHtml(instansi)}</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill me-2" id="count-${groupId}">0 Template</span>
                        </div>
                    </td>
                </tr>`;
                currentInstansi = instansi;
                globalNo = 1;
            }
            
            const peruntukanBadge = t.peruntukan === 'sekaligus' 
                ? '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Banyak Orang</span>'
                : '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Satu Orang</span>';
            const fileName = t.file_path.split('/').pop();
            const folderPath = t.file_path.includes('/') ? t.file_path.substring(0, t.file_path.lastIndexOf('/')) : '';
            
            const jsonAttr = t.json_data_collection ? t.json_data_collection.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';
            const ciAttr = t.json_custom_inputs ? t.json_custom_inputs.replace(/"/g, '&quot;').replace(/'/g, '&#39;') : '';
            
            html += `<tr class="${groupId} d-none">
                <td class="ps-3 ps-md-4 text-muted small">${globalNo++}</td>
                <td class="fw-semibold text-dark">${escHtml(t.nama_template)}</td>
                <td>${peruntukanBadge}</td>
                <td><code class="small text-primary fw-medium font-monospace">${escHtml(fileName)}</code></td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-light text-secondary border font-monospace path-folder-badge text-start" title="${escHtml(t.file_path)}">
                            <i class="bi bi-folder2 text-primary me-1"></i>${escHtml(folderPath)}/
                        </span>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="copyPathText('${escHtml(t.file_path)}')" title="Salin Path File">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </td>
                <td class="text-end pe-3 pe-md-4 text-nowrap">
                    <div class="template-action-group">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-action-sm rounded-circle" onclick="openTemplateDirect(${t.id})" title="Buka & Edit Langsung di Microsoft Word">
                            <i class="bi bi-file-earmark-word"></i>
                        </button>
                        <a href="${API_URL}/api/surat/templates/${t.id}/download" class="btn btn-sm btn-outline-secondary btn-action-sm rounded-circle" title="Unduh File Template (.docx)" download>
                            <i class="bi bi-download"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-warning btn-action-sm rounded-circle" onclick="editTemplate(${t.id}, '${escHtml(t.nama_template)}', '${escHtml(t.instansi_tujuan)}', '${t.peruntukan}', this.dataset.json, this.dataset.ci)" data-json="${jsonAttr}" data-ci="${ciAttr}" title="Edit / Timpa Template">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action-sm rounded-circle" onclick="deleteTemplate(${t.id}, '${escHtml(t.nama_template)}')" title="Hapus Template">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
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
        document.querySelector('#tableTemplates tbody').innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${err.message}</td></tr>`;
    }
}

function editTemplate(id, nama, instansi, peruntukan, jsonStr, customInputsStr) {
    const form = document.getElementById('formTambahTemplate');
    form.reset();
    document.getElementById('hiddenTemplateId').value = id;
    document.getElementById('inpNamaTemplate').value = nama;
    
    // Select instansi
    const selInstansi = document.getElementById('selInstansiTujuan');
    const inputInstansiBaru = document.getElementById('inputInstansiBaru');
    let matched = false;
    for (let i = 0; i < selInstansi.options.length; i++) {
        if (selInstansi.options[i].value === instansi) {
            selInstansi.selectedIndex = i;
            matched = true;
            break;
        }
    }
    if (!matched) {
        selInstansi.value = '__baru__';
        inputInstansiBaru.classList.remove('d-none');
        inputInstansiBaru.value = instansi;
    } else {
        inputInstansiBaru.classList.add('d-none');
    }
    document.getElementById('previewFolder').textContent = instansi ? instansi.replace(/[^a-zA-Z0-9_\-]/g, '_') : '...';
    
    document.getElementById('selPeruntukan').value = peruntukan;
    document.getElementById('modalTitle').textContent = 'Edit / Timpa Template Surat';
    document.getElementById('btnUploadText').textContent = 'Simpan Perubahan';
    document.getElementById('fileHelp').innerHTML = '<i class="bi bi-info-circle text-warning"></i> Unggah file baru untuk mengganti template .docx saat ini.';
    document.getElementById('fileTemplate').removeAttribute('required');
    document.getElementById('fileRequiredStar').classList.add('d-none');
    document.getElementById('cbOverwrite').checked = true;
    
    // Clear and populate Data Collection
    document.getElementById('collectionContainer').innerHTML = '';
    if (jsonStr) {
        try {
            const list = JSON.parse(jsonStr);
            if (Array.isArray(list) && list.length > 0) {
                document.getElementById('cbUseDataCollection').checked = true;
                document.getElementById('dataCollectionSection').classList.remove('d-none');
                list.forEach(item => {
                    addCollection(item.name || '', item.columns || []);
                });
            } else {
                document.getElementById('cbUseDataCollection').checked = false;
                document.getElementById('dataCollectionSection').classList.add('d-none');
            }
        } catch(e) {
            console.error("Error parsing json collection", e);
        }
    } else {
        document.getElementById('cbUseDataCollection').checked = false;
        document.getElementById('dataCollectionSection').classList.add('d-none');
    }

    // Clear and populate Custom Inputs
    document.getElementById('customInputContainer').innerHTML = '';
    if (customInputsStr) {
        try {
            const customList = JSON.parse(customInputsStr);
            if (Array.isArray(customList) && customList.length > 0) {
                document.getElementById('cbUseCustomInputs').checked = true;
                document.getElementById('customInputsSection').classList.remove('d-none');
                customList.forEach(item => {
                    addCustomInput(item.name || '', item.label || '', item.type || 'text');
                });
            } else {
                document.getElementById('cbUseCustomInputs').checked = false;
                document.getElementById('customInputsSection').classList.add('d-none');
            }
        } catch(e) {
            console.error("Error parsing json custom inputs", e);
        }
    } else {
        document.getElementById('cbUseCustomInputs').checked = false;
        document.getElementById('customInputsSection').classList.add('d-none');
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalTambahTemplate'));
    modal.show();
}

async function openTemplateDirect(id) {
    try {
        const res = await fetch(`${API_URL}/api/surat/templates/${id}/open`);
        const data = await res.json();
        if (data.success && data.sipln_url) {
            window.location.href = data.sipln_url;
        } else {
            window.location.href = `${API_URL}/api/surat/templates/${id}/download`;
        }
    } catch (e) {
        window.location.href = `${API_URL}/api/surat/templates/${id}/download`;
    }
}

function copyTag(tagText) {
    copyPathText(tagText, `Variabel ${tagText} disalin!`);
}

function copyPathText(text, msg = 'Path berhasil disalin ke clipboard!') {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: msg,
                showConfirmButton: false,
                timer: 1500
            });
        });
    } else {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: msg,
            showConfirmButton: false,
            timer: 1500
        });
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
    if (!s) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
</script>
