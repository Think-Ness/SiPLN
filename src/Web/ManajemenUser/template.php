<?php
declare(strict_types=1);
use Yiisoft\Html\Html;
/**
 * @var array $users
 * @var array $instansis
 * @var string $myRole
 * @var int|null $myInstansiId
 * @var string|null $csrf
 */
$this->setTitle('Manajemen Pengguna (User)');
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen Akun Pengguna</h4>
        <p class="text-muted small mb-0">Atur akses otorisasi untuk staf dan pimpinan instansi.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openUserModal()">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Akun
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Pegawai / Username</th>
                        <th class="py-3">Role Otoritas</th>
                        <th class="py-3">Penempatan Instansi</th>
                        <th class="pe-4 py-3 text-end" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-light"></i>
                            Belum ada akun pegawai yang terdaftar.
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-<?= $u['role'] === 'super_admin' ? 'danger' : 'primary' ?>-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="bi bi-person-badge text-<?= $u['role'] === 'super_admin' ? 'danger' : 'primary' ?> fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= Html::encode($u['nama_lengkap']) ?></div>
                                    <div class="small text-muted">@<?= Html::encode($u['username']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'super_admin'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-shield-lock-fill me-1"></i> Super Admin</span>
                            <?php elseif ($u['role'] === 'admin_instansi'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-person-gear me-1"></i> Admin Instansi</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="bi bi-person me-1"></i> Staff Instansi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['instansi_id']): ?>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building text-primary me-1"></i> <?= Html::encode($u['nama_instansi']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-dark">Global System</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-sm btn-light border-0" onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" title="Edit">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                                <?php if ($u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <button class="btn btn-sm btn-light border-0" onclick="deleteUser(<?= $u['id'] ?>, '<?= Html::encode($u['nama_lengkap']) ?>')" title="Hapus">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal User -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom-0 rounded-top-4 pb-2">
                <h5 class="modal-title fw-bold" id="userModalTitle">Tambah Akun Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2" style="max-height: 65vh; overflow-y: auto;">
                <form id="userForm">
                    <input type="hidden" id="form_user_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-lg fs-6 bg-light" id="form_nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama pegawai..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Tempat, Tanggal Lahir (TTL)</label>
                        <input type="text" class="form-control bg-light" id="form_ttl" name="ttl" placeholder="contoh: Jakarta, 30 Agustus 2005">
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Username Login</label>
                            <input type="text" class="form-control bg-light" id="form_username" name="username" placeholder="contoh: budi_staf" autocomplete="off" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Password Baru</label>
                            <input type="password" class="form-control bg-light" id="form_password" name="password" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Role & Otoritas</label>
                        <select class="form-select bg-light" id="form_role" name="role" required>
                            <option value="staff_instansi">Staff (Hanya Pelaksana)</option>
                            <option value="admin_instansi">Admin Instansi (Pimpinan Pondok)</option>
                            <?php if ($myRole === 'super_admin'): ?>
                            <option value="super_admin" class="text-danger fw-bold">Super Admin (God Mode)</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <?php if ($myRole === 'super_admin'): ?>
                    <div class="mb-3" id="wrapper_instansi">
                        <label class="form-label small fw-bold text-muted mb-1">Penempatan Instansi</label>
                        <select class="form-select bg-light" id="form_instansi" name="instansi_id">
                            <option value="">-- Pilih Instansi Penempatan --</option>
                            <?php foreach ($instansis as $i): ?>
                                <option value="<?= $i['kode'] ?>"><?= Html::encode($i['nama_instansi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">* Kosongkan jika role-nya Super Admin.</div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3 mt-4" id="wrapper_permissions" style="display:none;">
                        <label class="form-label small fw-bold text-muted mb-2">Matriks Otoritas Khusus (Batas Akses)</label>
                        <div class="card bg-light border-0 shadow-sm rounded-4">
                            <div class="card-body py-3">
                                <!-- Tombol Check All -->
                                <div class="d-flex justify-content-start gap-4 mb-3 pb-2 border-bottom">
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="checkAllView" onchange="toggleAll('.cb-view', this.checked)">
                                        <label class="form-check-label small fw-bold text-dark" for="checkAllView">Pilih Semua Buka</label>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input border-danger" type="checkbox" id="checkAllEdit" onchange="toggleAll('.cb-edit', this.checked)">
                                        <label class="form-check-label small fw-bold text-danger" for="checkAllEdit">Pilih Semua Edit</label>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <?php if (!empty($menus)): ?>
                                        <?php foreach ($menus as $m): ?>
                                        <div class="col-md-6 border-bottom pb-2">
                                            <div class="fw-bold mb-1 text-dark small text-truncate">
                                                <i class="<?= Html::encode($m['icon']) ?> me-1 text-primary"></i><?= Html::encode($m['nama_menu']) ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input perm-cb cb-view" 
                                                           type="checkbox" name="permissions[]" 
                                                           value="<?= Html::encode($m['permission_key']) ?>" 
                                                           id="perm_view_<?= $m['id'] ?>">
                                                    <label class="form-check-label small text-muted text-nowrap" for="perm_view_<?= $m['id'] ?>">
                                                        Buka
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input perm-cb cb-edit border-danger" 
                                                           type="checkbox" name="permissions[]" 
                                                           value="<?= Html::encode($m['permission_key']) ?>_edit" 
                                                           id="perm_edit_<?= $m['id'] ?>">
                                                    <label class="form-check-label small text-danger text-nowrap" for="perm_edit_<?= $m['id'] ?>">
                                                        Edit
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSaveUser" onclick="saveUser()">Simpan Akun</button>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = '<?= $csrf ?? '' ?>';
    const myRole = '<?= $myRole ?>';
    let userModalInstance = null;

    function getUserModal() {
        if (!userModalInstance) {
            userModalInstance = new bootstrap.Modal(document.getElementById('userModal'));
        }
        return userModalInstance;
    }

    function toggleAll(selector, isChecked) {
        document.querySelectorAll(selector).forEach(cb => cb.checked = isChecked);
    }

    function initRoleListener() {
        const roleSelect = document.getElementById('form_role');
        const instansiWrap = document.getElementById('wrapper_instansi');
        const permWrap = document.getElementById('wrapper_permissions');
        
        if (roleSelect && !roleSelect.dataset.listenerAttached) {
            roleSelect.addEventListener('change', function() {
                if (instansiWrap) instansiWrap.style.display = this.value === 'super_admin' ? 'none' : 'block';
                if (permWrap) permWrap.style.display = this.value !== 'super_admin' ? 'block' : 'none';
            });
            roleSelect.dataset.listenerAttached = 'true';
        }
    }

    function openUserModal() {
        initRoleListener();
        document.getElementById('userForm').reset();
        document.getElementById('form_user_id').value = '';
        document.getElementById('userModalTitle').innerText = 'Tambah Akun Pegawai';
        document.getElementById('form_password').placeholder = 'Wajib diisi untuk akun baru';
        document.getElementById('form_password').required = true;
        
        const instansiWrap = document.getElementById('wrapper_instansi');
        if (instansiWrap) instansiWrap.style.display = 'block';
        
        const permWrap = document.getElementById('wrapper_permissions');
        if (permWrap) permWrap.style.display = document.getElementById('form_role').value !== 'super_admin' ? 'block' : 'none';
        
        // Uncheck all permissions
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false);

        getUserModal().show();
    }

    function editUser(data) {
        initRoleListener();
        document.getElementById('form_user_id').value = data.id;
        document.getElementById('form_nama_lengkap').value = data.nama_lengkap;
        document.getElementById('form_ttl').value = data.ttl || '';
        document.getElementById('form_username').value = data.username;
        document.getElementById('form_password').value = '';
        document.getElementById('form_password').placeholder = 'Kosongkan jika tidak diubah';
        document.getElementById('form_password').required = false;
        
        document.getElementById('form_role').value = data.role;
        
        const instansiWrap = document.getElementById('wrapper_instansi');
        if (myRole === 'super_admin' && instansiWrap) {
            document.getElementById('form_instansi').value = data.instansi_id || '';
            instansiWrap.style.display = data.role === 'super_admin' ? 'none' : 'block';
        }
        
        const permWrap = document.getElementById('wrapper_permissions');
        if (permWrap) permWrap.style.display = data.role !== 'super_admin' ? 'block' : 'none';
        
        // Ceklik checkbox sesuai data permissions JSON (jika ada)
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false);
        if (data.permissions) {
            try {
                const perms = JSON.parse(data.permissions);
                if (Array.isArray(perms)) {
                    document.querySelectorAll('.perm-cb').forEach(cb => {
                        if (perms.includes(cb.value)) cb.checked = true;
                    });
                }
            } catch (e) {}
        }
        
        // Reset check all toggles
        const chkView = document.getElementById('checkAllView');
        const chkEdit = document.getElementById('checkAllEdit');
        if (chkView) chkView.checked = false;
        if (chkEdit) chkEdit.checked = false;

        document.getElementById('userModalTitle').innerText = 'Edit Pegawai: ' + data.nama_lengkap;
        getUserModal().show();
    }

    function saveUser() {
        const form = document.getElementById('userForm');
        if (!form.reportValidity()) return;

        const btn = document.getElementById('btnSaveUser');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        btn.disabled = true;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Ambil data array dari checkbox permissions karena Object.fromEntries tidak support array
        data.permissions = [];
        document.querySelectorAll('input[name="permissions[]"]:checked').forEach(cb => {
            data.permissions.push(cb.value);
        });

        fetch('<?= API_URL ?>/api/manajemen-user/store', {
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
                getUserModal().hide();
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

    function deleteUser(id, name) {
        Swal.fire({
            title: 'Cabut Akses Akun?',
            html: `Anda yakin ingin menghapus akses sistem untuk <b>${name}</b>?<br>Tindakan ini tidak bisa dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?= API_URL ?>/api/manajemen-user/${id}/delete`, {
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
