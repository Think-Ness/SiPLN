<?php
declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $dataItas
 * @var array $dataPaspor
 * @var array $activeJobDeskKdsSet
 */

$this->setTitle('Auto Rekap | ' . $applicationParams->name);

// ==========================================
// HITUNG METRIK ANALITIK
// ==========================================
$itasTotal = count($dataItas);
$itasUrgent = 0;
foreach ($dataItas as $row) {
    $days = (strtotime($row['exp_itas'] ?? '') - time()) / (60 * 60 * 24);
    if ($days <= 30) $itasUrgent++;
}

$pasporTotal = count($dataPaspor);
$pasporUrgent = 0;
foreach ($dataPaspor as $row) {
    $days = (strtotime($row['exp_paspor'] ?? '') - time()) / (60 * 60 * 24);
    if ($days <= 180) $pasporUrgent++;
}

$myKep = $_SESSION['def_kepengurusan'] ?? '';
?>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
            <h5 class="m-0 text-dark fw-bold"><i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i>Rekapan Perpanjangan ITAS dan Paspor</h5>
            <a href="<?= API_URL ?>/kalender-expiry" class="btn btn-primary btn-sm rounded-pill px-3 fw-medium shadow-sm">
                <i class="bi bi-calendar3 me-1"></i> Lihat Kalender
            </a>
        </div>
        
        <form method="GET" action="" class="row g-3 align-items-end" id="filterForm">
            <div class="col-md-4">
                <label class="form-label small mb-1 fw-medium text-muted">Cari (Nama, No Paspor, Stambuk)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Ketik pencarian..." onchange="document.getElementById('filterForm').submit()">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1 fw-medium text-muted">Kepengurusan</label>
                <select name="kep" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Semua Kepengurusan --</option>
                    <?php foreach ($kepList ?? [] as $k): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= ($kep === $k) ? 'selected' : '' ?>><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-medium" onclick="openPrintPopup('itas')">
                    <i class="bi bi-printer me-1"></i> Print ITAS
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-medium ms-1" onclick="openPrintPopup('paspor')">
                    <i class="bi bi-printer me-1"></i> Print Paspor
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$kepListJson    = json_encode($kepList    ?? []);
$pondokListJson = json_encode($pondokList ?? []);
?>
<script>
const _kepList    = <?= $kepListJson ?>;
const _pondokList = <?= $pondokListJson ?>;

async function openPrintPopup(type) {
    const docLabel = type === 'itas' ? 'ITAS' : 'Paspor';

    const buildOptions = (list, allLabel) => {
        let html = `<option value="">${allLabel}</option>`;
        list.forEach(v => { html += `<option value="${v}">${v}</option>`; });
        return html;
    };

    const { value: formValues } = await Swal.fire({
        title: `<div class="d-flex align-items-center gap-3 mb-2"><div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: ${type==='itas'?'#19875415':'#0d6efd15'};"><i class="bi bi-printer-fill fs-5 ${type==='itas'?'text-success':'text-primary'}"></i></div><span style="font-size:1.15rem; font-weight:700; text-align:left; line-height:1.2;">Print Rekap<br><span class="text-muted" style="font-size:.9rem; font-weight:500;">Dokumen ${docLabel}</span></span></div>`,
        html: `
            <div class="text-start mt-4">
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: .75rem; color: #495057; letter-spacing: .5px;">FILTER KEPENGURUSAN</label>
                    <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-building text-secondary"></i></span>
                        <select id="swal-kep" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .85rem;">
                            ${buildOptions(_kepList, 'Semua Kepengurusan')}
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: .75rem; color: #495057; letter-spacing: .5px;">FILTER PONDOK</label>
                    <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-house-door text-secondary"></i></span>
                        <select id="swal-pondok" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .85rem;">
                            ${buildOptions(_pondokList, 'Semua Pondok')}
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold" style="font-size: .75rem; color: #495057; letter-spacing: .5px;">TAMPILKAN SANTRI</label>
                    <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-people text-secondary"></i></span>
                        <select id="swal-urgency" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .85rem;">
                            <option value="period">✅ Yang Sudah Masuk Periode (Default)</option>
                            <option value="urgent">âš ï¸ Urgent Saja (Habis ≤ 1 Bulan)</option>
                            <option value="all">ðŸ‘¥ Semua Santri Aktif</option>
                        </select>
                    </div>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer me-1"></i> Buka Preview',
        cancelButtonText: 'Batal',
        buttonsStyling: false,
        customClass: { 
            popup: 'rounded-4 shadow-lg border-0',
            confirmButton: 'btn btn-primary rounded-pill px-4 fw-medium mx-1 shadow-sm',
            cancelButton: 'btn btn-light rounded-pill px-4 fw-medium mx-1 border shadow-sm'
        },
        preConfirm: () => ({
            kep:     document.getElementById('swal-kep').value,
            pondok:  document.getElementById('swal-pondok').value,
            urgency: document.getElementById('swal-urgency').value,
        })
    });

    if (!formValues) return;

    const params = new URLSearchParams({
        type,
        kep:     formValues.kep,
        pondok:  formValues.pondok,
        urgency: formValues.urgency,
    });
    window.open('<?= API_URL ?>/auto-rekap/print?' + params.toString(), '_blank');
}
</script>

<!-- KARTU ANALITIK -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-success bg-opacity-10 h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-card-checklist fs-4"></i>
                </div>
                <div>
                    <div class="text-success small fw-bold mb-1">TOTAL ITAS KRITIS (≤ 3 Bln)</div>
                    <h3 class="m-0 fw-bold text-dark"><?= $itasTotal ?> <span class="fs-6 text-muted fw-normal">Santri</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-danger bg-opacity-10 h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-danger small fw-bold mb-1">URGENT ITAS (≤ 1 Bln)</div>
                    <h3 class="m-0 fw-bold text-dark"><?= $itasUrgent ?> <span class="fs-6 text-muted fw-normal">Santri</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-primary bg-opacity-10 h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-journal-album fs-4"></i>
                </div>
                <div>
                    <div class="text-primary small fw-bold mb-1">TOTAL PASPOR KRITIS (≤ 18 Bln)</div>
                    <h3 class="m-0 fw-bold text-dark"><?= $pasporTotal ?> <span class="fs-6 text-muted fw-normal">Santri</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-warning bg-opacity-10 h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-hourglass-bottom fs-4"></i>
                </div>
                <div>
                    <div class="text-warning-emphasis small fw-bold mb-1">URGENT PASPOR (≤ 6 Bln)</div>
                    <h3 class="m-0 fw-bold text-dark"><?= $pasporUrgent ?> <span class="fs-6 text-muted fw-normal">Santri</span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SISTEM TAB -->
<ul class="nav nav-pills mb-3 gap-2" id="rekap-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="itas-tab" data-bs-toggle="pill" data-bs-target="#itas-pane" type="button" role="tab" aria-controls="itas-pane" aria-selected="true">
            <i class="bi bi-card-checklist me-1"></i> Perpanjangan ITAS
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm bg-white text-secondary border" id="paspor-tab" data-bs-toggle="pill" data-bs-target="#paspor-pane" type="button" role="tab" aria-controls="paspor-pane" aria-selected="false">
            <i class="bi bi-journal-album me-1"></i> Perpanjangan Paspor
        </button>
    </li>
</ul>

<div class="tab-content" id="rekap-tabsContent">
    
    <!-- TAB ITAS -->
    <div class="tab-pane fade show active" id="itas-pane" role="tabpanel" aria-labelledby="itas-tab" tabindex="0">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-table me-2 text-success"></i>Data Santri (H-90 Hari dari Exp ITAS)</h6>
                </div>
                <button id="btnBulkProcess" onclick="bukaProsesBulk()" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium shadow-sm" style="display: none;">
                    <i class="bi bi-play-circle me-1"></i>Buka Job Desk Terpilih (<span id="bulkCount">0</span>)
                </button>
            </div>
            <div class="table-responsive" style="height: 60vh;">
                <table class="table table-hover align-middle m-0" style="font-size: 0.85rem;">
                    <thead class="table-light sticky-top shadow-sm" style="z-index: 1;">
                        <tr>
                            <th class="ps-4 border-0" style="width: 40px;">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" id="checkAllItas" onchange="toggleAllItas(this)">
                                </div>
                            </th>
                            <th class="border-0">No Paspor</th>
                            <th class="border-0">Nama Santri</th>
                            <th class="border-0">Pondok / Kelas</th>
                            <th class="border-0">Daerah Asal</th>
                            <th class="pe-4 border-0">Tanggal Exp ITAS</th>
                        </tr>
                        <tr class="table-secondary column-filters">
                            <th class="ps-4 border-bottom-0"></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 1)" placeholder="Cari..."></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 2)" placeholder="Cari..."></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 3)" placeholder="Cari..."></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 4)" placeholder="Cari..."></th>
                            <th class="pe-4 border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 5)" placeholder="Cari..."></th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php 
                        $today = new DateTime();
                        $today->setTime(0, 0, 0); // Normalize to start of day
                        foreach ($dataItas as $s): 
                            $jobDesks = $activeJobDeskKdsSet[$s['kds']] ?? [];
                            $isOnJobDesk = count($jobDesks) > 0;
                            $expDate = new DateTime($s['exp_itas']);
                            $expDate->setTime(0, 0, 0);
                            $diffDays = (int) $today->diff($expDate)->format('%r%a');
                            $isExpired = $expDate < $today;
                            $isUrgent = $isExpired || $diffDays <= 30;
                        ?>
                        <tr class="<?= $isExpired ? 'bg-danger bg-opacity-25' : ($isUrgent ? 'bg-danger bg-opacity-10' : '') ?>">
                            <td class="ps-4">
                                <div class="form-check m-0">
                                    <input class="form-check-input itas-cb row-cb" type="checkbox" onchange="updateBulkButton()"
                                        value="<?= $s['kds'] ?>"
                                        data-nama="<?= htmlspecialchars((string)($s['nama'] ?? '')) ?>"
                                        <?php if ($isOnJobDesk): ?>
                                        data-jobdesks="<?= htmlspecialchars(json_encode($jobDesks)) ?>"
                                        <?php endif; ?>
                                    >
                                </div>
                            </td>
                            <td class="text-nowrap fw-medium text-secondary"><?= htmlspecialchars((string)($s['no_paspor'] ?? '-')) ?></td>
                            <td class="fw-semibold">
                                <?= htmlspecialchars((string)($s['nama'] ?? '-')) ?>
                                <?php 
                                    $myKep = $_SESSION['def_kepengurusan'] ?? '';
                                    $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                                    $isPindahan = ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0);
                                ?>
                                <?php if ($isPindahan || $isOnJobDesk): ?>
                                <div class="d-flex flex-column align-items-start gap-1 mt-1">
                                    <?php if ($isPindahan): ?>
                                        <span class="badge bg-warning text-dark fw-medium border border-warning" style="font-size: 0.6rem;">Pindahan</span>
                                    <?php endif; ?>
                                    <?php if ($isOnJobDesk): ?>
                                        <?php foreach ($jobDesks as $jd): ?>
                                        <a href="<?= API_URL ?>/job-desk?q=<?= urlencode($s['kds']) ?>" target="_blank"
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-medium text-decoration-none d-inline-block text-truncate"
                                            style="font-size: 0.6rem; max-width: 200px; text-align: left;"
                                            title="<?= htmlspecialchars($jd['nama_proses']) ?><?= !empty($jd['tahap_saat_ini']) ? ' › ' . htmlspecialchars($jd['tahap_saat_ini']) : '' ?>">
                                            <i class="bi bi-rocket-takeoff-fill me-1"></i><?= htmlspecialchars($jd['nama_proses']) ?><?php if (!empty($jd['tahap_saat_ini'])): ?> › <em><?= htmlspecialchars($jd['tahap_saat_ini']) ?></em><?php endif; ?>
                                        </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars((string)($s['pondok'] ?? '-')) ?> - <?= htmlspecialchars((string)($s['kelas'] ?? '-')) ?></span></td>
                            <td><?= htmlspecialchars((string)($s['daerah'] ?? '-')) ?></td>
                            <?php
                                $expStr = $s['exp_itas'] ?? '';
                                $formattedDate = $expStr ? date('d-M-Y', strtotime($expStr)) : '-';
                                $colorClass = $isExpired ? 'text-danger fw-bold' : 'text-warning fw-bold';
                            ?>
                            <td class="pe-4 <?= $colorClass ?>">
                                <i class="bi bi-calendar-event me-1"></i><?= $formattedDate ?>
                                <?php if ($isExpired): ?>
                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                <?php else: ?>
                                    <?php $textI = $diffDays > 90 ? floor($diffDays / 30) . ' bln lagi' : $diffDays . ' hari lagi'; ?>
                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dataItas)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>Bebas Kritis ITAS</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-light border-top p-3 d-flex justify-content-between align-items-center small rounded-bottom-4">
                <span class="text-muted fw-medium">Menampilkan Total Data:</span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 fs-6"><?= count($dataItas) ?> Santri</span>
            </div>
        </div>
    </div>

    <!-- TAB PASPOR -->
    <div class="tab-pane fade" id="paspor-pane" role="tabpanel" aria-labelledby="paspor-tab" tabindex="0">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i>Data Santri (H-540 Hari dari Exp Paspor)</h6>
                </div>
            </div>
            <div class="table-responsive" style="height: 60vh;">
                <table class="table table-hover align-middle m-0" style="font-size: 0.85rem;">
                    <thead class="table-light sticky-top shadow-sm" style="z-index: 1;">
                        <tr>
                            <th class="ps-4 border-0">No Paspor</th>
                            <th class="border-0">Nama Santri</th>
                            <th class="border-0">Pondok / Kelas</th>
                            <th class="border-0">Daerah Asal</th>
                            <th class="pe-4 border-0">Tanggal Exp Paspor</th>
                        </tr>
                        <tr class="table-secondary column-filters">
                            <th class="ps-4 border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 0)" placeholder="Cari..."></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 1)" placeholder="Cari..."></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 2)" placeholder="Cari..."></th>
                            <th class="border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 3)" placeholder="Cari..."></th>
                            <th class="pe-4 border-bottom-0"><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 4)" placeholder="Cari..."></th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php 
                        $today = new DateTime();
                        $today->setTime(0, 0, 0);
                        foreach ($dataPaspor as $row): 
                            $isPindahan = (!empty($myKep) && trim((string)($row['kepengurusan'] ?? '')) !== trim($myKep));
                            $expStr = $row['exp_paspor'] ?? '';
                            $expDate = $expStr ? new DateTime($expStr) : null;
                            if ($expDate) $expDate->setTime(0, 0, 0);
                            $isExpired = $expDate && $expDate < $today;
                            $diffDays = $expDate ? (int) $today->diff($expDate)->format('%r%a') : null;
                            $isUrgent = $isExpired || ($diffDays !== null && $diffDays <= 180);
                        ?>
                            <tr class="<?= $isExpired ? 'bg-danger bg-opacity-25' : ($isUrgent ? 'bg-danger bg-opacity-10' : '') ?>">
                                <td class="ps-4 text-nowrap fw-medium text-secondary"><?= htmlspecialchars((string)($row['no_paspor'] ?? '-')) ?></td>
                                <td class="fw-bold text-dark" style="max-width: 250px;">
                                    <?= htmlspecialchars((string)($row['nama'] ?? '-')) ?>
                                    <?php
                                        $jobDesks = $activeJobDeskKdsSet[$row['kds']] ?? [];
                                        $isOnJobDesk = count($jobDesks) > 0;
                                    ?>
                                    <?php if ($isPindahan || $isOnJobDesk): ?>
                                    <div class="d-flex flex-column align-items-start gap-1 mt-1">
                                        <?php if ($isPindahan): ?>
                                            <span class="badge bg-warning text-dark fw-medium border border-warning" style="font-size: 0.6rem;">Pindahan</span>
                                        <?php endif; ?>
                                        <?php if ($isOnJobDesk): ?>
                                            <?php foreach ($jobDesks as $jd): ?>
                                            <a href="<?= API_URL ?>/job-desk?q=<?= urlencode($row['kds']) ?>" target="_blank"
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-medium text-decoration-none d-inline-block text-truncate"
                                                style="font-size: 0.6rem; max-width: 200px; text-align: left;"
                                                title="<?= htmlspecialchars($jd['nama_proses']) ?><?= !empty($jd['tahap_saat_ini']) ? ' › ' . htmlspecialchars($jd['tahap_saat_ini']) : '' ?>">
                                                <i class="bi bi-rocket-takeoff-fill me-1"></i><?= htmlspecialchars($jd['nama_proses']) ?><?php if (!empty($jd['tahap_saat_ini'])): ?> › <em><?= htmlspecialchars($jd['tahap_saat_ini']) ?></em><?php endif; ?>
                                            </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars((string)($row['pondok'] ?? '-')) ?> - <?= htmlspecialchars((string)($row['kelas'] ?? '-')) ?></span></td>
                                <td><?= htmlspecialchars((string)($row['daerah'] ?? '-')) ?></td>
                                <?php 
                                    $formattedDate = $expStr ? date('d-M-Y', strtotime($expStr)) : '-';
                                    $colorClass = $isExpired ? 'text-danger fw-bold' : 'text-warning fw-bold';
                                ?>
                                <td class="pe-4 <?= $colorClass ?>">
                                    <i class="bi bi-calendar-event me-1"></i><?= $formattedDate ?>
                                    <?php if ($isExpired): ?>
                                        <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                    <?php elseif ($diffDays !== null): ?>
                                        <?php $textP = $diffDays > 90 ? floor($diffDays / 30) . ' bln lagi' : $diffDays . ' hari lagi'; ?>
                                        <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textP ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dataPaspor)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>Bebas Kritis Paspor</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-light border-top p-3 d-flex justify-content-between align-items-center small rounded-bottom-4">
                <span class="text-muted fw-medium">Menampilkan Total Data:</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 fs-6"><?= count($dataPaspor) ?> Santri</span>
            </div>
        </div>
    </div>
</div>


<script>
function filterTable(input, colIndex) {
    const table = input.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    const filterInputs = Array.from(table.querySelectorAll('.column-filters input'));

    const firstTh = table.querySelector('thead tr:first-child th:first-child');
    const hasCheckboxCol = firstTh && firstTh.querySelector('input[type="checkbox"]');
    const colOffset = hasCheckboxCol ? 1 : 0;

    rows.forEach(row => {
        if (row.cells.length === 1) return; 

        let show = true;
        filterInputs.forEach((inp, i) => {
            const filterVal = inp.value.toLowerCase();
            if (filterVal) {
                const cell = row.cells[i + colOffset];
                const cellText = cell ? cell.textContent.toLowerCase() : '';
                if (!cellText.includes(filterVal)) show = false;
            }
        });
        row.style.display = show ? '' : 'none';
    });
}

function toggleAllItas(source) {
    const checkboxes = document.querySelectorAll('.itas-cb');
    checkboxes.forEach(cb => {
        if (cb.closest('tr').style.display !== 'none') {
            cb.checked = source.checked;
        }
    });
    updateBulkButton();
}

function updateBulkButton() {
    const allCbs = document.querySelectorAll('.itas-cb');
    let visibleCount = 0;
    let checkedCount = 0;
    allCbs.forEach(c => {
        if (c.closest('tr').style.display !== 'none') {
            visibleCount++;
            if (c.checked) checkedCount++;
        }
    });

    const checked = document.querySelectorAll('.itas-cb:checked').length;
    const btn = document.getElementById('btnBulkProcess');
    const countSpan = document.getElementById('bulkCount');
    
    if (checked > 0) {
        btn.style.display = 'inline-block';
        countSpan.textContent = checked;
    } else {
        btn.style.display = 'none';
    }
    
    const checkAll = document.getElementById('checkAllItas');
    if (checkAll) {
        checkAll.checked = (visibleCount > 0 && checkedCount === visibleCount);
    }
}

function bukaProsesBulk(type = 'itas') {
    let bulkSelected = [];
    let conflicts = [];
    let processId = type === 'itas' ? 1 : 2; 
    let processName = type === 'itas' ? 'Perpanjangan ITAS' : 'Perpanjangan Paspor';

    document.querySelectorAll('.' + type + '-cb:checked').forEach(cb => {
        const kds    = cb.value;
        const nama   = cb.getAttribute('data-nama') || kds;
        const jobdesksAttr = cb.getAttribute('data-jobdesks');
        bulkSelected.push({ kds });
        
        if (jobdesksAttr) {
            try {
                const jobdesks = JSON.parse(jobdesksAttr);
                if (jobdesks.length > 0) {
                    conflicts.push({ nama, jobdesks });
                }
            } catch(e) {}
        }
    });

    if (conflicts.length > 0) {
        let warningRows = conflicts.map(c => {
            let badges = c.jobdesks.map(jd => {
                const tahapLabel = jd.tahap_saat_ini ? ` &rsaquo; <em>${jd.tahap_saat_ini}</em>` : '';
                return `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-wrap text-start mb-1 d-block" style="line-height: 1.4;">${jd.nama_proses}${tahapLabel}</span>`;
            }).join('');
            
            return `<tr>
                <td class="fw-bold">${c.nama}</td>
                <td class="pb-1">${badges}</td>
            </tr>`;
        }).join('');

        Swal.fire({
            icon: 'warning',
            title: '<span style="font-size: 1rem;">âš ï¸ Peringatan: Ada Proses Aktif!</span>',
            html: `
                <div class="text-start small text-muted mb-3">
                    Santri berikut <strong>sudah dalam proses Job Desk aktif</strong>. 
                    Memulai proses baru tidak akan membatalkan proses yang sedang berjalan.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-start small">
                        <thead><tr class="table-warning"><th style="width: 40%;">Santri</th><th style="width: 60%;">Proses Aktif Saat Ini</th></tr></thead>
                        <tbody>${warningRows}</tbody>
                    </table>
                </div>
                <div class="small text-muted mt-2">Apakah Anda tetap ingin melanjutkan membuka proses baru untuk <strong>${bulkSelected.length} santri</strong> terpilih?</div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-play-circle me-1"></i> Tetap Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#fd7e14',
            customClass: { popup: 'shadow-lg rounded-4' }
        }).then(result => {
            if (result.isConfirmed) doCreateBulk(bulkSelected);
        });
        return;
    }

    // Tidak ada konflik — konfirmasi biasa
    Swal.fire({
        title: '<span style="font-size: 1.1rem;">Buka Proses Massal?</span>',
        html: `Anda akan membuka proses Job Desk Perpanjangan ITAS untuk <strong>${bulkSelected.length} santri</strong>.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Proses Sekarang',
        cancelButtonText: 'Batal',
        customClass: { popup: 'shadow-sm rounded-4' }
    }).then(result => {
        if (result.isConfirmed) doCreateBulk(bulkSelected);
    });
}

function doCreateBulk(items) {
    Swal.fire({
        title: 'Memproses...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('<?= API_URL ?>/api/job-desk/create', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ items: items, process_id: 1 })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: res.message,
                timer: 4000,
                showConfirmButton: true,
                confirmButtonText: 'Ke Job Desk',
                confirmButtonColor: '#0d6efd',
                customClass: { popup: 'shadow-sm rounded-4' }
            }).then(() => {
                window.location.href = '<?= API_URL ?>/job-desk';
            });
        } else {
            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan', 'error');
        }
    }).catch(() => {
        Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
    });
}
</script>
