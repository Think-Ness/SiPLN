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
.step-container { transition: opacity 0.3s ease, transform 0.3s ease; }
.step-hidden { display: none !important; opacity: 0; transform: translateY(10px); }
.step-active { display: block !important; opacity: 1; transform: translateY(0); }

/* Document Cards */
.doc-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    background: #fff;
}
.doc-card:hover {
    border-color: #b8daff;
    background: #f8fbff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.doc-card.selected {
    border-color: #0d6efd;
    background: #f0f7ff;
}
.doc-card .form-check-input {
    cursor: pointer;
}

/* Badges */
.badge-doc {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
    border-radius: 6px;
    font-weight: 500;
}
.badge-doc.has-doc { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
.badge-doc.no-doc { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; text-decoration: line-through; opacity: 0.6; }

/* Floating Action Bar */
.floating-action-bar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 1000;
    display: flex;
    gap: 12px;
    align-items: center;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}
.floating-action-bar.hidden {
    bottom: -100px;
    opacity: 0;
}

/* Custom Checkbox Size */
.santri-checkbox, #selectAllSantri {
    width: 1.2em;
    height: 1.2em;
    cursor: pointer;
}
.santri-row {
    user-select: none; /* Prevent text selection during drag-select */
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #e3f2fd;">
            <i class="bi bi-layers fs-5 text-primary"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Cetak Berkas Massal</h4>
            <div class="text-muted small fw-medium mt-1">Wizard Pencetakan Dokumen Spesifik secara Massal</div>
        </div>
    </div>
</div>

<!-- STEP 1: PILIH JENIS BERKAS -->
<div id="step1" class="step-container step-active">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary">
                <span class="badge bg-primary rounded-circle me-2">1</span>Pilih Jenis Dokumen
            </h6>
            <?php if (!empty($availableYears)): 
                $currentYear = date('Y');
            ?>
            <select id="filterTahun" class="form-select form-select-sm w-auto">
                <option value="all">-- Semua Tahun --</option>
                <?php foreach ($availableYears as $y): ?>
                <option value="<?= htmlspecialchars((string)$y) ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= htmlspecialchars((string)$y) ?></option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <select id="filterTahun" class="d-none"><option value="all">all</option></select>
            <?php endif; ?>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small mb-4">Pilih satu atau beberapa jenis dokumen yang ingin Bapak/Ibu kumpulkan. Setelah memilih, Bapak/Ibu dapat memilih santri mana saja yang akan dicetak dokumennya.</p>
            
            <?php foreach ($groupedBerkas as $kategori => $docs): ?>
            <div class="mb-4">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="bi bi-folder2-open me-2 text-primary"></i><?= htmlspecialchars($kategori) ?></h6>
                <div class="row g-3">
                    <?php 
                    ksort($docs);
                    foreach ($docs as $namaUnik => $namaDisplay): 
                    ?>
                    <div class="col-md-4 col-lg-3">
                        <label class="doc-card w-100 p-3 h-100 d-flex align-items-start gap-2 m-0">
                            <input class="form-check-input mt-1 doc-type-checkbox flex-shrink-0" type="checkbox" value="<?= htmlspecialchars($namaUnik) ?>">
                            <div>
                                <div class="fw-bold text-dark small lh-sm mb-1"><?= htmlspecialchars($namaDisplay) ?></div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($groupedBerkas)): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-folder-x fs-1 text-light mb-2"></i>
                <p>Belum ada data dokumen yang terunggah.</p>
            </div>
            <?php endif; ?>

            <div class="mt-4 pt-3 border-top text-end">
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-medium shadow-sm" id="btnGoToStep2" disabled>
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
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary">
                <span class="badge bg-primary rounded-circle me-2">2</span>Pilih Target Santri
            </h6>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnBackToStep1">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </button>
        </div>
        <div class="card-body p-3 bg-light">
            <div class="mb-3 p-3 bg-white rounded shadow-sm border">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-sort-down text-primary me-1"></i> Atur Urutan Penggabungan Dokumen</h6>
                <div class="text-muted small mb-3">Geser (Drag & Drop) kotak di bawah ini ke atas atau ke bawah untuk menentukan urutan halaman dokumen saat di-Merge.</div>
                <ul id="sortableList" class="list-group gap-2 border-0">
                    <!-- Populated by JS -->
                </ul>
            </div>
            <!-- Filter Bar for Step 2 -->
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-0 px-3"><i class="bi bi-search text-secondary"></i></span>
                        <input type="text" id="globalSearchSantri" class="form-control border-0 shadow-none bg-white py-2" placeholder="Cari nama santri, paspor, negara..." style="font-size: .85rem;">
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
                        <th>Ketersediaan Dokumen</th>
                        <th>Kelas</th>
                        <th>Negara</th>
                        <th>Exp ITAS</th>
                        <th>Exp Paspor</th>
                    </tr>
                    <tr class="table-secondary column-filters">
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm col-search" data-col="1" placeholder="Cari Santri..."></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm col-search" data-col="3" placeholder="Cari Kelas..."></th>
                        <th><input type="text" class="form-control form-control-sm col-search" data-col="4" placeholder="Cari Negara..."></th>
                        <th><input type="text" class="form-control form-control-sm col-search" data-col="5" placeholder="Cari Exp ITAS..."></th>
                        <th><input type="text" class="form-control form-control-sm col-search" data-col="6" placeholder="Cari..."></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($santris)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data santri</td></tr>
                    <?php else: ?>
                    <?php foreach ($santris as $s): 
                        $kds = $s['kds'];
                        $berkas = $berkasMap[$kds] ?? [];
                        // Simplify berkas list for frontend processing
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
                        <td class="fw-medium text-dark"><?= htmlspecialchars((string)$s['nama']) ?></td>
                        <td class="berkas-badges-container">
                            <!-- Badges will be injected here by JS -->
                        </td>
                        <td class="text-muted"><?= htmlspecialchars((string)($s['kelas'] ?? '-')) ?></td>
                        <td class="text-muted"><?= htmlspecialchars((string)($s['negara'] ?? '-')) ?></td>
                        <td class="<?= $isExpiredI ? 'text-danger fw-bold' : ($isUrgentI ? 'text-warning fw-bold' : 'text-muted') ?>">
                            <?= $fmtI ?>
                            <?php if ($isExpiredI): ?>
                                <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                            <?php elseif ($isUrgentI): ?>
                                <?php $textI = $diffDaysI > 90 ? floor($diffDaysI / 30) . ' bln lagi' : $diffDaysI . ' hari lagi'; ?>
                                <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="<?= $isExpiredP ? 'text-danger fw-bold' : ($isUrgentP ? 'text-warning fw-bold' : 'text-muted') ?>">
                            <?= $fmtP ?>
                            <?php if ($isExpiredP): ?>
                                <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
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
    </div>
</div>

<!-- Floating Action Bar -->
<div class="floating-action-bar hidden" id="actionBar">
    <div class="fw-bold text-dark me-auto">
        <span id="selectedCountSantri" class="text-primary fs-5">0</span> Santri &bull; 
        <span id="selectedCountFile" class="text-success fs-5">0</span> File
    </div>
    <div class="d-flex align-items-center gap-2">
        <select name="merge_mode" class="form-select form-select-sm border-success text-success fw-bold rounded-pill" style="width: auto;" id="mergeMode">
            <option value="sekaligus">Gabung Semua Santri (1 PDF)</option>
            <option value="individual">Pisah Per Santri (.zip)</option>
            <option value="save_storage">Simpan Langsung ke Storage Server</option>
        </select>
        <button type="submit" name="action" value="print" class="btn btn-info rounded-pill px-4 fw-medium text-white" id="btnPrint" disabled>
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <button type="submit" name="action" value="merge" class="btn btn-success rounded-pill px-4 fw-medium" id="btnMerge" disabled>
            <i class="bi bi-file-earmark-pdf me-1"></i> Merge
        </button>
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
    const btnBackToStep1 = document.getElementById('btnBackToStep1');
    
    // --- Step 2 Elements ---
    const santriRows = document.querySelectorAll('.santri-row');
    const selectAllSantri = document.getElementById('selectAllSantri');
    const santriCheckboxes = document.querySelectorAll('.santri-checkbox');
    const globalSearchSantri = document.getElementById('globalSearchSantri');
    
    // --- Action Bar ---
    const actionBar = document.getElementById('actionBar');
    const selectedCountSantri = document.getElementById('selectedCountSantri');
    const selectedCountFile = document.getElementById('selectedCountFile');
    const btnPrint = document.getElementById('btnPrint');
    const btnMerge = document.getElementById('btnMerge');
    const mergeForm = document.getElementById('mergeForm');

    // Current selection state
    let selectedDocTypes = [];
    
    // Update Step 1 card styles when checkbox changes
    docCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const card = this.closest('.doc-card');
            if (this.checked) card.classList.add('selected');
            else card.classList.remove('selected');
            
            // Enable/disable next button
            const anyChecked = document.querySelectorAll('.doc-type-checkbox:checked').length > 0;
            btnGoToStep2.disabled = !anyChecked;
        });
    });

    // Navigation Step 1 -> Step 2
    btnGoToStep2.addEventListener('click', function() {
        // Get selected doc types
        selectedDocTypes = Array.from(document.querySelectorAll('.doc-type-checkbox:checked')).map(cb => cb.value);
        
        // Refresh Table rows visibility and badges based on selected doc types
        refreshSantriTable();
        
        // Populate Sortable List
        const sortableList = document.getElementById('sortableList');
        sortableList.innerHTML = '';
        selectedDocTypes.forEach(docType => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center gap-2 border shadow-sm rounded-3 bg-white';
            li.style.cursor = 'grab';
            li.draggable = true;
            li.innerHTML = `<i class="bi bi-grip-vertical text-muted"></i> <span class="doc-name fw-medium">${docType}</span>`;
            
            li.addEventListener('dragstart', function(e) {
                this.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', docType);
            });
            li.addEventListener('dragend', function() {
                this.classList.remove('opacity-50');
                // Update selectedDocTypes order
                const newOrder = Array.from(sortableList.querySelectorAll('.doc-name')).map(el => el.textContent);
                selectedDocTypes = newOrder;
            });
            sortableList.appendChild(li);
        });

        // Drag over handling for sorting
        sortableList.addEventListener('dragover', function(e) {
            e.preventDefault();
            const dragging = document.querySelector('.opacity-50');
            if (!dragging) return;
            const siblings = [...sortableList.querySelectorAll('.list-group-item:not(.opacity-50)')];
            let nextSibling = siblings.find(sibling => {
                const rect = sibling.getBoundingClientRect();
                return e.clientY <= rect.top + rect.height / 2;
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
            actionBar.classList.remove('hidden');
        }, 150);
        
        // Reset search and selection
        globalSearchSantri.value = '';
        selectAllSantri.checked = false;
        santriCheckboxes.forEach(cb => cb.checked = false);
        updateActionBar();
    });

    // Navigation Step 2 -> Step 1
    btnBackToStep1.addEventListener('click', function() {
        step2.classList.remove('step-active');
        step2.classList.add('step-hidden');
        actionBar.classList.add('hidden');
        setTimeout(() => {
            step1.classList.remove('step-hidden');
            step1.classList.add('step-active');
        }, 150);
    });

    // Refresh Santri Table logic
    function refreshSantriTable() {
        const searchQuery = globalSearchSantri.value.toLowerCase();
        const filterTahun = document.getElementById('filterTahun').value;
        
        // Get column filters
        const colSearchInputs = document.querySelectorAll('.col-search');
        const colFilters = Array.from(colSearchInputs).map(i => ({
            col: parseInt(i.getAttribute('data-col')),
            val: i.value.toLowerCase()
        })).filter(f => f.val !== '');
        
        santriRows.forEach(row => {
            // Parse data
            const berkasData = JSON.parse(row.getAttribute('data-berkas'));
            const nama = row.getAttribute('data-nama');
            const paspor = row.getAttribute('data-paspor');
            const negara = row.getAttribute('data-negara');
            const badgeContainer = row.querySelector('.berkas-badges-container');
            
            // Check if santri has ANY of the selected docs
            let hasAnyDoc = false;
            let matchingFilesCount = 0;
            
            // Generate Badges HTML
            let badgesHtml = '';
            selectedDocTypes.forEach(docType => {
                const foundDocs = berkasData.filter(b => {
                    if (b.nama !== docType) return false;
                    if (!b.tahun) return true;
                    if (filterTahun === 'all') return true;
                    return b.tahun == filterTahun;
                });
                if (foundDocs.length > 0) {
                    hasAnyDoc = true;
                    matchingFilesCount += foundDocs.length;
                    
                    // Jika lebih dari 1 dokumen, tunjukkan jumlah file (contoh: Seluruh Riwayat ITAS)
                    const countLabel = foundDocs.length > 1 ? ` (${foundDocs.length} file)` : '';
                    badgesHtml += `<span class="badge badge-doc has-doc me-1 mb-1"><i class="bi bi-check-circle me-1"></i>${docType}${countLabel}</span>`;
                } else {
                    badgesHtml += `<span class="badge badge-doc no-doc me-1 mb-1"><i class="bi bi-x-circle me-1"></i>${docType}</span>`;
                }
            });
            
            badgeContainer.innerHTML = badgesHtml;
            // Store the amount of matching files on the row for calculation later
            row.setAttribute('data-files-count', matchingFilesCount);
            
            // Global Filter logic
            const matchesGlobalSearch = nama.includes(searchQuery) || paspor.includes(searchQuery) || negara.includes(searchQuery);
            
            // Column Filter Logic
            let matchesCols = true;
            colFilters.forEach(f => {
                const cellText = row.children[f.col].textContent.toLowerCase();
                if (!cellText.includes(f.val)) {
                    matchesCols = false;
                }
            });
            
            // Only show if they have at least one doc AND match both searches
            if (hasAnyDoc && matchesGlobalSearch && matchesCols) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                // Automatically uncheck hidden rows
                row.querySelector('.santri-checkbox').checked = false;
            }
        });
        
        // Re-evaluate "Select All" checkbox state
        updateSelectAllState();
        updateActionBar();
    }

    // Step 2 Search Input
    globalSearchSantri.addEventListener('input', refreshSantriTable);
    
    // Column Search Inputs
    const colSearchInputsList = document.querySelectorAll('.col-search');
    colSearchInputsList.forEach(input => {
        input.addEventListener('keyup', refreshSantriTable);
    });

    // Step 2 Checkbox Logic
    selectAllSantri.addEventListener('change', function() {
        const isChecked = this.checked;
        santriRows.forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelector('.santri-checkbox').checked = isChecked;
            }
        });
        updateActionBar();
    });

    santriCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelectAllState();
            updateActionBar();
        });
    });

    // Drag-to-Select Logic
    let isDragging = false;
    let dragState = false;
    const tableBody = document.querySelector('#santriTable tbody');

    tableBody.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return; // Only left click
        const row = e.target.closest('.santri-row');
        if (!row || row.style.display === 'none') return;

        isDragging = true;
        const cb = row.querySelector('.santri-checkbox');

        // If clicked on input, browser will toggle it. Just record the target state.
        if (e.target.tagName.toLowerCase() === 'input') {
            dragState = !cb.checked;
        } else {
            // Clicked anywhere else on the row: prevent text selection, manually toggle
            e.preventDefault();
            dragState = !cb.checked;
            cb.checked = dragState;
        }
        
        updateSelectAllState();
        updateActionBar();
    });

    tableBody.addEventListener('mouseover', function(e) {
        if (!isDragging) return;
        const row = e.target.closest('.santri-row');
        if (!row || row.style.display === 'none') return;
        
        const cb = row.querySelector('.santri-checkbox');
        if (cb.checked !== dragState) {
            cb.checked = dragState;
            updateSelectAllState();
            updateActionBar();
        }
    });

    window.addEventListener('mouseup', function() {
        isDragging = false;
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

    // Update Floating Action Bar
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
        
        selectedCountSantri.textContent = santriCount;
        selectedCountFile.textContent = fileCount;
        
        const hasSelection = santriCount > 0;
        btnPrint.disabled = !hasSelection;
        btnMerge.disabled = !hasSelection;
    }

    // Form Submission Logic
    if (mergeForm) {
        mergeForm.addEventListener('submit', function(e) {
            // Remove any previously appended hidden inputs
            mergeForm.querySelectorAll('input.dynamic-input').forEach(el => el.remove());
            
            // Add document_order array
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
            
            // CSRF
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
