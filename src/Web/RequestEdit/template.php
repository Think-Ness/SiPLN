<?php
declare(strict_types=1);

use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $requests
 * @var array $outgoingRequests
 * @var array $historyRequests
 * @var string $myKepengurusan
 */
$this->setTitle('Persetujuan Data | Sistem Informasi');

$countIncoming = count($requests ?? []);
$countOutgoing = count($outgoingRequests ?? []);
$countHistory = count($historyRequests ?? []);

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

<style>
/* Modern Responsive Styling for Persetujuan Data */
:root {
    --req-warning: #f59e0b;
    --req-primary: #3b82f6;
    --req-success: #10b981;
    --req-danger: #ef4444;
}

.req-stat-card {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.85);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    background: #ffffff;
}
.req-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}
.req-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

/* Segmented Navigation Tabs */
.req-nav-tabs {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    gap: 8px;
    padding: 6px;
    background: #f1f5f9;
    border-radius: 14px;
    border: none;
}
.req-nav-tabs::-webkit-scrollbar {
    height: 3px;
}
.req-nav-tabs::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.req-nav-tabs .nav-link {
    white-space: nowrap;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 8px 16px;
    color: #64748b;
    border: none !important;
    background: transparent;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}
.req-nav-tabs .nav-link:hover:not(.active) {
    background: rgba(255, 255, 255, 0.6);
    color: #1e293b;
}
.req-nav-tabs .nav-link.active {
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.req-nav-tabs .nav-link#incoming-tab.active {
    color: #d97706 !important;
}
.req-nav-tabs .nav-link#outgoing-tab.active {
    color: #2563eb !important;
}
.req-nav-tabs .nav-link#history-tab.active {
    color: #475569 !important;
}

/* Upgraded Modern Mobile Cards */
.req-modern-card {
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.req-modern-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.1) !important;
}

.req-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.25);
}

.req-diff-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px;
}

.req-diff-item {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    padding: 8px 10px;
    margin-bottom: 6px;
}
.req-diff-item:last-child {
    margin-bottom: 0;
}

.req-old-val {
    background: #fee2e2;
    color: #991b1b;
    border-radius: 6px;
    padding: 2px 6px;
    font-size: 0.72rem;
    text-decoration: line-through;
    max-width: 45%;
    display: inline-block;
    vertical-align: middle;
}

.req-new-val {
    background: #dcfce7;
    color: #166534;
    border-radius: 6px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-weight: 600;
    max-width: 48%;
    display: inline-block;
    vertical-align: middle;
}

.req-card-actions {
    display: flex;
    gap: 8px;
    width: 100%;
}

/* Modal Responsiveness */
.req-modal-content {
    border-radius: 18px;
    overflow: hidden;
}

/* Responsive Overrides */
@media (max-width: 991.98px) {
    .page-header-responsive {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px;
    }
    .req-stat-card .card-body {
        padding: 0.85rem !important;
    }
    .req-stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.15rem;
    }
    .req-stat-number {
        font-size: 1.35rem !important;
    }
}
@media (max-width: 575.98px) {
    .req-nav-tabs .nav-link {
        font-size: 0.8rem;
        padding: 7px 12px;
    }
    .req-stat-number {
        font-size: 1.25rem !important;
    }
    .req-card-actions {
        flex-direction: row;
    }
    .req-card-actions .btn {
        flex: 1 1 50%;
        font-size: 0.82rem;
        padding: 8px 10px !important;
    }
}
</style>

<!-- Page Header (Clean, Non-Redundant) -->
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
</div>

<!-- Stat Metric Cards (3 Main Tabs Summary) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card req-stat-card border-0 shadow-sm h-100" style="cursor: pointer;" onclick="document.getElementById('incoming-tab').click()">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="req-stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Menunggu Persetujuan</div>
                    <div class="h4 mb-0 fw-bold text-warning req-stat-number"><?= $countIncoming ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card req-stat-card border-0 shadow-sm h-100" style="cursor: pointer;" onclick="document.getElementById('outgoing-tab').click()">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="req-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-send-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Pengajuan Saya</div>
                    <div class="h4 mb-0 fw-bold text-primary req-stat-number"><?= $countOutgoing ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card req-stat-card border-0 shadow-sm h-100" style="cursor: pointer;" onclick="document.getElementById('history-tab').click()">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="req-stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small fw-semibold text-truncate">Riwayat Keputusan</div>
                    <div class="h4 mb-0 fw-bold text-secondary req-stat-number"><?= $countHistory ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Segmented Navigation Tabs -->
<ul class="nav req-nav-tabs mb-4" id="req-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" type="button" role="tab" aria-controls="incoming" aria-selected="true">
            <i class="bi bi-inbox-fill"></i> Menunggu Persetujuan
            <span class="badge bg-warning text-dark ms-1 rounded-pill"><?= $countIncoming ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" type="button" role="tab" aria-controls="outgoing" aria-selected="false">
            <i class="bi bi-send-fill"></i> Pengajuan Saya
            <span class="badge bg-primary ms-1 rounded-pill text-white"><?= $countOutgoing ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">
            <i class="bi bi-clock-history"></i> Riwayat Keputusan
            <span class="badge bg-secondary ms-1 rounded-pill text-white"><?= $countHistory ?></span>
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
            <div class="text-center py-5 my-4 bg-white rounded-4 shadow-sm border border-light">
                <i class="bi bi-clipboard2-check text-success" style="font-size: 4.5rem; opacity: 0.3;"></i>
                <h4 class="fw-bold mt-3 text-secondary">Semua Bersih!</h4>
                <p class="text-muted small mb-0 px-3">Tidak ada usulan perubahan data santri yang memerlukan persetujuan Anda saat ini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-lg-4">
                <?php foreach ($requests as $r): 
                    $changes = json_decode($r['requested_changes'], true) ?? [];
                    $oldData = json_decode($r['old_values'] ?? '', true) ?? [];
                    $namaInitial = mb_substr(trim($r['nama'] ?? 'S'), 0, 1);
                ?>
                    <div class="col-12 col-md-6 col-lg-4" id="req-card-<?= $r['id'] ?>">
                        <div class="req-modern-card h-100">
                            <!-- Card Header -->
                            <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border font-monospace fw-bold">
                                        <i class="bi bi-person-badge me-1 text-secondary"></i><?= htmlspecialchars((string)($r['stambuk'] ?? '0')) ?>
                                    </span>
                                    <span class="badge bg-warning bg-opacity-25 text-dark border border-warning" style="font-size: 0.65rem;">
                                        USULAN BARU
                                    </span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bi bi-clock me-1"></i><?= date('d M, H:i', strtotime($r['created_at'])) ?>
                                </small>
                            </div>

                            <!-- Card Body -->
                            <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                                <!-- Profile Row -->
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="req-avatar-circle">
                                        <?= htmlspecialchars($namaInitial) ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($r['nama']) ?>">
                                            <?= htmlspecialchars($r['nama']) ?>
                                        </h5>
                                        <div class="text-muted small text-truncate mt-1">
                                            <i class="bi bi-box-arrow-in-right text-primary me-1"></i>
                                            Dari: <strong class="text-dark"><?= htmlspecialchars($r['instansi_pengaju'] ?? 'Pondok Pindahan') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Diff Changes Box -->
                                <div class="req-diff-box mb-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-secondary">
                                            <i class="bi bi-sliders text-primary me-1"></i> Perubahan Kolom
                                        </span>
                                        <?php 
                                            $visibleCount = 0;
                                            foreach ($changes as $key => $val) {
                                                if (str_starts_with($key, '_')) continue;
                                                $visibleCount++;
                                            }
                                        ?>
                                        <span class="badge bg-primary rounded-pill" style="font-size: 0.7rem;">
                                            <?= $visibleCount ?> Kolom
                                        </span>
                                    </div>

                                    <?php 
                                    $glimpse = 0;
                                    foreach ($changes as $k => $v) {
                                        if (str_starts_with($k, '_')) continue;
                                        if ($glimpse >= 2) break;
                                        $oKey = $k === 'no_sktt' ? 'nik' : $k;
                                        $oVal = array_key_exists($k, $oldData) ? $oldData[$k] : ($r[$oKey] ?? '');
                                    ?>
                                    <div class="req-diff-item shadow-none">
                                        <div class="text-muted text-capitalize mb-1" style="font-size: 0.72rem; font-weight: 600;">
                                            <?= htmlspecialchars(str_replace('_', ' ', $k)) ?>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="req-old-val text-truncate" title="Nilai Lama">
                                                <?= htmlspecialchars((string)$oVal) ?: '(Kosong)' ?>
                                            </span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="req-new-val text-truncate" title="Nilai Usulan Baru">
                                                <?= htmlspecialchars((string)$v) ?: '(Kosong)' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php $glimpse++; } ?>
                                    
                                    <button type="button" class="btn btn-sm btn-white bg-white border border-secondary-subtle text-primary rounded-pill px-3 shadow-sm fw-bold w-100 mt-2 py-1" data-bs-toggle="modal" data-bs-target="#modalIn-<?= $r['id'] ?>" style="font-size: 0.78rem;">
                                        <i class="bi bi-list-check me-1"></i> Tinjau & Pilih Perubahan <i class="bi bi-chevron-right ms-1 small"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Footer Action Buttons -->
                            <div class="p-3 bg-light border-top mt-auto">
                                <div class="req-card-actions">
                                    <button type="button" class="btn btn-outline-danger rounded-pill fw-semibold btn-sm py-2" onclick="rejectReq(<?= $r['id'] ?>)">
                                        <i class="bi bi-x-circle me-1"></i> Tolak
                                    </button>
                                    <button type="button" class="btn btn-success rounded-pill fw-semibold btn-sm shadow-sm py-2" onclick="approveReq(<?= $r['id'] ?>)">
                                        <i class="bi bi-check-circle me-1"></i> Setujui Semua
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Detail Incoming -->
                        <div class="modal fade" id="modalIn-<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow-lg req-modal-content">
                                    <div class="modal-header border-bottom-0 bg-light py-3 px-4">
                                        <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Usulan Perubahan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-white" style="max-height: calc(80vh - 120px); overflow-y: auto;">
                                        <div class="p-3 bg-light border-bottom">
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($r['nama']) ?></div>
                                            <div class="text-muted small">Stambuk: <strong class="text-dark"><?= htmlspecialchars((string)($r['stambuk'] ?? '0')) ?></strong> &bull; Dari: <strong><?= htmlspecialchars($r['instansi_pengaju'] ?? '') ?></strong></div>
                                        </div>
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
                                            <div class="px-3 py-2 small fw-bold text-secondary border-bottom d-flex justify-content-between align-items-center bg-light">
                                                <div><i class="bi <?= $icon ?> me-1 text-primary"></i> <?= $groupName ?></div>
                                                <?php if ($isGroupCheck): ?>
                                                    <div class="form-check form-switch m-0" title="Setujui/Tolak seluruh <?= $groupName ?>">
                                                        <input class="form-check-input chk-partial-<?= $r['id'] ?>" type="checkbox" value="<?= $groupKey ?>" checked style="cursor: pointer;" onchange="toggleGroup(this, '<?= $groupName ?>', <?= $r['id'] ?>)">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <ul class="list-group list-group-flush small border-bottom mb-0">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="list-group-item px-3 py-2 bg-white d-flex flex-column border-0 border-bottom">
                                                        <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                                            <span class="text-muted fw-bold text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $item['key'])) ?></span>
                                                            <?php if (!$isGroupCheck): ?>
                                                                <div class="form-check m-0">
                                                                    <input class="form-check-input chk-partial-<?= $r['id'] ?>" type="checkbox" value="<?= htmlspecialchars($item['key']) ?>" checked style="cursor: pointer; width: 1.1rem; height: 1.1rem;">
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between w-100 <?= $isGroupCheck ? "group-item-{$r['id']}-" . str_replace(' ', '', $groupName) : '' ?>" style="transition: opacity 0.2s;">
                                                            <div class="req-old-val text-truncate" style="max-width: 45%;"><?= htmlspecialchars((string)$item['oldVal']) ?: '(Kosong)' ?></div>
                                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                            <div class="req-new-val text-truncate text-end" style="max-width: 45%;"><?= htmlspecialchars((string)$item['val']) ?: '(Kosong)' ?></div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light p-3 d-flex justify-content-between flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2" onclick="rejectReq(<?= $r['id'] ?>)">Tolak Semua</button>
                                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-bold" onclick="approvePartialReq(<?= $r['id'] ?>)">
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
            <div class="text-center py-5 my-4 bg-white rounded-4 shadow-sm border border-light">
                <i class="bi bi-send-slash text-muted" style="font-size: 4.5rem; opacity: 0.3;"></i>
                <h4 class="fw-bold mt-3 text-secondary">Belum Ada Pengajuan</h4>
                <p class="text-muted small mb-0 px-3">Anda belum mengajukan usulan perubahan data ke instansi/pondok lain.</p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-lg-4">
                <?php foreach ($outgoingRequests as $o): 
                    $changesOut = json_decode($o['requested_changes'], true) ?? [];
                    $oldData = json_decode($o['old_values'] ?? '', true) ?? [];
                    $namaInitial = mb_substr(trim($o['nama'] ?? 'S'), 0, 1);
                ?>
                    <div class="col-12 col-md-6 col-lg-4" id="req-card-<?= $o['id'] ?>">
                        <div class="req-modern-card h-100">
                            <!-- Card Header -->
                            <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border font-monospace fw-bold">
                                        <i class="bi bi-person-badge me-1 text-secondary"></i><?= htmlspecialchars((string)($o['stambuk'] ?? '0')) ?>
                                    </span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bi bi-clock me-1"></i><?= date('d M, H:i', strtotime($o['created_at'])) ?>
                                </small>
                            </div>

                            <!-- Card Body -->
                            <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                                <!-- Profile Row -->
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="req-avatar-circle" style="background: linear-gradient(135deg, #0d9488, #0f766e);">
                                        <?= htmlspecialchars($namaInitial) ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($o['nama']) ?>">
                                            <?= htmlspecialchars($o['nama']) ?>
                                        </h5>
                                        <div class="text-muted small text-truncate mt-1">
                                            <i class="bi bi-arrow-right-circle text-primary me-1"></i>
                                            Tujuan: <strong class="text-dark"><?= htmlspecialchars($o['kep_tujuan'] ?? 'Pondok Tujuan') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Diff Changes Box -->
                                <div class="req-diff-box mb-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-secondary">
                                            <i class="bi bi-sliders text-primary me-1"></i> Perubahan Kolom
                                        </span>
                                        <?php 
                                            $visibleCount = 0;
                                            foreach ($changesOut as $key => $val) {
                                                if (str_starts_with($key, '_')) continue;
                                                $visibleCount++;
                                            }
                                        ?>
                                        <span class="badge bg-primary rounded-pill" style="font-size: 0.7rem;">
                                            <?= $visibleCount ?> Kolom
                                        </span>
                                    </div>

                                    <?php 
                                    $glimpse = 0;
                                    foreach ($changesOut as $k => $v) {
                                        if (str_starts_with($k, '_')) continue;
                                        if ($glimpse >= 2) break;
                                        $oKey = $k === 'no_sktt' ? 'nik' : $k;
                                        $oVal = array_key_exists($k, $oldData) ? $oldData[$k] : ($o[$oKey] ?? '');
                                    ?>
                                    <div class="req-diff-item shadow-none">
                                        <div class="text-muted text-capitalize mb-1" style="font-size: 0.72rem; font-weight: 600;">
                                            <?= htmlspecialchars(str_replace('_', ' ', $k)) ?>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="req-old-val text-truncate" title="Nilai Lama">
                                                <?= htmlspecialchars((string)$oVal) ?: '(Kosong)' ?>
                                            </span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="req-new-val text-truncate" title="Nilai Usulan Baru">
                                                <?= htmlspecialchars((string)$v) ?: '(Kosong)' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php $glimpse++; } ?>
                                    
                                    <button type="button" class="btn btn-sm btn-white bg-white border border-secondary-subtle text-primary rounded-pill px-3 shadow-sm fw-bold w-100 mt-2 py-1" data-bs-toggle="modal" data-bs-target="#modalOut-<?= $o['id'] ?>" style="font-size: 0.78rem;">
                                        <i class="bi bi-list-check me-1"></i> Lihat Selengkapnya <i class="bi bi-chevron-right ms-1 small"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="p-3 bg-light border-top mt-auto text-center">
                                <?php if ($o['status'] === 'pending'): ?>
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning fs-6 px-3 py-2 rounded-pill shadow-sm w-100"><i class="bi bi-hourglass-split me-1 text-warning"></i> Menunggu Persetujuan</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill fw-medium py-1 px-3 w-100" onclick="cancelReq(<?= $o['id'] ?>)">
                                            <i class="bi bi-trash-fill me-1"></i> Batalkan Pengajuan
                                        </button>
                                    </div>
                                <?php elseif ($o['status'] === 'approved'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success fs-6 px-3 py-2 rounded-pill shadow-sm w-100"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fs-6 px-3 py-2 rounded-pill shadow-sm w-100"><i class="bi bi-x-circle me-1"></i> Ditolak</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Modal Detail Outgoing -->
                        <div class="modal fade" id="modalOut-<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow-lg req-modal-content">
                                    <div class="modal-header border-bottom-0 bg-light py-3 px-4">
                                        <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Pengajuan Saya</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-white" style="max-height: calc(80vh - 120px); overflow-y: auto;">
                                        <div class="p-3 bg-light border-bottom">
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($o['nama']) ?></div>
                                            <div class="text-muted small">Stambuk: <strong class="text-dark"><?= htmlspecialchars((string)($o['stambuk'] ?? '0')) ?></strong> &bull; Tujuan: <strong><?= htmlspecialchars($o['kep_tujuan'] ?? '') ?></strong></div>
                                        </div>
                                        <?php 
                                        $groupedOut = groupChanges($changesOut, $oldData, $o);
                                        foreach ($groupedOut as $groupName => $items): 
                                            $icon = 'bi-record-circle';
                                            if ($groupName === 'Biodata') $icon = 'bi-person-vcard';
                                            elseif ($groupName === 'Dokumen Paspor') { $icon = 'bi-passport'; }
                                            elseif ($groupName === 'Dokumen ITAS') { $icon = 'bi-card-heading'; }
                                        ?>
                                            <div class="px-3 py-2 small fw-bold text-secondary border-bottom bg-light">
                                                <i class="bi <?= $icon ?> me-1 text-primary"></i> <?= $groupName ?>
                                            </div>
                                            <ul class="list-group list-group-flush small border-bottom mb-0">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="list-group-item px-3 py-2 bg-white d-flex flex-column border-0 border-bottom">
                                                        <span class="text-muted fw-bold text-capitalize mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $item['key'])) ?></span>
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="req-old-val text-truncate" style="max-width: 45%;"><?= htmlspecialchars((string)$item['oldVal']) ?: '(Kosong)' ?></div>
                                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                            <div class="req-new-val text-truncate text-end" style="max-width: 45%;"><?= htmlspecialchars((string)$item['val']) ?: '(Kosong)' ?></div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light p-3">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4 w-100" data-bs-dismiss="modal">Tutup</button>
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
            <div class="text-center py-5 my-4 bg-white rounded-4 shadow-sm border border-light">
                <i class="bi bi-clock-history text-muted" style="font-size: 4.5rem; opacity: 0.3;"></i>
                <h4 class="fw-bold mt-3 text-secondary">Belum Ada Riwayat</h4>
                <p class="text-muted small mb-0 px-3">Riwayat persetujuan atau penolakan usulan data santri akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-lg-4">
                <?php foreach ($historyRequests as $h): 
                    $changesHist = json_decode($h['requested_changes'], true) ?? [];
                    $oldData = json_decode($h['old_values'] ?? '', true) ?? [];
                    $namaInitial = mb_substr(trim($h['nama'] ?? 'S'), 0, 1);
                ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="req-modern-card h-100" style="opacity: 0.94;">
                            <!-- Card Header -->
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border font-monospace fw-bold">
                                        <i class="bi bi-person-badge me-1 text-secondary"></i><?= htmlspecialchars((string)($h['stambuk'] ?? '0')) ?>
                                    </span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bi bi-calendar-check me-1"></i><?= date('d M Y', strtotime($h['created_at'])) ?>
                                </small>
                            </div>

                            <!-- Card Body -->
                            <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                                <!-- Profile Row -->
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="req-avatar-circle" style="background: linear-gradient(135deg, #64748b, #475569);">
                                        <?= htmlspecialchars($namaInitial) ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($h['nama']) ?>">
                                            <?= htmlspecialchars($h['nama']) ?>
                                        </h5>
                                        <div class="text-muted small text-truncate mt-1">
                                            <i class="bi bi-building text-secondary me-1"></i>
                                            Dari: <strong class="text-dark"><?= htmlspecialchars($h['instansi_pengaju'] ?? 'Pondok Asal') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Diff Changes Box -->
                                <div class="req-diff-box mb-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-secondary">
                                            <i class="bi bi-sliders text-primary me-1"></i> Perubahan Kolom
                                        </span>
                                        <?php 
                                            $visibleCount = 0;
                                            foreach ($changesHist as $key => $val) {
                                                if (str_starts_with($key, '_')) continue;
                                                $visibleCount++;
                                            }
                                        ?>
                                        <span class="badge bg-secondary rounded-pill" style="font-size: 0.7rem;">
                                            <?= $visibleCount ?> Kolom
                                        </span>
                                    </div>

                                    <?php 
                                    $glimpse = 0;
                                    foreach ($changesHist as $k => $v) {
                                        if (str_starts_with($k, '_')) continue;
                                        if ($glimpse >= 2) break;
                                        $oKey = $k === 'no_sktt' ? 'nik' : $k;
                                        $oVal = array_key_exists($k, $oldData) ? $oldData[$k] : ($h[$oKey] ?? '');
                                    ?>
                                    <div class="req-diff-item shadow-none">
                                        <div class="text-muted text-capitalize mb-1" style="font-size: 0.72rem; font-weight: 600;">
                                            <?= htmlspecialchars(str_replace('_', ' ', $k)) ?>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="req-old-val text-truncate" title="Nilai Lama">
                                                <?= htmlspecialchars((string)$oVal) ?: '(Kosong)' ?>
                                            </span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="req-new-val text-truncate" title="Nilai Usulan Baru">
                                                <?= htmlspecialchars((string)$v) ?: '(Kosong)' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php $glimpse++; } ?>
                                    
                                    <button type="button" class="btn btn-sm btn-white bg-white border border-secondary-subtle text-primary rounded-pill px-3 shadow-sm fw-bold w-100 mt-2 py-1" data-bs-toggle="modal" data-bs-target="#modalHist-<?= $h['id'] ?>" style="font-size: 0.78rem;">
                                        <i class="bi bi-list-check me-1"></i> Lihat Selengkapnya <i class="bi bi-chevron-right ms-1 small"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="p-3 bg-light border-top mt-auto text-center">
                                <?php if ($h['status'] === 'approved'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill fs-6 w-100"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
                                <?php elseif ($h['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill fs-6 w-100"><i class="bi bi-x-circle me-1"></i> Ditolak</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Modal Detail History -->
                        <div class="modal fade" id="modalHist-<?= $h['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow-lg req-modal-content">
                                    <div class="modal-header border-bottom-0 bg-light py-3 px-4">
                                        <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Detail Riwayat Keputusan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-white" style="max-height: calc(80vh - 120px); overflow-y: auto;">
                                        <div class="p-3 bg-light border-bottom">
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($h['nama']) ?></div>
                                            <div class="text-muted small">Stambuk: <strong class="text-dark"><?= htmlspecialchars((string)($h['stambuk'] ?? '0')) ?></strong> &bull; Pengaju: <strong><?= htmlspecialchars($h['instansi_pengaju'] ?? '') ?></strong></div>
                                        </div>
                                        <?php 
                                        $groupedHist = groupChanges($changesHist, $oldData, $h);
                                        foreach ($groupedHist as $groupName => $items): 
                                            $icon = 'bi-record-circle';
                                            if ($groupName === 'Biodata') $icon = 'bi-person-vcard';
                                            elseif ($groupName === 'Dokumen Paspor') $icon = 'bi-passport';
                                            elseif ($groupName === 'Dokumen ITAS') $icon = 'bi-card-heading';
                                        ?>
                                            <div class="px-3 py-2 small fw-bold text-secondary border-bottom bg-light">
                                                <i class="bi <?= $icon ?> me-1 text-primary"></i> <?= $groupName ?>
                                            </div>
                                            <ul class="list-group list-group-flush small border-bottom mb-0">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="list-group-item px-3 py-2 bg-white d-flex flex-column border-0 border-bottom">
                                                        <span class="text-muted fw-bold text-capitalize mb-1" style="font-size: 0.75rem;"><?= htmlspecialchars(str_replace('_', ' ', $item['key'])) ?></span>
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="req-old-val text-truncate" style="max-width: 45%;"><?= htmlspecialchars((string)$item['oldVal']) ?: '(Kosong)' ?></div>
                                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                                            <div class="req-new-val text-truncate text-end" style="max-width: 45%;"><?= htmlspecialchars((string)$item['val']) ?: '(Kosong)' ?></div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light p-3">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4 w-100" data-bs-dismiss="modal">Tutup</button>
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
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const modalEl = document.getElementById(`modalIn-${id}`);
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if(modalInstance) modalInstance.hide();
            
            processReq(id, 'approve', { approved_keys: approvedKeys });
        }
    });
}

function approveReq(id) {
    const checkboxes = document.querySelectorAll(`.chk-partial-${id}`);
    const approvedKeys = Array.from(checkboxes).map(cb => cb.value);
    
    Swal.fire({
        title: 'Setujui Perubahan?',
        text: 'Semua usulan perubahan pada kartu ini akan disetujui.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
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
        confirmButtonColor: '#ef4444',
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
        confirmButtonColor: '#ef4444',
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
