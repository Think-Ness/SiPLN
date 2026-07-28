<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;
use Yiisoft\Router\UrlGeneratorInterface;
/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $berkas
 * @var int $total
 * @var string $search
 * @var string $myRole
 * @var array $semuaInstansi
 * @var UrlGeneratorInterface $urlGenerator
 */
$this->setTitle('Pemberkasan | Sistem Informasi');
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #ffc10715;">
            <i class="bi bi-folder2-open fs-5 text-warning"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Pemberkasan Dokumen</h4>
            <div class="text-muted small fw-medium mt-1">Total: <strong class="text-dark"><?= $total ?></strong> berkas tersimpan</div>
        </div>
    </div>
    <button class="btn btn-warning text-white rounded-pill px-4 fw-medium shadow-sm" onclick="new bootstrap.Modal(document.getElementById('uploadModal')).show()">
        <i class="bi bi-cloud-upload me-2"></i>Upload Berkas
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-md-5">
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="q" class="form-control border-0 shadow-none bg-white py-2" placeholder="Cari nama berkas..." value="<?= htmlspecialchars($search) ?>" style="font-size: .85rem;">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium w-100 shadow-sm">
                    Cari
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($berkas)): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="mb-3">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa;">
                    <i class="bi bi-folder-x text-muted" style="font-size: 2.5rem;"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark">Belum Ada Berkas</h5>
            <p class="text-muted small mb-0" style="max-width: 300px; margin: 0 auto;">Belum ada berkas yang diunggah. Silakan klik tombol Upload Berkas untuk menambahkan dokumen baru.</p>
        </div>
    </div>
    <?php else: foreach ($berkas as $b):
        $ext = strtolower(pathinfo($b['path_file'] ?? '', PATHINFO_EXTENSION));
        $icon = match($ext) { 'pdf' => 'bi-file-pdf text-danger', 'jpg','jpeg','png','gif' => 'bi-file-image text-primary', 'doc','docx' => 'bi-file-word text-info', default => 'bi-file-earmark text-secondary' };
    ?>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='none';">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background: #f8f9fa;">
                    <i class="bi <?= $icon ?>" style="font-size:2rem;"></i>
                </div>
                <h6 class="mb-1 fw-bold text-dark text-truncate" title="<?= htmlspecialchars($b['nama_berkas']) ?>"><?= htmlspecialchars($b['nama_berkas']) ?></h6>
                <div class="mt-2">
                    <?php if (($b['is_public'] ?? 0) == 1): ?>
                        <span class="badge bg-success rounded-pill fw-normal px-2"><i class="bi bi-globe me-1"></i>Publik (Semua Instansi)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill fw-normal px-2 text-truncate d-inline-block" style="max-width: 100%;" title="<?= htmlspecialchars((string)($b['nama_instansi'] ?? $b['kode'])) ?>">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars((string)($b['nama_instansi'] ?? $b['kode'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer d-flex gap-2 justify-content-center bg-white border-top-0 pb-4 pt-0">
                <?php if ($b['path_file']): ?>
                <a href="<?= API_URL ?>/berkas/<?= $b['id'] ?>/view" target="_blank" class="btn btn-sm btn-light text-primary rounded-pill px-3 shadow-sm border">
                    <i class="bi bi-eye"></i> Lihat
                </a>
                <a href="<?= API_URL ?>/berkas/<?= $b['id'] ?>/view?dl=1" class="btn btn-sm btn-light text-success rounded-circle shadow-sm border" style="width:32px;height:32px;padding:0;line-height:30px;" title="Download">
                    <i class="bi bi-download"></i>
                </a>
                <?php endif; ?>
                
                <?php if ($myRole === 'super_admin' || $b['kode'] === ($_SESSION['instansi_id'] ?? null)): ?>
                <button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm border" style="width:32px;height:32px;padding:0;line-height:30px;" onclick="openEditModal(<?= $b['id'] ?>, '<?= addslashes($b['nama_berkas']) ?>', <?= $b['is_public'] ?? 0 ?>, '<?= htmlspecialchars((string)$b['kode']) ?>')" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-light text-danger rounded-circle shadow-sm border" style="width:32px;height:32px;padding:0;line-height:30px;" onclick="hapusBerkas(<?= $b['id'] ?>, '<?= addslashes($b['nama_berkas']) ?>')" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cloud-upload text-warning me-2"></i>Upload Berkas Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-4">
                <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px;">NAMA BERKAS <span class="text-danger">*</span></label>
                <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-tag text-secondary"></i></span>
                    <input type="text" id="up_nama" class="form-control border-0 shadow-none bg-white py-2" placeholder="cth: Fotokopi Paspor">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px;">FILE DOKUMEN <span class="text-danger">*</span></label>
                <div id="dropZoneUp" class="border rounded-3 mb-2 flex-grow-1 d-flex flex-column align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative" style="min-height: 120px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s; cursor: pointer;" ondragover="handleDragOver(event, 'dropZoneUp', 'dragOverlayUp')" ondragleave="handleDragLeave(event, 'dropZoneUp', 'dragOverlayUp')" ondrop="handleDrop(event, 'dropZoneUp', 'dragOverlayUp', 'up_file')" onclick="document.getElementById('up_file').click()">
                    <i id="up_icon" class="bi bi-cloud-arrow-up text-secondary" style="font-size: 2.5rem;"></i>
                    <span id="up_text" class="text-muted small mt-2">Klik atau seret file ke sini</span>
                    <div id="dragOverlayUp" class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                        <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan File</span>
                    </div>
                </div>
                <input type="file" id="up_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="updateFileName('up_file', 'up_text', 'up_icon')">
                <div class="form-text mt-2" style="font-size: .75rem;"><i class="bi bi-info-circle me-1"></i>Format: PDF, JPG, PNG, DOC (Maks. 5MB)</div>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-shield-lock me-1 text-primary"></i>Hak Akses Dokumen</label>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="up_is_public" id="up_public_0" value="0" checked onchange="toggleInstansiSelect()">
                        <label class="form-check-label small" for="up_public_0">Khusus Instansi Sendiri</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="up_is_public" id="up_public_1" value="1" onchange="toggleInstansiSelect()">
                        <label class="form-check-label small text-success fw-medium" for="up_public_1">Publik (Terlihat Semua)</label>
                    </div>
                </div>
                
                <?php if ($myRole === 'super_admin'): ?>
                <div class="mt-3 pt-3 border-top" id="wrapper_target_instansi">
                    <label class="form-label text-muted small fw-bold mb-1">Upload ke Instansi (Khusus Super Admin):</label>
                    <select class="form-select border shadow-sm py-2" id="up_target_kode">
                        <?php foreach ($semuaInstansi as $si): ?>
                            <option value="<?= htmlspecialchars((string)$si['kode']) ?>"><?= htmlspecialchars((string)$si['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
            <button class="btn btn-light rounded-pill px-4 fw-medium border shadow-sm" data-bs-dismiss="modal">Batal</button>
            <button class="btn btn-warning text-white rounded-pill px-4 fw-medium shadow-sm" onclick="uploadBerkas()">
                <i class="bi bi-cloud-upload me-1"></i> Upload
            </button>
        </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Informasi Berkas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <input type="hidden" id="edit_id">
            <div class="mb-4">
                <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px;">NAMA BERKAS <span class="text-danger">*</span></label>
                <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-tag text-secondary"></i></span>
                    <input type="text" id="edit_nama" class="form-control border-0 shadow-none bg-white py-2">
                </div>
            </div>
            
            <div class="mb-2">
                <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px;">GANTI FILE DOKUMEN (OPSIONAL)</label>
                <div id="dropZoneEdit" class="border rounded-3 mb-2 flex-grow-1 d-flex flex-column align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative" style="min-height: 120px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s; cursor: pointer;" ondragover="handleDragOver(event, 'dropZoneEdit', 'dragOverlayEdit')" ondragleave="handleDragLeave(event, 'dropZoneEdit', 'dragOverlayEdit')" ondrop="handleDrop(event, 'dropZoneEdit', 'dragOverlayEdit', 'edit_file')" onclick="document.getElementById('edit_file').click()">
                    <i id="edit_icon" class="bi bi-cloud-arrow-up text-secondary" style="font-size: 2.5rem;"></i>
                    <span id="edit_text" class="text-muted small mt-2">Klik atau seret file ke sini</span>
                    <div id="dragOverlayEdit" class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                        <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan File</span>
                    </div>
                </div>
                <input type="file" id="edit_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="updateFileName('edit_file', 'edit_text', 'edit_icon')">
                <div class="form-text mt-2" style="font-size: .75rem;"><i class="bi bi-info-circle me-1"></i>Biarkan kosong jika tidak ingin mengganti file yang sudah ada.</div>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-shield-lock me-1 text-primary"></i>Hak Akses Dokumen</label>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="edit_is_public" id="edit_public_0" value="0" onchange="toggleEditInstansiSelect()">
                        <label class="form-check-label small" for="edit_public_0">Khusus Instansi Sendiri</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="edit_is_public" id="edit_public_1" value="1" onchange="toggleEditInstansiSelect()">
                        <label class="form-check-label small text-success fw-medium" for="edit_public_1">Publik (Terlihat Semua)</label>
                    </div>
                </div>
                
                <?php if ($myRole === 'super_admin'): ?>
                <div class="mt-3 pt-3 border-top" id="wrapper_edit_target_instansi">
                    <label class="form-label text-muted small fw-bold mb-1">Kepemilikan Instansi:</label>
                    <select class="form-select border shadow-sm py-2" id="edit_target_kode">
                        <?php foreach ($semuaInstansi as $si): ?>
                            <option value="<?= htmlspecialchars((string)$si['kode']) ?>"><?= htmlspecialchars((string)$si['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
            <button class="btn btn-light rounded-pill px-4 fw-medium border shadow-sm" data-bs-dismiss="modal">Batal</button>
            <button class="btn btn-primary text-white rounded-pill px-4 fw-medium shadow-sm" onclick="simpanEditBerkas()">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
        </div>
    </div>
  </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toastMsg" class="toast align-items-center text-white border-0 bg-success" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
function showToast(msg, ok=true) {
    const t = document.getElementById('toastMsg');
    t.className = 'toast align-items-center text-white border-0 '+(ok?'bg-success':'bg-danger');
    document.getElementById('toastBody').textContent = msg;
    new bootstrap.Toast(t, {delay:3000}).show();
}

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
        updateFileName(inputId, inputId === 'up_file' ? 'up_text' : 'edit_text', inputId === 'up_file' ? 'up_icon' : 'edit_icon');
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

function toggleInstansiSelect() {
    const wrap = document.getElementById('wrapper_target_instansi');
    if (!wrap) return;
    const isPublic = document.getElementById('up_public_1').checked;
    wrap.style.display = isPublic ? 'none' : 'block';
}

function toggleEditInstansiSelect() {
    const wrap = document.getElementById('wrapper_edit_target_instansi');
    if (!wrap) return;
    const isPublic = document.getElementById('edit_public_1').checked;
    wrap.style.display = isPublic ? 'none' : 'block';
}

function openEditModal(id, nama, isPublic, kode) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_file').value = '';
    updateFileName('edit_file', 'edit_text', 'edit_icon');
    
    if (isPublic == 1) {
        document.getElementById('edit_public_1').checked = true;
    } else {
        document.getElementById('edit_public_0').checked = true;
    }
    
    const targetKode = document.getElementById('edit_target_kode');
    if (targetKode) targetKode.value = kode;
    
    toggleEditInstansiSelect();
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function simpanEditBerkas() {
    const id = document.getElementById('edit_id').value;
    const nama = document.getElementById('edit_nama').value;
    const file = document.getElementById('edit_file').files[0];
    const isPublic = document.querySelector('input[name="edit_is_public"]:checked').value;
    const targetKode = document.getElementById('edit_target_kode');
    
    if (!nama) { showToast('Nama berkas wajib diisi', false); return; }
    
    const form = new FormData();
    form.append('nama_berkas', nama);
    form.append('is_public', isPublic);
    if (targetKode && isPublic === '0') form.append('target_kode', targetKode.value);
    if (file) form.append('berkas_file', file);
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/berkas/' + id + '/update', { method: 'POST', body: form, headers: { 'X-CSRF-Token': csrf } })
        .then(r => r.json()).then(data => {
            showToast(data.message, data.success);
            if (data.success) { bootstrap.Modal.getInstance(document.getElementById('editModal'))?.hide(); setTimeout(()=>location.reload(), 800); }
        });
}

function uploadBerkas() {
    const nama = document.getElementById('up_nama').value;
    const file = document.getElementById('up_file').files[0];
    const isPublic = document.querySelector('input[name="up_is_public"]:checked').value;
    const targetKode = document.getElementById('up_target_kode');
    
    if (!nama) { showToast('Nama berkas wajib diisi', false); return; }
    
    const form = new FormData();
    form.append('nama_berkas', nama);
    form.append('is_public', isPublic);
    if (targetKode && isPublic === '0') form.append('target_kode', targetKode.value);
    if (file) form.append('berkas_file', file);
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/berkas/upload', { method: 'POST', body: form, headers: { 'X-CSRF-Token': csrf } })
        .then(r => r.json()).then(data => {
            showToast(data.message, data.success);
            if (data.success) { bootstrap.Modal.getInstance(document.getElementById('uploadModal'))?.hide(); setTimeout(()=>location.reload(), 800); }
        });
}

function hapusBerkas(id, nama) {
    Swal.fire({
        title: 'Hapus Berkas?',
        text: `Hapus berkas "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/berkas/' + id + '/delete', {method:'POST', headers: { 'X-CSRF-Token': csrf }})
                .then(r=>r.json()).then(data => {
                    showToast(data.message, data.success);
                    if (data.success) setTimeout(()=>location.reload(), 800);
                });
        }
    });
}
</script>
