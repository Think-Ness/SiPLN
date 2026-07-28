<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var array $santris
 * @var array $pondokList
 * @var array $kelasList
 * @var array $negaraList
 * @var array $kepengurusanList
 * @var array $expItasList
 * @var array $expPasporList
 * @var array $params
 */

$this->setTitle('Menu Print Data Santri Luar Negeri');
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #6f42c115;">
            <i class="bi bi-printer fs-5" style="color: #6f42c1;"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Menu Print Data Santri Luar Negeri</h4>
            <div class="text-muted small fw-medium mt-1">Cetak dan Export data santri dengan berbagai template laporan</div>
        </div>
    </div>
    <button id="toggleRightSidebarBtn" class="btn btn-outline-primary rounded-pill px-4 fw-medium shadow-sm bg-white" type="button" style="transition: all 0.3s ease;">
        <i class="bi bi-layout-sidebar-reverse me-2"></i> <span class="d-none d-md-inline fw-medium">Panel Print</span>
    </button>
</div>

<form method="GET" action="" id="filterForm">
<style>
    #printLayoutWrapper { transition: all 0.3s ease-in-out; overflow-x: hidden; }
    #mainCol { transition: width 0.3s ease-in-out; }
    #rightCol { transition: width 0.3s ease-in-out, opacity 0.3s ease-in-out, padding 0.3s ease-in-out; }
    
    #printLayoutWrapper.panel-collapsed #mainCol { width: 100%; }
    #printLayoutWrapper.panel-collapsed #rightCol {
        width: 0;
        padding-left: 0 !important;
        padding-right: 0 !important;
        opacity: 0;
        overflow: hidden;
    }
</style>
<div class="row gx-4" id="printLayoutWrapper">
<script>if(localStorage.getItem('rightSidebarState') === 'collapsed') document.getElementById('printLayoutWrapper').classList.add('panel-collapsed');</script>
    <!-- Main Content (Table & Filters) -->
    <div class="col-lg-9" id="mainCol" style="transition: width 0.3s ease-in-out;">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                


                <!-- Filters Row 2 -->
                <div class="row g-3 mb-4">
                    <div class="col">
                        <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px; font-size: .75rem;">PONDOK</label>
                        <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                            <select name="pondok" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .8rem;" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Pondok</option>
                                <?php foreach ($pondokList as $v): ?>
                                    <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['pondok'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px; font-size: .75rem;">NEGARA</label>
                        <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                            <select name="negara" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .8rem;" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Negara</option>
                                <?php foreach ($negaraList as $v): ?>
                                    <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['negara'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px; font-size: .75rem;">KEPENGURUSAN</label>
                        <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                            <select name="kepengurusan" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .8rem;" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Kepengurusan</option>
                                <?php foreach ($kepengurusanList as $v): ?>
                                    <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['kepengurusan'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px; font-size: .75rem;">KELAS</label>
                        <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                            <select name="kelas" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .8rem;" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelasList as $v): ?>
                                    <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['kelas'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px; font-size: .75rem;">EXP ITAS</label>
                        <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                            <select name="itas" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .8rem;" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua ITAS</option>
                                <?php foreach ($expItasList as $v): ?>
                                    <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['itas'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold text-muted small mb-1" style="letter-spacing: .5px; font-size: .75rem;">EXP PASPOR</label>
                        <div class="input-group input-group-sm border shadow-sm rounded-3 overflow-hidden">
                            <select name="paspor" class="form-select border-0 shadow-none bg-white py-2" style="font-size: .8rem;" onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Paspor</option>
                                <?php foreach ($expPasporList as $v): ?>
                                    <option value="<?= htmlspecialchars((string)$v) ?>" <?= ($params['paspor'] ?? '') === $v ? 'selected' : '' ?>><?= htmlspecialchars((string)$v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <a href="<?= API_URL ?>/master-print/menu-print" class="btn btn-light shadow-sm border rounded-pill px-4 text-danger fw-medium" style="padding-top: .4rem; padding-bottom: .4rem;">Clear</a>
                    </div>
                </div>
</form>

                <hr class="text-muted opacity-25">

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-light text-success border rounded-pill px-3 shadow-sm" onclick="checkAll(true)"><i class="bi bi-check-all me-1"></i> Cek All</button>
                    <button type="button" class="btn btn-sm btn-light text-danger border rounded-pill px-3 shadow-sm" onclick="checkAll(false)"><i class="bi bi-square me-1"></i> Uncek All</button>
                    <button type="button" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background:#1a2035;" onclick="submitExport('umum', 'excel')"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel</button>
                    <button type="button" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background:#1a2035;" onclick="submitExport('umum', 'pdf')"><i class="bi bi-printer me-1"></i> Print PDF</button>
                </div>

                <!-- Table -->
                <form id="exportForm" method="POST" action="<?= API_URL ?>/master-print/export">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?? '' ?>">
                    <input type="hidden" name="reportType" id="reportTypeInput" value="umum">
                    <input type="hidden" name="outputType" id="outputTypeInput" value="excel">
                    
                    <!-- Hidden inputs for Absen Anggota -->
                    <input type="hidden" name="absen_judul" id="absenJudulInput">
                    <input type="hidden" name="absen_frekuensi" id="absenFrekuensiInput">
                    <input type="hidden" name="absen_kolom" id="absenKolomInput">
                    <input type="hidden" name="absen_instansi" id="absenInstansiInput">
                    <input type="hidden" name="absen_orientasi" id="absenOrientasiInput">
                    <input type="hidden" name="absen_tampil_kop" id="absenTampilKopInput">
                    <input type="hidden" name="absen_sort_keys" id="absenSortKeysInput">
                    
                    <div class="table-responsive rounded-4 overflow-hidden border">
                        <table class="table table-hover table-striped mb-0 align-middle" style="font-size:0.85rem;">
                            <thead class="table-light text-muted shadow-sm sticky-top">
                                <tr>
                                    <th class="ps-3">No Paspor</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Daerah</th>
                                    <th>Rayon</th>
                                    <th>Exp Paspor</th>
                                    <th>Exp Itas</th>
                                    <th>Lvl</th>
                                    <th class="text-center pe-3">Aksi</th>
                                </tr>
                                <tr class="table-secondary column-filters">
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 1)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 2)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 3)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 4)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 5)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 6)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 7)" placeholder="Cari..."></th>
                                    <th><input type="text" class="form-control form-control-sm" onkeyup="filterTable(this, 8)" placeholder="Cari..."></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($santris)): ?>
                                    <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data ditemukan</td></tr>
                                <?php else: ?>
                                    <?php foreach ($santris as $s): 
                                        $today = new DateTime();
                                        $today->setTime(0, 0, 0);
                                        $expPStr = $s['exp_paspor'] ?? '';
                                        $expIStr = $s['exp_itas'] ?? '';
                                        
                                        $expP  = !empty($expPStr) && $expPStr !== '0000-00-00' ? new DateTime($expPStr) : null;
                                        if ($expP) $expP->setTime(0, 0, 0);
                                        $expI  = !empty($expIStr) && $expIStr !== '0000-00-00' ? new DateTime($expIStr) : null;
                                        if ($expI) $expI->setTime(0, 0, 0);
                                        $diffDaysP = $expP ? (int) $today->diff($expP)->format('%r%a') : null;
                                        $diffDaysI = $expI ? (int) $today->diff($expI)->format('%r%a') : null;
                                        
                                        $isExpiredP = $expP && $diffDaysP <= 0;
                                        $isUrgentP = $isExpiredP || ($diffDaysP !== null && $diffDaysP <= 540);
                                        
                                        $isExpiredI = $expI && $diffDaysI <= 0;
                                        $isUrgentI = $isExpiredI || ($diffDaysI !== null && $diffDaysI <= 90);
                                        
                                        $fmtP = $expP ? $expP->format('d-M-Y') : '-';
                                        $fmtI = $expI ? $expI->format('d-M-Y') : '-';
                                        
                                        $rowClass = ($isExpiredP || $isExpiredI) ? 'bg-danger bg-opacity-25' : (($isUrgentP || $isUrgentI) ? 'bg-danger bg-opacity-10' : '');
                                    ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td><?= htmlspecialchars((string)$s['no_paspor']) ?></td>
                                            <td class="fw-medium text-dark">
                                                <?= htmlspecialchars((string)$s['nama']) ?>
                                                <?php 
                                                    $myKep = $_SESSION['def_kepengurusan'] ?? '';
                                                    $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                                                    if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0): 
                                                ?>
                                                    <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem; padding: 2px 4px; border-radius: 4px;">Pindahan</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string)$s['kelas']) ?></td>
                                            <td><?= htmlspecialchars((string)$s['negara']) ?></td>
                                            <td><?= htmlspecialchars((string)$s['rayon']) ?></td>
                                            <td class="<?= $isExpiredP ? 'text-danger fw-bold' : ($isUrgentP ? 'text-warning fw-bold' : '') ?>">
                                                <?= $fmtP ?>
                                                <?php if ($isExpiredP): ?>
                                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                                <?php elseif ($isUrgentP): ?>
                                                    <?php $textP = $diffDaysP > 90 ? floor($diffDaysP / 30) . ' bln lagi' : $diffDaysP . ' hari lagi'; ?>
                                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textP ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="<?= $isExpiredI ? 'text-danger fw-bold' : ($isUrgentI ? 'text-warning fw-bold' : '') ?>">
                                                <?= $fmtI ?>
                                                <?php if ($isExpiredI): ?>
                                                    <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;"><i class="bi bi-exclamation-octagon me-1"></i>KADALUARSA - UPDATE!</span>
                                                <?php elseif ($isUrgentI): ?>
                                                    <?php $textI = $diffDaysI > 90 ? floor($diffDaysI / 30) . ' bln lagi' : $diffDaysI . ' hari lagi'; ?>
                                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;"><?= $textI ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string)$s['lvl']) ?></td>
                                            <td class="text-center">
                                                <input class="form-check-input kds-checkbox border-secondary" type="checkbox" name="kds[]" value="<?= htmlspecialchars((string)$s['kds']) ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3 p-3 bg-light rounded d-flex align-items-center">
                        <span class="fw-bold me-3">Jumlah Keseluruhan</span>
                        <span class="badge bg-secondary"><?= count($santris) ?></span>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Right Sidebar (Report Templates) -->
    <style>
        .template-btn { transition: all 0.2s ease; }
        .template-btn:hover { background: #f8f9fa !important; border-color: #6f42c1 !important; color: #6f42c1 !important; transform: translateY(-2px); }
    </style>
    <div class="col-lg-3" id="rightCol" style="transition: all 0.3s ease-in-out;">
        <div class="card border-0 rounded-4 shadow-sm h-100 bg-white">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Template Laporan</h6>
            </div>
            <div class="card-body p-4 d-flex flex-column gap-3 pt-2">
                <button type="button" class="btn btn-light border w-100 text-start py-3 px-4 fw-medium shadow-sm rounded-4 text-secondary template-btn" onclick="submitExport('formulir_pendataan')">
                    <i class="bi bi-file-earmark-person fs-5 me-3 align-middle text-primary"></i> Formulir Pendataan
                </button>
                <button type="button" class="btn btn-light border w-100 text-start py-3 px-4 fw-medium shadow-sm rounded-4 text-secondary template-btn" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                    <i class="bi bi-card-checklist fs-5 me-3 align-middle text-success"></i> Absen Anggota
                </button>
                <button type="button" class="btn btn-light border w-100 text-start py-3 px-4 fw-medium shadow-sm rounded-4 text-secondary template-btn" data-bs-toggle="modal" data-bs-target="#modalUkuranBaju">
                    <i class="bi bi-person-lines-fill fs-5 me-3 align-middle text-info"></i> Ukuran Baju
                </button>
                <button type="button" class="btn btn-light border w-100 text-start py-3 px-4 fw-medium shadow-sm rounded-4 text-secondary template-btn" data-bs-toggle="modal" data-bs-target="#modalFotoPersonal">
                    <i class="bi bi-person-bounding-box fs-5 me-3 align-middle text-warning"></i> Foto Personal 3x4
                </button>
                <button type="button" class="btn btn-light border w-100 text-start py-3 px-4 fw-medium shadow-sm rounded-4 text-secondary template-btn" onclick="submitExport('laporan_vertikal')">
                    <i class="bi bi-layout-text-sidebar-reverse fs-5 me-3 align-middle text-danger"></i> Laporan Vertikal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Absen Anggota -->
<div class="modal fade" id="modalAbsen" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-card-checklist text-success me-2"></i> Konfigurasi Absensi Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php if ($isSuperAdmin ?? false): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Instansi (Untuk Kop Surat)</label>
                    <select id="modalAbsenInstansi" class="form-select">
                        <option value="">-- Gunakan Default/Pusat --</option>
                        <?php foreach ($instansiList ?? [] as $inst): ?>
                            <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['nama_instansi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Laporan</label>
                    <input type="text" id="modalAbsenJudul" class="form-control" value="Daftar Hadir Anggota" placeholder="Contoh: Daftar Hadir Kelas 5">
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Kolom Informasi (No dan Nama akan selalu ditampilkan)</label>
                    <div class="row g-3" id="checkbox-columns-container">
                        <?php 
                        $kategoriKolom = [
                            'Akademik & Penempatan' => [
                                'Stambuk' => 'stambuk',
                                'Kelas' => 'kelas',
                                'Rayon' => 'rayon',
                                'Pondok/Asrama' => 'pondok',
                                'Status Santri' => 'status_santri',
                            ],
                            'Biodata & Keluarga' => [
                                'Tempat Lahir' => 'tempat_lahir',
                                'Tanggal Lahir' => 'tanggal_lahir',
                                'Jenis Kelamin' => 'jenis_kelamin',
                                'Ukuran Baju' => 'ukuran_baju',
                                'Nama Ayah' => 'nama_ayah',
                                'Nama Ibu' => 'nama_ibu',
                                'No HP (Ortu/Alt)' => 'no_hp',
                                'Alamat' => 'alamat',
                            ],
                            'Identitas & Kenegaraan' => [
                                'Asal Negara' => 'negara',
                                'Kewarganegaraan' => 'kewarganegaraan',
                                'No IC' => 'no_ic',
                                'No SKTT' => 'no_sktt',
                            ],
                            'Dokumen Paspor' => [
                                'Kondisi Paspor' => 'keberadaan_paspor',
                                'No Paspor' => 'no_paspor',
                                'Exp Paspor' => 'exp_paspor',
                            ],
                            'Dokumen ITAS' => [
                                'No ITAS' => 'no_itas',
                                'Level ITAS' => 'level_itas',
                                'Exp ITAS' => 'exp_itas',
                            ],
                        ];
                        foreach($kategoriKolom as $kategori => $koloms): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 bg-white">
                                <div class="card-header py-2 bg-light border-bottom-0 fw-bold text-secondary" style="font-size:0.85rem;">
                                    <?= htmlspecialchars($kategori) ?>
                                </div>
                                <div class="card-body py-2 px-3 border border-top-0 rounded-bottom">
                                    <div class="d-flex flex-column gap-1">
                                        <?php foreach($koloms as $label => $val): ?>
                                        <div class="form-check m-0">
                                            <input class="form-check-input absen-col-cb" type="checkbox" value="<?= $val ?>" id="cb_<?= $val ?>" <?= in_array($val, ['kelas', 'rayon']) ? 'checked' : '' ?>>
                                            <label class="form-check-label text-truncate w-100" style="font-size:0.85rem;" for="cb_<?= $val ?>" title="<?= htmlspecialchars($label) ?>">
                                                <?= htmlspecialchars($label) ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Urutan Kolom & Sorting Data</label>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-info-circle"></i> <b>Geser (Drag & Drop)</b> untuk mengubah posisi kolom dari kiri ke kanan.<br>
                        <i class="bi bi-info-circle"></i> <b>Klik Ganda (Double Click)</b> pada label biru untuk menjadikannya acuan pengurutan data (Sorting prioritas 1, 2, 3).
                    </div>
                    <div id="sortable-columns-container" class="d-flex flex-wrap gap-2 p-3 border rounded bg-light" style="min-height: 60px;">
                        <!-- Chips will be appended here by JS -->
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Orientasi Kertas</label>
                    <select id="modalAbsenOrientasi" class="form-select">
                        <option value="landscape">Lanskap (Mendatar)</option>
                        <option value="portrait">Potret (Tegak)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalAbsenTampilKop" checked>
                        <label class="form-check-label fw-bold" for="modalAbsenTampilKop">Tampilkan Kop Surat</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jumlah Kolom Kosong (Frekuensi / Ceklis)</label>
                    <input type="number" id="modalAbsenFrekuensi" class="form-control" value="14" min="1" max="50">
                    <div class="form-text">Contoh: 14 akan membuat 14 kolom bernomor 1-14 di sisi kanan tabel untuk tempat ceklis atau absen.</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="generateAbsen('excel')"><i class="bi bi-file-excel"></i> Export Excel</button>
                <button type="button" class="btn btn-primary" onclick="generateAbsen('html')"><i class="bi bi-printer"></i> Print Absen</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ukuran Baju -->
<div class="modal fade" id="modalUkuranBaju" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Pengaturan Laporan Ukuran Baju</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-white">
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Laporan</label>
                    <input type="text" id="modalBajuJudul" class="form-control" value="Laporan Rekapitulasi Ukuran Baju Santri">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Orientasi Kertas</label>
                    <select id="modalBajuOrientasi" class="form-select">
                        <option value="portrait">Potret (Tegak)</option>
                        <option value="landscape">Lanskap (Mendatar)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalBajuTampilKop" checked>
                        <label class="form-check-label fw-bold" for="modalBajuTampilKop">Tampilkan Kop Surat</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="generateBaju('excel')"><i class="bi bi-file-excel"></i> Export Excel</button>
                <button type="button" class="btn btn-primary" onclick="generateBaju('html')"><i class="bi bi-printer"></i> Print Preview</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto Personal -->
<div class="modal fade" id="modalFotoPersonal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Pengaturan Cetak Foto 3x4</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-white">
                <div class="alert alert-info py-2" style="font-size: 0.85rem;">
                    <i class="bi bi-info-circle me-1"></i> Ukuran cetak baku foto adalah <strong>3x4 cm</strong>. Menggunakan kertas A4 (lebar 210mm).
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Jumlah Foto per Baris</label>
                    <div class="input-group">
                        <input type="number" id="modalFotoCols" class="form-control" value="5" min="1" max="6">
                        <span class="input-group-text">Foto</span>
                    </div>
                    <div class="form-text mt-2 text-muted" style="font-size: 0.8rem; line-height: 1.4;">
                        <strong>Saran Cerdas:</strong> Untuk kertas A4 posisi Potret, batas maksimal yang rapi adalah <strong>5 foto per baris</strong>. Ini menyisakan ruang margin tepi dan jarak antar foto (*gap* 5mm) agar mudah digunting.
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalFotoNama" checked>
                        <label class="form-check-label fw-bold" for="modalFotoNama">Tampilkan Nama & Stambuk di Bawah Foto</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalFotoGaris" checked>
                        <label class="form-check-label fw-bold" for="modalFotoGaris">Tampilkan Garis Potong (Cut Line)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="generateFoto('zip')"><i class="bi bi-file-zip"></i> Download Gambar (ZIP)</button>
                <button type="button" class="btn btn-primary" onclick="generateFoto('html')"><i class="bi bi-printer"></i> Print Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
function generateFoto(outType = 'html') {
    const checked = document.querySelectorAll('input[name="kds[]"]:checked').length;
    if (checked === 0) {
        Swal.fire({icon: 'warning', title: 'Oops...', text: 'Silakan pilih minimal satu data santri.', confirmButtonColor: '#0d6efd'});
        return;
    }
    
    // We use absen_frekuensi as the column count
    const cols = document.getElementById('modalFotoCols').value;
    document.getElementById('absenFrekuensiInput').value = cols;
    
    // Use absen_kolom to store checkboxes states
    const showNama = document.getElementById('modalFotoNama').checked ? '1' : '0';
    const showGaris = document.getElementById('modalFotoGaris').checked ? '1' : '0';
    document.getElementById('absenKolomInput').value = showNama + ',' + showGaris;

    bootstrap.Modal.getInstance(document.getElementById('modalFotoPersonal')).hide();
    submitExport('foto_personal', outType);
}

function generateBaju(outType = 'html') {
    const checked = document.querySelectorAll('input[name="kds[]"]:checked').length;
    if (checked === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Silakan pilih minimal satu data santri di tabel sebelum membuat absensi.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    const judulEl = document.getElementById('modalBajuJudul');
    if (judulEl) document.getElementById('absenJudulInput').value = judulEl.value;
    
    const orientasiEl = document.getElementById('modalBajuOrientasi');
    if (orientasiEl) document.getElementById('absenOrientasiInput').value = orientasiEl.value;
    
    const tampilKopEl = document.getElementById('modalBajuTampilKop');
    if (tampilKopEl) document.getElementById('absenTampilKopInput').value = tampilKopEl.checked ? '1' : '0';

    bootstrap.Modal.getInstance(document.getElementById('modalUkuranBaju')).hide();
    
    submitExport('ukuran_baju', outType);
}

function generateAbsen(outType = 'html') {
    // Check if any checkbox is checked in the main table
    const checked = document.querySelectorAll('input[name="kds[]"]:checked').length;
    if (checked === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Silakan pilih minimal satu data santri di tabel sebelum membuat absensi.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    const judulEl = document.getElementById('modalAbsenJudul');
    if (judulEl) {
        document.getElementById('absenJudulInput').value = judulEl.value;
    }
    
    // Read the order from the sortable chips instead of just checkboxes
    const chips = Array.from(document.getElementById('sortable-columns-container').children);
    const cols = chips.map(chip => chip.dataset.val);
    document.getElementById('absenKolomInput').value = cols.join(',');
    
    const frekEl = document.getElementById('modalAbsenFrekuensi');
    if(frekEl) {
        document.getElementById('absenFrekuensiInput').value = frekEl.value;
    }
    
    const instansiSelect = document.getElementById('modalAbsenInstansi');
    if (instansiSelect) {
        document.getElementById('absenInstansiInput').value = instansiSelect.value;
    }
    
    const orientasiEl = document.getElementById('modalAbsenOrientasi');
    if (orientasiEl) {
        document.getElementById('absenOrientasiInput').value = orientasiEl.value;
    }
    
    const tampilKopEl = document.getElementById('modalAbsenTampilKop');
    if (tampilKopEl) {
        document.getElementById('absenTampilKopInput').value = tampilKopEl.checked ? '1' : '0';
    }

    // Set sort keys
    document.getElementById('absenSortKeysInput').value = window.absenSortKeys ? window.absenSortKeys.join(',') : '';

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('modalAbsen')).hide();
    
    // Submit
    submitExport('absen_anggota', outType);
}

function filterTable() {
    const table = document.querySelector('.table-responsive table');
    if (!table) return;
    const trs = table.getElementsByTagName("tr");
    const inputs = table.querySelectorAll('.column-filters input');
    
    for (let i = 2; i < trs.length; i++) {
        const tds = trs[i].getElementsByTagName("td");
        if (tds.length <= 1) continue; // skip "No data" row
        
        let showRow = true;
        inputs.forEach((input) => {
            const filter = input.value.toUpperCase();
            if (filter) {
                const th = input.closest('th');
                const colIndex = Array.from(th.parentNode.children).indexOf(th);
                
                if (tds.length > colIndex) {
                    const td = tds[colIndex];
                    if (td) {
                        const txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) === -1) {
                            showRow = false;
                        }
                    }
                }
            }
        });
        
        trs[i].style.display = showRow ? "" : "none";
    }
}

// Drag and drop column ordering logic
document.addEventListener('DOMContentLoaded', function() {
    const cbContainer = document.getElementById('checkbox-columns-container');
    const sortContainer = document.getElementById('sortable-columns-container');
    if (!cbContainer || !sortContainer) return;
    
    const checkboxes = cbContainer.querySelectorAll('.absen-col-cb');
    window.absenSortKeys = []; // Array of column values for sorting
    
    function renderSortBadges() {
        const chips = Array.from(sortContainer.children);
        chips.forEach(chip => {
            const val = chip.dataset.val;
            const idx = window.absenSortKeys.indexOf(val);
            
            // Remove existing badge
            const existingBadge = chip.querySelector('.sort-badge');
            if (existingBadge) existingBadge.remove();
            
            if (idx > -1) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-warning text-dark ms-2 sort-badge';
                badge.style.fontSize = '0.7rem';
                badge.innerText = 'Sort ' + (idx + 1);
                chip.appendChild(badge);
                chip.classList.replace('bg-primary', 'bg-success');
            } else {
                chip.classList.replace('bg-success', 'bg-primary');
            }
        });
    }
    
    function updateSortableChips() {
        const existingChips = Array.from(sortContainer.children).map(c => c.dataset.val);
        
        checkboxes.forEach(cb => {
            const val = cb.value;
            const label = cb.nextElementSibling.innerText.trim();
            const exists = existingChips.includes(val);
            
            if (cb.checked && !exists) {
                // Create chip
                const chip = document.createElement('div');
                chip.className = 'badge bg-primary fs-6 py-2 px-3 fw-normal cursor-move user-select-none';
                chip.style.cursor = 'move';
                chip.draggable = true;
                chip.dataset.val = val;
                chip.innerHTML = '<i class="bi bi-grip-vertical me-1 opacity-50"></i><span>' + label + '</span>';
                
                // Drag events
                chip.addEventListener('dragstart', handleDragStart);
                chip.addEventListener('dragover', handleDragOver);
                chip.addEventListener('drop', handleDrop);
                chip.addEventListener('dragend', handleDragEnd);
                
                // Double click event for sorting
                chip.addEventListener('dblclick', function() {
                    const v = this.dataset.val;
                    const idx = window.absenSortKeys.indexOf(v);
                    if (idx > -1) {
                        window.absenSortKeys.splice(idx, 1);
                    } else {
                        if (window.absenSortKeys.length >= 3) {
                            alert('Maksimal 3 urutan sorting yang diperbolehkan.');
                            return;
                        }
                        window.absenSortKeys.push(v);
                    }
                    renderSortBadges();
                });
                
                sortContainer.appendChild(chip);
            } else if (!cb.checked && exists) {
                // Remove chip
                const toRemove = sortContainer.querySelector(`[data-val="${val}"]`);
                if (toRemove) toRemove.remove();
                
                // Remove from sortKeys if unchecked
                const sIdx = window.absenSortKeys.indexOf(val);
                if (sIdx > -1) window.absenSortKeys.splice(sIdx, 1);
            }
        });
        renderSortBadges();
    }
    
    let draggedItem = null;
    function handleDragStart(e) {
        draggedItem = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        this.classList.add('opacity-50');
    }
    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }
    function handleDrop(e) {
        e.stopPropagation();
        if (draggedItem !== this) {
            const children = Array.from(sortContainer.children);
            const draggedIdx = children.indexOf(draggedItem);
            const targetIdx = children.indexOf(this);
            if (draggedIdx < targetIdx) {
                this.parentNode.insertBefore(draggedItem, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedItem, this);
            }
        }
        return false;
    }
    function handleDragEnd(e) {
        this.classList.remove('opacity-50');
    }
    
    // Listen to changes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSortableChips);
    });
    
    // Initialize chips on load
    updateSortableChips();
});

function checkAll(check) {
    const checkboxes = document.querySelectorAll('.kds-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = check;
    });
}

function submitExport(reportType, outputOverride = null) {
    const outputType = outputOverride || 'excel';
    
    document.getElementById('reportTypeInput').value = reportType;
    document.getElementById('outputTypeInput').value = outputType;
    
    // Check if any checkbox is checked
    const checked = document.querySelectorAll('.kds-checkbox:checked').length;
    if (checked === 0 && reportType !== 'formulir_pendataan') {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Silakan pilih minimal satu data santri untuk di-print/export.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    document.getElementById('exportForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleRightSidebarBtn');
    const wrapper = document.getElementById('printLayoutWrapper');
    
    if (toggleBtn && wrapper) {
        const updateBtnStyle = () => {
            if (wrapper.classList.contains('panel-collapsed')) {
                toggleBtn.classList.replace('btn-outline-primary', 'btn-primary');
                toggleBtn.classList.remove('bg-white');
            } else {
                toggleBtn.classList.replace('btn-primary', 'btn-outline-primary');
                toggleBtn.classList.add('bg-white');
            }
        };
        updateBtnStyle();
        
        toggleBtn.addEventListener('click', function() {
            wrapper.classList.toggle('panel-collapsed');
            const isCollapsed = wrapper.classList.contains('panel-collapsed');
            localStorage.setItem('rightSidebarState', isCollapsed ? 'collapsed' : 'expanded');
            updateBtnStyle();
        });
    }
});
</script>
