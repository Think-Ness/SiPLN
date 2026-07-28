<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;
/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $instansi
 * @var array $semuaInstansi
 * @var string $myRole
 */
$this->setTitle('Profil Instansi | Sistem Informasi');
$i = $instansi;
?>

<style>
/* Modern SaaS & Glassmorphism Aesthetics */
.glass-panel {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 1.25rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
}
.glass-panel:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.06);
}
.premium-header {
    background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.input-glass {
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
}
.input-glass:focus-within {
    background: #fff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}
.gradient-icon-bg {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: white;
}
.btn-premium {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    transition: all 0.2s ease;
}
.btn-premium:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
}
.section-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 gradient-icon-bg shadow-sm" style="width: 50px; height: 50px;">
            <i class="bi bi-building fs-4"></i>
        </div>
        <div>
            <h3 class="mb-0 fw-bolder text-dark" style="letter-spacing: -0.5px;">Profil Instansi</h3>
            <div class="text-secondary small fw-medium mt-1">Kelola identitas dan pengaturan operasional kepengurusan Anda</div>
        </div>
    </div>
</div>

<?php if ($myRole === 'super_admin' && !empty($semuaInstansi)): ?>
<div class="row justify-content-center mb-4">
    <div class="col-lg-9 col-xl-8">
        <div class="glass-panel p-3 d-flex align-items-center gap-3">
            <div class="fw-bold text-dark text-nowrap"><i class="bi bi-funnel text-primary me-2"></i>Pilih Instansi:</div>
            <select class="form-select bg-light border-0 shadow-none fw-medium" onchange="window.location.href='?kode='+this.value">
                <?php foreach ($semuaInstansi as $si): ?>
                    <option value="<?= htmlspecialchars((string)$si['kode']) ?>" <?= ($i['kode'] ?? '') === $si['kode'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$si['nama_instansi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="target_kode" value="<?= htmlspecialchars((string)($i['kode'] ?? '')) ?>">
        </div>
    </div>
</div>
<?php else: ?>
    <input type="hidden" id="target_kode" value="<?= htmlspecialchars((string)($i['kode'] ?? '')) ?>">
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        
        <!-- Informasi Dasar -->
        <div class="glass-panel overflow-hidden mb-4">
            <div class="premium-header pt-4 pb-3 px-4">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-building-gear fs-5 text-primary me-2"></i> Identitas Pokok Instansi
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="section-label">Nama Instansi</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-buildings text-primary"></i></span>
                            <input type="text" id="f_nama_instansi" class="form-control border-0 shadow-none bg-transparent py-2 fw-medium" value="<?= htmlspecialchars((string)($i['nama_instansi'] ?? '')) ?>" placeholder="Masukkan nama instansi resmi...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="section-label">No. Registrasi / ID Instansi</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-hash text-primary"></i></span>
                            <input type="number" id="f_no_instansi" class="form-control border-0 shadow-none bg-transparent py-2" value="<?= htmlspecialchars((string)($i['no_Instansi'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="section-label">Email Resmi</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-envelope-at text-primary"></i></span>
                            <input type="email" id="f_email" class="form-control border-0 shadow-none bg-transparent py-2" value="<?= htmlspecialchars((string)($i['email'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="section-label">Pondok Asrama Utama</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-house-door text-primary"></i></span>
                            <input type="text" id="f_pondok" class="form-control border-0 shadow-none bg-transparent py-2" value="<?= htmlspecialchars((string)($i['pondok'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="section-label">Path Folder Sistem</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-folder-symlink text-primary"></i></span>
                            <input type="text" id="f_path_folder" class="form-control border-0 shadow-none bg-transparent py-2" value="<?= htmlspecialchars((string)($i['path_folder'] ?? '')) ?>" placeholder="Ketik atau paste absolut path (misal: D:\uploads\G1)">
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size: 0.7rem;"><i class="bi bi-info-circle"></i> Isikan *absolute path* ke folder di server untuk instansi ini.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengaturan Default Data -->
        <div class="glass-panel overflow-hidden mb-4">
            <div class="premium-header pt-4 pb-3 px-4">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-person-gear fs-5 text-success me-2"></i> Default Input Santri Baru
                </h6>
                <div class="text-muted small mt-1 ms-4 ps-1">Aturan ini akan otomatis mengisi form setiap kali Anda menambah data santri baru.</div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="section-label text-success">Kepengurusan (Wewenang)</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-diagram-3 text-success"></i></span>
                            <input type="text" id="f_def_kepengurusan" class="form-control border-0 shadow-none bg-transparent py-2 fw-medium text-success" value="<?= htmlspecialchars((string)($i['def_kepengurusan'] ?? '')) ?>" placeholder="Misal: Ponorogo">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="section-label text-success">Pondok (Asrama)</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-house text-success"></i></span>
                            <input type="text" id="f_def_pondok" class="form-control border-0 shadow-none bg-transparent py-2 fw-medium text-success" value="<?= htmlspecialchars((string)($i['def_pondok'] ?? '')) ?>" list="pondokList" placeholder="Ketik atau pilih...">
                            <datalist id="pondokList">
                                <option value="G1">
                                <option value="G2">
                                <option value="G3">
                                <option value="G4">
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="section-label">Jenis Kelamin Default</label>
                        <div class="input-group input-glass rounded-3 overflow-hidden">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-gender-ambiguous text-secondary"></i></span>
                            <select id="f_def_jenis_kelamin" class="form-select border-0 shadow-none bg-transparent py-2">
                                <option value="" <?= empty($i['def_jenis_kelamin']) ? 'selected' : '' ?>>-- Bebas (Manual) --</option>
                                <option value="Laki-laki" <?= ($i['def_jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= ($i['def_jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kop Surat -->
        <div class="glass-panel overflow-hidden mb-4">
            <div class="premium-header pt-4 pb-3 px-4">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-file-earmark-image fs-5 text-warning me-2"></i> Kop Surat Resmi
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <?php if (!empty($i['kop_surat'])): ?>
                            <div class="mb-4 p-3 border rounded-3 bg-white text-center shadow-sm position-relative overflow-hidden">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #ffffff 10px, #ffffff 20px); z-index: 0; opacity: 0.5;"></div>
                                <img src="<?= API_URL ?>/profil-instansi/kop-surat/view?kode=<?= urlencode((string)($i['kode'] ?? '')) ?>&v=<?= time() ?>" alt="Kop Surat" class="position-relative shadow-sm border" style="max-height: 120px; max-width: 100%; z-index: 1;">
                                <div class="small fw-bold text-success mt-3 position-relative" style="z-index: 1;"><i class="bi bi-check-circle-fill me-1"></i> Kop surat saat ini aktif terpasang</div>
                            </div>
                        <?php endif; ?>
                        
                        <label class="section-label">Unggah Kop Surat Baru</label>
                        <div id="dropZoneKop" class="border rounded-3 mb-2 flex-grow-1 d-flex flex-column align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative" style="min-height: 120px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s; cursor: pointer;" ondragover="handleDragOver(event, 'dropZoneKop', 'dragOverlayKop')" ondragleave="handleDragLeave(event, 'dropZoneKop', 'dragOverlayKop')" ondrop="handleDrop(event, 'dropZoneKop', 'dragOverlayKop', 'f_kop_surat')" onclick="document.getElementById('f_kop_surat').click()">
                            <i id="kop_icon" class="bi bi-cloud-arrow-up text-secondary" style="font-size: 2.5rem;"></i>
                            <span id="kop_text" class="text-muted small mt-2">Klik atau seret file ke sini</span>
                            <div id="dragOverlayKop" class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                                <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan File</span>
                            </div>
                        </div>
                        <input type="file" id="f_kop_surat" class="d-none" accept=".png,.jpg,.jpeg" onchange="updateFileName('f_kop_surat', 'kop_text', 'kop_icon')">
                        <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Format: PNG/JPG. Kosongkan jika tidak ingin mengubah kop surat saat ini.</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-4 text-end">
                <button class="btn btn-premium rounded-pill px-5 py-2 fw-bold text-white" id="btnSimpan" onclick="simpanInstansi()">
                    <i class="bi bi-floppy me-2"></i> Simpan Konfigurasi
                </button>
            </div>
        </div>

    </div>
</div>

<script>
function handleDragOver(e, zoneId, overlayId) {
    e.preventDefault();
    document.getElementById(zoneId).classList.replace('border-light', 'border-primary');
    document.getElementById(overlayId).classList.remove('d-none');
}

function handleDragLeave(e, zoneId, overlayId) {
    e.preventDefault();
    document.getElementById(zoneId).classList.replace('border-primary', 'border-light');
    document.getElementById(overlayId).classList.add('d-none');
}

function handleDrop(e, zoneId, overlayId, inputId) {
    e.preventDefault();
    document.getElementById(zoneId).classList.replace('border-primary', 'border-light');
    document.getElementById(overlayId).classList.add('d-none');
    
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const fileInput = document.getElementById(inputId);
        fileInput.files = e.dataTransfer.files;
        updateFileName(inputId, 'kop_text', 'kop_icon');
    }
}

function updateFileName(inputId, textId, iconId) {
    const input = document.getElementById(inputId);
    const text = document.getElementById(textId);
    const icon = document.getElementById(iconId);
    
    if (input.files && input.files.length > 0) {
        text.textContent = input.files[0].name;
        text.classList.remove('text-muted');
        text.classList.add('text-primary', 'fw-bold');
        icon.className = 'bi bi-file-earmark-check text-primary';
        icon.style.fontSize = '2.5rem';
    } else {
        text.textContent = 'Klik atau seret file ke sini';
        text.classList.add('text-muted');
        text.classList.remove('text-primary', 'fw-bold');
        icon.className = 'bi bi-cloud-arrow-up text-secondary';
        icon.style.fontSize = '2.5rem';
    }
}

function simpanInstansi() {
    const btn = document.getElementById('btnSimpan');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const formData = new FormData();
    const targetKode = document.getElementById('target_kode');
    if (targetKode && targetKode.value) {
        formData.append('target_kode', targetKode.value);
    }
    formData.append('nama_instansi', document.getElementById('f_nama_instansi').value);
    formData.append('no_Instansi', document.getElementById('f_no_instansi').value);
    formData.append('email', document.getElementById('f_email').value);
    formData.append('pondok', document.getElementById('f_pondok').value);
    formData.append('path_folder', document.getElementById('f_path_folder').value);
    formData.append('def_kepengurusan', document.getElementById('f_def_kepengurusan').value);
    formData.append('def_pondok', document.getElementById('f_def_pondok').value);
    formData.append('def_jenis_kelamin', document.getElementById('f_def_jenis_kelamin').value);
    
    const kopFile = document.getElementById('f_kop_surat').files[0];
    if (kopFile) {
        formData.append('kop_surat', kopFile);
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/instansi/update', { 
        method: 'POST', 
        body: formData,
        headers: {
            'X-CSRF-Token': csrf
        } 
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (d.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: d.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: d.message
            });
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Jaringan',
            text: 'Terjadi kesalahan saat menghubungi server.'
        });
    });
}
</script>
