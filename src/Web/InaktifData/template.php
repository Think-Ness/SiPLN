<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;
use Yiisoft\Router\UrlGeneratorInterface;
/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $santris
 * @var int $total
 * @var string $search
 * @var UrlGeneratorInterface $urlGenerator
 */
$this->setTitle('Inaktif Data | Sistem Informasi');
$isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #dc354515;">
            <i class="bi bi-person-x fs-5 text-danger"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Data Santri Inaktif</h4>
            <div class="text-muted small fw-medium mt-1">Total: <strong class="text-dark"><?= $total ?></strong> santri tidak aktif</div>
        </div>
    </div>
    <?php if ($isSuperAdmin): ?>
    <button class="btn btn-danger rounded-pill px-4 fw-medium shadow-sm d-none" id="btnBulkDelete" onclick="bulkHapus()">
        <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="countSelected">0</span>)
    </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-md-5">
                <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="q" class="form-control border-0 shadow-none bg-white py-2" placeholder="Cari nama atau stambuk..." value="<?= htmlspecialchars($search) ?>" style="font-size: .85rem;">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-medium w-100 shadow-sm">Cari</button>
            </div>
            <div class="col-md-2">
                <a href="<?= rtrim(API_URL, '/') . $urlGenerator->generate('inaktif-data') ?>" class="btn btn-sm btn-light border rounded-pill px-4 fw-medium w-100 text-secondary shadow-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:calc(100vh - 280px); overflow-y:auto;">
            <table class="table table-hover table-striped align-middle mb-0" style="font-size:.85rem;">
                <thead class="table-light sticky-top shadow-sm">
                    <tr>
                        <?php if ($isSuperAdmin): ?>
                        <th class="ps-3 text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                        <?php endif; ?>
                        <th class="ps-3">#</th>
                        <th>Stambuk</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Pondok</th>
                        <th>Kewarganegaraan</th>
                        <th>No Paspor</th>
                        <th>Exp Paspor</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                    <tr class="table-secondary column-filters">
                        <?php if ($isSuperAdmin): ?><th></th><?php endif; ?>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable()" placeholder="Cari..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($santris)): ?>
                    <tr><td colspan="<?= $isSuperAdmin ? '10' : '9' ?>" class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                        Tidak ada santri inaktif
                    </td></tr>
                <?php else: $no = 1; foreach ($santris as $s): ?>
                    <tr>
                        <?php if ($isSuperAdmin): ?>
                        <td class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input row-cb" value="<?= $s['kds'] ?>">
                        </td>
                        <?php endif; ?>
                        <td class="ps-3 text-muted"><?= $no++ ?></td>
                        <td><?= htmlspecialchars((string)$s['stambuk']) ?></td>
                        <td class="fw-semibold">
                            <?= htmlspecialchars($s['nama']) ?>
                            <?php 
                                $myKep = $_SESSION['def_kepengurusan'] ?? '';
                                $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                                if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0): 
                            ?>
                                <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem; padding: 2px 4px; border-radius: 4px;">Pindahan</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($s['kelas']) ?></span></td>
                        <td><?= htmlspecialchars($s['pondok']) ?></td>
                        <td><?= htmlspecialchars($s['kewarganegaraan']) ?></td>
                        <td><code><?= htmlspecialchars((string)($s['no_paspor'] ?? '-')) ?></code></td>
                        <td><?= htmlspecialchars((!empty($s['exp_paspor']) && $s['exp_paspor'] !== '0000-00-00') ? date('d-M-Y', strtotime($s['exp_paspor'])) : '-') ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center gap-1">
                                <button class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-sm text-nowrap" title="Lihat Detail"
                                    onclick="lihatSantri(<?= $s['kds'] ?>)">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap" title="Aktifkan Kembali"
                                    onclick="reaktifkan(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Aktifkan
                                </button>
                                <?php if ($isSuperAdmin): ?>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm text-nowrap" title="Hapus Permanen"
                                    onclick="hapusData(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0">
        <span class="text-muted small">Total <?= $total ?> data inaktif</span>
    </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toastMsg" class="toast align-items-center text-white border-0 bg-success" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="santriModalInaktif" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width:1100px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header py-3 px-4 bg-light border-0">
        <h5 class="modal-title fw-bold text-dark" id="modalTitle"><i class="bi bi-person-lines-fill me-2"></i>Detail Santri Inaktif</h5>
        <div class="d-flex gap-2">
            
            <button type="button" class="btn btn-light border rounded-pill px-4 shadow-sm fw-medium text-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
      <div class="modal-body bg-light" style="font-size: 0.85rem;">
        <input type="hidden" id="santriKds" value="" readonly disabled>
        <input type="hidden" id="f_hapus_foto" value="0" readonly disabled>
        <div class="row g-2">
            <!-- KOLOM KIRI: Biodata Lengkap -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-1 px-3"><h6 class="mb-0 small fw-bold text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i>Biodata Lengkap</h6></div>
                    <div class="card-body py-3 px-4">
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Regno (Stambuk)</label><input type="number" id="f_stambuk" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">NIK (SKTT)</label><input type="text" id="f_nik" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Nama Lengkap</label><input type="text" id="f_nama" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Kelas</label><input type="text" id="f_kelas" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Rayon</label><input type="text" id="f_rayon" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Negara Asal</label><input type="text" id="f_negara" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Tempat Lahir</label><input type="text" id="f_tempat_lahir" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Tanggal Lahir</label><input type="date" id="f_tanggal_lahir" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Alamat</label><textarea id="f_alamat" class="form-control form-control-sm bg-white border border-secondary-subtle" rows="3" readonly disabled></textarea></div>
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
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No Paspor Baru</label><input type="text" id="f_no_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No Paspor Lama</label><input type="text" id="f_no_paspor_lama" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly placeholder="(Otomatis Riwayat)" readonly disabled></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-5"><label class="text-muted small fw-bold mb-1">Exp Paspor</label><input type="date" id="f_exp_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-5"><label class="text-muted small fw-bold mb-1">Exp ITAS</label><input type="date" id="f_exp_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-2"><label class="text-muted small fw-bold mb-1">Lvl ITAS</label><input type="number" id="f_level_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" min="0" readonly disabled></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No ITAS</label><input type="text" id="f_no_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">No IC Santri</label><input type="text" id="f_no_ic" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="row g-2 mb-2 border-top pt-3 mt-3">
                            <div class="col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="text-muted small fw-bold mb-0">File Paspor <a href="#" id="view_file_paspor" target="_blank" class="ms-1 small d-none" title="Lihat"><i class="bi bi-eye"></i></a></label>
                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 fw-medium shadow-sm" style="font-size: 0.65rem; border-radius: 4px;" onclick="scanMRZPaspor()" title="Ekstrak data dari gambar">
                                        <i class="bi bi-upc-scan me-1"></i>Scan Auto-Fill
                                    </button>
                                </div>
                                <input type="file" id="f_file_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" accept=".pdf,.jpg,.jpeg,.png" readonly disabled>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold mb-1" style="height: 24px;">File ITAS <a href="#" id="view_file_itas" target="_blank" class="ms-1 small d-none" title="Lihat"><i class="bi bi-eye"></i></a></label>
                                <input type="file" id="f_file_itas" class="form-control form-control-sm bg-white border border-secondary-subtle" accept=".pdf,.jpg,.jpeg,.png" readonly disabled>
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
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">Tempat Dikeluarkan</label><input type="text" id="f_tempat_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">Tanggal Dikeluarkan</label><input type="date" id="f_tgl_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_ortu">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">Nama Ayah</label><input type="text" id="f_nama_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">Nama Ibu</label><input type="text" id="f_nama_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_telp">
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No. HP Ayah</label><input type="number" id="f_no_ayah" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="mb-3"><label class="text-muted small fw-bold mb-1">No. HP Ibu</label><input type="number" id="f_no_ibu" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div><label class="text-muted small fw-bold mb-1">No. HP Alternatif</label><input type="number" id="f_no_hp_alternatif" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        </div>
                        <div class="tab-pane fade" id="t_barang">
                            <div class="d-flex gap-2 mb-3">
                                <input type="text" id="b_nama" class="form-control form-control-sm bg-white border border-secondary-subtle" placeholder="Nama barang" readonly disabled>
                                <input type="number" id="b_jumlah" class="form-control form-control-sm bg-white border border-secondary-subtle w-25" placeholder="Jml" value="1" readonly disabled>
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
                                <button type="button" class="btn btn-sm btn-primary py-0" onclick="document.getElementById('fileUploadBerkas').click()"><i class="bi bi-upload"></i> Unggah</button>
                                <input type="file" id="fileUploadBerkas" class="d-none" onchange="uploadBerkas(this)" readonly disabled>
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
                            <input type="file" id="f_foto_santri" class="d-none" accept="image/*" onchange="previewFoto(this)" readonly disabled>
                        </div>
                        
                        <div class="row g-2 mb-3 mt-1">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Status</label><select id="f_aktif" class="form-select form-select-sm bg-white border border-secondary-subtle" disabled><option value="1">Aktif</option><option value="0">Tidak Aktif</option></select></div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold mb-1">Pondok</label>
                                <input type="text" id="f_pondok" class="form-control form-control-sm bg-white border border-secondary-subtle" placeholder="Pilih / Ketik..." readonly disabled>
                            </div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Keberadaan Paspor</label><input type="text" id="f_keberadaan_paspor" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Ukuran Baju</label><input type="text" id="f_ukuran_baju" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                            <div class="col-6"><label class="text-muted small fw-bold mb-1">Jenis Kelamin</label><select id="f_jenis_kelamin" class="form-select form-select-sm bg-white border border-secondary-subtle" disabled><option value="Laki-laki">L</option><option value="Perempuan">P</option></select></div>
                        </div>
                        <div class="mb-3"><label class="text-muted small fw-bold mb-1">Kepengurusan</label><input type="text" id="f_kepengurusan" class="form-control form-control-sm bg-white border border-secondary-subtle" readonly disabled></div>
                        
                        <!-- Kewarganegaraan (Auto Server-Side) -->
                        <div class="mt-auto"><label class="text-muted small fw-bold mb-1">Kewarganegaraan</label><input type="text" id="f_kewarganegaraan" class="form-control form-control-sm bg-light text-muted border border-secondary-subtle" readonly title="Ditentukan otomatis oleh sistem berdasarkan Negara asal" readonly disabled></div>
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


<script>
function lihatSantri(kds) {
    const tbodyBarang = document.getElementById('tbodyBarang');
    const listRPaspor = document.getElementById('listRPaspor');
    const listRITAS = document.getElementById('listRITAS');
    const listBerkas = document.getElementById('listBerkas');
    
    // reset
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('imgIcon').style.display = 'block';
    
    ['stambuk','nama','kelas','rayon','pondok','negara','kewarganegaraan','jenis_kelamin','kepengurusan',
     'tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_ayah','no_ibu',
     'no_hp_alternatif','alamat','no_paspor','tempat_paspor','tgl_paspor','exp_paspor',
     'no_itas','exp_itas','level_itas','nik','no_ic','no_paspor_lama','keberadaan_paspor','ukuran_baju'].forEach(id => {
         const el = document.getElementById('f_' + id);
         if(el) el.value = '';
     });

    fetch('<?= API_URL ?>/api/santri/' + kds)
        .then(r => r.json())
        .then(data => {
            const s = data.santri || {};
            const p = data.paspor || {};
            const i = data.itas || {};
            const pl = data.paspor_lama || {};

            const set = (id, val) => { const el = document.getElementById('f_' + id); if(el) el.value = val || ''; };
            
            set('stambuk', s.stambuk); set('nama', s.nama); set('kelas', s.kelas); 
            set('rayon', s.rayon); set('pondok', s.pondok); set('negara', s.negara); 
            set('kewarganegaraan', s.kewarganegaraan); set('jenis_kelamin', s.jenis_kelamin);
            set('kepengurusan', s.kepengurusan); set('tempat_lahir', s.tempat_lahir);
            set('tanggal_lahir', s.tanggal_lahir); set('nama_ayah', s.nama_ayah);
            set('nama_ibu', s.nama_ibu); set('no_ayah', s.no_ayah); set('no_ibu', s.no_ibu);
            set('no_hp_alternatif', s.no_hp_alternatif); set('alamat', s.alamat);
            set('nik', s.no_sktt); set('no_ic', s.no_ic);
            set('keberadaan_paspor', s.keberadaan_paspor); set('ukuran_baju', s.ukuran_baju);

            if (p) { 
                set('no_paspor', p.no_paspor); set('tempat_paspor', p.tempat_keluaran); 
                set('tgl_paspor', p.tgl_keluaran); set('exp_paspor', p.exp_paspor); 
                if (p.path_file) {
                    const vp = document.getElementById('view_file_paspor');
                    if (vp) { vp.href = '<?= API_URL ?>/api/santri/doc/paspor/' + p.id; vp.classList.remove('d-none'); }
                }
            }
            if (pl) { set('no_paspor_lama', pl.no_paspor); }
            if (i) { 
                set('no_itas', i.no_itas); set('exp_itas', i.exp_itas); set('level_itas', i.level_itas); 
                if (i.path_file) {
                    const vi = document.getElementById('view_file_itas');
                    if(vi) { vi.href = '<?= API_URL ?>/api/santri/doc/itas/' + i.id; vi.classList.remove('d-none'); }
                }
            }

            if (tbodyBarang) {
                tbodyBarang.innerHTML = (data.barang || []).map((b, idx) => `
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td>${b.nama_barang}</td>
                        <td class="text-center">${b.jumlah_barang}</td>
                    </tr>
                `).join('') || '<tr><td colspan="3" class="text-center text-muted">Belum ada barang bawaan</td></tr>';
            }

            if (listRPaspor) {
                listRPaspor.innerHTML = (data.r_paspor || []).map(rp => `
                    <tr>
                        <td>${rp.no_paspor}</td>
                        <td>${rp.exp_paspor}</td>
                        <td>${rp.path_file ? `<a href="<?= API_URL ?>/api/santri/doc/paspor/${rp.id}" target="_blank" title="Lihat Dok"><i class="bi bi-eye"></i></a>` : '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="3" class="text-center text-muted">Kosong</td></tr>';
            }

            if (listRITAS) {
                listRITAS.innerHTML = (data.r_itas || []).map(ri => `
                    <tr>
                        <td>${ri.no_itas}</td>
                        <td>${ri.level_itas || '-'}</td>
                        <td>${ri.exp_itas}</td>
                        <td>${ri.path_file ? `<a href="<?= API_URL ?>/api/santri/doc/itas/${ri.id}" target="_blank" title="Lihat Dok"><i class="bi bi-eye"></i></a>` : '-'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="4" class="text-center text-muted">Kosong</td></tr>';
            }

            if (listBerkas) {
                listBerkas.innerHTML = (data.berkas || []).map(b => `
                    <tr>
                        <td><div class="text-truncate" style="max-width: 200px;" title="${b.nama}">${b.nama}</div></td>
                        <td class="text-center">
                            <a href="${b.url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" title="Lihat"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="2" class="text-center text-muted">Belum ada berkas</td></tr>';
            }

            if (s.path_foto) {
                const imgPreview = document.getElementById('imgPreview');
                if(imgPreview) {
                    imgPreview.src = '<?= API_URL ?>/santri/' + kds + '/photo?v=' + new Date().getTime();
                    imgPreview.style.display = 'block';
                    document.getElementById('imgIcon').style.display = 'none';
                }
            }
            
            const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('santriModalInaktif'));
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-person-lines-fill me-2"></i>Detail Data Santri <span class="badge bg-danger ms-2 align-middle" style="font-size: 0.65rem; padding: 4px 6px; border-radius: 6px; vertical-align: text-top;"><i class="bi bi-x-circle me-1"></i>SANTRI INAKTIF</span>';
            myModal.show();
        });
}

function filterTable() {
    const table = document.querySelector('.table-hover');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    // Create an array of filter values and their corresponding column index
    const inputs = Array.from(table.querySelectorAll('.column-filters input'));
    const filters = inputs.map(inp => {
        return {
            val: inp.value.toLowerCase(),
            cellIndex: inp.closest('th').cellIndex
        };
    });

    rows.forEach(row => {
        // Skip the empty message row
        if(row.cells.length === 1) return;
        
        let show = true;
        filters.forEach(f => {
            if (f.val) {
                const cellText = row.cells[f.cellIndex]?.textContent.toLowerCase() || '';
                if (!cellText.includes(f.val)) {
                    show = false;
                }
            }
        });
        row.style.display = show ? '' : 'none';
    });
}

function reaktifkan(kds, nama) {
    Swal.fire({
        title: 'Aktifkan Santri?',
        text: `Aktifkan kembali santri "${nama}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, aktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/santri/' + kds + '/toggle-aktif', { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
                .then(r => r.json()).then(data => {
                    const t = document.getElementById('toastMsg');
                    t.className = 'toast align-items-center text-white border-0 ' + (data.success ? 'bg-success' : 'bg-danger');
                    document.getElementById('toastBody').textContent = data.message;
                    new bootstrap.Toast(t, {delay: 2500}).show();
                    if (data.success) setTimeout(() => location.reload(), 1000);
                });
        }
    });
}

<?php if ($isSuperAdmin): ?>
// Drag to select logic
let isDragging = false;
const checkboxes = document.querySelectorAll('.row-cb');

document.addEventListener('mousedown', (e) => {
    if (e.target.classList.contains('row-cb') || e.target.closest('td') && e.target.closest('td').querySelector('.row-cb')) {
        isDragging = true;
    }
});
document.addEventListener('mouseup', () => { isDragging = false; updateBulkAction(); });
document.addEventListener('mouseleave', () => { isDragging = false; updateBulkAction(); });

checkboxes.forEach(cb => {
    cb.addEventListener('mouseenter', function() {
        if (isDragging) {
            this.checked = !this.checked;
            updateBulkAction();
        }
    });
    cb.addEventListener('change', updateBulkAction);
});

const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
        updateBulkAction();
    });
}

function updateBulkAction() {
    const count = document.querySelectorAll('.row-cb:checked').length;
    const btn = document.getElementById('btnBulkDelete');
    if (btn) {
        document.getElementById('countSelected').textContent = count;
        if (count > 0) btn.classList.remove('d-none');
        else btn.classList.add('d-none');
    }
}

function hapusData(kds, nama) {
    Swal.fire({
        title: 'Hapus Permanen?',
        html: `Anda yakin ingin menghapus data santri <strong>"${nama}"</strong> secara permanen?<br><br><span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Peringatan: Data yang dihapus tidak dapat dikembalikan, termasuk seluruh data berkas dan riwayat JobDesk.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Permanen!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang menghapus...',
                text: 'Mohon tunggu, jangan tutup halaman ini.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/inaktif-data/' + kds + '/hard-delete', { method: 'POST', headers: { 'X-CSRF-Token': csrf } })
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                }).catch(() => {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
                });
        }
    });
}

function bulkHapus() {
    const selected = Array.from(document.querySelectorAll('.row-cb:checked')).map(cb => cb.value);
    if (selected.length === 0) return;

    Swal.fire({
        title: 'Hapus Massal Permanen?',
        html: `Anda akan menghapus <strong>${selected.length}</strong> data santri secara permanen.<br><br><span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Peringatan: Seluruh data yang terkait akan terhapus dan tidak dapat dikembalikan.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang menghapus...',
                text: 'Mohon tunggu, jangan tutup halaman ini.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch('<?= API_URL ?>/api/inaktif-data/bulk-hard-delete', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ kds: selected })
            }).then(r => r.json()).then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            }).catch(() => {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data massal.', 'error');
            });
        }
    });
}
<?php endif; ?>
</script>
