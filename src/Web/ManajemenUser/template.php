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
$this->setTitle('Manajemen Staf');
?>

<style>
    /* ===== STAFF CARD - Perfect Reference Style ===== */
    .card-staff {
        background: #fff;
        border-radius: 20px;
        padding: 6px;
        border: 1px solid #e8e8e8;
        transition: transform 0.4s cubic-bezier(.25,.8,.25,1), box-shadow 0.4s;
        position: relative;
    }
    .card-staff:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .card-staff.inactive { opacity: 0.6; filter: grayscale(40%); }
    .card-staff.inactive:hover { opacity: 0.9; filter: grayscale(10%); }

    .card-staff .staff-photo-wrap {
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        background: #f8f9fa;
    }
    .card-staff .staff-photo-wrap img {
        width: 100%;
        aspect-ratio: 4/5;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(.25,.8,.25,1);
    }
    .card-staff:hover .staff-photo-wrap img {
        transform: scale(1.05);
    }

    /* Overlay container at bottom of photo */
    .card-staff .staff-overlay {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: linear-gradient(to top, rgba(255,255,255,1) 0%, rgba(255,255,255,0.95) 25%, rgba(255,255,255,0) 100%);
        padding: 60px 16px 16px 16px;
        z-index: 2;
        display: flex;
        flex-direction: column;
    }

    .card-staff .staff-name {
        font-size: 1.25rem;
        font-weight: 800;
        color: #111;
        margin-bottom: 0;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* Bio Section - slides up on hover */
    .card-staff .staff-bio-hover {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(.25,.8,.25,1);
        transform: translateY(10px);
    }
    .card-staff:hover .staff-bio-hover {
        max-height: 150px;
        opacity: 1;
        transform: translateY(0);
        margin-top: 8px;
    }

    .card-staff .staff-desc {
        font-size: 0.85rem;
        color: #444;
        line-height: 1.4;
        margin-bottom: 14px;
    }

    /* Footer Stats & Button */
    .card-staff .staff-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 8px;
        border-top: 1px solid rgba(0,0,0,0.06);
    }

    .card-staff .staff-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: #111;
        font-weight: 600;
    }
    .card-staff .staff-stat i {
        color: #777;
        font-size: 1rem;
        font-weight: normal;
    }

    /* Action button morphs on hover */
    .card-staff .btn-action-card {
        border: 1.5px solid #e0e0e0;
        border-radius: 50px;
        padding: 5px 18px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #555;
        background: #fff;
        transition: all 0.3s ease;
    }
    .card-staff:hover .btn-action-card {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }
    .card-staff.inactive:hover .btn-action-card {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }

    /* Role badge inside photo */
    .card-staff .role-badge-overlay {
        position: absolute;
        top: 22px;
        left: 22px;
        z-index: 2;
        font-size: 0.65rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 50px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .card-staff .status-badge-overlay {
        position: absolute;
        top: 22px;
        right: 22px;
        z-index: 2;
        font-size: 0.6rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 50px;
    }

    .view-toggle .btn { padding: 6px 14px; }
    .view-toggle .btn.active { background: var(--bs-primary); color: #fff; }

    /* Print Frame Styles */
    .print-card-frame {
        width: 260px;
        border: 3px solid #1a3a5c;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8fbff 0%, #e8f0fe 100%);
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .print-card-frame .frame-header {
        background: linear-gradient(135deg, #1a3a5c, #2d5986);
        color: #fff;
        text-align: center;
        padding: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .print-card-frame .frame-photo {
        width: 120px; height: 150px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border-radius: 8px;
    }
    .print-card-frame .frame-name {
        font-family: 'Georgia', serif;
        font-size: 1rem;
        font-weight: 700;
        color: #1a3a5c;
    }

    @media print {
        body * { visibility: hidden !important; }
        #printArea, #printArea * { visibility: visible !important; }
        #printArea {
            position: fixed !important; left: 0; top: 0;
            width: 100% !important;
        }
        .print-card-frame { box-shadow: none !important; }
        .no-print { display: none !important; }
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen Staf</h4>
        <p class="text-muted small mb-0">Atur akses otorisasi untuk staf dan pimpinan instansi.</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <!-- View Toggle -->
        <div class="btn-group view-toggle shadow-sm rounded-pill overflow-hidden border" role="group">
            <button class="btn btn-sm btn-white active" id="btnTableView" onclick="switchView('table')" title="Mode Tabel">
                <i class="bi bi-list-ul"></i>
            </button>
            <button class="btn btn-sm btn-white" id="btnGridView" onclick="switchView('grid')" title="Mode Grid Card">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </button>
        </div>
        <!-- Print -->
        <button class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm" onclick="openPrintPanel()" title="Cetak Bingkai Staf">
            <i class="bi bi-printer-fill me-1"></i> Cetak Bingkai
        </button>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openUserModal()">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Staf
        </button>
    </div>
</div>

<!-- ==================== TABLE VIEW ==================== -->
<div id="tableView">
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Staf / Username</th>
                        <th class="py-3">Bagian</th>
                        <th class="py-3">Role Otoritas</th>
                        <th class="py-3">Telepon</th>
                        <th class="py-3">Instansi</th>
                        <th class="py-3">Status</th>
                        <th class="pe-4 py-3 text-end" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-light"></i>
                            Belum ada akun staf yang terdaftar.
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($users as $u): 
                        $pathFolderBase = !empty($u['path_folder']) ? basename(str_replace('\\', '/', $u['path_folder'])) : '';
                        $subFolder = $pathFolderBase ? '/' . $pathFolderBase . '/profil_staf/' : '/profil_staf/';
                        
                        $waLink = '';
                        if (!empty($u['no_telepon'])) {
                            $waNumber = preg_replace('/[^0-9]/', '', $u['no_telepon']);
                            if (strpos($waNumber, '0') === 0) {
                                $waNumber = '62' . substr($waNumber, 1);
                            }
                            $waLink = "https://wa.me/" . $waNumber;
                        }
                    ?>
                    <tr class="<?= ((int)($u['is_active'] ?? 1)) === 0 ? 'table-secondary opacity-50' : '' ?>">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <?php 
                                    $avatarUrlTable = !empty($u['foto_profile']) 
                                        ? ASSET_URL . '/uploads' . $subFolder . Html::encode($u['foto_profile']) 
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($u['nama_lengkap']) . '&background=e8e8e8&color=555&size=300&font-size=0.4';
                                ?>
                                <img src="<?= $avatarUrlTable ?>" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;" alt="<?= Html::encode($u['nama_lengkap']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['nama_lengkap']) ?>&background=e8e8e8&color=555&size=300&font-size=0.4'">
                                <div>
                                    <div class="fw-bold text-dark"><?= Html::encode($u['nama_lengkap']) ?></div>
                                    <div class="small text-muted">@<?= Html::encode($u['username']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark"><?= !empty($u['bagian']) ? Html::encode($u['bagian']) : '-' ?></div>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'super_admin'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-shield-lock-fill me-1"></i> Super Admin</span>
                            <?php elseif ($u['role'] === 'admin_instansi'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-person-gear me-1"></i> Admin Instansi</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="bi bi-person me-1"></i> Staff</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($u['no_telepon'])): ?>
                                <a href="<?= $waLink ?>" target="_blank" class="small text-decoration-none text-success fw-medium" title="Hubungi via WhatsApp">
                                    <i class="bi bi-whatsapp me-1"></i><?= Html::encode($u['no_telepon']) ?>
                                </a>
                            <?php else: ?>
                                <span class="small text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['instansi_id']): ?>
                                <span class="badge bg-light text-dark border"><i class="bi bi-building text-primary me-1"></i> <?= Html::encode($u['nama_instansi'] ?? '') ?></span>
                            <?php else: ?>
                                <span class="badge bg-dark">Global System</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (((int)($u['is_active'] ?? 1)) === 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-sm btn-light border-0" onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" title="Edit">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                                <?php if ($u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <button class="btn btn-sm btn-light border-0" onclick="toggleActive(<?= $u['id'] ?>, '<?= Html::encode($u['nama_lengkap']) ?>', <?= (int)($u['is_active'] ?? 1) ?>)" title="<?= ((int)($u['is_active'] ?? 1)) === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                    <i class="bi bi-<?= ((int)($u['is_active'] ?? 1)) === 1 ? 'toggle-on text-success' : 'toggle-off text-secondary' ?> fs-5"></i>
                                </button>
                                <?php if ($myRole === 'super_admin'): ?>
                                <button class="btn btn-sm btn-light border-0" onclick="deleteUser(<?= $u['id'] ?>, '<?= Html::encode($u['nama_lengkap']) ?>')" title="Hapus Permanen">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                                <?php endif; ?>
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
</div>

<!-- ==================== GRID CARD VIEW ==================== -->
<div id="gridView" style="display:none;">
<div class="row g-4">
    <?php foreach ($users as $u): 
        $isActive = ((int)($u['is_active'] ?? 1)) === 1;
        
        $pathFolderBase = !empty($u['path_folder']) ? basename(str_replace('\\', '/', $u['path_folder'])) : '';
        $subFolder = $pathFolderBase ? '/' . $pathFolderBase . '/profil_staf/' : '/profil_staf/';
        
        $avatarUrl = !empty($u['foto_profile']) 
            ? ASSET_URL . '/uploads' . $subFolder . $u['foto_profile']
            : 'https://ui-avatars.com/api/?name=' . urlencode($u['nama_lengkap']) . '&background=e8e8e8&color=555&size=300&font-size=0.4';
        $roleColor = $u['role'] === 'super_admin' ? '#dc3545' : ($u['role'] === 'admin_instansi' ? '#198754' : '#0d6efd');
        $roleLabel = $u['role'] === 'super_admin' ? 'Super Admin' : ($u['role'] === 'admin_instansi' ? 'Admin' : 'Staff');
    ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card-staff h-100 <?= !$isActive ? 'inactive' : '' ?>">
            <div class="staff-photo-wrap h-100">
                <img src="<?= $avatarUrl ?>" alt="<?= Html::encode($u['nama_lengkap']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['nama_lengkap']) ?>&background=e8e8e8&color=555&size=300&font-size=0.4'">
                
                <!-- Role Badge (Top Left) -->
                <span class="role-badge-overlay shadow-sm" style="position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.95); color: <?= $roleColor ?>; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; width: max-content; display: inline-block;">
                    <?php if ($u['role'] === 'super_admin'): ?><i class="bi bi-shield-lock-fill me-1"></i><?= $roleLabel ?>
                    <?php elseif ($u['role'] === 'admin_instansi'): ?><i class="bi bi-person-gear me-1"></i><?= $roleLabel ?>
                    <?php else: ?><i class="bi bi-person me-1"></i><?= $roleLabel ?><?php endif; ?>
                </span>
                
                <?php if (!$isActive): ?>
                <span class="status-badge-overlay bg-danger text-white shadow-sm" style="position: absolute; top: 12px; right: 12px; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>
                <?php endif; ?>

                <!-- Gradient Overlay Bottom -->
                <div class="staff-overlay">
                    <h4 class="staff-name"><?= Html::encode($u['nama_lengkap']) ?></h4>
                    
                    <div class="staff-bio-hover">
                        <!-- Instansi and Phone stacked neatly -->
                        <div class="d-flex flex-column gap-1 mb-2 mt-1">
                            <div class="staff-stat text-truncate" title="<?= Html::encode($u['nama_instansi']) ?>">
                                <i class="bi bi-building"></i> <span><?= Html::encode($u['nama_instansi']) ?></span>
                            </div>
                            <?php if (!empty($u['no_telepon'])): ?>
                            <a href="<?= $waLink ?>" target="_blank" class="staff-stat text-decoration-none" title="Chat WhatsApp">
                                <i class="bi bi-whatsapp text-success"></i> <span class="text-success"><?= Html::encode($u['no_telepon']) ?></span>
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Bagian and Edit Button horizontally aligned -->
                        <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid rgba(0,0,0,0.08);">
                            <div class="fw-bold text-dark mb-0 text-truncate pe-2" style="font-size: 0.85rem;" title="<?= Html::encode($u['bagian'] ?? 'Staf') ?>">
                                <?= !empty($u['bagian']) ? Html::encode($u['bagian']) : 'Staf ' . Html::encode(mb_substr((string)($u['nama_instansi'] ?? 'Global'), 0, 10)) ?>
                            </div>
                            
                            <button class="btn btn-sm <?= $isActive ? 'btn-success' : 'btn-secondary' ?> rounded-pill px-3 py-1 fw-bold shadow-sm flex-shrink-0" onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)" style="font-size:0.8rem; z-index: 10;">
                                <?= $isActive ? 'Edit' : 'Aktifkan' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</div>

<!-- ==================== PRINT PANEL MODAL ==================== -->
<div class="modal fade" id="printPanelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-printer-fill text-primary me-2"></i>Cetak Bingkai Staf</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Paper Size & Orientation -->
                <div class="d-flex flex-wrap gap-4 mb-4 align-items-center">
                    <div class="d-flex gap-2 align-items-center">
                        <label class="form-label fw-bold text-muted mb-0 small">Ukuran Kertas:</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="paperSize" id="sizeA3" value="A3">
                            <label class="btn btn-outline-primary btn-sm" for="sizeA3">A3</label>
                            
                            <input type="radio" class="btn-check" name="paperSize" id="sizeA4" value="A4" checked>
                            <label class="btn btn-outline-primary btn-sm" for="sizeA4">A4</label>
                            
                            <input type="radio" class="btn-check" name="paperSize" id="sizeF4" value="Legal">
                            <label class="btn btn-outline-primary btn-sm" for="sizeF4">F4</label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <label class="form-label fw-bold text-muted mb-0 small">Orientasi:</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="printOrientation" id="orientPortrait" value="portrait" checked>
                            <label class="btn btn-outline-primary btn-sm" for="orientPortrait">Potret</label>
                            
                            <input type="radio" class="btn-check" name="printOrientation" id="orientLandscape" value="landscape">
                            <label class="btn btn-outline-primary btn-sm" for="orientLandscape">Lanskap</label>
                        </div>
                    </div>
                </div>
                
                <!-- Staff List with Checkboxes -->
                <p class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Hilangkan centang pada staf yang <b>tidak ingin dicetak</b>.</p>
                <div class="border rounded-4 p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="printSelectAll" checked onchange="document.querySelectorAll('.print-cb').forEach(c=>c.checked=this.checked)">
                            <label class="form-check-label fw-bold small" for="printSelectAll">Pilih Semua</label>
                        </div>
                        <small class="text-muted" id="printCount"><?= count(array_filter($users, fn($u) => ((int)($u['is_active'] ?? 1)) === 1)) ?> staf aktif</small>
                    </div>
                    <?php foreach ($users as $u): 
                        $isActive = ((int)($u['is_active'] ?? 1)) === 1;
                    ?>
                    <div class="form-check d-flex align-items-center gap-2 py-1 <?= !$isActive ? 'opacity-50' : '' ?>">
                        <input class="form-check-input print-cb" type="checkbox" value="<?= $u['id'] ?>" id="print_<?= $u['id'] ?>" <?= $isActive ? 'checked' : '' ?>>
                        <label class="form-check-label small d-flex align-items-center gap-2 flex-fill" for="print_<?= $u['id'] ?>">
                            <span class="fw-bold"><?= Html::encode($u['nama_lengkap']) ?></span>
                            <span class="text-muted">@<?= Html::encode($u['username']) ?></span>
                            <?php if (!$isActive): ?><span class="badge bg-danger-subtle text-danger" style="font-size:0.6rem;">Nonaktif</span><?php endif; ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="doPrint()">
                    <i class="bi bi-printer me-1"></i> Cetak Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Print Area -->
<div id="printArea" style="display:none;">
    <div id="printContent" class="d-flex flex-wrap gap-3 justify-content-center p-4"></div>
</div>

<!-- ==================== USER FORM MODAL ==================== -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom-0 rounded-top-4 pb-2">
                <h5 class="modal-title fw-bold" id="userModalTitle">Tambah Akun Staf</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="userForm">
                    <input type="hidden" id="form_user_id" name="id">
                    
                    <div class="row g-0 h-100">
                        <!-- Left Column: Data Profil -->
                        <div class="col-lg-5 border-end p-4 bg-white h-100 overflow-y-auto" style="max-height: 75vh;">
                            <h6 class="fw-bold text-primary mb-4"><i class="bi bi-person-lines-fill me-2"></i>Data Profil & Login</h6>
                            
                            <!-- Foto Profile -->
                            <div class="d-flex flex-column align-items-center mb-4">
                                <div class="position-relative">
                                    <div id="preview_container" class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm border border-2 border-primary" style="width: 120px; height: 120px; overflow: hidden;">
                                        <i class="bi bi-person text-secondary fs-1" id="preview_icon"></i>
                                        <img id="preview_foto_profile" class="d-none w-100 h-100 object-fit-cover" alt="Preview">
                                    </div>
                                    <label for="form_foto_profile" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="Pilih Foto">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" id="form_foto_profile" name="foto_profile" class="d-none" accept="image/*" onchange="previewImage(event)">
                                </div>
                                <div class="form-text small mt-2">* Opsional (Max 2MB). Format: JPG, PNG.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1">Nama Lengkap</label>
                                <input type="text" class="form-control bg-light" id="form_nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama staf..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1">Bagian / Posisi</label>
                                <input type="text" class="form-control bg-light" id="form_bagian" name="bagian" placeholder="contoh: UI/UX Designer">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1">Tempat, Tanggal Lahir (TTL)</label>
                                <input type="text" class="form-control bg-light" id="form_ttl" name="ttl" placeholder="contoh: Jakarta, 30 Agustus 2005">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-1">No. Telepon</label>
                                <input type="text" class="form-control bg-light" id="form_no_telepon" name="no_telepon" placeholder="contoh: 081234567890">
                            </div>
                            
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Username Login</label>
                                    <input type="text" class="form-control bg-light" id="form_username" name="username" placeholder="contoh: budi_staf" autocomplete="off" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Password Baru</label>
                                    <input type="password" class="form-control bg-light" id="form_password" name="password" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Otoritas & Akses -->
                        <div class="col-lg-7 p-4 bg-light h-100 overflow-y-auto" style="max-height: 75vh;">
                            <h6 class="fw-bold text-primary mb-4"><i class="bi bi-shield-lock-fill me-2"></i>Otoritas & Hak Akses</h6>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Role & Otoritas</label>
                                    <select class="form-select bg-white" id="form_role" name="role" required>
                                        <option value="staff_instansi">Staff (Hanya Pelaksana)</option>
                                        <option value="admin_instansi">Admin Instansi (Pimpinan Pondok)</option>
                                        <?php if ($myRole === 'super_admin'): ?>
                                        <option value="super_admin" class="text-danger fw-bold">Super Admin (God Mode)</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if ($myRole === 'super_admin'): ?>
                                <div class="col-md-6" id="wrapper_instansi">
                                    <label class="form-label small fw-bold text-muted mb-1">Penempatan Instansi</label>
                                    <select class="form-select bg-white" id="form_instansi" name="instansi_id">
                                        <option value="">-- Pilih Instansi --</option>
                                        <?php foreach ($instansis as $i): ?>
                                            <option value="<?= $i['kode'] ?>"><?= Html::encode($i['nama_instansi']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2" id="wrapper_permissions" style="display:none;">
                                <label class="form-label small fw-bold text-muted mb-2">Matriks Otoritas Khusus (Batas Akses)</label>
                                <div class="card bg-white border-0 shadow-sm rounded-4">
                                    <div class="card-body py-3">
                                        <div class="d-flex flex-wrap justify-content-start gap-4 mb-3 pb-2 border-bottom">
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
                                                            <input class="form-check-input perm-cb cb-view" type="checkbox" name="permissions[]" value="<?= Html::encode($m['permission_key']) ?>" id="perm_view_<?= $m['id'] ?>">
                                                            <label class="form-check-label small text-muted text-nowrap" for="perm_view_<?= $m['id'] ?>">Buka</label>
                                                        </div>
                                                        <div class="form-check form-switch m-0">
                                                            <input class="form-check-input perm-cb cb-edit border-danger" type="checkbox" name="permissions[]" value="<?= Html::encode($m['permission_key']) ?>_edit" id="perm_edit_<?= $m['id'] ?>">
                                                            <label class="form-check-label small text-danger text-nowrap" for="perm_edit_<?= $m['id'] ?>">Edit</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0 rounded-bottom-4 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSaveUser" onclick="saveUser()">Simpan Akun</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Profile Modal -->
<div class="modal fade" id="previewProfileModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body d-flex justify-content-center p-0">
                <div class="position-relative overflow-hidden rounded-4 shadow-lg mx-auto" style="width: 320px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(255, 255, 255, 0.4);">
                    <div class="position-relative bg-light" style="height: 250px;">
                        <img id="preview_card_image" src="" class="w-100 h-100 object-fit-cover" alt="User">
                        <div class="position-absolute bottom-0 w-100" style="height: 120px; background: linear-gradient(to top, rgba(255,255,255,1) 10%, transparent);"></div>
                        <div class="position-absolute top-0 w-100 p-3 d-flex justify-content-between align-items-start">
                            <span class="badge rounded-pill text-dark d-flex align-items-center shadow-sm" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(10px);"><i class="bi bi-check-circle-fill text-success me-1"></i> Tersimpan</span>
                            <span class="badge rounded-pill text-dark d-flex align-items-center shadow-sm border border-white" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(10px);" id="preview_role_badge">Role</span>
                        </div>
                    </div>
                    <div class="p-4 pt-0 text-center bg-white position-relative" style="margin-top: -20px; z-index: 2;">
                        <h4 class="fw-bold mb-0 text-dark" id="preview_card_name" style="font-family: 'Outfit', 'Inter', sans-serif;">Nama Lengkap</h4>
                        <p class="small text-muted fw-bold mb-4" id="preview_card_username" style="letter-spacing: 0.5px;">@username</p>
                        <div class="d-flex justify-content-center gap-4 mt-2 mb-4">
                            <div class="text-center">
                                <div class="small fw-bold text-muted mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">STATUS</div>
                                <div class="fw-bold text-primary"><i class="bi bi-circle-fill small me-1"></i>Aktif</div>
                            </div>
                            <div class="text-center" style="border-left: 1px solid #eee; padding-left: 1.5rem;">
                                <div class="small fw-bold text-muted mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">INSTANSI</div>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 100px;" id="preview_card_instansi">HQ</div>
                            </div>
                        </div>
                        <button class="btn btn-dark rounded-pill w-100 shadow fw-bold py-2 mt-2" onclick="window.location.reload()" style="background: linear-gradient(135deg, #1a1a1a, #333);">Selesai & Muat Ulang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = '<?= $csrf ?? '' ?>';
    const myRole = '<?= $myRole ?>';
    const API_URL = '<?= API_URL ?>';
    const ASSET_URL = '<?= ASSET_URL ?>';
    let userModalInstance = null;

    // All users data for print
    const allUsers = <?= json_encode(array_map(function($u) {
        return [
            'id' => $u['id'],
            'nama_lengkap' => $u['nama_lengkap'],
            'username' => $u['username'],
            'role' => $u['role'],
            'bagian' => $u['bagian'] ?? '',
            'no_telepon' => $u['no_telepon'] ?? '',
            'foto_profile' => $u['foto_profile'] ?? '',
            'path_folder' => $u['path_folder'] ?? '',
            'nama_instansi' => $u['nama_instansi'] ?? 'Global System',
            'is_active' => (int)($u['is_active'] ?? 1),
        ];
    }, $users)) ?>;

    function getUserModal() {
        if (!userModalInstance) {
            userModalInstance = new bootstrap.Modal(document.getElementById('userModal'));
        }
        return userModalInstance;
    }

    // ====== VIEW TOGGLE ======
    function switchView(mode) {
        document.getElementById('tableView').style.display = mode === 'table' ? 'block' : 'none';
        document.getElementById('gridView').style.display = mode === 'grid' ? 'block' : 'none';
        document.getElementById('btnTableView').classList.toggle('active', mode === 'table');
        document.getElementById('btnGridView').classList.toggle('active', mode === 'grid');
        localStorage.setItem('staffViewMode', mode);
    }
    // Restore last view
    (function() {
        const saved = localStorage.getItem('staffViewMode');
        if (saved === 'grid') switchView('grid');
    })();

    // ====== TOGGLE ALL PERMISSIONS ======
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

    // ====== OPEN USER MODAL ======
    function openUserModal() {
        initRoleListener();
        document.getElementById('userForm').reset();
        document.getElementById('form_user_id').value = '';
        document.getElementById('userModalTitle').innerText = 'Tambah Akun Staf';
        document.getElementById('form_password').placeholder = 'Wajib diisi untuk akun baru';
        document.getElementById('form_password').required = true;
        
        const instansiWrap = document.getElementById('wrapper_instansi');
        if (instansiWrap) instansiWrap.style.display = 'block';
        
        const permWrap = document.getElementById('wrapper_permissions');
        if (permWrap) permWrap.style.display = document.getElementById('form_role').value !== 'super_admin' ? 'block' : 'none';
        
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false);

        document.getElementById('form_foto_profile').value = '';
        document.getElementById('preview_foto_profile').classList.add('d-none');
        document.getElementById('preview_icon').classList.remove('d-none');

        getUserModal().show();
    }

    // ====== EDIT USER ======
    function editUser(data) {
        initRoleListener();
        document.getElementById('form_user_id').value = '';
        document.getElementById('form_username').value = '';
        document.getElementById('form_nama_lengkap').value = '';
        document.getElementById('form_ttl').value = '';
        document.getElementById('form_bagian').value = '';
        document.getElementById('form_no_telepon').value = '';
        document.getElementById('form_password').value = '';
        document.getElementById('form_password').placeholder = 'Kosongkan jika tidak diubah';
        document.getElementById('form_password').required = false;
        
        document.getElementById('form_user_id').value = data.id;
        document.getElementById('form_nama_lengkap').value = data.nama_lengkap;
        document.getElementById('form_ttl').value = data.ttl || '';
        document.getElementById('form_bagian').value = data.bagian || '';
        document.getElementById('form_no_telepon').value = data.no_telepon || '';
        document.getElementById('form_username').value = data.username;
        
        document.getElementById('form_role').value = data.role;
        
        const instansiWrap = document.getElementById('wrapper_instansi');
        if (myRole === 'super_admin' && instansiWrap) {
            document.getElementById('form_instansi').value = data.instansi_id || '';
            instansiWrap.style.display = data.role === 'super_admin' ? 'none' : 'block';
        }
        
        const permWrap = document.getElementById('wrapper_permissions');
        if (permWrap) permWrap.style.display = data.role !== 'super_admin' ? 'block' : 'none';
        
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
        
        const chkView = document.getElementById('checkAllView');
        const chkEdit = document.getElementById('checkAllEdit');
        if (chkView) chkView.checked = false;
        if (chkEdit) chkEdit.checked = false;

        document.getElementById('form_foto_profile').value = '';
        if (data.foto_profile) {
            let pathFolderBase = data.path_folder ? data.path_folder.replace(/\\/g, '/').split('/').pop() : '';
            let subFolder = pathFolderBase ? '/' + pathFolderBase + '/profil_staf/' : '/profil_staf/';
            document.getElementById('preview_foto_profile').src = ASSET_URL + '/uploads' + subFolder + data.foto_profile;
            document.getElementById('preview_foto_profile').classList.remove('d-none');
            document.getElementById('preview_icon').classList.add('d-none');
        } else {
            document.getElementById('preview_foto_profile').classList.add('d-none');
            document.getElementById('preview_icon').classList.remove('d-none');
        }

        document.getElementById('userModalTitle').innerText = 'Edit Staf: ' + data.nama_lengkap;
        getUserModal().show();
    }

    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_foto_profile').src = e.target.result;
                document.getElementById('preview_foto_profile').classList.remove('d-none');
                document.getElementById('preview_icon').classList.add('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ====== SAVE USER ======
    function saveUser() {
        const form = document.getElementById('userForm');
        if (!form.reportValidity()) return;

        const btn = document.getElementById('btnSaveUser');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        btn.disabled = true;

        const formData = new FormData(form);
        formData.delete('permissions[]');
        document.querySelectorAll('input[name="permissions[]"]:checked').forEach(cb => {
            formData.append('permissions[]', cb.value);
        });

        fetch(API_URL + '/api/manajemen-user/store', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                getUserModal().hide();
                
                document.getElementById('preview_card_name').innerText = document.getElementById('form_nama_lengkap').value;
                document.getElementById('preview_card_username').innerText = '@' + document.getElementById('form_username').value;
                const roleSel = document.getElementById('form_role');
                document.getElementById('preview_role_badge').innerText = roleSel.options[roleSel.selectedIndex].text;
                
                const instansiSel = document.getElementById('form_instansi');
                document.getElementById('preview_card_instansi').innerText = (instansiSel && instansiSel.value) ? instansiSel.options[instansiSel.selectedIndex].text : 'Global System';

                const src = document.getElementById('preview_foto_profile').src;
                if (!document.getElementById('preview_foto_profile').classList.contains('d-none')) {
                    document.getElementById('preview_card_image').src = src;
                } else {
                    document.getElementById('preview_card_image').src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(document.getElementById('form_nama_lengkap').value) + '&background=random&color=fff&size=250';
                }

                new bootstrap.Modal(document.getElementById('previewProfileModal')).show();
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

    // ====== TOGGLE ACTIVE ======
    function toggleActive(id, name, currentStatus) {
        const action = currentStatus === 1 ? 'menonaktifkan' : 'mengaktifkan kembali';
        Swal.fire({
            title: currentStatus === 1 ? 'Nonaktifkan Staf?' : 'Aktifkan Kembali?',
            html: `Anda yakin ingin ${action} <b>${name}</b>?`,
            icon: currentStatus === 1 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: currentStatus === 1 ? '#dc3545' : '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: currentStatus === 1 ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_URL}/api/manajemen-user/${id}/toggle-active`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': csrfToken }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success', title: 'Berhasil!', text: data.message,
                            timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-4' }
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Gagal memproses permintaan.', 'error'));
            }
        });
    }

    // ====== DELETE USER (PERMANENT - Super Admin only) ======
    function deleteUser(id, name) {
        Swal.fire({
            title: 'Hapus Permanen?',
            html: `<b class="text-danger">PERINGATAN:</b> Aksi ini akan menghapus <b>${name}</b> secara permanen dan tidak bisa dibatalkan!<br><br>Jika Anda hanya ingin menonaktifkan, gunakan tombol toggle.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Hapus Permanen!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_URL}/api/manajemen-user/${id}/delete`, {
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

    // ====== PRINT PANEL ======
    function openPrintPanel() {
        new bootstrap.Modal(document.getElementById('printPanelModal')).show();
    }

    function doPrint() {
        const selectedIds = Array.from(document.querySelectorAll('.print-cb:checked')).map(c => parseInt(c.value));
        const paperSize = document.querySelector('input[name="paperSize"]:checked').value;
        
        const selectedUsers = allUsers.filter(u => selectedIds.includes(parseInt(u.id)));
        
        if (selectedUsers.length === 0) {
            Swal.fire('Oops', 'Tidak ada staf yang dipilih untuk dicetak.', 'warning');
            return;
        }

        let html = '';
        selectedUsers.forEach(u => {
            let pathFolderBase = u.path_folder ? u.path_folder.replace(/\\/g, '/').split('/').pop() : '';
            let subFolder = pathFolderBase ? '/' + pathFolderBase + '/profil_staf/' : '/profil_staf/';
            const imgSrc = u.foto_profile 
                ? ASSET_URL + '/uploads' + subFolder + u.foto_profile
                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.nama_lengkap) + '&background=1a3a5c&color=fff&size=400';
            const roleTxt = u.role === 'super_admin' ? 'Super Admin' : (u.role === 'admin_instansi' ? 'Admin Instansi' : 'Staff');
            
            html += `
            <div class="print-card-frame" style="position:relative; background:#fff; border-radius:24px; padding:10px; border:1px solid #e8e8e8; margin: 15px; break-inside: avoid; width: 340px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                <div style="position:relative; border-radius:18px; overflow:hidden; background:#f8f9fa;">
                    <img src="${imgSrc}" style="width:100%; aspect-ratio:4/5; object-fit:cover;" alt="${u.nama_lengkap}" 
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(u.nama_lengkap)}&background=6c757d&color=fff&size=400'">
                    
                    <!-- Role Badge -->
                    <div style="position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.9); padding:6px 14px; border-radius:20px; font-size:0.85rem; font-weight:700; color:#1a3a5c; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        ${roleTxt}
                    </div>

                    <!-- Overlay -->
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(255,255,255,1) 0%, rgba(255,255,255,0.95) 25%, rgba(255,255,255,0) 100%); padding:80px 24px 20px 24px;">
                        <h3 style="font-size:1.5rem; font-weight:800; color:#111; margin-bottom:4px; font-family:'Inter', sans-serif;">${u.nama_lengkap}</h3>
                        <p style="font-size:0.95rem; color:#444; line-height:1.4; margin-bottom:16px;">
                            ${u.bagian ? u.bagian : 'Staf ' + u.nama_instansi}
                        </p>
                        
                        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid rgba(0,0,0,0.08);">
                            <div style="display:flex; gap:16px; font-size:0.9rem; color:#111; font-weight:600;">
                                <span><i class="bi bi-building" style="color:#777; margin-right:6px;"></i>${u.nama_instansi}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        document.getElementById('printContent').innerHTML = html;

        // Set print page size and orientation
        let styleSheet = document.getElementById('printSizeStyle');
        if (!styleSheet) {
            styleSheet = document.createElement('style');
            styleSheet.id = 'printSizeStyle';
            document.head.appendChild(styleSheet);
        }
        const printOrientation = document.querySelector('input[name="printOrientation"]:checked')?.value || 'portrait';
        styleSheet.textContent = `@media print { @page { size: ${paperSize} ${printOrientation}; margin: 10mm; } }`;
        
        document.getElementById('printArea').style.display = 'block';
        
        // Close the print panel modal
        bootstrap.Modal.getInstance(document.getElementById('printPanelModal'))?.hide();
        
        setTimeout(() => {
            window.print();
            setTimeout(() => { document.getElementById('printArea').style.display = 'none'; }, 500);
        }, 300);
    }
</script>
