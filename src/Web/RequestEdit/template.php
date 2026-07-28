<?php
declare(strict_types=1);

use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $requests
 * @var string $myKepengurusan
 */
$this->setTitle('Persetujuan Data | Sistem Informasi');
?>

<?php
function groupChanges($changes, $oldData, $row) {
    $groups = [
        'Biodata' => [],
        'Dokumen Paspor' => [],
        'Dokumen ITAS' => [],
        'Lainnya' => []
    ];
    
    foreach ($changes as $key => $val) {
        if (str_starts_with($key, '_')) continue;
        
        $oldKey = $key;
        if ($key === 'no_sktt') $oldKey = 'nik';
        $oldVal = array_key_exists($key, $oldData) ? $oldData[$key] : ($row[$oldKey] ?? '');
        
        $item = ['key' => $key, 'val' => $val, 'oldVal' => $oldVal];
        
        $lowerKey = strtolower($key);
        if (str_contains($lowerKey, 'paspor')) {
            $groups['Dokumen Paspor'][] = $item;
        } elseif (str_contains($lowerKey, 'itas')) {
            $groups['Dokumen ITAS'][] = $item;
        } elseif (in_array($lowerKey, ['stambuk', 'nama', 'kelas', 'rayon', 'pondok', 'negara', 'kewarganegaraan', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'nama_ayah', 'nama_ibu', 'no_sktt', 'no_ic', 'alamat', 'ukuran_baju'])) {
            $groups['Biodata'][] = $item;
        } else {
            $groups['Lainnya'][] = $item;
        }
    }
    
    return array_filter($groups, fn($g) => count($g) > 0);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706);">
            <i class="bi bi-file-earmark-check-fill text-white fs-4"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Persetujuan Usulan Data</h4>
            <div class="text-muted small fw-medium mt-1">
                Tinjau usulan perubahan kolom terbatas dari Pondok Pindahan
            </div>
        </div>
    </div>
    <div>
        <span class="badge bg-warning text-dark border border-warning fs-6 px-3 py-2 shadow-sm rounded-pill">
            <i class="bi bi-hourglass-split me-1"></i> <?= count($requests) ?> Menunggu
        </span>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4 gap-2" id="req-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" type="button" role="tab" aria-controls="incoming" aria-selected="true">
            <i class="bi bi-inbox-fill me-1"></i> Menunggu Persetujuan Anda 
            <span class="badge bg-white text-primary ms-2 rounded-pill"><?= count($requests) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-secondary rounded-pill px-4 fw-bold border-0" style="background-color: transparent;" onmouseover="this.style.backgroundColor='#e9ecef'" onmouseout="this.style.backgroundColor='transparent'" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" type="button" role="tab" aria-controls="outgoing" aria-selected="false">
            <i class="bi bi-send-fill me-1"></i> Pengajuan Saya
            <span class="badge bg-secondary ms-2 rounded-pill"><?= count($outgoingRequests) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-secondary rounded-pill px-4 fw-bold border-0" style="background-color: transparent;" onmouseover="this.style.backgroundColor='#e9ecef'" onmouseout="this.style.backgroundColor='transparent'" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">
            <i class="bi bi-clock-history me-1"></i> Riwayat Keputusan
            <span class="badge bg-secondary ms-2 rounded-pill"><?= count($historyRequests) ?></span>
        </button>
    </li>
</ul>

<script>
    // Tab persistence
    document.addEventListener('DOMContentLoaded', function() {
        const activeTab = sessionStorage.getItem('req-active-tab');
        if (activeTab) {
            const tabEl = document.querySelector(`button[data-bs-target="${activeTab}"]`);
            if (tabEl) {
                new bootstrap.Tab(tabEl).show();
            }
        }
        
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                sessionStorage.setItem('req-active-tab', event.target.dataset.bsTarget);
            });
        });
    });
</script>

<div class="tab-content" id="req-tabsContent">
    <!-- TAB 1: INCOMING REQUESTS -->
    <div class="tab-pane fade show active" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 my-5 bg-white rounded-4 shadow-sm border border-light">
                <i class="bi bi-clipboard2-check text-muted" style="font-size: 5rem; opacity: 0.2;"></i>
                <h4 class="fw-bold mt-4 text-secondary">Semua Bersih!</h4>
                <p class="text-muted">Tidak ada usulan perubahan data santri yang memerlukan persetujuan Anda saat ini.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($requests as $r): 
                    $changes = json_decode($r['requested_changes'], true) ?? [];
                    $oldData = json_decode($r['old_values'] ?? '', true) ?? [];
                ?>
                    <div class="col-md-6 col-lg-4" id="req-card-<?= $r['id'] ?>">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="transition: transform 0.2s;">
                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded px-2 py-1 small fw-bold">
                                        <?= htmlspecialchars((string)($r['stambuk'] ?? '0')) ?>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></small>
                            </div>
                            <div class="card-body p-4 bg-light bg-opacity-50">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($r['nama']) ?>"><?= htmlspecialchars($r['nama']) ?></h5>
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="bi bi-box-arrow-in-right me-1 text-primary"></i> 
                                    Diajukan oleh: <strong class="ms-1 text-dark"><?= htmlspecialchars($r['instansi_pengaju'] ?? 'Unknown') ?> (<?= htmlspecialchars($r['kep_pengaju'] ?? '') ?>)</strong>
                                </div>
                                
                                <div class="rounded-3 border overflow-hidden">
                                    <div class="bg-white border-bottom px-3 py-2 small fw-bold text-secondary text-center" style="background-color: #f8f9fa !important;">
                                        <?php 
                                            $visibleCount = 0;
                                            foreach ($changes as $key => $val) {
                                                if (str_starts_with($key, '_')) continue;
                                                $visibleCount++;
                                            }
                                        ?>
                                        Total <span class="text-primary"><?= $visibleCount ?></span> Kolom Diusulkan
                                    </div>
                                    <div class="bg-white p-3 position-relative">
                                        <div class="small fw-bold text-muted mb-2"><i class="bi bi-eye me-1"></i> Sekilas Perubahan:</div>
                                        <?php 
                                        $glimpse = 0;
                                        foreach ($changes as $k => $v) {
                                            if (str_starts_with($k, '_')) continue;
                                            if ($glimpse >= 2) break;
                                            $oKey = $k === 'no_sktt' ? 'nik' : $k;
                                            $oVal = array_key_exists($k, $oldData) ? $oldData[$k] : ($r[$oKey] ?? '');
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                            <span class="text-capitalize text-truncate" style="max-width: 35%; font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                                            <div class="text-truncate text-end" style="max-width: 60%;">
                                                <span class="text-decoration-line-through me-1" style="font-size: 0.7rem; opacity: 0.7;"><?= htmlspecialchars((string)$oVal) ?: '-' ?></span>
                                                <i class="bi bi-arrow-right text-warning mx-1" style="font-size: 0.7rem;"></i>
                                                <strong class="text-dark" style="font-size: 0.75rem;"><?= htmlspecialchars((string)$v) ?: '-' ?></strong>
                                            </div>
                                        </div>
                                        <?php $glimpse++; } ?>
                                        
                                        <div class="mt-3 text-center position-relative" style="z-index: 2;">
                                            <button type="button" class="btn btn-sm btn-light border-primary text-primary rounded-pill px-4 shadow-sm fw-bold w-100" data-bs-toggle="modal" data-bs-target="#modalIn-<?= $r['id'] ?>" style="transition: all 0.2s;" onmouseover="this.classList.replace('btn-light','btn-primary'); this.classList.replace('text-primary','text-white');" onmouseout="this.classList.replace('btn-primary','btn-light'); this.classList.replace('text-white','text-primary');">
                                                Lihat Selengkapnya <i class="bi bi-chevron-right ms-1"></i>
                                            </button>
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50" style="background: linear-gradient(to bottom, transparent, white); pointer-events: none; z-index: 1;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 p-3 pb-4 d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-medium btn-sm" onclick="rejectReq(<?= $r['id'] ?>)">
                                    <i class="bi bi-x-circle me-1"></i> Tolak
                                </button>
                                <button type="button" class="btn btn-success rounded-pill px-4 fw-medium btn-sm shadow-sm" onclick="approveReq(<?= $r['id'] ?>)">
                                    <i class="bi bi-check-circle me-1"></i> Setujui
                                </button>
                            </div>
                        </div>

                        <!-- Modal Detail Incoming -->
                        <div class="modal fade" id="modalIn-<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <div class="modal-header border-bottom-0 bg-light rounded-top-4 pb-2">
                                        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Usulan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-white">
                                        <?php 
                                        $grouped = groupChanges($changes, $oldData, $r);
                                        foreach ($grouped as $groupName => $items): 
                                            $icon = 'bi-record-circle';
                                            $isGroupCheck = false;
                                            $groupKey = '';
                                            if ($groupName === 'Biodata') $icon = 'bi-person-vcard';
                                            elseif ($groupName === 'Dokumen Paspor') { $icon = 'bi-passport'; $isGroupCheck = true; $groupKey = '_paspor_data'; }
                                            elseif ($groupName === 'Dokumen ITAS') { $icon = 'bi-card-heading'; $isGroupCheck = true; $groupKey = '_itas_data'; }
                                        ?>
                                            <div class="px-3 py-2 small fw-bold text-secondary border-bottom d-flex justify-content-between align-items-center" style="background-color: #fcfcfc;">
                                                <div><i class="bi <?= $icon ?> me-1 text-primary"></i> <?= $groupName ?></div>
                                                <?php if ($isGroupCheck): ?>
                                                    <div class="form-check form-switch m-0" title="Setujui/Tolak seluruh <?= $groupName ?>">
                                                        <input class="form-check-input chk-partial-<?= $r['id'] ?>" type="checkbox" value="<?= $groupKey ?>" checked style="cursor: pointer;" onchange="toggleGroup(this, '<?= $groupName ?>', <?= $r['id'] ?>)">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <ul class="list-group list-group-flush small border-bottom mb-0">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="list-group-item px-3 py-2 bg-transparent d-flex flex-column border-0 border-bottom">
                                                        <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                                            <span class="text-muted fw-bold text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $item['key'])) ?></span>
                                                            <?php if (!$isGroupCheck): ?>
                                                                <div class="form-check m-0">
                                                                    <input class="form-check-input chk-partial-<?= $r['id'] ?>" type="checkbox" value="<?= htmlspecialchars($item['key']) ?>" checked style="cursor: pointer; width: 1.1rem; height: 1.1rem;">
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between w-100 <?= $isGroupCheck ? "group-item-{$r['id']}-" . str_replace(' ', '', $groupName) : '' ?>" style="transition: opacity 0.2s;">
                                                            <div class="text-secondary text-decoration-line-through small" style="max-width: 45%; word-break: break-all;"><?= htmlspecialchars((string)$item['oldVal']) ?: '<em class="text-muted fw-normal">(Kosong)</em>' ?></div>
                                                            <i class="bi bi-arrow-right text-warning mx-1"></i>
                                                            <strong class="text-dark text-end" style="max-width: 45%; word-break: break-all;"><?= htmlspecialchars((string)$item['val']) ?: '<em class="text-muted fw-normal">(Kosong)</em>' ?></strong>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light rounded-bottom-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="rejectReq(<?= $r['id'] ?>)">Tolak Semua</button>
                                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" onclick="approvePartialReq(<?= $r['id'] ?>)">
                                            <i class="bi bi-check-circle me-1"></i> Setujui Pilihan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB 2: OUTGOING REQUESTS -->
    <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
        <?php if (empty($outgoingRequests)): ?>
            <div class="text-center py-5 my-5 bg-white rounded-4 shadow-sm border border-light">
                <i class="bi bi-send-slash text-muted" style="font-size: 5rem; opacity: 0.2;"></i>
                <h4 class="fw-bold mt-4 text-secondary">Belum Ada Pengajuan</h4>
                <p class="text-muted">Anda belum mengajukan usulan perubahan data ke instansi/pondok lain.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($outgoingRequests as $o): 
                    $changesOut = json_decode($o['requested_changes'], true) ?? [];
                    $oldData = json_decode($o['old_values'] ?? '', true) ?? [];
                ?>
                    <div class="col-md-6 col-lg-4" id="req-card-<?= $o['id'] ?>">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded px-2 py-1 small fw-bold">
                                        <?= htmlspecialchars((string)($o['stambuk'] ?? '0')) ?>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></small>
                            </div>
                            <div class="card-body p-4 bg-light bg-opacity-50">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($o['nama']) ?>"><?= htmlspecialchars($o['nama']) ?></h5>
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="bi bi-arrow-right-circle me-1 text-secondary"></i> 
                                    Tujuan: <strong class="ms-1 text-dark"><?= htmlspecialchars($o['kep_tujuan'] ?? 'Unknown') ?></strong>
                                </div>
                                
                                <div class="rounded-3 border overflow-hidden mb-3">
                                    <div class="bg-white border-bottom px-3 py-2 small fw-bold text-secondary text-center" style="background-color: #f8f9fa !important;">
                                        <?php 
                                            $visibleCount = 0;
                                            foreach ($changesOut as $key => $val) {
                                                if (str_starts_with($key, '_')) continue;
                                                $visibleCount++;
                                            }
                                        ?>
                                        Total <span class="text-primary"><?= $visibleCount ?></span> Kolom Diusulkan
                                    </div>
                                    <div class="bg-white p-3 position-relative">
                                        <div class="small fw-bold text-muted mb-2"><i class="bi bi-eye me-1"></i> Sekilas Perubahan:</div>
                                        <?php 
                                        $glimpse = 0;
                                        foreach ($changesOut as $k => $v) {
                                            if (str_starts_with($k, '_')) continue;
                                            if ($glimpse >= 2) break;
                                            $oKey = $k === 'no_sktt' ? 'nik' : $k;
                                            $oVal = array_key_exists($k, $oldData) ? $oldData[$k] : ($o[$oKey] ?? '');
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                            <span class="text-capitalize text-truncate" style="max-width: 35%; font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                                            <div class="text-truncate text-end" style="max-width: 60%;">
                                                <span class="text-decoration-line-through me-1" style="font-size: 0.7rem; opacity: 0.7;"><?= htmlspecialchars((string)$oVal) ?: '-' ?></span>
                                                <i class="bi bi-arrow-right text-warning mx-1" style="font-size: 0.7rem;"></i>
                                                <strong class="text-dark" style="font-size: 0.75rem;"><?= htmlspecialchars((string)$v) ?: '-' ?></strong>
                                            </div>
                                        </div>
                                        <?php $glimpse++; } ?>
                                        
                                        <div class="mt-3 text-center position-relative" style="z-index: 2;">
                                            <button type="button" class="btn btn-sm btn-light border-primary text-primary rounded-pill px-4 shadow-sm fw-bold w-100" data-bs-toggle="modal" data-bs-target="#modalOut-<?= $o['id'] ?>" style="transition: all 0.2s;" onmouseover="this.classList.replace('btn-light','btn-primary'); this.classList.replace('text-primary','text-white');" onmouseout="this.classList.replace('btn-primary','btn-light'); this.classList.replace('text-white','text-primary');">
                                                Lihat Selengkapnya <i class="bi bi-chevron-right ms-1"></i>
                                            </button>
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50" style="background: linear-gradient(to bottom, transparent, white); pointer-events: none; z-index: 1;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 p-3 text-center">
                                <?php if ($o['status'] === 'pending'): ?>
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        <span class="badge bg-warning text-dark border border-warning fs-6 px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill fw-bold" onclick="cancelReq(<?= $o['id'] ?>)">
                                            <i class="bi bi-trash-fill me-1"></i> Batalkan Pengajuan
                                        </button>
                                    </div>
                                <?php elseif ($o['status'] === 'approved'): ?>
                                    <span class="badge bg-success border border-success fs-6 px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
                                <?php else: ?>
                                    <span class="badge bg-danger border border-danger fs-6 px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-x-circle me-1"></i> Ditolak</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Modal Detail Outgoing -->
                        <div class="modal fade" id="modalOut-<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <div class="modal-header border-bottom-0 bg-light rounded-top-4 pb-2">
                                        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Pengajuan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-white">
                                        <?php 
                                        $groupedOut = groupChanges($changesOut, $oldData, $o);
                                        foreach ($groupedOut as $groupName => $items): 
                                            $icon = 'bi-record-circle';
                                            if ($groupName === 'Biodata') $icon = 'bi-person-vcard';
                                            elseif ($groupName === 'Dokumen Paspor') $icon = 'bi-passport';
                                            elseif ($groupName === 'Dokumen ITAS') $icon = 'bi-card-heading';
                                        ?>
                                            <div class="px-3 py-2 small fw-bold text-secondary border-bottom" style="background-color: #fcfcfc;">
                                                <i class="bi <?= $icon ?> me-1 text-primary"></i> <?= $groupName ?>
                                            </div>
                                            <ul class="list-group list-group-flush small border-bottom mb-0">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="list-group-item px-3 py-2 bg-transparent d-flex flex-column border-0 border-bottom">
                                                        <span class="text-muted fw-bold text-capitalize mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $item['key'])) ?></span>
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="text-secondary text-decoration-line-through small" style="max-width: 45%; word-break: break-all;"><?= htmlspecialchars((string)$item['oldVal']) ?: '<em class="text-muted fw-normal">(Kosong)</em>' ?></div>
                                                            <i class="bi bi-arrow-right text-warning mx-1"></i>
                                                            <strong class="text-dark text-end" style="max-width: 45%; word-break: break-all;"><?= htmlspecialchars((string)$item['val']) ?: '<em class="text-muted fw-normal">(Kosong)</em>' ?></strong>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB 3: HISTORY REQUESTS (Riwayat Keputusan Anda) -->
    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
        <?php if (empty($historyRequests)): ?>
            <div class="text-center py-5 my-5 bg-white rounded-4 shadow-sm border border-light">
                <i class="bi bi-clock-history text-muted" style="font-size: 5rem; opacity: 0.2;"></i>
                <h4 class="fw-bold mt-4 text-secondary">Belum Ada Riwayat</h4>
                <p class="text-muted">Riwayat persetujuan atau penolakan usulan data santri akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($historyRequests as $h): 
                    $changesHist = json_decode($h['requested_changes'], true) ?? [];
                    $oldData = json_decode($h['old_values'] ?? '', true) ?? [];
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="opacity: 0.85;">
                            <div class="card-header bg-light border-bottom p-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded px-2 py-1 small fw-bold">
                                        <?= htmlspecialchars((string)($h['stambuk'] ?? '0')) ?>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-calendar-check me-1"></i><?= date('d M Y', strtotime($h['created_at'])) ?></small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($h['nama']) ?>"><?= htmlspecialchars($h['nama']) ?></h5>
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="bi bi-building me-1 text-secondary"></i> 
                                    Dari: <strong class="ms-1 text-dark"><?= htmlspecialchars($h['instansi_pengaju'] ?? 'Unknown') ?> (<?= htmlspecialchars($h['kep_pengaju'] ?? '') ?>)</strong>
                                </div>
                                
                                <div class="rounded-3 border overflow-hidden mb-3">
                                    <div class="bg-white border-bottom px-3 py-2 small fw-bold text-secondary text-center" style="background-color: #f8f9fa !important;">
                                        <?php 
                                            $visibleCount = 0;
                                            foreach ($changesHist as $key => $val) {
                                                if (str_starts_with($key, '_')) continue;
                                                $visibleCount++;
                                            }
                                        ?>
                                        Total <span class="text-primary"><?= $visibleCount ?></span> Kolom Diusulkan
                                    </div>
                                    <div class="bg-white p-3 position-relative">
                                        <div class="small fw-bold text-muted mb-2"><i class="bi bi-eye me-1"></i> Sekilas Perubahan:</div>
                                        <?php 
                                        $glimpse = 0;
                                        foreach ($changesHist as $k => $v) {
                                            if (str_starts_with($k, '_')) continue;
                                            if ($glimpse >= 2) break;
                                            $oKey = $k === 'no_sktt' ? 'nik' : $k;
                                            $oVal = array_key_exists($k, $oldData) ? $oldData[$k] : ($h[$oKey] ?? '');
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                            <span class="text-capitalize text-truncate" style="max-width: 35%; font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                                            <div class="text-truncate text-end" style="max-width: 60%;">
                                                <span class="text-decoration-line-through me-1" style="font-size: 0.7rem; opacity: 0.7;"><?= htmlspecialchars((string)$oVal) ?: '-' ?></span>
                                                <i class="bi bi-arrow-right text-warning mx-1" style="font-size: 0.7rem;"></i>
                                                <strong class="text-dark" style="font-size: 0.75rem;"><?= htmlspecialchars((string)$v) ?: '-' ?></strong>
                                            </div>
                                        </div>
                                        <?php $glimpse++; } ?>
                                        
                                        <div class="mt-3 text-center position-relative" style="z-index: 2;">
                                            <button type="button" class="btn btn-sm btn-light border-primary text-primary rounded-pill px-4 shadow-sm fw-bold w-100" data-bs-toggle="modal" data-bs-target="#modalHist-<?= $h['id'] ?>" style="transition: all 0.2s;" onmouseover="this.classList.replace('btn-light','btn-primary'); this.classList.replace('text-primary','text-white');" onmouseout="this.classList.replace('btn-primary','btn-light'); this.classList.replace('text-white','text-primary');">
                                                Lihat Selengkapnya <i class="bi bi-chevron-right ms-1"></i>
                                            </button>
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 h-50" style="background: linear-gradient(to bottom, transparent, white); pointer-events: none; z-index: 1;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-top-0 p-3 text-center">
                                <?php if ($h['status'] === 'approved'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill fs-6"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
                                <?php elseif ($h['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill fs-6"><i class="bi bi-x-circle me-1"></i> Ditolak</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Modal Detail History -->
                        <div class="modal fade" id="modalHist-<?= $h['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <div class="modal-header border-bottom-0 bg-light rounded-top-4 pb-2">
                                        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Riwayat Keputusan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-white">
                                        <?php 
                                        $groupedHist = groupChanges($changesHist, $oldData, $h);
                                        foreach ($groupedHist as $groupName => $items): 
                                            $icon = 'bi-record-circle';
                                            if ($groupName === 'Biodata') $icon = 'bi-person-vcard';
                                            elseif ($groupName === 'Dokumen Paspor') $icon = 'bi-passport';
                                            elseif ($groupName === 'Dokumen ITAS') $icon = 'bi-card-heading';
                                        ?>
                                            <div class="px-3 py-2 small fw-bold text-secondary border-bottom" style="background-color: #fcfcfc;">
                                                <i class="bi <?= $icon ?> me-1 text-primary"></i> <?= $groupName ?>
                                            </div>
                                            <ul class="list-group list-group-flush small border-bottom mb-0">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="list-group-item px-3 py-2 bg-transparent d-flex flex-column border-0 border-bottom">
                                                        <span class="text-muted fw-bold text-capitalize mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $item['key'])) ?></span>
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="text-secondary text-decoration-line-through small" style="max-width: 45%; word-break: break-all;"><?= htmlspecialchars((string)$item['oldVal']) ?: '<em class="text-muted fw-normal">(Kosong)</em>' ?></div>
                                                            <i class="bi bi-arrow-right text-warning mx-1"></i>
                                                            <strong class="text-dark text-end" style="max-width: 45%; word-break: break-all;"><?= htmlspecialchars((string)$item['val']) ?: '<em class="text-muted fw-normal">(Kosong)</em>' ?></strong>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleGroup(cb, groupName, id) {
    const className = `group-item-${id}-${groupName.replace(/\s+/g, '')}`;
    const items = document.querySelectorAll('.' + className);
    items.forEach(el => {
        el.style.opacity = cb.checked ? '1' : '0.4';
    });
}

function approvePartialReq(id) {
    const checkboxes = document.querySelectorAll(`.chk-partial-${id}:checked`);
    if (checkboxes.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal satu perubahan yang akan disetujui, atau tolak semua jika tidak ada.', 'warning');
        return;
    }
    
    const approvedKeys = Array.from(checkboxes).map(cb => cb.value);
    
    Swal.fire({
        title: 'Setujui Pilihan?',
        text: 'Hanya data yang Anda pilih yang akan diterapkan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Close the modal
            const modalEl = document.getElementById(`modalIn-${id}`);
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if(modalInstance) modalInstance.hide();
            
            processReq(id, 'approve', { approved_keys: approvedKeys });
        }
    });
}

function approveReq(id) {
    // Legacy full approve (from the card button)
    // To make it consistent, we can just grab all checkboxes if they exist, or send nothing (which implies full approve)
    const checkboxes = document.querySelectorAll(`.chk-partial-${id}`);
    const approvedKeys = Array.from(checkboxes).map(cb => cb.value);
    
    Swal.fire({
        title: 'Setujui Perubahan?',
        text: 'Semua usulan perubahan pada kartu ini akan disetujui.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            processReq(id, 'approve', { approved_keys: approvedKeys });
        }
    });
}

function rejectReq(id) {
    Swal.fire({
        title: 'Tolak Perubahan?',
        text: 'Usulan akan dihapus dan data santri tidak akan berubah.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            processReq(id, 'reject');
        }
    });
}

function cancelReq(id) {
    Swal.fire({
        title: 'Batalkan Pengajuan?',
        text: 'Usulan akan dibatalkan dan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tutup'
    }).then((result) => {
        if (result.isConfirmed) {
            processReq(id, 'cancel');
        }
    });
}

function processReq(id, action, extraData = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    // Create Form Data
    const formData = new FormData();
    if (extraData.approved_keys) {
        extraData.approved_keys.forEach(key => {
            formData.append('approved_keys[]', key);
        });
    }

    fetch(`<?= API_URL ?>/api/request-edit/${id}/${action}`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire('Berhasil', res.message, 'success').then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Terjadi kesalahan pada sistem.', 'error');
        console.error(err);
    });
}
</script>
