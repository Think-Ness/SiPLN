<?php
declare(strict_types=1);
use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $santris
 * @var int $total
 * @var int $page
 * @var int $totalPages
 * @var string $search
 * @var string $pondok
 * @var string $kelas
 * @var array $pondokList
 * @var array $kelasList
 * @var UrlGeneratorInterface $urlGenerator
 */
$this->setTitle('Master Data Santri | Sistem Informasi');
?>

<style>
.tbl-status-exp { background: #fff3cd; }
.tbl-status-kritis { background: #f8d7da; }
.badge-aktif { background:#198754; }
.badge-inaktif { background:#dc3545; }
.preview-row:hover { background-color: #f8f9fa; cursor: pointer; }
.modal-fullwidth { max-width: 900px; }
</style>

<script>
    const allowedFields = <?= json_encode($allowedFields ?? []) ?>;
    const myKepengurusan = '<?= $_SESSION['def_kepengurusan'] ?? '' ?>';
    const IS_SUPER_ADMIN = <?= (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') ? 'true' : 'false' ?>;
    const instansiList = <?= json_encode($instansiList ?? []) ?>;
</script>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #0d6efd15;">
            <i class="bi bi-people fs-5 text-primary"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Data Santri Luar Negeri</h4>
            <div class="text-muted small fw-medium mt-1">Total: <strong class="text-dark"><?= number_format($total) ?></strong> santri aktif</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info rounded-pill px-4 fw-medium shadow-sm" onclick="tarikDataFirebase()">
            <i class="bi bi-cloud-arrow-down me-1"></i> Tarik Data
        </button>
        <button class="btn btn-outline-warning rounded-pill px-4 fw-medium shadow-sm" onclick="new bootstrap.Modal(document.getElementById('modalAutoItas')).show()">
            <i class="bi bi-robot me-1"></i> Auto-Upload ITAS
        </button>
        <a href="<?= API_URL ?>/import-excel" class="btn btn-outline-success rounded-pill px-4 fw-medium shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Excel
        </a>
        <button class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" onclick="openModal('add')">
            <i class="bi bi-plus-circle me-1"></i> Tambah Santri
        </button>
    </div>
</div>

<!-- Filter & Bulk Action Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <button class="btn btn-sm btn-primary d-none me-2 rounded-pill shadow-sm px-3" id="btnBulkBukaProses" onclick="bukaProsesBulkSantri()">
            <i class="bi bi-play-circle me-1"></i> Buka Proses Terpilih (<span id="countSelectedProses">0</span>)
        </button>
        <button class="btn btn-sm btn-danger d-none me-2 rounded-pill shadow-sm px-3" id="btnBulkNonaktif" onclick="bulkNonaktif()">
            <i class="bi bi-trash-fill me-1"></i> Nonaktifkan Terpilih (<span id="countSelected">0</span>)
        </button>
        <button class="btn btn-sm btn-info d-none me-2 rounded-pill shadow-sm px-3 text-white" id="btnBulkEdit" onclick="bukaEditMassal()">
            <i class="bi bi-pencil-square me-1"></i> Edit Massal (<span id="countSelectedEdit">0</span>)
        </button>
    </div>
</div>

<div class="accordion mb-4 shadow-sm border-0 rounded-4 overflow-hidden" id="filterAccordion">
  <div class="accordion-item border-0">
    <h2 class="accordion-header" id="headingFilter">
      <button class="accordion-button collapsed py-3 bg-light fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
        <i class="bi bi-funnel text-primary me-2"></i> Filter Pencarian Lanjutan
      </button>
    </h2>
    <div id="collapseFilter" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
      <div class="accordion-body bg-white py-4 px-4 border-top">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">PONDOK</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-house-door text-secondary"></i></span>
                    <select id="flt_pondok" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Pondok</option>
                        <?php foreach ($pondokList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">KEPENGURUSAN</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-building text-secondary"></i></span>
                    <select id="flt_kepengurusan" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Kepengurusan</option>
                        <?php foreach ($kepengurusanList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">NEGARA ASAL</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-globe text-secondary"></i></span>
                    <select id="flt_negara" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Negara</option>
                        <?php foreach ($negaraList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">KEWARGANEGARAAN</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-flag text-secondary"></i></span>
                    <select id="flt_kewarganegaraan" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Kewarganegaraan</option>
                        <?php foreach ($kewarganegaraanList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">RAYON</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-geo-alt text-secondary"></i></span>
                    <select id="flt_rayon" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Rayon</option>
                        <?php foreach ($rayonList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">KELAS</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-book text-secondary"></i></span>
                    <select id="flt_kelas" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">EXP ITAS</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-calendar-event text-secondary"></i></span>
                    <select id="flt_exp_itas" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Bulan</option>
                        <?php foreach ($expItasList as $v): ?>
                            <option value="<?= htmlspecialchars((string)$v) ?>"><?= date('F Y', strtotime($v . '-01')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small mb-1" style="font-size: .75rem; letter-spacing: .5px;">EXP PASPOR</label>
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-2"><i class="bi bi-calendar2-check text-secondary"></i></span>
                    <select id="flt_exp_paspor" class="form-select border-0 shadow-none bg-white dt-filter py-2" style="font-size: .8rem;">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($expPasporList as $v): ?>
                            <option value="<?= htmlspecialchars((string)$v) ?>"><?= htmlspecialchars((string)$v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="text-end mt-4">
            <button class="btn btn-sm btn-light border shadow-sm rounded-pill px-4 fw-medium text-secondary" onclick="resetFilters()">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
            </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="santriTable" class="table table-hover table-striped align-middle w-100 mb-0" style="font-size:.85rem;">
                <thead class="table-light sticky-top shadow-sm">
                    <tr>
                        <th class="ps-3 text-center" style="width: 30px;"><input type="checkbox" class="form-check-input" id="selectAll" autocomplete="off"></th>
                        <th>No Paspor</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Daerah</th>
                        <th>Rayon</th>
                        <th>Exp Paspor</th>
                        <th>Exp ITAS</th>
                        <th>Lvl</th>
                        <th class="d-none">Kewarganegaraan</th>
                        <th class="d-none">Kepengurusan</th>
                        <th>Pondok</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                    <tr class="table-secondary">
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="1" placeholder="Cari..." style="min-width:90px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="2" placeholder="Cari..." style="min-width:120px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="3" placeholder="Cari..." style="min-width:70px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="4" placeholder="Cari..." style="min-width:90px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="5" placeholder="Cari..." style="min-width:70px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="6" placeholder="Cari..." style="min-width:90px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="7" placeholder="Cari..." style="min-width:90px"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="8" placeholder="Cari..." style="min-width:50px"></th>
                        <th class="d-none"></th>
                        <th class="d-none"></th>
                        <th><input type="text" class="form-control form-control-sm dt-search" data-col="11" placeholder="Cari..." style="min-width:90px"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="santriTableBody">
                <?php if (!empty($santris)): ?>
                <?php $no = 1; foreach ($santris as $s):
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    $expP  = !empty($s['exp_paspor']) && $s['exp_paspor'] !== '0000-00-00' ? new DateTime($s['exp_paspor']) : null;
                    if ($expP) $expP->setTime(0, 0, 0);
                    $expI  = !empty($s['exp_itas']) && $s['exp_itas'] !== '0000-00-00'   ? new DateTime($s['exp_itas'])   : null;
                    if ($expI) $expI->setTime(0, 0, 0);
                    
                    $rowClass = '';
                    $diffP = $expP ? (int) $today->diff($expP)->format('%r%a') : null;
                    $diffI = $expI ? (int) $today->diff($expI)->format('%r%a') : null;
                    
                    if (($diffP !== null && $diffP <= 0) || ($diffI !== null && $diffI <= 0)) {
                        $rowClass = 'bg-danger bg-opacity-25';
                    }
                    
                    $jobDesks = $activeJobDeskKdsSet[$s['kds']] ?? [];
                    $isOnJobDesk = count($jobDesks) > 0;
                    
                    $myKep = $_SESSION['def_kepengurusan'] ?? '';
                    $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                    $isPindahan = ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0);
                ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input row-cb" autocomplete="off"
                                value="<?= $s['kds'] ?>"
                                data-nama="<?= htmlspecialchars((string)($s['nama'] ?? '')) ?>"
                                <?php if ($isOnJobDesk): ?>
                                data-jobdesks="<?= htmlspecialchars(json_encode($jobDesks)) ?>"
                                <?php endif; ?>
                                <?= $isPindahan ? 'disabled title="Santri pindahan tidak dapat diproses"' : '' ?>
                            >
                        </td>
                        <td><code class="text-primary"><?= htmlspecialchars((string)($s['no_paspor'] ?? '-')) ?></code></td>
                        <td class="fw-semibold">
                            <?= htmlspecialchars((string)($s['nama'] ?? '-')) ?>

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
                        <td><span class="badge bg-secondary"><?= htmlspecialchars((string)($s['kelas'] ?? '-')) ?></span></td>
                        <td><?= htmlspecialchars((string)($s['negara'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string)($s['rayon'] ?? '-')) ?></td>
                        <td data-sort="<?= htmlspecialchars((!empty($s['exp_paspor']) && $s['exp_paspor'] !== '0000-00-00') ? $s['exp_paspor'] : '0000-00-00') ?>">
                            <?php if ($expP): ?>
                                <?php if ($diffP <= 0): ?>
                                    <span class="text-danger fw-bold"><?= date('d-M-Y', strtotime($s['exp_paspor'])) ?></span>
                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                <?php elseif ($diffP <= 540): ?>
                                    <span class="text-warning fw-bold"><?= date('d-M-Y', strtotime($s['exp_paspor'])) ?></span>
                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $diffP > 90 ? floor($diffP / 30) . ' bln lagi' : $diffP . ' hari lagi' ?></span>
                                <?php else: ?>
                                    <?= date('d-M-Y', strtotime($s['exp_paspor'])) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td data-sort="<?= htmlspecialchars((!empty($s['exp_itas']) && $s['exp_itas'] !== '0000-00-00') ? $s['exp_itas'] : '0000-00-00') ?>">
                            <?php if ($expI): ?>
                                <?php if ($diffI <= 0): ?>
                                    <span class="text-danger fw-bold"><?= date('d-M-Y', strtotime($s['exp_itas'])) ?></span>
                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                <?php elseif ($diffI <= 90): ?>
                                    <span class="text-warning fw-bold"><?= date('d-M-Y', strtotime($s['exp_itas'])) ?></span>
                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $diffI > 90 ? floor($diffI / 30) . ' bln lagi' : $diffI . ' hari lagi' ?></span>
                                <?php else: ?>
                                    <?= date('d-M-Y', strtotime($s['exp_itas'])) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string)($s['lvl'] ?? '-')) ?></td>
                        <td class="d-none"><?= htmlspecialchars((string)($s['kewarganegaraan'] ?? '-')) ?></td>
                        <td class="d-none"><?= htmlspecialchars((string)($s['kepengurusan'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string)($s['pondok'] ?? '-')) ?></td>
                        <td class="text-center text-nowrap">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-info py-0 px-2" title="Lihat & Edit" onclick="editSantri(<?= $s['kds'] ?>)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Nonaktifkan" onclick="deleteSantri(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<!-- ===== MODAL TAMBAH/EDIT SANTRI ===== -->
<div class="modal fade" id="santriModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-mobile-fullscreen" style="max-width:1100px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header py-3 px-4 bg-light border-0">
        <h5 class="modal-title fw-bold text-dark" id="modalTitle">Personality</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-medium" id="btnSimpan" onclick="simpanSantri()">Simpan</button>
            <button type="button" class="btn btn-light border rounded-pill px-4 shadow-sm fw-medium text-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
      <div class="modal-body bg-light" style="font-size: 0.85rem;">
        <input type="hidden" id="santriKds" value="">
        <input type="hidden" id="f_hapus_foto" value="0">
        <div class="row g-2">
            <!-- KOLOM KIRI: Biodata Lengkap -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3"><h6 class="mb-0 small fw-bold text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i>Biodata Lengkap</h6></div>
                    <div class="card-body py-3 px-4">
                        <div class="row g-2 mb-3">
                            <div class="col-12"><label class="text-muted small fw-bold mb-1">Kode Data Santri (KDS)</label><input type="text" id="f_kds_display" class="form-control form-control-sm bg-light border border-secondary-subtle text-secondary" readonly></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Regno (Stambuk)</label><input type="number" id="f_stambuk" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">NIK (SKTT)</label><input type="text" id="f_nik" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Nama Lengkap</label><input type="text" id="f_nama" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Kelas</label><input type="text" id="f_kelas" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Rayon</label><input type="text" id="f_rayon" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Negara Asal</label><input type="text" id="f_negara" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Tempat Lahir</label><input type="text" id="f_tempat_lahir" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Tanggal Lahir</label><input type="date" id="f_tanggal_lahir" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Alamat</label><textarea id="f_alamat" class="form-control form-control-sm bg-white border border-secondary-subtle" rows="3"></textarea></div>
                    </div>
                </div>
            </div>

            <!-- KOLOM TENGAH: Paspor & Tabs -->
            <div class="col-md-5">
                <!-- Tentang Paspor -->
                <div class="card border-0 shadow-sm rounded-4 mb-2 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3"><h6 class="mb-0 small fw-bold text-dark"><i class="bi bi-passport text-warning me-2"></i>Tentang Paspor</h6></div>
                    <div class="card-body py-3 px-4">
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No Paspor Baru</label><input type="text" id="f_no_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No Paspor Lama</label><input type="text" id="f_no_paspor_lama" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly placeholder="(Otomatis Riwayat)"></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-5"><label class="text-muted small fw-bold mb-1">Exp Paspor</label><input type="date" id="f_exp_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-5"><label class="text-muted small fw-bold mb-1">Exp ITAS</label><input type="date" id="f_exp_itas" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-2"><label class="text-muted small fw-bold mb-1">Lvl ITAS</label><input type="number" id="f_level_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" min="0"></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No ITAS</label><input type="text" id="f_no_itas" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No IC Santri</label><input type="text" id="f_no_ic" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="row g-2 mb-2 border-top pt-3 mt-3">
                            <div class="col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="text-muted small fw-bold mb-0">File Paspor <a href="#" id="view_file_paspor" target="_blank" class="ms-1 small d-none" title="Lihat"><i class="bi bi-eye"></i></a></label>
                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 fw-medium shadow-sm" style="font-size: 0.65rem; border-radius: 4px;" onclick="scanMRZPaspor()" title="Ekstrak data dari gambar">
                                        <i class="bi bi-upc-scan me-1"></i>Scan Auto-Fill
                                    </button>
                                </div>
                                <input type="file" id="f_file_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold mb-1" style="height: 24px;">File ITAS <a href="#" id="view_file_itas" target="_blank" class="ms-1 small d-none" title="Lihat"><i class="bi bi-eye"></i></a></label>
                                <input type="file" id="f_file_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs Detail -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="min-height: 180px;">
                    <div class="card-header bg-white p-0 border-bottom-0 pt-2 px-2">
                        <ul class="nav nav-tabs nav-tabs-sm px-2 pt-1" style="font-size:0.8rem;" id="detailTabs">
                            <li class="nav-item"><a class="nav-link active px-2 py-1" data-bs-toggle="tab" href="#t_paspor">Detail Paspor</a></li>
                            <li class="nav-item"><a class="nav-link px-2 py-1" data-bs-toggle="tab" href="#t_ortu">Orang Tua</a></li>
                            <li class="nav-item"><a class="nav-link px-2 py-1" data-bs-toggle="tab" href="#t_telp">No Telp</a></li>
                            <li class="nav-item"><a class="nav-link px-2 py-1" data-bs-toggle="tab" href="#t_barang">Barang Bawaan</a></li>
                            <li class="nav-item"><a class="nav-link px-2 py-1" data-bs-toggle="tab" href="#t_rpaspor">R.Paspor</a></li>
                            <li class="nav-item"><a class="nav-link px-2 py-1" data-bs-toggle="tab" href="#t_ritas">R.ITAS</a></li>
                            <li class="nav-item"><a class="nav-link px-2 py-1 text-primary fw-medium" data-bs-toggle="tab" href="#t_berkas"><i class="bi bi-folder-check me-1"></i>Berkas</a></li>
                        </ul>
                    </div>
                    <div class="card-body py-3 px-4 tab-content">
                        <div class="tab-pane fade show active" id="t_paspor">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">Tempat Dikeluarkan</label><input type="text" id="f_tempat_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div><label class="text-muted small fw-bold mb-1">Tanggal Dikeluarkan</label><input type="date" id="f_tgl_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="tab-pane fade" id="t_ortu">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">Nama Ayah</label><input type="text" id="f_nama_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div><label class="text-muted small fw-bold mb-1">Nama Ibu</label><input type="text" id="f_nama_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="tab-pane fade" id="t_telp">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No. HP Ayah</label><input type="number" id="f_no_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No. HP Ibu</label><input type="number" id="f_no_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div><label class="text-muted small fw-bold mb-1">No. HP Alternatif</label><input type="number" id="f_no_hp_alternatif" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        </div>
                        <div class="tab-pane fade" id="t_barang">
                            <div class="d-flex gap-2 mb-3">
                                <input type="text" id="b_nama" class="form-control form-control-sm bg-white border border-secondary-subtle" placeholder="Nama barang">
                                <input type="number" id="b_jumlah" class="form-control form-control-sm bg-white border border-secondary-subtle w-25" placeholder="Jml" value="1">
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="addBarang()"><i class="bi bi-plus-lg"></i></button>
                            </div>
                            <div class="table-responsive" style="max-height: 100px;">
                                <table class="table table-sm table-bordered m-0">
                                    <tbody id="listBarang"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_rpaspor">
                            <div class="table-responsive" style="max-height: 130px;">
                                <table class="table table-sm table-bordered m-0"><thead class="table-light"><tr><th>No Paspor</th><th>Exp</th><th>Dok</th><th>Aksi</th></tr></thead><tbody id="listRPaspor"></tbody></table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_ritas">
                            <div class="table-responsive" style="max-height: 130px;">
                                <table class="table table-sm table-bordered m-0"><thead class="table-light"><tr><th>No ITAS</th><th>Lvl</th><th>Exp</th><th>Dok</th><th>Aksi</th></tr></thead><tbody id="listRITAS"></tbody></table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="t_berkas">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Daftar Berkas:</span>
                                <button type="button" id="btnUnggahBerkas" class="btn btn-sm btn-primary py-0" onclick="document.getElementById('fileUploadBerkas').click()"><i class="bi bi-upload"></i> Unggah</button>
                                <input type="file" id="fileUploadBerkas" class="d-none" onchange="uploadBerkas(this)">
                            </div>
                            <div class="table-responsive" style="max-height: 100px;">
                                <table class="table table-sm table-bordered m-0"><thead class="table-light"><tr><th>Nama File Berkas</th><th class="text-center" style="width:100px;">Aksi</th></tr></thead><tbody id="listBerkas"></tbody></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Foto & Status -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body py-3 px-3 d-flex flex-column">
                        <div id="fotoContainer" class="border rounded-4 mb-3 flex-grow-1 d-flex align-items-center justify-content-center bg-light overflow-hidden shadow-sm position-relative" style="min-height: 160px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s;" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)" onclick="if(document.getElementById('fotoActions').classList.contains('d-flex')) document.getElementById('f_foto_santri').click()" style="cursor: pointer;">
                            <img id="imgPreview" src="" style="max-width:100%; max-height:160px; display:none;">
                            <i id="imgIcon" class="bi bi-person text-secondary" style="font-size: 5rem;"></i>
                            <div id="dragOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                                <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan Foto</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mb-3" id="fotoActions">
                            <button class="btn btn-sm btn-dark w-50 py-1 rounded-pill shadow-sm fw-medium" onclick="document.getElementById('f_foto_santri').click()">Tmbh Foto</button>
                            <button class="btn btn-sm btn-outline-danger w-50 py-1 rounded-pill shadow-sm fw-medium" onclick="hapusFoto()">Hapus Foto</button>
                            <input type="file" id="f_foto_santri" class="d-none" accept="image/*" onchange="previewFoto(this)">
                        </div>
                        
                        <div class="row g-2 mb-3 mt-1">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Status</label><select id="f_aktif" class="form-select form-select-sm bg-white border border-secondary-subtle"><option value="1">Aktif</option><option value="0">Tidak Aktif</option></select></div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold mb-1">Pondok</label>
                                <input type="text" id="f_pondok" list="listModalPondok" class="form-control form-control-sm bg-white border border-secondary-subtle" placeholder="Pilih / Ketik...">
                                <datalist id="listModalPondok">
                                    <?php foreach ($pondokList as $v): ?>
                                        <option value="<?= htmlspecialchars($v) ?>"></option>
                                    <?php endforeach; ?>
                                    <option value="G1"></option>
                                    <option value="G2"></option>
                                    <option value="G3"></option>
                                    <option value="G4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Keberadaan Paspor</label><input type="text" id="f_keberadaan_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Ukuran Baju</label><input type="text" id="f_ukuran_baju" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Jenis Kelamin</label><select id="f_jenis_kelamin" class="form-select form-select-sm bg-white border border-secondary-subtle"><option value="Laki-laki">L</option><option value="Perempuan">P</option></select></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Kepengurusan</label><input type="text" id="f_kepengurusan" class="form-control form-control-sm bg-white border border-secondary-subtle"></div>
                        
                        <!-- Kewarganegaraan (Auto/Manual) -->
                        <div class="mt-auto"><label class="text-muted small fw-bold mb-1">Kewarganegaraan</label><input type="text" id="f_kewarganegaraan" class="form-control form-control-sm bg-white border border-secondary-subtle" title="Bisa diedit manual"></div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- Toast Notification -->
<!-- Confirm Document Modal -->
<div class="modal fade" id="confirmDocModal" tabindex="-1" data-bs-backdrop="false" style="background-color: rgba(0,0,0,0.2); backdrop-filter: blur(2px);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-question-circle text-warning" style="font-size: 4rem;"></i>
                </div>
                <h5 class="fw-bold mb-3">Konfirmasi Perubahan Dokumen</h5>
                <p class="text-muted mb-4">
                    Sistem mendeteksi adanya perubahan pada <strong id="confirmDocType">Paspor/ITAS</strong>.
                    <br><br>
                    Apakah ini adalah pendaftaran <strong>dokumen baru (perpanjangan)</strong>, atau Anda sekadar <strong>melengkapi/mengoreksi</strong> data yang sebelumnya kosong/salah?
                </p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary rounded-pill shadow-sm fw-medium" id="btnDocBaru">
                        <i class="bi bi-file-earmark-plus me-1"></i> Dokumen Baru (Arsipkan yang lama)
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill shadow-sm fw-medium" id="btnDocKoreksi">
                        <i class="bi bi-pencil-square me-1"></i> Hanya Mengoreksi / Melengkapi Data
                    </button>
                    <button type="button" class="btn btn-link text-muted text-decoration-none mt-2 fw-medium" data-bs-dismiss="modal">Batal Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Histori Modal -->
<div class="modal fade" id="histDetailModal" tabindex="-1" data-bs-backdrop="false" style="background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 px-4 py-3">
                <h6 class="modal-title fw-bold" id="histDetailTitle">Detail Riwayat</h6>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" style="box-shadow: none;"></button>
            </div>
            <div class="modal-body bg-light p-4" id="histDetailBody">
            </div>
            <div class="modal-footer border-0 pt-0 pb-3 px-4 bg-light d-flex justify-content-between align-items-center">
                <span id="histDetailStatus" class="fw-medium text-secondary" style="font-size: .85rem;"></span>
                <button type="button" class="btn btn-light border rounded-pill shadow-sm fw-medium px-4 text-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1090">
    <div id="toastMsg" class="toast align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Modal Histori -->
<div class="modal fade" id="historiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Histori Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="historiContent"></div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Preview Tarik Data -->
<div class="modal fade" id="modalPreviewSync" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-download me-2"></i> Pratinjau Tarik Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i> Silakan pilih data santri yang ingin Anda tarik ke database lokal. Data yang tidak dicentang akan dilewati.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <input class="form-check-input" type="checkbox" id="checkAllSync" onclick="toggleAllSync(this)">
                                </th>
                                <th>Nama Santri</th>
                                <th>Kelas/Pondok</th>
                                <th>WN</th>
                                <th>Paspor</th>
                                <th>ITAS</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewSyncTbody">
                            <!-- Data dimuat via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="prosesTarikDataTerpilih()">Setujui & Tarik Terpilih</button>
            </div>
        </div>
    </div>
</div>    

<!-- Modal Auto-Upload ITAS Massal -->
<div class="modal fade" id="modalAutoItas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-robot text-warning me-2"></i>Auto-Upload ITAS Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info rounded-3 border-0 small mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>Fitur ini akan mengekstrak otomatis nama dari dalam file PDF ITAS, lalu mencocokkannya dengan database Santri untuk mengubah nama file dan menyimpannya secara otomatis.
                </div>
                
                <div id="dropZoneItas" class="border rounded-3 mb-4 d-flex flex-column align-items-center justify-content-center bg-light shadow-sm position-relative" style="min-height: 200px; border-width: 2px !important; border-style: dashed !important; transition: all 0.2s; cursor: pointer;" ondragover="handleDragOverItas(event)" ondragleave="handleDragLeaveItas(event)" ondrop="handleDropItas(event)" onclick="document.getElementById('inputItasMassal').click()">
                    <i id="iconItas" class="bi bi-cloud-arrow-up text-secondary" style="font-size: 3rem;"></i>
                    <h6 class="fw-bold mt-3 mb-1 text-dark">Tarik (Drag & Drop) File PDF ITAS ke Sini</h6>
                    <span class="text-muted small">Atau klik untuk memilih file (Bisa pilih banyak sekaligus)</span>
                    <div id="dragOverlayItas" class="position-absolute top-0 start-0 w-100 h-100 bg-warning bg-opacity-25 d-flex align-items-center justify-content-center d-none" style="z-index: 10; pointer-events: none;">
                        <span class="fw-bold text-warning fs-5"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Lepaskan File</span>
                    </div>
                </div>
                <input type="file" id="inputItasMassal" class="d-none" accept=".pdf" multiple onchange="handleFilesItas(this.files)">
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0">Progress Log</h6>
                    <button class="btn btn-sm btn-light border rounded-pill small" onclick="document.getElementById('logItasBox').innerHTML=''" title="Bersihkan Log"><i class="bi bi-eraser"></i> Bersihkan</button>
                </div>
                <div id="logItasBox" class="bg-dark text-light p-3 rounded-3 font-monospace small overflow-auto" style="height: 250px; font-size: 0.8rem; line-height: 1.5;">
                    <i>Menunggu file...</i>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                <button class="btn btn-light rounded-pill px-4 fw-medium border shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Massal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info bg-opacity-10 border-bottom-0 pb-2">
                <h5 class="modal-title text-info fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Massal Biodata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light pt-3 pb-4">
                <div class="alert alert-info py-2 small border-0 bg-white shadow-sm rounded-3">
                    <i class="bi bi-info-circle-fill text-info me-2"></i>Hanya kolom yang <strong>diisi/dipilih</strong> yang akan mengubah data santri terpilih. Kosongkan jika tidak ingin mengubah.
                </div>
                <form id="bulkEditForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">Kelas</label>
                            <input list="dl_kelas" name="kelas" class="form-control border-0 shadow-sm" placeholder="-- Biarkan (Tidak Diubah) --">
                            <datalist id="dl_kelas">
                                <?php foreach ($kelasList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">Pondok</label>
                            <input list="dl_pondok" name="pondok" class="form-control border-0 shadow-sm" placeholder="-- Biarkan (Tidak Diubah) --">
                            <datalist id="dl_pondok">
                                <?php foreach ($pondokList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">Rayon</label>
                            <input list="dl_rayon" name="rayon" class="form-control border-0 shadow-sm" placeholder="-- Biarkan (Tidak Diubah) --">
                            <datalist id="dl_rayon">
                                <?php foreach ($rayonList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">Kepengurusan</label>
                            <input list="dl_kepengurusan" name="kepengurusan" class="form-control border-0 shadow-sm" placeholder="-- Biarkan (Tidak Diubah) --">
                            <datalist id="dl_kepengurusan">
                                <?php foreach ($kepengurusanList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">Kewarganegaraan</label>
                            <input list="dl_kewarganegaraan" name="kewarganegaraan" class="form-control border-0 shadow-sm" placeholder="-- Biarkan --">
                            <datalist id="dl_kewarganegaraan">
                                <?php foreach ($kewarganegaraanList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">Negara</label>
                            <input list="dl_negara" name="negara" class="form-control border-0 shadow-sm" placeholder="-- Biarkan --">
                            <datalist id="dl_negara">
                                <?php foreach ($negaraList as $v): ?><option value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">
                    
                    <input type="hidden" name="doc_mode" id="bulkDocMode" value="koreksi">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 shadow-sm mb-3">
                                <h6 class="text-primary fw-bold small mb-3 border-bottom pb-2"><i class="bi bi-passport me-2"></i>Data Paspor</h6>
                                <div class="mb-2">
                                    <label class="form-label text-secondary small">Tempat Keluar</label>
                                    <input type="text" name="tempat_paspor" class="form-control form-control-sm" placeholder="Kosongkan jika tetap">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-secondary small">Tgl Keluar</label>
                                    <input type="date" name="tgl_paspor" class="form-control form-control-sm">
                                </div>
                                <div>
                                    <label class="form-label text-secondary small">Exp Paspor</label>
                                    <input type="date" name="exp_paspor" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 shadow-sm mb-3">
                                <h6 class="text-success fw-bold small mb-3 border-bottom pb-2"><i class="bi bi-card-heading me-2"></i>Data ITAS</h6>
                                <div class="mb-2">
                                    <label class="form-label text-secondary small">Level ITAS</label>
                                    <input type="number" name="level_itas" class="form-control form-control-sm" placeholder="Kosongkan jika tetap">
                                </div>
                                <div>
                                    <label class="form-label text-secondary small">Exp ITAS</label>
                                    <input type="date" name="exp_itas" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info text-white rounded-pill px-4 fw-medium shadow-sm" id="btnSubmitBulkEdit" onclick="submitBulkEdit()">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Riwayat Dokumen -->
<div class="modal fade" id="editHistModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom-0 pb-2">
                <h6 class="modal-title text-warning-emphasis fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Riwayat <span id="editHistTitleType"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light pt-3 pb-4">
                <form id="editHistForm">
                    <input type="hidden" id="editHistType" name="type">
                    <input type="hidden" id="editHistId" name="id">
                    
                    <div id="editHistPasporFields" class="d-none">
                        <div class="mb-2">
                            <label class="form-label text-secondary small">No. Paspor</label>
                            <input type="text" name="no_paspor" id="eh_no_paspor" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-secondary small">Tempat Keluar</label>
                            <input type="text" name="tempat_dikeluarkan" id="eh_tempat_paspor" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-secondary small">Tgl Keluar</label>
                            <input type="date" name="tanggal_dikeluarkan" id="eh_tgl_paspor" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-secondary small">Exp Paspor</label>
                            <input type="date" name="exp_paspor" id="eh_exp_paspor" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div id="editHistItasFields" class="d-none">
                        <div class="mb-2">
                            <label class="form-label text-secondary small">No. ITAS</label>
                            <input type="text" name="no_itas" id="eh_no_itas" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-secondary small">Level ITAS</label>
                            <input type="number" name="level_itas" id="eh_level_itas" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-secondary small">Exp ITAS</label>
                            <input type="date" name="exp_itas" id="eh_exp_itas" class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0 bg-light">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 fw-medium shadow-sm" id="btnSimpanEditHist" onclick="simpanEditHist()">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const instansiDefaults = <?= json_encode($instansiDefaults ?? []) ?>;

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const d = new Date(dateString);
    if (isNaN(d)) return dateString;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return ('0' + d.getDate()).slice(-2) + '-' + months[d.getMonth()] + '-' + d.getFullYear();
};
function showHistDetail(type, item) {
    const title = type === 'paspor' ? 'Detail Paspor Lama' : 'Detail ITAS Lama';
    document.getElementById('histDetailTitle').innerText = title;
    
    // Get santri details from form
    const nama = document.getElementById('f_nama').value || '-';
    const negara = document.getElementById('f_negara').value || 'INDONESIA';
    const kewarganegaraan = document.getElementById('f_kewarganegaraan').value || negara;
    const nik = document.getElementById('f_nik').value || document.getElementById('f_no_ic').value || '-';
    const tglLahir = document.getElementById('f_tanggal_lahir').value || '-';
    const tempatLahir = document.getElementById('f_tempat_lahir').value || '-';
    const jk = document.getElementById('f_jenis_kelamin').value || '-';
    const imgPreview = document.getElementById('imgPreview');
    const fotoUrl = imgPreview.src;
    const isHidden = imgPreview.style.display === 'none' || imgPreview.classList.contains('d-none');
    const hasFoto = !isHidden && fotoUrl && fotoUrl.includes('/photo');

    const photoHtml = hasFoto ? 
        `<img src="${fotoUrl}" class="img-fluid rounded border shadow-sm" style="height: 150px; width: 100%; object-fit: cover;">` :
        `<div class="border rounded shadow-sm d-flex align-items-center justify-content-center bg-light" style="height: 150px; width: 100%;">
            <i class="bi bi-person text-secondary" style="font-size: 5rem;"></i>
         </div>`;

    let html = '';
    const getDocHtml = (type, item) => `
        <div class="border rounded p-1 text-center mt-2" style="background: rgba(255,255,255,0.7);">
            <small class="d-block text-secondary mb-1" style="font-size: 0.65rem;">Dokumen ${type === 'paspor' ? 'Paspor' : 'ITAS'}</small>
            ${item.path_file ? `
                <a href="<?= API_URL ?>/api/santri/doc/${type}/${item.id}" target="_blank" class="d-block mb-1" title="Lihat Dokumen">
                    <i class="bi bi-file-earmark-check-fill text-success" style="font-size: 1.8rem;"></i>
                </a>
                <label class="btn btn-outline-primary btn-sm py-0 px-2 mt-1" style="font-size: 0.65rem; cursor: pointer;">
                    Ganti File <input type="file" hidden accept=".pdf,.jpg,.jpeg,.png" onchange="uploadHistDoc('${type}', ${item.id}, this)">
                </label>
            ` : `
                <i class="bi bi-file-earmark-x text-danger mb-1 d-block" style="font-size: 1.8rem;"></i>
                <label class="btn btn-primary btn-sm py-0 px-2 mt-1" style="font-size: 0.65rem; cursor: pointer;">
                    Upload File <input type="file" hidden accept=".pdf,.jpg,.jpeg,.png" onchange="uploadHistDoc('${type}', ${item.id}, this)">
                </label>
            `}
        </div>
    `;



    if (type === 'paspor') {
        html = `
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-0" style="background: linear-gradient(135deg, #f0f8ff 0%, #e6f2ff 100%);">
            <div class="card-header bg-transparent border-bottom border-info border-opacity-25 d-flex justify-content-between align-items-center px-4 py-3">
                <span class="fs-4 fw-bolder text-info text-uppercase" style="letter-spacing: 2px; text-shadow: 1px 1px 0px #fff;">${negara}</span>
                <div class="text-end" style="line-height: 1.2;">
                    <small class="d-block text-secondary" style="font-size: 0.65rem;">No. Paspor / Passport No.</small>
                    <strong class="fs-5 text-dark">${item.no_paspor || '-'}</strong>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-4 text-center">
                        ${photoHtml}
                        ${getDocHtml('paspor', item)}
                    </div>
                    <div class="col-8">
                        <div class="mb-2">
                            <small class="d-block text-secondary" style="font-size: 0.65rem;">Nama / Name</small>
                            <strong class="d-block text-uppercase text-dark" style="font-size: 0.85rem;">${nama}</strong>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Warganegara / Nationality</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${kewarganegaraan}</strong>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">No. Pengenalan / Identity No.</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${nik}</strong>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tgl Lahir / Date of Birth</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${tglLahir}</strong>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tempat Lahir / Place of Birth</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${tempatLahir}</strong>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tgl Dikeluarkan / Date of Issue</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${item.tanggal_dikeluarkan || '-'}</strong>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tgl Expired / Date of Expiry</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${item.exp_paspor || '-'}</strong>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Jenis Kelamin / Sex</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${jk === 'L' ? 'Laki-Laki / M' : (jk === 'P' ? 'Perempuan / F' : '-')}</strong>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tempat Keluaran / Issuing Office</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${item.tempat_dikeluarkan || '-'}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    } else {
        html = `
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-0" style="background: linear-gradient(135deg, #fffdf0 0%, #fff8cc 100%);">
            <div class="card-header bg-transparent border-bottom border-warning border-opacity-25 d-flex justify-content-between align-items-center px-4 py-3">
                <span class="fs-4 fw-bolder text-warning text-uppercase" style="letter-spacing: 2px;">ITAS</span>
                <div class="text-end" style="line-height: 1.2;">
                    <small class="d-block text-secondary" style="font-size: 0.65rem;">Berlaku Hingga / Date of Expiry</small>
                    <strong class="fs-5 text-dark">${formatDate(item.exp_itas)}</strong>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-4 text-center">
                        ${photoHtml}
                        ${getDocHtml('itas', item)}
                    </div>
                    <div class="col-8">
                        <div class="mb-2">
                            <small class="d-block text-secondary" style="font-size: 0.65rem;">No. ITAS / ITAS No.</small>
                            <strong class="d-block text-uppercase text-dark" style="font-size: 0.85rem;">${item.no_itas || '-'}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="d-block text-secondary" style="font-size: 0.65rem;">Nama / Name</small>
                            <strong class="d-block text-uppercase text-dark" style="font-size: 0.85rem;">${nama}</strong>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Warganegara / Nationality</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${kewarganegaraan}</strong>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Negara Asal / Country of Origin</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${negara}</strong>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tgl Lahir / Date of Birth</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${tglLahir}</strong>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Tempat Lahir / Place of Birth</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${tempatLahir}</strong>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-12">
                                <small class="d-block text-secondary" style="font-size: 0.65rem;">Level ITAS / ITAS Level</small>
                                <strong class="d-block text-uppercase text-dark" style="font-size: 0.8rem;">${item.level_itas || '-'}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }

    document.getElementById('histDetailBody').innerHTML = html;
    document.getElementById('histDetailStatus').innerHTML = `Status Aktif: <span class="badge bg-${item.aktif == 1 ? 'success' : 'secondary'}">${item.aktif == 1 ? 'Ya' : 'Tidak'}</span>`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('histDetailModal')).show();
}

function uploadHistDoc(type, id, input) {
    if (!input.files || !input.files[0]) return;
    const formData = new FormData();
    formData.append('file', input.files[0]);
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const btnLabel = input.parentElement;
    const origHtml = btnLabel.innerHTML;
    btnLabel.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btnLabel.style.pointerEvents = 'none';

    fetch(`<?= API_URL ?>/api/santri/doc/${type}/${id}/upload`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Dokumen berhasil diunggah!');
            bootstrap.Modal.getInstance(document.getElementById('histDetailModal')).hide();
            editSantri(currentKds); // reload data
        } else {
            showToast(data.message || 'Gagal mengunggah dokumen', false);
            btnLabel.innerHTML = origHtml;
            btnLabel.style.pointerEvents = 'auto';
        }
    })
    .catch(e => {
        showToast('Terjadi kesalahan', false);
        btnLabel.innerHTML = origHtml;
        btnLabel.style.pointerEvents = 'auto';
    });
}

function tutupSelesaiAll() {
    Swal.close();
}

/** 
 * PASSPORT OCR MRZ SCANNER 
 */
async function scanMRZPaspor() {
    const fileInput = document.getElementById('f_file_paspor');
    const file = fileInput.files[0];
    
    if (!file) {
        Swal.fire('Peringatan', 'Silakan Pilih/Unggah File Paspor di kotak input terlebih dahulu sebelum menekan tombol Scan.', 'warning');
        return;
    }
    
    if (file.type === 'application/pdf') {
        Swal.fire('Format Tidak Didukung', 'Fitur scan otomatis AI saat ini hanya mendukung file gambar (JPG/JPEG/PNG). Anda mengunggah PDF.', 'info');
        return;
    }

    Swal.fire({
        title: 'Memindai Paspor...',
        html: '<div class="text-muted small mb-3">AI sedang mengekstrak teks (OCR) dari gambar. Proses ini memakan waktu 3-5 detik tergantung perangkat Anda.</div><div class="progress"><div id="scanProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        if (typeof Tesseract === 'undefined') {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = '<?= ASSET_URL ?>/assets/offline/js/tesseract.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        const worker = await Tesseract.createWorker('eng', 1, {
            logger: m => {
                if (m.status === 'recognizing text') {
                    const pct = Math.round(m.progress * 100);
                    const bar = document.getElementById('scanProgressBar');
                    if (bar) bar.style.width = pct + '%';
                }
            }
        });

        const ret = await worker.recognize(file);
        const text = ret.data.text;
        await worker.terminate();

        const lines = text.split('\n').map(l => l.replace(/\s/g, '').toUpperCase());
        
        let mrzLine1 = -1;
        let mrzLine2 = -1;
        
        for (let i = 0; i < lines.length - 1; i++) {
            const l1 = lines[i];
            const l2 = lines[i+1];
            
            // Typical MRZ length is 44, but OCR might miss a character. We check >= 40.
            // Starts with P (Passport).
            if (l1.length >= 40 && l2.length >= 40 && l1.startsWith('P')) {
                mrzLine1 = i;
                mrzLine2 = i + 1;
                break;
            }
        }

        if (mrzLine1 === -1) {
             Swal.fire('Gagal Membaca', 'Sistem tidak menemukan Zona MRZ pada gambar. Pastikan dua baris kode di bagian paling bawah paspor terlihat jelas, terang, dan tidak terpotong.', 'error');
             return;
        }

        let l2 = lines[mrzLine2];
        
        // Fix very common OCR errors in MRZ
        // MRZ Line 2 format: [PassNo 9 chars][1 char CD][Nationality 3 chars][DOB 6 chars][1 char CD][Sex 1 char][Exp 6 chars]...
        
        let passportNo = l2.substring(0, 9).replace(/</g, '').replace(/O/g, '0');
        let dobStr = l2.substring(13, 19).replace(/O/g, '0').replace(/I/g, '1').replace(/S/g, '5');
        let sexStr = l2.substring(20, 21);
        let expStr = l2.substring(21, 27).replace(/O/g, '0').replace(/I/g, '1').replace(/S/g, '5');

        let dobYear = parseInt(dobStr.substring(0, 2));
        let currentYear = new Date().getFullYear() % 100;
        dobYear = (dobYear > currentYear) ? (1900 + dobYear) : (2000 + dobYear);
        let dobMonth = dobStr.substring(2, 4);
        let dobDay = dobStr.substring(4, 6);
        let dob = `${dobYear}-${dobMonth}-${dobDay}`;

        let expYear = parseInt(expStr.substring(0, 2));
        expYear = 2000 + expYear;
        let expMonth = expStr.substring(2, 4);
        let expDay = expStr.substring(4, 6);
        let exp = `${expYear}-${expMonth}-${expDay}`;

        let filled = [];

        if (passportNo && passportNo.length >= 5) {
            document.getElementById('f_no_paspor').value = passportNo;
            filled.push('No Paspor');
        }
        if (!isNaN(dobYear) && dobMonth <= 12 && dobDay <= 31) {
            document.getElementById('f_tanggal_lahir').value = dob;
            filled.push('Tgl Lahir');
        }
        if (!isNaN(expYear) && expMonth <= 12 && expDay <= 31) {
            document.getElementById('f_exp_paspor').value = exp;
            filled.push('Exp Paspor');
        }
        if (sexStr === 'M') {
            document.getElementById('f_jenis_kelamin').value = 'Laki-laki';
            filled.push('Jenis Kelamin');
        } else if (sexStr === 'F') {
            document.getElementById('f_jenis_kelamin').value = 'Perempuan';
            filled.push('Jenis Kelamin');
        }

        if (filled.length > 0) {
            Swal.fire({
                title: 'Scan Berhasil!',
                html: `<div class="text-success fw-bold mb-2">Berhasil auto-fill: ${filled.join(', ')}</div><div class="small text-muted">Mohon selalu periksa ulang kotak input untuk memastikan tidak ada kesalahan pembacaan (typo) dari AI.</div>`,
                icon: 'success',
                confirmButtonText: 'Oke, Saya Cek'
            });
        } else {
            Swal.fire('Hasil Kosong', 'Format MRZ ditemukan namun gagal mengekstrak data spesifik. Kualitas gambar mungkin kurang optimal.', 'warning');
        }

    } catch (e) {
        console.error(e);
        Swal.fire('Terjadi Kesalahan', 'Gagal menjalankan pemindaian AI: ' + e.message, 'error');
    }
}

function editHist(type, item) {
    document.getElementById('editHistType').value = type;
    document.getElementById('editHistId').value = item.id;
    document.getElementById('editHistTitleType').innerText = type === 'paspor' ? 'Paspor' : 'ITAS';
    
    if (type === 'paspor') {
        document.getElementById('editHistPasporFields').classList.remove('d-none');
        document.getElementById('editHistItasFields').classList.add('d-none');
        document.getElementById('eh_no_paspor').value = item.no_paspor || '';
        document.getElementById('eh_tempat_paspor').value = item.tempat_dikeluarkan || '';
        document.getElementById('eh_tgl_paspor').value = item.tanggal_dikeluarkan || '';
        document.getElementById('eh_exp_paspor').value = item.exp_paspor || '';
    } else {
        document.getElementById('editHistPasporFields').classList.add('d-none');
        document.getElementById('editHistItasFields').classList.remove('d-none');
        document.getElementById('eh_no_itas').value = item.no_itas || '';
        document.getElementById('eh_level_itas').value = item.level_itas || '';
        document.getElementById('eh_exp_itas').value = item.exp_itas || '';
    }
    
    const modalEl = document.getElementById('editHistModal');
    new bootstrap.Modal(modalEl).show();
    
    setTimeout(() => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops[backdrops.length - 1].style.zIndex = '1059';
        }
    }, 150);
}

function simpanEditHist() {
    const type = document.getElementById('editHistType').value;
    const id = document.getElementById('editHistId').value;
    const form = document.getElementById('editHistForm');
    const formData = new FormData(form);
    
    const data = {};
    formData.forEach((val, key) => { if (val) data[key] = val; });
    
    const btn = document.getElementById('btnSimpanEditHist');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch(`<?= API_URL ?>/api/santri/doc/${type}/${id}/update`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf, 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showToast(res.message, res.success);
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('editHistModal')).hide();
            if (currentKds) editSantri(currentKds);
        }
    })
    .catch(() => showToast('Gagal menyimpan', false))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan';
    });
}

function deleteHist(type, id) {
    Swal.fire({
        title: 'Hapus Riwayat?',
        text: 'Yakin ingin menghapus riwayat ini? Data file juga akan terhapus jika ada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= API_URL ?>/api/santri/doc/' + type + '/' + id + '/delete', {
                method: 'DELETE',
                headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content }
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success);
                if (data.success && currentKds) {
                    editSantri(currentKds);
                }
            })
            .catch(() => showToast('Gagal menghapus', false));
        }
    });
}
const BASE_URL = '';
let modalMode = 'add';
let currentKds = null;
let origValues = {};
let isPindahanActive = false;

function showToast(msg, ok = true) {
    const t = document.getElementById('toastMsg');
    t.className = 'toast align-items-center text-white border-0 ' + (ok ? 'bg-success' : 'bg-danger');
    document.getElementById('toastBody').textContent = msg;
    new bootstrap.Toast(t, {delay: 3000}).show();
}

function openModal(mode) {
    modalMode = mode;
    document.getElementById('modalTitle').innerHTML = mode === 'add'
        ? '<i class="bi bi-person-plus me-2"></i>Tambah Santri Baru'
        : '<i class="bi bi-pencil-square me-2"></i>Edit Data Santri';
    document.getElementById('btnSimpan').style.display = 'inline-block';
    
    if (mode === 'add') {
        document.getElementById('santriKds').value = '';
         ['stambuk','nama','kelas','rayon','negara',
         'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
         'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
         'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
             const el = document.getElementById('f_' + id);
             if(el) el.value = '';
         });
         
         // Set default values from instansi
         document.getElementById('f_kepengurusan').value = instansiDefaults.def_kepengurusan || '';
         document.getElementById('f_pondok').value = instansiDefaults.def_pondok || 'G1';
         document.getElementById('f_jenis_kelamin').value = instansiDefaults.def_jenis_kelamin || 'Laki-laki';
         
         document.getElementById('f_aktif').value = '1';
         hapusFoto();
         document.getElementById('listRPaspor').innerHTML = '';
         document.getElementById('listRITAS').innerHTML = '';
         barangList = [];
         renderBarang();
         
         document.getElementById('f_file_paspor').value = '';
         document.getElementById('f_file_itas').value = '';
         document.getElementById('view_file_paspor').classList.add('d-none');
         document.getElementById('view_file_itas').classList.add('d-none');
         
         const firstTab = document.querySelector('#t_paspor');
         if(firstTab) {
             const tab = new bootstrap.Tab(document.querySelector('a[href="#t_paspor"]'));
             tab.show();
         }
    }
    const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('santriModal'));
    myModal.show();
}

let barangList = [];
function addBarang() {
    const nama = document.getElementById('b_nama').value.trim();
    const jml = document.getElementById('b_jumlah').value || 1;
    if (nama) {
        barangList.push({nama, jumlah: jml});
        document.getElementById('b_nama').value = '';
        document.getElementById('b_jumlah').value = '1';
        renderBarang();
    }
}
function hapusBarang(idx) {
    barangList.splice(idx, 1);
    renderBarang();
}
function renderBarang() {
    const html = barangList.map((b, i) => `<tr>
        <td class="px-2 align-middle">${b.nama}</td>
        <td class="text-center align-middle" style="width:40px;">${b.jumlah}</td>
        <td class="text-center align-middle" style="width:40px;"><button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="hapusBarang(${i})"><i class="bi bi-x"></i></button></td>
    </tr>`).join('');
    document.getElementById('listBarang').innerHTML = html || '<tr><td colspan="3" class="text-center text-muted small">Belum ada barang</td></tr>';
}

function previewFoto(input) {
    if (input.files && input.files[0]) {
        document.getElementById('f_hapus_foto').value = '0';
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreview').style.display = 'block';
            document.getElementById('imgIcon').style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function handleDragOver(e) {
    e.preventDefault();
    if(document.getElementById('fotoActions').classList.contains('d-flex')) {
        document.getElementById('fotoContainer').classList.replace('border-light', 'border-primary');
        document.getElementById('dragOverlay').classList.remove('d-none');
    }
}

function handleDragLeave(e) {
    e.preventDefault();
    document.getElementById('fotoContainer').classList.replace('border-primary', 'border-light');
    document.getElementById('dragOverlay').classList.add('d-none');
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('fotoContainer').classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
    document.getElementById('dragOverlay').classList.add('d-none');
    if(e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        document.getElementById('f_foto_santri').files = e.dataTransfer.files;
        previewFoto(document.getElementById('f_foto_santri'));
    }
}

function uploadBerkas(input) {
    if (!input.files || input.files.length === 0) return;
    if (!currentKds) {
        showToast('Pilih santri terlebih dahulu', false);
        return;
    }
    const file = input.files[0];
    
    Swal.fire({
        title: 'Konfirmasi Nama Berkas',
        input: 'text',
        inputValue: file.name,
        target: document.querySelector('.modal.show') || document.body,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-upload"></i> Unggah',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) return 'Nama berkas tidak boleh kosong!';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('custom_name', result.value);
            
            showToast('Mengunggah berkas...', true);
            fetch('<?= API_URL ?>/api/santri/berkas/' + currentKds + '/upload', {
                method: 'POST',
                headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success);
                if (data.success) {
                    editSantri(currentKds);
                }
            })
            .catch(() => showToast('Gagal mengunggah berkas', false))
            .finally(() => {
                input.value = '';
            });
        } else {
            input.value = '';
        }
    });
}

function renameBerkas(oldName) {
    Swal.fire({
        title: 'Ubah Nama Berkas',
        input: 'text',
        inputValue: oldName,
        target: document.querySelector('.modal.show') || document.body,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Nama file tidak boleh kosong!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= API_URL ?>/api/santri/berkas/' + currentKds + '/rename', {
                method: 'POST',
                headers: { 
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ old_name: oldName, new_name: result.value })
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success);
                if (data.success) editSantri(currentKds);
            })
            .catch(() => showToast('Gagal mengubah nama', false));
        }
    });
}

function deleteBerkas(filename) {
    Swal.fire({
        title: 'Hapus Berkas?',
        text: 'Yakin ingin menghapus berkas ' + filename + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= API_URL ?>/api/santri/berkas/' + currentKds + '/delete', {
                method: 'POST',
                headers: { 
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ filename: filename })
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success);
                if (data.success) editSantri(currentKds);
            })
            .catch(() => showToast('Gagal menghapus berkas', false));
        }
    });
}

function hapusFoto() {
    document.getElementById('f_foto_santri').value = '';
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('imgPreview').src = '';
    document.getElementById('imgIcon').style.display = 'block';
    document.getElementById('f_hapus_foto').value = '1';
}

let origPaspor = { no: '', exp: '', tempat: '', tgl: '' };
let origItas = { no: '', exp: '', level: '' };

function editSantri(kds) {
    currentKds = kds;
    fetch(BASE_URL + '<?= API_URL ?>/api/santri/' + kds)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showToast('Gagal memuat data', false); return; }
            const s = data.santri, p = data.paspor, i = data.itas, pl = data.paspor_lama;
            document.getElementById('santriKds').value = kds;
            const kdsDisplay = document.getElementById('f_kds_display');
            if (kdsDisplay) kdsDisplay.value = kds;
            const set = (id, val) => { const el = document.getElementById('f_' + id); if(el) el.value = val ?? ''; };
            
            document.getElementById('f_file_paspor').value = '';
            document.getElementById('f_file_itas').value = '';
            document.getElementById('view_file_paspor').classList.add('d-none');
            document.getElementById('view_file_itas').classList.add('d-none');

            origPaspor = { 
                no: p?.no_paspor || '', 
                exp: p?.exp_paspor || '',
                tempat: p?.tempat_dikeluarkan || '',
                tgl: p?.tanggal_dikeluarkan || ''
            };
            origItas = { 
                no: i?.no_itas || '', 
                exp: i?.exp_itas || '',
                level: i?.level_itas?.toString() || ''
            };

            set('stambuk', s.stambuk); set('nama', s.nama); set('kelas', s.kelas);
            set('rayon', s.rayon); set('pondok', s.pondok); set('negara', s.negara);
            set('kewarganegaraan', s.kewarganegaraan); 
            
            let jk = s.jenis_kelamin;
            if (jk === 'L' || jk === 'Laki-laki') jk = 'Laki-laki';
            if (jk === 'P' || jk === 'Perempuan') jk = 'Perempuan';
            set('jenis_kelamin', jk);
            
            set('kepengurusan', s.kepengurusan); set('nik', s.no_sktt); set('no_ic', s.no_ic);
            set('keberadaan_paspor', s.keberadaan_paspor); set('ukuran_baju', s.ukuran_baju);
            set('tempat_lahir', s.tempat_lahir); set('tanggal_lahir', s.tanggal_lahir);
            set('nama_ayah', s.nama_ayah); set('nama_ibu', s.nama_ibu);
            set('no_ayah', s.no_ayah); set('no_ibu', s.no_ibu);
            set('no_hp_alternatif', s.no_hp_alternatif); set('alamat', s.alamat);
            set('aktif', s.aktif);
            if (p) { 
                set('no_paspor', p.no_paspor); set('tempat_paspor', p.tempat_dikeluarkan); set('tgl_paspor', p.tanggal_dikeluarkan); set('exp_paspor', p.exp_paspor); 
                if (p.path_file) {
                    const vp = document.getElementById('view_file_paspor');
                    vp.href = '<?= API_URL ?>/api/santri/doc/paspor/' + p.id;
                    vp.classList.remove('d-none');
                }
            }
            if (pl) { set('no_paspor_lama', pl.no_paspor); }
            if (i) { 
                set('no_itas', i.no_itas); set('exp_itas', i.exp_itas); set('level_itas', i.level_itas); 
                if (i.path_file) {
                    const vi = document.getElementById('view_file_itas');
                    vi.href = '<?= API_URL ?>/api/santri/doc/itas/' + i.id;
                    vi.classList.remove('d-none');
                }
            }
            
            barangList = data.barang ? data.barang.map(b => ({nama: b.nama_barang, jumlah: b.jumlah_barang})) : [];
            renderBarang();

            // Visual Cues untuk Santri Pindahan (Request Edit Workflow)
            const isPindahan = myKepengurusan !== '' && s.kepengurusan && s.kepengurusan.trim().toLowerCase() !== myKepengurusan.trim().toLowerCase();
            isPindahanActive = isPindahan;
            
            const canEditFoto = !isPindahan || allowedFields.includes('edit_foto_personal');
            if (canEditFoto) {
                document.getElementById('fotoActions').classList.remove('d-none');
                document.getElementById('fotoActions').classList.add('d-flex');
                document.getElementById('fotoContainer').style.cursor = 'pointer';
            } else {
                document.getElementById('fotoActions').classList.remove('d-flex');
                document.getElementById('fotoActions').classList.add('d-none');
                document.getElementById('fotoContainer').style.cursor = 'default';
            }

            const canEditPaspor = !isPindahan || allowedFields.includes('edit_riwayat_paspor');
            document.getElementById('listRPaspor').innerHTML = (data.r_paspor || []).map(rp => `
                <tr>
                    <td>${rp.no_paspor}</td>
                    <td>${formatDate(rp.exp_paspor)}</td>
                    <td>${rp.path_file ? `<a href="<?= API_URL ?>/api/santri/doc/paspor/${rp.id}" target="_blank" title="Lihat Dok"><i class="bi bi-eye"></i></a>` : '-'}</td>
                    <td>
                        ${canEditPaspor ? `
                        <button type="button" class="btn btn-sm btn-outline-info py-0 px-1" onclick='showHistDetail("paspor", ${JSON.stringify(rp)})' title="Detail"><i class="bi bi-list"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1" onclick='editHist("paspor", ${JSON.stringify(rp)})' title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="deleteHist('paspor', ${rp.id})" title="Hapus"><i class="bi bi-trash"></i></button>
                        ` : '<span class="text-muted small"><i class="bi bi-lock-fill"></i> Kunci</span>'}
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="4" class="text-center text-muted">Kosong</td></tr>';

            const canEditItas = !isPindahan || allowedFields.includes('edit_riwayat_itas');
            document.getElementById('listRITAS').innerHTML = (data.r_itas || []).map(ri => `
                <tr>
                    <td>${ri.no_itas}</td>
                    <td>${ri.level_itas || '-'}</td>
                    <td>${formatDate(ri.exp_itas)}</td>
                    <td>${ri.path_file ? `<a href="<?= API_URL ?>/api/santri/doc/itas/${ri.id}" target="_blank" title="Lihat Dok"><i class="bi bi-eye"></i></a>` : '-'}</td>
                    <td>
                        ${canEditItas ? `
                        <button type="button" class="btn btn-sm btn-outline-info py-0 px-1" onclick='showHistDetail("itas", ${JSON.stringify(ri)})' title="Detail"><i class="bi bi-list"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1" onclick='editHist("itas", ${JSON.stringify(ri)})' title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="deleteHist('itas', ${ri.id})" title="Hapus"><i class="bi bi-trash"></i></button>
                        ` : '<span class="text-muted small"><i class="bi bi-lock-fill"></i> Kunci</span>'}
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="5" class="text-center text-muted">Kosong</td></tr>';

            const canEditBerkas = !isPindahan || allowedFields.includes('edit_berkas_santri');

            document.getElementById('listBerkas').innerHTML = (data.berkas || []).map(b => `
                <tr>
                    <td><div class="text-truncate" style="max-width: 200px;" title="${b.nama}">${b.nama}</div></td>
                    <td class="text-center">
                        <a href="${b.url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" title="Lihat"><i class="bi bi-eye"></i></a>
                        ${canEditBerkas ? `
                        <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1" onclick="renameBerkas('${b.nama}')" title="Ubah Nama"><i class="bi bi-pencil-square"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="deleteBerkas('${b.nama}')" title="Hapus"><i class="bi bi-trash"></i></button>
                        ` : '<span class="text-muted small ms-1" title="Dikunci"><i class="bi bi-lock-fill"></i></span>'}
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="2" class="text-center text-muted">Belum ada berkas</td></tr>';

            const btnUnggahBerkas = document.getElementById('btnUnggahBerkas');
            if (btnUnggahBerkas) {
                if (canEditBerkas) {
                    btnUnggahBerkas.style.display = 'inline-block';
                } else {
                    btnUnggahBerkas.style.display = 'none';
                }
            }


            if (s.path_foto) {
                document.getElementById('imgPreview').src = '<?= API_URL ?>/santri/' + kds + '/photo?v=' + new Date().getTime();
                document.getElementById('imgPreview').style.display = 'block';
                document.getElementById('imgIcon').style.display = 'none';
            } else {
                hapusFoto();
            }
            
            origValues = {};
            ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
             'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
             'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
             'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
                 origValues[id] = document.getElementById('f_' + id)?.value ?? '';
             });
            
            const fieldMap = {
                'nik': 'no_sktt'
            };

            ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
             'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
             'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
             'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
                 const el = document.getElementById('f_' + id);
                 if (el) {
                     const allowedKey = fieldMap[id] || id;

                     if (isPindahan && !allowedFields.includes(allowedKey)) {
                         el.classList.add('border-warning');
                         el.setAttribute('style', 'background-color: #fff3cd !important;');
                         el.setAttribute('title', 'Perubahan pada kolom ini akan dikirim sebagai draf persetujuan (Request Edit).');
                     } else {
                         el.classList.remove('border-warning');
                         el.removeAttribute('style');
                         el.removeAttribute('title');
                     }
                 }
             });

            openModal('edit');
            
            if (isPindahan) {
                document.getElementById('modalTitle').innerHTML += `<span class="badge bg-warning text-dark ms-2 align-middle border border-warning" style="font-size: 0.65rem; padding: 4px 6px; border-radius: 6px; vertical-align: text-top;"><i class="bi bi-exclamation-circle me-1"></i>Pindahan (${s.kepengurusan || '-'})</span>`;
            }
        });
}

function collectForm() {
    const get = id => document.getElementById('f_' + id)?.value ?? '';
    const form = new FormData();
    ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
     'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
     'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
     'no_itas','exp_itas','level_itas','nik','no_ic','keberadaan_paspor','ukuran_baju','aktif', 'hapus_foto'].forEach(f => form.append(f, get(f)));
    
    const foto = document.getElementById('f_foto_santri').files[0];
    if (foto) form.append('foto_santri', foto);
    
    const filePaspor = document.getElementById('f_file_paspor').files[0];
    if (filePaspor) form.append('file_paspor', filePaspor);
    
    const fileItas = document.getElementById('f_file_itas').files[0];
    if (fileItas) form.append('file_itas', fileItas);
    
    form.append('barang_bawaan', JSON.stringify(barangList));
    return form;
}

function simpanSantri() {
    if (modalMode === 'edit' && isPindahanActive) {
        let requestedChanges = [];
        ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
         'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
         'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
         'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
             const el = document.getElementById('f_' + id);
             if (el && el.classList.contains('border-warning')) {
                 if (el.value !== origValues[id]) {
                     requestedChanges.push({ field: id.replace(/_/g, ' ').toUpperCase(), old: origValues[id], new: el.value });
                 }
             }
         });

        if (requestedChanges.length > 0) {
            let html = '<div class="text-start mb-2"><div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 small mb-3"><i class="bi bi-info-circle-fill me-2"></i>Sistem mendeteksi adanya usulan perubahan pada data sensitif. Perubahan ini tidak langsung diterapkan, melainkan dikirim sebagai usulan ke kepengurusan asalnya.</div><div class="list-group list-group-flush border rounded-3 shadow-sm" style="max-height: 40vh; overflow-y: auto;">';
            requestedChanges.forEach(c => {
                html += `<div class="list-group-item p-3"><div class="fw-bold text-dark small mb-2 text-capitalize"><i class="bi bi-arrow-right-circle-fill text-warning me-2"></i>${c.field.replace('_', ' ')}</div><div class="d-flex align-items-center justify-content-between px-3 py-2 bg-light rounded"><div class="text-secondary text-decoration-line-through small" style="max-width: 45%; word-break: break-all;">${c.old || '<em class="text-muted">(Kosong)</em>'}</div><i class="bi bi-arrow-right text-muted mx-2"></i><strong class="text-primary text-end" style="max-width: 45%; word-break: break-all;">${c.new || '<em class="text-muted">(Kosong)</em>'}</strong></div></div>`;
            });
            html += '</div></div>';
            
            Swal.fire({
                icon: 'warning',
                iconColor: '#f59e0b',
                title: '<span class="fw-bold fs-5">Konfirmasi Usulan Perubahan</span>',
                html: html,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ajukan Usulan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                customClass: { popup: 'shadow-lg rounded-4', htmlContainer: 'px-0' }
            }).then((result) => {
                if (result.isConfirmed) {
                    processSimpanDocs();
                }
            });
            return;
        }
    }
    processSimpanDocs();
}

function processSimpanDocs() {
    if (modalMode === 'edit') {
        const noP = document.getElementById('f_no_paspor').value;
        const expP = document.getElementById('f_exp_paspor').value;
        const tempatP = document.getElementById('f_tempat_paspor').value;
        const tglP = document.getElementById('f_tgl_paspor').value;
        const fileP = document.getElementById('f_file_paspor').files.length > 0;
        
        const noI = document.getElementById('f_no_itas').value;
        const expI = document.getElementById('f_exp_itas').value;
        const levelI = document.getElementById('f_level_itas').value;
        const fileI = document.getElementById('f_file_itas').files.length > 0;

        let pasporChanged = fileP || 
            ((noP !== origPaspor.no || expP !== origPaspor.exp || tempatP !== origPaspor.tempat || tglP !== origPaspor.tgl) && (noP || expP || tempatP || tglP));
            
        let itasChanged = fileI || 
            ((noI !== origItas.no || expI !== origItas.exp || levelI !== origItas.level) && (noI || expI || levelI));

        if (pasporChanged || itasChanged) {
            let textChanges = "";
            if (pasporChanged && itasChanged) textChanges = "Paspor dan ITAS";
            else if (pasporChanged) textChanges = "Paspor saja";
            else if (itasChanged) textChanges = "ITAS saja";
            document.getElementById('confirmDocType').innerText = textChanges;
            
            const confirmModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDocModal'));
            document.getElementById('btnDocBaru').onclick = function() { confirmModal.hide(); doSimpan(1); };
            document.getElementById('btnDocKoreksi').onclick = function() { confirmModal.hide(); doSimpan(0); };
            confirmModal.show();
            return;
        }
    }
    doSimpan(1);
}

function doSimpan(isNewDoc) {
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    const kds = document.getElementById('santriKds').value;
    const url = modalMode === 'add' ? '<?= API_URL ?>/api/santri/store' : '<?= API_URL ?>/api/santri/' + kds + '/update';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    
    const formData = collectForm();
    formData.append('is_new_doc', isNewDoc);

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrf },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan';
        if (data.success) {
            const handleSuccess = () => {
                if (modalMode === 'edit') {
                    needReload = true;
                    editSantri(kds);
                } else {
                    bootstrap.Modal.getInstance(document.getElementById('santriModal'))?.hide();
                    setTimeout(() => location.reload(), 500);
                }
            };

            if (data.requested_edits && Object.keys(data.requested_edits).length > 0) {
                let html = '<div class="text-start small text-muted mb-3">Sistem telah menyimpan draf usulan Anda untuk disetujui kepengurusan asalnya:</div><div class="table-responsive"><table class="table table-sm table-bordered text-start small"><tbody>';
                for(let key in data.requested_edits) {
                    html += `<tr><th class="bg-light w-50">${key.replace(/_/g, ' ').toUpperCase()}</th><td class="fw-bold">${data.requested_edits[key] || '-'}</td></tr>`;
                }
                html += '</tbody></table></div>';
                
                Swal.fire({
                    icon: 'info',
                    title: 'Usulan Terkirim!',
                    html: html,
                    confirmButtonText: 'Tutup'
                }).then(() => handleSuccess());
            } else {
                showToast(data.message);
                handleSuccess();
            }
        } else {
            showToast(data.message || 'Terjadi kesalahan', false);
        }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan'; showToast('Koneksi gagal', false); });
}

let needReload = false;
document.getElementById('santriModal').addEventListener('hidden.bs.modal', function () {
    if (needReload) {
        location.reload();
    }
});

function deleteSantri(kds, nama) {
    Swal.fire({
        title: 'Konfirmasi',
        text: `Yakin ingin menonaktifkan santri "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/santri/' + kds + '/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
                .then(r => r.json())
                .then(data => {
                    showToast(data.message, data.success);
                    if (data.success) setTimeout(() => location.reload(), 800);
                });
        }
    });
}

function bulkNonaktif() {
    let bulkSelected = [];
    document.querySelectorAll('.row-cb:checked').forEach(cb => bulkSelected.push(cb.value));
    if (bulkSelected.length === 0) {
        showToast('Tidak ada data yang dipilih', false);
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Massal',
        text: `Yakin ingin menonaktifkan ${bulkSelected.length} santri terpilih secara massal?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang memproses...',
                text: 'Mohon tunggu, jangan tutup halaman ini.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/santri/bulk-toggle', { 
                method: 'POST', 
                headers: { 'X-CSRF-Token': csrf, 'Content-Type': 'application/json' },
                body: JSON.stringify({ kds: bulkSelected })
            })
            .then(r => r.json())
            .then(data => {
                Swal.close();
                showToast(data.message, data.success);
                if (data.success) setTimeout(() => location.reload(), 1000);
            }).catch(() => {
                Swal.fire('Gagal', 'Gagal menghubungi server.', 'error');
            });
        }
    });
}

function bukaEditMassal() {
    let bulkSelected = [];
    document.querySelectorAll('.row-cb:checked').forEach(cb => bulkSelected.push(cb.value));
    if (bulkSelected.length === 0) {
        showToast('Tidak ada data yang dipilih', false);
        return;
    }
    document.getElementById('bulkEditForm').reset();
    new bootstrap.Modal(document.getElementById('bulkEditModal')).show();
}

function submitBulkEdit() {
    let bulkSelected = [];
    document.querySelectorAll('.row-cb:checked').forEach(cb => bulkSelected.push(cb.value));
    
    let formData = new FormData(document.getElementById('bulkEditForm'));
    let data = { kds: bulkSelected };
    formData.forEach((val, key) => data[key] = val);

    let hasPaspor = data.tempat_paspor || data.tgl_paspor || data.exp_paspor;
    let hasItas = data.level_itas || data.exp_itas;

    if (hasPaspor || hasItas) {
        let textChanges = "";
        if (hasPaspor && hasItas) textChanges = "Paspor dan ITAS";
        else if (hasPaspor) textChanges = "Paspor saja";
        else if (hasItas) textChanges = "ITAS saja";
        
        document.getElementById('confirmDocType').innerText = textChanges;
        const confirmModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDocModal'));
        
        document.getElementById('btnDocBaru').onclick = function() { 
            confirmModal.hide(); 
            doBulkSimpan('baru', data); 
        };
        document.getElementById('btnDocKoreksi').onclick = function() { 
            confirmModal.hide(); 
            doBulkSimpan('koreksi', data); 
        };
        
        bootstrap.Modal.getInstance(document.getElementById('bulkEditModal')).hide();
        setTimeout(() => confirmModal.show(), 400);
    } else {
        doBulkSimpan('koreksi', data);
    }
}

function doBulkSimpan(docMode, data) {
    data.doc_mode = docMode;
    let btn = document.getElementById('btnSubmitBulkEdit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/santri/bulk-edit', { 
        method: 'POST', 
        headers: { 'X-CSRF-Token': csrf, 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showToast(res.message, res.success);
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('bulkEditModal')).hide();
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Perubahan';
        }
    })
    .catch(() => {
        showToast('Koneksi gagal', false);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Perubahan';
    });
}

<?php if (!empty($processes)): ?>
const masterProcesses = [
    <?php foreach ($processes as $p): ?>
    { id: "<?= $p['id'] ?>", label: "<?= htmlspecialchars(addslashes($p['nama_proses'] . (isset($p['nama_instansi']) ? ' - ' . $p['nama_instansi'] : ''))) ?>" },
    <?php endforeach; ?>
];
<?php else: ?>
const masterProcesses = [];
<?php endif; ?>


function bukaProsesBulkSantri() {
    if (typeof masterProcesses === 'undefined' || masterProcesses.length === 0) {
        Swal.fire('Tidak Ada Proses', 'Instansi Anda belum memiliki atau menyalin referensi Job Desk.', 'warning');
        return;
    }

    let bulkSelected = [];
    let conflicts = [];

    document.querySelectorAll('.row-cb:checked:not(:disabled)').forEach(cb => {
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
    
    if (bulkSelected.length === 0) {
        showToast('Tidak ada data yang dipilih', false);
        return;
    }
    
    let optionsHtml = '';
    masterProcesses.forEach(p => {
        optionsHtml += `<option value="${p.id}">${p.label}</option>`;
    });

    const executeBulk = () => {
        Swal.fire({
            title: '<span style="font-size: 1.1rem;">Buka Proses Massal</span>',
            html: `
                <div class="text-start mt-2">
                    <div class="alert alert-primary py-2 px-3 small border-primary-subtle mb-3">
                        <i class="bi bi-info-circle me-1"></i> <strong>${bulkSelected.length} Santri</strong> terpilih.
                    </div>
                    
                    <label class="form-label small fw-bold text-muted mb-1">Pilih Jenis Proses</label>
                    <select id="swal-process-id" class="form-select form-select-sm mb-3">
                        ${optionsHtml}
                    </select>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-play-circle me-1"></i>Buka Proses',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0d6efd',
            customClass: { popup: 'shadow-lg rounded-4' },
            preConfirm: () => {
                return {
                    process_id: document.getElementById('swal-process-id').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
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
                    body: JSON.stringify({ items: bulkSelected, process_id: result.value.process_id })
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
                            window.location.href = '<?= API_URL ?>/job-desk?status=aktif&process_id=' + result.value.process_id;
                        });
                    } else {
                        Swal.fire('Gagal!', res.message || 'Terjadi kesalahan', 'error');
                    }
                }).catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
                });
            }
        });
    };

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
            if (result.isConfirmed) executeBulk();
        });
    } else {
        executeBulk();
    }
}
document.getElementById('f_negara')?.addEventListener('input', updateKewarganegaraan);
document.getElementById('f_exp_itas')?.addEventListener('change', updateKewarganegaraan);

function updateKewarganegaraan() {
    const negara = document.getElementById('f_negara').value.trim().toLowerCase();
    const expItas = document.getElementById('f_exp_itas').value;
    const kewarganegaraanEl = document.getElementById('f_kewarganegaraan');
    
    if (negara === 'indonesia') {
        kewarganegaraanEl.value = 'WNI';
    } else if (negara !== '') {
        if (!expItas) {
            kewarganegaraanEl.value = 'Affidavit';
        } else {
            kewarganegaraanEl.value = 'WNA';
        }
    }
}
</script>

<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/buttons.bootstrap5.min.css">
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jquery.dataTables.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/dataTables.buttons.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/buttons.bootstrap5.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/jszip.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/buttons.html5.min.js"></script>

<script>
let table;

// Custom Filtering Logic
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable.id !== 'santriTable') return true;

    // Filter values
    const fPondok = $('#flt_pondok').val().toLowerCase();
    const fKepeng = $('#flt_kepengurusan').val().toLowerCase();
    const fNegara = $('#flt_negara').val().toLowerCase();
    const fKewarg = $('#flt_kewarganegaraan').val().toLowerCase();
    const fRayon  = $('#flt_rayon').val().toLowerCase();
    const fKelas  = $('#flt_kelas').val().toLowerCase();
    const fItas   = $('#flt_exp_itas').val(); // YYYY-MM
    const fPaspor = $('#flt_exp_paspor').val(); // YYYY

    // Row data (based on column index)
    // 3: Kelas, 4: Negara, 5: Rayon, 6: Exp Paspor, 7: Exp ITAS
    // 9: Kewarganegaraan, 10: Kepengurusan, 11: Pondok
    const rKelas = data[3].toLowerCase();
    const rNegara = data[4].toLowerCase();
    const rRayon = data[5].toLowerCase();
    const rPaspor = data[6];
    const rItas = data[7];
    const rKewarg = data[9].toLowerCase();
    const rKepeng = data[10].toLowerCase();
    const rPondok = data[11].toLowerCase();

    if (fPondok && rPondok !== fPondok) return false;
    if (fKepeng && rKepeng !== fKepeng) return false;
    if (fNegara && rNegara !== fNegara) return false;
    if (fKewarg && rKewarg !== fKewarg) return false;
    if (fRayon && rRayon !== fRayon) return false;
    if (fKelas && rKelas !== fKelas) return false;
    
    if (fItas && rItas.indexOf(fItas) !== 0) return false; // YYYY-MM match
    if (fPaspor && rPaspor.indexOf(fPaspor) !== 0) return false; // YYYY match

    return true;
});

function resetFilters() {
    $('.dt-filter').val('');
    table.draw();
}

    $(document).ready(function() {
    table = $('#santriTable').DataTable({
        pageLength: 20,
        lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "Semua"]],
        dom: "<'row mb-3 pt-3 px-3 align-items-center'<'col-sm-12 col-md-6 d-flex align-items-center gap-3'l B><'col-sm-12 col-md-6 px-4 text-end'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row px-3 pb-3'<'col-sm-12 col-md-5 mt-2'i><'col-sm-12 col-md-7 mt-2'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                className: 'btn btn-sm btn-success',
                title: 'Data Santri',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] // Ignore checkbox and aksi
                }
            }
        ],
        language: {
            search: "🔍 Pencarian Cepat:",
            lengthMenu: "Tampil _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ santri",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Santri tidak ditemukan",
            paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
        },
        orderCellsTop: true,
        initComplete: function() {
            $('.dt-search').on('keyup change clear', function() {
                var colIdx = $(this).data('col');
                table.column(colIdx).search(this.value).draw();
            });
        },
        columnDefs: [
            { orderable: false, targets: [0, 12] }
        ]
    });

    // Checkbox logics
    $('#selectAll').on('change', function() {
        $('.row-cb:not(:disabled)', table.rows({search:'applied'}).nodes()).prop('checked', this.checked);
        updateBulkButton();
    });

    $('#santriTable').on('change', '.row-cb', function(e) {
        if (!this.checked) $('#selectAll').prop('checked', false);
        updateBulkButton();
    });

    // -----------------------

    function updateBulkButton() {
        const count = $('.row-cb:checked:not(:disabled)', table.rows({search:'applied'}).nodes()).length;
        $('#countSelected').text(count);
        $('#countSelectedProses').text(count);
        $('#countSelectedEdit').text(count);
        
        if (count > 0) {
            $('#btnBulkNonaktif').removeClass('d-none');
            $('#btnBulkBukaProses').removeClass('d-none');
            $('#btnBulkEdit').removeClass('d-none');
        } else {
            $('#btnBulkNonaktif').addClass('d-none');
            $('#btnBulkBukaProses').addClass('d-none');
            $('#btnBulkEdit').addClass('d-none');
        }
    }

    // Filter redraw
    $('.dt-filter').on('change keyup', function() {
        table.draw();
        $('#selectAll').prop('checked', false);
        updateBulkButton();
    });
});

// AUTO-UPLOAD ITAS SCRIPT
function handleDragOverItas(e) {
    e.preventDefault();
    document.getElementById('dropZoneItas').classList.replace('border-light', 'border-warning');
    document.getElementById('dragOverlayItas').classList.remove('d-none');
}

function handleDragLeaveItas(e) {
    e.preventDefault();
    document.getElementById('dropZoneItas').classList.replace('border-warning', 'border-light');
    document.getElementById('dragOverlayItas').classList.add('d-none');
}

function handleDropItas(e) {
    e.preventDefault();
    document.getElementById('dropZoneItas').classList.replace('border-warning', 'border-light');
    document.getElementById('dragOverlayItas').classList.add('d-none');
    
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        handleFilesItas(e.dataTransfer.files);
    }
}

function appendLogItas(msg, type = 'info') {
    const box = document.getElementById('logItasBox');
    if (box.innerHTML.includes('Menunggu file...')) box.innerHTML = '';
    
    let color = '#fff';
    if (type === 'success') color = '#28a745';
    else if (type === 'error') color = '#dc3545';
    else if (type === 'warning') color = '#ffc107';

    box.innerHTML += `<div style="color: ${color}">> ${msg}</div>`;
    box.scrollTop = box.scrollHeight;
}

async function handleFilesItas(files) {
    const fileArray = Array.from(files).filter(f => f.name.toLowerCase().endsWith('.pdf'));
    
    if (fileArray.length === 0) {
        appendLogItas('[WARNING] Tidak ada file PDF yang dipilih.', 'warning');
        return;
    }

    appendLogItas(`[INFO] Memulai proses ${fileArray.length} file PDF...`, 'info');
    
    // Process files sequentially to avoid server overload
    for (let i = 0; i < fileArray.length; i++) {
        const file = fileArray[i];
        appendLogItas(`[PROSES] ${i+1}/${fileArray.length} - Membaca ${file.name}...`, 'info');
        
        const form = new FormData();
        form.append('itas_file', file);
        
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('<?= API_URL ?>/api/santri/auto-upload-itas', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf },
                body: form
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                appendLogItas(`[OK] ${file.name} -> ${data.santri_name} (${data.extracted_name})`, 'success');
            } else {
                appendLogItas(`[ERROR] ${file.name}: ${data.message}`, 'error');
            }
        } catch (err) {
            appendLogItas(`[ERROR] ${file.name}: Gagal menghubungi server (${err.message})`, 'error');
        }
    }
    
    appendLogItas(`[INFO] Proses Selesai!`, 'info');
    // Refresh page after done
    setTimeout(()=>location.reload(), 1500);
}

let currentTarikDataInstansiKode = null;

function tarikDataFirebase() {
    if (IS_SUPER_ADMIN) {
        if (!instansiList || instansiList.length === 0) {
            Swal.fire('Error', 'Data instansi tidak tersedia.', 'error');
            return;
        }
        
        let optionsHtml = '<select id="swal-instansi-kode" class="form-select mt-3">';
        instansiList.forEach(inst => {
            optionsHtml += `<option value="${inst.kode}">${inst.nama_instansi}</option>`;
        });
        optionsHtml += '</select>';
        
        Swal.fire({
            title: 'Pilih Instansi',
            html: `Pilih instansi untuk ditarik datanya:<br>${optionsHtml}`,
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const val = document.getElementById('swal-instansi-kode').value;
                if (!val) {
                    Swal.showValidationMessage('Silakan pilih instansi');
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                ambilDataPreviewFirebase(result.value);
            }
        });
    } else {
        ambilDataPreviewFirebase();
    }
}

function ambilDataPreviewFirebase(instansiKode = null) {
    currentTarikDataInstansiKode = instansiKode;
    
    Swal.fire({
        title: 'Mempersiapkan Pratinjau...',
        text: 'Mengambil data terbaru dari Firebase',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    
    const bodyPayload = {};
    if (instansiKode) {
        bodyPayload.instansi_kode = instansiKode;
    }
    
    fetch('<?= API_URL ?>/api/sync/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: Object.keys(bodyPayload).length > 0 ? JSON.stringify(bodyPayload) : undefined
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            Swal.close();
            
            if (res.data.length === 0) {
                Swal.fire('Info', 'Tidak ada data santri ditemukan di Firebase.', 'info');
                return;
            }

            const tbody = document.getElementById('previewSyncTbody');
            tbody.innerHTML = '';
            
            res.data.forEach(item => {
                const badge = item.status === 'new' 
                    ? '<span class="badge bg-success">Data Baru</span>' 
                    : '<span class="badge bg-warning text-dark">Diperbarui</span>';
                    
                const kelasPondok = `${item.kelas || '-'} / ${item.pondok || '-'}`;
                
                const expPasporHTML = item.exp_paspor ? `<br><small class="text-muted" style="font-size:0.7rem;"><i class="bi bi-calendar-check"></i> ${item.exp_paspor}</small>` : '';
                const paspor = item.no_paspor ? `<span class="badge bg-info text-dark">${item.no_paspor}</span>${expPasporHTML}` : '-';
                
                const expItasHTML = item.exp_itas ? `<br><small class="text-muted" style="font-size:0.7rem;"><i class="bi bi-calendar-check"></i> ${item.exp_itas}</small>` : '';
                const itas = item.no_itas ? `<span class="badge bg-secondary">${item.no_itas}</span>${expItasHTML}` : '-';
                
                const wn = item.kewarganegaraan || '-';
                    
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center">
                            <input class="form-check-input sync-checkbox" type="checkbox" value="${item.kds}" checked>
                        </td>
                        <td class="fw-medium">${item.nama || '-'}</td>
                        <td>${kelasPondok}</td>
                        <td>${wn}</td>
                        <td>${paspor}</td>
                        <td>${itas}</td>
                        <td>${badge}</td>
                    </tr>
                `;
            });
            
            document.getElementById('checkAllSync').checked = true;
            new bootstrap.Modal(document.getElementById('modalPreviewSync')).show();

        } else {
            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Gagal!', 'Terjadi kesalahan pada server saat menarik data.', 'error');
    });
}

function toggleAllSync(el) {
    const checkboxes = document.querySelectorAll('.sync-checkbox');
    checkboxes.forEach(cb => cb.checked = el.checked);
}

function prosesTarikDataTerpilih() {
    const checkboxes = document.querySelectorAll('.sync-checkbox:checked');
    if (checkboxes.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal 1 data untuk ditarik.', 'warning');
        return;
    }
    
    const selectedKds = Array.from(checkboxes).map(cb => cb.value);
    
    Swal.fire({
        title: 'Menyimpan Data...',
        text: `Memproses ${selectedKds.length} data santri`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const bodyPayload = { selected_kds: selectedKds };
    if (currentTarikDataInstansiKode) {
        bodyPayload.instansi_kode = currentTarikDataInstansiKode;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('<?= API_URL ?>/api/sync/pull', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify(bodyPayload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.message,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Gagal!', 'Terjadi kesalahan pada server saat memproses data.', 'error');
    });
}
</script>
