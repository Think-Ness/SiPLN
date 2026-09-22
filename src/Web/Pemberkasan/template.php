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

<style>
    /* Modern Search Bar */
    .modern-search-wrapper {
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
    }
    .modern-search-input {
        width: 100% !important;
        height: 42px !important;
        padding-left: 38px !important;
        padding-right: 36px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        background-color: #ffffff !important;
        font-size: 0.85rem !important;
        color: #1e293b !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        transition: all 0.2s ease-in-out !important;
    }
    .modern-search-input:focus {
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.18) !important;
        outline: none !important;
    }
    .modern-search-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
        pointer-events: none;
        z-index: 5;
    }
    .modern-search-clear {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        color: #94a3b8;
        background: transparent;
        border: none;
        padding: 0;
        cursor: pointer;
        display: <?= !empty($search) ? 'inline-flex' : 'none' ?>;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        z-index: 5;
    }
    .modern-search-clear:hover {
        color: #475569;
    }

    /* Berkas Card Styling */
    .berkas-card {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 16px !important;
        background: #ffffff;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .berkas-card:hover {
        transform: translateY(-4px);
        border-color: #cbd5e1 !important;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
    }
    .berkas-icon-box {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: transform 0.2s ease;
    }
    .berkas-card:hover .berkas-icon-box {
        transform: scale(1.08);
    }
    .berkas-title-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.35;
        min-height: 2.7em;
        font-size: 0.82rem;
    }

    /* Action Buttons */
    .btn-action-circle {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 50% !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.82rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        transition: all 0.15s ease;
    }
    .btn-action-circle:hover {
        transform: scale(1.1);
        background: #ffffff;
    }

    /* List View Styling */
    .berkas-list-item {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 14px !important;
        background: #ffffff;
        padding: 12px 16px;
        transition: all 0.2s ease;
        margin-bottom: 10px;
    }
    .berkas-list-item:hover {
        border-color: #93c5fd !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.06);
    }

    /* Responsive Mobile Tweaks */
    @media (max-width: 768px) {
        .page-header-responsive {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        .page-header-responsive > div:first-child {
            width: 100% !important;
        }
        .page-header-responsive .btn-upload-top {
            width: 100% !important;
            justify-content: center !important;
            padding: 10px 16px !important;
        }
        .berkas-card .card-body {
            padding: 12px 10px 8px 10px !important;
        }
        .berkas-icon-box {
            width: 46px !important;
            height: 46px !important;
            border-radius: 12px !important;
        }
        .berkas-icon-box i {
            font-size: 1.5rem !important;
        }
        .berkas-title-clamp {
            font-size: 0.76rem !important;
            min-height: 2.7em !important;
        }
        .berkas-card .card-footer {
            padding: 6px 8px 12px 8px !important;
            gap: 4px !important;
        }
        .btn-action-circle {
            width: 30px !important;
            height: 30px !important;
            min-width: 30px !important;
            font-size: 0.75rem !important;
        }
        .btn-lihat-mobile {
            font-size: 0.72rem !important;
            padding: 4px 10px !important;
        }
        
        /* Modal on Mobile */
        #uploadModal .modal-dialog,
        #editModal .modal-dialog {
            margin: 0.4rem;
            max-width: calc(100% - 0.8rem) !important;
        }
        #uploadModal .modal-content,
        #editModal .modal-content {
            border-radius: 16px !important;
            max-height: 94vh !important;
        }
        #uploadModal .modal-header,
        #editModal .modal-header {
            padding: 14px 16px !important;
        }
        #uploadModal .modal-body,
        #editModal .modal-body {
            padding: 14px 16px !important;
        }
        #uploadModal .modal-footer,
        #editModal .modal-footer {
            padding: 10px 16px 14px 16px !important;
            flex-direction: column !important;
            gap: 8px !important;
        }
        #uploadModal .modal-footer .btn,
        #editModal .modal-footer .btn {
            width: 100% !important;
            justify-content: center !important;
            padding: 9px 16px !important;
        }
    }
</style>

<div class="px-2 py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 page-header-responsive">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1.5px solid #fcd34d;">
                <i class="bi bi-folder2-open fs-4 text-warning-emphasis"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Pemberkasan Dokumen</h4>
                <div class="text-muted small fw-medium mt-1">
                    Total: <strong class="text-dark" id="labelTotalCount"><?= $total ?></strong> berkas tersimpan
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 btn-upload-top" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" onclick="openUploadModal()">
                <i class="bi bi-cloud-upload-fill"></i> Upload Berkas
            </button>
        </div>
    </div>

    <!-- Search Toolbar & View Switcher -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white;">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Search Box -->
                <div class="col-12 col-md-7 col-lg-6">
                    <form id="formSearchBerkas" method="GET" class="m-0" onsubmit="return handleFormSearchSubmit(event)">
                        <div class="modern-search-wrapper">
                            <i class="bi bi-search modern-search-icon"></i>
                            <input type="text" id="inputSearchBerkas" name="q" class="modern-search-input" placeholder="Cari nama berkas, instansi, atau tipe file..." value="<?= htmlspecialchars($search) ?>" oninput="handleSearchLiveFilter(this)">
                            <button id="btnClearSearchBerkas" class="modern-search-clear" type="button" onclick="clearBerkasSearch()" title="Reset Pencarian">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- View Mode Switcher -->
                <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    <span class="text-muted small d-none d-sm-inline" style="font-size: 0.78rem;" id="labelLiveCount">Menampilkan <?= count($berkas) ?> berkas</span>
                    <div class="btn-group btn-group-sm shadow-sm rounded-pill p-1 bg-light border" role="group">
                        <button type="button" id="btnViewGrid" class="btn btn-primary rounded-pill px-3 fw-medium active" onclick="switchBerkasView('grid')" title="Tampilan Grid">
                            <i class="bi bi-grid-fill me-1"></i>Grid
                        </button>
                        <button type="button" id="btnViewList" class="btn btn-light text-secondary rounded-pill px-3 fw-medium" onclick="switchBerkasView('list')" title="Tampilan List">
                            <i class="bi bi-list-ul me-1"></i>List
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State (No Berkas) -->
    <div id="emptyStateContainer" class="col-12 <?= empty($berkas) ? '' : 'd-none' ?>">
        <div class="card border-0 shadow-sm rounded-4 text-center py-5" style="background: white;">
            <div class="mb-3">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; background: #f8fafc; border: 2px dashed #cbd5e1;">
                    <i class="bi bi-folder-x text-muted" style="font-size: 2.2rem;"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark mb-1">Tidak Ada Berkas Ditemukan</h5>
            <p class="text-muted small mb-3" style="max-width: 320px; margin: 0 auto;">Belum ada berkas yang sesuai dengan kata kunci pencarian atau belum ada file yang diunggah.</p>
            <div>
                <button class="btn btn-sm btn-primary rounded-pill px-4 fw-medium shadow-sm" onclick="openUploadModal()">
                    <i class="bi bi-cloud-upload me-1"></i>Upload Dokumen Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Container Grid View -->
    <div id="berkasGridView" class="row g-3 <?= empty($berkas) ? 'd-none' : '' ?>">
        <?php foreach ($berkas as $b):
            $ext = strtolower(pathinfo($b['path_file'] ?? '', PATHINFO_EXTENSION));
            $iconInfo = match($ext) {
                'pdf' => ['icon' => 'bi-filetype-pdf', 'color' => '#ef4444', 'bg' => '#fef2f2', 'border' => '#fecaca', 'label' => 'PDF'],
                'jpg','jpeg','png','gif','webp' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#0284c7', 'bg' => '#f0f9ff', 'border' => '#bae6fd', 'label' => strtoupper($ext)],
                'doc','docx' => ['icon' => 'bi-filetype-doc', 'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'label' => 'DOC'],
                'xls','xlsx','csv' => ['icon' => 'bi-filetype-xls', 'color' => '#16a34a', 'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'label' => 'XLS'],
                default => ['icon' => 'bi-file-earmark-text-fill', 'color' => '#64748b', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'label' => strtoupper($ext ?: 'FILE')]
            };
            $isPublic = ($b['is_public'] ?? 0) == 1;
            $instansiName = (string)($b['nama_instansi'] ?? $b['kode'] ?? 'Instansi');
            $canManage = ($myRole === 'super_admin' || $b['kode'] === ($_SESSION['instansi_id'] ?? null));
        ?>
        <div class="col-6 col-md-4 col-lg-3 berkas-item-card" data-title="<?= htmlspecialchars(strtolower($b['nama_berkas'])) ?>" data-instansi="<?= htmlspecialchars(strtolower($instansiName)) ?>" data-ext="<?= htmlspecialchars(strtolower($ext)) ?>">
            <div class="card h-100 berkas-card shadow-sm overflow-hidden">
                <!-- Top Badge Ext -->
                <div class="position-absolute top-0 end-0 pt-2 pe-2 z-1">
                    <span class="badge rounded-pill fw-bold" style="background: <?= $iconInfo['bg'] ?>; color: <?= $iconInfo['color'] ?>; border: 1px solid <?= $iconInfo['border'] ?>; font-size: 0.65rem;">
                        <?= $iconInfo['label'] ?>
                    </span>
                </div>

                <div class="card-body text-center p-3 pt-4 d-flex flex-column align-items-center">
                    <!-- Icon File -->
                    <div class="berkas-icon-box mb-3 shadow-sm" style="background: <?= $iconInfo['bg'] ?>; border: 1.5px solid <?= $iconInfo['border'] ?>; color: <?= $iconInfo['color'] ?>;">
                        <i class="bi <?= $iconInfo['icon'] ?> fs-3"></i>
                    </div>

                    <!-- Nama Berkas -->
                    <h6 class="fw-bold text-dark berkas-title-clamp mb-2" title="<?= htmlspecialchars($b['nama_berkas']) ?>">
                        <?= htmlspecialchars($b['nama_berkas']) ?>
                    </h6>

                    <!-- Hak Akses / Instansi Badge -->
                    <div class="mt-auto w-100">
                        <?php if ($isPublic): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-1 w-100 text-truncate" style="font-size: 0.68rem;" title="Publik (Semua Instansi)">
                                <i class="bi bi-globe me-1"></i>Publik
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-1 w-100 text-truncate" style="font-size: 0.68rem;" title="<?= htmlspecialchars($instansiName) ?>">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($instansiName) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="card-footer bg-white border-top-0 d-flex gap-2 justify-content-center align-items-center pb-3 pt-0">
                    <?php if ($b['path_file']): ?>
                    <a href="<?= API_URL ?>/berkas/<?= $b['id'] ?>/view" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-medium shadow-sm d-flex align-items-center gap-1 btn-lihat-mobile" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none;">
                        <i class="bi bi-eye"></i> Lihat
                    </a>
                    <a href="<?= API_URL ?>/berkas/<?= $b['id'] ?>/view?dl=1" class="btn-action-circle text-success" title="Download File">
                        <i class="bi bi-download"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($canManage): ?>
                    <button type="button" class="btn-action-circle text-primary" onclick="openEditModal(<?= $b['id'] ?>, '<?= addslashes($b['nama_berkas']) ?>', <?= $b['is_public'] ?? 0 ?>, '<?= htmlspecialchars((string)$b['kode']) ?>')" title="Edit Berkas">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn-action-circle text-danger" onclick="hapusBerkas(<?= $b['id'] ?>, '<?= addslashes($b['nama_berkas']) ?>')" title="Hapus Berkas">
                        <i class="bi bi-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Container List View -->
    <div id="berkasListView" class="d-none">
        <?php foreach ($berkas as $b):
            $ext = strtolower(pathinfo($b['path_file'] ?? '', PATHINFO_EXTENSION));
            $iconInfo = match($ext) {
                'pdf' => ['icon' => 'bi-filetype-pdf', 'color' => '#ef4444', 'bg' => '#fef2f2', 'border' => '#fecaca', 'label' => 'PDF'],
                'jpg','jpeg','png','gif','webp' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#0284c7', 'bg' => '#f0f9ff', 'border' => '#bae6fd', 'label' => strtoupper($ext)],
                'doc','docx' => ['icon' => 'bi-filetype-doc', 'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'label' => 'DOC'],
                'xls','xlsx','csv' => ['icon' => 'bi-filetype-xls', 'color' => '#16a34a', 'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'label' => 'XLS'],
                default => ['icon' => 'bi-file-earmark-text-fill', 'color' => '#64748b', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'label' => strtoupper($ext ?: 'FILE')]
            };
            $isPublic = ($b['is_public'] ?? 0) == 1;
            $instansiName = (string)($b['nama_instansi'] ?? $b['kode'] ?? 'Instansi');
            $canManage = ($myRole === 'super_admin' || $b['kode'] === ($_SESSION['instansi_id'] ?? null));
        ?>
        <div class="berkas-list-item berkas-item-card d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-sm" data-title="<?= htmlspecialchars(strtolower($b['nama_berkas'])) ?>" data-instansi="<?= htmlspecialchars(strtolower($instansiName)) ?>" data-ext="<?= htmlspecialchars(strtolower($ext)) ?>">
            <div class="d-flex align-items-center gap-3 text-truncate" style="max-width: calc(100% - 150px);">
                <div class="berkas-icon-box shadow-sm flex-shrink-0" style="width: 44px; height: 44px; background: <?= $iconInfo['bg'] ?>; border: 1.5px solid <?= $iconInfo['border'] ?>; color: <?= $iconInfo['color'] ?>;">
                    <i class="bi <?= $iconInfo['icon'] ?> fs-4"></i>
                </div>
                <div class="text-truncate">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($b['nama_berkas']) ?>">
                        <?= htmlspecialchars($b['nama_berkas']) ?>
                    </h6>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge rounded-pill fw-bold" style="background: <?= $iconInfo['bg'] ?>; color: <?= $iconInfo['color'] ?>; font-size: 0.65rem;">
                            <?= $iconInfo['label'] ?>
                        </span>
                        <?php if ($isPublic): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.65rem;">
                                <i class="bi bi-globe me-1"></i>Publik
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 text-truncate" style="font-size: 0.65rem;" title="<?= htmlspecialchars($instansiName) ?>">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($instansiName) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- List Actions -->
            <div class="d-flex align-items-center gap-1 ms-auto">
                <?php if ($b['path_file']): ?>
                <a href="<?= API_URL ?>/berkas/<?= $b['id'] ?>/view" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-medium shadow-sm d-flex align-items-center gap-1" style="font-size: 0.75rem; background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none;">
                    <i class="bi bi-eye"></i> Lihat
                </a>
                <a href="<?= API_URL ?>/berkas/<?= $b['id'] ?>/view?dl=1" class="btn-action-circle text-success" title="Download File">
                    <i class="bi bi-download"></i>
                </a>
                <?php endif; ?>
                
                <?php if ($canManage): ?>
                <button type="button" class="btn-action-circle text-primary" onclick="openEditModal(<?= $b['id'] ?>, '<?= addslashes($b['nama_berkas']) ?>', <?= $b['is_public'] ?? 0 ?>, '<?= htmlspecialchars((string)$b['kode']) ?>')" title="Edit Berkas">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn-action-circle text-danger" onclick="hapusBerkas(<?= $b['id'] ?>, '<?= addslashes($b['nama_berkas']) ?>')" title="Hapus Berkas">
                    <i class="bi bi-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header bg-white border-bottom px-4 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 38px; height: 38px;">
                    <i class="bi bi-cloud-upload-fill fs-5"></i>
                </div>
                <h5 class="modal-title fw-bold text-dark mb-0">Upload Berkas Baru</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <div id="uploadErrorAlert" class="alert alert-danger d-none py-2 px-3 small rounded-3 mb-3"></div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Nama Berkas / Dokumen <span class="text-danger">*</span></label>
                <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-tag text-secondary"></i></span>
                    <input type="text" id="up_nama" class="form-control border-0 shadow-none bg-white py-2" placeholder="Cth: Fotokopi Paspor, Formulir ITAS...">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">File Dokumen <span class="text-danger">*</span></label>
                <div id="dropZoneUp" class="border rounded-3 mb-2 d-flex flex-column align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative p-3" style="min-height: 120px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s; cursor: pointer;" ondragover="handleDragOver(event, 'dropZoneUp', 'dragOverlayUp')" ondragleave="handleDragLeave(event, 'dropZoneUp', 'dragOverlayUp')" ondrop="handleDrop(event, 'dropZoneUp', 'dragOverlayUp', 'up_file')" onclick="document.getElementById('up_file').click()">
                    <i id="up_icon" class="bi bi-cloud-arrow-up text-secondary" style="font-size: 2.2rem;"></i>
                    <span id="up_text" class="text-muted small mt-2 text-center">Klik atau seret file ke sini</span>
                    <div id="dragOverlayUp" class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                        <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan File</span>
                    </div>
                </div>
                <input type="file" id="up_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" onchange="updateFileName('up_file', 'up_text', 'up_icon')">
                <div class="form-text" style="font-size: .75rem;"><i class="bi bi-info-circle me-1"></i>Format yang didukung: PDF, JPG, PNG, DOC, DOCX, XLS (Maks. 10MB)</div>
            </div>
            
            <div class="p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-shield-lock me-1 text-primary"></i>Hak Akses Dokumen</label>
                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-4">
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
                    <label class="form-label text-muted small fw-bold mb-1">Upload ke Instansi (Super Admin):</label>
                    <select class="form-select border shadow-sm py-2" id="up_target_kode">
                        <?php foreach ($semuaInstansi as $si): ?>
                            <option value="<?= htmlspecialchars((string)$si['kode']) ?>"><?= htmlspecialchars((string)$si['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="button" id="btnUploadSubmit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border: none;" onclick="uploadBerkas()">
                <i class="bi bi-cloud-upload-fill me-1"></i> <span>Upload Sekarang</span>
            </button>
        </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header bg-white border-bottom px-4 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 38px; height: 38px;">
                    <i class="bi bi-pencil-square fs-5"></i>
                </div>
                <h5 class="modal-title fw-bold text-dark mb-0">Edit Informasi Berkas</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <div id="editErrorAlert" class="alert alert-danger d-none py-2 px-3 small rounded-3 mb-3"></div>
            <input type="hidden" id="edit_id">
            
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Nama Berkas / Dokumen <span class="text-danger">*</span></label>
                <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-tag text-secondary"></i></span>
                    <input type="text" id="edit_nama" class="form-control border-0 shadow-none bg-white py-2">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Ganti File Dokumen (Opsional)</label>
                <div id="dropZoneEdit" class="border rounded-3 mb-2 d-flex flex-column align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative p-3" style="min-height: 120px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s; cursor: pointer;" ondragover="handleDragOver(event, 'dropZoneEdit', 'dragOverlayEdit')" ondragleave="handleDragLeave(event, 'dropZoneEdit', 'dragOverlayEdit')" ondrop="handleDrop(event, 'dropZoneEdit', 'dragOverlayEdit', 'edit_file')" onclick="document.getElementById('edit_file').click()">
                    <i id="edit_icon" class="bi bi-cloud-arrow-up text-secondary" style="font-size: 2.2rem;"></i>
                    <span id="edit_text" class="text-muted small mt-2 text-center">Klik atau seret file baru ke sini</span>
                    <div id="dragOverlayEdit" class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                        <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan File</span>
                    </div>
                </div>
                <input type="file" id="edit_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" onchange="updateFileName('edit_file', 'edit_text', 'edit_icon')">
                <div class="form-text" style="font-size: .75rem;"><i class="bi bi-info-circle me-1"></i>Biarkan kosong jika tidak ingin mengganti file yang sudah ada.</div>
            </div>
            
            <div class="p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-shield-lock me-1 text-primary"></i>Hak Akses Dokumen</label>
                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-4">
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
        <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Batal</button>
            <button type="button" id="btnEditSubmit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none;" onclick="simpanEditBerkas()">
                <i class="bi bi-save me-1"></i> <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toastMsg" class="toast align-items-center text-white border-0 bg-success shadow-lg rounded-3" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
// View Mode Handler (Grid / List)
function switchBerkasView(mode) {
    const gridEl = document.getElementById('berkasGridView');
    const listEl = document.getElementById('berkasListView');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnList = document.getElementById('btnViewList');

    if (mode === 'list') {
        gridEl?.classList.add('d-none');
        listEl?.classList.remove('d-none');
        btnGrid?.classList.replace('btn-primary', 'btn-light');
        btnGrid?.classList.add('text-secondary');
        btnList?.classList.replace('btn-light', 'btn-primary');
        btnList?.classList.remove('text-secondary');
        localStorage.setItem('pemberkasan_view_mode', 'list');
    } else {
        listEl?.classList.add('d-none');
        gridEl?.classList.remove('d-none');
        btnList?.classList.replace('btn-primary', 'btn-light');
        btnList?.classList.add('text-secondary');
        btnGrid?.classList.replace('btn-light', 'btn-primary');
        btnGrid?.classList.remove('text-secondary');
        localStorage.setItem('pemberkasan_view_mode', 'grid');
    }
}

// Restore saved view mode
document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('pemberkasan_view_mode');
    if (savedMode === 'list') {
        switchBerkasView('list');
    }
});

// Live Search Filtering
function handleSearchLiveFilter(input) {
    const clearBtn = document.getElementById('btnClearSearchBerkas');
    const query = input.value.toLowerCase().trim();
    if (clearBtn) {
        clearBtn.style.display = query.length > 0 ? 'inline-flex' : 'none';
    }

    const items = document.querySelectorAll('.berkas-item-card');
    let visibleCount = 0;

    items.forEach(card => {
        const title = card.getAttribute('data-title') || '';
        const instansi = card.getAttribute('data-instansi') || '';
        const ext = card.getAttribute('data-ext') || '';
        
        if (!query || title.includes(query) || instansi.includes(query) || ext.includes(query)) {
            card.classList.remove('d-none');
            visibleCount++;
        } else {
            card.classList.add('d-none');
        }
    });

    const emptyContainer = document.getElementById('emptyStateContainer');
    const gridView = document.getElementById('berkasGridView');
    const listView = document.getElementById('berkasListView');
    const labelLive = document.getElementById('labelLiveCount');

    if (labelLive) {
        labelLive.innerText = `Menampilkan ${visibleCount} berkas`;
    }

    if (visibleCount === 0 && items.length > 0) {
        emptyContainer?.classList.remove('d-none');
    } else {
        emptyContainer?.classList.add('d-none');
    }
}

function clearBerkasSearch() {
    const input = document.getElementById('inputSearchBerkas');
    const clearBtn = document.getElementById('btnClearSearchBerkas');
    if (input) {
        input.value = '';
        input.focus();
    }
    if (clearBtn) clearBtn.style.display = 'none';
    handleSearchLiveFilter({ value: '' });

    // If there was a server GET search parameter, redirect to clear it
    if (window.location.search.includes('q=')) {
        window.location.href = '<?= API_URL ?>/pemberkasan';
    }
}

function handleFormSearchSubmit(e) {
    // Form can submit normally for full server-side search
    return true;
}

function showToast(msg, ok=true) {
    const t = document.getElementById('toastMsg');
    t.className = 'toast align-items-center text-white border-0 shadow-lg rounded-3 '+(ok?'bg-success':'bg-danger');
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
        icon.style.fontSize = '2.2rem';
    } else {
        text.textContent = 'Klik atau seret file ke sini';
        text.classList.add('text-muted');
        text.classList.remove('text-primary', 'fw-bold');
        icon.className = 'bi bi-cloud-arrow-up text-secondary';
        icon.style.fontSize = '2.2rem';
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

function openUploadModal() {
    const errAlert = document.getElementById('uploadErrorAlert');
    if (errAlert) {
        errAlert.classList.add('d-none');
        errAlert.textContent = '';
    }
    const upNama = document.getElementById('up_nama');
    if (upNama) upNama.value = '';
    const upFile = document.getElementById('up_file');
    if (upFile) upFile.value = '';
    updateFileName('up_file', 'up_text', 'up_icon');
    
    const public0 = document.getElementById('up_public_0');
    if (public0) public0.checked = true;
    toggleInstansiSelect();
    
    const modalEl = document.getElementById('uploadModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function openEditModal(id, nama, isPublic, kode) {
    const errAlert = document.getElementById('editErrorAlert');
    if (errAlert) {
        errAlert.classList.add('d-none');
        errAlert.textContent = '';
    }
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
    const modalEl = document.getElementById('editModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function uploadBerkas() {
    const errAlert = document.getElementById('uploadErrorAlert');
    if (errAlert) {
        errAlert.classList.add('d-none');
        errAlert.textContent = '';
    }

    const namaInput = document.getElementById('up_nama');
    const nama = namaInput ? namaInput.value.trim() : '';
    const fileInput = document.getElementById('up_file');
    const file = fileInput && fileInput.files ? fileInput.files[0] : null;
    const isPublicEl = document.querySelector('input[name="up_is_public"]:checked');
    const isPublic = isPublicEl ? isPublicEl.value : '0';
    const targetKode = document.getElementById('up_target_kode');
    const btnSubmit = document.getElementById('btnUploadSubmit');

    if (!nama) {
        if (errAlert) {
            errAlert.textContent = 'Nama berkas wajib diisi!';
            errAlert.classList.remove('d-none');
        }
        namaInput?.focus();
        showToast('Nama berkas wajib diisi', false);
        return;
    }

    if (!file) {
        if (errAlert) {
            errAlert.textContent = 'Silakan pilih file dokumen yang ingin diunggah!';
            errAlert.classList.remove('d-none');
        }
        showToast('Silakan pilih file dokumen terlebih dahulu!', false);
        return;
    }

    // Cek ukuran file maks 10MB
    if (file.size > 10 * 1024 * 1024) {
        if (errAlert) {
            errAlert.textContent = 'Ukuran file melebihi batas maksimal (10MB)!';
            errAlert.classList.remove('d-none');
        }
        showToast('Ukuran file melebihi batas maksimal (10MB)', false);
        return;
    }

    const form = new FormData();
    form.append('nama_berkas', nama);
    form.append('is_public', isPublic);
    if (targetKode && isPublic === '0') form.append('target_kode', targetKode.value);
    form.append('berkas_file', file);

    // Disable button & tampilkan spinner
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengunggah...';
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/berkas/upload', {
        method: 'POST',
        body: form,
        headers: csrf ? { 'X-CSRF-Token': csrf } : {}
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (!r.ok || !data) {
            throw new Error((data && data.message) ? data.message : `Gagal mengunggah berkas (Status: ${r.status})`);
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Berkas berhasil diunggah', true);
            const modalEl = document.getElementById('uploadModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => location.reload(), 700);
        } else {
            throw new Error(data.message || 'Gagal mengunggah berkas');
        }
    })
    .catch(err => {
        if (errAlert) {
            errAlert.textContent = err.message;
            errAlert.classList.remove('d-none');
        }
        showToast(err.message, false);
    })
    .finally(() => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-cloud-upload-fill me-1"></i> <span>Upload Sekarang</span>';
        }
    });
}

function simpanEditBerkas() {
    const errAlert = document.getElementById('editErrorAlert');
    if (errAlert) {
        errAlert.classList.add('d-none');
        errAlert.textContent = '';
    }

    const id = document.getElementById('edit_id').value;
    const namaInput = document.getElementById('edit_nama');
    const nama = namaInput ? namaInput.value.trim() : '';
    const fileInput = document.getElementById('edit_file');
    const file = fileInput && fileInput.files ? fileInput.files[0] : null;
    const isPublicEl = document.querySelector('input[name="edit_is_public"]:checked');
    const isPublic = isPublicEl ? isPublicEl.value : '0';
    const targetKode = document.getElementById('edit_target_kode');
    const btnSubmit = document.getElementById('btnEditSubmit');

    if (!nama) {
        if (errAlert) {
            errAlert.textContent = 'Nama berkas wajib diisi!';
            errAlert.classList.remove('d-none');
        }
        namaInput?.focus();
        showToast('Nama berkas wajib diisi', false);
        return;
    }

    const form = new FormData();
    form.append('nama_berkas', nama);
    form.append('is_public', isPublic);
    if (targetKode && isPublic === '0') form.append('target_kode', targetKode.value);
    if (file) form.append('berkas_file', file);

    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/berkas/' + id + '/update', {
        method: 'POST',
        body: form,
        headers: csrf ? { 'X-CSRF-Token': csrf } : {}
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (!r.ok || !data) {
            throw new Error((data && data.message) ? data.message : `Gagal memperbarui berkas (Status: ${r.status})`);
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Berkas berhasil diperbarui', true);
            const modalEl = document.getElementById('editModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => location.reload(), 700);
        } else {
            throw new Error(data.message || 'Gagal memperbarui berkas');
        }
    })
    .catch(err => {
        if (errAlert) {
            errAlert.textContent = err.message;
            errAlert.classList.remove('d-none');
        }
        showToast(err.message, false);
    })
    .finally(() => {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save me-1"></i> <span>Simpan Perubahan</span>';
        }
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
        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 shadow-lg' }
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/berkas/' + id + '/delete', {method:'POST', headers: csrf ? { 'X-CSRF-Token': csrf } : {}})
                .then(r => r.json())
                .then(data => {
                    showToast(data.message, data.success);
                    if (data.success) setTimeout(() => location.reload(), 700);
                })
                .catch(err => {
                    showToast('Gagal menghapus berkas: ' + err.message, false);
                });
        }
    });
}
</script>
