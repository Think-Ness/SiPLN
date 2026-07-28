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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-gear-fill me-2 text-primary"></i>Pengaturan Proses Job Desk <?= $role === 'super_admin' ? '(Referensi Global)' : '' ?></h4>
        <div class="text-muted small">Kelola tahapan dan jenis proses yang tersedia.</div>
    </div>
    <div>
        <?php if ($role !== 'super_admin'): ?>
            <button class="btn btn-warning px-4 fw-medium me-2" onclick="copyGlobalTemplate()" id="btnCopy">
                <i class="bi bi-copy me-1"></i> Salin Referensi Super Admin
            </button>
        <?php endif; ?>
        <a href="<?= API_URL ?>/job-desk" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        <button class="btn btn-primary px-4 fw-medium" onclick="saveSettings()" id="btnSave">
            <i class="bi bi-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 bg-light rounded-4">
                
                <div id="processContainer" class="d-flex flex-column gap-4">
                    <!-- Processes will be rendered here -->
                </div>

                <div class="text-center mt-4 border-top pt-4">
                    <button class="btn btn-outline-primary rounded-pill px-4 fw-medium" onclick="addProcess()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Proses Baru
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Template for Process -->
<template id="tmpl-process">
    <div class="card border border-primary-subtle shadow-sm rounded-4 process-item overflow-hidden" data-id="">
        <div class="card-header bg-white border-bottom-0 p-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center flex-grow-1 me-3">
                <i class="bi bi-grip-vertical text-muted me-2 cursor-grab fs-5 handle-process"></i>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-diagram-3"></i></span>
                    <input type="text" class="form-control fw-bold border-start-0 ps-0 process-name" placeholder="Nama Proses (misal: Perpanjangan ITAS)" value="">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleProcessCollapse(this)" title="Tutup / Buka">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProcess(this)" title="Hapus Proses">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="collapse process-collapse">
            <div class="card-body p-3 bg-light border-top">
                <!-- Tarif Section -->
                <h6 class="small text-muted fw-bold mb-3 text-uppercase letter-spacing-1"><i class="bi bi-cash-coin me-1"></i>Tarif Biaya</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Biaya ke Instansi Pemerintah</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">Rp</span>
                            <input type="number" class="form-control tarif-instansi" placeholder="0" min="0" step="1000">
                        </div>
                        <div class="form-text" style="font-size: .65rem;">Biaya resmi yang dibayarkan ke pemerintah</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Biaya Ditagih ke Santri</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">Rp</span>
                            <input type="number" class="form-control tarif-santri" placeholder="0" min="0" step="1000">
                        </div>
                        <div class="form-text" style="font-size: .65rem;">Biaya yang harus dibayar santri</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Selisih Operasional</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-success-subtle text-success">Rp</span>
                            <input type="text" class="form-control tarif-selisih bg-success-subtle text-success fw-bold" readonly>
                        </div>
                        <div class="form-text" style="font-size: .65rem;">Otomatis: Santri âˆ’ Instansi</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted mb-1">Keterangan Tarif (opsional)</label>
                        <input type="text" class="form-control form-control-sm tarif-keterangan" placeholder="Contoh: Termasuk biaya admin Rp 100.000">
                    </div>
                </div>
                <hr class="my-3">
                <h6 class="small text-muted fw-bold mb-3 text-uppercase letter-spacing-1">Tahapan / Langkah</h6>
                <div class="steps-container d-flex flex-column gap-2 mb-3">
                    <!-- Steps go here -->
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="addStep(this)">
                    <i class="bi bi-plus me-1"></i> Tambah Tahap
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Template for Step -->
<template id="tmpl-step">
    <div class="d-flex align-items-center step-item bg-white p-2 rounded shadow-sm border" data-id="">
        <i class="bi bi-grip-vertical text-muted me-2 cursor-grab handle-step"></i>
        <div class="flex-grow-1 me-2">
            <input type="text" class="form-control form-control-sm step-name" placeholder="Nama Tahap (misal: Proses Kanwil)">
        </div>
        <button class="btn btn-sm btn-light text-danger border-0" onclick="removeStep(this)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

<style>
    .cursor-grab { cursor: grab; }
    .cursor-grab:active { cursor: grabbing; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .sortable-ghost { opacity: 0.4; }
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
