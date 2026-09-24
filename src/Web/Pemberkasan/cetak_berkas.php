<?php
declare(strict_types=1);
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $santris
 * @var array $berkasMap
 * @var string $search
 * @var int $total
 */
$this->setTitle('Cetak Berkas Individu | Sistem Informasi');

// Extract unique document types and group by category
$groupedBerkas = [];
$availableYears = [];
foreach ($berkasMap as $kds => $berkasList) {
    foreach ($berkasList as $b) {
        $kat = $b['kategori'] ?? 'Dokumen Umum';
        $namaUnik = $b['nama_unik'] ?? $b['nama_berkas'];
        $groupedBerkas[$kat][$namaUnik] = $b['nama_berkas'];
        
        if (!empty($b['tahun'])) {
            $availableYears[$b['tahun']] = true;
        }
    }
}
ksort($groupedBerkas);

$availableYears = array_keys($availableYears);
rsort($availableYears); // Descending order: 2026, 2025...
?>

<style>
/* Wizard Styles */
.step-container { transition: opacity 0.25s ease, transform 0.25s ease; }
.step-hidden { display: none !important; opacity: 0; transform: translateY(8px); }
.step-active { display: block !important; opacity: 1; transform: translateY(0); }

/* Document Cards */
.doc-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}
.doc-card:hover {
    border-color: #93c5fd;
    background: #f8faff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.08);
}
.doc-card.selected {
    border-color: #0d6efd;
    background: #eff6ff;
    box-shadow: 0 4px 14px rgba(13, 110, 253, 0.12);
}
.doc-card .form-check-input {
    width: 1.2em;
    height: 1.2em;
    cursor: pointer;
}
.doc-card.selected .form-check-input {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
.doc-title-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.25;
}

/* Category Box & Accordion */
.category-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 16px;
    transition: all 0.2s ease;
}
.category-section:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
.category-header-clickable {
    cursor: pointer;
    user-select: none;
    padding: 4px 0;
    border-radius: 8px;
    transition: background-color 0.15s ease;
}
.category-header-clickable:hover {
    opacity: 0.9;
}
.toggle-cat-chevron {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.category-section.collapsed .toggle-cat-chevron {
    transform: rotate(-90deg);
}
.category-section.collapsed .doc-items-row {
    display: none !important;
}
.category-section.collapsed {
    padding-bottom: 14px;
}

/* Badges */
.badge-doc {
    font-size: 0.75rem;
    padding: 0.4em 0.7em;
    border-radius: 6px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    line-height: 1.2;
}
.badge-doc.has-doc { 
    background-color: #ecfdf5; 
    color: #065f46; 
    border: 1px solid #a7f3d0; 
}
.badge-doc.no-doc { 
    background-color: #fef2f2; 
    color: #991b1b; 
    border: 1px solid #fecaca; 
    text-decoration: line-through; 
    opacity: 0.7; 
}

/* Action Toolbar Badges & Controls */
.action-pill-badge {
    font-size: 0.78rem;
    padding: 0.35rem 0.65rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 50rem;
    line-height: 1.2;
}
.action-pill-badge .counter-num {
    font-size: 0.85rem;
    font-weight: 800;
}

/* Custom Checkbox Size */
.santri-checkbox, #selectAllSantri {
    width: 1.25em;
    height: 1.25em;
    cursor: pointer;
}
.santri-row {
    user-select: none;
    transition: background-color 0.15s ease;
}
.santri-row:hover {
    background-color: #f8fafc;
}
.santri-row.selected-row {
    background-color: #eff6ff !important;
}

/* Sortable List Item */
.sortable-doc-item {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    background: #ffffff;
    cursor: grab;
    transition: all 0.2s ease;
}
.sortable-doc-item:hover {
    border-color: #93c5fd;
    background: #f8faff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

/* Unified 360 Search Box */
.search-box-unified {
    border: 1.5px solid #cbd5e1;
    border-radius: 50rem;
    background: #ffffff;
    transition: all 0.2s ease;
    overflow: hidden;
}
.search-box-unified:focus-within {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}
.search-box-unified input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    outline: none !important;
}
.search-box-unified .input-group-text {
    border: none !important;
    background: transparent !important;
}

/* Mobile Responsive Optimization */
@media (max-width: 767.98px) {
    .header-responsive-step2 {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .header-responsive-step2 > div {
        width: 100% !important;
        justify-content: space-between !important;
    }
    
    .doc-card {
        padding: 10px !important;
    }
    .doc-card .form-check-input {
        width: 1.1em !important;
        height: 1.1em !important;
    }
    .category-section {
        padding: 12px 14px !important;
        border-radius: 12px !important;
    }
    .badge-doc {
        font-size: 0.68rem !important;
        padding: 0.3em 0.5em !important;
    }
    .header-responsive-step1 {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .header-responsive-step1 > div {
        width: 100% !important;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-4 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff;">
            <i class="bi bi-printer-fill fs-5"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Cetak Berkas Individu</h4>
            <div class="text-muted small fw-medium mt-1">Pilih jenis berkas dan surat santri secara terpadu, atur urutan, dan gabungkan ke PDF</div>
        </div>
    </div>
</div>

<!-- STEP 1: PILIH JENIS BERKAS -->
<div id="step1" class="step-container step-active">
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3 sticky-top header-responsive-step1" style="z-index: 10;">
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-between w-100 w-lg-auto">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold" style="font-size: .85rem;">Langkah 1</span>
                    <h6 class="mb-0 fw-bold text-dark">Pilih Jenis Dokumen</h6>
                </div>
                <div class="d-flex gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold rounded-pill" id="btnSelectAllDocs" style="font-size: 0.78rem;">
                        <i class="bi bi-check-all me-1"></i>Pilih Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fw-semibold rounded-pill" id="btnUnselectAllDocs" style="font-size: 0.78rem;">
                        <i class="bi bi-x me-1"></i>Batal Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2 fw-semibold rounded-pill" id="btnToggleAllCategories" style="font-size: 0.78rem;" title="Buka / Tutup Semua Kategori">
                        <i class="bi bi-arrows-collapse me-1" id="toggleAllCatIcon"></i><span id="toggleAllCatText">Tutup Semua</span>
                    </button>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-lg-auto justify-content-end">
                <!-- Search Box Dokumen 360 Unified -->
                <div class="input-group input-group-sm search-box-unified flex-grow-1 flex-lg-grow-0" style="min-width: 180px; max-width: 260px;">
                    <span class="input-group-text ps-3 pe-1"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" id="searchDocInput" class="form-control shadow-none py-1 pe-3" placeholder="Cari dokumen..." style="font-size: .82rem;">
                </div>

                <?php if (!empty($availableYears)): 
                    $currentYear = date('Y');
                ?>
                <select id="filterTahun" class="form-select form-select-sm w-auto rounded-pill fw-semibold border-secondary border-opacity-25">
                    <option value="all">Semua Tahun</option>
                    <?php foreach ($availableYears as $y): ?>
                    <option value="<?= htmlspecialchars((string)$y) ?>" <?= $y == $currentYear ? 'selected' : '' ?>>Tahun <?= htmlspecialchars((string)$y) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <select id="filterTahun" class="d-none"><option value="all">all</option></select>
                <?php endif; ?>

                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" id="btnTopGoToStep2" disabled>
                    Lanjut Pilih Santri (<span id="topSelectedDocsCount">0</span>) <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-3 p-md-4 bg-light bg-opacity-50">
            <div class="alert alert-primary border-0 bg-primary bg-opacity-10 text-primary d-flex align-items-center gap-2 py-2 px-3 mb-4 rounded-3 small">
                <i class="bi bi-info-circle-fill fs-6 flex-shrink-0"></i>
                <div>Klik judul kategori untuk <strong>buka/tutup</strong> list berkas. Pilih dokumen yang ingin dicetak, lalu klik <strong>Lanjut Pilih Santri</strong>.</div>
            </div>
            
            <div id="categoryContainer">
            <?php 
            $catIndex = 0;
            foreach ($groupedBerkas as $kategori => $docs): 
                $catIndex++;
                $isSuratCategory = !in_array($kategori, ['Dokumen Umum', 'Dokumen Berkas', 'Dokumen ITAS & Paspor', 'Dokumen Instansi', 'Dokumen Instansi (Global)']);
            ?>
            <div class="category-section" data-category="<?= htmlspecialchars($kategori) ?>" id="catSection_<?= $catIndex ?>">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom pb-2 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 category-header-clickable" data-cat-idx="<?= $catIndex ?>" title="Klik untuk Buka/Tutup Kategori">
                        <i class="bi bi-chevron-down text-primary fw-bold toggle-cat-chevron me-1" style="font-size: .85rem;"></i>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: <?= $isSuratCategory ? '#e0e7ff; color: #4338ca;' : '#e2e8f0; color: #475569;' ?>">
                            <i class="bi <?= $isSuratCategory ? 'bi-envelope-paper-fill' : 'bi-folder-fill' ?>" style="font-size: .8rem;"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0 fs-6"><?= htmlspecialchars($kategori) ?></h6>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill category-count-badge ms-1" style="font-size: .7rem;">0/<?= count($docs) ?> dipilih</span>
                    </div>
                    <div class="d-flex gap-1 ms-auto">
                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill btn-toggle-cat" data-cat-idx="<?= $catIndex ?>" style="font-size: .75rem;">
                            <i class="bi bi-check-all me-1"></i>Pilih Kategori Ini
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill btn-uncheck-cat" data-cat-idx="<?= $catIndex ?>" style="font-size: .75rem;">
                            <i class="bi bi-x"></i>Batal
                        </button>
                    </div>
                </div>

                <div class="row g-2 g-md-3 doc-items-row" id="catRow_<?= $catIndex ?>">
                    <?php 
                    ksort($docs);
                    foreach ($docs as $namaUnik => $namaDisplay): 
                        $docIcon = 'bi-file-earmark-text';
                        $iconColor = '#0d6efd';
                        if (str_contains(strtolower($namaDisplay), 'paspor')) { $docIcon = 'bi-journal-bookmark-fill'; $iconColor = '#854d0e'; }
                        elseif (str_contains(strtolower($namaDisplay), 'itas')) { $docIcon = 'bi-card-heading'; $iconColor = '#0891b2'; }
                        elseif (str_contains(strtolower($namaDisplay), 'foto')) { $docIcon = 'bi-person-badge-fill'; $iconColor = '#059669'; }
                        elseif (str_contains(strtolower($namaDisplay), 'ic') || str_contains(strtolower($namaDisplay), 'ktp')) { $docIcon = 'bi-person-vcard-fill'; $iconColor = '#4f46e5'; }
                        elseif (str_contains(strtolower($namaDisplay), 'ijazah') || str_contains(strtolower($namaDisplay), 'rapor')) { $docIcon = 'bi-mortarboard-fill'; $iconColor = '#d97706'; }
                        elseif (str_contains(strtolower($namaDisplay), 'keterangan')) { $docIcon = 'bi-file-earmark-check-fill'; $iconColor = '#16a34a'; }
                        elseif (str_contains(strtolower($namaDisplay), 'permohonan')) { $docIcon = 'bi-file-earmark-text-fill'; $iconColor = '#2563eb'; }
                        elseif (str_contains(strtolower($namaDisplay), 'jaminan')) { $docIcon = 'bi-shield-check'; $iconColor = '#ea580c'; }
                        elseif (str_contains(strtolower($namaDisplay), 'tugas')) { $docIcon = 'bi-briefcase-fill'; $iconColor = '#9333ea'; }
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 doc-col-item" data-doc-name="<?= strtolower(htmlspecialchars($namaDisplay)) ?>" data-doc-cat="<?= strtolower(htmlspecialchars($kategori)) ?>">
                        <label class="doc-card w-100 p-2 p-md-3 h-100 d-flex align-items-start gap-2 gap-md-3 m-0" data-cat-idx="<?= $catIndex ?>">
                            <input class="form-check-input mt-1 doc-type-checkbox flex-shrink-0" type="checkbox" value="<?= htmlspecialchars($namaUnik) ?>">
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-start gap-1 gap-md-2 mb-1">
                                    <i class="bi <?= $docIcon ?> flex-shrink-0 mt-1" style="color: <?= $iconColor ?>; font-size: 0.95rem;"></i>
                                    <span class="fw-bold text-dark small doc-title-clamp"><?= htmlspecialchars($namaDisplay) ?></span>
                                </div>
                                <?php if ($isSuratCategory): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: .62rem;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Surat Generator
                                </span>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>

            <?php if (empty($groupedBerkas)): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-folder-x fs-1 text-light mb-2"></i>
                <p>Belum ada data dokumen yang terunggah.</p>
            </div>
            <?php endif; ?>

            <div class="mt-4 pt-3 border-top text-end">
                <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm w-100 w-md-auto" id="btnGoToStep2" disabled>
                    Lanjut Pilih Santri <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- STEP 2: PILIH TARGET SANTRI -->
<form method="POST" action="<?= API_URL ?>/api/pemberkasan/merge" target="_blank" id="mergeForm">
<input type="hidden" name="_csrf" value="<?= $csrf ?? '' ?>">
<div id="step2" class="step-container step-hidden">
    
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 sticky-top header-responsive-step2" style="z-index: 10;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold btn-back-step1">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </button>
                <div class="vr mx-1 opacity-25 d-none d-sm-block"></div>
                <span class="badge bg-primary rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: .8rem;">Langkah 2</span>
                <h6 class="mb-0 fw-bold text-dark">Pilih Target Santri & Atur Urutan Berkas</h6>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <!-- Badges Info -->
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-people-fill me-1"></i><span class="count-santri-text">0</span> Santri
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-file-earmark-check-fill me-1"></i><span class="count-file-text">0</span> File
                    </span>
                </div>

                <!-- Mode Merge -->
                <select name="merge_mode" class="form-select form-select-sm rounded-pill fw-semibold border-secondary border-opacity-25 merge-mode-sync" style="width: auto; font-size: 0.8rem;">
                    <option value="sekaligus">Gabung Semua (1 PDF Utuh)</option>
                    <option value="individual">Pisah Per Santri (.zip)</option>
                    <option value="save_storage">Simpan Langsung ke Server</option>
                </select>

                <!-- Action Buttons -->
                <button type="submit" name="action" value="print" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold btn-action-print" disabled style="font-size: 0.8rem;">
                    <i class="bi bi-printer-fill me-1"></i> Print
                </button>
                <button type="submit" name="action" value="merge" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold text-white shadow-sm btn-action-merge" disabled style="font-size: 0.8rem;">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Merge PDF
                </button>
            </div>
        </div>

        <div class="card-body p-3 bg-light">
            <!-- Atur Urutan Dokumen -->
            <div class="mb-3 p-3 bg-white rounded-3 shadow-sm border">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-sort-down text-primary me-2"></i>Urutan Penggabungan Dokumen dalam PDF</h6>
                    <span class="badge bg-light text-muted border small"><i class="bi bi-grip-vertical me-1"></i>Geser (Drag & Drop) untuk mengatur urutan</span>
                </div>
                <div class="text-muted small mb-3">Dokumen akan digabungkan berurutan dari nomor 1 ke bawah untuk setiap santri yang dipilih.</div>
                <div id="sortableList" class="d-flex flex-wrap gap-2">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Toolbar Filter Santri Cerdas -->
            <div class="p-3 bg-white rounded-3 shadow-sm border mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm search-box-unified">
                            <span class="input-group-text ps-3"><i class="bi bi-search text-secondary"></i></span>
                            <input type="text" id="globalSearchSantri" class="form-control py-2 pe-3" placeholder="Cari nama santri, paspor, negara..." style="font-size: .85rem;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterCompleteness" class="form-select form-select-sm rounded-pill border fw-semibold">
                            <option value="all">Semua Status Kelengkapan</option>
                            <option value="complete">Hanya Berkas Lengkap (100% Ada)</option>
                            <option value="partial">Ada Sebagian Berkas</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-md-end d-flex gap-1 justify-content-md-end flex-wrap">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold flex-grow-1 flex-md-grow-0" id="btnSelectCompleteOnly">
                            <i class="bi bi-check-circle-fill me-1"></i>Pilih Yang Lengkap Saja
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-grow-1 flex-md-grow-0" id="btnUnselectAllSantri">
                            Batal Pilih
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;" id="santriTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="ps-4 text-center" style="width: 50px;">
                            <input class="form-check-input" type="checkbox" id="selectAllSantri">
                        </th>
                        <th>Santri</th>
                        <th>Ketersediaan Dokumen Terpilih</th>
                        <th>Kelas</th>
                        <th>Negara</th>
                        <th>Exp ITAS</th>
                        <th>Exp Paspor</th>
                    </tr>
                    <tr class="table-secondary column-filters">
                        <th></th>
                        <th>
                            <div class="input-group input-group-sm search-box-unified">
                                <input type="text" class="form-control col-search px-2 py-1" data-col="1" placeholder="Cari Santri...">
                            </div>
                        </th>
                        <th></th>
                        <th>
                            <div class="input-group input-group-sm search-box-unified">
                                <input type="text" class="form-control col-search px-2 py-1" data-col="3" placeholder="Cari Kelas...">
                            </div>
                        </th>
                        <th>
                            <div class="input-group input-group-sm search-box-unified">
                                <input type="text" class="form-control col-search px-2 py-1" data-col="4" placeholder="Cari Negara...">
                            </div>
                        </th>
                        <th>
                            <div class="input-group input-group-sm search-box-unified">
                                <input type="text" class="form-control col-search px-2 py-1" data-col="5" placeholder="Cari Exp ITAS...">
                            </div>
                        </th>
                        <th>
                            <div class="input-group input-group-sm search-box-unified">
                                <input type="text" class="form-control col-search px-2 py-1" data-col="6" placeholder="Cari...">
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($santris)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada data santri ditemukan.</td></tr>
                    <?php else: ?>
                    <?php foreach ($santris as $s): 
                        $kds = $s['kds'];
                        $berkas = $berkasMap[$kds] ?? [];
                        
                        $berkasSimple = array_map(function($b) {
                            return [
                                'id' => $b['id'], 
                                'nama' => $b['nama_unik'] ?? $b['nama_berkas'],
                                'tahun' => $b['tahun'] ?? null
                            ];
                        }, $berkas);
                        
                        $today = new DateTime();
                        $today->setTime(0, 0, 0);
                        
                        $expPStr = $s['exp_paspor'] ?? '';
                        $expIStr = $s['exp_itas'] ?? '';
                        
                        $expP  = !empty($expPStr) && $expPStr !== '0000-00-00' ? new DateTime($expPStr) : null;
                        if ($expP) $expP->setTime(0, 0, 0);
                        $expI  = !empty($expIStr) && $expIStr !== '0000-00-00' ? new DateTime($expIStr) : null;
                        if ($expI) $expI->setTime(0, 0, 0);
                        
                        $diffDaysP = $expP ? (int) $today->diff($expP)->format('%r%a') : null;
                        $diffDaysI = $expI ? (int) $today->diff($expI)->format('%r%a') : null;
                        
                        $isExpiredP = $expP && $diffDaysP <= 0;
                        $isUrgentP = $isExpiredP || ($diffDaysP !== null && $diffDaysP <= 540);
                        
                        $isExpiredI = $expI && $diffDaysI <= 0;
                        $isUrgentI = $isExpiredI || ($diffDaysI !== null && $diffDaysI <= 90);
                        
                        $fmtP = $expP ? $expP->format('d-M-Y') : '-';
                        $fmtI = $expI ? $expI->format('d-M-Y') : '-';
                    ?>
                    <tr class="border-bottom santri-row <?= ($isExpiredP || $isExpiredI) ? 'bg-danger bg-opacity-25' : (($isUrgentP || $isUrgentI) ? 'bg-danger bg-opacity-10' : '') ?>"
                        data-kds="<?= $kds ?>"
                        data-nama="<?= strtolower(htmlspecialchars((string)$s['nama'])) ?>" 
                        data-paspor="<?= strtolower(htmlspecialchars((string)($s['no_paspor'] ?? ''))) ?>"
                        data-negara="<?= strtolower(htmlspecialchars((string)($s['negara'] ?? ''))) ?>"
                        data-berkas='<?= json_encode($berkasSimple, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                        <td class="ps-4 text-center">
                            <input class="form-check-input santri-checkbox" type="checkbox">
                        </td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars((string)$s['nama']) ?></td>
                        <td class="berkas-badges-container">
                            <!-- Badges will be injected here by JS -->
                        </td>
                        <td class="text-muted"><?= htmlspecialchars((string)($s['kelas'] ?? '-')) ?></td>
                        <td class="text-muted"><?= htmlspecialchars((string)($s['negara'] ?? '-')) ?></td>
                        <td class="<?= $isExpiredI ? 'text-danger fw-bold' : ($isUrgentI ? 'text-warning fw-bold' : 'text-muted') ?>">
                            <?= $fmtI ?>
                            <?php if ($isExpiredI): ?>
                                <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA</span>
                            <?php elseif ($isUrgentI): ?>
                                <?php $textI = $diffDaysI > 90 ? floor($diffDaysI / 30) . ' bln lagi' : $diffDaysI . ' hari lagi'; ?>
                                <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="<?= $isExpiredP ? 'text-danger fw-bold' : ($isUrgentP ? 'text-warning fw-bold' : 'text-muted') ?>">
                            <?= $fmtP ?>
                            <?php if ($isExpiredP): ?>
                                <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA</span>
                            <?php elseif ($isUrgentP): ?>
                                <?php $textP = $diffDaysP > 90 ? floor($diffDaysP / 30) . ' bln lagi' : $diffDaysP . ' hari lagi'; ?>
                                <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textP ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Card Footer Action Toolbar (Bottom) -->
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold btn-back-step1">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </button>
                <div class="vr mx-1 opacity-25 d-none d-sm-block"></div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-people-fill me-1"></i><span class="count-santri-text">0</span> Santri
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-file-earmark-check-fill me-1"></i><span class="count-file-text">0</span> File
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <select name="merge_mode" class="form-select form-select-sm rounded-pill fw-semibold border-secondary border-opacity-25 merge-mode-sync" style="width: auto; font-size: 0.8rem;">
                    <option value="sekaligus">Gabung Semua (1 PDF Utuh)</option>
                    <option value="individual">Pisah Per Santri (.zip)</option>
                    <option value="save_storage">Simpan Langsung ke Server</option>
                </select>

                <button type="submit" name="action" value="print" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold btn-action-print" disabled style="font-size: 0.8rem;">
                    <i class="bi bi-printer-fill me-1"></i> Print
                </button>
                <button type="submit" name="action" value="merge" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold text-white shadow-sm btn-action-merge" disabled style="font-size: 0.8rem;">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Merge PDF
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Step 1 Elements ---
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const docCards = document.querySelectorAll('.doc-card');
    const docCheckboxes = document.querySelectorAll('.doc-type-checkbox');
    const btnGoToStep2 = document.getElementById('btnGoToStep2');
    const btnTopGoToStep2 = document.getElementById('btnTopGoToStep2');
    const btnSelectAllDocs = document.getElementById('btnSelectAllDocs');
    const btnUnselectAllDocs = document.getElementById('btnUnselectAllDocs');
    const topSelectedDocsCount = document.getElementById('topSelectedDocsCount');
    const searchDocInput = document.getElementById('searchDocInput');
    
    // --- Step 2 Elements ---
    const santriRows = document.querySelectorAll('.santri-row');
    const selectAllSantri = document.getElementById('selectAllSantri');
    const santriCheckboxes = document.querySelectorAll('.santri-checkbox');
    const globalSearchSantri = document.getElementById('globalSearchSantri');
    const filterCompleteness = document.getElementById('filterCompleteness');
    const btnSelectCompleteOnly = document.getElementById('btnSelectCompleteOnly');
    const btnUnselectAllSantri = document.getElementById('btnUnselectAllSantri');
    const mergeForm = document.getElementById('mergeForm');

    // Current selection state
    let selectedDocTypes = [];
    
    function updateDocSelection() {
        const checkedBoxes = document.querySelectorAll('.doc-type-checkbox:checked');
        const count = checkedBoxes.length;
        
        btnGoToStep2.disabled = (count === 0);
        if (btnTopGoToStep2) btnTopGoToStep2.disabled = (count === 0);
        if (topSelectedDocsCount) topSelectedDocsCount.textContent = count;

        // Update category count badges
        document.querySelectorAll('.category-section').forEach(catSec => {
            const totalInCat = catSec.querySelectorAll('.doc-type-checkbox').length;
            const checkedInCat = catSec.querySelectorAll('.doc-type-checkbox:checked').length;
            const badge = catSec.querySelector('.category-count-badge');
            if (badge) {
                badge.textContent = `${checkedInCat}/${totalInCat} dipilih`;
                if (checkedInCat > 0) {
                    badge.classList.remove('bg-secondary', 'bg-opacity-10', 'text-secondary');
                    badge.classList.add('bg-primary', 'text-white');
                } else {
                    badge.classList.remove('bg-primary', 'text-white');
                    badge.classList.add('bg-secondary', 'bg-opacity-10', 'text-secondary');
                }
            }
        });
    }

    // Step 1 Checkbox Click / Toggle Card
    docCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const card = this.closest('.doc-card');
            if (this.checked) card.classList.add('selected');
            else card.classList.remove('selected');
            updateDocSelection();
        });
    });

    // Category Accordion Toggle (Per-Category & Global)
    const btnToggleAllCategories = document.getElementById('btnToggleAllCategories');
    const toggleAllCatIcon = document.getElementById('toggleAllCatIcon');
    const toggleAllCatText = document.getElementById('toggleAllCatText');
    let allCategoriesCollapsed = false;

    // Single Category Accordion Click
    document.querySelectorAll('.category-header-clickable').forEach(header => {
        header.addEventListener('click', function(e) {
            const catSec = this.closest('.category-section');
            if (catSec) {
                catSec.classList.toggle('collapsed');
                checkAllCategoriesState();
            }
        });
    });

    // Global Toggle All Categories
    if (btnToggleAllCategories) {
        btnToggleAllCategories.addEventListener('click', function() {
            allCategoriesCollapsed = !allCategoriesCollapsed;
            document.querySelectorAll('.category-section').forEach(catSec => {
                if (allCategoriesCollapsed) {
                    catSec.classList.add('collapsed');
                } else {
                    catSec.classList.remove('collapsed');
                }
            });
            updateToggleAllCatButtonState(allCategoriesCollapsed);
        });
    }

    function checkAllCategoriesState() {
        const sections = document.querySelectorAll('.category-section');
        const collapsedSections = document.querySelectorAll('.category-section.collapsed');
        if (collapsedSections.length === sections.length) {
            allCategoriesCollapsed = true;
            updateToggleAllCatButtonState(true);
        } else if (collapsedSections.length === 0) {
            allCategoriesCollapsed = false;
            updateToggleAllCatButtonState(false);
        }
    }

    function updateToggleAllCatButtonState(isCollapsed) {
        if (!toggleAllCatText || !toggleAllCatIcon) return;
        if (isCollapsed) {
            toggleAllCatText.textContent = 'Buka Semua';
            toggleAllCatIcon.className = 'bi bi-arrows-expand me-1';
        } else {
            toggleAllCatText.textContent = 'Tutup Semua';
            toggleAllCatIcon.className = 'bi bi-arrows-collapse me-1';
        }
    }

    // Category Level Toggle Buttons (Check / Uncheck all in category)
    document.querySelectorAll('.btn-toggle-cat').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const catIdx = this.getAttribute('data-cat-idx');
            const catSec = this.closest('.category-section');
            catSec.querySelectorAll('.doc-type-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('.doc-card')?.classList.add('selected');
            });
            // If collapsed, open it so user sees selected items
            if (catSec.classList.contains('collapsed')) {
                catSec.classList.remove('collapsed');
                checkAllCategoriesState();
            }
            updateDocSelection();
        });
    });

    document.querySelectorAll('.btn-uncheck-cat').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const catSec = this.closest('.category-section');
            catSec.querySelectorAll('.doc-type-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('.doc-card')?.classList.remove('selected');
            });
            updateDocSelection();
        });
    });

    // Live Search Step 1 (Dokumen)
    if (searchDocInput) {
        searchDocInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.doc-col-item').forEach(col => {
                const name = col.getAttribute('data-doc-name') || '';
                const cat = col.getAttribute('data-doc-cat') || '';
                if (q === '' || name.includes(q) || cat.includes(q)) {
                    col.style.display = '';
                } else {
                    col.style.display = 'none';
                }
            });

            // Hide category if all items are hidden, or expand if search match found
            document.querySelectorAll('.category-section').forEach(catSec => {
                const visibleItems = catSec.querySelectorAll('.doc-col-item:not([style*="display: none"])');
                if (visibleItems.length > 0) {
                    catSec.style.display = '';
                    if (q !== '') {
                        // Automatically uncollapse when searching so results are immediately visible
                        catSec.classList.remove('collapsed');
                    }
                } else {
                    catSec.style.display = 'none';
                }
            });
            if (q !== '') checkAllCategoriesState();
        });
    }

    if (btnSelectAllDocs) {
        btnSelectAllDocs.addEventListener('click', function() {
            docCheckboxes.forEach(cb => {
                cb.checked = true;
                const card = cb.closest('.doc-card');
                if (card) card.classList.add('selected');
            });
            updateDocSelection();
        });
    }

    if (btnUnselectAllDocs) {
        btnUnselectAllDocs.addEventListener('click', function() {
            docCheckboxes.forEach(cb => {
                cb.checked = false;
                const card = cb.closest('.doc-card');
                if (card) card.classList.remove('selected');
            });
            updateDocSelection();
        });
    }

    // Navigation Step 1 -> Step 2
    function proceedToStep2() {
        selectedDocTypes = Array.from(document.querySelectorAll('.doc-type-checkbox:checked')).map(cb => cb.value);
        if (selectedDocTypes.length === 0) return;
        
        refreshSantriTable();
        
        // Populate Sortable List
        const sortableList = document.getElementById('sortableList');
        sortableList.innerHTML = '';
        selectedDocTypes.forEach((docType, index) => {
            const div = document.createElement('div');
            div.className = 'sortable-doc-item d-flex align-items-center gap-2 shadow-sm';
            div.draggable = true;
            div.innerHTML = `
                <span class="badge bg-primary rounded-circle order-number">${index + 1}</span>
                <i class="bi bi-grip-vertical text-muted fs-6"></i> 
                <span class="doc-name fw-semibold text-dark small">${docType}</span>
            `;
            
            div.addEventListener('dragstart', function(e) {
                this.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', docType);
            });
            div.addEventListener('dragend', function() {
                this.classList.remove('opacity-50');
                // Update selectedDocTypes order & re-number badges
                const items = sortableList.querySelectorAll('.sortable-doc-item');
                const newOrder = [];
                items.forEach((it, idx) => {
                    const badge = it.querySelector('.order-number');
                    if (badge) badge.textContent = idx + 1;
                    const docText = it.querySelector('.doc-name').textContent;
                    newOrder.push(docText);
                });
                selectedDocTypes = newOrder;
                refreshSantriTable();
            });
            sortableList.appendChild(div);
        });

        // Drag over handling for sorting
        sortableList.addEventListener('dragover', function(e) {
            e.preventDefault();
            const dragging = document.querySelector('.sortable-doc-item.opacity-50');
            if (!dragging) return;
            const siblings = [...sortableList.querySelectorAll('.sortable-doc-item:not(.opacity-50)')];
            let nextSibling = siblings.find(sibling => {
                const rect = sibling.getBoundingClientRect();
                return e.clientX <= rect.left + rect.width / 2;
            });
            if (nextSibling) sortableList.insertBefore(dragging, nextSibling);
            else sortableList.appendChild(dragging);
        });

        // Transition UI
        step1.classList.remove('step-active');
        step1.classList.add('step-hidden');
        setTimeout(() => {
            step2.classList.remove('step-hidden');
            step2.classList.add('step-active');
        }, 120);
        
        globalSearchSantri.value = '';
        selectAllSantri.checked = false;
        santriCheckboxes.forEach(cb => {
            cb.checked = false;
            cb.closest('.santri-row')?.classList.remove('selected-row');
        });
        updateActionBar();
    }

    btnGoToStep2.addEventListener('click', proceedToStep2);
    if (btnTopGoToStep2) btnTopGoToStep2.addEventListener('click', proceedToStep2);

    // Navigation Step 2 -> Step 1 (All back buttons)
    document.querySelectorAll('.btn-back-step1').forEach(btn => {
        btn.addEventListener('click', function() {
            step2.classList.remove('step-active');
            step2.classList.add('step-hidden');
            setTimeout(() => {
                step1.classList.remove('step-hidden');
                step1.classList.add('step-active');
            }, 120);
        });
    });

    // Synchronize Top & Bottom Merge Mode dropdowns
    document.querySelectorAll('.merge-mode-sync').forEach(sel => {
        sel.addEventListener('change', function() {
            const val = this.value;
            document.querySelectorAll('.merge-mode-sync').forEach(s => {
                if (s !== this) s.value = val;
            });
        });
    });

    // Refresh Santri Table logic
    function refreshSantriTable() {
        const searchQuery = globalSearchSantri.value.toLowerCase();
        const filterTahun = document.getElementById('filterTahun').value;
        const compFilter = filterCompleteness ? filterCompleteness.value : 'all';
        
        const colSearchInputs = document.querySelectorAll('.col-search');
        const colFilters = Array.from(colSearchInputs).map(i => ({
            col: parseInt(i.getAttribute('data-col')),
            val: i.value.toLowerCase()
        })).filter(f => f.val !== '');
        
        santriRows.forEach(row => {
            const berkasData = JSON.parse(row.getAttribute('data-berkas'));
            const nama = row.getAttribute('data-nama');
            const paspor = row.getAttribute('data-paspor');
            const negara = row.getAttribute('data-negara');
            const badgeContainer = row.querySelector('.berkas-badges-container');
            
            let matchedDocsCount = 0;
            let totalSelectedDocs = selectedDocTypes.length;
            let matchingFilesCount = 0;
            
            let badgesHtml = '';
            selectedDocTypes.forEach(docType => {
                const foundDocs = berkasData.filter(b => {
                    if (b.nama !== docType) return false;
                    if (!b.tahun) return true;
                    if (filterTahun === 'all') return true;
                    return b.tahun == filterTahun;
                });
                if (foundDocs.length > 0) {
                    matchedDocsCount++;
                    matchingFilesCount += foundDocs.length;
                    const countLabel = foundDocs.length > 1 ? ` (${foundDocs.length} file)` : '';
                    badgesHtml += `<span class="badge badge-doc has-doc me-1 mb-1 shadow-xs"><i class="bi bi-check-circle-fill me-1 text-success"></i>${docType}${countLabel}</span>`;
                } else {
                    badgesHtml += `<span class="badge badge-doc no-doc me-1 mb-1"><i class="bi bi-x-circle me-1 text-danger"></i>${docType}</span>`;
                }
            });
            
            badgeContainer.innerHTML = badgesHtml;
            row.setAttribute('data-files-count', matchingFilesCount);
            row.setAttribute('data-matched-docs', matchedDocsCount);
            row.setAttribute('data-is-complete', (matchedDocsCount === totalSelectedDocs && totalSelectedDocs > 0) ? '1' : '0');
            
            // Search filters
            const matchesGlobalSearch = nama.includes(searchQuery) || paspor.includes(searchQuery) || negara.includes(searchQuery);
            
            let matchesCols = true;
            colFilters.forEach(f => {
                const cellText = row.children[f.col].textContent.toLowerCase();
                if (!cellText.includes(f.val)) matchesCols = false;
            });

            // Completeness filter
            let matchesCompleteness = true;
            if (compFilter === 'complete') {
                matchesCompleteness = (matchedDocsCount === totalSelectedDocs && totalSelectedDocs > 0);
            } else if (compFilter === 'partial') {
                matchesCompleteness = (matchedDocsCount > 0);
            }
            
            if (matchedDocsCount > 0 && matchesGlobalSearch && matchesCols && matchesCompleteness) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                row.querySelector('.santri-checkbox').checked = false;
                row.classList.remove('selected-row');
            }
        });
        
        updateSelectAllState();
        updateActionBar();
    }

    globalSearchSantri.addEventListener('input', refreshSantriTable);
    if (filterCompleteness) filterCompleteness.addEventListener('change', refreshSantriTable);
    
    document.querySelectorAll('.col-search').forEach(input => {
        input.addEventListener('keyup', refreshSantriTable);
    });

    // Select All
    selectAllSantri.addEventListener('change', function() {
        const isChecked = this.checked;
        santriRows.forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelector('.santri-checkbox').checked = isChecked;
                if (isChecked) row.classList.add('selected-row');
                else row.classList.remove('selected-row');
            }
        });
        updateActionBar();
    });

    // Smart Select: Hanya Pilih yang Lengkap Saja
    if (btnSelectCompleteOnly) {
        btnSelectCompleteOnly.addEventListener('click', function() {
            santriRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const isComplete = row.getAttribute('data-is-complete') === '1';
                    const cb = row.querySelector('.santri-checkbox');
                    cb.checked = isComplete;
                    if (isComplete) row.classList.add('selected-row');
                    else row.classList.remove('selected-row');
                }
            });
            updateSelectAllState();
            updateActionBar();
        });
    }

    // Unselect All Santri
    if (btnUnselectAllSantri) {
        btnUnselectAllSantri.addEventListener('click', function() {
            santriRows.forEach(row => {
                row.querySelector('.santri-checkbox').checked = false;
                row.classList.remove('selected-row');
            });
            selectAllSantri.checked = false;
            updateActionBar();
        });
    }

    santriCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.santri-row');
            if (this.checked) row?.classList.add('selected-row');
            else row?.classList.remove('selected-row');
            updateSelectAllState();
            updateActionBar();
        });
    });

    function updateSelectAllState() {
        const visibleRows = Array.from(santriRows).filter(row => row.style.display !== 'none');
        const visibleChecked = visibleRows.filter(row => row.querySelector('.santri-checkbox').checked);
        
        if (visibleRows.length === 0) {
            selectAllSantri.checked = false;
            selectAllSantri.indeterminate = false;
        } else if (visibleChecked.length === visibleRows.length) {
            selectAllSantri.checked = true;
            selectAllSantri.indeterminate = false;
        } else if (visibleChecked.length > 0) {
            selectAllSantri.checked = false;
            selectAllSantri.indeterminate = true;
        } else {
            selectAllSantri.checked = false;
            selectAllSantri.indeterminate = false;
        }
    }

    function updateActionBar() {
        let santriCount = 0;
        let fileCount = 0;
        
        santriRows.forEach(row => {
            const cb = row.querySelector('.santri-checkbox');
            if (cb.checked) {
                santriCount++;
                fileCount += parseInt(row.getAttribute('data-files-count') || 0);
            }
        });
        
        document.querySelectorAll('.count-santri-text').forEach(el => el.textContent = santriCount);
        document.querySelectorAll('.count-file-text').forEach(el => el.textContent = fileCount);
        
        const hasSelection = santriCount > 0;
        document.querySelectorAll('.btn-action-print').forEach(btn => btn.disabled = !hasSelection);
        document.querySelectorAll('.btn-action-merge').forEach(btn => btn.disabled = !hasSelection);
    }

    // Form Submission
    if (mergeForm) {
        mergeForm.addEventListener('submit', function(e) {
            mergeForm.querySelectorAll('input.dynamic-input').forEach(el => el.remove());
            
            const orderInput = document.createElement('input');
            orderInput.type = 'hidden';
            orderInput.name = 'document_order';
            orderInput.value = JSON.stringify(selectedDocTypes);
            orderInput.classList.add('dynamic-input');
            mergeForm.appendChild(orderInput);

            let addedIds = 0;
            const uniqueBerkasIds = new Set();
            
            const filterTahun = document.getElementById('filterTahun').value;
            santriRows.forEach(row => {
                const cb = row.querySelector('.santri-checkbox');
                if (cb.checked) {
                    const kds = row.getAttribute('data-kds');
                    if (kds) {
                        const inputKds = document.createElement('input');
                        inputKds.type = 'hidden';
                        inputKds.name = 'santri_kds[]';
                        inputKds.value = kds;
                        inputKds.classList.add('dynamic-input');
                        mergeForm.appendChild(inputKds);
                    }
                    
                    const berkasData = JSON.parse(row.getAttribute('data-berkas'));
                    selectedDocTypes.forEach(docType => {
                        const foundDocs = berkasData.filter(b => {
                            if (b.nama !== docType) return false;
                            if (!b.tahun) return true;
                            if (filterTahun === 'all') return true;
                            return b.tahun == filterTahun;
                        });
                        
                        foundDocs.forEach(foundDoc => {
                            if (!uniqueBerkasIds.has(foundDoc.id)) {
                                uniqueBerkasIds.add(foundDoc.id);
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'berkas_ids[]';
                                input.value = foundDoc.id;
                                input.classList.add('dynamic-input');
                                mergeForm.appendChild(input);
                                addedIds++;
                            }
                        });
                    });
                }
            });
            
            if (addedIds === 0) {
                e.preventDefault();
                alert('Tidak ada file yang valid untuk diproses.');
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                let csrfInput = mergeForm.querySelector('input[name="_csrf"]');
                if (!csrfInput) {
                    csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_csrf';
                    mergeForm.appendChild(csrfInput);
                }
                csrfInput.value = csrfToken;
            }
        });
    }
});
</script>
