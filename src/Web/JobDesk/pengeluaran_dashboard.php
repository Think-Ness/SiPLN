<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var float $totalPemasukan
 * @var float $totalPengeluaran
 * @var float $saldoOperasional
 * @var array $pengeluaranList
 * @var array $pengeluaranPerKategori
 * @var array $pengeluaranBulanan
 * @var string $bulananLabels
 * @var string $bulananData
 * @var array $kategoris
 * @var array $kategoriColors
 * @var array $kategoriIcons
 * @var \App\Shared\ApplicationParams $applicationParams
 */

$this->setTitle('Pengeluaran Operasional | ' . $applicationParams->name);

$formatRupiah = function($num) {
    return 'Rp ' . number_format((float)$num, 0, ',', '.');
};
?>

<style>
.card-keuangan { transition: transform .2s, box-shadow .2s; }
.card-keuangan:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.12) !important; }
.summary-stat-card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 14px 16px; transition: all .2s; position: relative; overflow: hidden; }
.summary-stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 2px 0 0 2px; }
.summary-stat-card.accent-blue::before { background: #0d6efd; }
.summary-stat-card.accent-green::before { background: #198754; }
.summary-stat-card.accent-purple::before { background: #6f42c1; }
.summary-stat-card:hover { background: #f0f4ff; border-color: #c9d4f7; }
.summary-stat-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9fa8b3; margin-bottom: 4px; }
.summary-stat-value { font-size: .95rem; font-weight: 700; color: #1a2035; }
.summary-stat-sub { font-size: .65rem; color: #9fa8b3; margin-top: 3px; }

.kategori-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: .72rem; font-weight: 600; color: white; transition: all .2s; }
.kategori-chip:hover { opacity: .85; transform: scale(1.02); }
.kategori-manage-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 6px; }
.kategori-manage-item .color-dot { width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0; }

@media print {
    body * { visibility: hidden !important; }
    #printArea, #printArea * { visibility: visible !important; }
    #printArea { position: fixed; left: 0; top: 0; width: 100%; z-index: 99999; background: #fff; padding: 20px; }
}
</style>

<div class="container-fluid px-0">

    <!-- === HEADER === -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Pengeluaran Operasional</h4>
            <p class="text-muted small mb-0">Pencatatan penggunaan dana operasional birokrasi</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-medium shadow-sm" onclick="showFormPengeluaran()">
                <i class="bi bi-plus-circle me-1"></i>Catat Pengeluaran
            </button>
            <button class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="showKategoriManager()">
                <i class="bi bi-tags me-1"></i>Kelola Kategori
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="exportPengeluaran()">
                <i class="bi bi-download me-1"></i>Export CSV
            </button>
            <a href="<?= API_URL ?>/job-desk/keuangan" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium">
                <i class="bi bi-arrow-left me-1"></i>Ke Operasional Birokrasi
            </a>
        </div>
    </div>

    <!-- === FINANCIAL OVERVIEW CARDS === -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-keuangan" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); color:white;">
                <div class="card-body p-3 position-relative">
                    <div class="mb-1 opacity-75 fw-semibold small text-uppercase" style="letter-spacing:.06em; font-size:.65rem;">Pemasukan Operasional</div>
                    <h4 class="fw-bolder mb-1" style="font-size:1.2rem;"><?= $formatRupiah($totalPemasukan) ?></h4>
                    <div class="small opacity-75" style="font-size:.65rem;"><i class="bi bi-info-circle me-1"></i>Dari selisih tarif Job Desk (lunas)</div>
                    <i class="bi bi-wallet2 position-absolute opacity-10" style="font-size:4.5rem;right:-10px;bottom:-15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-keuangan" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color:white;">
                <div class="card-body p-3 position-relative">
                    <div class="mb-1 opacity-75 fw-semibold small text-uppercase" style="letter-spacing:.06em; font-size:.65rem;">Total Pengeluaran</div>
                    <h4 class="fw-bolder mb-1" style="font-size:1.2rem;"><?= $formatRupiah($totalPengeluaran) ?></h4>
                    <div class="small opacity-75" style="font-size:.65rem;"><i class="bi bi-info-circle me-1"></i><?= count($pengeluaranList) ?> transaksi tercatat</div>
                    <i class="bi bi-receipt position-absolute opacity-10" style="font-size:4.5rem;right:-10px;bottom:-15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden card-keuangan" style="background: linear-gradient(135deg, <?= $saldoOperasional >= 0 ? '#0d6efd, #0a58ca' : '#dc3545, #b02a37' ?>); color:white;">
                <div class="card-body p-3 position-relative">
                    <div class="mb-1 opacity-75 fw-semibold small text-uppercase" style="letter-spacing:.06em; font-size:.65rem;">Saldo Operasional</div>
                    <h4 class="fw-bolder mb-1" style="font-size:1.2rem;"><?= $formatRupiah($saldoOperasional) ?></h4>
                    <div class="small opacity-75" style="font-size:.65rem;"><i class="bi bi-info-circle me-1"></i>Pemasukan âˆ’ Pengeluaran</div>
                    <i class="bi bi-safe position-absolute opacity-10" style="font-size:4.5rem;right:-10px;bottom:-15px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- === BREAKDOWN PER KATEGORI === -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Breakdown per Kategori</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php if (empty($pengeluaranPerKategori)): ?>
                    <span class="text-muted small"><em>Belum ada pengeluaran tercatat</em></span>
                <?php else: ?>
                    <?php foreach ($pengeluaranPerKategori as $pk): ?>
                        <div class="kategori-chip" style="background: <?= $kategoriColors[$pk['kategori']] ?? '#6c757d' ?>;">
                            <i class="bi <?= $kategoriIcons[$pk['kategori']] ?? 'bi-tag' ?>"></i>
                            <?= htmlspecialchars($pk['kategori']) ?>: <?= $formatRupiah((float)$pk['total']) ?> (<?= $pk['jumlah'] ?>x)
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- === CHART TREND BULANAN === -->
    <?php if (!empty($pengeluaranBulanan)): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Tren Pengeluaran Bulanan</h6>
            <div class="text-muted small mt-1">12 bulan terakhir</div>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            <canvas id="pengeluaranChart" height="60"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- === TABLE PENGELUARAN === -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header bg-white border-bottom px-4 py-3">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-primary me-2"></i>Riwayat Pengeluaran</h6>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pengeluaranTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 border-0" style="width:40px;">No</th>
                            <th class="border-0">Tanggal</th>
                            <th class="border-0">Keterangan</th>
                            <th class="border-0">Kategori</th>
                            <th class="border-0">Nominal</th>
                            <th class="border-0">Nota</th>
                            <th class="border-0">Dicatat Oleh</th>
                            <th class="pe-3 border-0 text-end" style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pengeluaranList)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                                Belum ada pencatatan pengeluaran.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($pengeluaranList as $idx => $pg): ?>
                            <tr>
                                <td class="ps-3 text-muted small"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-semibold" style="font-size:.8rem;"><?= date('d M Y', strtotime($pg['tanggal'])) ?></div>
                                    <div class="text-muted" style="font-size:.65rem;"><?= date('H:i', strtotime($pg['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark" style="font-size:.82rem; max-width:300px; white-space:pre-wrap;"><?= htmlspecialchars($pg['keterangan']) ?></div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 text-white" style="font-size:.68rem; background:<?= $kategoriColors[$pg['kategori']] ?? '#6c757d' ?>;">
                                        <i class="bi <?= $kategoriIcons[$pg['kategori']] ?? 'bi-tag' ?> me-1"></i><?= htmlspecialchars($pg['kategori']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger" style="font-size:.85rem;">-<?= $formatRupiah((float)$pg['nominal']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($pg['foto_nota'])): ?>
                                        <?php 
                                            $notaUrl = str_starts_with($pg['foto_nota'], '/uploads/') ? $pg['foto_nota'] : API_URL . '/api/job-desk/pengeluaran/view-nota/' . $pg['id'];
                                            $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $pg['foto_nota']); 
                                        ?>
                                        <?php if ($isImg): ?>
                                            <img src="<?= htmlspecialchars($notaUrl) ?>" alt="Nota" 
                                                 style="width:45px;height:45px;object-fit:cover;border-radius:8px;border:2px solid #e9ecef;cursor:pointer;transition:transform .2s;"
                                                 onmouseover="this.style.transform='scale(1.5)'" 
                                                 onmouseout="this.style.transform='scale(1)'"
                                                 onclick="showNotaPreview('<?= htmlspecialchars($notaUrl) ?>')">
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0">
                                                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="bi bi-dash-circle"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><div class="text-muted small"><?= htmlspecialchars($pg['created_by'] ?? '-') ?></div></td>
                                <td class="pe-3 text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button class="btn btn-sm btn-outline-warning py-0 px-2" title="Edit" 
                                                onclick="editPengeluaran(<?= $pg['id'] ?>, '<?= htmlspecialchars(addslashes($pg['keterangan']), ENT_QUOTES) ?>', <?= (float)$pg['nominal'] ?>, '<?= htmlspecialchars($pg['kategori']) ?>', '<?= $pg['tanggal'] ?>', '<?= htmlspecialchars($pg['foto_nota'] ?? '') ?>')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus" onclick="hapusPengeluaran(<?= $pg['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL NOTA PREVIEW ===== -->
<div class="modal fade" id="notaPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark">
            <div class="modal-header border-0 bg-dark text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-image me-2"></i>Preview Nota</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <img id="notaPreviewImg" src="" alt="Nota" style="max-width:100%; max-height:75vh; object-fit:contain;">
            </div>
            <div class="modal-footer border-0 bg-dark py-2">
                <a id="notaPreviewDownload" href="" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-download me-1"></i>Unduh
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL FORM PENGELUARAN ===== -->
<div class="modal fade" id="formPengeluaranModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="formPengeluaranTitle"><i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPengeluaran" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" id="pg_edit_id" value="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="pg_tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="pg_kategori" required>
                            <?php foreach ($kategoris as $kat): ?>
                            <option value="<?= htmlspecialchars($kat['nama']) ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pg_nominal" required placeholder="Contoh: 150000"
                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatNominalPreview(this);">
                        <div id="pg_nominal_preview" class="small text-primary mt-1 fw-semibold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="pg_keterangan" rows="3" required placeholder="Contoh: Beli kertas HVS 2 rim, tinta printer..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Foto Nota / Struk (Opsional)</label>
                        <input type="file" class="form-control" id="pg_foto_nota" accept="image/*,.pdf">
                        <div class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i>JPG, PNG, WebP, PDF (maks 5MB)</div>
                        <div id="pg_foto_preview" class="mt-2" style="display:none;">
                            <img id="pg_foto_preview_img" src="" style="max-height:120px; border-radius:8px; border:2px solid #e9ecef;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" id="btnSimpanPengeluaran">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL KELOLA KATEGORI ===== -->
<div class="modal fade" id="kategoriModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-info text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-tags me-2"></i>Kelola Kategori Pengeluaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="kategoriList">
                    <?php foreach ($kategoris as $k): ?>
                    <div class="kategori-manage-item" data-id="<?= $k['id'] ?>">
                        <div class="color-dot" style="background: <?= htmlspecialchars($k['warna']) ?>;"></div>
                        <i class="bi <?= htmlspecialchars($k['icon']) ?> text-muted"></i>
                        <span class="fw-medium flex-grow-1"><?= htmlspecialchars($k['nama']) ?></span>
                        <button class="btn btn-sm btn-outline-danger py-0 px-1 border-0" title="Hapus" onclick="hapusKategori(<?= $k['id'] ?>, '<?= htmlspecialchars(addslashes($k['nama'])) ?>')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="d-flex gap-2 align-items-end">
                    <div class="flex-grow-1">
                        <label class="form-label small fw-bold text-muted mb-1">Tambah Kategori Baru</label>
                        <input type="text" class="form-control form-control-sm" id="newKategoriNama" placeholder="Nama kategori...">
                    </div>
                    <div style="width: 50px;">
                        <label class="form-label small fw-bold text-muted mb-1">Warna</label>
                        <input type="color" class="form-control form-control-sm form-control-color p-0 border-0" id="newKategoriWarna" value="#0d6efd" style="height:31px;">
                    </div>
                    <button class="btn btn-sm btn-info text-white rounded-pill px-3" onclick="tambahKategori()">
                        <i class="bi bi-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/dataTables.bootstrap5.min.css">
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>

<?php if (!empty($pengeluaranBulanan)): ?>
<script src="<?= ASSET_URL ?>/assets/offline/js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('pengeluaranChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= $bulananLabels ?>,
            datasets: [{
                label: 'Pengeluaran',
                data: <?= $bulananData ?>,
                backgroundColor: 'rgba(111, 66, 193, 0.15)',
                borderColor: '#6f42c1',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(111, 66, 193, 0.3)',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1000).toLocaleString('id-ID') + 'K' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
<?php endif; ?>

<script>
$(document).ready(function() {
    if ($('#pengeluaranTable tbody tr').length > 1 || ($('#pengeluaranTable tbody tr').length === 1 && $('#pengeluaranTable tbody tr td').length > 1)) {
        $('#pengeluaranTable').DataTable({
            pageLength: 20,
            lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "Semua"]],
            language: { search: "Cari:", lengthMenu: "Tampil _MENU_ data", info: "_START_-_END_ dari _TOTAL_", paginate: { previous: "Prev", next: "Next" } },
            columnDefs: [{ orderable: false, targets: [5, 7] }],
            order: [[1, 'desc']]
        });
    }
});

function formatNominalPreview(el) {
    const val = parseInt(el.value) || 0;
    document.getElementById('pg_nominal_preview').textContent = val > 0 ? 'Rp ' + val.toLocaleString('id-ID') : '';
}

function showNotaPreview(src) {
    document.getElementById('notaPreviewImg').src = src;
    document.getElementById('notaPreviewDownload').href = src;
    new bootstrap.Modal(document.getElementById('notaPreviewModal')).show();
}

function showFormPengeluaran() {
    document.getElementById('pg_edit_id').value = '';
    document.getElementById('formPengeluaranTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran Baru';
    document.getElementById('formPengeluaran').reset();
    document.getElementById('pg_tanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('pg_nominal_preview').textContent = '';
    document.getElementById('pg_foto_preview').style.display = 'none';
    new bootstrap.Modal(document.getElementById('formPengeluaranModal')).show();
}

function editPengeluaran(id, keterangan, nominal, kategori, tanggal, fotoNota) {
    document.getElementById('pg_edit_id').value = id;
    document.getElementById('formPengeluaranTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Pengeluaran #' + id;
    document.getElementById('pg_tanggal').value = tanggal;
    document.getElementById('pg_kategori').value = kategori;
    document.getElementById('pg_nominal').value = nominal;
    document.getElementById('pg_keterangan').value = keterangan;
    formatNominalPreview(document.getElementById('pg_nominal'));
    if (fotoNota && /\.(jpg|jpeg|png|webp)$/i.test(fotoNota)) {
        document.getElementById('pg_foto_preview').style.display = 'block';
        document.getElementById('pg_foto_preview_img').src = fotoNota.startsWith('/uploads/') ? fotoNota : '<?= API_URL ?>/api/job-desk/pengeluaran/view-nota/' + id;
    } else {
        document.getElementById('pg_foto_preview').style.display = 'none';
    }
    new bootstrap.Modal(document.getElementById('formPengeluaranModal')).show();
}

document.getElementById('pg_foto_nota').addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('pg_foto_preview').style.display = 'block'; document.getElementById('pg_foto_preview_img').src = e.target.result; };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('pg_foto_preview').style.display = 'none';
    }
});

document.getElementById('formPengeluaran').addEventListener('submit', function(e) {
    e.preventDefault();
    const editId = document.getElementById('pg_edit_id').value;
    const isEdit = editId !== '';
    const url = isEdit ? '<?= API_URL ?>/api/job-desk/pengeluaran/' + editId + '/update' : '<?= API_URL ?>/api/job-desk/pengeluaran/create';
    const fd = new FormData();
    fd.append('tanggal', document.getElementById('pg_tanggal').value);
    fd.append('kategori', document.getElementById('pg_kategori').value);
    fd.append('nominal', document.getElementById('pg_nominal').value);
    fd.append('keterangan', document.getElementById('pg_keterangan').value);
    const fotoFile = document.getElementById('pg_foto_nota').files[0];
    if (fotoFile) {
        if (fotoFile.size > 5 * 1024 * 1024) { Swal.fire('File Terlalu Besar', 'Maksimal 5MB.', 'warning'); return; }
        fd.append('foto_nota', fotoFile);
    }
    const btn = document.getElementById('btnSimpanPengeluaran');
    btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menyimpan...';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch(url, { method: 'POST', headers: csrfToken ? { 'X-CSRF-Token': csrfToken } : {}, body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) { Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false }).then(() => location.reload()); }
        else { Swal.fire('Gagal', res.message || 'Error', 'error'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan'; }
    }).catch(() => { Swal.fire('Error', 'Koneksi gagal.', 'error'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan'; });
});

function hapusPengeluaran(id) {
    Swal.fire({ title: 'Hapus Pengeluaran?', text: 'Data tidak dapat dikembalikan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', confirmButtonColor: '#dc3545', customClass: { popup: 'shadow-lg rounded-4' } })
    .then(result => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/job-desk/pengeluaran/' + id + '/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken || '', 'Content-Type': 'application/json' } })
            .then(r => r.json()).then(res => {
                if (res.success) { Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1500, showConfirmButton: false }).then(() => location.reload()); }
                else Swal.fire('Gagal', res.message, 'error');
            }).catch(() => Swal.fire('Error', 'Koneksi gagal.', 'error'));
        }
    });
}

function exportPengeluaran() {
    const rows = [['No', 'Tanggal', 'Kategori', 'Keterangan', 'Nominal', 'Dicatat Oleh']];
    document.querySelectorAll('#pengeluaranTable tbody tr').forEach((tr, idx) => {
        const cells = tr.querySelectorAll('td');
        if (cells.length >= 7) rows.push([idx+1, cells[1]?.textContent.trim().split('\n')[0]||'', cells[3]?.textContent.trim()||'', cells[2]?.textContent.trim()||'', cells[4]?.textContent.trim()||'', cells[6]?.textContent.trim()||'']);
    });
    const csv = rows.map(r => r.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
    const blob = new Blob(['\uFEFF'+csv], { type:'text/csv;charset=utf-8;' });
    const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = 'pengeluaran_' + new Date().toISOString().slice(0,10) + '.csv'; link.click();
}

// === KATEGORI MANAGEMENT ===
function showKategoriManager() {
    new bootstrap.Modal(document.getElementById('kategoriModal')).show();
}

function tambahKategori() {
    const nama = document.getElementById('newKategoriNama').value.trim();
    const warna = document.getElementById('newKategoriWarna').value;
    if (!nama) { Swal.fire('Oops', 'Nama kategori wajib diisi.', 'warning'); return; }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/job-desk/pengeluaran/kategori/create', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken || '', 'Content-Type': 'application/json' },
        body: JSON.stringify({ nama, warna })
    }).then(r => r.json()).then(res => {
        if (res.success) { Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Kategori ditambahkan.', timer: 1500, showConfirmButton: false }).then(() => location.reload()); }
        else Swal.fire('Gagal', res.message, 'error');
    }).catch(() => Swal.fire('Error', 'Koneksi gagal.', 'error'));
}

function hapusKategori(id, nama) {
    Swal.fire({ title: 'Hapus Kategori?', html: 'Kategori <strong>' + nama + '</strong> akan dihapus.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', confirmButtonColor: '#dc3545' })
    .then(result => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/job-desk/pengeluaran/kategori/' + id + '/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken || '', 'Content-Type': 'application/json' } })
            .then(r => r.json()).then(res => {
                if (res.success) { Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1500, showConfirmButton: false }).then(() => location.reload()); }
                else Swal.fire('Gagal', res.message, 'error');
            }).catch(() => Swal.fire('Error', 'Koneksi gagal.', 'error'));
        }
    });
}
</script>
