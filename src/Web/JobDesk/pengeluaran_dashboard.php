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
/* Modern Compact & Responsive Styling for Pengeluaran Operasional */
.card-keuangan {
    transition: transform .2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow .2s ease;
    border-radius: 14px;
}
.card-keuangan:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,.08) !important;
}

.kategori-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 16px;
    font-size: .72rem;
    font-weight: 600;
    color: white;
    transition: all .2s ease;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.kategori-chip:hover {
    opacity: .92;
    transform: translateY(-1px);
}

.kategori-manage-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 6px;
    font-size: .82rem;
}
.kategori-manage-item .color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* Table & Sticky Action Column */
.pengeluaran-table-wrapper {
    max-height: calc(100vh - 280px);
    min-height: 260px;
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    position: relative;
    border-radius: 12px;
}
.pengeluaran-table-wrapper::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
.pengeluaran-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.pengeluaran-table-wrapper table {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 760px;
    width: 100%;
    margin-bottom: 0;
}
.pengeluaran-table-wrapper thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 10px 12px;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* ONLY Action column is sticky on the right */
.pengeluaran-sticky-action {
    position: sticky;
    right: 0;
    z-index: 8;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.pengeluaran-table-wrapper thead th.pengeluaran-sticky-action {
    position: sticky;
    top: 0;
    right: 0;
    z-index: 12;
    background-color: #f8fafc !important;
    box-shadow: -4px 0 8px -2px rgba(0,0,0,0.06);
}
.pengeluaran-table-wrapper tbody td {
    padding: 10px 12px;
    font-size: 0.82rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.pengeluaran-table-wrapper tbody tr:hover td {
    background-color: #f8fafc;
}
.pengeluaran-table-wrapper tbody tr:hover td.pengeluaran-sticky-action {
    background-color: #f8fafc !important;
}

.page-header-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.page-header-controls .btn {
    font-size: 0.78rem;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    border-radius: 20px;
}

/* Responsive Overrides */
@media (max-width: 991.98px) {
    .page-header-responsive {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px;
    }
    .page-header-controls {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }
    .page-header-controls .btn {
        width: 100%;
        padding: 6px 10px;
        font-size: 0.75rem;
    }
    .card-keuangan {
        border-radius: 12px;
    }
    .pengeluaran-table-wrapper {
        max-height: 52vh;
    }
}
@media (max-width: 420px) {
    .page-header-controls {
        grid-template-columns: repeat(2, 1fr);
        gap: 5px;
    }
    .page-header-controls .btn {
        font-size: 0.72rem;
        padding: 5px 6px;
    }
}

@media print {
    body * { visibility: hidden !important; }
    #printArea, #printArea * { visibility: visible !important; }
    #printArea { position: fixed; left: 0; top: 0; width: 100%; z-index: 99999; background: #fff; padding: 20px; }
}
</style>

<div class="container-fluid px-0">

    <!-- === HEADER === -->
    <div class="d-flex align-items-center justify-content-between mb-3 page-header-responsive">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 38px; height: 38px; background: rgba(13, 110, 253, 0.1);">
                <i class="bi bi-receipt-cutoff text-primary fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -.3px;">Pengeluaran Operasional</h5>
                <p class="text-muted small mb-0" style="font-size: .75rem;">Dana operasional birokrasi & catatan pengeluaran</p>
            </div>
        </div>
        <div class="page-header-controls">
            <button class="btn btn-sm btn-primary fw-semibold shadow-sm" onclick="showFormPengeluaran()">
                <i class="bi bi-plus-lg me-1"></i> Catat Pengeluaran
            </button>
            <button class="btn btn-sm btn-outline-info fw-semibold" onclick="showKategoriManager()">
                <i class="bi bi-tags me-1"></i> Kategori
            </button>
            <button class="btn btn-sm btn-outline-secondary fw-semibold" onclick="exportPengeluaran()">
                <i class="bi bi-download me-1"></i> Export CSV
            </button>
            <a href="<?= API_URL ?>/job-desk/keuangan" class="btn btn-sm btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Keuangan
            </a>
        </div>
    </div>

    <!-- === FINANCIAL OVERVIEW CARDS (COMPACT) === -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:white;">
                <div class="card-body p-2.5 p-md-3 position-relative d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="opacity-80 fw-semibold text-uppercase" style="letter-spacing:.05em; font-size:.65rem;">Pemasukan Operasional</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-0.5" style="font-size: .65rem;"><i class="bi bi-wallet2"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.2rem;"><?= $formatRupiah($totalPemasukan) ?></h4>
                    </div>
                    <div class="small opacity-80 mt-1 text-truncate" style="font-size:.68rem;"><i class="bi bi-info-circle me-1"></i>Selisih Job Desk (lunas)</div>
                    <i class="bi bi-wallet2 position-absolute opacity-10" style="font-size:3.5rem;right:8px;bottom:-6px;pointer-events:none;"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color:white;">
                <div class="card-body p-2.5 p-md-3 position-relative d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="opacity-80 fw-semibold text-uppercase" style="letter-spacing:.05em; font-size:.65rem;">Total Pengeluaran</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-0.5" style="font-size: .65rem;"><i class="bi bi-receipt"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.2rem;"><?= $formatRupiah($totalPengeluaran) ?></h4>
                    </div>
                    <div class="small opacity-80 mt-1 text-truncate" style="font-size:.68rem;"><i class="bi bi-check2-circle me-1"></i><?= count($pengeluaranList) ?> transaksi tercatat</div>
                    <i class="bi bi-receipt position-absolute opacity-10" style="font-size:3.5rem;right:8px;bottom:-6px;pointer-events:none;"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden card-keuangan h-100" style="background: linear-gradient(135deg, <?= $saldoOperasional >= 0 ? '#3b82f6 0%, #1d4ed8 100%' : '#ef4444 0%, #b91c1c 100%' ?>); color:white;">
                <div class="card-body p-2.5 p-md-3 position-relative d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="opacity-80 fw-semibold text-uppercase" style="letter-spacing:.05em; font-size:.65rem;">Saldo Operasional</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-0.5" style="font-size: .65rem;"><i class="bi bi-safe"></i></span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-truncate" style="font-size:1.2rem;"><?= $formatRupiah($saldoOperasional) ?></h4>
                    </div>
                    <div class="small opacity-80 mt-1 text-truncate" style="font-size:.68rem;"><i class="bi bi-calculator me-1"></i>Pemasukan &minus; Pengeluaran</div>
                    <i class="bi bi-safe position-absolute opacity-10" style="font-size:3.5rem;right:8px;bottom:-6px;pointer-events:none;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- === BREAKDOWN PER KATEGORI (COMPACT) === -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-2.5 p-md-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-dark small d-flex align-items-center gap-1.5" style="font-size:.8rem;">
                    <i class="bi bi-pie-chart-fill text-primary"></i> Breakdown Kategori
                </span>
                <span class="badge bg-light text-muted border" style="font-size:.68rem;"><?= count($pengeluaranPerKategori) ?> Kategori</span>
            </div>
            <div class="d-flex flex-wrap gap-1.5">
                <?php if (empty($pengeluaranPerKategori)): ?>
                    <span class="text-muted small" style="font-size:.75rem;"><em>Belum ada transaksi pengeluaran tercatat</em></span>
                <?php else: ?>
                    <?php foreach ($pengeluaranPerKategori as $pk): ?>
                        <div class="kategori-chip" style="background: <?= $kategoriColors[$pk['kategori']] ?? '#6c757d' ?>;">
                            <i class="bi <?= $kategoriIcons[$pk['kategori']] ?? 'bi-tag' ?>"></i>
                            <?= htmlspecialchars($pk['kategori']) ?>: <?= $formatRupiah((float)$pk['total']) ?> <span class="badge bg-white bg-opacity-25 rounded-pill ms-0.5"><?= $pk['jumlah'] ?>x</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- === CHART TREND BULANAN (COMPACT) === -->
    <?php if (!empty($pengeluaranBulanan)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small d-flex align-items-center gap-1.5" style="font-size:.8rem;">
                <i class="bi bi-bar-chart-line-fill text-primary"></i> Tren Pengeluaran Bulanan
            </span>
            <span class="text-muted" style="font-size:.7rem;">12 bulan terakhir</span>
        </div>
        <div class="card-body px-2 px-md-3 pb-3 pt-1">
            <div style="position: relative; height: 150px; width: 100%;">
                <canvas id="pengeluaranChart"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- === TABLE PENGELUARAN === -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-3 py-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark small d-flex align-items-center gap-1.5" style="font-size:.82rem;">
                <i class="bi bi-table text-primary"></i> Riwayat Pengeluaran
            </span>
            <span class="badge bg-light text-muted border" style="font-size:.7rem;">Total: <?= count($pengeluaranList) ?> Transaksi</span>
        </div>
        <div class="card-body p-2.5 p-md-3">
            <div class="pengeluaran-table-wrapper border rounded-3">
                <table class="table table-hover align-middle mb-0" id="pengeluaranTable">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width:40px;">No</th>
                            <th style="min-width: 110px;">Tanggal</th>
                            <th style="min-width: 220px;">Keterangan</th>
                            <th style="min-width: 130px;">Kategori</th>
                            <th style="min-width: 130px;">Nominal</th>
                            <th style="min-width: 70px;">Nota</th>
                            <th style="min-width: 120px;">Dicatat Oleh</th>
                            <th class="pe-3 text-center pengeluaran-sticky-action" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pengeluaranList)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                                Belum ada pencatatan pengeluaran operasional.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($pengeluaranList as $idx => $pg): ?>
                            <tr>
                                <td class="ps-3 text-muted small fw-semibold"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size:.82rem;"><?= date('d M Y', strtotime($pg['tanggal'])) ?></div>
                                    <div class="text-muted" style="font-size:.68rem;"><?= date('H:i', strtotime($pg['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark" style="font-size:.85rem; max-width:320px; white-space:pre-wrap;"><?= htmlspecialchars($pg['keterangan']) ?></div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 text-white" style="font-size:.7rem; background:<?= $kategoriColors[$pg['kategori']] ?? '#6c757d' ?>;">
                                        <i class="bi <?= $kategoriIcons[$pg['kategori']] ?? 'bi-tag' ?> me-1"></i><?= htmlspecialchars($pg['kategori']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger font-monospace" style="font-size:.88rem;">-<?= $formatRupiah((float)$pg['nominal']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($pg['foto_nota'])): ?>
                                        <?php 
                                            $notaUrl = str_starts_with($pg['foto_nota'], '/uploads/') ? $pg['foto_nota'] : API_URL . '/api/job-desk/pengeluaran/view-nota/' . $pg['id'];
                                            $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $pg['foto_nota']); 
                                        ?>
                                        <?php if ($isImg): ?>
                                            <img src="<?= htmlspecialchars($notaUrl) ?>" alt="Nota" 
                                                 style="width:42px;height:42px;object-fit:cover;border-radius:8px;border:2px solid #e9ecef;cursor:pointer;transition:transform .2s;"
                                                 onmouseover="this.style.transform='scale(1.2)'" 
                                                 onmouseout="this.style.transform='scale(1)'"
                                                 onclick="showNotaPreview('<?= htmlspecialchars($notaUrl) ?>')">
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($notaUrl) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" style="font-size:0.72rem;">
                                                <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>PDF
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><div class="text-muted small"><?= htmlspecialchars($pg['created_by'] ?? '-') ?></div></td>
                                <td class="pe-3 text-center pengeluaran-sticky-action">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-pill py-1 px-2 shadow-sm" title="Edit Pengeluaran" 
                                                onclick="editPengeluaran(<?= $pg['id'] ?>, '<?= htmlspecialchars(addslashes($pg['keterangan']), ENT_QUOTES) ?>', <?= (float)$pg['nominal'] ?>, '<?= htmlspecialchars($pg['kategori']) ?>', '<?= $pg['tanggal'] ?>', '<?= htmlspecialchars($pg['foto_nota'] ?? '') ?>')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill py-1 px-2 shadow-sm" title="Hapus Pengeluaran" onclick="hapusPengeluaran(<?= $pg['id'] ?>)">
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
            <div class="modal-header border-0 bg-dark text-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-image me-2 text-warning"></i>Preview Nota Transaksi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center bg-dark">
                <img id="notaPreviewImg" src="" alt="Nota" style="max-width:100%; max-height:75vh; object-fit:contain; border-radius: 8px;">
            </div>
            <div class="modal-footer border-0 bg-dark py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <a id="notaPreviewDownload" href="" target="_blank" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium shadow-sm">
                    <i class="bi bi-download me-1"></i>Unduh Berkas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL FORM PENGELUARAN ===== -->
<div class="modal fade" id="formPengeluaranModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold mb-0" id="formPengeluaranTitle"><i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPengeluaran" enctype="multipart/form-data">
                <div class="modal-body p-3 p-md-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                    <input type="hidden" id="pg_edit_id" value="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">TANGGAL TRANSAKSI <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3" id="pg_tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">KATEGORI <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="pg_kategori" required>
                            <?php foreach ($kategoris as $kat): ?>
                            <option value="<?= htmlspecialchars($kat['nama']) ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">NOMINAL (RP) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 font-monospace" id="pg_nominal" required placeholder="Contoh: 150000"
                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatNominalPreview(this);">
                        <div id="pg_nominal_preview" class="small text-primary mt-1 fw-bold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">KETERANGAN <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" id="pg_keterangan" rows="3" required placeholder="Contoh: Beli kertas HVS 2 rim, konsumsi dinas..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">FOTO NOTA / STRUK (OPSIONAL)</label>
                        <input type="file" class="form-control rounded-3" id="pg_foto_nota" accept="image/*,.pdf">
                        <div class="small text-muted mt-1" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1"></i>JPG, PNG, WebP, PDF (maksimal 5MB)</div>
                        <div id="pg_foto_preview" class="mt-2" style="display:none;">
                            <img id="pg_foto_preview_img" src="" style="max-height:120px; border-radius:8px; border:2px solid #e9ecef;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" id="btnSimpanPengeluaran">
                        <i class="bi bi-save me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL KELOLA KATEGORI ===== -->
<div class="modal fade" id="kategoriModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-info text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-tags me-2"></i>Kelola Kategori Pengeluaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                <div id="kategoriList">
                    <?php foreach ($kategoris as $k): ?>
                    <div class="kategori-manage-item" data-id="<?= $k['id'] ?>">
                        <div class="color-dot" style="background: <?= htmlspecialchars($k['warna']) ?>;"></div>
                        <i class="bi <?= htmlspecialchars($k['icon']) ?> text-muted"></i>
                        <span class="fw-semibold flex-grow-1 text-dark small"><?= htmlspecialchars($k['nama']) ?></span>
                        <button class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill border-0" title="Hapus Kategori" onclick="hapusKategori(<?= $k['id'] ?>, '<?= htmlspecialchars(addslashes($k['nama'])) ?>')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr class="my-3 opacity-25">
                <div class="d-flex gap-2 align-items-end flex-wrap">
                    <div class="flex-grow-1" style="min-width: 160px;">
                        <label class="form-label small fw-bold text-muted mb-1">Tambah Kategori Baru</label>
                        <input type="text" class="form-control form-control-sm rounded-3" id="newKategoriNama" placeholder="Nama kategori...">
                    </div>
                    <div style="width: 50px;">
                        <label class="form-label small fw-bold text-muted mb-1">Warna</label>
                        <input type="color" class="form-control form-control-sm form-control-color p-0 border-0 rounded-3" id="newKategoriWarna" value="#0d6efd" style="height:31px;">
                    </div>
                    <button class="btn btn-sm btn-info text-white rounded-pill px-3 py-1 fw-semibold" onclick="tambahKategori()">
                        <i class="bi bi-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light p-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4 w-100" data-bs-dismiss="modal">Tutup</button>
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
                backgroundColor: 'rgba(111, 66, 193, 0.18)',
                borderColor: '#6f42c1',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(111, 66, 193, 0.35)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
