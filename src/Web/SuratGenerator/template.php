<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $santris
 * @var array $jenisPengajuan
 * @var array $mailings
 * @var array $instansi
 * @var array $currentUser
 * @var array $suratCounters
 */
$this->setTitle('Surat Generator | Sistem Informasi');
$kopSuratPath = $instansi['kop_surat'] ?? '';
$namaInstansi = $instansi['nama_instansi'] ?? 'Instansi';
$kodeInstansi = $instansi['kode_instansi'] ?? '';
?>

<style>
/* ========== WIZARD STEPPER ========== */
.wizard-stepper { display:flex; gap:0; margin-bottom:2rem; position:relative; }
.wizard-step { flex:1; text-align:center; padding:16px 12px; position:relative; cursor:pointer; transition:all .3s; border-bottom:3px solid #dee2e6; }
.wizard-step.active { border-bottom-color:#0d6efd; }
.wizard-step.completed { border-bottom-color:#198754; }
.wizard-step .step-number { width:36px; height:36px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; margin-bottom:6px; background:#e9ecef; color:#6c757d; transition:all .3s; }
.wizard-step.active .step-number { background:#0d6efd; color:#fff; box-shadow:0 2px 8px rgba(13,110,253,.35); }
.wizard-step.completed .step-number { background:#198754; color:#fff; }
.wizard-step .step-label { display:block; font-size:.75rem; font-weight:600; color:#adb5bd; letter-spacing:.5px; text-transform:uppercase; }
.wizard-step.active .step-label { color:#0d6efd; }
.wizard-step.completed .step-label { color:#198754; }
.wizard-panel { display:none; animation:fadeIn .3s ease; }
.wizard-panel.active { display:block; }
@keyframes fadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* ========== SANTRI TABLE ========== */
.santri-pick-table { font-size:.82rem; }
.santri-pick-table th { font-size:.7rem; letter-spacing:.5px; text-transform:uppercase; color:#6c757d; white-space:nowrap; }
.santri-pick-table td { vertical-align:middle; }
.santri-pick-table .form-check-input { width:1.1em; height:1.1em; }

/* ========== SURAT PREVIEW ========== */
.surat-preview-container { background:#f8f9fa; border-radius:12px; padding:20px; }
.surat-paper { background:#fff; max-width:210mm; margin:0 auto; padding:30mm 25mm 25mm 25mm; box-shadow:0 2px 20px rgba(0,0,0,.08); border-radius:4px; font-family:'Times New Roman', serif; font-size:12pt; line-height:1.7; position:relative; min-height:297mm; }
.surat-paper .kop-img { width:100%; max-height:120px; object-fit:contain; margin-bottom:8px; }
.surat-paper .surat-body table.data-table td { padding:2px 10px 2px 0; vertical-align:top; }
.surat-paper .surat-body table.data-table td:first-child { white-space:nowrap; }

/* ========== PRINT ========== */
@media print {
    body > *:not(.print-area) { display:none !important; }
    .print-area { display:block !important; position:fixed; top:0; left:0; width:100%; }
    .surat-paper { box-shadow:none; padding:0; margin:0; page-break-after:always; }
    .no-print { display:none !important; }
}

/* ========== MAILING CARD ========== */
.mailing-card { border-left:4px solid #0d6efd; transition:all .2s; }
.mailing-card:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(0,0,0,.1) !important; }

/* ========== MODE TOGGLE ========== */
.mode-option { cursor:pointer; border:2px solid #dee2e6; border-radius:12px; padding:16px; transition:all .25s; text-align:center; }
.mode-option:hover { border-color:#86b7fe; background:#f0f7ff; }
.mode-option.selected { border-color:#0d6efd; background:#e7f1ff; box-shadow:0 0 0 3px rgba(13,110,253,.15); }
.mode-option i { font-size:1.8rem; margin-bottom:8px; display:block; }
.mode-option .mode-title { font-weight:700; font-size:.9rem; }
.mode-option .mode-desc { font-size:.75rem; color:#6c757d; margin-top:4px; }

/* ========== SURAT TYPE CARD ========== */
.surat-type-card { border:2px solid #dee2e6; border-radius:12px; padding:14px 16px; cursor:pointer; transition:all .25s; }
.surat-type-card:hover { border-color:#86b7fe; }
.surat-type-card.selected { border-color:#0d6efd; background:#e7f1ff; }
.surat-type-card .surat-type-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:45px;height:45px;background:#19875415;">
            <i class="bi bi-envelope-paper fs-5 text-success"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing:-.5px;">Surat Generator</h4>
            <div class="text-muted small fw-medium mt-1">Buat mailing & cetak surat resmi secara profesional</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info rounded-pill px-3 fw-medium shadow-sm" onclick="showTemplateBookmarkInfo()">
            <i class="bi bi-info-circle me-1"></i> Info Bookmark
        </button>
        <button class="btn btn-outline-primary rounded-pill px-3 fw-medium shadow-sm" onclick="showJenisPengajuanModal()">
            <i class="bi bi-gear me-1"></i> Kelola Jenis Pengajuan
        </button>
    </div>
</div>

<!-- TAB NAV -->
<ul class="nav nav-pills mb-4 gap-2" id="mainTab">
    <li class="nav-item">
        <button class="nav-link active rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="pill" data-bs-target="#tabWizard">
            <i class="bi bi-magic me-1"></i> Buat Surat Baru
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-semibold" data-bs-toggle="pill" data-bs-target="#tabRiwayat">
            <i class="bi bi-clock-history me-1"></i> Riwayat Mailing <span class="badge bg-primary ms-1"><?= count($mailings) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

<!-- ======= TAB: WIZARD ======= -->
<div class="tab-pane fade show active" id="tabWizard">

<!-- Stepper -->
<div class="wizard-stepper no-print">
    <div class="wizard-step active" data-step="1" onclick="goToStep(1)">
        <span class="step-number">1</span>
        <span class="step-label">Pilih Santri & Mode</span>
    </div>
    <div class="wizard-step" data-step="2" onclick="goToStep(2)">
        <span class="step-number">2</span>
        <span class="step-label">Jenis Pengajuan</span>
    </div>
    <div class="wizard-step" data-step="3" onclick="goToStep(3)">
        <span class="step-number">3</span>
        <span class="step-label">Generate & Cetak</span>
    </div>
</div>

<!-- STEP 1: Pilih Santri & Mode -->
<div class="wizard-panel active" id="step1">
<div class="row g-4">
    <!-- Mode Selection -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-ui-radios-grid text-primary me-2"></i>Pilih Mode Surat</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mode-option selected" onclick="selectMode('sekaligus')" id="modeSekaligus">
                            <i class="bi bi-people-fill text-primary"></i>
                            <div class="mode-title">Sekaligus (Kolektif)</div>
                            <div class="mode-desc">1 surat dengan semua data terlampir di dalamnya</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mode-option" onclick="selectMode('perseorangan')" id="modePerseorangan">
                            <i class="bi bi-person-fill text-info"></i>
                            <div class="mode-title">Perseorangan</div>
                            <div class="mode-desc">1 data = 1 surat terpisah masing-masing</div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="selectedMode" value="sekaligus">
            </div>
        </div>
    </div>

    <!-- Santri Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom pt-3 pb-3 px-4 d-flex justify-content-between align-items-center sticky-top" style="z-index: 10;">
                <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="bi bi-person-check text-success me-2"></i>Pilih Santri</h6>
                    <div class="text-muted small fw-medium">Total Terpilih: <span class="badge bg-primary rounded-pill px-2" id="countPicked">0</span></div>
                </div>
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" onclick="goToStep(2)" id="btnToStep2">
                    Lanjut Pengajuan <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle santri-pick-table mb-0" id="santriPickTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 text-center" style="width:30px"><input type="checkbox" class="form-check-input" id="pickAll"></th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Pondok</th>
                                <th>Negara</th>
                                <th>No Paspor</th>
                                <th>Exp ITAS</th>
                            </tr>
                            <tr class="table-secondary">
                                <th></th>
                                <th><input type="text" class="form-control form-control-sm dt-search" data-col="1" placeholder="Cari Nama..." style="min-width:120px"></th>
                                <th>
                                    <input type="text" list="listKelas" class="form-control form-control-sm dt-search" data-col="2" placeholder="Cari/Pilih..." style="min-width:70px">
                                    <datalist id="listKelas">
                                        <?php foreach ($kelasList ?? [] as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </th>
                                <th>
                                    <input type="text" list="listPondok" class="form-control form-control-sm dt-search" data-col="3" placeholder="Cari/Pilih..." style="min-width:70px">
                                    <datalist id="listPondok">
                                        <?php foreach ($pondokList ?? [] as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </th>
                                <th>
                                    <input type="text" list="listNegara" class="form-control form-control-sm dt-search" data-col="4" placeholder="Cari/Pilih..." style="min-width:90px">
                                    <datalist id="listNegara">
                                        <?php foreach ($negaraList ?? [] as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </th>
                                <th><input type="text" class="form-control form-control-sm dt-search" data-col="5" placeholder="Cari..." style="min-width:90px"></th>
                                <th>
                                    <input type="text" list="listExpItas" class="form-control form-control-sm dt-search" data-col="6" placeholder="Cari/Pilih..." style="min-width:100px">
                                    <datalist id="listExpItas">
                                        <?php foreach ($expItasList ?? [] as $v): ?>
                                        <option value="<?= htmlspecialchars((string)$v) ?>"><?= date('F Y', strtotime($v . '-01')) ?></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($santris)): ?>
                        <?php foreach ($santris as $s): ?>
                            <?php 
                                $myKep = $_SESSION['def_kepengurusan'] ?? '';
                                $isPindahan = ($myKep !== '' && strcasecmp((string)($s['kepengurusan'] ?? ''), trim($myKep)) !== 0);
                                $jobDesks = $activeJobDeskMap[$s['kds']] ?? [];
                                $isOnJobDesk = count($jobDesks) > 0;
                                
                                $today = new DateTime();
                                $today->setTime(0, 0, 0);
                                
                                $expIStr = $s['exp_itas'] ?? '';
                                $expI  = !empty($expIStr) && $expIStr !== '0000-00-00' ? new DateTime($expIStr) : null;
                                if ($expI) $expI->setTime(0, 0, 0);
                                
                                $diffDaysI = $expI ? (int) $today->diff($expI)->format('%r%a') : null;
                                $isExpiredI = $expI && $diffDaysI <= 0;
                                $isUrgentI = $isExpiredI || ($diffDaysI !== null && $diffDaysI <= 90);
                                
                                $fmtI = $expI ? $expI->format('d-M-Y') : '-';
                                $rowClass = ($isExpiredI) ? 'bg-danger bg-opacity-25' : ($isUrgentI ? 'bg-danger bg-opacity-10' : '');
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="ps-3 text-center">
                                    <input type="checkbox" class="form-check-input pick-cb" value="<?= $s['kds'] ?>"
                                        data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                        data-kelas="<?= htmlspecialchars($s['kelas'] ?? '') ?>"
                                        data-pondok="<?= htmlspecialchars($s['pondok'] ?? '') ?>"
                                        data-negara="<?= htmlspecialchars($s['negara'] ?? '') ?>"
                                        data-kwn="<?= htmlspecialchars($s['kewarganegaraan'] ?? '') ?>"
                                        data-tl="<?= htmlspecialchars($s['tempat_lahir'] ?? '') ?>"
                                        data-tgl="<?= htmlspecialchars($s['tanggal_lahir'] ?? '') ?>"
                                        data-alamat="<?= htmlspecialchars($s['alamat'] ?? '') ?>"
                                        data-paspor="<?= htmlspecialchars($s['no_paspor'] ?? '') ?>"
                                        data-exp-paspor="<?= htmlspecialchars($s['exp_paspor'] ?? '') ?>"
                                        data-exp-itas="<?= htmlspecialchars($s['exp_itas'] ?? '') ?>"
                                    >
                                </td>
                                <td class="fw-semibold">
                                    <div class="mb-1"><?= htmlspecialchars($s['nama']) ?></div>
                                    <?php if ($isPindahan): ?>
                                        <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.65rem; vertical-align: middle;" title="Titipan dari <?= htmlspecialchars($s['kepengurusan'] ?? '') ?>">Pindahan</span>
                                    <?php endif; ?>
                                    <?php if ($isOnJobDesk): ?>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <?php foreach ($jobDesks as $jd): 
                                                $fullText = $jd['nama_proses'] . ' › ' . ($jd['tahap_saat_ini'] ?: 'Menunggu');
                                            ?>
                                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle d-inline-flex align-items-center" style="font-size: 0.65rem; padding: 4px 8px; font-weight: 500; max-width: 220px; cursor: help;" title="<?= htmlspecialchars($fullText) ?>" data-bs-toggle="tooltip">
                                                    <i class="bi bi-rocket-takeoff-fill me-1 flex-shrink-0"></i> 
                                                    <span class="text-truncate">
                                                        <?= htmlspecialchars($jd['nama_proses']) ?> 
                                                        <i class="bi bi-chevron-right mx-1 opacity-50" style="font-size:0.5rem"></i> 
                                                        <span class="fst-italic opacity-75"><?= htmlspecialchars($jd['tahap_saat_ini'] ?: 'Menunggu') ?></span>
                                                    </span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($s['kelas'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($s['pondok'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($s['negara'] ?? '-') ?></td>
                                <td><code class="text-primary"><?= htmlspecialchars($s['no_paspor'] ?? '-') ?></code></td>
                                <td class="<?= $isExpiredI ? 'text-danger fw-bold' : ($isUrgentI ? 'text-warning fw-bold' : '') ?>" data-sort="<?= htmlspecialchars((!empty($s['exp_itas']) && $s['exp_itas'] !== '0000-00-00') ? $s['exp_itas'] : '0000-00-00') ?>">
                                    <?= $fmtI ?>
                                    <?php if ($isExpiredI): ?>
                                        <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                    <?php elseif ($isUrgentI): ?>
                                        <?php $textI = $diffDaysI > 90 ? floor($diffDaysI / 30) . ' bln lagi' : $diffDaysI . ' hari lagi'; ?>
                                        <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom spacing for the table -->
    <div class="col-12 mb-4"></div>
</div>
</div>

<!-- STEP 2: Jenis Pengajuan & Buat Mailing -->
<div class="wizard-panel" id="step2">
<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white border-bottom pt-3 pb-3 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i>Detail Pengajuan</h6>
            </div>
            <div class="card-body p-4">
                <?php if (($_SESSION['role'] ?? '') === 'super_admin'): ?>
                <div class="mb-4 p-3 bg-danger bg-opacity-10 border border-danger-subtle rounded-3">
                    <label class="form-label text-danger small fw-bold"><i class="bi bi-buildings me-1"></i>KOP SURAT & ARSIP INSTANSI PENGIRIM (SUPER ADMIN)</label>
                    <select class="form-select border-danger-subtle bg-white" id="inpInstansiId">
                        <option value="">-- Gunakan Template Pusat --</option>
                        <?php foreach ($instansiList ?? [] as $inst): ?>
                            <option value="<?= htmlspecialchars((string)$inst['kode']) ?>"><?= htmlspecialchars($inst['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-danger mt-1" style="font-size:0.7rem;">Pilih instansi untuk menyesuaikan Kop Surat dan tujuan penyimpanan arsip.</div>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">JENIS PENGAJUAN</label>
                    <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-tag text-secondary"></i></span>
                        <select id="selJenisPengajuan" class="form-select border-0 shadow-none bg-white py-2" onchange="onJenisChange()">
                            <option value="">-- Pilih Jenis Pengajuan --</option>
                            <?php foreach ($jenisPengajuan as $jp): ?>
                            <option value="<?= $jp['id'] ?>"
                                data-hal="<?= htmlspecialchars($jp['hal'] ?? '') ?>"
                                data-isi="<?= htmlspecialchars($jp['isi'] ?? '') ?>"
                                data-kepada="<?= htmlspecialchars($jp['kepada'] ?? '') ?>"
                                data-tempat="<?= htmlspecialchars($jp['tempat'] ?? '') ?>"
                                data-surat="<?= htmlspecialchars($jp['surat_dibutuhkan'] ?? 'SP,SK,SJ,ST') ?>"
                                data-kantor="<?= htmlspecialchars($jp['kantor'] ?? '') ?>"
                            ><?= htmlspecialchars($jp['jenis_pengajuan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">TANGGAL SURAT</label>
                    <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-calendar text-secondary"></i></span>
                        <input type="date" id="inpTanggalSurat" class="form-control border-0 shadow-none bg-white py-2" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">URUTAN SANTRI DI LAMPIRAN</label>
                    <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-sort-alpha-down text-secondary"></i></span>
                        <select id="inpSortBy" class="form-select border-0 shadow-none bg-white py-2" onchange="updateStep2Summary()">
                            <option value="nama ASC">Abjad Nama (A - Z)</option>
                            <option value="negara ASC">Negara Asal</option>
                            <option value="exp_itas ASC">Masa Berlaku ITAS terdekat</option>
                            <option value="no_paspor ASC">Nomor Paspor</option>
                            <option value="manual">Kustom (Manual Drag & Drop)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">CATATAN TAMBAHAN (OPSIONAL)</label>
                    <textarea id="inpCatatan" class="form-control border shadow-sm rounded-3" rows="2" placeholder="Catatan internal..."></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-light border-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-card-checklist text-success me-2"></i>Ringkasan</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-muted small fw-bold mb-1">MODE</div>
                    <div class="fw-semibold" id="summaryMode">Sekaligus</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small fw-bold mb-1">SANTRI TERPILIH</div>
                    <div id="summarySantriList" class="small" style="max-height:200px;overflow-y:auto;"></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small fw-bold mb-1">SURAT YANG DIBUTUHKAN</div>
                    <div id="summarySuratNeeded" class="d-flex flex-wrap gap-1"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 d-flex justify-content-between mt-4 mb-5 border-top pt-4">
        <button class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-medium shadow-sm" onclick="goToStep(1)">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
        <button class="btn btn-success rounded-pill px-5 py-2 fw-semibold shadow" onclick="buatMailing()" id="btnBuatMailing" disabled>
            <i class="bi bi-check-circle me-1"></i> Buat Mailing & Lanjut
        </button>
    </div>
</div>
</div>

<!-- STEP 3: Generate & Cetak Surat -->
<div class="wizard-panel" id="step3">
<div class="row g-4">
    <div class="col-md-4 no-print">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-light border-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Generate Surat</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-muted small fw-bold mb-2">KODE MAILING</div>
                    <div class="fw-bold text-primary fs-6" id="step3KodeMailing">-</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small fw-bold mb-2">PILIH SURAT UNTUK DI-GENERATE</div>
                    <div id="suratTypeCards" class="d-flex flex-column gap-2"></div>
                    
                    <div class="form-check form-switch mt-3 p-3 bg-light rounded-3 border" id="lampiranCheckboxGroup" style="display:none;">
                        <input class="form-check-input ms-0 mt-1" type="checkbox" id="inpWithLampiran">
                        <label class="form-check-label small fw-bold text-dark ms-2" for="inpWithLampiran" style="letter-spacing:0.5px;">Sertakan Lampiran Daftar Santri</label>
                        <div class="text-muted small ms-2" style="font-size:0.75rem;">Otomatis menambah daftar nama di akhir halaman</div>
                    </div>
                </div>
                <hr>
                <div id="metadataSuratGroup" style="display:none;" class="mb-4">
                    <div id="konvensionalFields">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">NOMOR SURAT</label>
                            <span class="small text-muted" id="lastNomorInfo"></span>
                        </div>
                        <div class="input-group border shadow-sm rounded-3 overflow-hidden">
                            <input type="number" id="inpNomorUrut" class="form-control border-0 shadow-none bg-white py-2 fw-bold text-primary" placeholder="001">
                            <span class="input-group-text bg-light border-0 fw-medium text-secondary" id="inpNomorSuffix">/SP/PLN/VI/2026</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">HAL</label>
                        <textarea id="inpHal" class="form-control border shadow-sm rounded-3 py-2" rows="2" placeholder="Hal Surat"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">KEPADA</label>
                            <textarea id="inpKepada" class="form-control border shadow-sm rounded-3 py-2" rows="2" placeholder="Tujuan Surat"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">TEMPAT</label>
                            <input type="text" id="inpTempat" class="form-control border shadow-sm rounded-3 py-2" placeholder="Tempat Tujuan">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">ISI / KETERANGAN</label>
                        <textarea id="inpIsi" class="form-control border shadow-sm rounded-3" rows="3" placeholder="Isi detail surat (bila ada)"></textarea>
                    </div>
                    </div> <!-- end konvensionalFields -->
                    
                    <!-- Khusus Surat Tugas -->
                    <div id="petugasGroup" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">PILIH PETUGAS (USER)</label>
                            <select id="inpPetugasId" class="form-select border shadow-sm rounded-3 py-2" onchange="updatePetugasTtl()">
                                <?php if (!empty($pegawaiList)): ?>
                                    <?php foreach($pegawaiList as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-ttl="<?= htmlspecialchars($p['ttl'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($p['nama_lengkap']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="<?= $_SESSION['user_id'] ?? 0 ?>" data-ttl=""><?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'User') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">TEMPAT, TANGGAL LAHIR</label>
                            <input type="text" id="inpTtl" class="form-control border shadow-sm rounded-3 py-2 bg-light" placeholder="TTL Petugas..." readonly>
                        </div>
                    </div>
                </div>
                
                <div id="dynamicCollectionFields" class="mb-4 d-none"></div>
                <div id="dynamicCustomInputFields" class="mb-4 d-none"></div>

                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-outline-secondary rounded-pill fw-semibold shadow-sm w-100" id="btnBukaTemplate" onclick="bukaTemplate()" style="display:none;">
                        <i class="bi bi-file-word me-1"></i> Buka Template Asli
                    </button>
                    <button class="btn btn-primary rounded-pill fw-semibold shadow w-100" onclick="generateSurat()" id="btnGenerateSurat" disabled>
                        <i class="bi bi-lightning me-1"></i> Generate
                    </button>
                </div>
                <button class="btn btn-outline-success rounded-pill w-100 py-2 fw-semibold mt-2" onclick="cetakSurat()" id="btnCetakSurat" style="display:none;">
                    <i class="bi bi-printer me-1"></i> Cetak Surat
                </button>
            </div>
        </div>
    </div>
    
    <!-- Kolom Preview Area -->
    <div class="col-md-8 h-100" style="min-height: 80vh;">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-0 h-100" id="suratPreviewArea">
                <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 text-muted p-5 bg-light rounded-4">
                    <i class="bi bi-file-earmark-pdf" style="font-size: 5rem; opacity: 0.2; margin-bottom: 1rem;"></i>
                    <h4 class="fw-bold">Pratinjau Surat</h4>
                    <p class="mb-0">Pilih salah satu jenis surat di panel kiri, isi data yang diperlukan, lalu klik <strong>Generate Surat</strong> untuk melihat hasilnya di sini.</p>
                </div>
            </div>
        </div>
        
        <!-- Pindahkan tombol Buat Mailing Baru ke bawah Preview agar tidak mepet dan lebih rapi -->
        <div class="d-flex justify-content-end mt-4 pt-3 pb-5">
            <button class="btn btn-dark rounded-pill px-5 py-2 fw-bold shadow-sm" onclick="resetWizard()" style="letter-spacing: 0.5px;">
                <i class="bi bi-plus-lg me-2"></i> BUAT MAILING BARU
            </button>
        </div>
    </div>
</div>
</div>

</div><!-- /tabWizard -->

<!-- ======= TAB: RIWAYAT MAILING ======= -->
<div class="tab-pane fade" id="tabRiwayat">
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <?php if (empty($mailings)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <div class="fw-medium">Belum ada riwayat mailing</div>
            </div>
        <?php else: ?>
        <div class="d-flex justify-content-between align-items-center bg-light px-4 py-3 border-bottom">
            <div>
                <button class="btn btn-sm btn-danger d-none rounded-pill shadow-sm px-3 fw-medium" id="btnBulkDeleteMailing" onclick="bulkDeleteMailing()">
                    <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="countSelectedMailing">0</span>)
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="mailingTable" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center" style="width:30px"><input type="checkbox" class="form-check-input" id="pickAllMailing"></th>
                        <th>Kode Mailing</th>
                        <th>Jenis Pengajuan</th>
                        <th>Mode</th>
                        <th>Tanggal</th>
                        <th class="text-center">Santri</th>
                        <th class="text-center">Surat</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($mailings as $m): ?>
                    <tr>
                        <td class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input mailing-cb" value="<?= $m['id'] ?>">
                        </td>
                        <td><code class="text-primary fw-bold"><?= htmlspecialchars($m['kode_mailing']) ?></code></td>
                        <td><?= htmlspecialchars($m['jenis_pengajuan'] ?? '-') ?></td>
                        <td>
                            <?php if ($m['mode'] === 'sekaligus'): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">Sekaligus</span>
                            <?php else: ?>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle">Perseorangan</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($m['tanggal_surat'] ?? '-') ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= $m['jumlah_santri'] ?></span></td>
                        <td class="text-center"><span class="badge bg-success"><?= $m['jumlah_surat'] ?></span></td>
                        <td class="text-muted"><?= htmlspecialchars($m['created_by_name'] ?? '-') ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-primary py-0 px-2" title="Lihat Detail" onclick="openMailingDetail(<?= $m['id'] ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus" onclick="deleteMailing(<?= $m['id'] ?>, '<?= addslashes($m['kode_mailing']) ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

</div><!-- /tab-content -->

<!-- ======= MODAL: KELOLA JENIS PENGAJUAN ======= -->
<div class="modal fade" id="modalJenisPengajuan" tabindex="-1" data-bs-backdrop="static">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="modal-header py-3 px-4 bg-light border-0">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-tag-fill text-primary me-2"></i>Kelola Jenis Pengajuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body p-4">
        <div class="mb-4">
            <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="toggleJPForm()">
                <i class="bi bi-plus-circle me-1"></i> Tambah Jenis Pengajuan
            </button>
        </div>
        <div id="jpFormContainer" class="card border border-primary-subtle bg-white rounded-4 p-4 mb-4 shadow-sm" style="display:none;">
            <input type="hidden" id="jpEditId" value="">
            
            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Jenis Pengajuan *</label>
                    <input type="text" id="jpNama" class="form-control" placeholder="Contoh: Perpanjangan ITAS">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-secondary">Perihal (Hal)</label>
                    <textarea id="jpHal" class="form-control" rows="2" placeholder="Contoh: Permohonan Perpanjangan ITAS..."></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold text-secondary">Isi (Konten Surat)</label>
                    <textarea id="jpIsi" class="form-control" rows="2" placeholder="Contoh: Surat Rekomendasi Pembuatan Visa Belajar"></textarea>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-building me-2"></i>Tujuan Surat</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">Kepada</label>
                    <textarea id="jpKepada" class="form-control" rows="2" placeholder="Contoh: Kepala Kantor Imigrasi Kelas II"></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Instansi Tujuan</label>
                    <select id="jpKantor" class="form-select" onchange="onJPKantorChange()">
                        <option value="">Pilih Instansi...</option>
                        <!-- Akan diisi dinamis dari API -->
                    </select>
                    <div class="form-text" style="font-size:.7rem;">Folder dari Surat_Menyurat/</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Tempat</label>
                    <input type="text" id="jpTempat" class="form-control" placeholder="Contoh: Ponorogo / Jakarta">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-gear me-2"></i>Konfigurasi Output</h6>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-bold text-secondary mb-2 d-block">Surat yang Dibutuhkan (Pilih yang akan di-generate otomatis)</label>
                    
                    <div id="dynamicTemplateHint" class="form-text mb-2" style="font-size:.75rem;">Pilih Instansi Tujuan terlebih dahulu untuk melihat daftar template surat yang tersedia.</div>
                    <div id="dynamicTemplateCheckboxes" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="col-md-12 mt-3">
                    <label class="form-label small fw-bold text-secondary">Path Folder Output (Opsional)</label>
                    <input type="text" id="jpOutputPath" class="form-control" placeholder="Contoh: \Permohonan\ atau D:\Berkas\Output\">
                    <div class="form-text" style="font-size:0.75rem;">Biarkan kosong jika ingin sistem menyimpannya ke folder otomatis bawaan.</div>
                </div>
                <div class="col-12" style="display:none;">
                    <input type="file" id="jpTemplate" class="form-control" accept=".docx">
                </div>
                <div class="col-12 text-end mt-4 pt-3 border-top">
                    <button class="btn btn-light border px-4 me-2 rounded-pill fw-medium" onclick="toggleJPForm()">Batal</button>
                    <button class="btn btn-primary px-4 rounded-pill fw-medium" onclick="simpanJP()"><i class="bi bi-save me-2"></i>Simpan Konfigurasi</button>
                </div>
            </div>
        </div>
        <div id="jpListContainer">
            <div class="text-center py-3 text-muted"><i class="bi bi-hourglass-split"></i> Memuat...</div>
        </div>
    </div>
</div>
</div>
</div>

<!-- ======= MODAL: DETAIL MAILING ======= -->
<div class="modal fade" id="modalMailingDetail" tabindex="-1">
<div class="modal-dialog modal-dialog-centered modal-xl">
<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="modal-header py-3 px-4 bg-light border-0">
        <h5 class="modal-title fw-bold text-dark" id="modalMailingTitle">Detail Mailing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body p-4" id="modalMailingBody">
        <div class="text-center py-4"><i class="bi bi-hourglass-split fs-3"></i></div>
    </div>
</div>
</div>
</div>

<!-- ======= SCRIPTS ======= -->
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/dataTables.bootstrap5.min.css">
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>

<script>
// ========== STATE ==========
let currentStep = 1;
let selectedMode = 'sekaligus';
let currentMailingData = null;
let currentSuratType = null;
let pickTable = null;

// User Data from PHP
const currentUserName = "<?= addslashes($currentUser['nama_lengkap'] ?? '') ?>";

const suratCounters = <?= json_encode($suratCounters) ?>;
const instansiData = <?= json_encode($instansi) ?>;
const currentUserData = <?= json_encode($currentUser) ?>;
const kopSuratUrl = '<?= !empty($kopSuratPath) ? API_URL . "/profil-instansi/kop-surat/view" : "" ?>';

const SURAT_LABELS = {
    SP: { label: 'Surat Permohonan', icon: 'bi-file-earmark-text', color: '#0d6efd', bg: '#e7f1ff' },
    SK: { label: 'Surat Keterangan', icon: 'bi-file-earmark-check', color: '#198754', bg: '#d1e7dd' },
    SJ: { label: 'Surat Jaminan', icon: 'bi-shield-check', color: '#fd7e14', bg: '#fff3cd' },
    ST: { label: 'Surat Tugas', icon: 'bi-person-badge', color: '#6f42c1', bg: '#e8d5f5' },
};

const bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function formatTglIndo(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.getDate() + ' ' + bulanIndo[d.getMonth()] + ' ' + d.getFullYear();
}

function escHtml(s) {
    if (s === null || s === undefined) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

// ========== WIZARD NAV ==========
function goToStep(step) {
    if (step === 2 && getPickedKds().length === 0) {
        Swal.fire({
            title: 'Buat Surat Umum?',
            text: 'Anda tidak memilih santri satupun. Sistem akan memproses ini sebagai Surat Umum (Tanpa Lampiran Santri). Lanjutkan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjut',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                proceedToStep2();
            }
        });
        return;
    }
    if (step === 3 && !currentMailingData) {
        Swal.fire('Peringatan', 'Buat mailing terlebih dahulu.', 'warning');
        return;
    }
    
    if (step === 2) {
        proceedToStep2();
    } else {
        doGoToStep(step);
    }
}

function proceedToStep2() {
    updateStep2Summary();
    doGoToStep(2);
}

function doGoToStep(step) {
    currentStep = step;
    document.querySelectorAll('.wizard-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step' + step).classList.add('active');

    document.querySelectorAll('.wizard-step').forEach(s => {
        const sn = parseInt(s.dataset.step);
        s.classList.remove('active', 'completed');
        if (sn === step) s.classList.add('active');
        else if (sn < step) s.classList.add('completed');
    });
}

// ========== MODE SELECT ==========
function selectMode(mode) {
    selectedMode = mode;
    document.getElementById('selectedMode').value = mode;
    document.querySelectorAll('.mode-option').forEach(o => o.classList.remove('selected'));
    document.getElementById(mode === 'sekaligus' ? 'modeSekaligus' : 'modePerseorangan').classList.add('selected');
}

// ========== SANTRI PICK ==========
function getPickedKds() {
    if (pickTable) {
        let kdsList = [];
        pickTable.$('.pick-cb:checked').each(function() {
            kdsList.push(this.value);
        });
        return kdsList;
    }
    return Array.from(document.querySelectorAll('.pick-cb:checked')).map(cb => cb.value);
}

function getPickedSantriData() {
    if (pickTable) {
        let santris = [];
        pickTable.$('.pick-cb:checked').each(function() {
            santris.push({
                kds: this.value,
                nama: this.dataset.nama,
                kelas: this.dataset.kelas,
                pondok: this.dataset.pondok,
                negara: this.dataset.negara,
                kewarganegaraan: this.dataset.kwn,
                tempat_lahir: this.dataset.tl,
                tanggal_lahir: this.dataset.tgl,
                alamat: this.dataset.alamat,
                no_paspor: this.dataset.paspor,
                exp_paspor: this.dataset.expPaspor,
                exp_itas: this.dataset.expItas,
            });
        });
        return santris;
    }
    return Array.from(document.querySelectorAll('.pick-cb:checked')).map(cb => ({
        kds: cb.value,
        nama: cb.dataset.nama,
        kelas: cb.dataset.kelas,
        pondok: cb.dataset.pondok,
        negara: cb.dataset.negara,
        kewarganegaraan: cb.dataset.kwn,
        tempat_lahir: cb.dataset.tl,
        tanggal_lahir: cb.dataset.tgl,
        alamat: cb.dataset.alamat,
        no_paspor: cb.dataset.paspor,
        exp_paspor: cb.dataset.expPaspor,
        exp_itas: cb.dataset.expItas,
    }));
}

function updatePickCount() {
    const c = getPickedKds().length;
    document.getElementById('countPicked').textContent = c;
    document.getElementById('btnToStep2').disabled = false;
}

// ========== STEP 2 SUMMARY ==========
function updateSantriNumbers() {
    const items = document.querySelectorAll('#summarySantriList .santri-item');
    items.forEach((item, index) => {
        const badge = item.querySelector('.number-badge');
        if (badge) badge.textContent = index + 1;
    });
}

function updateStep2Summary() {
    const santris = getPickedSantriData();
    
    if (santris.length === 0) {
        selectMode('sekaligus');
        document.getElementById('modePerseorangan').style.opacity = '0.5';
        document.getElementById('modePerseorangan').style.pointerEvents = 'none';
    } else {
        document.getElementById('modePerseorangan').style.opacity = '1';
        document.getElementById('modePerseorangan').style.pointerEvents = 'auto';
    }

    const mode = selectedMode;
    document.getElementById('summaryMode').textContent = mode === 'sekaligus' ? 'Sekaligus (Kolektif)' : 'Perseorangan';
    const sortBy = document.getElementById('inpSortBy') ? document.getElementById('inpSortBy').value : 'nama ASC';
    
    // Only sort if not manual, because manual means they've dragged them or we just use default
    if (sortBy !== 'manual') {
        santris.sort((a, b) => {
            if (sortBy === 'nama ASC') return (a.nama||'').localeCompare(b.nama||'');
            if (sortBy === 'negara ASC') return (a.negara||'').localeCompare(b.negara||'');
            if (sortBy === 'exp_itas ASC') return (a.exp_itas||'').localeCompare(b.exp_itas||'');
            if (sortBy === 'no_paspor ASC') return (a.no_paspor||'').localeCompare(b.no_paspor||'');
            return 0;
        });
    }

    let html = '';
    santris.forEach((s, i) => {
        let extraInfo = s.kelas || '-';
        if (sortBy === 'negara ASC') extraInfo = s.negara || '-';
        if (sortBy === 'exp_itas ASC') extraInfo = s.exp_itas ? 'Exp: ' + s.exp_itas : 'Exp: -';
        if (sortBy === 'no_paspor ASC') extraInfo = s.no_paspor || '-';

        html += `<div class="santri-item d-flex align-items-center gap-2 py-1 border-bottom" data-kds="${s.kds}" style="cursor: grab;">
            <i class="bi bi-grip-vertical text-muted"></i>
            <span class="badge bg-secondary rounded-pill number-badge">${i+1}</span>
            <span class="fw-medium">${s.nama}</span>
            <span class="text-muted small">${extraInfo}</span>
        </div>`;
    });
    const listEl = document.getElementById('summarySantriList');
    if (santris.length === 0) {
        listEl.innerHTML = '<div class="text-muted fst-italic fw-medium text-center py-3 bg-light rounded"><i class="bi bi-file-earmark-text me-1"></i> Surat Umum (Tanpa Santri)</div>';
    } else {
        listEl.innerHTML = html;
    }

    // Init SortableJS for manual dragging
    if (window.santriSortable) window.santriSortable.destroy();
    if (santris.length > 0) {
        window.santriSortable = new Sortable(listEl, {
            multiDrag: true,
            selectedClass: 'bg-warning-subtle', // class when clicked for multidrag
            animation: 150,
            fallbackTolerance: 3,
            onEnd: function () {
                document.getElementById('inpSortBy').value = 'manual';
                updateSantriNumbers();
            }
        });
    }
}

function onJenisChange() {
    const sel = document.getElementById('selJenisPengajuan');
    const opt = sel.options[sel.selectedIndex];
    const suratNeeded = (opt?.dataset.surat || '').split(',').filter(Boolean);

    let html = '';
    suratNeeded.forEach(t => {
        const tr = t.trim();
        const s = SURAT_LABELS[tr];
        if (s) {
            html += `<span class="badge rounded-pill px-3 py-1 fw-medium" style="background:${s.bg};color:${s.color};border:1px solid ${s.color}20;">${s.label}</span>`;
        } else {
            html += `<span class="badge rounded-pill px-3 py-1 fw-medium bg-secondary text-white border" style="font-size:0.8rem;">${tr.replace(/_/g, ' ')}</span>`;
        }
    });
    document.getElementById('summarySuratNeeded').innerHTML = html || '<span class="text-muted small">Pilih jenis pengajuan</span>';
    document.getElementById('btnBuatMailing').disabled = !sel.value;
}

// ========== BUAT MAILING ==========
async function buatMailing() {
    const jpId = document.getElementById('selJenisPengajuan').value;
    const tglSurat = document.getElementById('inpTanggalSurat').value;
    const catatan = document.getElementById('inpCatatan').value;
    const sortBy = document.getElementById('inpSortBy') ? document.getElementById('inpSortBy').value : 'nama ASC';

    let kdsList = [];
    if (sortBy === 'manual') {
        const items = document.querySelectorAll('#summarySantriList .santri-item');
        items.forEach(el => kdsList.push(el.dataset.kds));
    } else {
        kdsList = getPickedKds();
    }

    if (!jpId) {
        Swal.fire('Peringatan', 'Pilih jenis pengajuan terlebih dahulu.', 'warning');
        return;
    }

    const btn = document.getElementById('btnBuatMailing');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    Swal.fire({ title:'Membuat Mailing...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

    const payload = {
        jenis_pengajuan_id: jpId,
        mode: selectedMode,
        tanggal_surat: tglSurat,
        catatan: catatan,
        sort_by: sortBy,
        kds_list: kdsList
    };

    const instEl = document.getElementById('inpInstansiId');
    if (instEl) {
        payload.instansi_id = instEl.value;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('<?= API_URL ?>/api/surat/create-mailing', {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        currentMailingData = data;
        Swal.fire('Berhasil!', data.message, 'success');
        setupStep3();
        goToStep(3);
    } catch(e) {
        Swal.fire('Gagal', e.message || 'Terjadi kesalahan.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Buat Mailing & Lanjut';
    }
}

// ========== STEP 3: GENERATE ==========
// Template file map sesuai nama file nyata di folder Surat_Menyurat
const TEMPLATE_MAP = {
    'perseorangan': {
        'SP': { label: 'Surat Permohonan',  file: 'Surat_Permohonan_Satu_Orang.docx',  icon: 'bi-file-text',      bg: '#EBF0FF', color: '#3B5BDB' },
        'SK': { label: 'Surat Keterangan',  file: 'Surat_Keterangan_Satu_Orang.docx',  icon: 'bi-patch-check',    bg: '#E6FCF5', color: '#0CA678' },
        'SJ': { label: 'Surat Jaminan',     file: 'Surat_Jaminan_Satu_Orang.docx',     icon: 'bi-shield-check',   bg: '#FFF4E6', color: '#E67700' },
        'ST': { label: 'Surat Tugas',       file: 'Surat_Tugas.docx',                  icon: 'bi-briefcase',      bg: '#F3F0FF', color: '#7950F2' },
    },
    'sekaligus': {
        'SP': { label: 'Permohonan (Kolektif)',  file: 'Surat_Permohonan_Banyak_Orang.docx',  icon: 'bi-file-text',      bg: '#EBF0FF', color: '#3B5BDB' },
        'SK': { label: 'Keterangan (Kolektif)', file: 'Surat_Keterangan_Banyak_Orang.docx',  icon: 'bi-patch-check',    bg: '#E6FCF5', color: '#0CA678' },
        'SJ': { label: 'Jaminan (Kolektif)',    file: 'Surat_Jaminan_Banyak_Orang.docx',     icon: 'bi-shield-check',   bg: '#FFF4E6', color: '#E67700' },
        'ST': { label: 'Surat Tugas',           file: 'Surat_Tugas.docx',                    icon: 'bi-briefcase',      bg: '#F3F0FF', color: '#7950F2' },
    }
};

function setupStep3() {
    if (!currentMailingData) return;
    const m = currentMailingData.mailing;
    document.getElementById('step3KodeMailing').textContent = m.kode_mailing;

    // Gunakan surat_dibutuhkan dari database
    const requiredSuratStr = m.surat_dibutuhkan || '';
    let suratTypes = requiredSuratStr.split(',').map(s => s.trim()).filter(s => s);
    
    // Fallback jika kosong
    if (suratTypes.length === 0) {
        suratTypes = ['SP', 'SK', 'SJ', 'ST'];
    }

    const mode = m.mode || 'perseorangan';
    const modeMap = TEMPLATE_MAP[mode] || TEMPLATE_MAP['perseorangan'];

    let html = '';
    suratTypes.forEach(t => {
        let s = modeMap[t];
        if (!s) {
            // Dynamic template
            s = {
                label: t.replace(/_/g, ' '),
                file: t + (mode === 'sekaligus' ? '_Banyak_Orang' : '_Satu_Orang') + '.docx',
                icon: 'bi-file-word',
                bg: '#f8f9fa',
                color: '#212529'
            };
        }
        
        let generatedBadge = '';
        if (currentMailingData && currentMailingData.surats) {
            let generatedSurat = currentMailingData.surats.find(surat => surat.tipe_surat === t);
            if (generatedSurat) {
                generatedBadge = `<div class="badge bg-success bg-opacity-10 text-success mt-1 border border-success border-opacity-25" style="font-size: 0.65rem;"><i class="bi bi-check2-all me-1"></i>Sudah di-generate (${generatedSurat.nomor_surat})</div>`;
            }
        }

        html += `<div class="surat-type-card d-flex align-items-center gap-3" onclick="selectSuratType('${t}')" id="stCard_${t}">
            <div class="surat-type-icon" style="background:${s.bg};color:${s.color}; border:1px solid #dee2e6;"><i class="bi ${s.icon}"></i></div>
            <div>
                <div class="fw-bold small">${s.label}</div>
                <div class="text-muted" style="font-size:.7rem;">${s.file}</div>
                ${generatedBadge}
            </div>
        </div>`;
    });
    document.getElementById('suratTypeCards').innerHTML = html;
}

function selectSuratType(type) {
    currentSuratType = type;
    document.querySelectorAll('.surat-type-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('stCard_' + type)?.classList.add('selected');

    // Auto-generate nomor
    const year = new Date().getFullYear();
    const month = new Date().getMonth() + 1;
    const romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][month - 1];
    const nextNo = (suratCounters[type] || 1);
    const nomor = String(nextNo).padStart(3, '0');
    
    // Buat singkatan untuk tipe surat dinamis
    let abbrev = type;
    if (type.length > 3 && !['SP', 'SK', 'SJ', 'ST'].includes(type)) {
        abbrev = type.split('_').map(w => w.charAt(0)).join('').toUpperCase().substring(0, 3);
    }
    
    document.getElementById('inpNomorUrut').value = nomor;
    document.getElementById('inpNomorSuffix').textContent = `/${abbrev}/PLN/${romawi}/${year}`;
    document.getElementById('lastNomorInfo').textContent = `(Terakhir: ${nextNo > 1 ? String(nextNo - 1).padStart(3, '0') : '000'})`;

    // Populate metadata form with default data from mailing
    const m = currentMailingData.mailing;
    
    // Auto-select dropdown user if matches current user
    const selPetugas = document.getElementById('inpPetugasId');
    if (selPetugas) {
        let found = false;
        for(let i=0; i<selPetugas.options.length; i++){
            if(selPetugas.options[i].value == currentUserData.id){
                selPetugas.selectedIndex = i;
                found = true;
                break;
            }
        }
        updatePetugasTtl();
    }

    const lampiranGroup = document.getElementById('lampiranCheckboxGroup');
    if (m.mode === 'sekaligus' && type !== 'ST') {
        lampiranGroup.style.display = 'block';
        // Hanya menyala default jika Permohonan
        document.getElementById('inpWithLampiran').checked = (type === 'SP');
    } else {
        lampiranGroup.style.display = 'none';
        document.getElementById('inpWithLampiran').checked = false;
    }

    document.getElementById('inpHal').value = m.hal || '';
    document.getElementById('inpKepada').value = m.kepada || '';
    document.getElementById('inpTempat').value = m.tempat || '';
    document.getElementById('inpIsi').value = m.isi || '';
    
    if (type === 'ST') {
        document.getElementById('konvensionalFields').style.display = 'none';
        document.getElementById('petugasGroup').style.display = 'block';
    } else {
        document.getElementById('konvensionalFields').style.display = 'block';
        document.getElementById('petugasGroup').style.display = 'none';
    }

    document.getElementById('metadataSuratGroup').style.display = 'block';
    document.getElementById('btnBukaTemplate').style.display = 'block';
    document.getElementById('btnGenerateSurat').disabled = false;
    
    // Render dynamic collection fields if schema exists
    const schema = currentMailingData.templateSchemas ? currentMailingData.templateSchemas[type] : null;
    const collContainer = document.getElementById('dynamicCollectionFields');
    collContainer.innerHTML = '';
    
    if (schema && schema.length > 0) {
        collContainer.classList.remove('d-none');
        let html = '<h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Data Collection (Tabel Dinamis)</h6>';
        
        schema.forEach((coll) => {
            html += `
            <div class="card shadow-sm border-0 mb-3 bg-light">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-secondary small">${coll.name}</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="addCollectionRow('${coll.name}', '${coll.columns.join(',')}')">
                            <i class="bi bi-plus"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-0" id="tblColl_${coll.name}">
                            <thead class="table-light">
                                <tr>
                                    ${coll.columns.map(c => `<th class="small text-muted fw-bold">${c}</th>`).join('')}
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;
        });
        collContainer.innerHTML = html;
        
        let existingData = null;
        if (currentMailingData.surats) {
            const extSurat = currentMailingData.surats.find(s => s.tipe_surat === type);
            if (extSurat && extSurat.json_dynamic_data) {
                try {
                    existingData = typeof extSurat.json_dynamic_data === 'string' ? JSON.parse(extSurat.json_dynamic_data) : extSurat.json_dynamic_data;
                } catch(e) {}
            }
        }

        schema.forEach(coll => {
            if (existingData && existingData[coll.name] && existingData[coll.name].length > 0) {
                existingData[coll.name].forEach(rowData => {
                    const tr = addCollectionRow(coll.name, coll.columns.join(','));
                    if (tr) {
                        tr.querySelectorAll('input').forEach(inp => {
                            const col = inp.dataset.col;
                            if (rowData[col] !== undefined) {
                                inp.value = rowData[col];
                            }
                        });
                    }
                });
            } else {
                addCollectionRow(coll.name, coll.columns.join(','));
            }
        });
    } else {
        collContainer.classList.add('d-none');
    }
    
    // Render dynamic custom input fields if schema exists
    const customSchema = currentMailingData.templateCustomInputs ? currentMailingData.templateCustomInputs[type] : null;
    const ciContainer = document.getElementById('dynamicCustomInputFields');
    ciContainer.innerHTML = '';
    
    if (customSchema && customSchema.length > 0) {
        ciContainer.classList.remove('d-none');
        let html = '<h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Variabel Kustom</h6>';
        
        // Get existing data if any
        let existingCiData = null;
        if (currentMailingData.surats) {
            const extSurat = currentMailingData.surats.find(s => s.tipe_surat === type);
            if (extSurat && extSurat.custom_inputs_data) {
                try {
                    existingCiData = typeof extSurat.custom_inputs_data === 'string' ? JSON.parse(extSurat.custom_inputs_data) : extSurat.custom_inputs_data;
                } catch(e) {}
            }
        }
        
        customSchema.forEach((ci) => {
            const value = existingCiData && existingCiData[ci.name] ? existingCiData[ci.name] : '';
            html += `<div class="mb-3">
                <label class="form-label fw-bold text-muted small" style="letter-spacing:.5px;">${ci.label.toUpperCase()}</label>`;
            
            if (ci.type === 'textarea') {
                html += `<textarea class="form-control border shadow-sm rounded-3 py-2 dynamic-custom-input" rows="2" data-name="${ci.name}" placeholder="Masukkan ${ci.label}">${escHtml(value)}</textarea>`;
            } else if (ci.type === 'date') {
                html += `<input type="date" class="form-control border shadow-sm rounded-3 py-2 dynamic-custom-input" data-name="${ci.name}" value="${escHtml(value)}">`;
            } else {
                html += `<input type="text" class="form-control border shadow-sm rounded-3 py-2 dynamic-custom-input" data-name="${ci.name}" placeholder="Masukkan ${ci.label}" value="${escHtml(value)}">`;
            }
            html += `</div>`;
        });
        ciContainer.innerHTML = html;
    } else {
        ciContainer.classList.add('d-none');
    }
    
    // Update Preview Area with generated files if already exists
    const previewArea = document.getElementById('suratPreviewArea');
    let generatedFilesForType = [];
    if (currentMailingData && currentMailingData.generatedFiles) {
        generatedFilesForType = currentMailingData.generatedFiles.filter(f => f.tipe_surat === type);
    }
    
    if (generatedFilesForType.length > 0) {
        let filesHtml = '<div class="d-flex flex-wrap gap-2 justify-content-center mt-3">';
        generatedFilesForType.forEach(f => {
            filesHtml += `<a href="${f.url}" target="_blank" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 text-decoration-none shadow-sm hover-shadow" style="font-size: .85rem;">
                <i class="bi bi-file-pdf me-1"></i>${f.name}
            </a>`;
        });
        filesHtml += '</div>';
        
        previewArea.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 p-5 bg-light rounded-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem; opacity: 0.8; margin-bottom: 1rem;"></i>
                <h5 class="fw-bold text-dark">Surat Telah Di-generate</h5>
                <p class="text-muted small mb-3">Surat ini sudah di-generate sebelumnya. Anda dapat mengunduhnya di bawah ini, atau mengubah data di panel kiri lalu klik <strong>Generate Surat</strong> untuk membuat ulang.</p>
                ${filesHtml}
            </div>
        `;
    } else {
        previewArea.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 text-muted p-5 bg-light rounded-4">
                <i class="bi bi-file-earmark-pdf" style="font-size: 5rem; opacity: 0.2; margin-bottom: 1rem;"></i>
                <h4 class="fw-bold">Pratinjau Surat</h4>
                <p class="mb-0">Silakan isi data yang diperlukan di panel kiri, lalu klik <strong>Generate Surat</strong> untuk melihat hasilnya di sini.</p>
            </div>
        `;
    }
}

function addCollectionRow(name, colsStr) {
    const cols = colsStr.split(',');
    const tbody = document.querySelector(`#tblColl_${name} tbody`);
    if (!tbody) return null;
    
    const tr = document.createElement('tr');
    let tds = '';
    cols.forEach(c => {
        tds += `<td><input type="text" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="${c}" data-col="${c}"></td>`;
    });
    tds += `<td class="text-center align-middle"><button class="btn btn-sm text-danger p-0 m-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>`;
    tr.innerHTML = tds;
    tbody.appendChild(tr);
    return tr;
}

    function updatePetugasTtl() {
        const sel = document.getElementById('inpPetugasId');
        if (!sel || sel.selectedIndex < 0) return;
        const ttl = sel.options[sel.selectedIndex].getAttribute('data-ttl') || '';
        document.getElementById('inpTtl').value = ttl;
    }
    
    async function bukaTemplate() {
        if (!currentSuratType || !currentMailingData) return;
        const mailing_id = currentMailingData.mailing.id;
        const tipe = currentSuratType;
        
        const btn = document.getElementById('btnBukaTemplate');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membuka...';
        btn.disabled = true;

        try {
            const res = await fetch(`<?= API_URL ?>/api/surat/download-template?mailing_id=${mailing_id}&tipe=${tipe}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            // Sukses buka di background
        } catch(e) {
            Swal.fire('Gagal Membuka Template', e.message, 'error');
        } finally {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    }

    async function generateSurat() {
    if (!currentMailingData || !currentSuratType) return;
    const m = currentMailingData.mailing;

    Swal.fire({ title:'Generating surat...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const nomorSurat = String(document.getElementById('inpNomorUrut').value).padStart(3, '0') + document.getElementById('inpNomorSuffix').textContent;
        const hal = document.getElementById('inpHal').value;
        
        let kepada = document.getElementById('inpKepada').value;
        let tempat = document.getElementById('inpTempat').value;
        let isi = document.getElementById('inpIsi').value;
        
        if (currentSuratType === 'ST') {
            kepada = document.getElementById('inpPetugasId').value;
            // Tempat dan isi diabaikan untuk ST, karena di-handle backend
        }
        const withLampiran = document.getElementById('inpWithLampiran').checked ? 1 : 0;
        
        let jsonDynamicData = null;
        if (currentMailingData.templateSchemas && currentMailingData.templateSchemas[currentSuratType]) {
            const schema = currentMailingData.templateSchemas[currentSuratType];
            jsonDynamicData = {};
            schema.forEach(coll => {
                const tbody = document.querySelector(`#tblColl_${coll.name} tbody`);
                if (tbody) {
                    const rows = [];
                    tbody.querySelectorAll('tr').forEach(tr => {
                        const rowData = {};
                        tr.querySelectorAll('input').forEach(inp => {
                            rowData[inp.dataset.col] = inp.value;
                        });
                        // Only add row if at least one field has value
                        if (Object.values(rowData).some(v => v.trim() !== '')) {
                            rows.push(rowData);
                        }
                    });
                    jsonDynamicData[coll.name] = rows;
                }
            });
        }
        let customInputsData = null;
        const ciInputs = document.querySelectorAll('.dynamic-custom-input');
        if (ciInputs.length > 0) {
            customInputsData = {};
            ciInputs.forEach(inp => {
                customInputsData[inp.dataset.name] = inp.value;
            });
        }

        const res = await fetch('<?= API_URL ?>/api/surat/generate', {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-Token': csrfToken
            },
            body:JSON.stringify({
                mailing_id: m.id,
                tipe_surat: currentSuratType,
                nomor_surat: nomorSurat,
                tanggal_surat: m.tanggal_surat,
                hal: hal,
                kepada: kepada,
                tempat: tempat,
                isi: isi,
                with_lampiran: withLampiran,
                json_dynamic_data: jsonDynamicData ? JSON.stringify(jsonDynamicData) : null,
                custom_inputs_data: customInputsData ? JSON.stringify(customInputsData) : null
            })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        // Increment counter locally
        suratCounters[currentSuratType] = (suratCounters[currentSuratType] || 1) + 1;

        Swal.close();
        
        if (data.is_docx && data.download_url) {
            // Setup Progress Tracker
            const progressId = Date.now() + Math.random().toString(36).substr(2, 5);
            const dlUrl = data.download_url + (data.download_url.includes('?') ? '&' : '?') + 'progress_id=' + progressId;

            let pollInterval = null;

            Swal.fire({ 
                title: 'Menyiapkan PDF...', 
                html: '<div id="progress-text" class="mt-2 text-primary fw-bold" style="font-size: 0.9rem;">Menghubungkan ke sistem...</div>',
                allowOutsideClick: false, 
                didOpen: () => {
                    Swal.showLoading();
                    pollInterval = setInterval(async () => {
                        try {
                            const pRes = await fetch('<?= ASSET_URL ?>/assets/progress/progress_' + progressId + '.json');
                            if (pRes.ok) {
                                const pData = await pRes.json();
                                const pEl = document.getElementById('progress-text');
                                if (pEl && pData.message) pEl.innerText = pData.message;
                            }
                        } catch (e) {} // ignore 404s
                    }, 1000);
                },
                willClose: () => {
                    if (pollInterval) clearInterval(pollInterval);
                }
            });

            try {
                const dlRes = await fetch(dlUrl);
                const contentType = dlRes.headers.get('Content-Type') || '';

                if (!dlRes.ok || contentType.includes('json') || contentType.includes('text/html')) {
                    // Error — read body as text ONCE, then try to JSON.parse
                    const rawText = await dlRes.text();
                    
                    // Jika response berupa halaman HTML (misal dari Yiisoft Error Handler)
                    if (rawText.toLowerCase().includes('<html')) {
                        if (pollInterval) clearInterval(pollInterval);
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            html: `<div style="background:#f8f9fa; padding:10px; border-radius:8px; border:1px solid #dee2e6;"><iframe srcdoc="${rawText.replace(/"/g, '&quot;')}" style="width:100%; height:450px; border:none;"></iframe></div>`,
                            width: '800px',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Tutup'
                        });
                        return; // Halt further execution
                    }
                    
                    let errMsg = rawText || 'Gagal mengunduh file.';
                    try {
                        const errData = JSON.parse(rawText);
                        errMsg = errData.message || errMsg;
                    } catch(_) { /* not JSON, use raw text */ }
                    throw new Error(errMsg);
                }

                // Success — body read as blob ONCE (not touched above)
                const blob = await dlRes.blob();
                const blobUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = blobUrl;
                const ext = contentType.includes('zip') ? 'zip' : 'pdf';
                a.download = `${data.surat.tipe_surat}_${m.kode_mailing}.${ext}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(blobUrl);

                Swal.close();
                document.getElementById('suratPreviewArea').innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 text-muted p-5 bg-white rounded-4 shadow-sm border">
                        <i class="bi bi-file-earmark-check-fill text-success" style="font-size: 5rem; margin-bottom: 1rem;"></i>
                        <h4 class="fw-bold text-dark">PDF Berhasil Diunduh!</h4>
                        <p class="mb-0 text-secondary">File PDF telah tersimpan otomatis ke folder instansi Anda.</p>
                    </div>`;
            } catch (dlErr) {
                Swal.fire('Gagal Generate PDF', dlErr.message || 'Terjadi kesalahan saat generate.', 'error');
            }
        } else {
            renderSuratPreview(data.surat, currentMailingData.santris, m);
            document.getElementById('btnCetakSurat').style.display = 'block';
        }
    } catch(e) {
        Swal.fire('Gagal', e.message || 'Terjadi kesalahan.', 'error');
    }
}

// ========== RENDER SURAT PREVIEW ==========
function renderSuratPreview(surat, santris, mailing) {
    const mode = mailing.mode;
    const tipeSurat = surat.tipe_surat;
    const container = document.getElementById('suratPreviewArea');

    if (tipeSurat === 'ST') {
        // Surat Tugas — individu staf
        container.innerHTML = renderSuratTugas(surat, mailing);
    } else if (mode === 'sekaligus') {
        container.innerHTML = renderSuratKolektif(surat, santris, mailing);
    } else {
        // Perseorangan: satu surat per santri
        let allHtml = '';
        santris.forEach((s, i) => {
            allHtml += renderSuratIndividu(surat, s, mailing, i);
        });
        container.innerHTML = allHtml;
    }
}

function getKopHtml() {
    if (kopSuratUrl) {
        return `<img src="${kopSuratUrl}" class="kop-img" alt="Kop Surat"><hr style="border-top:2px solid #000;margin:0 0 16px;">`;
    }
    return `<div class="text-center mb-3"><h5 class="fw-bold mb-0" style="text-transform:uppercase;">${instansiData.nama_instansi || 'INSTANSI'}</h5><p class="mb-0 small">${instansiData.kepengurusan || ''}</p></div><hr style="border-top:2px solid #000;margin:0 0 16px;">`;
}

function getSuratTitle(tipe) {
    const titles = { SP:'SURAT PERMOHONAN', SK:'SURAT KETERANGAN', SJ:'SURAT JAMINAN', ST:'SURAT TUGAS' };
    return titles[tipe] || 'SURAT';
}

function renderSuratKolektif(surat, santris, mailing) {
    let lampiran = '<table class="data-table" style="width:100%;border-collapse:collapse;margin:16px 0;">';
    lampiran += '<tr style="border-bottom:2px solid #000;"><th style="padding:4px 8px;text-align:left;">No</th><th style="padding:4px 8px;text-align:left;">Nama</th><th style="padding:4px 8px;text-align:left;">Kelas</th><th style="padding:4px 8px;text-align:left;">Negara</th><th style="padding:4px 8px;text-align:left;">No Paspor</th></tr>';
    santris.forEach((s, i) => {
        lampiran += `<tr style="border-bottom:1px solid #ddd;"><td style="padding:4px 8px;">${i+1}</td><td style="padding:4px 8px;">${s.nama}</td><td style="padding:4px 8px;">${s.kelas||'-'}</td><td style="padding:4px 8px;">${s.negara||'-'}</td><td style="padding:4px 8px;">${s.no_paspor||'-'}</td></tr>`;
    });
    lampiran += '</table>';

    return `<div class="surat-paper">
        ${getKopHtml()}
        <div class="surat-body">
            <table style="width:100%;margin-bottom:16px;"><tr>
                <td style="width:50%">Nomor : ${surat.nomor_surat}<br>Hal : <strong>${surat.hal}</strong></td>
                <td style="width:50%;text-align:right;">${formatTglIndo(surat.tanggal_surat)}</td>
            </tr></table>
            <p>Kepada Yth.<br><strong>${surat.kepada}</strong><br>di ${surat.tempat || 'Tempat'}</p>
            <p style="text-align:center;margin:20px 0;"><strong><u>${getSuratTitle(surat.tipe_surat)}</u></strong></p>
            <p><em>Assalamu'alaikum Wr. Wb.</em></p>
            <p>${surat.isi}</p>
            <p>Adapun nama-nama santri tersebut adalah sebagai berikut:</p>
            ${lampiran}
            <p>Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
            <p><em>Wassalamu'alaikum Wr. Wb.</em></p>
            <div style="text-align:right;margin-top:40px;">
                <div style="display:inline-block;text-align:center;min-width:200px;">
                    <p style="margin-bottom:60px;">Hormat kami,</p>
                    <p style="font-weight:bold;text-decoration:underline;">${currentUserData.nama_lengkap || '.....................'}</p>
                    <p>${instansiData.nama_instansi || 'Pembimbing Luar Negeri'}</p>
                </div>
            </div>
        </div>
    </div>`;
}

function renderSuratIndividu(surat, santri, mailing, idx) {
    return `<div class="surat-paper" style="${idx > 0 ? 'margin-top:30px;' : ''}">
        ${getKopHtml()}
        <div class="surat-body">
            <table style="width:100%;margin-bottom:16px;"><tr>
                <td style="width:50%">Nomor : ${surat.nomor_surat}<br>Hal : <strong>${surat.hal}</strong></td>
                <td style="width:50%;text-align:right;">${formatTglIndo(surat.tanggal_surat)}</td>
            </tr></table>
            <p>Kepada Yth.<br><strong>${surat.kepada}</strong><br>di ${surat.tempat || 'Tempat'}</p>
            <p style="text-align:center;margin:20px 0;"><strong><u>${getSuratTitle(surat.tipe_surat)}</u></strong></p>
            <p><em>Assalamu'alaikum Wr. Wb.</em></p>
            <p>${surat.isi}</p>
            <table class="data-table" style="margin:8px 0 8px 30px;">
                <tr><td>Nama</td><td style="padding:2px 12px;">:</td><td>${santri.nama}</td></tr>
                <tr><td>Tempat, Tgl Lahir</td><td style="padding:2px 12px;">:</td><td>${santri.tempat_lahir || '-'}, ${formatTglIndo(santri.tanggal_lahir)}</td></tr>
                <tr><td>Kewarganegaraan</td><td style="padding:2px 12px;">:</td><td>${santri.kewarganegaraan || santri.negara || '-'}</td></tr>
                <tr><td>No. Paspor</td><td style="padding:2px 12px;">:</td><td>${santri.no_paspor || '-'}</td></tr>
                <tr><td>Alamat</td><td style="padding:2px 12px;">:</td><td>${santri.alamat || '-'}</td></tr>
            </table>
            <p>Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
            <p><em>Wassalamu'alaikum Wr. Wb.</em></p>
            <div style="text-align:right;margin-top:40px;">
                <div style="display:inline-block;text-align:center;min-width:200px;">
                    <p style="margin-bottom:60px;">Hormat kami,</p>
                    <p style="font-weight:bold;text-decoration:underline;">${currentUserData.nama_lengkap || '.....................'}</p>
                    <p>${instansiData.nama_instansi || 'Pembimbing Luar Negeri'}</p>
                </div>
            </div>
        </div>
    </div>`;
}

function renderSuratTugas(surat, mailing) {
    return `<div class="surat-paper">
        ${getKopHtml()}
        <div class="surat-body">
            <p style="text-align:center;margin:20px 0;"><strong><u>SURAT TUGAS</u></strong><br>Nomor: ${surat.nomor_surat}</p>
            <p>Yang bertanda tangan di bawah ini:</p>
            <table class="data-table" style="margin:8px 0 8px 30px;">
                <tr><td>Nama</td><td style="padding:2px 12px;">:</td><td>${currentUserData.nama_lengkap || '-'}</td></tr>
                <tr><td>NIK</td><td style="padding:2px 12px;">:</td><td>${currentUserData.nik || '-'}</td></tr>
                <tr><td>Tempat, Tgl Lahir</td><td style="padding:2px 12px;">:</td><td>${currentUserData.tempat_lahir || '-'}, ${formatTglIndo(currentUserData.tanggal_lahir)}</td></tr>
                <tr><td>Jabatan</td><td style="padding:2px 12px;">:</td><td>${instansiData.nama_instansi || '-'}</td></tr>
            </table>
            <p>Memberikan tugas kepada yang bersangkutan untuk:</p>
            <div style="margin:12px 0 12px 30px;padding:10px 16px;border-left:3px solid #333;background:#f9f9f9;">
                ${surat.isi || surat.hal || 'Melaksanakan tugas sebagaimana dimaksud.'}
            </div>
            <p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan sebaik-baiknya.</p>
            <div style="text-align:right;margin-top:40px;">
                <p>${formatTglIndo(surat.tanggal_surat)}</p>
                <div style="display:inline-block;text-align:center;min-width:200px;">
                    <p style="margin-bottom:60px;">Yang Memberi Tugas,</p>
                    <p style="font-weight:bold;text-decoration:underline;">${currentUserData.nama_lengkap || '.....................'}</p>
                    <p>${instansiData.nama_instansi || 'Pembimbing Luar Negeri'}</p>
                </div>
            </div>
        </div>
    </div>`;
}

function cetakSurat() {
    window.print();
}

// ========== RESET WIZARD ==========
function resetWizard() {
    currentStep = 1;
    currentMailingData = null;
    currentSuratType = null;
    document.querySelectorAll('.pick-cb').forEach(c => c.checked = false);
    document.getElementById('pickAll').checked = false;
    document.getElementById('selJenisPengajuan').value = '';
    document.getElementById('inpCatatan').value = '';
    document.getElementById('suratPreviewArea').innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i><div class="fw-medium">Pilih tipe surat dan klik "Generate" untuk melihat pratinjau</div></div>';
    document.getElementById('btnCetakSurat').style.display = 'none';
    updatePickCount();
    goToStep(1);
}

// ========== RIWAYAT MAILING ==========
async function openMailingDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalMailingDetail'));
    document.getElementById('modalMailingBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    try {
        const res = await fetch('<?= API_URL ?>/api/surat/mailing/' + id);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        const m = data.mailing;
        document.getElementById('modalMailingTitle').textContent = 'Detail Mailing: ' + m.kode_mailing;

        let hasGeneratedSurat = data.surats.length > 0;
        let baseSurat = hasGeneratedSurat ? data.surats[0] : null;

        let thNomorSurat = (m.mode === 'Perseorangan' && hasGeneratedSurat && baseSurat.nomor_surat) ? '<th>No Surat (Utama)</th>' : '';
        let santriHtml = `<table class="table table-sm table-striped mb-0" style="font-size:.8rem;"><thead><tr><th>#</th><th>Nama</th><th>Kelas</th><th>Negara</th><th>No Paspor</th>${thNomorSurat}</tr></thead><tbody>`;
        
        let baseNoStr = '';
        let baseNo = 0;
        let suffix = '';
        if (m.mode === 'Perseorangan' && hasGeneratedSurat && baseSurat.nomor_surat) {
            baseNoStr = baseSurat.nomor_surat.split('/')[0];
            baseNo = parseInt(baseNoStr, 10);
            suffix = baseSurat.nomor_surat.substring(baseNoStr.length);
        }

        data.santris.forEach((s, i) => {
            let tdNomorSurat = '';
            if (m.mode === 'Perseorangan' && hasGeneratedSurat && baseSurat.nomor_surat) {
                let currentNo = baseNo + i;
                let noSuratFormat = String(currentNo).padStart(3, '0') + suffix;
                tdNomorSurat = `<td><span class="badge bg-secondary">${noSuratFormat}</span></td>`;
            }
            santriHtml += `<tr><td>${i+1}</td><td class="fw-semibold">${s.nama}</td><td>${s.kelas||'-'}</td><td>${s.negara||'-'}</td><td><code>${s.no_paspor||'-'}</code></td>${tdNomorSurat}</tr>`;
        });
        santriHtml += '</tbody></table>';

        let suratHtml = '';
        if (data.generatedFiles && data.generatedFiles.length > 0) {
            suratHtml = '<div class="d-flex flex-wrap gap-2">';
            data.generatedFiles.forEach(f => {
                const sl = SURAT_LABELS[f.tipe_surat];
                suratHtml += `<a href="${f.url}" target="_blank" class="badge rounded-pill px-3 py-2 fw-medium text-decoration-none shadow-sm hover-shadow" style="background:${sl?.bg};color:${sl?.color};border:1px solid ${sl?.color}20;font-size:.8rem;">
                    <i class="bi bi-file-pdf me-1"></i>${f.name} — ${f.nomor_surat}
                </a>`;
            });
            suratHtml += '</div>';
        } else if (data.surats && data.surats.length > 0) {
            suratHtml = '<div class="d-flex flex-wrap gap-2">';
            data.surats.forEach(s => {
                const sl = SURAT_LABELS[s.tipe_surat];
                suratHtml += `<div class="badge rounded-pill px-3 py-2 fw-medium" style="background:${sl?.bg};color:${sl?.color};border:1px solid ${sl?.color}20;font-size:.8rem;">
                    <i class="bi ${sl?.icon} me-1"></i>${sl?.label} — ${s.nomor_surat}
                </div>`;
            });
            suratHtml += '</div>';
        } else {
            suratHtml = '<span class="text-muted small">Belum ada surat di-generate</span>';
        }

        // Button to load mailing into wizard
        let loadBtn = `<button class="btn btn-sm btn-primary rounded-pill px-3 mt-3" onclick="loadMailingToWizard(${id})"><i class="bi bi-arrow-right me-1"></i>Buka di Wizard & Generate Surat</button>`;

        document.getElementById('modalMailingBody').innerHTML = `
            <div class="row g-3 mb-3">
                <div class="col-md-4"><div class="text-muted small fw-bold">Jenis Pengajuan</div><div class="fw-semibold">${m.jenis_pengajuan||'-'}</div></div>
                <div class="col-md-3"><div class="text-muted small fw-bold">Mode</div><div class="fw-semibold">${m.mode==='sekaligus'?'Sekaligus (Kolektif)':'Perseorangan'}</div></div>
                <div class="col-md-3"><div class="text-muted small fw-bold">Tanggal Surat</div><div class="fw-semibold">${formatTglIndo(m.tanggal_surat)}</div></div>
                <div class="col-md-2"><div class="text-muted small fw-bold">Dibuat Oleh</div><div class="fw-semibold">${m.created_by_name||'-'}</div></div>
            </div>
            <h6 class="fw-bold text-dark mt-3 mb-2"><i class="bi bi-people text-primary me-2"></i>Daftar Santri (${data.santris.length})</h6>
            <div class="table-responsive border rounded-3 mb-3" style="max-height:350px;">${santriHtml}</div>
            <h6 class="fw-bold text-dark mt-3 mb-2"><i class="bi bi-file-earmark-check text-success me-2"></i>Surat yang Sudah Di-generate</h6>
            ${suratHtml}
            ${loadBtn}
        `;
    } catch(e) {
        document.getElementById('modalMailingBody').innerHTML = `<div class="text-center text-danger py-3">${e.message}</div>`;
    }
}

async function loadMailingToWizard(id) {
    bootstrap.Modal.getInstance(document.getElementById('modalMailingDetail'))?.hide();
    try {
        const res = await fetch('<?= API_URL ?>/api/surat/mailing/' + id);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        currentMailingData = data;
        // Switch to wizard tab
        document.querySelector('[data-bs-target="#tabWizard"]').click();
        setupStep3();
        goToStep(3);
    } catch(e) {
        Swal.fire('Gagal', e.message, 'error');
    }
}

async function deleteMailing(id, kode) {
    const result = await Swal.fire({
        title: 'Hapus Mailing?',
        html: `Mailing <strong>${kode}</strong> beserta semua surat yang sudah di-generate akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    });
    if (!result.isConfirmed) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('<?= API_URL ?>/api/surat/mailing/' + id + '/delete', { 
            method:'POST',
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
    } catch(e) {
        Swal.fire('Gagal', e.message, 'error');
    }
}

// ========== BULK DELETE MAILING ==========
async function bulkDeleteMailing() {
    const ids = Array.from(document.querySelectorAll('.mailing-cb:checked')).map(cb => cb.value);
    if (ids.length === 0) return;

    const result = await Swal.fire({
        title: 'Hapus Mailing Secara Masal?',
        html: `Anda akan menghapus <strong>${ids.length}</strong> mailing terpilih secara permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus Semua',
        cancelButtonText: 'Batal'
    });

    if (!result.isConfirmed) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('<?= API_URL ?>/api/surat/mailing/bulk-delete', {
            method: 'POST',
            headers: { 
                'X-CSRF-Token': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        });
        
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
    } catch(e) {
        Swal.fire('Gagal', e.message, 'error');
    }
}

function updateMailingPickCount() {
    const count = document.querySelectorAll('.mailing-cb:checked').length;
    document.getElementById('countSelectedMailing').textContent = count;
    const btn = document.getElementById('btnBulkDeleteMailing');
    if (count > 0) {
        btn.classList.remove('d-none');
    } else {
        btn.classList.add('d-none');
    }
}

// ========== JENIS PENGAJUAN CRUD ==========
function showJenisPengajuanModal() {
    const modal = new bootstrap.Modal(document.getElementById('modalJenisPengajuan'));
    modal.show();
    loadJPList();
    loadInstansiTujuanList();
}

async function loadInstansiTujuanList() {
    try {
        const res = await fetch('<?= API_URL ?>/api/surat/instansi-tujuan');
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        
        const sel = document.getElementById('jpKantor');
        const currentVal = sel.value;
        
        let html = '<option value="">Pilih Instansi...</option>';
        data.data.forEach(folder => {
            const displayName = folder.replace(/_/g, ' ');
            html += `<option value="${folder}">${displayName}</option>`;
        });
        
        sel.innerHTML = html;
        if (currentVal && data.data.includes(currentVal)) {
            sel.value = currentVal;
        }
    } catch (e) {
        console.error("Gagal memuat list instansi tujuan", e);
    }
}

async function onJPKantorChange() {
    const kantor = document.getElementById('jpKantor').value;
    const container = document.getElementById('dynamicTemplateCheckboxes');
    const hint = document.getElementById('dynamicTemplateHint');
    
    container.innerHTML = '';
    hint.innerHTML = '';
    
    if (!kantor) return;
    
    hint.innerHTML = '<i class="bi bi-hourglass-split"></i> Memuat template...';
    
    try {
        const res = await fetch(`<?= API_URL ?>/api/surat/instansi-tujuan/${encodeURIComponent(kantor)}/templates`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        
        if (data.data.length === 0) {
            hint.innerHTML = `<span class="text-muted"><i class="bi bi-info-circle"></i> Tidak ada custom template di folder ${kantor}</span>`;
            return;
        }
        
        hint.innerHTML = `<span class="text-primary"><i class="bi bi-check-circle"></i> Ditemukan ${data.data.length} custom template di folder ${kantor}</span>`;
        
        let html = '';
        let addedCoreNames = new Set();
        data.data.forEach(tpl => {
            const val = tpl.core_name;
            if (addedCoreNames.has(val)) return; // Only show one checkbox per core name (e.g. one for "Surat Perizinan")
            addedCoreNames.add(val);
            
            const id = 'cb_' + val;
            
            html += `
                <input type="checkbox" class="btn-check surat-cb" id="${id}" value="${val}">
                <label class="btn btn-outline-dark btn-sm rounded-pill px-3" for="${id}">
                    <i class="bi bi-file-word me-1"></i> ${tpl.display_name} 
                </label>
            `;
        });
        container.innerHTML = html;
        
        // Re-check currently checked boxes if we are in edit mode
        if (window.currentEditJP && window.currentEditJP.surat_dibutuhkan) {
            const arr = window.currentEditJP.surat_dibutuhkan.split(',');
            arr.forEach(v => {
                const cb = document.getElementById('cb_' + v.trim());
                if (cb) cb.checked = true;
            });
        }
        
    } catch (e) {
        hint.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Gagal memuat template: ${e.message}</span>`;
    }
}


async function loadJPList() {
    try {
        const res = await fetch('<?= API_URL ?>/api/surat/jenis-pengajuan');
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        let html = '<div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0" style="font-size:.82rem;"><thead class="table-light"><tr><th>Jenis Pengajuan</th><th>Perihal</th><th>Kepada</th><th>Surat</th><th class="text-center">Aksi</th></tr></thead><tbody>';
        data.data.forEach(jp => {
            const suratBadges = (jp.surat_dibutuhkan||'').split(',').map(s => {
                const t = s.trim();
                if (!t) return '';
                const sl = SURAT_LABELS[t];
                if (sl) {
                    return `<span class="badge" style="background:${sl.bg};color:${sl.color};font-size:.65rem;">${t}</span>`;
                } else {
                    return `<span class="badge bg-secondary" style="font-size:.65rem;">${t.replace(/_/g, ' ')}</span>`;
                }
            }).join(' ');
            html += `<tr>
                <td class="fw-semibold">${jp.jenis_pengajuan}</td>
                <td class="text-muted small">${jp.hal||'-'}</td>
                <td class="text-muted small">${jp.kepada||'-'}</td>
                <td>${suratBadges}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick='editJP(${JSON.stringify(jp)})'><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="deleteJP(${jp.id})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        document.getElementById('jpListContainer').innerHTML = html;
    } catch(e) {
        document.getElementById('jpListContainer').innerHTML = `<div class="text-danger small">${e.message}</div>`;
    }
}

function toggleJPForm() {
    const el = document.getElementById('jpFormContainer');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if (el.style.display === 'none') resetJPForm();
}

function resetJPForm() {
    document.getElementById('jpEditId').value = '';
    document.getElementById('jpNama').value = '';
    document.getElementById('jpHal').value = '';
    document.getElementById('jpKepada').value = '';
    document.getElementById('jpIsi').value = '';
    document.getElementById('jpOutputPath').value = '';
    document.getElementById('jpTempat').value = '';
    document.getElementById('jpKantor').value = '';
    document.getElementById('jpTemplate').value = '';
    
    document.getElementById('dynamicTemplateCheckboxes').innerHTML = '';
    document.getElementById('dynamicTemplateHint').innerHTML = 'Pilih Instansi Tujuan terlebih dahulu untuk melihat daftar template surat yang tersedia.';
}

function editJP(jp) {
    document.getElementById('jpEditId').value = jp.id;
    document.getElementById('jpNama').value = jp.jenis_pengajuan;
    document.getElementById('jpHal').value = jp.hal || '';
    document.getElementById('jpKepada').value = jp.kepada || '';
    document.getElementById('jpIsi').value = jp.isi || '';
    document.getElementById('jpOutputPath').value = jp.output_path || '';
    document.getElementById('jpTempat').value = jp.tempat || '';
    
    // Store for dynamic checking
    window.currentEditJP = jp;

    // Handle checkboxes for surat dibutuhkan
    document.querySelectorAll('.surat-cb').forEach(cb => cb.checked = false);
    if (jp.surat_dibutuhkan) {
        const arr = jp.surat_dibutuhkan.split(',');
        arr.forEach(val => {
            // Check default SP,SJ,SK,ST
            const cb = document.getElementById('cb' + val.trim());
            if (cb) cb.checked = true;
            // Note: dynamic custom templates are checked inside onJPKantorChange via window.currentEditJP
        });
    }

    // Set value for Kantor select and trigger change to load templates
    let k = jp.kantor || '';
    let select = document.getElementById('jpKantor');
    let optionExists = Array.from(select.options).some(opt => opt.value === k);
    if (!optionExists && k !== '') {
        let newOption = new Option(k, k);
        select.add(newOption);
    }
    select.value = k;
    onJPKantorChange();

    document.getElementById('jpFormContainer').style.display = 'block';
}

async function simpanJP() {
    const id = document.getElementById('jpEditId').value;
    const url = id ? `<?= API_URL ?>/api/surat/jenis-pengajuan/${id}/update` : '<?= API_URL ?>/api/surat/jenis-pengajuan/store';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        const formData = new FormData();
        formData.append('jenis_pengajuan', document.getElementById('jpNama').value);
        formData.append('hal', document.getElementById('jpHal').value);
        formData.append('kepada', document.getElementById('jpKepada').value);
        formData.append('isi', document.getElementById('jpIsi').value);
        formData.append('output_path', document.getElementById('jpOutputPath').value);
        formData.append('tempat', document.getElementById('jpTempat').value);
        
        // Gabungkan array checkbox yang di-check menjadi string dipisah koma
        const checkedSurat = Array.from(document.querySelectorAll('.surat-cb:checked'))
                                  .map(cb => cb.value)
                                  .join(',');
        formData.append('surat_dibutuhkan', checkedSurat);
        
        formData.append('kantor', document.getElementById('jpKantor').value);
        
        const fileInput = document.getElementById('jpTemplate');
        if (fileInput.files.length > 0) {
            formData.append('template_file', fileInput.files[0]);
        }

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: formData
        });
        
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        Swal.fire('Berhasil', data.message, 'success');
        resetJPForm();
        document.getElementById('jpFormContainer').style.display = 'none';
        loadJPList();
    } catch(e) {
        Swal.fire('Gagal', e.message, 'error');
    }
}

async function deleteJP(id) {
    const result = await Swal.fire({ title:'Hapus jenis pengajuan ini?', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Ya, Hapus' });
    if (!result.isConfirmed) return;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch(`<?= API_URL ?>/api/surat/jenis-pengajuan/${id}/delete`, { 
            method:'POST',
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        Swal.fire('Berhasil', data.message, 'success');
        loadJPList();
    } catch(e) {
        Swal.fire('Gagal', e.message, 'error');
    }
}

// ========== INIT ==========
$(document).ready(function() {
    // DataTable for santri picking
    pickTable = $('#santriPickTable').DataTable({
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "Semua"]],
        dom: "<'row mb-2 px-3 pt-3'<'col-md-6'l><'col-md-6 text-end'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row px-3 pb-3'<'col-md-5 mt-2'i><'col-md-7 mt-2'p>>",
        language: {
            search: "🔍 Cari:",
            lengthMenu: "Tampil _MENU_",
            info: "_START_ - _END_ dari _TOTAL_",
            paginate: { next:"→", previous:"←" }
        },
        columnDefs: [{ orderable:false, targets:[0] }],
        orderCellsTop: true,
    });

    // Handle column specific search
    $('.dt-search').on('keyup change clear', function() {
        const colIdx = $(this).data('col');
        if (pickTable.column(colIdx).search() !== this.value) {
            pickTable.column(colIdx).search(this.value).draw();
        }
    });

    // Checkbox events
    $('#pickAll').on('change', function() {
        const checked = this.checked;
        pickTable.rows({search:'applied'}).nodes().each(function() {
            $(this).find('.pick-cb').prop('checked', checked);
        });
        updatePickCount();
    });

    $('#santriPickTable').on('change', '.pick-cb', function() {
        if (!this.checked) $('#pickAll').prop('checked', false);
        updatePickCount();
    });

    // Drag to select logic
    let isDragging = false;
    let dragCheckValue = true;

    $('#santriPickTable tbody').on('mousedown', 'tr', function(e) {
        if ($(e.target).closest('input[type="checkbox"]').length > 0) return; // biarkan checkbox berfungsi normal
        
        isDragging = true;
        const cb = $(this).find('.pick-cb');
        if (cb.length > 0) {
            dragCheckValue = !cb.prop('checked');
            cb.prop('checked', dragCheckValue).trigger('change');
        }
        
        // Prevent text selection while dragging
        e.preventDefault();
    });

    $('#santriPickTable tbody').on('mouseenter', 'tr', function(e) {
        if (isDragging) {
            const cb = $(this).find('.pick-cb');
            if (cb.length > 0) {
                cb.prop('checked', dragCheckValue).trigger('change');
            }
        }
    });

    $(document).on('mouseup', function() {
        isDragging = false;
        isDraggingMailing = false;
    });

    // Mailing Table Init (if there is data)
    if ($('#mailingTable').length > 0) {
        const mailingTable = $('#mailingTable').DataTable({
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "Semua"]],
            dom: "<'row px-3 pt-3'<'col-md-6'l><'col-md-6 text-end'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row px-3 pb-3'<'col-md-5 mt-2'i><'col-md-7 mt-2'p>>",
            language: {
                search: "🔍 Cari:",
                lengthMenu: "Tampil _MENU_",
                info: "_START_ - _END_ dari _TOTAL_",
                paginate: { next:"→", previous:"←" }
            },
            columnDefs: [{ orderable:false, targets:[0, 7] }],
            order: [[3, 'desc']] // sort by Tanggal desc
        });

        $('#pickAllMailing').on('change', function() {
            const checked = this.checked;
            mailingTable.rows({search:'applied'}).nodes().each(function() {
                $(this).find('.mailing-cb').prop('checked', checked);
            });
            updateMailingPickCount();
        });

        $('#mailingTable').on('change', '.mailing-cb', function() {
            if (!this.checked) $('#pickAllMailing').prop('checked', false);
            updateMailingPickCount();
        });

        // Drag to select logic for Mailing Table
        let isDraggingMailing = false;
        let dragCheckValueMailing = true;

        $('#mailingTable tbody').on('mousedown', 'tr', function(e) {
            if ($(e.target).closest('input[type="checkbox"]').length > 0) return;
            if ($(e.target).closest('button').length > 0) return;
            if ($(e.target).closest('a').length > 0) return;
            
            isDraggingMailing = true;
            const cb = $(this).find('.mailing-cb');
            if (cb.length > 0) {
                dragCheckValueMailing = !cb.prop('checked');
                cb.prop('checked', dragCheckValueMailing).trigger('change');
            }
            e.preventDefault();
        });

        $('#mailingTable tbody').on('mouseenter', 'tr', function(e) {
            if (isDraggingMailing) {
                const cb = $(this).find('.mailing-cb');
                if (cb.length > 0) {
                    cb.prop('checked', dragCheckValueMailing).trigger('change');
                }
            }
        });
    }
});

function showBookmarkInfo() {
    Swal.fire({
        title: 'Bookmark/Filter Santri',
        html: '<div class="text-start fs-6"><p>Anda bisa menyeleksi beberapa santri lalu menyimpannya sebagai Bookmark/Grup, sehingga lain kali Anda hanya perlu memuat bookmark tersebut tanpa harus mencari dan menyeleksi ulang satu persatu.</p><p>Cocok untuk rombongan santri yang sering dibuatkan surat secara kolektif.</p></div>',
        icon: 'info'
    });
}

function showTemplateBookmarkInfo() {
    Swal.fire({
        title: 'Panduan Bookmark Template',
        html: `
            <div class="text-start" style="font-size: 0.9rem;">
                <p>Saat membuat template Word (<code>.docx</code>), Anda bisa menyisipkan <strong>Bookmark</strong> di tempat yang Anda inginkan agar sistem otomatis mengisi data pada posisi tersebut.</p>
                
                <h6 class="fw-bold mt-3 text-primary">Bookmark Umum (Header Surat)</h6>
                <table class="table table-sm table-bordered">
                    <tr><td><code>NO</code></td><td>Nomor Surat (Hanya Angka)</td></tr>
                    <tr><td><code>Tanggal_Buat</code></td><td>Tanggal Surat (Contoh: 5 Juni 2026)</td></tr>
                    <tr><td><code>Bln_Romawi</code></td><td>Bulan dalam Romawi (Contoh: VI)</td></tr>
                    <tr><td><code>Tahun_Buat</code></td><td>Tahun (Contoh: 2026)</td></tr>
                    <tr><td><code>JML_LAMPIRAN</code></td><td>Jumlah Lampiran (Contoh: 2 Lembar)</td></tr>
                    <tr><td><code>Kepada</code></td><td>Tujuan Surat</td></tr>
                    <tr><td><code>Tempat</code></td><td>Tempat Tujuan</td></tr>
                    <tr><td><code>Hal</code></td><td>Perihal Surat</td></tr>
                    <tr><td><code>Isi</code></td><td>Isi Utama Surat</td></tr>
                    <tr><td><code>Kepala_Staf</code></td><td>Nama Staf / Pejabat</td></tr>
                    <tr><td><code>Staf_TTL</code></td><td>Tempat, Tanggal Lahir Staf</td></tr>
                    <tr><td><code>Staf_Pekerjaan</code></td><td>Pekerjaan Staf</td></tr>
                </table>

                <h6 class="fw-bold mt-3 text-success">Bookmark Personal (Khusus Perseorangan)</h6>
                <table class="table table-sm table-bordered">
                    <tr><td><code>Nama_Santri</code></td><td>Nama Lengkap Santri</td></tr>
                    <tr><td><code>Tempat_Lahir</code></td><td>Tempat Lahir Santri</td></tr>
                    <tr><td><code>Tgl_Lahir</code></td><td>Tanggal Lahir Santri</td></tr>
                    <tr><td><code>Jenis_Kelamin</code></td><td>Jenis Kelamin Santri</td></tr>
                    <tr><td><code>Kewarganegaraan</code></td><td>Status (WNA/WNI/Affidavit)</td></tr>
                    <tr><td><code>Negara_Asal</code></td><td>Negara Asal</td></tr>
                    <tr><td><code>Alamat_Santri</code></td><td>Alamat Santri</td></tr>
                    <tr><td><code>Alamat_Idn</code></td><td>Alamat di Indonesia</td></tr>
                    <tr><td><code>No_Paspor</code></td><td>Nomor Paspor</td></tr>
                    <tr><td><code>Tgl_Berlaku</code></td><td>Tanggal Berlaku S.D Paspor</td></tr>
                    <tr><td><code>Tgl_Keluar_Paspor</code></td><td>Tanggal Paspor Dikeluarkan</td></tr>
                    <tr><td><code>Tempat_Keluar</code></td><td>Tempat Paspor Dikeluarkan</td></tr>
                    <tr><td><code>Exp_ITAS</code></td><td>Tanggal Berlaku ITAS</td></tr>
                </table>
                <div class="alert alert-info mt-2 mb-0 py-2">
                    <small><i class="bi bi-info-circle"></i> <strong>Tips:</strong> Untuk surat kolektif (Sekaligus), tabel lampiran santri akan disisipkan otomatis di akhir dokumen (halaman baru). Anda cukup menggunakan Bookmark Umum di halaman pertama.</small>
                </div>
            </div>
        `,
        width: '600px',
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#0d6efd'
    });
}
</script>

<!-- Load SortableJS for Drag and Drop, including MultiDrag -->
<script src="<?= ASSET_URL ?>/assets/offline/js/Sortable.min.js"></script>
