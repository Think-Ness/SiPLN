<?php
declare(strict_types=1);
use Yiisoft\Html\Html;
/**
 * @var array $instansis
 * @var string|null $csrf
 */
$this->setTitle('Manajemen Instansi (Tenant)');
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-buildings text-primary me-2"></i>Manajemen Instansi</h4>
        <p class="text-muted small mb-0">Kelola profil pondok/instansi dan hak akses kepengurusan sistem.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openInstansiModal()">
        <i class="bi bi-plus-circle me-1"></i> Tambah Instansi
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="instansiTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">ID</th>
                        <th class="py-3">Nama Instansi</th>
                        <th class="py-3">Kepengurusan Utama</th>
                        <th class="py-3">Pondok Asal</th>
                        <th class="pe-4 py-3 text-end" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($instansis)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-light"></i>
                            Belum ada Instansi yang terdaftar.
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($instansis as $i): ?>
                    <tr>
                        <td class="ps-4"><span class="badge bg-secondary">#<?= $i['kode'] ?></span></td>
                        <td>
                            <div class="fw-bold text-dark"><?= Html::encode($i['nama_instansi']) ?></div>
                            <div class="small text-muted">Kode: <?= Html::encode($i['kode_instansi'] ?: '-') ?></div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-diagram-3 me-1"></i> <?= Html::encode($i['def_kepengurusan']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                <i class="bi bi-house me-1"></i> <?= Html::encode($i['def_pondok']) ?>
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-sm btn-light border-0" onclick="editInstansi(<?= htmlspecialchars(json_encode($i), ENT_QUOTES, 'UTF-8') ?>)" title="Edit">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                                <button class="btn btn-sm btn-light border-0" onclick="deleteInstansi(<?= $i['kode'] ?>, '<?= Html::encode($i['nama_instansi']) ?>')" title="Hapus">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Instansi -->
<div class="modal fade" id="instansiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom-0 rounded-top-4 pb-2">
                <h5 class="modal-title fw-bold" id="instansiModalTitle">Tambah Instansi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="instansiForm">
                    <input type="hidden" id="form_kode" name="kode">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Nama Instansi</label>
                        <input type="text" class="form-control form-control-lg fs-6 bg-light" id="form_nama" name="nama_instansi" placeholder="Contoh: Instansi Gontor 1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Kode Instansi (Opsional)</label>
                        <input type="text" class="form-control bg-light" id="form_kode_instansi" name="kode_instansi" placeholder="G1">
                    </div>
                    
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Kepengurusan Matriks</label>
                            <input type="text" class="form-control bg-light" id="form_kepengurusan" name="def_kepengurusan" placeholder="Contoh: PONOROGO" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Pondok Matriks</label>
                            <input type="text" class="form-control bg-light" id="form_pondok" name="def_pondok" placeholder="Contoh: G1" required>
                        </div>
                    </div>
                    <div class="form-text text-muted small mt-0 mb-3">
                        * Data Matriks di atas digunakan untuk melacak Hak Milik Santri Pindahan secara otomatis.
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSaveInstansi" onclick="saveInstansi()">Simpan Profil</button>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = '<?= $csrf ?? '' ?>';
    let instansiModalInstance = null;

    function getInstansiModal() {
        if (!instansiModalInstance) {
            instansiModalInstance = new bootstrap.Modal(document.getElementById('instansiModal'));
        }
        return instansiModalInstance;
    }

    function openInstansiModal() {
        document.getElementById('instansiForm').reset();
        document.getElementById('form_kode').value = '';
        document.getElementById('instansiModalTitle').innerText = 'Tambah Instansi Baru';
        getInstansiModal().show();
    }

    function editInstansi(data) {
        document.getElementById('form_kode').value = data.kode;
        document.getElementById('form_nama').value = data.nama_instansi;
        document.getElementById('form_kode_instansi').value = data.kode_instansi || '';
        document.getElementById('form_kepengurusan').value = data.def_kepengurusan;
        document.getElementById('form_pondok').value = data.def_pondok;
        
        document.getElementById('instansiModalTitle').innerText = 'Edit Instansi: ' + data.nama_instansi;
        getInstansiModal().show();
    }

    function saveInstansi() {
        const form = document.getElementById('instansiForm');
        if (!form.reportValidity()) return;

        const btn = document.getElementById('btnSaveInstansi');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        btn.disabled = true;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('<?= API_URL ?>/api/manajemen-instansi/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                getInstansiModal().hide();
                Swal.fire({
                    icon: 'success', title: 'Berhasil', text: data.message,
                    timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-4' }
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Kesalahan koneksi ke server.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function deleteInstansi(id, name) {
        Swal.fire({
            title: 'Hapus Instansi?',
            html: `Anda yakin ingin menghapus <b>${name}</b>?<br>Tindakan ini tidak bisa dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?= API_URL ?>/api/manajemen-instansi/${id}/delete`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': csrfToken }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success', title: 'Terhapus!', text: data.message,
                            timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-4' }
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal Dihapus', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Gagal memproses penghapusan.', 'error'));
            }
        });
    }
</script>
