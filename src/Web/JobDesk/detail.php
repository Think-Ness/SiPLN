<?php
/**
 * @var array $case
 * @var array $steps
 * @var array $notes
 */

if (!function_exists('calculateDaysInfo')) {
    function calculateDaysInfo($startDate, $endDate = null) {
        if (!$startDate) return null;
        $start = new DateTime($startDate);
        $end = $endDate ? new DateTime($endDate) : new DateTime();
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);
        if ($start > $end) return ['total' => 0, 'kerja' => 0, 'libur' => 0];
        $totalDays = $start->diff($end)->days;
        $kerja = 0; $libur = 0;
        $current = clone $start;
        for ($i = 0; $i < $totalDays; $i++) {
            $dayOfWeek = $current->format('N');
            if ($dayOfWeek >= 6) $libur++; else $kerja++;
            $current->modify('+1 day');
        }
        return ['total' => $totalDays, 'kerja' => $kerja, 'libur' => $libur];
    }
}

// Calculate progress
$completedSteps = 0;
$currentStep = null;
$hasBlocked = false;
foreach ($steps as $s) {
    if ($s['status'] === 'selesai') $completedSteps++;
    if ($s['status'] === 'proses' && !$currentStep) $currentStep = $s;
    if ($s['status'] === 'blocked') { $hasBlocked = true; if (!$currentStep) $currentStep = $s; }
}
$totalSteps = count($steps);
$progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

// Dynamic step icons and colors
$stepIcons = ['bi-file-earmark-text', 'bi-check2-square', 'bi-send', 'bi-person-vcard', 'bi-camera', 'bi-credit-card-2-front', 'bi-database-check'];
$stepColors = ['#6f42c1', '#0d6efd', '#198754', '#fd7e14', '#20c997', '#0dcaf0', '#e83e8c'];
?>
<style>
    .detail-stepper-item { position: relative; padding-left: 52px; padding-bottom: 28px; }
    .detail-stepper-item:last-child { padding-bottom: 0; }
    .detail-stepper-item::before {
        content: ''; position: absolute; left: 18px; top: 38px; bottom: 0;
        width: 2px; background: #dee2e6;
    }
    .detail-stepper-item:last-child::before { display: none; }
    .detail-stepper-item.step-done::before { background: #198754; }
    .step-circle {
        position: absolute; left: 0; top: 2px;
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .8rem;
        border: 2px solid #dee2e6; background: #fff; color: #6c757d;
        z-index: 2;
    }
    .step-done .step-circle { background: #198754; border-color: #198754; color: #fff; }
    .step-proses .step-circle { background: #0d6efd; border-color: #0d6efd; color: #fff; animation: pulse-glow 2s infinite; }
    .step-blocked .step-circle { background: #dc3545; border-color: #dc3545; color: #fff; }
    @keyframes pulse-glow {
        0%,100% { box-shadow: 0 0 0 0 rgba(13,110,253,.4); }
        50% { box-shadow: 0 0 0 8px rgba(13,110,253,0); }
    }
    @keyframes pulse-glow-danger {
        0%,100% { box-shadow: 0 0 0 0 rgba(220,53,69,.4); }
        50% { box-shadow: 0 0 0 8px rgba(220,53,69,0); }
    }
    .step-content-box {
        background: #fff; border: 1px solid #e9ecef; border-radius: 10px; padding: 14px 16px;
        transition: all .2s;
    }
    .step-content-box:hover { border-color: #b6d4fe; box-shadow: 0 4px 12px rgba(0,0,0,.04); }
    .step-done .step-content-box { border-left: 3px solid #198754; background: #f8fdf9; }
    .step-proses .step-content-box { border-left: 3px solid #0d6efd; background: #f0f6ff; }
    .step-blocked .step-content-box { border-left: 3px solid #dc3545; background: #fef2f2; }
    .note-item { border-left: 3px solid #dee2e6; padding-left: 12px; margin-bottom: 12px; }
    .note-item.note-kekurangan { border-left-color: #dc3545; background: #fef2f2; padding: 10px 12px; border-radius: 8px; }
    .note-item.note-selesai { border-left-color: #198754; }
    .note-item.note-tindakan { border-left-color: #fd7e14; }
    .info-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; font-size: .78rem; }
</style>

<div class="px-2 py-3">
    <!-- Back & Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= API_URL ?>/job-desk" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-medium" style="font-size: .78rem;">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <?php 
            $myKep = $_SESSION['def_kepengurusan'] ?? '';
            $sKep  = trim((string)($case['kepengurusan'] ?? ''));
            $isPindahan = ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0);
        ?>
        <?php if (!$isPindahan): ?>
        <button onclick="deleteCase(<?= $case['id'] ?>)" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-medium" style="font-size: .78rem;">
            <i class="bi bi-trash me-1"></i>Hapus Kasus
        </button>
        <?php else: ?>
        <span class="badge bg-warning text-dark border border-warning px-3 py-2 rounded-pill shadow-sm" style="font-size: .75rem;"><i class="bi bi-eye-fill me-1"></i>Mode Pemantau</span>
        <?php endif; ?>
    </div>

    <!-- Info Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-3 mb-3 mb-lg-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px; background: #0d6efd15;">
                            <i class="bi bi-person-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <?= htmlspecialchars($case['nama']) ?>
                                <?php 
                                    $myKep = $_SESSION['def_kepengurusan'] ?? '';
                                    $sKep  = trim((string)($case['kepengurusan'] ?? ''));
                                    if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0): 
                                ?>
                                    <span class="badge bg-warning text-dark border ms-2 align-middle" style="font-size: 0.65rem; padding: 3px 6px; border-radius: 4px; vertical-align: text-top;">Pindahan</span>
                                <?php endif; ?>
                            </h5>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="info-pill bg-light border"><i class="bi bi-clipboard2-check-fill text-primary" style="font-size: .7rem;"></i> <?= htmlspecialchars($case['nama_proses']) ?></span>
                                <span class="info-pill bg-light border"><i class="bi bi-house-door-fill text-secondary" style="font-size: .7rem;"></i> <?= htmlspecialchars($case['pondok']) ?> (<?= htmlspecialchars($case['kelas']) ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3 text-center text-lg-start">
                        <div class="col-2">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">KDS</div>
                            <div class="fw-bold text-dark small"><?= htmlspecialchars($case['kds']) ?></div>
                        </div>
                        <div class="col-2">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">TGL REF</div>
                            <div class="fw-bold text-danger small"><?= $case['tanggal_referensi'] ? date('d M Y', strtotime($case['tanggal_referensi'])) : '-' ?></div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">EXP PASPOR</div>
                            <div class="fw-bold text-dark small"><?= $case['exp_paspor'] ? date('d M Y', strtotime($case['exp_paspor'])) : '-' ?></div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">EXP ITAS</div>
                            <div class="fw-bold text-danger small"><?= $case['exp_itas'] ? date('d M Y', strtotime($case['exp_itas'])) : '-' ?></div>
                        </div>
                        <div class="col-2">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">STATUS</div>
                            <?php if ($case['status'] === 'selesai'): ?>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="font-size: .65rem;"><?= strtoupper($case['status']) ?></span>
                            <?php elseif ($hasBlocked): ?>
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle" style="font-size: .65rem;">TERKENDALA</span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle" style="font-size: .65rem;"><?= strtoupper($case['status']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold <?= $progress == 100 ? 'text-success' : 'text-primary' ?>">
                        Progres: <?= $completedSteps ?>/<?= $totalSteps ?> tahap (<?= $progress ?>%)
                    </span>
                    <?php 
                        $endProcessDate = null;
                        if ($case['status'] === 'selesai') {
                            $maxDate = null;
                            foreach ($steps as $st) {
                                if ($st['tgl_realisasi'] && (!$maxDate || $st['tgl_realisasi'] > $maxDate)) {
                                    $maxDate = $st['tgl_realisasi'];
                                }
                            }
                            $endProcessDate = $maxDate;
                        }
                        $caseDur = calculateDaysInfo($case['tgl_mulai_proses'], $endProcessDate);
                    ?>
                    <div class="text-end">
                        <span class="text-muted" style="font-size: .75rem;">Mulai proses: <strong class="text-dark"><?= date('d M Y', strtotime($case['tgl_mulai_proses'])) ?></strong></span>
                    </div>
                </div>
                <div class="progress mb-3" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar <?= $progress == 100 ? 'bg-success' : ($hasBlocked ? 'bg-danger' : 'bg-primary') ?>" 
                         role="progressbar" style="width: <?= $progress ?>%; transition: width .5s;" 
                         aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <?php if ($caseDur): ?>
                <div class="alert <?= $case['status'] === 'selesai' ? 'alert-success border-success-subtle' : 'alert-primary bg-primary-subtle border-primary-subtle' ?> py-2 px-3 mb-0 d-flex justify-content-between align-items-center rounded-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="bi <?= $case['status'] === 'selesai' ? 'bi-check2-all text-success' : 'bi-clock-history text-primary' ?> fs-6"></i>
                        </div>
                        <div>
                            <div class="<?= $case['status'] === 'selesai' ? 'text-success' : 'text-primary' ?> fw-bold" style="font-size: .75rem; line-height: 1.2;">DURASI PROSES</div>
                            <div class="text-dark fw-medium" style="font-size: .85rem;">
                                <?= $caseDur['total'] == 0 ? 'Hari ini' : $caseDur['total'] . ' Hari Keseluruhan' ?>
                                <span class="text-muted fw-normal" style="font-size: .8rem;">(<?= $caseDur['kerja'] ?> Hari Kerja, <?= $caseDur['libur'] ?> Libur)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Document Info Row -->
    <div class="row g-4 mb-4">
        <!-- Paspor Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(13, 110, 253, 0.06); border: 1px solid rgba(13, 110, 253, 0.3) !important; border-left: 5px solid #0d6efd !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(13, 110, 253, 0.1);">
                                <i class="bi bi-passport text-primary fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0">Informasi Paspor</h6>
                        </div>
                        <?php 
                        $pasporUrl = ($paspor && !empty($paspor['path_file'])) ? API_URL . "<?= API_URL ?>/api/santri/doc/paspor/{$paspor['id']}" : ''; 
                        $pasporIsPdf = strtolower(pathinfo($paspor['path_file'] ?? '', PATHINFO_EXTENSION)) === 'pdf';
                        ?>
                        <button onclick="viewDocument('<?= $pasporUrl ?>', 'Paspor', <?= $pasporIsPdf ? 'true' : 'false' ?>)" class="btn btn-sm btn-light border rounded-pill text-primary fw-medium shadow-sm" style="font-size: .7rem; background-color: #fff;"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat Dokumen</button>
                    </div>
                    
                    <?php if ($paspor): ?>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">NO PASPOR</div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($paspor['no_paspor']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">STATUS</div>
                                <?= $paspor['aktif'] == 1 ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Aktif</span>' : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">Tidak Aktif</span>' ?>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">TGL DIKELUARKAN</div>
                                <div class="fw-medium text-dark small"><?= $paspor['tanggal_dikeluarkan'] ? date('d M Y', strtotime($paspor['tanggal_dikeluarkan'])) : '-' ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">TEMPAT DIKELUARKAN</div>
                                <div class="fw-medium text-dark small"><?= htmlspecialchars($paspor['tempat_dikeluarkan']) ?></div>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="p-2 bg-white border rounded-3 d-flex justify-content-between align-items-center shadow-sm">
                                    <span class="text-muted fw-bold" style="font-size: .65rem; letter-spacing: .05em;">BERLAKU SAMPAI</span>
                                    <span class="fw-bold text-danger"><?= $paspor['exp_paspor'] ? date('d M Y', strtotime($paspor['exp_paspor'])) : '-' ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-passport text-muted" style="font-size: 2rem; opacity: .2;"></i>
                            <p class="text-muted small mt-2 mb-0">Belum ada data paspor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ITAS Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: rgba(25, 135, 84, 0.06); border: 1px solid rgba(25, 135, 84, 0.3) !important; border-left: 5px solid #198754 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(25, 135, 84, 0.1);">
                                <i class="bi bi-person-vcard text-success fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0">Informasi ITAS</h6>
                        </div>
                        <?php 
                        $itasUrl = ($itas && !empty($itas['path_file'])) ? API_URL . "<?= API_URL ?>/api/santri/doc/itas/{$itas['id']}" : ''; 
                        $itasIsPdf = strtolower(pathinfo($itas['path_file'] ?? '', PATHINFO_EXTENSION)) === 'pdf';
                        ?>
                        <button onclick="viewDocument('<?= $itasUrl ?>', 'ITAS', <?= $itasIsPdf ? 'true' : 'false' ?>)" class="btn btn-sm btn-light border rounded-pill text-success fw-medium shadow-sm" style="font-size: .7rem; background-color: #fff;"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat Dokumen</button>
                    </div>
                    
                    <?php if ($itas): ?>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">NO ITAS</div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($itas['no_itas']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">STATUS</div>
                                <?= $itas['aktif'] == 1 ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Aktif</span>' : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">Tidak Aktif</span>' ?>
                            </div>
                            <div class="col-12">
                                <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">JENIS/LEVEL ITAS</div>
                                <div class="fw-medium text-dark small">Tingkat <?= htmlspecialchars($itas['level_itas']) ?></div>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="p-2 bg-white border rounded-3 d-flex justify-content-between align-items-center shadow-sm">
                                    <span class="text-muted fw-bold" style="font-size: .65rem; letter-spacing: .05em;">BERLAKU SAMPAI</span>
                                    <span class="fw-bold text-danger"><?= $itas['exp_itas'] ? date('d M Y', strtotime($itas['exp_itas'])) : '-' ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-person-vcard text-muted" style="font-size: 2rem; opacity: .2;"></i>
                            <p class="text-muted small mt-2 mb-0">Belum ada data ITAS.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== KEUANGAN BIROKRASI PANEL ===== -->
    <?php if ($payment): 
        $nomSantri = (float)($payment['nominal_santri'] ?? 0);
        $nomInstansi = (float)($payment['nominal_instansi'] ?? 0);
        $selisih = (float)($payment['selisih_operasional'] ?? ($nomSantri - $nomInstansi));
        $isSantriLunas = ($payment['status_bayar_santri'] ?? '') === 'lunas';
        $isInstansiLunas = ($payment['status_bayar_instansi'] ?? '') === 'lunas';
        $totalCicilan = (float)($payment['total_cicilan'] ?? 0);
        $jumlahCicilan = (int)($payment['jumlah_cicilan'] ?? 0);
        $sisaTagihan = $nomSantri - $totalCicilan;
        $pctCicilan = $nomSantri > 0 ? min(100, ($totalCicilan / $nomSantri) * 100) : 0;
    ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #f8f9ff 0%, #fff8f0 100%);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="bi bi-cash-coin text-white fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Keuangan Birokrasi</h6>
                        <div class="text-muted" style="font-size: .68rem;">Pencatatan pembayaran proses <?= htmlspecialchars($case['nama_proses']) ?></div>
                    </div>
                </div>
                <?php if (!$isPindahan): ?>
                <div class="d-flex gap-2">
                    <?php if (!$isSantriLunas): ?>
                    <button onclick="openCicilanDetailModal()" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-medium" style="font-size: .72rem;">
                        <i class="bi bi-cash-coin me-1"></i>Cicil
                    </button>
                    <?php endif; ?>
                    <button onclick="openPaymentModal()" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-medium" style="font-size: .72rem;">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <button onclick="printDetailKwitansi()" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Kwitansi" style="font-size: .72rem;"><i class="bi bi-receipt"></i></button>
                </div>
                <?php endif; ?>
            </div>

            <!-- 3 Summary Cards -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 position-relative overflow-hidden" style="background: <?= $isSantriLunas ? '#f0fdf4' : '#fff' ?>; border-color: <?= $isSantriLunas ? '#86efac' : '#e5e7eb' ?> !important;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">DITERIMA DARI SANTRI</div>
                            <?php if ($isSantriLunas): ?>
                                <span class="badge bg-success rounded-pill" style="font-size: .6rem;"><i class="bi bi-check-circle-fill me-1"></i>Lunas</span>
                            <?php elseif ($jumlahCicilan > 0): ?>
                                <span class="badge bg-info rounded-pill" style="font-size: .6rem;"><i class="bi bi-arrow-repeat me-1"></i>Cicilan <?= $jumlahCicilan ?>x</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark rounded-pill" style="font-size: .6rem;"><i class="bi bi-clock-fill me-1"></i>Belum</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 1.15rem;">Rp <?= number_format($nomSantri, 0, ',', '.') ?></div>
                        <?php if ($payment['tgl_bayar_santri']): ?>
                            <div class="text-muted mt-1" style="font-size: .65rem;"><i class="bi bi-calendar-check me-1"></i><?= date('d M Y', strtotime($payment['tgl_bayar_santri'])) ?></div>
                        <?php endif; ?>
                        <?php if (!$isSantriLunas && $jumlahCicilan > 0): ?>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1" style="font-size:.63rem;">
                                <span class="text-info">Dibayar: Rp <?= number_format($totalCicilan,0,',','.') ?></span>
                                <span class="text-muted"><?= round($pctCicilan) ?>%</span>
                            </div>
                            <div class="progress" style="height:5px;border-radius:3px;"><div class="progress-bar bg-info" style="width:<?= $pctCicilan ?>%"></div></div>
                            <div class="text-warning fw-semibold mt-1" style="font-size:.63rem;">Sisa: Rp <?= number_format($sisaTagihan,0,',','.') ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!$isSantriLunas && !$isPindahan): ?>
                            <button onclick="markPaid('santri')" class="btn btn-sm btn-success rounded-pill px-3 mt-2 w-100 fw-medium" style="font-size: .7rem;">
                                <i class="bi bi-check2 me-1"></i>Tandai Lunas Sekaligus
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 position-relative overflow-hidden" style="background: <?= $isInstansiLunas ? '#f0fdf4' : '#fff' ?>; border-color: <?= $isInstansiLunas ? '#86efac' : '#e5e7eb' ?> !important;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="text-muted fw-bold" style="font-size: .6rem; letter-spacing: .05em;">DIBAYAR KE INSTANSI</div>
                            <?php if ($isInstansiLunas): ?>
                                <span class="badge bg-success rounded-pill" style="font-size: .6rem;"><i class="bi bi-check-circle-fill me-1"></i>Lunas</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark rounded-pill" style="font-size: .6rem;"><i class="bi bi-clock-fill me-1"></i>Belum</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 1.15rem;">Rp <?= number_format($nomInstansi, 0, ',', '.') ?></div>
                        <?php if ($payment['tgl_bayar_instansi']): ?>
                            <div class="text-muted mt-1" style="font-size: .65rem;"><i class="bi bi-calendar-check me-1"></i><?= date('d M Y', strtotime($payment['tgl_bayar_instansi'])) ?></div>
                        <?php endif; ?>
                        <?php if (!$isInstansiLunas && !$isPindahan): ?>
                            <button onclick="markPaid('instansi')" class="btn btn-sm btn-primary rounded-pill px-3 mt-2 w-100 fw-medium" style="font-size: .7rem;">
                                <i class="bi bi-building me-1"></i>Tandai Sudah Bayar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100" style="background: linear-gradient(135deg, #fefce8, #f0fdf4); border-color: #a3e635 !important;">
                        <div class="text-muted fw-bold mb-2" style="font-size: .6rem; letter-spacing: .05em;">UANG OPERASIONAL</div>
                        <div class="fw-bold <?= $selisih >= 0 ? 'text-success' : 'text-danger' ?>" style="font-size: 1.15rem;">
                            Rp <?= number_format($selisih, 0, ',', '.') ?>
                        </div>
                        <div class="text-muted mt-1" style="font-size: .65rem;">
                            <i class="bi bi-info-circle me-1"></i>Masuk kas operasional birokrasi
                        </div>
                        <?php if (!$isSantriLunas || !$isInstansiLunas): ?>
                        <div class="mt-1 badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.58rem;">Pending – belum final</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($payment['catatan'])): ?>
            <div class="alert alert-light border py-2 px-3 mb-3 rounded-3" style="font-size: .78rem;">
                <i class="bi bi-sticky text-muted me-1"></i><strong>Catatan:</strong> <?= htmlspecialchars($payment['catatan']) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($payment['bukti_bayar_path'])): ?>
            <div class="d-flex align-items-center gap-2 mt-2">
                <button onclick="viewDocument('<?= $payment['bukti_bayar_path'] ?>', 'Bukti Bayar', <?= strtolower(pathinfo($payment['bukti_bayar_path'], PATHINFO_EXTENSION)) === 'pdf' ? 'true' : 'false' ?>)" class="btn btn-sm btn-light border rounded-pill text-primary fw-medium" style="font-size: .7rem;">
                    <i class="bi bi-file-earmark-image me-1"></i>Lihat Bukti Bayar
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- HIDDEN PRINT AREA for kwitansi -->
    <div id="detailPrintKwitansiArea" style="display:none;"></div>

    <!-- ===== RIWAYAT PEMBAYARAN SANTRI ===== -->
    <?php if (count($paymentHistory) > 1): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(139, 92, 246, 0.1);">
                        <i class="bi bi-clock-history" style="color: #8b5cf6; font-size: 1.1rem;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Riwayat Pembayaran Santri</h6>
                        <div class="text-muted" style="font-size: .68rem;"><?= count($paymentHistory) ?> transaksi tercatat untuk <?= htmlspecialchars($case['nama']) ?></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#paymentHistoryCollapse">
                    <i class="bi bi-chevron-down me-1"></i>Lihat Riwayat
                </button>
            </div>
            
            <div class="collapse" id="paymentHistoryCollapse">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: .78rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="font-size: .65rem;" class="text-muted">TANGGAL</th>
                                <th style="font-size: .65rem;" class="text-muted">PROSES</th>
                                <th style="font-size: .65rem;" class="text-muted text-end">BAYAR SANTRI</th>
                                <th style="font-size: .65rem;" class="text-muted text-end">BAYAR INSTANSI</th>
                                <th style="font-size: .65rem;" class="text-muted text-end">SELISIH</th>
                                <th style="font-size: .65rem;" class="text-muted text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $prevNominal = null;
                            foreach ($paymentHistory as $idx => $h): 
                                $hSantri = (float)($h['nominal_santri'] ?? 0);
                                $hInstansi = (float)($h['nominal_instansi'] ?? 0);
                                $hSelisih = (float)($h['selisih_operasional'] ?? ($hSantri - $hInstansi));
                                $isCurrentCase = ($h['case_id'] == $case['id']);
                                $priceChange = null;
                                if ($prevNominal !== null && $prevNominal != $hInstansi) {
                                    $diff = $hInstansi - $prevNominal;
                                    $priceChange = $diff;
                                }
                                $prevNominal = $hInstansi;
                            ?>
                            <tr class="<?= $isCurrentCase ? 'table-primary' : '' ?>">
                                <td class="fw-medium">
                                    <?= $h['case_created_at'] ? date('d M Y', strtotime($h['case_created_at'])) : '-' ?>
                                    <?php if ($isCurrentCase): ?>
                                        <span class="badge bg-primary rounded-pill ms-1" style="font-size: .55rem;">Saat ini</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($h['nama_proses'] ?? '-') ?></td>
                                <td class="text-end fw-medium">Rp <?= number_format($hSantri, 0, ',', '.') ?></td>
                                <td class="text-end fw-medium">
                                    Rp <?= number_format($hInstansi, 0, ',', '.') ?>
                                    <?php if ($priceChange !== null): ?>
                                        <span class="badge <?= $priceChange > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> ms-1" style="font-size: .55rem;">
                                            <?= $priceChange > 0 ? 'â†‘' : 'â†“' ?> <?= number_format(abs($priceChange), 0, ',', '.') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end <?= $hSelisih >= 0 ? 'text-success' : 'text-danger' ?> fw-bold">Rp <?= number_format($hSelisih, 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if ($h['status_bayar_santri'] === 'lunas' && $h['status_bayar_instansi'] === 'lunas'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: .6rem;">Selesai</span>
                                    <?php elseif ($h['status_bayar_santri'] === 'lunas'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: .6rem;">Santri Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill" style="font-size: .6rem;">Belum</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <?php
                                $totalHS = 0; $totalHI = 0; $totalHSel = 0;
                                foreach ($paymentHistory as $h) {
                                    $totalHS += (float)($h['nominal_santri'] ?? 0);
                                    $totalHI += (float)($h['nominal_instansi'] ?? 0);
                                    $totalHSel += (float)($h['selisih_operasional'] ?? ((float)$h['nominal_santri'] - (float)$h['nominal_instansi']));
                                }
                            ?>
                            <tr>
                                <td colspan="2" class="text-muted" style="font-size: .7rem;">TOTAL RIWAYAT</td>
                                <td class="text-end">Rp <?= number_format($totalHS, 0, ',', '.') ?></td>
                                <td class="text-end">Rp <?= number_format($totalHI, 0, ',', '.') ?></td>
                                <td class="text-end text-success">Rp <?= number_format($totalHSel, 0, ',', '.') ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <div class="row g-4">
        <!-- Stepper Column -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-check text-primary me-2"></i>Tahapan Proses</h6>
                    </div>
                    
                    <?php 
                        $prevDate = $case['tgl_mulai_proses'];
                        foreach ($steps as $i => $step): 
                        $stepClass = 'step-pending';
                        if ($step['status'] === 'selesai') $stepClass = 'step-done';
                        elseif ($step['status'] === 'proses') $stepClass = 'step-proses';
                        elseif ($step['status'] === 'blocked') $stepClass = 'step-blocked';
                        
                        $icon = $stepIcons[$i % count($stepIcons)];
                        $color = $stepColors[$i % count($stepColors)];
                        
                        $stepDur = null;
                        if ($step['status'] === 'selesai') {
                            $stepDur = calculateDaysInfo($prevDate, $step['tgl_realisasi']);
                            if ($step['tgl_realisasi']) $prevDate = $step['tgl_realisasi'];
                        } elseif ($step['status'] === 'proses' || $step['status'] === 'blocked') {
                            $stepDur = calculateDaysInfo($prevDate, null);
                        }
                    ?>
                    <div class="detail-stepper-item <?= $stepClass ?>">
                        <div class="step-circle">
                            <?php if ($step['status'] === 'selesai'): ?>
                                <i class="bi bi-check-lg" style="font-size: 1rem;"></i>
                            <?php elseif ($step['status'] === 'blocked'): ?>
                                <i class="bi bi-exclamation" style="font-size: 1rem;"></i>
                            <?php else: ?>
                                <?= $step['step_urut'] ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="step-content-box">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi <?= $icon ?>" style="color: <?= $color ?>; font-size: .85rem;"></i>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: .88rem;"><?= htmlspecialchars($step['step_nama']) ?></h6>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                        <?php if ($step['status'] === 'pending'): ?>
                                            <span class="badge bg-light text-secondary border" style="font-size: .68rem;"><i class="bi bi-clock me-1"></i>Menunggu</span>
                                        <?php elseif ($step['status'] === 'proses'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: .7rem;"><i class="bi bi-arrow-repeat me-1"></i>Sedang Diproses</span>
                                            <?php if ($stepDur): ?>
                                                <?php 
                                                    $slaLimit = isset($_GET['sla']) ? (int)$_GET['sla'] : 3;
                                                    $isSlaWarn = $stepDur['kerja'] > $slaLimit;
                                                ?>
                                                <span class="badge <?= $isSlaWarn ? 'bg-danger text-white border-danger shadow-sm' : 'bg-white text-dark border border-secondary-subtle fw-medium shadow-sm' ?>" style="font-size: .7rem; padding: 5px 8px; <?= $isSlaWarn ? 'animation: pulse-glow-danger 2s infinite;' : '' ?>">
                                                    <i class="bi <?= $isSlaWarn ? 'bi-alarm-fill' : 'bi-hourglass-split' ?> <?= $isSlaWarn ? 'text-white' : 'text-warning' ?> me-1"></i> <?= $stepDur['total'] == 0 ? '< 1 Hari' : $stepDur['total'] . ' Hari' ?> (<?= $stepDur['kerja'] ?> HK, <?= $stepDur['libur'] ?> HL)
                                                    <?= $isSlaWarn ? ' - TERLAMBAT SLA' : '' ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($step['status'] === 'selesai'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: .7rem;"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                                            <?php if ($step['tgl_realisasi']): ?>
                                                <span class="badge bg-light text-dark border shadow-sm" style="font-size: .7rem;"><i class="bi bi-calendar-check text-success me-1"></i><?= date('d M Y', strtotime($step['tgl_realisasi'])) ?></span>
                                            <?php endif; ?>
                                            <?php if ($stepDur): ?>
                                                <span class="badge bg-white text-dark border border-secondary-subtle fw-medium shadow-sm" style="font-size: .7rem; padding: 5px 8px;"><i class="bi bi-stopwatch text-info me-1"></i> Memakan Waktu <?= $stepDur['total'] == 0 ? '< 1 Hari' : $stepDur['total'] . ' Hari' ?> (<?= $stepDur['kerja'] ?> HK, <?= $stepDur['libur'] ?> HL)</span>
                                            <?php endif; ?>
                                        <?php elseif ($step['status'] === 'blocked'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: .7rem;"><i class="bi bi-exclamation-triangle me-1"></i>Terkendala</span>
                                            <?php if ($stepDur): ?>
                                                <span class="badge bg-white text-danger border border-danger-subtle fw-bold shadow-sm" style="font-size: .7rem; padding: 5px 8px;"><i class="bi bi-hourglass-bottom text-danger me-1"></i> Terhenti <?= $stepDur['total'] == 0 ? '< 1 Hari' : $stepDur['total'] . ' Hari' ?> (<?= $stepDur['kerja'] ?> HK, <?= $stepDur['libur'] ?> HL)</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!$isPindahan): ?>
                                <button onclick="openUpdateModal(<?= $step['id'] ?>, '<?= $step['status'] ?>', '<?= htmlspecialchars(addslashes($step['step_nama'])) ?>')" 
                                        class="btn btn-sm btn-outline-secondary py-1 px-3 rounded-pill fw-medium flex-shrink-0" style="font-size: .72rem;">
                                    <i class="bi bi-pencil-square me-1"></i>Update
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Notes / Log Column -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Log & Catatan</h6>
                        <?php if (!$isPindahan): ?>
                        <button onclick="openNoteModal()" class="btn btn-sm btn-primary py-1 px-3 rounded-pill fw-medium" style="font-size: .72rem;">
                            <i class="bi bi-plus-lg me-1"></i>Tambah
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($notes)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-left-dots text-muted" style="font-size: 2.5rem; opacity: .3;"></i>
                            <p class="text-muted small mt-2 mb-0">Belum ada catatan.<br>Klik <strong>Tambah</strong> untuk menambahkan.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($notes as $note): 
                                $noteClass = 'note-info';
                                $noteIcon = 'bi-info-circle text-primary';
                                $noteLabel = 'Info';
                                if ($note['tipe'] === 'kekurangan') {
                                    $noteClass = 'note-kekurangan';
                                    $noteIcon = 'bi-exclamation-triangle-fill text-danger';
                                    $noteLabel = 'Kekurangan Berkas';
                                } elseif ($note['tipe'] === 'selesai') {
                                    $noteClass = 'note-selesai';
                                    $noteIcon = 'bi-check-circle-fill text-success';
                                    $noteLabel = 'Selesai';
                                } elseif ($note['tipe'] === 'tindakan') {
                                    $noteClass = 'note-tindakan';
                                    $noteIcon = 'bi-lightning-fill text-warning';
                                    $noteLabel = 'Tindakan';
                                }
                            ?>
                            <div class="note-item <?= $noteClass ?>">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size: .65rem;" class="fw-bold text-uppercase">
                                        <i class="bi <?= $noteIcon ?> me-1"></i><?= $noteLabel ?>
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted" style="font-size: .62rem;"><?= date('d M Y, H:i', strtotime($note['created_at'])) ?></span>
                                        <?php if (!$isPindahan): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted p-0 border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: .8rem; min-width: 120px;">
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="editNote(<?= htmlspecialchars(json_encode($note), ENT_QUOTES, 'UTF-8') ?>)"><i class="bi bi-pencil me-2 text-primary"></i>Edit</a></li>
                                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteNote(<?= $note['id'] ?>)"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="small text-muted mb-1" style="font-size: .68rem;">
                                    Tahap <?= $note['step_urut'] ?>: <?= htmlspecialchars($note['step_nama']) ?> &middot; oleh <?= htmlspecialchars($note['created_by']) ?>
                                </div>
                                <p class="text-dark small mb-0" style="font-size: .78rem;"><?= nl2br(htmlspecialchars($note['isi'])) ?></p>
                                <?php if ($note['tipe'] === 'kekurangan' && $note['berkas_kurang']): ?>
                                    <div class="mt-2 small text-danger fw-bold bg-white p-2 rounded border border-danger-subtle" style="font-size: .72rem;">
                                        <i class="bi bi-file-earmark-x me-1"></i>Berkas kurang: <?= htmlspecialchars($note['berkas_kurang']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewDocument(url, type, isPdf) {
    if (!url || url.trim() === '') {
        Swal.fire({
            icon: 'info',
            title: 'Dokumen Tidak Tersedia',
            text: 'File dokumen ' + type + ' belum diunggah atau tidak ditemukan.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    let contentHtml = '';
    
    if (isPdf) {
        contentHtml = `<iframe src="${url}" style="width: 100%; height: 70vh; border: none; border-radius: 8px;"></iframe>`;
    } else {
        contentHtml = `<div class="text-center"><img src="${url}" style="max-width: 100%; max-height: 70vh; border-radius: 8px; object-fit: contain;"></div>`;
    }

    Swal.fire({
        title: `<span style="font-size: 1.1rem;">Pratinjau Dokumen ${type}</span>`,
        html: contentHtml,
        width: '80%',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: { popup: 'rounded-4 shadow-lg' }
    });
}
function openUpdateModal(stepId, currentStatus, stepName) {
    Swal.fire({
        title: '<span style="font-size: 1rem;">Update Tahapan</span>',
        html: `
            <div class="text-start">
                <div class="alert alert-light border py-2 px-3 mb-3">
                    <small class="text-muted">Tahap:</small> <strong>${stepName}</strong>
                </div>
                <label class="form-label small fw-bold text-muted mb-1">Status</label>
                <select id="swal-status" class="form-select form-select-sm mb-3">
                    <option value="pending" ${currentStatus==='pending'?'selected':''}>Menunggu (Pending)</option>
                    <option value="proses" ${currentStatus==='proses'?'selected':''}>Sedang Diproses</option>
                    <option value="blocked" ${currentStatus==='blocked'?'selected':''}>Terkendala / Kurang Berkas</option>
                    <option value="selesai" ${currentStatus==='selesai'?'selected':''}>Selesai</option>
                </select>
                <label class="form-label small fw-bold text-muted mb-1">Tanggal Realisasi</label>
                <input type="date" id="swal-date" class="form-control form-control-sm mb-3" value="${new Date().toISOString().split('T')[0]}">
                <label class="form-label small fw-bold text-muted mb-1">Catatan (opsional)</label>
                <textarea id="swal-note" class="form-control form-control-sm" rows="3" placeholder="Tambahkan catatan jika perlu..."></textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => {
            return {
                status: document.getElementById('swal-status').value,
                date: document.getElementById('swal-date').value,
                note: document.getElementById('swal-note').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('status', result.value.status);
            formData.append('date', result.value.date);
            formData.append('note', result.value.note);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch('<?= API_URL ?>/api/job-desk/step/' + stepId + '/update', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: 'Status tahapan berhasil diupdate.',
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(e => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function openNoteModal() {
    let stepOptions = '';
    <?php foreach ($steps as $s): ?>
        stepOptions += `<option value="<?= $s['id'] ?>">Tahap <?= $s['step_urut'] ?> — <?= htmlspecialchars($s['step_nama']) ?></option>`;
    <?php endforeach; ?>

    Swal.fire({
        title: '<span style="font-size: 1rem;">Tambah Catatan</span>',
        html: `
            <div class="text-start">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Tahap</label>
                <select id="swal-note-step" class="form-select form-select-sm mb-3">
                    ${stepOptions}
                </select>
                <label class="form-label small fw-bold text-muted mb-1">Tipe Catatan</label>
                <select id="swal-note-type" class="form-select form-select-sm mb-3">
                    <option value="info">Info Umum</option>
                    <option value="kekurangan">Kekurangan Berkas</option>
                    <option value="tindakan">Tindakan Diperlukan</option>
                </select>
                <label class="form-label small fw-bold text-muted mb-1">Berkas Kurang (jika ada)</label>
                <input type="text" id="swal-berkas" class="form-control form-control-sm mb-3" placeholder="Contoh: KTP, Foto 4x6, Surat Sponsor...">
                <label class="form-label small fw-bold text-muted mb-1">Isi Catatan</label>
                <textarea id="swal-note-content" class="form-control form-control-sm" rows="3" placeholder="Tulis catatan di sini..."></textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => {
            const isi = document.getElementById('swal-note-content').value;
            if (!isi.trim()) {
                Swal.showValidationMessage('Isi catatan tidak boleh kosong');
                return false;
            }
            return {
                step_id: document.getElementById('swal-note-step').value,
                tipe: document.getElementById('swal-note-type').value,
                berkas: document.getElementById('swal-berkas').value,
                isi: isi
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('step_id', result.value.step_id);
            formData.append('tipe', result.value.tipe);
            formData.append('berkas_kurang', result.value.berkas);
            formData.append('isi', result.value.isi);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch('<?= API_URL ?>/api/job-desk/step/' + result.value.step_id + '/catatan', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: 'Catatan berhasil ditambahkan.',
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function deleteCase(id) {
    Swal.fire({
        title: 'Hapus Kasus?',
        text: 'Anda yakin ingin menghapus kasus ini secara permanen? Data tahapan dan riwayat juga akan ikut terhapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/job-desk/case/' + id + '/delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Terhapus!', text: 'Kasus berhasil dihapus.',
                        timer: 1500, showConfirmButton: false
                    }).then(() => {
                        window.location.href = '<?= API_URL ?>/job-desk';
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menghapus kasus', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}
function editNote(note) {
    let stepOptions = '';
    <?php foreach ($steps as $s): ?>
        stepOptions += `<option value="<?= $s['id'] ?>" ${note.step_id == <?= $s['id'] ?> ? 'selected' : ''}>Tahap <?= $s['step_urut'] ?> — <?= htmlspecialchars($s['step_nama']) ?></option>`;
    <?php endforeach; ?>

    Swal.fire({
        title: '<span style="font-size: 1rem;">Edit Catatan</span>',
        html: `
            <div class="text-start">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Tahap</label>
                <select id="swal-edit-step" class="form-select form-select-sm mb-3">
                    ${stepOptions}
                </select>
                <label class="form-label small fw-bold text-muted mb-1">Tipe Catatan</label>
                <select id="swal-edit-type" class="form-select form-select-sm mb-3">
                    <option value="info" ${note.tipe === 'info' ? 'selected' : ''}>Info Umum</option>
                    <option value="kekurangan" ${note.tipe === 'kekurangan' ? 'selected' : ''}>Kekurangan Berkas</option>
                    <option value="tindakan" ${note.tipe === 'tindakan' ? 'selected' : ''}>Tindakan Diperlukan</option>
                    <option value="selesai" ${note.tipe === 'selesai' ? 'selected' : ''}>Selesai</option>
                </select>
                <label class="form-label small fw-bold text-muted mb-1">Berkas Kurang (jika ada)</label>
                <input type="text" id="swal-edit-berkas" class="form-control form-control-sm mb-3" value="${note.berkas_kurang || ''}">
                <label class="form-label small fw-bold text-muted mb-1">Isi Catatan</label>
                <textarea id="swal-edit-content" class="form-control form-control-sm" rows="3">${note.isi || ''}</textarea>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Simpan Perubahan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => {
            const isi = document.getElementById('swal-edit-content').value;
            if (!isi.trim()) {
                Swal.showValidationMessage('Isi catatan tidak boleh kosong');
                return false;
            }
            return {
                step_id: document.getElementById('swal-edit-step').value,
                tipe: document.getElementById('swal-edit-type').value,
                berkas: document.getElementById('swal-edit-berkas').value,
                isi: isi
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('step_id', result.value.step_id);
            formData.append('tipe', result.value.tipe);
            formData.append('berkas_kurang', result.value.berkas);
            formData.append('isi', result.value.isi);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch('<?= API_URL ?>/api/job-desk/catatan/' + note.id + '/update', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: 'Catatan berhasil diupdate.',
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function deleteNote(id) {
    Swal.fire({
        title: 'Hapus Catatan?',
        text: 'Catatan ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/job-desk/catatan/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken }
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Terhapus!', text: 'Catatan berhasil dihapus.',
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menghapus catatan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

// ===== PAYMENT FUNCTIONS =====
function markPaid(type) {
    const caseId = <?= $case['id'] ?>;
    const label = type === 'santri' ? 'Santri telah membayar' : 'Sudah dibayarkan ke Instansi';
    
    Swal.fire({
        title: '<span style="font-size: 1rem;">Konfirmasi Pembayaran</span>',
        html: `
            <div class="text-start">
                <div class="alert alert-success border-success-subtle py-2 px-3 mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i> ${label}
                </div>
                <label class="form-label small fw-bold text-muted mb-1">Tanggal Pembayaran</label>
                <input type="date" id="swal-pay-date" class="form-control form-control-sm mb-3" value="${new Date().toISOString().split('T')[0]}">
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Konfirmasi',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
        customClass: { popup: 'shadow-lg rounded-4' },
        preConfirm: () => ({
            date: document.getElementById('swal-pay-date').value
        })
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const payload = {};
            if (type === 'santri') {
                payload.status_bayar_santri = 'lunas';
                payload.tgl_bayar_santri = result.value.date;
            } else {
                payload.status_bayar_instansi = 'lunas';
                payload.tgl_bayar_instansi = result.value.date;
            }
            
            fetch('<?= API_URL ?>/api/job-desk/payment/' + caseId, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken 
                },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: 'Pembayaran berhasil dicatat.',
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function openPaymentModal() {
    const caseId = <?= $case['id'] ?>;
    const currentData = <?= json_encode($payment ?? []) ?>;
    
    Swal.fire({
        title: '<span style="font-size: 1rem;"><i class="bi bi-cash-coin me-2"></i>Edit Pembayaran</span>',
        html: `
            <div class="text-start">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Nominal dari Santri (Rp)</label>
                        <input type="number" id="swal-nom-santri" class="form-control form-control-sm" 
                               value="${currentData.nominal_santri || 0}" min="0" step="1000">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Nominal ke Instansi (Rp)</label>
                        <input type="number" id="swal-nom-instansi" class="form-control form-control-sm" 
                               value="${currentData.nominal_instansi || 0}" min="0" step="1000">
                    </div>
                    <div class="col-12">
                        <div class="alert alert-success-subtle border-success py-2 px-3 rounded-3 mb-0">
                            <span class="small fw-bold text-success">Selisih Operasional: Rp <span id="swal-selisih-preview">0</span></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Status Bayar Santri</label>
                        <select id="swal-status-santri" class="form-select form-select-sm">
                            <option value="belum" ${currentData.status_bayar_santri !== 'lunas' ? 'selected' : ''}>Belum Bayar</option>
                            <option value="lunas" ${currentData.status_bayar_santri === 'lunas' ? 'selected' : ''}>Lunas</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Tgl Bayar Santri</label>
                        <input type="date" id="swal-tgl-santri" class="form-control form-control-sm" value="${currentData.tgl_bayar_santri || ''}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Status Bayar Instansi</label>
                        <select id="swal-status-instansi" class="form-select form-select-sm">
                            <option value="belum" ${currentData.status_bayar_instansi !== 'lunas' ? 'selected' : ''}>Belum Bayar</option>
                            <option value="lunas" ${currentData.status_bayar_instansi === 'lunas' ? 'selected' : ''}>Lunas</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Tgl Bayar Instansi</label>
                        <input type="date" id="swal-tgl-instansi" class="form-control form-control-sm" value="${currentData.tgl_bayar_instansi || ''}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted mb-1">Catatan (opsional)</label>
                        <textarea id="swal-pay-catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan pembayaran...">${currentData.catatan || ''}</textarea>
                    </div>
                </div>
            </div>
        `,
        width: '550px',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        customClass: { popup: 'shadow-lg rounded-4' },
        didOpen: () => {
            const updatePreview = () => {
                const s = parseFloat(document.getElementById('swal-nom-santri').value) || 0;
                const i = parseFloat(document.getElementById('swal-nom-instansi').value) || 0;
                document.getElementById('swal-selisih-preview').textContent = new Intl.NumberFormat('id-ID').format(s - i);
            };
            document.getElementById('swal-nom-santri').addEventListener('input', updatePreview);
            document.getElementById('swal-nom-instansi').addEventListener('input', updatePreview);
            updatePreview();
        },
        preConfirm: () => ({
            nominal_santri: document.getElementById('swal-nom-santri').value,
            nominal_instansi: document.getElementById('swal-nom-instansi').value,
            status_bayar_santri: document.getElementById('swal-status-santri').value,
            tgl_bayar_santri: document.getElementById('swal-tgl-santri').value,
            status_bayar_instansi: document.getElementById('swal-status-instansi').value,
            tgl_bayar_instansi: document.getElementById('swal-tgl-instansi').value,
            catatan: document.getElementById('swal-pay-catatan').value
        })
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/job-desk/payment/' + caseId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(result.value)
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: res.message,
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }).catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}
function openCicilanDetailModal() {
    const caseId = <?= $case['id'] ?>;
    const nomSantri = <?= $nomSantri ?? 0 ?>;
    const totCicilan = <?= $totalCicilan ?? 0 ?>;
    const nama = '<?= htmlspecialchars(addslashes($case['nama'])) ?>';
    const kds = '<?= htmlspecialchars(addslashes($case['kds'])) ?>';
    const namaProses = '<?= htmlspecialchars(addslashes($case['nama_proses'])) ?>';
    const sisa = nomSantri - totCicilan;

    // Load existing installments for display
    let riwayatHtml = '';
    
    Swal.fire({
        title: '<span style="font-size:1rem;"><i class="bi bi-cash-coin me-2 text-info"></i>Input Cicilan Pembayaran</span>',
        html: `
            <div class="text-start">
                <div class="alert alert-info-subtle border border-info-subtle py-2 px-3 mb-3 rounded-3 bg-info-subtle">
                    <div class="d-flex justify-content-between">
                        <span class="small fw-bold text-dark">${nama}</span>
                        <span class="small text-muted">${kds}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="small text-muted">Total tagihan:</span>
                        <span class="small fw-bold text-danger">Rp ${new Intl.NumberFormat('id-ID').format(nomSantri)}</span>
                    </div>
                    ${totCicilan > 0 ? `<div class="d-flex justify-content-between">
                        <span class="small text-muted">Sudah dibayar:</span>
                        <span class="small fw-semibold text-info">Rp ${new Intl.NumberFormat('id-ID').format(totCicilan)}</span>
                    </div>` : ''}
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Sisa tagihan:</span>
                        <span class="small fw-bold text-warning">Rp ${new Intl.NumberFormat('id-ID').format(sisa)}</span>
                    </div>
                    ${nomSantri > 0 ? `<div class="progress mt-2" style="height:6px;border-radius:3px;">
                        <div class="progress-bar bg-info" style="width:${Math.min(100,(totCicilan/nomSantri*100)).toFixed(0)}%"></div>
                    </div>` : ''}
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted mb-1">Nominal Cicilan (Rp)</label>
                        <input type="number" id="swal-cicil-nominal" class="form-control" placeholder="Masukkan nominal" max="${sisa}" step="1000">
                        <div class="d-flex gap-1 mt-2 flex-wrap">
                            ${[25,50,75,100].map(p => {
                                const amt = Math.round(sisa * p / 100);
                                return amt > 0 ? `<button type="button" class="btn btn-sm btn-outline-info rounded-pill" onclick="document.getElementById('swal-cicil-nominal').value=${amt}">${p}% (Rp ${new Intl.NumberFormat('id-ID').format(amt)})</button>` : '';
                            }).join('')}
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Tanggal Bayar</label>
                        <input type="date" id="swal-cicil-tgl" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Catatan</label>
                        <input type="text" id="swal-cicil-catatan" class="form-control" placeholder="Opsional...">
                    </div>
                </div>
            </div>
        `,
        width: '520px',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-floppy me-1"></i>Simpan Cicilan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0dcaf0',
        customClass: { popup: 'shadow-lg rounded-4', confirmButton: 'text-dark' },
        preConfirm: () => {
            const nominal = parseFloat(document.getElementById('swal-cicil-nominal').value || 0);
            if (!nominal || nominal <= 0) { Swal.showValidationMessage('Masukkan nominal yang valid.'); return false; }
            if (nominal > sisa + 0.01) { Swal.showValidationMessage(`Melebihi sisa tagihan (Rp ${new Intl.NumberFormat('id-ID').format(sisa)})`); return false; }
            return {
                nominal,
                tgl_bayar: document.getElementById('swal-cicil-tgl').value,
                catatan: document.getElementById('swal-cicil-catatan').value
            };
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        Swal.fire({ title:'Menyimpan...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
        fetch(`<?= API_URL ?>/api/job-desk/payment/${caseId}/cicil`, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},
            body:JSON.stringify(result.value)
        }).then(r=>r.json()).then(res=>{
            if (res.success) {
                Swal.fire({ icon:'success', title: res.is_lunas?'Lunas!':'Cicilan Dicatat', text:res.message, timer:1800, showConfirmButton:false })
                    .then(()=>location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        }).catch(()=>Swal.fire('Error','Gagal koneksi ke server.','error'));
    });
}

function printDetailKwitansi() {
    const caseId = <?= $case['id'] ?>;
    const nama = '<?= htmlspecialchars(addslashes($case['nama'])) ?>';
    const kds = '<?= htmlspecialchars(addslashes($case['kds'])) ?>';
    const namaProses = '<?= htmlspecialchars(addslashes($case['nama_proses'] ?? '')) ?>';
    const nomSantri = <?= $nomSantri ?? 0 ?>;
    const totCicilan = <?= $totalCicilan ?? 0 ?>;
    const sisa = nomSantri - totCicilan;
    const fRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n||0));
    const noKwt = 'KWT-' + String(caseId).padStart(5,'0') + '-' + new Date().getFullYear();

    const instansiInfo = <?= json_encode($instansiInfo ?? []) ?>;
    const instNama = instansiInfo.nama_instansi || 'Sistem Informasi Santri Luar Negeri';

    const html = `
    <div style="font-family:'Courier New',monospace;max-width:420px;margin:20px auto;border:2px solid #1a2035;border-radius:8px;overflow:hidden;">
        <div style="background:#1a2035;color:white;padding:16px 20px;text-align:center;">
            <div style="font-size:14px;font-weight:bold;letter-spacing:1px;">KWITANSI PEMBAYARAN</div>
            <div style="font-size:10px;opacity:.7;margin-top:2px;">${instNama}</div>
        </div>
        <div style="padding:16px 20px;background:white;">
            <table style="width:100%;font-size:11px;border-collapse:collapse;margin-bottom:12px;">
                <tr><td style="padding:3px 0;width:38%;color:#666;">No. Kwitansi</td><td style="font-weight:bold;">: ${noKwt}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">Tanggal</td><td>: ${new Date().toLocaleDateString('id-ID')}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">Nama Santri</td><td style="font-weight:bold;">: ${nama}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">KDS</td><td>: ${kds}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">Jenis Proses</td><td>: ${namaProses}</td></tr>
                <tr><td style="padding:3px 0;color:#666;">Case ID</td><td>: #${caseId}</td></tr>
            </table>
            <div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:12px;text-align:center;margin-bottom:12px;">
                <div style="font-size:10px;color:#666;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Total Tagihan</div>
                <div style="font-size:22px;font-weight:bold;color:#1a2035;">${fRp(nomSantri)}</div>
                ${totCicilan > 0 ? `<div style="font-size:10px;color:#0dcaf0;margin-top:4px;">Sudah Dibayar: ${fRp(totCicilan)}</div>` : ''}
            </div>
            ${sisa > 0
                ? `<div style="background:#fff3cd;border:1px solid #ffc107;padding:8px;border-radius:6px;font-size:11px;margin-bottom:12px;"><strong>Sisa Tagihan: ${fRp(sisa)}</strong></div>`
                : `<div style="background:#d1e7dd;border:1px solid #198754;padding:8px;border-radius:6px;font-size:11px;margin-bottom:12px;text-align:center;"><strong>LUNAS</strong></div>`
            }
            <div style="text-align:center;margin-top:10px;font-size:9px;color:#aaa;">Dicetak: ${new Date().toLocaleString('id-ID')}</div>
        </div>
    </div>`;

    const area = document.getElementById('detailPrintKwitansiArea');
    area.innerHTML = html;
    document.body.appendChild(area);
    area.style.display = 'block';

    document.getElementById('detail-kwt-style')?.remove();
    const ps = document.createElement('style');
    ps.id = 'detail-kwt-style';
    ps.innerHTML = `@media print { body > * { display:none !important; } #detailPrintKwitansiArea { display:block !important; position:fixed;left:0;top:0;width:100%;z-index:99999;background:white; } }`;
    document.head.appendChild(ps);
    window.print();
    setTimeout(()=>{ area.style.display='none'; ps.remove(); }, 1500);
}
</script>
