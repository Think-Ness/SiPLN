<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $processes
 * @var array $processSteps
 * @var array $processTarif
 * @var string $role
 * @var array $globalProcesses
 */

$this->setTitle('Pengaturan Job Desk | ' . $applicationParams->name);

$globalProcessesJson = json_encode($globalProcesses ?? []);
$processesJson = json_encode(array_map(function($p) use ($processSteps, $processTarif) {
    $tarif = $processTarif[$p['id']] ?? null;
    return [
        'id' => $p['id'],
        'nama_proses' => $p['nama_proses'],
        'biaya_instansi' => $tarif ? (float)$tarif['biaya_instansi'] : 0,
        'biaya_santri' => $tarif ? (float)$tarif['biaya_santri'] : 0,
        'keterangan_tarif' => $tarif['keterangan'] ?? '',
        'steps' => array_map(function($s) {
            return [
                'id' => $s['id'],
                'step_nama' => $s['step_nama']
            ];
        }, $processSteps[$p['id']] ?? [])
    ];
}, $processes));
?>

<div class="jobdesk-page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-3 fs-5 d-inline-flex align-items-center justify-content-center">
                <i class="bi bi-gear-wide-connected"></i>
            </span>
            <span>Pengaturan Proses Job Desk <?= $role === 'super_admin' ? '<span class="badge bg-info text-dark fs-6 rounded-pill ms-1">Referensi Global</span>' : '' ?></span>
        </h4>
        <div class="text-muted small">Kelola tahapan alur kerja, tarif biaya instansi, dan penagihan santri.</div>
    </div>
    <div class="jobdesk-header-actions d-flex flex-wrap gap-2 align-items-center">
        <?php if ($role !== 'super_admin'): ?>
            <button class="btn btn-warning rounded-pill px-3 py-2 fw-medium shadow-sm d-flex align-items-center justify-content-center" onclick="copyGlobalTemplate()" id="btnCopy">
                <i class="bi bi-copy me-1"></i> Salin Referensi
            </button>
        <?php endif; ?>
        <a href="<?= API_URL ?>/job-desk" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-medium shadow-sm d-flex align-items-center justify-content-center">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center" onclick="saveSettings()" id="btnSave">
            <i class="bi bi-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4 bg-light rounded-4">
                
                <div id="processContainer" class="d-flex flex-column gap-3">
                    <!-- Processes will be rendered here -->
                </div>

                <div class="text-center mt-4 border-top pt-4">
                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="addProcess()">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Proses Baru
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Template for Process -->
<template id="tmpl-process">
    <div class="card border border-primary-subtle shadow-sm rounded-4 process-item overflow-hidden" data-id="">
        <div class="card-header bg-white border-bottom-0 p-3 process-card-header">
            <div class="process-header-main d-flex align-items-center justify-content-between w-100 gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <span class="drag-handle-badge text-muted cursor-grab d-flex align-items-center justify-content-center handle-process" title="Tahan & geser untuk mengubah urutan">
                        <i class="bi bi-grip-vertical fs-5"></i>
                    </span>
                    <div class="input-group process-name-input-group flex-grow-1 shadow-xs">
                        <span class="input-group-text bg-light text-primary border-end-0"><i class="bi bi-diagram-3-fill"></i></span>
                        <input type="text" class="form-control fw-bold border-start-0 ps-1 process-name" placeholder="Nama Proses (misal: Perpanjangan ITAS)" value="">
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 process-action-btns flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center btn-icon-action" onclick="toggleProcessCollapse(this)" title="Buka / Tutup Detail">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center btn-icon-action" onclick="removeProcess(this)" title="Hapus Proses">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse process-collapse">
            <div class="card-body p-3 p-md-4 bg-light border-top">
                <!-- Tarif Section -->
                <div class="bg-white p-3 rounded-4 shadow-sm border border-light-subtle mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 text-uppercase small" style="letter-spacing:0.5px;">
                            <i class="bi bi-cash-coin text-success fs-5"></i> Tarif Biaya & Operasional
                        </h6>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">Keuangan</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Biaya ke Instansi Pemerintah</label>
                            <div class="input-group input-group-sm shadow-xs">
                                <span class="input-group-text bg-light fw-semibold">Rp</span>
                                <input type="number" class="form-control tarif-instansi fw-semibold" placeholder="0" min="0" step="1000">
                            </div>
                            <div class="form-text text-muted" style="font-size: .72rem;">Biaya resmi dibayarkan ke pemerintah</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Biaya Ditagih ke Santri</label>
                            <div class="input-group input-group-sm shadow-xs">
                                <span class="input-group-text bg-light fw-semibold">Rp</span>
                                <input type="number" class="form-control tarif-santri fw-semibold" placeholder="0" min="0" step="1000">
                            </div>
                            <div class="form-text text-muted" style="font-size: .72rem;">Biaya yang dibayarkan oleh santri</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Selisih Operasional</label>
                            <div class="input-group input-group-sm shadow-xs">
                                <span class="input-group-text bg-success-subtle text-success fw-bold">Rp</span>
                                <input type="text" class="form-control tarif-selisih bg-success-subtle text-success fw-bold" readonly>
                            </div>
                            <div class="form-text text-muted" style="font-size: .72rem;">Otomatis: Santri &minus; Instansi</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Keterangan Tarif (Opsional)</label>
                            <input type="text" class="form-control form-control-sm tarif-keterangan" placeholder="Contoh: Termasuk biaya admin pengurusan Rp 100.000">
                        </div>
                    </div>
                </div>

                <!-- Steps Section -->
                <div class="bg-white p-3 rounded-4 shadow-sm border border-light-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 text-uppercase small" style="letter-spacing:0.5px;">
                            <i class="bi bi-list-check text-primary fs-5"></i> Tahapan / Alur Kerja
                        </h6>
                        <small class="text-muted">Urutan alur kerja santri</small>
                    </div>
                    <div class="steps-container d-flex flex-column gap-2 mb-3">
                        <!-- Steps go here -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" onclick="addStep(this)">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Tahap
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Template for Step -->
<template id="tmpl-step">
    <div class="d-flex align-items-center step-item bg-light-subtle p-2 px-3 rounded-3 shadow-xs border" data-id="">
        <span class="cursor-grab text-muted me-2 handle-step d-flex align-items-center p-1" title="Geser urutan tahapan">
            <i class="bi bi-grip-vertical fs-5"></i>
        </span>
        <div class="flex-grow-1 me-2">
            <input type="text" class="form-control form-control-sm bg-white step-name fw-medium" placeholder="Nama Tahap (misal: Proses Kanwil)">
        </div>
        <button class="btn btn-sm btn-light text-danger rounded-circle p-1 d-flex align-items-center justify-content-center btn-remove-step" onclick="removeStep(this)" title="Hapus Tahap">
            <i class="bi bi-x-circle-fill fs-5"></i>
        </button>
    </div>
</template>

<style>
    .cursor-grab { cursor: grab; }
    .cursor-grab:active { cursor: grabbing; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .sortable-ghost { opacity: 0.4; background: #e0e7ff !important; border: 2px dashed #3b82f6 !important; }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .btn-icon-action {
        width: 36px;
        height: 36px;
        transition: all 0.2s ease;
    }
    .btn-icon-action:hover {
        transform: scale(1.05);
    }
    .btn-remove-step {
        width: 32px;
        height: 32px;
        transition: all 0.2s ease;
    }
    .btn-remove-step:hover {
        background-color: #fee2e2 !important;
    }
    .drag-handle-badge {
        width: 28px;
        height: 36px;
    }
    .process-item {
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .process-item:hover {
        border-color: #93c5fd !important;
    }

    /* Mobile Viewport Optimizations */
    @media (max-width: 767.98px) {
        .jobdesk-page-header {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 14px;
        }
        .jobdesk-header-actions {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 8px !important;
        }
        .jobdesk-header-actions .btn {
            font-size: 0.85rem;
            padding: 8px 12px !important;
            width: 100%;
        }
        .jobdesk-header-actions #btnSave {
            grid-column: span 2;
        }
        .process-card-header {
            padding: 12px !important;
        }
        .process-header-main {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px;
        }
        .process-header-main > div:first-child {
            width: 100%;
        }
        .process-action-btns {
            justify-content: flex-end;
            width: 100%;
            border-top: 1px dashed #e2e8f0;
            padding-top: 8px;
        }
        .step-item {
            padding: 8px 10px !important;
        }
    }
</style>

<script src="<?= ASSET_URL ?>/assets/offline/js/Sortable.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>
<script>
const processesData = <?= $processesJson ?>;
const globalTemplates = <?= $globalProcessesJson ?>;
const role = "<?= $role ?>";

document.addEventListener('DOMContentLoaded', () => {
    renderProcesses();
});

function renderProcesses() {
    const container = document.getElementById('processContainer');
    container.innerHTML = '';
    
    processesData.forEach(p => {
        const pEl = createProcessElement(p.id, p.nama_proses, p.biaya_instansi, p.biaya_santri, p.keterangan_tarif);
        const stepsContainer = pEl.querySelector('.steps-container');
        
        p.steps.forEach(s => {
            stepsContainer.appendChild(createStepElement(s.id, s.step_nama));
        });
        
        container.appendChild(pEl);
    });

    initSortableProcesses();
}

function createProcessElement(id, name, biayaInstansi, biayaSantri, keteranganTarif) {
    const tmpl = document.getElementById('tmpl-process').content.cloneNode(true);
    const el = tmpl.querySelector('.process-item');
    el.dataset.id = id || 'new-' + Date.now();
    el.querySelector('.process-name').value = name || '';
    
    // Set tarif values
    el.querySelector('.tarif-instansi').value = biayaInstansi || '';
    el.querySelector('.tarif-santri').value = biayaSantri || '';
    el.querySelector('.tarif-keterangan').value = keteranganTarif || '';
    updateSelisih(el);
    
    // Auto-calculate selisih on change
    el.querySelector('.tarif-instansi').addEventListener('input', () => updateSelisih(el));
    el.querySelector('.tarif-santri').addEventListener('input', () => updateSelisih(el));
    
    // Init sortable for steps inside this process
    new Sortable(el.querySelector('.steps-container'), {
        animation: 150,
        handle: '.handle-step',
        ghostClass: 'sortable-ghost'
    });
    
    return el;
}

function updateSelisih(processEl) {
    const santri = parseFloat(processEl.querySelector('.tarif-santri').value) || 0;
    const instansi = parseFloat(processEl.querySelector('.tarif-instansi').value) || 0;
    const selisih = santri - instansi;
    processEl.querySelector('.tarif-selisih').value = new Intl.NumberFormat('id-ID').format(selisih);
}

function createStepElement(id, name) {
    const tmpl = document.getElementById('tmpl-step').content.cloneNode(true);
    const el = tmpl.querySelector('.step-item');
    el.dataset.id = id || 'new-' + Date.now();
    el.querySelector('.step-name').value = name || '';
    return el;
}

function initSortableProcesses() {
    new Sortable(document.getElementById('processContainer'), {
        animation: 150,
        handle: '.handle-process',
        ghostClass: 'sortable-ghost'
    });
}

function addProcess() {
    const container = document.getElementById('processContainer');
    const newProcess = createProcessElement('', '');
    
    // Automatically expand new process
    const collapseEl = newProcess.querySelector('.process-collapse');
    const icon = newProcess.querySelector('.toggle-icon');
    collapseEl.classList.add('show');
    icon.classList.remove('bi-chevron-down');
    icon.classList.add('bi-chevron-up');
    
    container.appendChild(newProcess);
}

function addStep(btn) {
    const stepsContainer = btn.closest('.card-body').querySelector('.steps-container');
    stepsContainer.appendChild(createStepElement('', ''));
}

function removeProcess(btn) {
    const processDiv = btn.closest('.process-item');
    Swal.fire({
        title: 'Hapus Proses?',
        text: "Semua tahapan di dalam proses ini juga akan terhapus.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            processDiv.remove();
        }
    });
}

function copyGlobalTemplate() {
    if (globalTemplates.length === 0) {
        Swal.fire('Info', 'Tidak ada referensi global yang tersedia saat ini.', 'info');
        return;
    }
    
    let html = '<div class="text-start mb-3"><p class="text-muted">Pilih proses yang ingin Anda salin ke instansi Anda:</p>';
    globalTemplates.forEach(t => {
        let stepsHtml = '';
        if (t.steps && t.steps.length > 0) {
            stepsHtml = '<ul class="list-unstyled mb-0 ms-2 border-start border-2 border-primary-subtle ps-3">';
            t.steps.forEach((s, idx) => {
                stepsHtml += `<li class="py-1 text-muted small"><span class="badge bg-secondary opacity-75 me-2 rounded-pill">${idx+1}</span>${s.step_nama}</li>`;
            });
            stepsHtml += '</ul>';
        } else {
            stepsHtml = '<div class="small text-muted ms-3"><em>Belum ada tahapan</em></div>';
        }

        html += `
            <div class="border rounded-3 p-3 mb-3 bg-light shadow-sm text-start">
                <div class="form-check m-0 d-flex align-items-center">
                    <input class="form-check-input template-checkbox flex-shrink-0" type="checkbox" value="${t.id}" id="tmpl_${t.id}" checked style="cursor:pointer; width: 1.2rem; height: 1.2rem; margin-top:0;">
                    <label class="form-check-label ms-3 fw-bold text-dark w-100" for="tmpl_${t.id}" style="cursor:pointer;">
                        ${t.nama_proses}
                    </label>
                </div>
                <details class="mt-2 ms-4 ps-2">
                    <summary class="small text-primary" style="cursor:pointer; font-weight: 500; user-select: none;">Lihat ${t.steps ? t.steps.length : 0} Tahapan</summary>
                    <div class="mt-2">
                        ${stepsHtml}
                    </div>
                </details>
            </div>`;
    });
    html += '</div>';

    Swal.fire({
        title: 'Pilih Referensi',
        html: html,
        width: '600px',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Salin yang Dipilih!',
        preConfirm: () => {
            const selected = [];
            document.querySelectorAll('.template-checkbox:checked').forEach(function(el) {
                selected.push(el.value);
            });
            if (selected.length === 0) {
                Swal.showValidationMessage('Pilih minimal satu proses!');
                return false;
            }
            return selected;
        }
    }).then((res) => {
        if(res.isConfirmed) {
            const btn = document.getElementById('btnCopy');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyalin...';
            
            fetch('<?= API_URL ?>/api/job-desk/settings/copy-global', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ process_ids: res.value })
            })
            .then(response => response.json())
            .then(r => {
                if(r.success) {
                    Swal.fire('Berhasil', r.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', r.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-copy me-1"></i> Salin Referensi Super Admin';
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan jaringan/sistem.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-copy me-1"></i> Salin Referensi Super Admin';
            });
        }
    });
}


function removeStep(btn) {
    btn.closest('.step-item').remove();
}

function toggleProcessCollapse(btn) {
    const card = btn.closest('.process-item');
    const collapseEl = card.querySelector('.process-collapse');
    const icon = btn.querySelector('.toggle-icon');
    
    if (collapseEl.classList.contains('show')) {
        collapseEl.classList.remove('show');
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
    } else {
        collapseEl.classList.add('show');
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    }
}

function collectData() {
    let result = [];
    document.querySelectorAll('.process-item').forEach((pEl, pIdx) => {
        let p = {
            id: pEl.dataset.id.startsWith('new-') ? null : pEl.dataset.id,
            nama_proses: pEl.querySelector('.process-name').value.trim(),
            biaya_instansi: parseFloat(pEl.querySelector('.tarif-instansi').value) || 0,
            biaya_santri: parseFloat(pEl.querySelector('.tarif-santri').value) || 0,
            keterangan_tarif: pEl.querySelector('.tarif-keterangan').value.trim(),
            steps: []
        };
        
        pEl.querySelectorAll('.step-item').forEach((sEl, sIdx) => {
            let s = {
                id: sEl.dataset.id.startsWith('new-') ? null : sEl.dataset.id,
                step_nama: sEl.querySelector('.step-name').value.trim(),
                step_kode: sEl.querySelector('.step-name').value.trim().toUpperCase().replace(/[^A-Z0-9]/g, '_')
            };
            if(s.step_nama) p.steps.push(s);
        });
        
        if(p.nama_proses) result.push(p);
    });
    return result;
}

function saveSettings() {
    const data = collectData();
    if(data.length === 0) {
        Swal.fire('Peringatan', 'Minimal harus ada 1 proses', 'warning');
        return;
    }
    
    // Check if any process has no steps
    for(let i=0; i<data.length; i++) {
        if(data[i].steps.length === 0) {
            Swal.fire('Peringatan', `Proses "${data[i].nama_proses}" tidak memiliki tahapan.`, 'warning');
            return;
        }
    }

    const btn = document.getElementById('btnSave');
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
    btn.disabled = true;

    fetch('<?= API_URL ?>/api/job-desk/settings/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ processes: data })
    })
    .then(r => r.json())
    .then(res => {
        btn.innerHTML = origHtml;
        btn.disabled = false;
        
        if(res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan!',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = origHtml;
        btn.disabled = false;
        Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
    });
}
</script>
