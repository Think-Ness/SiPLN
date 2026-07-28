<?php
declare(strict_types=1);
use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $pengajuanList
 * @var bool $isAdmin
 * @var string $myKepengurusan
 * @var array $hijriMonths
 * @var string $currentHijriMonth
 * @var int $currentHijriYear
 */
$this->setTitle('Manajemen Anggaran Operasional | Sistem Informasi');
?>

<style>
    /* Styling khusus untuk mencetak (Print) */
    @media print {
        body * {
            visibility: hidden;
        }
        #printArea, #printArea * {
            visibility: visible;
        }
        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .modal, .swal2-container {
            display: none !important;
        }
    }
</style>
<div id="printArea" style="display:none;"></div>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white;">
            <i class="bi bi-wallet2 fs-4"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark">Anggaran Operasional</h4>
            <div class="text-muted small fw-medium mt-1">
                <?= $isAdmin ? 'Mode Super Admin (Semua Pondok)' : 'Pondok: <strong class="text-primary">' . htmlspecialchars($myPondok) . '</strong>' ?>
            </div>
        </div>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-medium" onclick="openPengajuanModal()">
        <i class="bi bi-plus-circle me-2"></i>Buat Pengajuan Baru
    </button>
</div>

<div class="row g-4 mb-4">
    <?php if (empty($pengajuanList)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <h5>Belum ada data pengajuan anggaran</h5>
            <p>Silakan buat pengajuan baru untuk memulai pencatatan anggaran.</p>
        </div>
    <?php else: ?>
        <?php foreach ($pengajuanList as $p): 
            $statusColors = [
                'draft' => 'secondary',
                'diajukan' => 'warning',
                'disetujui' => 'primary',
                'dilaporkan' => 'info',
                'ditolak' => 'danger',
                'selesai' => 'success'
            ];
            $color = $statusColors[$p['status']] ?? 'primary';
            $pct = $p['total_disetujui'] > 0 ? min(100, ($p['total_digunakan'] / $p['total_disetujui']) * 100) : 0;
            $isApproved = in_array($p['status'], ['disetujui', 'dilaporkan', 'selesai']);
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="transition: transform 0.2s;">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-start">
                    <div>
                        <?php if($isAdmin): ?>
                            <span class="badge bg-dark bg-opacity-10 text-dark mb-2"><?= htmlspecialchars($p['instansi']) ?></span>
                        <?php endif; ?>
                        <h5 class="fw-bold mb-1"><i class="bi bi-calendar2-month text-primary me-2"></i><?= htmlspecialchars($p['bulan_hijriah']) ?></h5>
                        <div class="text-muted small">ID: #<?= str_pad((string)$p['id'], 4, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle px-3 py-2 rounded-pill text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total Ajuan:</span>
                        <span class="fw-semibold">Rp <?= number_format((float)$p['total_ajuan'], 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted small">Total Disetujui:</span>
                        <span class="fw-bold text-success fs-5">Rp <?= number_format((float)$p['total_disetujui'], 0, ',', '.') ?></span>
                    </div>
                    
                    <?php if ($isApproved): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-2 align-items-center">
                            <span class="fw-bold bg-warning bg-opacity-25 text-warning-emphasis px-2 py-1 rounded shadow-sm">Terpakai: Rp <?= number_format((float)$p['total_digunakan'], 0, ',', '.') ?></span>
                            <span class="text-primary fw-bold"><?= round($pct) ?>%</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height: 10px;">
                            <div class="progress-bar <?= $pct > 90 ? 'bg-danger' : ($pct > 75 ? 'bg-warning' : 'bg-primary') ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $pct ?>%"></div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between small">
                            <span>
                                <?php if($p['durasi_cair'] !== '-'): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" title="Lama Pencairan"><i class="bi bi-stopwatch me-1"></i> Cair: <?= $p['durasi_cair'] ?></span>
                                <?php endif; ?>
                                <?php if($p['durasi_lapor'] !== '-'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" title="Lama Lapor Nota"><i class="bi bi-clock-history me-1"></i> Lapor: <?= $p['durasi_lapor'] ?></span>
                                <?php endif; ?>
                            </span>
                            <span><span class="text-muted">Sisa: </span><strong class="<?= $p['sisa_anggaran'] < 0 ? 'text-danger' : 'text-success' ?>">Rp <?= number_format((float)$p['sisa_anggaran'], 0, ',', '.') ?></strong></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4 pt-3 border-top position-relative">
                        <div class="d-flex justify-content-between position-relative px-2">
                            <div class="progress position-absolute top-50 start-0 end-0 translate-middle-y" style="height: 3px; z-index: 1;">
                                <?php 
                                    $w = 0; 
                                    if($p['status'] === 'diajukan') $w = 0; 
                                    if($p['status'] === 'disetujui') $w = 33.33; 
                                    if($p['status'] === 'dilaporkan') $w = 66.66;
                                    if($p['status'] === 'selesai') $w = 100;
                                ?>
                                <div class="progress-bar bg-success" style="width: <?= $w ?>%"></div>
                            </div>
                            <div class="position-relative text-center" style="z-index:2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?= $p['status']!=='draft' ? 'success' : 'secondary' ?> text-white mx-auto mb-1 border border-2 border-white shadow-sm" style="width: 28px; height: 28px; font-size: 0.8rem;"><i class="bi bi-check2"></i></div>
                                <div class="fw-bold text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Diajukan</div>
                            </div>
                            <div class="position-relative text-center" style="z-index:2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?= in_array($p['status'], ['disetujui', 'dilaporkan', 'selesai']) ? 'success' : 'secondary' ?> text-white mx-auto mb-1 border border-2 border-white shadow-sm" style="width: 28px; height: 28px; font-size: 0.8rem;"><i class="bi bi-check2"></i></div>
                                <div class="fw-bold text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Di-ACC</div>
                            </div>
                            <div class="position-relative text-center" style="z-index:2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?= in_array($p['status'], ['dilaporkan', 'selesai']) ? 'success' : 'secondary' ?> text-white mx-auto mb-1 border border-2 border-white shadow-sm" style="width: 28px; height: 28px; font-size: 0.8rem;"><i class="bi bi-file-earmark-check"></i></div>
                                <div class="fw-bold text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Dilaporkan</div>
                            </div>
                            <div class="position-relative text-center" style="z-index:2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?= $p['status']==='selesai' ? 'success' : 'secondary' ?> text-white mx-auto mb-1 border border-2 border-white shadow-sm" style="width: 28px; height: 28px; font-size: 0.8rem;"><i class="bi bi-flag-fill"></i></div>
                                <div class="fw-bold text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Selesai</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 px-4 py-3 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill flex-grow-1 fw-medium" onclick='viewDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                        <i class="bi bi-eye me-1"></i> Preview
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill fw-medium px-3" onclick='copyPengajuan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)' title="Duplikat / Salin Pengajuan">
                        <i class="bi bi-files"></i> Copy
                    </button>
                    <?php if ($p['status'] === 'draft' || $p['status'] === 'diajukan'): ?>
                        <button class="btn btn-sm btn-outline-danger rounded-pill fw-medium px-3" onclick="deletePengajuan(<?= $p['id'] ?>)" title="Hapus Pengajuan">
                            <i class="bi bi-trash"></i>
                        </button>
                    <?php endif; ?>
                    <?php if ($p['status'] === 'diajukan'): ?>
                        <button class="btn btn-sm btn-info rounded-pill text-white flex-grow-1 fw-bold shadow-sm" onclick='editPengajuan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-warning rounded-pill flex-grow-1 fw-bold shadow-sm" onclick='reviewPengajuan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                            <i class="bi bi-check2-circle me-1"></i> Input ACC
                        </button>
                    <?php endif; ?>
                    <?php if (in_array($p['status'], ['disetujui', 'dilaporkan'])): ?>
                        <button class="btn btn-sm btn-success rounded-pill flex-grow-1 fw-bold shadow-sm" onclick="laporNota(<?= $p['id'] ?>)">
                            <i class="bi bi-receipt me-1"></i> Lapor Nota
                        </button>
                        <?php if ($p['status'] === 'disetujui'): ?>
                        <button class="btn btn-sm btn-info text-white rounded-pill flex-grow-1 fw-bold shadow-sm" onclick="tandaiDilaporkan(<?= $p['id'] ?>)">
                            <i class="bi bi-send-fill me-1"></i> Dilaporkan
                        </button>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'dilaporkan'): ?>
                        <button class="btn btn-sm btn-primary rounded-pill flex-grow-1 fw-bold shadow-sm" onclick="tandaiSelesai(<?= $p['id'] ?>)">
                            <i class="bi bi-flag-fill me-1"></i> Selesai
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Pengajuan Baru -->
<div class="modal fade" id="modalPengajuan" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalPengajuanTitle"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Buat Pengajuan Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4 bg-light">
                <input type="hidden" id="form_pengajuan_id">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Bulan Hijriah</label>
                        <select class="form-select bg-white border border-secondary-subtle" id="form_bulan">
                            <?php foreach($hijriMonths as $m): ?>
                                <option value="<?= $m ?>" <?= $m === $currentHijriMonth ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Tahun Hijriah</label>
                        <div class="input-group">
                            <input type="number" class="form-control bg-white border border-secondary-subtle" id="form_tahun" value="<?= $currentHijriYear ?>">
                            <span class="input-group-text bg-light border border-secondary-subtle">H</span>
                        </div>
                    </div>
                </div>
                
                <?php if ($isAdmin): ?>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Pilih Pondok</label>
                    <select class="form-select bg-white border border-secondary-subtle" id="form_instansi">
                        <option value="">-- Pilih Pondok --</option>
                        <?php foreach($pondokList as $ins): ?>
                            <option value="<?= htmlspecialchars($ins['nama']) ?>"><?= htmlspecialchars($ins['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span>Rincian Kebutuhan Anggaran</span>
                </h6>
                <div id="tbodyItems" class="d-flex flex-column gap-3 mb-3">
                    <!-- JS injected items -->
                </div>
                
                <div class="d-flex justify-content-center mb-4">
                    <button class="btn btn-outline-primary rounded-pill fw-medium shadow-sm px-4" onclick="addItemRow()">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Baris Kebutuhan Baru
                    </button>
                </div>
                
                <div class="card border-0 bg-primary bg-opacity-10 rounded-3">
                    <div class="card-body px-4 py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">TOTAL PENGAJUAN :</span>
                        <span class="fw-bold text-primary fs-4" id="lblTotalAjuan">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 py-3 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSubmitPengajuan" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold" onclick="submitPengajuan()">Kirim Pengajuan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Review / Approval -->
<div class="modal fade" id="modalReview" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning border-0 px-4 py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-check2-circle me-2"></i>Input Nominal ACC Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4 bg-light">
                <input type="hidden" id="r_pengajuan_id">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="text-muted small">Instansi</div>
                        <h5 class="fw-bold mb-0" id="r_instansi"></h5>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Bulan</div>
                        <h5 class="fw-bold mb-0" id="r_bulan"></h5>
                    </div>
                </div>
                
                <div class="card border-0 bg-white mb-4 shadow-sm">
                    <div class="card-body px-3 py-3 d-flex justify-content-between align-items-center">
                        <div class="fw-bold text-dark"><i class="bi bi-sliders text-warning me-2"></i>Mode Input Nominal:</div>
                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                            <input class="form-check-input fs-5 m-0" style="cursor:pointer" type="checkbox" id="modeLumpsum" onchange="toggleLumpsum()">
                            <label class="form-check-label fw-medium text-dark" style="cursor:pointer" for="modeLumpsum">Tulis Total Keseluruhan Saja</label>
                        </div>
                    </div>
                </div>

                <div id="lumpsumContainer" style="display:none;" class="mb-4">
                    <div class="bg-success bg-opacity-10 p-4 rounded-4 border border-success border-opacity-25 text-center">
                        <label class="text-success small fw-bold mb-2">Total Nominal ACC Keseluruhan (Rp)</label>
                        <input type="number" id="r_totalLumpsum" class="form-control form-control-lg text-center fw-bold text-success fs-2 border-success shadow-sm" placeholder="0">
                        <div class="small text-muted mt-2">Harga per-item rincian akan diabaikan dan Anda hanya menetapkan nilai akhir yang disetujui.</div>
                    </div>
                </div>

                <div class="table-responsive bg-white rounded-3 shadow-sm mb-4 border-0" id="r_tableItems">
                    <table class="table table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item Kebutuhan</th>
                                <th>Ajuan Instansi</th>
                                <th width="200" class="bg-warning bg-opacity-10 text-dark">ACC Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="r_tbodyItems"></tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="text-end fw-bold">TOTAL :</td>
                                <td class="fw-bold text-secondary" id="r_totalAjuan"></td>
                                <td class="fw-bold text-success fs-5" id="r_totalAcc">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold">Catatan Tambahan (Opsional)</label>
                    <textarea id="r_catatan" class="form-control rounded-3 shadow-sm border-0" rows="2" placeholder="Alasan disetujui sebagian, dll..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 py-3 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 shadow-sm fw-bold" onclick="approvePengajuan('ditolak')">Tolak Pengajuan</button>
                <button type="button" class="btn btn-success rounded-pill px-5 shadow-sm fw-bold" onclick="approvePengajuan('disetujui')">Simpan & Setujui</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lapor Nota -->
<div class="modal fade" id="modalNota" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white border-0 px-4 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Laporan Nota Pembelanjaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4 bg-light">
                <input type="hidden" id="n_pengajuan_id">
                <input type="hidden" id="n_nota_id">
                <div class="row g-4">
                    <div class="col-md-7">
                        <div style="max-height: calc(100vh - 220px); overflow-y: auto; padding-right: 12px; padding-left: 5px;">
                            <div class="d-flex justify-content-between align-items-center mb-3 position-sticky top-0 bg-light py-2" style="z-index: 10;">
                                <h6 class="fw-bold mb-0">Daftar Nota Masuk</h6>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="printSemuaNota()"><i class="bi bi-printer me-1"></i>Cetak Laporan</button>
                            </div>
                            <div id="notaListContainer" class="d-flex flex-column gap-3">
                                <div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat nota...</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div style="max-height: calc(100vh - 220px); overflow-y: auto; padding-right: 5px;">
                            <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-plus-square me-2"></i>Tambah Nota Baru</h6>
                            </div>
                            <div class="card-body px-4 py-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="text-muted small fw-bold">Nomor/Label Nota</label>
                                        <input type="text" id="n_nomor" class="form-control form-control-sm bg-white border border-secondary-subtle" placeholder="Misal: Nota 1" onkeydown="notaInputKeydown(event, 'n_tanggal', null)">
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small fw-bold">Tanggal Belanja</label>
                                        <input type="date" id="n_tanggal" class="form-control form-control-sm bg-white border border-secondary-subtle" value="<?= date('Y-m-d') ?>" onkeydown="notaInputKeydown(event, 'n_bagian_nota', 'n_nomor')">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold">Bagian / Divisi (Opsional)</label>
                                    <input type="text" id="n_bagian_nota" class="form-control form-control-sm bg-white border border-secondary-subtle" placeholder="Pilih dari daftar atau ketik baru" list="listBagianNota" onkeydown="notaInputKeydown(event, 'nb_nama', 'n_tanggal')">
                                    <datalist id="listBagianNota"></datalist>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold">Foto Bukti (Opsional)</label>
                                    <input type="file" id="n_file" class="form-control form-control-sm bg-white border border-secondary-subtle" accept="image/*">
                                </div>
                                
                                <label class="text-muted small fw-bold">Rincian Barang</label>
                                <div class="bg-light p-3 rounded-4 border border-secondary-subtle mb-3 shadow-sm">
                                    <div class="row g-2 mb-2">
                                        <div class="col-12"><input type="text" id="nb_nama" class="form-control form-control-sm bg-white border border-secondary-subtle py-2" placeholder="Nama Barang" onkeydown="notaInputKeydown(event, 'nb_qty', 'n_bagian_nota')"></div>
                                        <div class="col-3"><input type="number" id="nb_qty" class="form-control form-control-sm bg-white border border-secondary-subtle py-2" placeholder="Qty" value="1" onkeydown="notaInputKeydown(event, 'nb_satuan', 'nb_nama')"></div>
                                        <div class="col-4"><input type="text" id="nb_satuan" class="form-control form-control-sm bg-white border border-secondary-subtle py-2" placeholder="Satuan" onkeydown="notaInputKeydown(event, 'nb_harga', 'nb_qty')"></div>
                                        <div class="col-5"><input type="number" id="nb_harga" class="form-control form-control-sm bg-white border border-secondary-subtle py-2" placeholder="Harga Satuan (Rp)" onkeydown="notaInputKeydown(event, 'nb_bagian', 'nb_satuan')"></div>
                                        <div class="col-9"><input type="text" id="nb_bagian" class="form-control form-control-sm bg-white border border-secondary-subtle py-2" placeholder="Bagian / Seksi (Opsional)" onkeydown="notaInputKeydown(event, 'ADD_BARANG', 'nb_harga')"></div>
                                        <div class="col-3"><button class="btn btn-sm btn-primary w-100 h-100 fw-bold shadow-sm" onclick="addBarangNota()"><i class="bi bi-plus-lg"></i> Tambah</button></div>
                                    </div>
                                    <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                                        <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                            <tbody id="tblBarangNota"></tbody>
                                        </table>
                                    </div>
                                    <div class="text-end fw-bold pt-2 border-top mt-2 text-dark">
                                        Total: <span id="nb_total" class="text-success">Rp 0</span>
                                    </div>
                                </div>
                                <button id="btnSimpanNota" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm" onclick="simpanNota()"><i class="bi bi-cloud-upload me-1"></i>Simpan Nota (Ctrl+Enter)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const allPengajuans = <?= json_encode($pengajuanList ?? []) ?>;
const instansiMap = <?= json_encode($instansiMap ?? []) ?>;

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// ==========================================
// PENGAJUAN BARU
// ==========================================
let itemsAjuan = [];

function openPengajuanModal() {
    document.getElementById('form_pengajuan_id').value = '';
    document.getElementById('modalPengajuanTitle').innerHTML = '<i class="bi bi-file-earmark-plus text-primary me-2"></i>Buat Pengajuan Anggaran';
    document.getElementById('btnSubmitPengajuan').innerText = 'Kirim Pengajuan';
    itemsAjuan = [];
    renderItemsAjuan();
    new bootstrap.Modal(document.getElementById('modalPengajuan')).show();
    addItemRow();
}

function editPengajuan(p) {
    document.getElementById('form_pengajuan_id').value = p.id;
    document.getElementById('modalPengajuanTitle').innerHTML = '<i class="bi bi-pencil-square text-info me-2"></i>Edit Pengajuan Anggaran';
    document.getElementById('btnSubmitPengajuan').innerText = 'Simpan Perubahan';
    
    let parts = p.bulan_hijriah.split(' ');
    if (parts.length >= 3) {
        document.getElementById('form_bulan').value = parts[0] + (parts[1] && parts[1] !== 'Awal' && parts[1] !== 'Akhir' ? '' : ' ' + parts[1]);
        let thn = parts[parts.length - 2];
        if(!isNaN(thn)) document.getElementById('form_tahun').value = thn;
    } else if (parts.length >= 2) {
        document.getElementById('form_bulan').value = parts[0];
        document.getElementById('form_tahun').value = parts[1];
    }
    
    let instansiEl = document.getElementById('form_instansi');
    if (instansiEl) instansiEl.value = p.instansi;

    itemsAjuan = p.items.map(it => ({
        nama_item: it.nama_item,
        qty: it.qty,
        satuan: it.satuan,
        bagian: it.bagian,
        harga_satuan: it.harga_satuan,
        nominal_ajuan: it.nominal_ajuan
    }));
    
    if (itemsAjuan.length === 0) addItemRow();
    else renderItemsAjuan();
    
    new bootstrap.Modal(document.getElementById('modalPengajuan')).show();
}

function copyPengajuan(p) {
    document.getElementById('form_pengajuan_id').value = '';
    document.getElementById('modalPengajuanTitle').innerHTML = '<i class="bi bi-files text-primary me-2"></i>Duplikat Pengajuan Anggaran';
    document.getElementById('btnSubmitPengajuan').innerText = 'Kirim Pengajuan Baru';
    
    let instansiEl = document.getElementById('form_instansi');
    if (instansiEl) instansiEl.value = p.instansi;

    itemsAjuan = p.items.map(it => ({
        nama_item: it.nama_item,
        qty: it.qty,
        satuan: it.satuan,
        bagian: it.bagian,
        harga_satuan: it.harga_satuan,
        nominal_ajuan: it.nominal_ajuan
    }));
    
    if (itemsAjuan.length === 0) addItemRow();
    else renderItemsAjuan();
    
    new bootstrap.Modal(document.getElementById('modalPengajuan')).show();
}

function addItemRow() {
    itemsAjuan.push({ nama_item: '', qty: 1, satuan: '', bagian: '', harga_satuan: 0, nominal_ajuan: 0 });
    renderItemsAjuan();
}

function renderItemsAjuan() {
    const tbody = document.getElementById('tbodyItems');
    tbody.innerHTML = '';
    let total = 0;
    itemsAjuan.forEach((item, index) => {
        let nominal = Number(item.qty || 0) * Number(item.harga_satuan || 0);
        item.nominal_ajuan = nominal;
        total += nominal;
        tbody.innerHTML += `
            <div class="card border border-secondary-subtle shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-3 bg-white">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="small text-muted mb-1 fw-bold">Nama Kebutuhan / Barang</label>
                            <input type="text" id="i_nama_item_${index}" class="form-control bg-white border border-secondary-subtle" placeholder="Misal: Kertas HVS" value="${item.nama_item}" oninput="updateItem(${index}, 'nama_item', this.value)" onkeydown="handleEnter(event, ${index}, 'nama_item')">
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="small text-muted mb-1 fw-bold">Qty</label>
                            <input type="number" id="i_qty_${index}" class="form-control bg-white border border-secondary-subtle text-center" placeholder="1" value="${item.qty}" oninput="updateItem(${index}, 'qty', this.value)" onkeydown="handleEnter(event, ${index}, 'qty')">
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="small text-muted mb-1 fw-bold">Satuan</label>
                            <input type="text" id="i_satuan_${index}" class="form-control bg-white border border-secondary-subtle" placeholder="Rim/Pcs" value="${item.satuan}" oninput="updateItem(${index}, 'satuan', this.value)" onkeydown="handleEnter(event, ${index}, 'satuan')">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-1 fw-bold">Bagian (Opsional)</label>
                            <input type="text" id="i_bagian_${index}" class="form-control bg-white border border-secondary-subtle" placeholder="Divisi/Seksi" value="${item.bagian}" oninput="updateItem(${index}, 'bagian', this.value)" onkeydown="handleEnter(event, ${index}, 'bagian')">
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="small text-muted mb-1 fw-bold">Harga Satuan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border border-secondary-subtle text-muted">Rp</span>
                                <input type="number" id="i_harga_satuan_${index}" class="form-control bg-white border border-secondary-subtle" placeholder="150000" value="${item.harga_satuan || ''}" oninput="updateItem(${index}, 'harga_satuan', this.value)" onkeydown="handleEnter(event, ${index}, 'harga_satuan')">
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="small text-muted d-block">Subtotal Baris:</span>
                                <span class="fw-bold text-dark fs-5" id="lblSubtotal_${index}">Rp ${formatRupiah(nominal)}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-danger py-1 px-3 rounded-pill" onclick="removeItem(${index})" title="Hapus Baris Ini"><i class="bi bi-trash me-1"></i>Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    document.getElementById('lblTotalAjuan').innerText = 'Rp ' + formatRupiah(total);
}

function updateItem(index, key, value) {
    itemsAjuan[index][key] = value;
    if (['qty', 'harga_satuan'].includes(key)) {
        let item = itemsAjuan[index];
        let nominal = Number(item.qty || 0) * Number(item.harga_satuan || 0);
        item.nominal_ajuan = nominal;
        document.getElementById('lblSubtotal_' + index).innerText = 'Rp ' + formatRupiah(nominal);
        let total = itemsAjuan.reduce((sum, it) => sum + (Number(it.qty || 0) * Number(it.harga_satuan || 0)), 0);
        document.getElementById('lblTotalAjuan').innerText = 'Rp ' + formatRupiah(total);
    }
}

function handleEnter(e, index, currentField) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const flow = ['nama_item', 'qty', 'satuan', 'bagian', 'harga_satuan'];
        let currentIndex = flow.indexOf(currentField);
        if (currentIndex < flow.length - 1) {
            let nextField = flow[currentIndex + 1];
            let nextEl = document.getElementById(`i_${nextField}_${index}`);
            if (nextEl) nextEl.focus();
        } else {
            addItemRow();
            setTimeout(() => {
                let nextRowEl = document.getElementById(`i_nama_item_${index + 1}`);
                if (nextRowEl) {
                    nextRowEl.scrollIntoView({behavior: "smooth", block: "center"});
                    nextRowEl.focus();
                }
            }, 100);
        }
    }
}

function removeItem(index) {
    itemsAjuan.splice(index, 1);
    renderItemsAjuan();
}

function submitPengajuan() {
    const btn = document.getElementById('btnSubmitPengajuan');
    Swal.fire({
        title: 'Kirim Pengajuan?',
        text: "Pastikan nominal sudah benar. Pengajuan akan dikirim ke Super Admin.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            let bulanValue = document.getElementById('form_bulan').value;
            let tahunValue = document.getElementById('form_tahun').value;
            let bulan = bulanValue + ' ' + tahunValue + ' H';
            let validItems = itemsAjuan.filter(i => i.nama_item.trim() !== '');
            
            if (!bulanValue || !tahunValue) {
                Swal.fire('Peringatan', 'Silakan lengkapi bulan dan tahun hijriah', 'warning');
                return;
            }

            let instansiEl = document.getElementById('form_instansi');
            let dataPayload = { bulan_hijriah: bulan, items: validItems };
            if (instansiEl) {
                if (!instansiEl.value) {
                    Swal.fire('Peringatan', 'Silakan pilih Instansi terlebih dahulu.', 'warning');
                    return;
                }
                dataPayload.instansi = instansiEl.value;
            }

            if (validItems.length === 0) {
                Swal.fire('Peringatan', 'Minimal harus ada 1 item kebutuhan.', 'warning');
                return;
            }

            let id = document.getElementById('form_pengajuan_id').value;
            let url = id ? `<?= API_URL ?>/api/anggaran/${id}/update` : '<?= API_URL ?>/api/anggaran/store';

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            btn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify(dataPayload)
            }).then(r => r.json()).then(res => {
                if(res.success) {
                    Swal.fire('Berhasil', res.message, 'success').then(()=>location.reload());
                } else {
                    btn.innerHTML = 'Kirim Pengajuan';
                    btn.disabled = false;
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

function deletePengajuan(id) {
    Swal.fire({
        title: 'Hapus Pengajuan?',
        text: "Data pengajuan anggaran ini akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/${id}/delete`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf }
            }).then(r => r.json()).then(res => {
                if(res.success) {
                    Swal.fire('Terhapus', res.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

// ==========================================
// REVIEW / APPROVE (ADMIN)
// ==========================================
let rItems = [];

function reviewPengajuan(p) {
    document.getElementById('r_pengajuan_id').value = p.id;
    document.getElementById('r_instansi').innerText = p.instansi;
    document.getElementById('r_bulan').innerText = p.bulan_hijriah;
    document.getElementById('r_catatan').value = p.catatan_admin || '';
    
    document.getElementById('r_totalAjuan').innerText = 'Rp ' + formatRupiah(p.total_ajuan);
    
    document.getElementById('modeLumpsum').checked = false;
    document.getElementById('r_totalLumpsum').value = p.total_disetujui > 0 ? p.total_disetujui : p.total_ajuan;
    toggleLumpsum();

    rItems = p.items;
    renderReviewItems();
    new bootstrap.Modal(document.getElementById('modalReview')).show();
}

function toggleLumpsum() {
    let isLump = document.getElementById('modeLumpsum').checked;
    document.getElementById('r_tableItems').style.display = isLump ? 'none' : 'block';
    document.getElementById('lumpsumContainer').style.display = isLump ? 'block' : 'none';
}

function renderReviewItems() {
    const tbody = document.getElementById('r_tbodyItems');
    tbody.innerHTML = '';
    let totalAcc = 0;
    rItems.forEach((item, idx) => {
        if(!item.nominal_disetujui || Number(item.nominal_disetujui) === 0) {
            item.nominal_disetujui = item.nominal_ajuan;
        }
        totalAcc += Number(item.nominal_disetujui);
        
        tbody.innerHTML += `
            <tr>
                <td><div class="fw-medium">${item.nama_item}</div>
                    <div class="small text-muted">${item.qty} ${item.satuan} @ Rp ${formatRupiah(item.harga_satuan)} ${item.bagian ? `(Bag. ${item.bagian})` : ''}</div>
                </td>
                <td><span class="text-secondary">Rp ${formatRupiah(item.nominal_ajuan)}</span></td>
                <td class="bg-warning bg-opacity-10 p-2">
                    <input type="number" class="form-control form-control-sm text-end border-warning shadow-sm" value="${item.nominal_disetujui}" oninput="updateAcc(${idx}, this.value)">
                </td>
            </tr>
        `;
    });
    document.getElementById('r_totalAcc').innerText = 'Rp ' + formatRupiah(totalAcc);
}

function updateAcc(idx, val) {
    rItems[idx].nominal_disetujui = val;
    let totalAcc = rItems.reduce((sum, it) => sum + Number(it.nominal_disetujui || 0), 0);
    document.getElementById('r_totalAcc').innerText = 'Rp ' + formatRupiah(totalAcc);
}

function approvePengajuan(status) {
    const id = document.getElementById('r_pengajuan_id').value;
    const catatan = document.getElementById('r_catatan').value;
    let isLumpsum = document.getElementById('modeLumpsum').checked;
    
    Swal.fire({
        title: status === 'disetujui' ? 'Simpan Nominal ACC?' : 'Batalkan Pengajuan?',
        icon: status === 'disetujui' ? 'success' : 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            
            let payload = { status, catatan_admin: catatan };
            if (status === 'disetujui') {
                if (isLumpsum) {
                    let total = document.getElementById('r_totalLumpsum').value;
                    if (!total || Number(total) <= 0) {
                        Swal.fire('Error', 'Nominal Total ACC tidak boleh kosong/nol', 'error');
                        return;
                    }
                    payload.total_lumpsum = total;
                } else {
                    payload.items = rItems;
                }
            }

            fetch(`<?= API_URL ?>/api/anggaran/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify(payload)
            }).then(r=>r.json()).then(res=>{
                if(res.success) {
                    Swal.fire('Berhasil', res.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            })
        }
    });
}

function viewDetail(p) {
    let isApproved = p.status === 'disetujui' || p.status === 'selesai' || p.status === 'dilaporkan';
    
    if (isApproved) {
        Swal.fire({
            title: 'Memuat Data Review...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(`<?= API_URL ?>/api/anggaran/${p.id}/nota`)
            .then(r => r.json())
            .then(res => {
                let notas = res.data || [];
                renderViewDetailHtml(p, isApproved, notas);
            })
            .catch(err => {
                renderViewDetailHtml(p, isApproved, []);
            });
    } else {
        renderViewDetailHtml(p, isApproved, []);
    }
}

function renderViewDetailHtml(p, isApproved, notas) {
    window.currentPrintP = p; 
    
    let itemHtml = '';
    p.items.forEach((it, i) => {
        itemHtml += `
        <tr>
            <td class="text-center text-muted" style="width: 40px">${i+1}</td>
            <td>
                <div class="fw-bold text-dark">${it.nama_item}</div>
                <div class="small text-muted">${it.qty} ${it.satuan} @ Rp ${formatRupiah(it.harga_satuan)} ${it.bagian ? `(Bag. ${it.bagian})` : ''}</div>
            </td>
            <td class="text-end text-secondary align-middle">Rp ${formatRupiah(it.nominal_ajuan)}</td>
            <td class="text-end fw-bold text-success align-middle">Rp ${formatRupiah(it.nominal_disetujui)}</td>
        </tr>`;
    });
    
    let stampHtml = isApproved 
        ? `<div class="d-flex align-items-center mb-4 p-3 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25 shadow-sm">
             <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px;">
                 <i class="bi bi-check2-all fs-3"></i>
             </div>
             <div>
                 <h6 class="fw-bold text-success mb-1 text-uppercase" style="letter-spacing: 1px;">Status: ${p.status.toUpperCase()}</h6>
                 <div class="text-muted small">Anggaran ini telah ditinjau dan disetujui pada <strong>${p.tanggal_disetujui ? p.tanggal_disetujui : '-'}</strong>.</div>
             </div>
           </div>` 
        : '';

    let notaHtml = '';
    let totalLaporan = 0;
    window.currentPrintNotas = notas || [];
    if (isApproved && notas.length > 0) {
        notaHtml += `
            <div class="mt-5 border-top pt-4">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-receipt me-2 text-primary"></i>LAPORAN REALISASI & NOTA</h5>
        `;

        let hasAnyBagian = notas.some(n => n.bagian && n.bagian.trim() !== '');
        let groupedNotas = {};
        
        if (hasAnyBagian) {
            notas.forEach(n => {
                let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
                if (!groupedNotas[b]) groupedNotas[b] = [];
                groupedNotas[b].push(n);
            });
        } else {
            groupedNotas['SEMUA'] = notas;
        }
        
        let keys = Object.keys(groupedNotas);
        if (hasAnyBagian) {
            keys = keys.sort((a, b) => {
                if (a === 'LAIN-LAIN') return 1;
                if (b === 'LAIN-LAIN') return -1;
                return a.localeCompare(b);
            });
        }
        
        keys.forEach(b => {
            if (hasAnyBagian) {
                let divTotal = groupedNotas[b].reduce((acc, n) => acc + Number(n.total_belanja), 0);
                notaHtml += `
                    <div class="d-flex justify-content-between align-items-center bg-light border-start border-primary border-4 p-3 rounded mb-3 mt-4">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase"><i class="bi bi-tags-fill text-primary me-2"></i>DIVISI / BAGIAN: <span class="text-primary">${b}</span></h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 fs-6">Subtotal: Rp ${formatRupiah(divTotal)}</span>
                    </div>
                `;
            }
            
            notaHtml += `<div class="row g-3">`;

            groupedNotas[b].forEach((n, idx) => {
                totalLaporan += Number(n.total_belanja);
                let nItemsHtml = '';
                if(n.items && n.items.length > 0) {
                    n.items.forEach(ni => {
                        nItemsHtml += `<li class="list-group-item d-flex justify-content-between px-3 py-2 border-light">
                                        <div><div class="fw-medium text-dark">${ni.nama_barang}</div><div class="text-muted" style="font-size:0.75rem">${ni.qty} ${ni.satuan || ''} x ${formatRupiah(ni.harga_satuan)}</div></div>
                                        <div class="fw-bold text-dark align-self-center">Rp ${formatRupiah(ni.subtotal)}</div>
                                     </li>`;
                    });
                }
                notaHtml += `
                    <div class="col-md-6">
                        <div class="card border border-secondary-subtle shadow-sm h-100 rounded-4 overflow-hidden">
                            <div class="card-header bg-light border-bottom border-secondary-subtle d-flex justify-content-between align-items-center py-3 px-3">
                                <div>
                                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;">No Nota</div>
                                    <div class="fw-bold">${n.nomor_nota || '-'}</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;">Tanggal</div>
                                    <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1">${n.tanggal}</div>
                                </div>
                            </div>
                            <div class="card-body p-0 bg-white">
                                <ul class="list-group list-group-flush small">
                                    ${nItemsHtml}
                                </ul>
                            </div>
                            <div class="card-footer bg-success bg-opacity-10 border-top border-success border-opacity-25 d-flex justify-content-between align-items-center py-3 px-3">
                                <span class="fw-bold text-success small text-uppercase" style="letter-spacing: 1px;">Subtotal</span>
                                <span class="fw-bold text-success fs-5">Rp ${formatRupiah(n.total_belanja)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            notaHtml += `</div>`;
        });
                
        notaHtml += `
                <div class="card bg-dark text-white mt-4 shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-wallet2 fs-4 text-white"></i>
                            </div>
                            <div>
                                <div class="text-white-50 small mb-1 text-uppercase fw-bold" style="letter-spacing: 1px;">Ringkasan Akhir</div>
                                <h4 class="mb-0 fw-bold">Total Laporan Realisasi</h4>
                            </div>
                        </div>
                        <div class="text-end">
                            <h2 class="mb-0 fw-bold text-warning">Rp ${formatRupiah(totalLaporan)}</h2>
                            <div class="${(p.total_disetujui - totalLaporan) < 0 ? 'text-danger bg-danger bg-opacity-10 px-2 py-1 rounded d-inline-block' : 'text-info'} small mt-2 fw-medium">
                                Sisa Anggaran: Rp ${formatRupiah(p.total_disetujui - totalLaporan)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (isApproved) {
        notaHtml = `
            <div class="mt-5 border-top pt-5 pb-4 text-center text-muted bg-light rounded-4 border border-secondary-subtle">
                <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                <h6 class="fw-bold mb-1">Belum Ada Laporan Realisasi</h6>
                <div class="small">Laporan nota belanja yang diinput akan muncul di sini.</div>
            </div>
        `;
    }

    Swal.fire({
        title: false,
        showConfirmButton: false,
        showCloseButton: true,
        width: isApproved ? 900 : 800,
        padding: 0,
        html: `
            <div class="text-start bg-white rounded-4 overflow-hidden position-relative">
                <div class="bg-primary p-4 text-white position-relative shadow-sm" style="z-index: 1;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1">${isApproved ? 'REVIEW DETIL ANGGARAN' : 'PROPOSAL ANGGARAN'}</h3>
                            <div class="text-white-50 small">ID Dokumen: #ANGG-${p.id.toString().padStart(4, '0')}</div>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-0">${p.instansi}</h5>
                            <div class="text-white-50 small">Periode: ${p.bulan_hijriah}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-light p-3 border-bottom border-secondary-subtle d-flex flex-wrap justify-content-end gap-2 position-relative" style="z-index: 1;">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm fw-bold" onclick="printLaporanAnggaran(window.currentPrintP, window.currentPrintNotas)"><i class="bi bi-file-earmark-bar-graph me-1"></i>Cetak Laporan Detail</button>
                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold" onclick="printSuratPengajuan(window.currentPrintP)"><i class="bi bi-printer me-1"></i>Cetak Surat Pengajuan</button>
                </div>
                
                <div class="p-4 pt-4 position-relative" style="z-index: 1;">
                    ${stampHtml}
                    ${isApproved ? '<h5 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-text me-2 text-primary"></i>PROPOSAL & PERSETUJUAN</h5>' : ''}
                    
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover mb-4" style="font-size: 0.9rem;">
                            <thead class="border-bottom border-secondary-subtle">
                                <tr class="text-muted small text-uppercase">
                                    <th class="text-center pb-2">No</th>
                                    <th class="pb-2">Rincian Kebutuhan</th>
                                    <th class="text-end pb-2">Nilai Ajuan</th>
                                    <th class="text-end pb-2 text-success">Nilai ACC</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemHtml}
                            </tbody>
                            <tfoot class="border-top border-secondary-subtle">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold py-3">TOTAL KESELURUHAN :</td>
                                    <td class="text-end fw-bold text-secondary py-3">Rp ${formatRupiah(p.total_ajuan)}</td>
                                    <td class="text-end fw-bold text-success fs-5 py-3">Rp ${formatRupiah(p.total_disetujui)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    ${p.catatan_admin ? `
                    <div class="bg-warning bg-opacity-10 border-start border-warning border-4 p-3 rounded small mb-4 shadow-sm">
                        <div class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-info-circle me-1"></i>Catatan Persetujuan:</div>
                        <div class="text-dark">${p.catatan_admin}</div>
                    </div>` : ''}

                    ${notaHtml}

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-5 border-top border-secondary-subtle pt-4" data-html2canvas-ignore="true">
                        <button class="btn btn-secondary rounded-pill px-5 shadow-sm fw-medium" onclick="Swal.close()">Tutup</button>
                    </div>
                </div>
            </div>
        `
    });
}

// ==========================================
// LAPOR NOTA
// ==========================================
let currentPengajuanId = null;
let currentNotaItems = [];
let currentPengajuan = null;

function laporNota(id) {
    currentPengajuanId = id;
    currentPengajuan = allPengajuans.find(p => p.id == id);
    currentNotaItems = [];
    document.getElementById('n_pengajuan_id').value = id;
    document.getElementById('n_nota_id').value = '';
    document.getElementById('n_nomor').value = '';
    document.getElementById('n_bagian_nota').value = '';
    document.getElementById('n_file').value = '';
    
    let datalist = document.getElementById('listBagianNota');
    if (datalist) {
        datalist.innerHTML = '';
        let bagians = new Set();
        if (currentPengajuan && currentPengajuan.items) {
            currentPengajuan.items.forEach(it => {
                if (it.bagian && it.bagian.trim() !== '') bagians.add(it.bagian.trim());
            });
        }
        bagians.forEach(b => {
            let opt = document.createElement('option');
            opt.value = b;
            datalist.appendChild(opt);
        });
    }
    
    renderNotaItems();
    loadNotaList();
    new bootstrap.Modal(document.getElementById('modalNota')).show();
    setTimeout(() => {
        document.getElementById('n_nomor').focus();
    }, 500);
}

let loadedNotas = [];

function editNota(id) {
    const nota = loadedNotas.find(n => n.id == id);
    if(!nota) return;
    
    document.getElementById('n_nota_id').value = nota.id;
    document.getElementById('n_nomor').value = nota.nomor_nota;
    document.getElementById('n_bagian_nota').value = nota.bagian || '';
    document.getElementById('n_tanggal').value = nota.tanggal;
    
    currentNotaItems = nota.items.map(it => ({
        nama_barang: it.nama_barang,
        qty: it.qty,
        satuan: it.satuan,
        bagian: it.bagian,
        harga_satuan: it.harga_satuan
    }));
    
    renderNotaItems();
    document.getElementById('n_nomor').focus();
}

function notaInputKeydown(e, nextId, prevId) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (e.shiftKey) {
            if (prevId) {
                let el = document.getElementById(prevId);
                if (el) el.focus();
            }
        } else {
            if (nextId === 'ADD_BARANG') {
                addBarangNota();
            } else if (nextId) {
                let el = document.getElementById(nextId);
                if (el) el.focus();
            }
        }
    }
}

document.getElementById('modalNota').addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        simpanNota();
    }
});

function addBarangNota() {
    const nama = document.getElementById('nb_nama').value;
    const qty = document.getElementById('nb_qty').value;
    const hrg = document.getElementById('nb_harga').value;
    const satuan = document.getElementById('nb_satuan').value;
    const bagian = document.getElementById('nb_bagian').value;
    if(!nama || !qty || !hrg) return;
    
    currentNotaItems.push({ nama_barang: nama, qty: qty, harga_satuan: hrg, satuan: satuan, bagian: bagian });
    document.getElementById('nb_nama').value = '';
    document.getElementById('nb_qty').value = 1;
    document.getElementById('nb_harga').value = '';
    document.getElementById('nb_satuan').value = '';
    document.getElementById('nb_bagian').value = '';
    document.getElementById('nb_nama').focus();
    renderNotaItems();
}

function renderNotaItems() {
    const tbody = document.getElementById('tblBarangNota');
    tbody.innerHTML = '';
    let total = 0;
    currentNotaItems.forEach((it, idx) => {
        let sub = Number(it.qty) * Number(it.harga_satuan);
        total += sub;
        tbody.innerHTML += `
            <tr>
                <td class="px-0">
                    <div class="fw-medium">${it.nama_barang}</div>
                    <div class="text-muted" style="font-size:0.75rem">${it.qty} ${it.satuan || 'x'} ${it.bagian ? `(Bag. ${it.bagian})` : ''}</div>
                </td>
                <td class="text-end px-0 text-muted align-middle">${formatRupiah(it.harga_satuan)}</td>
                <td class="text-end px-0 fw-bold align-middle">Rp ${formatRupiah(sub)}</td>
                <td class="text-end px-0 align-middle"><i class="bi bi-x-circle text-danger ms-2" style="cursor:pointer" onclick="removeBarangNota(${idx})"></i></td>
            </tr>
        `;
    });
    document.getElementById('nb_total').innerText = 'Rp ' + formatRupiah(total);
}

function removeBarangNota(idx) {
    currentNotaItems.splice(idx, 1);
    renderNotaItems();
}

function loadNotaList() {
    const c = document.getElementById('notaListContainer');
    c.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat nota...</div>';
    
    fetch(`<?= API_URL ?>/api/anggaran/${currentPengajuanId}/nota`).then(r=>r.json()).then(res=>{
        if(res.data.length === 0) {
            c.innerHTML = `<div class="alert alert-light border text-center text-muted"><i class="bi bi-emoji-frown fs-3 d-block mb-2"></i>Belum ada laporan nota</div>`;
            document.getElementById('n_nomor').value = '1';
            return;
        }
        
        loadedNotas = res.data;
        if (!document.getElementById('n_nota_id').value) {
            document.getElementById('n_nomor').value = (res.data.length + 1).toString();
        }
        let hasAnyBagian = res.data.some(n => n.bagian && n.bagian.trim() !== '');
        let groupedNotas = {};
        
        if (hasAnyBagian) {
            res.data.forEach(n => {
                let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
                if (!groupedNotas[b]) groupedNotas[b] = [];
                groupedNotas[b].push(n);
            });
        } else {
            groupedNotas['SEMUA'] = res.data;
        }
        
        let keys = Object.keys(groupedNotas);
        if (hasAnyBagian) {
            keys = keys.sort((a, b) => {
                if (a === 'LAIN-LAIN') return 1;
                if (b === 'LAIN-LAIN') return -1;
                return a.localeCompare(b);
            });
        }
        
        let htmlContent = '';
        keys.forEach(b => {
            if (hasAnyBagian) {
                let divTotal = groupedNotas[b].reduce((acc, n) => acc + Number(n.total_belanja), 0);
                htmlContent += `
                <div class="d-flex justify-content-between align-items-center bg-light border-start border-primary border-4 p-2 rounded mb-2 mt-3 shadow-sm">
                    <h6 class="mb-0 fw-bold text-dark text-uppercase" style="font-size:0.85rem; letter-spacing: 0.5px;"><i class="bi bi-tags-fill text-primary me-2"></i>${b}</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem">Rp ${formatRupiah(divTotal)}</span>
                </div>
                `;
            }
            
            groupedNotas[b].forEach(n => {
                let trs = '';
                n.items.forEach(it => {
                    trs += `<tr><td class="py-1 text-muted">${it.nama_barang} <span class="badge bg-light text-dark ms-1">${it.qty} ${it.satuan || 'x'}</span> ${it.bagian ? `<span class="badge bg-light text-dark ms-1">Bag. ${it.bagian}</span>` : ''}</td><td class="py-1 text-end fw-medium">Rp ${formatRupiah(it.subtotal)}</td></tr>`;
                });
                
                htmlContent += `
                <div class="card border border-success-subtle shadow-sm rounded-4 overflow-hidden mb-2">
                    <div class="card-header bg-success bg-opacity-10 border-0 py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-receipt-cutoff text-success me-2"></i><strong class="text-dark">${n.nomor_nota}</strong>
                            ${!hasAnyBagian && n.bagian ? `<span class="badge bg-primary bg-opacity-10 text-primary ms-2 border border-primary-subtle">${n.bagian}</span>` : ''}
                            <span class="text-muted small ms-2">${n.tanggal}</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-light text-warning py-0 me-1" onclick="editNota(${n.id})" title="Edit Nota"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-primary py-0" onclick="printNotaCard(this)" title="Cetak Nota"><i class="bi bi-printer"></i></button>
                            ${n.file_path ? `<a href="${n.file_path.startsWith('/uploads/') ? n.file_path : '<?= API_URL ?>/api/anggaran/view-nota/' + n.id}" target="_blank" class="btn btn-sm btn-light py-0 ms-1"><i class="bi bi-image text-primary"></i></a>` : ''}
                            <button class="btn btn-sm btn-light text-danger py-0 ms-1" onclick="deleteNota(${n.id})"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless m-0" style="font-size:0.85rem">
                            <tbody class="px-3 d-block pt-2 pb-2">${trs}</tbody>
                            <tfoot class="border-top bg-light d-block px-3 py-2">
                                <tr class="d-flex justify-content-between w-100">
                                    <td class="fw-bold p-0">Total Nota</td>
                                    <td class="fw-bold text-success p-0 fs-6">Rp ${formatRupiah(n.total_belanja)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>`;
            });
        });
        
        c.innerHTML = htmlContent;
    });
}

function simpanNota() {
    if(currentNotaItems.length === 0) {
        Swal.fire('Error', 'Silakan masukkan rincian barang terlebih dahulu.', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('nota_id', document.getElementById('n_nota_id').value);
    formData.append('nomor_nota', document.getElementById('n_nomor').value);
    formData.append('bagian', document.getElementById('n_bagian_nota').value);
    formData.append('tanggal', document.getElementById('n_tanggal').value);
    formData.append('items', JSON.stringify(currentNotaItems));
    
    const file = document.getElementById('n_file').files[0];
    if(file) formData.append('file_nota', file);
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    
    const btn = document.getElementById('btnSimpanNota');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    btn.disabled = true;
    
    fetch(`<?= API_URL ?>/api/anggaran/${currentPengajuanId}/nota/store`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf },
        body: formData
    }).then(r=>r.json()).then(res=>{
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if(res.success) {
            currentNotaItems = [];
            renderNotaItems();
            document.getElementById('n_nota_id').value = '';
            document.getElementById('n_file').value = '';
            loadNotaList();
            document.getElementById('n_nomor').focus();
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }).catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
    });
}

function deleteNota(id) {
    Swal.fire({
        title: 'Hapus Nota?',
        text: "Saldo akan dikembalikan.",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/nota/${id}/delete`, { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
            .then(r=>r.json()).then(res=>{
                if(res.success) loadNotaList();
            });
        }
    });
}

function tandaiDilaporkan(id) {
    Swal.fire({
        title: 'Tandai Sebagai Dilaporkan?',
        text: "Anda akan menandai anggaran ini sebagai telah dilaporkan ke ADM. Pastikan nota sudah Anda input.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tandai'
    }).then((res) => {
        if(res.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ status: 'dilaporkan' })
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire('Berhasil', 'Anggaran ditandai sebagai Dilaporkan!', 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

function tandaiSelesai(id) {
    Swal.fire({
        title: 'Selesaikan Anggaran Bulan Ini?',
        text: "Pastikan semua nota telah dilaporkan. Anggaran akan ditandai selesai.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesai'
    }).then((res) => {
        if(res.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`<?= API_URL ?>/api/anggaran/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ status: 'selesai' })
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire('Berhasil', 'Anggaran Selesai!', 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });
}

document.getElementById('modalNota').addEventListener('hidden.bs.modal', function () {
    location.reload();
});

function printHtml(htmlContent) {
    let printArea = document.getElementById('printArea');
    
    // Pindahkan printArea langsung ke bawah body agar tidak terpotong parent-nya
    if (printArea.parentNode !== document.body) {
        document.body.appendChild(printArea);
    }
    
    printArea.innerHTML = htmlContent;
    printArea.style.display = 'block';
    printArea.style.position = 'absolute';
    printArea.style.top = '0';
    printArea.style.left = '0';
    printArea.style.width = '100%';
    printArea.style.minHeight = '100vh';
    printArea.style.backgroundColor = 'white';
    printArea.style.zIndex = '999999';
    
    // Tambahkan style khusus cetak secara dinamis
    const tempStyle = document.createElement('style');
    tempStyle.id = 'tempPrintStyle';
    tempStyle.innerHTML = `
        @media print {
            @page { margin: 10mm 12mm 15mm 12mm; }
            body > *:not(#printArea):not(script):not(style) { display: none !important; }
            #printArea { display: block !important; position: static !important; min-height: auto; }
        }
    `;
    document.head.appendChild(tempStyle);
    
    // Tunggu sebentar agar style sempat diterapkan
    setTimeout(() => {
        const imgs = printArea.querySelectorAll('img');
        let loadedCount = 0;
        
        function doPrint() {
            // Beri sedikit jeda ekstra setelah gambar termuat agar render sempurna
            setTimeout(() => {
                window.print();
                
                // Kembalikan seperti semula
                printArea.style.display = 'none';
                printArea.innerHTML = '';
                document.head.removeChild(tempStyle);
            }, 200);
        }

        if (imgs.length === 0) {
            doPrint();
        } else {
            // Fallback timeout: Jika server sangat lambat, paksa cetak setelah 3 detik
            let fallbackTimeout = setTimeout(doPrint, 3000);
            
            imgs.forEach(img => {
                if (img.complete) {
                    loadedCount++;
                    if (loadedCount === imgs.length) {
                        clearTimeout(fallbackTimeout);
                        doPrint();
                    }
                } else {
                    img.addEventListener('load', () => {
                        loadedCount++;
                        if (loadedCount === imgs.length) {
                            clearTimeout(fallbackTimeout);
                            doPrint();
                        }
                    });
                    img.addEventListener('error', () => {
                        loadedCount++;
                        if (loadedCount === imgs.length) {
                            clearTimeout(fallbackTimeout);
                            doPrint();
                        }
                    });
                }
            });
        }
    }, 100);
}

function printNotaCard(btnEl) {
    const cardHtml = btnEl.closest('.card').outerHTML;
    printHtml(`
        <div class="p-4 bg-white">
            <h3 class="fw-bold mb-4 border-bottom pb-2">Bukti Laporan Pembelanjaan (Nota)</h3>
            ${cardHtml}
        </div>
    `);
}

function printLaporanAnggaran(p, notas) {
    if (!p) return;
    
    let targetInstansi = instansiMap[p.instansi] || {};
    let namaInstansi = targetInstansi.nama_instansi || p.instansi;
    let kodeInstansi = targetInstansi.kode || '';
    
    let imgSrc = `<?= API_URL ?>/profil-instansi/kop-surat/view?kode=${encodeURIComponent(kodeInstansi)}&v=${new Date().getTime()}`;

    let html = `
    <div style="padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: white; color: #333;">
        <!-- Kop Surat Image -->
        <img src="${imgSrc}" style="width: 100%; max-height: 200px; object-fit: contain; object-position: top; margin-bottom: 20px;" 
             onerror="this.style.display='none'; document.getElementById('kop_text_fallback_${p.id}_laporan').style.display='block';">
             
        <!-- Fallback Kop Surat HTML -->
        <div id="kop_text_fallback_${p.id}_laporan" style="display: none; width: 100%; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; color: #004aad; padding: 10px 10px;">
                <div style="text-align: left;">
                    <h2 style="margin:0; font-weight: 800; font-size: 22px; letter-spacing: 0.5px;">ADVISORY OF FOREIGN STUDENT</h2>
                    <h5 style="margin:6px 0 0 0; font-size: 13px; font-weight: 700;">DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL</h5>
                    <h5 style="margin:4px 0 0 0; font-size: 13px; font-weight: 600;">GONTOR-PONOROGO-INDONESIA</h5>
                </div>
                <div style="text-align: right;" dir="rtl">
                    <h2 style="margin:0; font-weight: bold; font-size: 26px; font-family: 'Times New Roman', serif;">هيئة إشراف شؤون الطلاب الوافدين</h2>
                    <h5 style="margin:6px 0 0 0; font-size: 15px; font-weight: bold; font-family: 'Times New Roman', serif;">معهد دار السلام كونتور فونوروغو</h5>
                    <h5 style="margin:4px 0 0 0; font-size: 15px; font-weight: bold; font-family: 'Times New Roman', serif;">للتربية الإسلامية الحديثة</h5>
                </div>
            </div>
            <div style="background-color: #004aad; color: white; text-align: center; padding: 6px; font-style: italic; font-size: 12px; border-radius: 4px;">
                Head Office : Sahifah One Building Second Floor Room 203, Email: plngontor@gmail.com
            </div>
        </div>

        <!-- Title -->
        <div style="text-align: center; margin-top: 25px; margin-bottom: 35px;">
            <h4 style="font-weight: 800; margin: 0; font-size: 20px; color: #212529; letter-spacing: 1px;">LAPORAN ANGGARAN OPERASIONAL</h4>
            <h4 style="font-weight: 700; margin: 8px 0 0 0; font-size: 16px; color: #495057; text-transform: uppercase;">${namaInstansi} <span style="opacity: 0.6;">|</span> BULAN ${p.bulan_hijriah}</h4>
        </div>

        <!-- Tables per Nota -->
        ${(() => {
            let hasAnyBagian = notas.some(n => n.bagian && n.bagian.trim() !== '');
            
            const renderNotaTable = (n, i, isGrouped) => {
                let totalNota = n.items.reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0);
                let hasItemBagian = n.items.some(it => it.bagian && it.bagian.trim() !== '');
                let tbodyHtml = '';
                let globalIdx = 1;
                
                if (hasItemBagian) {
                    let grouped = {};
                    n.items.forEach(it => {
                        let b = (it.bagian && it.bagian.trim() !== '') ? it.bagian.trim() : 'Lain-lain';
                        if (!grouped[b]) grouped[b] = [];
                        grouped[b].push(it);
                    });
                    
                    for (let b in grouped) {
                        tbodyHtml += `
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="6" style="padding: 8px 10px; text-align: left; font-weight: bold; color: #495057; border-bottom: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                                    <i class="bi bi-tag-fill me-2" style="color: #6c757d;"></i>Bagian / Divisi: ${b}
                                </td>
                            </tr>
                        `;
                        grouped[b].forEach(it => {
                            tbodyHtml += `
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 10px; border-right: 1px solid #e9ecef; color: #6c757d;">${globalIdx++}</td>
                                <td style="padding: 10px; border-right: 1px solid #e9ecef; text-align: left; font-weight: 500; color: #212529;">${it.nama_barang}</td>
                                <td style="padding: 10px; border-right: 1px solid #e9ecef;">${it.qty}</td>
                                <td style="padding: 10px; border-right: 1px solid #e9ecef;">${it.satuan || '-'}</td>
                                <td style="padding: 10px; border-right: 1px solid #e9ecef; text-align: right; color: #495057;">Rp${formatRupiah(it.harga_satuan)}</td>
                                <td style="padding: 10px; text-align: right; font-weight: 600; color: #212529;">Rp${formatRupiah(it.subtotal)}</td>
                            </tr>
                            `;
                        });
                    }
                } else {
                    n.items.forEach(it => {
                        tbodyHtml += `
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 10px; border-right: 1px solid #e9ecef; color: #6c757d;">${globalIdx++}</td>
                            <td style="padding: 10px; border-right: 1px solid #e9ecef; text-align: left; font-weight: 500; color: #212529;">${it.nama_barang}</td>
                            <td style="padding: 10px; border-right: 1px solid #e9ecef;">${it.qty}</td>
                            <td style="padding: 10px; border-right: 1px solid #e9ecef;">${it.satuan || '-'}</td>
                            <td style="padding: 10px; border-right: 1px solid #e9ecef; text-align: right; color: #495057;">Rp${formatRupiah(it.harga_satuan)}</td>
                            <td style="padding: 10px; text-align: right; font-weight: 600; color: #212529;">Rp${formatRupiah(it.subtotal)}</td>
                        </tr>
                        `;
                    });
                }

                let badgeHtml = (!isGrouped && n.bagian) ? `<div style="background-color: #f1f3f5; border: 1px solid #ced4da; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 700; color: #495057; -webkit-print-color-adjust: exact; print-color-adjust: exact;">BAGIAN/DIVISI: <span style="color: #0d6efd; margin-left: 4px;">${n.bagian.toUpperCase()}</span></div>` : '';

                return `
                <div style="margin-bottom: 30px; page-break-inside: avoid;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h5 style="font-weight: 800; margin: 0; font-size: 16px; color: #212529;">Nota Ke- ${n.nomor_nota || (i + 1)}</h5>
                        ${badgeHtml}
                    </div>
                    <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 14px; box-shadow: 0 0 0 1px #dee2e6; border-radius: 8px; overflow: hidden;">
                        <thead>
                            <tr style="background-color: #f1f3f5; color: #495057;">
                                <th style="padding: 10px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; width: 5%;">No</th>
                                <th style="padding: 10px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; text-align: left;">Nama Barang</th>
                                <th style="padding: 10px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; width: 10%;">Jumlah</th>
                                <th style="padding: 10px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; width: 12%;">Satuan</th>
                                <th style="padding: 10px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; text-align: right; width: 20%;">Harga Satuan</th>
                                <th style="padding: 10px; border-bottom: 2px solid #dee2e6; text-align: right; width: 20%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tbodyHtml}
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="5" style="padding: 12px; font-weight: 700; text-align: center; border-right: 1px solid #dee2e6; color: #495057;">Total Nota ${n.nomor_nota || (i + 1)}</td>
                                <td style="padding: 12px; font-weight: 800; text-align: right; font-size: 15px; color: #0d6efd;">Rp${formatRupiah(totalNota)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                `;
            };

            if (!hasAnyBagian) {
                return notas.map((n, i) => renderNotaTable(n, i, false)).join('');
            }
            
            let groupedNotas = {};
            notas.forEach(n => {
                let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
                if (!groupedNotas[b]) groupedNotas[b] = [];
                groupedNotas[b].push(n);
            });
            
            let htmlGroups = '';
            let keys = Object.keys(groupedNotas).sort((a, b) => {
                if (a === 'LAIN-LAIN') return 1;
                if (b === 'LAIN-LAIN') return -1;
                return a.localeCompare(b);
            });

            for (let b of keys) {
                let divisiTotal = 0;
                let originalIndexes = groupedNotas[b].map(n => notas.indexOf(n));
                
                let notasHtmlArray = groupedNotas[b].map((n, idx) => {
                    divisiTotal += n.items.reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0);
                    return renderNotaTable(n, originalIndexes[idx] + 1, true); // +1 because i is 0-indexed
                });
                
                let firstNota = notasHtmlArray.shift() || '';
                let restNotas = notasHtmlArray.join('');
                
                htmlGroups += `
                <div style="margin-top: 35px; margin-bottom: 40px;">
                    <div style="page-break-inside: avoid;">
                        <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: 5px solid #0d6efd; border-radius: 6px; padding: 10px 15px; margin-bottom: 20px; -webkit-print-color-adjust: exact; print-color-adjust: exact; display: flex; align-items: center;">
                            <span style="background-color: #0d6efd; color: white; padding: 4px 8px; border-radius: 4px; margin-right: 12px; font-size: 12px;"><i class="bi bi-tags-fill"></i></span>
                            <h4 style="font-weight: 800; color: #212529; margin: 0; font-size: 16px; letter-spacing: 0.5px; text-transform: uppercase;">
                                DIVISI / BAGIAN: <span style="color: #0d6efd;">${b}</span>
                            </h4>
                        </div>
                        
                        ${firstNota}
                    </div>
                    
                    ${restNotas}
                    
                    <div style="display: flex; justify-content: flex-end; margin-top: -15px;">
                        <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-right: 5px solid #0d6efd; border-radius: 6px; padding: 12px 20px; -webkit-print-color-adjust: exact; print-color-adjust: exact; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <span style="font-weight: 700; color: #495057; margin-right: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Divisi ${b}:</span>
                            <span style="font-weight: 900; font-size: 18px; color: #0d6efd;">Rp${formatRupiah(divisiTotal)}</span>
                        </div>
                    </div>
                </div>`;
            }
            return htmlGroups;
        })()}

        <!-- Rekapitulasi / Summary -->
        ${(() => {
            let totalBelanjaan = notas.reduce((acc, n) => acc + n.items.reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0), 0);
            let nominalACC = parseFloat(p.total_disetujui || 0);
            let sisa = nominalACC - totalBelanjaan;
            
            let colorClass = sisa < 0 ? '#dc3545' : '#198754';
            let sisaText = sisa < 0 ? `(Minus) Rp${formatRupiah(Math.abs(sisa))}` : `Rp${formatRupiah(sisa)}`;
            
            let hasAnyBagian = notas.some(n => n.bagian && n.bagian.trim() !== '');
            let rekapDivisiHtml = '';
            
            if (hasAnyBagian) {
                let groupedTotals = {};
                notas.forEach(n => {
                    let b = (n.bagian && n.bagian.trim() !== '') ? n.bagian.trim() : 'LAIN-LAIN';
                    if (!groupedTotals[b]) groupedTotals[b] = 0;
                    groupedTotals[b] += n.items.reduce((sum, it) => sum + parseFloat(it.subtotal || 0), 0);
                });
                
                let keys = Object.keys(groupedTotals).sort((a, b) => {
                    if (a === 'LAIN-LAIN') return 1;
                    if (b === 'LAIN-LAIN') return -1;
                    return a.localeCompare(b);
                });
                
                let tbody = '';
                keys.forEach((b, idx) => {
                    tbody += `
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6;">${idx + 1}</td>
                            <td style="padding: 10px; font-weight: 600; color: #212529; border-right: 1px solid #dee2e6;">${b.toUpperCase()}</td>
                            <td style="padding: 10px; text-align: right; font-weight: 700; color: #495057;">Rp${formatRupiah(groupedTotals[b])}</td>
                        </tr>
                    `;
                });
                
                rekapDivisiHtml = `
                <div style="margin-top: 40px; page-break-inside: avoid;">
                    <h5 style="font-weight: 800; font-size: 16px; text-align: center; margin-bottom: 15px; color: #212529; text-transform: uppercase; letter-spacing: 1px;">Rincian Pengeluaran Per Divisi</h5>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; text-align: left; box-shadow: 0 0 0 1px #dee2e6; border-radius: 8px; overflow: hidden;">
                        <thead>
                            <tr style="background-color: #f1f3f5; color: #495057;">
                                <th style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6; border-bottom: 2px solid #dee2e6; width: 10%;">No</th>
                                <th style="padding: 10px; border-right: 1px solid #dee2e6; border-bottom: 2px solid #dee2e6;">Nama Divisi / Bagian</th>
                                <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6; width: 35%;">Total Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tbody}
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="2" style="padding: 12px; font-weight: 800; text-align: center; border-right: 1px solid #dee2e6; color: #212529;">TOTAL KESELURUHAN</td>
                                <td style="padding: 12px; text-align: right; font-weight: 800; color: #0d6efd; font-size: 15px;">Rp${formatRupiah(totalBelanjaan)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                `;
            }

            return `
            ${rekapDivisiHtml}
            <div style="margin-top: ${hasAnyBagian ? '20px' : '50px'}; border: 2px solid #e9ecef; border-radius: 12px; padding: 25px; page-break-inside: avoid; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <h5 style="font-weight: 800; margin-bottom: 15px; font-size: 16px; color: #212529; text-transform: uppercase; text-align: center; letter-spacing: 1px;">Rekapitulasi Anggaran Total</h5>
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 15px; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background-color: #e9ecef; color: #495057;">
                            <th style="padding: 15px; border-right: 1px solid #dee2e6; width: 33.33%;">Total Belanjaan Keseluruhan</th>
                            <th style="padding: 15px; border-right: 1px solid #dee2e6; width: 33.33%;">Anggaran Disetujui Pimpinan</th>
                            <th style="padding: 15px; width: 33.33%; color: ${colorClass};">Uang Sisa / Kekurangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #fff;">
                            <td style="padding: 15px; border-right: 1px solid #dee2e6; border-top: 1px solid #dee2e6; font-weight: 700; color: #212529; font-size: 16px;">Rp${formatRupiah(totalBelanjaan)}</td>
                            <td style="padding: 15px; border-right: 1px solid #dee2e6; border-top: 1px solid #dee2e6; font-weight: 700; color: #0d6efd; font-size: 16px;">Rp${formatRupiah(nominalACC)}</td>
                            <td style="padding: 15px; border-top: 1px solid #dee2e6; font-weight: 800; color: ${colorClass}; font-size: 18px;">${sisaText}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            `;
        })()}
    </div>
    `;
    
    printHtml(html);
}

function printSemuaNota() {
    printLaporanAnggaran(currentPengajuan, loadedNotas);
}

function printSuratPengajuan(p) {
    let dateStr = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
    let targetInstansi = instansiMap[p.instansi] || {};
    let namaInstansi = targetInstansi.nama_instansi || p.instansi;
    let kodeInstansi = targetInstansi.kode || '';
    
    let imgSrc = `<?= API_URL ?>/profil-instansi/kop-surat/view?kode=${encodeURIComponent(kodeInstansi)}&v=${new Date().getTime()}`;

    let html = `
    <div style="padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: white; color: #333;">
        <!-- Kop Surat Image -->
        <img src="${imgSrc}" style="width: 100%; max-height: 200px; object-fit: contain; object-position: top; margin-bottom: 20px;" 
             onerror="this.style.display='none'; document.getElementById('kop_text_fallback_${p.id}').style.display='block';">
             
        <!-- Fallback Kop Surat HTML -->
        <div id="kop_text_fallback_${p.id}" style="display: none; width: 100%; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; color: #004aad; padding: 10px 10px;">
                <div style="text-align: left;">
                    <h2 style="margin:0; font-weight: 800; font-size: 22px; letter-spacing: 0.5px;">ADVISORY OF FOREIGN STUDENT</h2>
                    <h5 style="margin:6px 0 0 0; font-size: 13px; font-weight: 700;">DARUSSALAM MODERN ISLAMIC BOARDING SCHOOL</h5>
                    <h5 style="margin:4px 0 0 0; font-size: 13px; font-weight: 600;">GONTOR-PONOROGO-INDONESIA</h5>
                </div>
                <div style="text-align: right;" dir="rtl">
                    <h2 style="margin:0; font-weight: bold; font-size: 26px; font-family: 'Times New Roman', serif;">هيئة إشراف شؤون الطلاب الوافدين</h2>
                    <h5 style="margin:6px 0 0 0; font-size: 15px; font-weight: bold; font-family: 'Times New Roman', serif;">معهد دار السلام كونتور فونوروغو</h5>
                    <h5 style="margin:4px 0 0 0; font-size: 15px; font-weight: bold; font-family: 'Times New Roman', serif;">للتربية الإسلامية الحديثة</h5>
                </div>
            </div>
            <div style="background-color: #004aad; color: white; text-align: center; padding: 6px; font-style: italic; font-size: 12px; border-radius: 4px;">
                Head Office : Sahifah One Building Second Floor Room 203, Email: plngontor@gmail.com
            </div>
        </div>

        <!-- Title -->
        <div style="text-align: center; margin-top: 25px; margin-bottom: 35px;">
            <h4 style="font-weight: 800; margin: 0; font-size: 20px; color: #212529; letter-spacing: 1px;">PENGAJUAN ANGGARAN OPERASIONAL</h4>
            <h4 style="font-weight: 700; margin: 8px 0 0 0; font-size: 16px; color: #495057; text-transform: uppercase;">${namaInstansi} <span style="opacity: 0.6;">|</span> BULAN ${p.bulan_hijriah}</h4>
            <div style="margin-top: 15px; display: inline-block; padding: 5px 15px; background: #f8f9fa; border-radius: 20px; font-weight: 600; font-size: 14px; color: #6c757d; border: 1px solid #e9ecef;">
                <i class="bi bi-calendar3 me-2"></i> ${dateStr}
            </div>
        </div>

        <!-- Table Modern -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 25px; text-align: center; font-size: 14px; box-shadow: 0 0 0 1px #dee2e6; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background-color: #f1f3f5; color: #495057;">
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; width: 5%;">No</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; text-align: left;">Rincian Kebutuhan</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; width: 10%;">Qty</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; width: 12%;">Satuan</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; border-right: 1px solid #dee2e6; text-align: right; width: 18%;">Harga Satuan</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: right; width: 20%;">Total Nominal</th>
                </tr>
            </thead>
            <tbody>
                ${(() => {
                    let hasAnyBagian = p.items.some(it => it.bagian && it.bagian.trim() !== '');
                    let tbodyHtml = '';
                    
                    if (hasAnyBagian) {
                        let groupedItems = {};
                        p.items.forEach(it => {
                            let b = (it.bagian && it.bagian.trim() !== '') ? it.bagian.trim() : 'LAIN-LAIN';
                            if (!groupedItems[b]) groupedItems[b] = [];
                            groupedItems[b].push(it);
                        });
                        
                        let keys = Object.keys(groupedItems).sort((a, b) => {
                            if (a === 'LAIN-LAIN') return 1;
                            if (b === 'LAIN-LAIN') return -1;
                            return a.localeCompare(b);
                        });
                        
                        let globalIndex = 1;
                        keys.forEach(b => {
                            let divTotal = groupedItems[b].reduce((sum, it) => sum + parseFloat(it.nominal_ajuan || 0), 0);
                            
                            tbodyHtml += `
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="6" style="padding: 10px 12px; text-align: left; font-weight: 800; color: #0d6efd; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                                    DIVISI / BAGIAN: ${b}
                                </td>
                            </tr>
                            `;
                            
                            groupedItems[b].forEach(it => {
                                tbodyHtml += `
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 12px; border-right: 1px solid #e9ecef; color: #6c757d;">${globalIndex++}</td>
                                    <td style="padding: 12px; border-right: 1px solid #e9ecef; text-align: left; font-weight: 500; color: #212529;">${it.nama_item}</td>
                                    <td style="padding: 12px; border-right: 1px solid #e9ecef;">${it.qty}</td>
                                    <td style="padding: 12px; border-right: 1px solid #e9ecef; font-style: italic; color: #868e96;">${it.satuan}</td>
                                    <td style="padding: 12px; border-right: 1px solid #e9ecef; text-align: right; color: #495057;">Rp${formatRupiah(it.harga_satuan)}</td>
                                    <td style="padding: 12px; text-align: right; font-weight: 600; color: #212529;">Rp${formatRupiah(it.nominal_ajuan)}</td>
                                </tr>
                                `;
                            });
                            
                            tbodyHtml += `
                            <tr style="background-color: #f1f3f5; border-bottom: 2px solid #dee2e6;">
                                <td colspan="5" style="padding: 10px 12px; text-align: right; font-weight: 700; color: #495057; border-right: 1px solid #dee2e6;">Subtotal Divisi ${b}</td>
                                <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #0d6efd;">Rp${formatRupiah(divTotal)}</td>
                            </tr>
                            `;
                        });
                    } else {
                        p.items.forEach((it, i) => {
                            tbodyHtml += `
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 12px; border-right: 1px solid #e9ecef; color: #6c757d;">${i + 1}</td>
                                <td style="padding: 12px; border-right: 1px solid #e9ecef; text-align: left; font-weight: 500; color: #212529;">${it.nama_item}</td>
                                <td style="padding: 12px; border-right: 1px solid #e9ecef;">${it.qty}</td>
                                <td style="padding: 12px; border-right: 1px solid #e9ecef; font-style: italic; color: #868e96;">${it.satuan}</td>
                                <td style="padding: 12px; border-right: 1px solid #e9ecef; text-align: right; color: #495057;">Rp${formatRupiah(it.harga_satuan)}</td>
                                <td style="padding: 12px; text-align: right; font-weight: 600; color: #212529;">Rp${formatRupiah(it.nominal_ajuan)}</td>
                            </tr>
                            `;
                        });
                    }
                    return tbodyHtml;
                })()}
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa;">
                    <td colspan="5" style="padding: 15px; font-weight: 700; text-align: right; border-right: 1px solid #dee2e6; color: #495057; text-transform: uppercase; letter-spacing: 1px;">TOTAL KESELURUHAN</td>
                    <td style="padding: 15px; font-weight: 800; text-align: right; font-size: 16px; color: #0d6efd;">Rp${formatRupiah(p.total_ajuan)}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Modern -->
        <div style="margin-top: 80px; display: flex; justify-content: space-between; padding: 0 30px; font-size: 15px;">
            <div style="text-align: center; width: 300px;">
                <div style="color: #6c757d; font-size: 14px; margin-bottom: 95px;">Mengetahui,</div>
                <div style="border-bottom: 1.5px solid #212529; margin-bottom: 8px;"></div>
                <div style="font-weight: 700; color: #212529;">Koordinator Kamar</div>
            </div>
            <div style="text-align: center; width: 300px;">
                <div style="color: #6c757d; font-size: 14px; margin-bottom: 95px;">Menyetujui,</div>
                <div style="border-bottom: 1.5px solid #212529; margin-bottom: 8px;"></div>
                <div style="font-weight: 700; color: #212529;">Staff ADM</div>
            </div>
        </div>
    </div>
    `;
    printHtml(html);
}
</script>
