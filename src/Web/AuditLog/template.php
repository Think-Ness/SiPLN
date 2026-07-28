<?php
declare(strict_types=1);

use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $logs
 */
$this->setTitle('Log Aktivitas | Sistem Informasi');
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #17a2b8, #117a8b);">
            <i class="bi bi-activity text-white fs-4"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Sistem Rekam Jejak (Audit Log)</h4>
            <div class="text-muted small fw-medium mt-1">
                Pantau seluruh rekam jejak manipulasi data secara real-time
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
                <thead class="bg-light text-secondary" style="font-size: 0.85rem;">
                    <tr>
                        <th class="py-3 ps-4" style="border-bottom: 2px solid #e9ecef;">WAKTU (WIB)</th>
                        <th class="py-3" style="border-bottom: 2px solid #e9ecef;">PELAKU</th>
                        <th class="py-3" style="border-bottom: 2px solid #e9ecef;">MODUL & TARGET</th>
                        <th class="py-3" style="border-bottom: 2px solid #e9ecef;">TINDAKAN</th>
                        <th class="py-3 text-center pe-4" style="border-bottom: 2px solid #e9ecef;">RINCIAN</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada riwayat aktivitas yang tercatat.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr style="border-bottom: 1px solid #f1f3f5;">
                                <td class="ps-4 text-muted small fw-medium">
                                    <i class="bi bi-clock me-1"></i> <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 me-1">
                                        <?= htmlspecialchars($log['module']) ?>
                                    </span>
                                    <span class="fw-bold text-dark">ID: <?= htmlspecialchars($log['entity_id'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $action = strtoupper($log['action']);
                                        $color = 'primary';
                                        if ($action === 'CREATE') $color = 'success';
                                        if ($action === 'UPDATE') $color = 'warning text-dark';
                                        if ($action === 'DELETE') $color = 'danger';
                                        if ($action === 'APPROVE') $color = 'success';
                                        if ($action === 'REJECT') $color = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $color ?> rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-lightning-charge-fill me-1"></i><?= $action ?></span>
                                </td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-light text-primary fw-bold rounded-pill shadow-sm" onclick='showDetails(<?= json_encode($log['old_data'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($log['new_data'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="bi bi-eye-fill"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 p-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Rincian Manipulasi Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-white">
                
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs px-4 pt-3 border-bottom-0" id="auditTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark border-0 border-bottom border-primary border-3 bg-transparent" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-pane" type="button" role="tab">Ringkasan Perubahan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium text-secondary border-0 bg-transparent" id="raw-tab" data-bs-toggle="tab" data-bs-target="#raw-pane" type="button" role="tab">Data Mentah (Code)</button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="auditTabsContent" style="min-height: 300px; max-height: 60vh; overflow-y: auto;">
                    
                    <!-- Pane 1: Ringkasan Manusiawi -->
                    <div class="tab-pane fade show active" id="summary-pane" role="tabpanel" tabindex="0">
                        <div id="textOnlyView" class="d-none">
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-0">
                                <i class="bi bi-chat-square-text-fill fs-4 me-3 text-info"></i>
                                <div>
                                    <div class="fw-bold mb-1">Catatan Sistem:</div>
                                    <div id="textMessageContent" class="mb-0"></div>
                                </div>
                            </div>
                        </div>

                        <div id="tableView" class="d-none table-responsive border rounded-3">
                            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%;">
                                <thead class="table-light text-secondary" style="font-size: 0.85rem;">
                                    <tr>
                                        <th class="ps-4 w-25">KOLOM DATA</th>
                                        <th class="w-35 text-danger"><i class="bi bi-dash-circle me-1"></i>DATA LAMA</th>
                                        <th class="w-35 text-success"><i class="bi bi-plus-circle me-1"></i>DATA BARU</th>
                                    </tr>
                                </thead>
                                <tbody id="diffTableBody" style="font-size: 0.9rem;">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pane 2: Raw Code JSON -->
                    <div class="tab-pane fade" id="raw-pane" role="tabpanel" tabindex="0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="fw-bold text-danger mb-2 small"><i class="bi bi-dash-circle me-1"></i>OLD DATA JSON</div>
                                <pre class="bg-light p-3 rounded-3 small text-secondary border" style="height: 300px; overflow-y: auto;" id="oldDataView"></pre>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-success mb-2 small"><i class="bi bi-plus-circle me-1"></i>NEW DATA JSON</div>
                                <pre class="bg-primary bg-opacity-10 p-3 rounded-3 small text-dark fw-medium border border-primary border-opacity-25" style="height: 300px; overflow-y: auto;" id="newDataView"></pre>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function safeParseJSON(str) {
        if (!str) return null;
        if (typeof str !== 'string') return str;
        try {
            return JSON.parse(str);
        } catch (e) {
            return str;
        }
    }

    function showDetails(oldDataRaw, newDataRaw) {
        // Coba parse JSON, kadang tersimpan double stringified
        let oldObj = safeParseJSON(oldDataRaw);
        if (typeof oldObj === 'string') oldObj = safeParseJSON(oldObj);
        
        let newObj = safeParseJSON(newDataRaw);
        if (typeof newObj === 'string') newObj = safeParseJSON(newObj);

        // RAW PANE
        document.getElementById('oldDataView').textContent = typeof oldObj === 'object' && oldObj ? JSON.stringify(oldObj, null, 2) : (oldObj || 'Null / Kosong');
        document.getElementById('newDataView').textContent = typeof newObj === 'object' && newObj ? JSON.stringify(newObj, null, 2) : (newObj || 'Null / Kosong');

        // SUMMARY PANE
        const textOnlyView = document.getElementById('textOnlyView');
        const tableView = document.getElementById('tableView');
        const diffTableBody = document.getElementById('diffTableBody');
        const textMessageContent = document.getElementById('textMessageContent');

        // Reset
        diffTableBody.innerHTML = '';
        textOnlyView.classList.add('d-none');
        tableView.classList.add('d-none');

        // Jika keduanya bukan object (hanya pesan teks biasa)
        if ((typeof oldObj !== 'object' || oldObj === null) && (typeof newObj !== 'object' || newObj === null)) {
            textOnlyView.classList.remove('d-none');
            let msg = '';
            if (newDataRaw) msg += `<strong>Aksi:</strong> ${newDataRaw} <br>`;
            if (oldDataRaw && oldDataRaw !== newDataRaw) msg += `<strong>Keterangan Lama:</strong> ${oldDataRaw}`;
            textMessageContent.innerHTML = msg || 'Tidak ada rincian data.';
        } else {
            // Ini adalah Object JSON, kita bandingkan kunci-kuncinya
            tableView.classList.remove('d-none');
            let o = typeof oldObj === 'object' && oldObj ? oldObj : {};
            let n = typeof newObj === 'object' && newObj ? newObj : {};
            
            // Kumpulkan semua keys
            let allKeys = new Set([...Object.keys(o), ...Object.keys(n)]);
            
            if (allKeys.size === 0) {
                diffTableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Objek data kosong.</td></tr>';
            } else {
                allKeys.forEach(key => {
                    let oldVal = o[key];
                    let newVal = n[key];

                    // Abaikan jika tidak ada perubahan (bandingkan sebagai string untuk menghindari perbedaan tipe data int vs string)
                    if (String(oldVal) === String(newVal)) return;

                    let oldStr = oldVal === undefined || oldVal === null || oldVal === '' ? '<span class="text-muted fst-italic">Kosong</span>' : htmlEscape(typeof oldVal === 'object' ? JSON.stringify(oldVal) : String(oldVal));
                    let newStr = newVal === undefined || newVal === null || newVal === '' ? '<span class="text-muted fst-italic">Kosong/Dihapus</span>' : htmlEscape(typeof newVal === 'object' ? JSON.stringify(newVal) : String(newVal));

                    let tr = document.createElement('tr');
                    
                    // Highlight row if it's a completely new addition or deletion
                    let bgOld = 'bg-danger bg-opacity-10';
                    let bgNew = 'bg-success bg-opacity-10';
                    
                    if (oldVal === undefined || oldVal === null || oldVal === '') {
                        tr.classList.add('table-success'); // Ditambah
                        bgOld = '';
                    } else if (newVal === undefined || newVal === null || newVal === '') {
                        tr.classList.add('table-danger'); // Dihapus
                        bgNew = '';
                    } else {
                        // Diubah
                        tr.style.backgroundColor = '#fffaf0'; 
                    }

                    tr.innerHTML = `
                        <td class="ps-4 fw-bold text-dark text-capitalize text-wrap text-break">${key.replace(/_/g, ' ')}</td>
                        <td class="text-danger ${bgOld} text-wrap text-break py-2">${oldStr}</td>
                        <td class="text-success ${bgNew} text-wrap text-break py-2 fw-bold">${newStr}</td>
                    `;
                    diffTableBody.appendChild(tr);
                });

                if (diffTableBody.children.length === 0) {
                     diffTableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Tidak ada perbedaan nilai pada data yang dikirim.</td></tr>';
                }
            }
        }

        // Aktifkan tab pertama
        document.getElementById('summary-tab').click();
        new bootstrap.Modal(document.getElementById('auditDetailModal')).show();
    }

    function htmlEscape(str) {
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
</script>
