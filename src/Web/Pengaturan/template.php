<?php
declare(strict_types=1);

use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var array $allowedFields
 * @var array $availableFields
 * @var string $role
 */
$this->setTitle('Pengaturan Sistem | Manajemen Terpusat');
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #6f42c115;">
            <i class="bi bi-gear-fill text-purple" style="color: #6f42c1;"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Pengaturan Sistem</h4>
            <div class="text-muted small fw-medium mt-1">Konfigurasi Hak Akses dan Parameter Aplikasi</div>
        </div>
    </div>
</div>

<?php if ($role !== 'super_admin'): ?>
    <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
        <div>
            <h5 class="fw-bold mb-1">Akses Ditolak</h5>
            <p class="mb-0 text-muted small">Halaman ini hanya dapat diakses dan diubah oleh Super Admin. Anda dapat melihat pengaturannya saja.</p>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white p-4" data-bs-toggle="collapse" data-bs-target="#collapseAkses" style="cursor: pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
                    <h5 class="fw-bold mb-0">Hak Akses Instansi Pindahan</h5>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    Tentukan kolom data mana saja yang diizinkan untuk diubah secara langsung oleh Admin Instansi / Pondok Pindahan 
                    <span class="text-danger fw-bold">tanpa perlu persetujuan (Request Edit)</span> dari kepengurusan asli santri tersebut.
                </p>
            </div>
            <i class="bi bi-chevron-expand fs-4 text-secondary"></i>
        </div>
    </div>
    <div id="collapseAkses" class="collapse border-top">
        <div class="row g-0">
            <div class="col-lg-8 p-4 bg-light bg-opacity-50">
                <form id="settingsForm">
                    <div class="row g-3">
                        <?php foreach ($availableFields as $key => $label): 
                            $isChecked = in_array($key, $allowedFields);
                        ?>
                            <div class="col-md-6">
                                <label class="form-check p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center cursor-pointer" style="cursor: pointer; transition: all .2s; <?= $isChecked ? 'border-color: #0d6efd !important;' : '' ?>" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="fields[]" value="<?= $key ?>" <?= $isChecked ? 'checked' : '' ?> <?= $role !== 'super_admin' ? 'disabled' : '' ?> style="transform: scale(1.2);">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: .9rem;"><?= htmlspecialchars($label) ?></div>
                                        <div class="text-muted" style="font-size: .7rem;">(<?= htmlspecialchars($key) ?>)</div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($role === 'super_admin'): ?>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" onclick="saveSettings()">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i>Simpan Konfigurasi
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-lg-4 p-4 text-center d-flex flex-column justify-content-center" style="background: linear-gradient(145deg, #1e293b, #0f172a); color: white;">
                <i class="bi bi-info-circle-fill text-primary fs-1 mb-3 opacity-75"></i>
                <h5 class="fw-bold mb-3">Informasi Sistem</h5>
                <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                    Fitur Hak Akses Pindahan memungkinkan Pondok menempatkan (menitipkan) santrinya ke instansi/pondok lain untuk mengenyam pendidikan tertentu. 
                    <br><br>
                    Kolom yang <strong>tidak dicentang</strong> di samping ini akan dikunci. Jika instansi pindahan mencoba mengubah data yang dikunci, 
                    sistem akan otomatis membuat <span class="badge bg-warning text-dark border border-warning">Request Edit</span> yang harus disetujui oleh Anda (kepengurusan asalnya).
                </p>
                <div class="mt-auto">
                    <div class="p-3 rounded-4 bg-white bg-opacity-10">
                        <div class="fw-bold fs-4"><?= count($allowedFields) ?></div>
                        <div class="text-white-50 small text-uppercase" style="letter-spacing: 1px;">Kolom Dibebaskan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (in_array($role, ['super_admin', 'admin_instansi'])): ?>
<!-- Firebase Cloud Sync Section -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white p-4" data-bs-toggle="collapse" data-bs-target="#collapseFirebase" style="cursor: pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up-fill text-warning fs-5"></i>
                    <h5 class="fw-bold mb-0">Firebase Cloud Sync</h5>
                    <span id="firebase-status-badge" class="badge bg-secondary ms-2">Memeriksa...</span>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    Sinkronisasi data lokal (Santri, Paspor, ITAS, Users) ke Firebase Firestore untuk pemantauan online secara real-time.
                </p>
            </div>
            <i class="bi bi-chevron-expand fs-4 text-secondary"></i>
        </div>
    </div>
    <div id="collapseFirebase" class="collapse border-top">
        <div class="row g-0">
            <div class="col-lg-8 p-4 bg-white">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-arrow-repeat text-primary"></i>
                                <strong class="small">Dual-Write (Otomatis)</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Setiap kali data di-<strong>tambah</strong>, <strong>edit</strong>, atau <strong>hapus</strong> di aplikasi lokal, perubahannya akan otomatis dikirim ke Firebase secara real-time. <span class="text-success fw-bold">Sudah aktif ✓</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cloud-upload text-warning"></i>
                                <strong class="small">Full Sync (Manual)</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Kirim <strong>SEMUA</strong> data yang sudah ada di database lokal ke Firebase sekaligus. Gunakan ini saat pertama kali setup atau jika ada data yang belum tersinkron.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="sync-result" class="mt-3 d-none">
                    <div class="alert alert-info border-0 rounded-3 mb-0">
                        <div id="sync-result-content"></div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" onclick="triggerFullSync()" id="btn-full-sync">
                        <i class="bi bi-cloud-upload-fill me-2"></i>Kirim Semua Data ke Firebase
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="checkFirebaseStatus()">
                        <i class="bi bi-wifi me-1"></i>Cek Koneksi
                    </button>
                </div>
            </div>
            <div class="col-lg-4 p-4 text-center d-flex flex-column justify-content-center" style="background: linear-gradient(145deg, #f97316, #ea580c); color: white;">
                <i class="bi bi-cloud-fill fs-1 mb-3 opacity-75"></i>
                <h5 class="fw-bold mb-3">Cara Kerja</h5>
                <p class="text-white small mb-3" style="line-height: 1.6; opacity: .9;">
                    <strong>1. Dual-Write (Otomatis)</strong><br>
                    Setiap ada perubahan data (tambah/edit/hapus), sistem langsung mengirim salinannya ke Firebase Cloud.
                    <br><br>
                    <strong>2. Full Sync (Manual)</strong><br>
                    Tekan tombol <em>"Kirim Semua Data"</em> untuk mendorong seluruh isi database lokal ke Firebase. Biasanya hanya dijalankan sekali saat pertama kali setup.
                </p>
                <div class="mt-auto">
                    <div class="p-3 rounded-4 bg-white bg-opacity-25">
                        <div class="fw-bold small text-uppercase" style="letter-spacing: 1px;">Project Firebase</div>
                        <div class="fw-bold"><?= htmlspecialchars($firebaseConfig['project_id'] ?? 'Belum Dikonfigurasi') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($role === 'super_admin'): ?>
<!-- ITAS Parser & Template Configuration Section -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="cardItasParser">
    <div class="card-header bg-white p-4" data-bs-toggle="collapse" data-bs-target="#collapseItasParser" style="cursor: pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                    <h5 class="fw-bold mb-0">Pengaturan Format & Parser ITAS (Auto-Upload)</h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2" id="itas-mode-badge">
                        <?= ($itasConfig['mode'] ?? 'auto') === 'auto' ? '⚡ Auto-Detect Aktif' : '🎯 Format Terkunci' ?>
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    Konfigurasi fleksibel mesin pembaca PDF ITAS otomatis. Mengatasi perbedaan bentuk ITAS saat ini, format 2-3 tahun lalu, maupun perubahan format di masa depan tanpa merombak sistem.
                </p>
            </div>
            <i class="bi bi-chevron-expand fs-4 text-secondary"></i>
        </div>
    </div>
    <div id="collapseItasParser" class="collapse border-top">
        <div class="card-body p-4 bg-white">
            
            <!-- Pengaturan Utama Mode Parser -->
            <div class="row g-4 mb-4 pb-4 border-bottom">
                <div class="col-lg-6">
                    <label class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-gear-wide-connected text-primary"></i>
                        Mode Deteksi Dokumen ITAS
                    </label>
                    <div class="bg-light p-3 rounded-3 border">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="itas_mode" id="mode_auto" value="auto" <?= ($itasConfig['mode'] ?? 'auto') === 'auto' ? 'checked' : '' ?> onchange="onItasModeChange()">
                            <label class="form-check-label fw-bold text-dark" for="mode_auto">
                                ⚡ Mode Cerdas Multi-Format (Auto-Detect - Sangat Direkomendasikan)
                            </label>
                            <div class="text-muted small ms-4">
                                Mesin otomatis mencocokkan teks PDF dengan semua format yang aktif (format sekarang, format lama, maupun format baru) secara simultan.
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="radio" name="itas_mode" id="mode_manual" value="manual" <?= ($itasConfig['mode'] ?? 'auto') !== 'auto' ? 'checked' : '' ?> onchange="onItasModeChange()">
                            <label class="form-check-label fw-bold text-dark" for="mode_manual">
                                🎯 Kunci ke Format Tertentu Saja
                            </label>
                            <div class="ms-4 mt-2">
                                <select id="itas_active_profile_select" class="form-select form-select-sm" style="max-width: 380px;" <?= ($itasConfig['mode'] ?? 'auto') === 'auto' ? 'disabled' : '' ?>>
                                    <?php foreach (($itasConfig['profiles'] ?? []) as $p): ?>
                                        <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($itasConfig['active_profile_id'] ?? '') === $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <label class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        Pencocokan Cadangan via Nomor Paspor
                    </label>
                    <div class="bg-light p-3 rounded-3 border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="itas_fallback_passport" <?= !empty($itasConfig['fallback_passport_match']) ? 'checked' : '' ?> style="transform: scale(1.2); cursor: pointer;">
                                <label class="form-check-label fw-bold text-dark ms-2" for="itas_fallback_passport" style="cursor: pointer;">
                                    Aktifkan Pencarian via No Paspor (Smart Fallback)
                                </label>
                            </div>
                            <p class="text-muted small mt-2 mb-0" style="line-height: 1.5;">
                                Jika nama santri pada teks ITAS memiliki perbedaan ejaan atau urutan kata yang tidak terdeteksi, sistem akan otomatis mencocokkan <strong>Passport Number</strong> pada ITAS dengan data paspor santri di database.
                            </p>
                        </div>
                        <div class="alert alert-success border-0 py-2 px-3 small rounded-3 mb-0 mt-3 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Tingkat akurasi pencocokan meningkat hingga <strong>99.8%</strong>.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Profil Format ITAS -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-collection-fill text-warning"></i>
                        Daftar Template / Profil Format ITAS
                    </h6>
                    <span class="text-muted small">Kelola aturan pola pembacaan untuk setiap variasi dokumen ITAS.</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="openAddProfileModal()">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Template Baru
                </button>
            </div>

            <div id="itas-profiles-container" class="row g-3 mb-4">
                <!-- Diisi via JavaScript renderProfiles() -->
            </div>

            <!-- Bagian Live Test Preview PDF ITAS -->
            <div class="card border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold text-primary mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-flask-fill"></i>
                                Live Test / Uji Coba Parser PDF ITAS
                            </h6>
                            <span class="text-muted small">Coba upload 1 file PDF ITAS (format apapun) untuk melihat langsung hasil ekstraksi dan kecocokannya dengan aturan di atas.</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary bg-white rounded-pill px-3 fw-bold" onclick="document.getElementById('testPdfInput').click()">
                            <i class="bi bi-upload me-1"></i> Pilih PDF Uji Coba
                        </button>
                    </div>

                    <input type="file" id="testPdfInput" class="d-none" accept=".pdf" onchange="runLiveTestParser(this.files[0])">
                    
                    <div id="testDropZone" class="border rounded-3 p-4 bg-white text-center shadow-sm cursor-pointer position-relative mb-3" style="border-style: dashed !important; border-width: 2px !important; cursor: pointer;" onclick="document.getElementById('testPdfInput').click()" ondragover="event.preventDefault(); this.classList.add('border-primary')" ondragleave="this.classList.remove('border-primary')" ondrop="event.preventDefault(); this.classList.remove('border-primary'); if(event.dataTransfer.files.length) runLiveTestParser(event.dataTransfer.files[0])">
                        <i class="bi bi-file-earmark-arrow-up text-primary fs-2"></i>
                        <div class="fw-bold text-dark mt-2">Klik atau Tarik File PDF ITAS ke Sini untuk Menguji</div>
                        <div class="text-muted small">File hanya dianalisis sementara dan tidak akan disimpan ke database santri.</div>
                                      <div id="testResultBox" class="d-none">
                        <!-- Container hasil tes -->
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Simpan & Reset -->
            <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-medium btn-sm" onclick="resetItasConfigToDefault()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset ke Standar Pabrik
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow" onclick="saveItasParserConfig()">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> Simpan Konfigurasi ITAS
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Profil ITAS -->
<div class="modal fade" id="modalItasProfile" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalItasProfileTitle">
                    <i class="bi bi-sliders text-primary me-2"></i>Edit Aturan Template ITAS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- AI / Auto Wizard dari Sampel PDF -->
                <div class="card border-0 bg-primary bg-opacity-10 rounded-4 p-3 mb-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;">
                                <i class="bi bi-magic fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Deteksi Otomatis dari Sampel PDF</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.75rem;">Tinggal upload 1 contoh PDF ITAS — sistem otomatis menganalisis dan mengisi formulir aturan!</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="document.getElementById('samplePdfUploadInput').click()">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Upload Sampel PDF
                        </button>
                    </div>
                    <input type="file" id="samplePdfUploadInput" class="d-none" accept=".pdf" onchange="runAutoAnalyzeSample(this.files[0])">
                    
                    <div id="sampleDropZone" class="border rounded-3 p-3 bg-white text-center cursor-pointer position-relative mt-2 shadow-sm" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important; cursor: pointer;" onclick="document.getElementById('samplePdfUploadInput').click()" ondragover="event.preventDefault(); this.style.backgroundColor='#f0f7ff';" ondragleave="this.style.backgroundColor='white';" ondrop="event.preventDefault(); this.style.backgroundColor='white'; if(event.dataTransfer.files.length) runAutoAnalyzeSample(event.dataTransfer.files[0])">
                        <div id="sampleUploadPlaceholder">
                            <i class="bi bi-cloud-arrow-up-fill text-primary fs-3"></i>
                            <div class="fw-bold text-dark small mt-1">Klik atau Tarik File Sampel PDF ITAS ke Sini</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">Format baru/lama langsung dibaca tanpa perlu paham rumus regex manual.</div>
                        </div>
                        <div id="sampleAnalyzingSpinner" class="d-none py-2">
                            <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                            <span class="fw-bold text-dark small">Menganalisis sampel PDF secara otomatis...</span>
                        </div>
                    </div>

                    <!-- Hasil Deteksi Sampel (Visual Preview) -->
                    <div id="sampleDetectionResult" class="d-none mt-3 p-3 bg-white border border-success border-opacity-50 rounded-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Pola Berhasil Dideteksi Mesin</span>
                            <span class="text-muted small font-monospace" id="sampleFileName" style="font-size: 0.75rem;"></span>
                        </div>
                        <div class="row g-2 small text-dark">
                            <div class="col-sm-6"><strong>📋 No ITAS:</strong> <span class="badge bg-light text-dark border font-monospace" id="det_no_itas">-</span></div>
                            <div class="col-sm-6"><strong>📅 Masa Berlaku:</strong> <span class="badge bg-info-subtle text-info border" id="det_exp_itas">-</span></div>
                            <div class="col-sm-6"><strong>👤 Nama Terbaca:</strong> <span class="fw-bold text-primary" id="det_nama">-</span></div>
                            <div class="col-sm-6"><strong>🛂 No Paspor:</strong> <span class="font-monospace text-muted" id="det_paspor">-</span></div>
                        </div>
                        <div class="text-success small fw-medium mt-2 pt-1 border-top" style="font-size: 0.75rem;">
                            <i class="bi bi-stars me-1"></i> Semua parameter aturan di bawah telah diisi otomatis. Cukup tinjau lalu klik tombol <strong>"Terapkan Aturan"</strong>.
                        </div>
                    </div>
                </div>

                <form id="formItasProfile">
                    <input type="hidden" id="edit_prof_id">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Nama Template / Format</label>
                            <input type="text" id="edit_prof_name" class="form-control form-control-sm" placeholder="Contoh: Ditjen Imigrasi 2026+" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Status Format</label>
                            <select id="edit_prof_enabled" class="form-select form-select-sm">
                                <option value="1">Aktif (Digunakan)</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Deskripsi Singkat</label>
                            <input type="text" id="edit_prof_desc" class="form-control form-control-sm" placeholder="Keterangan layout atau tahun terbit format ini">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Kata Kunci Pengenal Dokumen (Identifier Keywords)</label>
                            <input type="text" id="edit_prof_keywords" class="form-control form-control-sm" placeholder="Pisahkan dengan koma, contoh: DIRECTORATE GENERAL OF IMMIGRATION, TEMPORARY STAY PERMIT">
                            <div class="form-text small" style="font-size: 0.75rem;">Kata kunci unik yang ada pada dokumen ini untuk membedakannya dari format lain saat Auto-Detect.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Metode Pengambilan Nama Santri</label>
                            <select id="edit_prof_name_mode" class="form-select form-select-sm" onchange="toggleNameRegexField()">
                                <option value="top_line">Baris Judul Pertama (Top Line Header)</option>
                                <option value="regex">Pola Teks / Label (Contoh: Full Name : ...)</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="wrap_name_regex">
                            <label class="form-label small fw-bold text-dark">Regex Pola Nama (Name Pattern)</label>
                            <input type="text" id="edit_prof_name_regex" class="form-control form-control-sm font-monospace" placeholder="/(?:Full\s*Name|Nama)\s*:\s*([^\n\r]+)/i">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Regex Pola Nomor ITAS (Permit Number)</label>
                            <input type="text" id="edit_prof_permit_regex" class="form-control form-control-sm font-monospace" placeholder="/(?:PERMIT\s+NUMBER)\s*:\s*([A-Z0-9\-]+)/i" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Regex Pola Masa Berlaku (Expiry Date)</label>
                            <input type="text" id="edit_prof_expiry_regex" class="form-control form-control-sm font-monospace" placeholder="/(?:STAY\s+PERMIT\s+EXPIRY)\s*:\s*(\d{2}[\/\-]\d{2}[\/\-]\d{4})/i" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Regex Pola Nomor Paspor (Opsional untuk Pencocokan Cadangan)</label>
                            <input type="text" id="edit_prof_passport_regex" class="form-control form-control-sm font-monospace" placeholder="/(?:Passport\s+Number|No\s+Paspor)\s*:\s*([A-Z0-9\-]+)/i">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top bg-light px-4 py-3">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-medium border" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="saveProfileModal()">
                    <i class="bi bi-check-lg me-1"></i> Terapkan Aturan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Database Migration / Update Section -->

<?php endif; ?>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span id="toastMsg" class="fw-medium">Tersimpan!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function saveSettings() {
    // Kumpulkan data form
    const form = document.getElementById('settingsForm');
    const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked');
    const fields = Array.from(checkboxes).map(cb => cb.value);

    // Siapkan body request
    const formData = new URLSearchParams();
    fields.forEach(f => formData.append('fields[]', f));

    // Kirim AJAX (Asumsi ada meta csrf-token di layout utama)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    Swal.fire({
        title: 'Menyimpan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('<?= API_URL ?>/api/pengaturan/update', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken,
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.close();
            const toastEl = document.getElementById('toastSuccess');
            document.getElementById('toastMsg').textContent = res.message;
            new bootstrap.Toast(toastEl, {delay: 3000}).show();
            
            // Beri highlight pada kotak centang
            form.querySelectorAll('label').forEach(lbl => lbl.style.borderColor = '#dee2e6');
            checkboxes.forEach(cb => {
                cb.closest('label').style.borderColor = '#0d6efd';
                cb.closest('label').style.borderWidth = '2px';
            });
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        console.error(err);
    });
}
</script>

<script>
// ============================================
// FIREBASE CLOUD SYNC FUNCTIONS
// ============================================

// Cek status koneksi Firebase saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('firebase-status-badge')) {
        checkFirebaseStatus();
    }
});

function checkFirebaseStatus() {
    const badge = document.getElementById('firebase-status-badge');
    if (!badge) return;
    badge.className = 'badge bg-secondary ms-2';
    badge.textContent = 'Memeriksa...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('<?= API_URL ?>/api/firebase/status', {
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(r => r.json())
    .then(res => {
        if (res.connected) {
            badge.className = 'badge bg-success ms-2';
            badge.textContent = '● Terhubung';
        } else {
            badge.className = 'badge bg-danger ms-2';
            badge.textContent = '● Tidak Terhubung';
        }
    })
    .catch(() => {
        badge.className = 'badge bg-danger ms-2';
        badge.textContent = '● Error';
    });
}

function triggerFullSync() {
    Swal.fire({
        title: 'Kirim Semua Data ke Firebase?',
        html: `<p class="text-muted">Ini akan mengirimkan <strong>semua</strong> data Santri, Paspor, ITAS, dan Users dari database lokal ke Firebase Cloud.</p>
               <p class="text-muted small">Proses ini mungkin memakan waktu beberapa menit tergantung jumlah data.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-cloud-upload-fill me-1"></i> Ya, Kirim Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#f97316',
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = document.getElementById('btn-full-sync');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim data...';
            }
            
            Swal.fire({
                title: 'Sedang Mengirim Data...',
                html: '<p class="text-muted">Mohon tunggu, jangan tutup halaman ini.</p><div class="progress mt-3"><div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 100%"></div></div>',
                allowOutsideClick: false,
                showConfirmButton: false,
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/firebase/sync-all', {
                method: 'POST',
                headers: { 
                    'X-CSRF-Token': csrfToken,
                    'Content-Type': 'application/json' 
                }
            })
            .then(r => r.json())
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cloud-upload-fill me-2"></i>Kirim Semua Data ke Firebase';
                }
                
                if (res.success) {
                    Swal.fire({
                        title: 'Berhasil! 🎉',
                        html: `<div class="text-start">
                            <table class="table table-sm table-bordered mt-3">
                                <tr><td>👤 Santri</td><td class="fw-bold">${res.santri || 0} data</td></tr>
                                <tr><td>📄 Paspor</td><td class="fw-bold">${res.paspor || 0} data</td></tr>
                                <tr><td>📋 ITAS</td><td class="fw-bold">${res.itas || 0} data</td></tr>
                                <tr><td>👥 Users</td><td class="fw-bold">${res.users || 0} data</td></tr>
                            </table>
                            <p class="text-muted small mt-2">Semua data berhasil dikirim ke Firebase Cloud!</p>
                        </div>`,
                        icon: 'success',
                    });
                    
                    // Update result panel
                    const resultDiv = document.getElementById('sync-result');
                    const contentDiv = document.getElementById('sync-result-content');
                    if (resultDiv && contentDiv) {
                        resultDiv.classList.remove('d-none');
                        contentDiv.innerHTML = `<strong>✅ Sync terakhir:</strong> ${new Date().toLocaleString('id-ID')} — ${res.santri || 0} santri, ${res.paspor || 0} paspor, ${res.itas || 0} ITAS, ${res.users || 0} users`;
                    }
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat sync', 'error');
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cloud-upload-fill me-2"></i>Kirim Semua Data ke Firebase';
                }
                Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
            });
        }
    });
}
</script>

<script>
function triggerDatabaseMigration() {
    Swal.fire({
        title: 'Suntik Perubahan Database?',
        html: `<p class="text-muted">Proses ini akan mengecek dan menerapkan perubahan struktur (kolom/tabel) terbaru ke database lokal Anda, seperti pada fitur Surat Generator.</p>
               <p class="text-danger small fw-bold">Pastikan tidak ada orang lain yang sedang mengedit data penting saat ini.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-database-up me-1"></i> Ya, Terapkan Perubahan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = document.getElementById('btn-db-migrate');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            }
            
            Swal.fire({
                title: 'Sedang Menerapkan Perubahan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('<?= API_URL ?>/api/pengaturan/migrate', {
                method: 'POST',
                headers: { 
                    'X-CSRF-Token': csrfToken,
                    'Content-Type': 'application/json' 
                }
            })
            .then(r => r.json())
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-lightning-fill me-2"></i>Suntik Database Baru';
                }
                
                if (res.success) {
                    Swal.fire({
                        title: 'Berhasil! 🎉',
                        text: res.message,
                        icon: 'success',
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat memproses database', 'error');
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-lightning-fill me-2"></i>Suntik Database Baru';
                }
                Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
            });
        }
    });
}
</script>

<script>
// ITAS Parser Management State
let itasConfigState = <?= json_encode($itasConfig, JSON_UNESCAPED_UNICODE) ?>;
let modalItasProfileInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    modalItasProfileInstance = new bootstrap.Modal(document.getElementById('modalItasProfile'));
    renderItasProfiles();
    
    // Auto expand if URL hash is #collapseItasParser
    if (window.location.hash === '#collapseItasParser' || window.location.hash === '#cardItasParser') {
        const collapseEl = document.getElementById('collapseItasParser');
        if (collapseEl) {
            new bootstrap.Collapse(collapseEl, { show: true });
            setTimeout(() => {
                collapseEl.scrollIntoView({ behavior: 'smooth' });
            }, 300);
        }
    }
});

function onItasModeChange() {
    const isAuto = document.getElementById('mode_auto').checked;
    const select = document.getElementById('itas_active_profile_select');
    const badge = document.getElementById('itas-mode-badge');
    
    select.disabled = isAuto;
    if (isAuto) {
        itasConfigState.mode = 'auto';
        badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle ms-2';
        badge.textContent = '⚡ Auto-Detect Aktif';
    } else {
        itasConfigState.mode = 'manual';
        itasConfigState.active_profile_id = select.value;
        badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle ms-2';
        badge.textContent = '🎯 Format Terkunci';
    }
}

document.getElementById('itas_active_profile_select')?.addEventListener('change', function() {
    itasConfigState.active_profile_id = this.value;
});

document.getElementById('itas_fallback_passport')?.addEventListener('change', function() {
    itasConfigState.fallback_passport_match = this.checked;
});

function renderItasProfiles() {
    const container = document.getElementById('itas-profiles-container');
    if (!container) return;

    const select = document.getElementById('itas_active_profile_select');
    if (select) {
        select.innerHTML = '';
    }

    if (!itasConfigState.profiles || itasConfigState.profiles.length === 0) {
        container.innerHTML = `<div class="col-12"><div class="alert alert-warning">Belum ada template format ITAS yang didaftarkan.</div></div>`;
        return;
    }

    let html = '';
    itasConfigState.profiles.forEach((prof, idx) => {
        const isEnabled = prof.enabled !== false;
        const isSystem = !!prof.is_system;
        
        if (select) {
            const opt = document.createElement('option');
            opt.value = prof.id;
            opt.textContent = prof.name + (!isEnabled ? ' (Nonaktif)' : '');
            opt.selected = (itasConfigState.active_profile_id === prof.id);
            select.appendChild(opt);
        }

        const kwBadges = (prof.identifier_keywords || []).map(k => `<span class="badge bg-secondary-subtle text-dark border me-1 mb-1 font-monospace" style="font-size:0.7rem;">${escapeHtml(k)}</span>`).join('');

        html += `
        <div class="col-md-6">
            <div class="card h-100 border ${isEnabled ? 'border-primary border-opacity-25' : 'border-secondary bg-light opacity-75'} rounded-4 shadow-sm">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1 ${isEnabled ? 'text-dark' : 'text-muted'}">${escapeHtml(prof.name)}</h6>
                                <div class="d-flex gap-2 align-items-center">
                                    ${isSystem ? '<span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:0.65rem;">Sistem Bawaan</span>' : '<span class="badge bg-primary-subtle text-primary border" style="font-size:0.65rem;">Kustom</span>'}
                                    <span class="badge ${isEnabled ? 'bg-success-subtle text-success' : 'bg-secondary text-white'}" style="font-size:0.65rem;">
                                        ${isEnabled ? 'Aktif' : 'Nonaktif'}
                                    </span>
                                </div>
                            </div>
                            <div class="form-check form-switch ms-2">
                                <input class="form-check-input" type="checkbox" role="switch" ${isEnabled ? 'checked' : ''} onchange="toggleProfileStatus('${escapeHtml(prof.id)}', this.checked)" title="Aktif/Nonaktifkan Format Ini">
                            </div>
                        </div>
                        <p class="text-muted small mb-2" style="font-size:0.8rem; line-height:1.4;">${escapeHtml(prof.description || 'Format ITAS')}</p>
                        
                        <div class="bg-light p-2 rounded-3 border mb-2 small" style="font-size:0.75rem;">
                            <div class="fw-bold text-secondary mb-1">Kata Kunci Pengenal:</div>
                            <div class="d-flex flex-wrap">${kwBadges || '<span class="text-muted fst-italic">Tanpa filter keyword (General)</span>'}</div>
                        </div>

                        <div class="small text-muted mb-3" style="font-size:0.75rem;">
                            <div><strong>Metode Nama:</strong> ${prof.name_mode === 'regex' ? 'Regex Label' : 'Baris Judul Atas (Top Line)'}</div>
                            <div><strong>Pola Permit:</strong> <code class="text-dark">${escapeHtml(prof.permit_regex || '-')}</code></div>
                            <div><strong>Pola Expiry:</strong> <code class="text-dark">${escapeHtml(prof.expiry_regex || '-')}</code></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill flex-fill fw-bold" onclick="openEditProfileModal('${escapeHtml(prof.id)}')">
                            <i class="bi bi-pencil-square me-1"></i> Edit Aturan
                        </button>
                        ${!isSystem ? `
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="deleteProfile('${escapeHtml(prof.id)}')">
                            <i class="bi bi-trash"></i>
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
        `;
    });

    container.innerHTML = html;
}

function toggleProfileStatus(id, isEnabled) {
    const prof = itasConfigState.profiles.find(p => p.id === id);
    if (prof) {
        prof.enabled = isEnabled;
        renderItasProfiles();
    }
}

function toggleNameRegexField() {
    const mode = document.getElementById('edit_prof_name_mode').value;
    const wrap = document.getElementById('wrap_name_regex');
    if (mode === 'regex') {
        wrap.classList.remove('d-none');
    } else {
        wrap.classList.add('d-none');
    }
}

function openAddProfileModal() {
    document.getElementById('modalItasProfileTitle').innerHTML = '<i class="bi bi-plus-circle text-primary me-2"></i>Tambah Template Format ITAS Baru';
    document.getElementById('edit_prof_id').value = '';
    document.getElementById('edit_prof_name').value = '';
    document.getElementById('edit_prof_enabled').value = '1';
    document.getElementById('edit_prof_desc').value = '';
    document.getElementById('edit_prof_keywords').value = '';
    document.getElementById('edit_prof_name_mode').value = 'top_line';
    document.getElementById('edit_prof_name_regex').value = '/(?:Full\\s*Name|Nama\\s*Lengkap)\\s*:\\s*([^\\n\\r]+)/i';
    document.getElementById('edit_prof_permit_regex').value = '/(?:PERMIT\\s+NUMBER|Permit\\s+Number|NIORA)\\s*:\\s*([A-Z0-9\\-]+)/i';
    document.getElementById('edit_prof_expiry_regex').value = '/(?:STAY\\s+PERMIT\\s+EXPIRY|Stay\\s+Permit\\s+Expiry|Permit\\s+Expiry)\\s*:\\s*(\\d{2}[\\/\\-\\.]\\d{2}[\\/\\-\\.]\\d{4})/i';
    document.getElementById('edit_prof_passport_regex').value = '/(?:TRAVEL\\s+DOC(?:UMENT)?(?:\\s+NUMBER|\\s+NO\\.?)?|PASSPORT(?:\\s+NUMBER|\\s+NO\\.?)?|NO(?:MOR)?\\.?\\s*(?:DOKUMEN\\s+PERJALANAN|PASPOR)|DOKUMEN\\s+PERJALANAN|PASPOR)(?:[\\/\\s]+(?:NOMOR|NUMBER|DOKUMEN|PERJALANAN|PASPOR|PASSPORT|NO\\.?))*\\s*[:=]\\s*[\\r\\n]*\\s*[:=]?\\s*([A-Z0-9\\-]+)/i';
    
    // Reset wizard sampel
    document.getElementById('sampleDetectionResult').classList.add('d-none');
    document.getElementById('sampleUploadPlaceholder').classList.remove('d-none');
    document.getElementById('sampleAnalyzingSpinner').classList.add('d-none');
    document.getElementById('samplePdfUploadInput').value = '';

    toggleNameRegexField();
    modalItasProfileInstance.show();
}

function openEditProfileModal(id) {
    const prof = itasConfigState.profiles.find(p => p.id === id);
    if (!prof) return;

    document.getElementById('modalItasProfileTitle').innerHTML = '<i class="bi bi-sliders text-primary me-2"></i>Edit Aturan: ' + escapeHtml(prof.name);
    document.getElementById('edit_prof_id').value = prof.id;
    document.getElementById('edit_prof_name').value = prof.name || '';
    document.getElementById('edit_prof_enabled').value = prof.enabled !== false ? '1' : '0';
    document.getElementById('edit_prof_desc').value = prof.description || '';
    document.getElementById('edit_prof_keywords').value = (prof.identifier_keywords || []).join(', ');
    document.getElementById('edit_prof_name_mode').value = prof.name_mode || 'top_line';
    document.getElementById('edit_prof_name_regex').value = prof.name_regex || '';
    document.getElementById('edit_prof_permit_regex').value = prof.permit_regex || '';
    document.getElementById('edit_prof_expiry_regex').value = prof.expiry_regex || '';
    document.getElementById('edit_prof_passport_regex').value = prof.passport_regex || '';

    // Reset wizard sampel
    document.getElementById('sampleDetectionResult').classList.add('d-none');
    document.getElementById('sampleUploadPlaceholder').classList.remove('d-none');
    document.getElementById('sampleAnalyzingSpinner').classList.add('d-none');
    document.getElementById('samplePdfUploadInput').value = '';

    toggleNameRegexField();
    modalItasProfileInstance.show();
}

function runAutoAnalyzeSample(file) {
    if (!file) return;
    if (!file.name.toLowerCase().endsWith('.pdf')) {
        Swal.fire('File Tidak Valid', 'Silakan pilih file PDF sampel ITAS.', 'warning');
        return;
    }

    const placeholder = document.getElementById('sampleUploadPlaceholder');
    const spinner = document.getElementById('sampleAnalyzingSpinner');
    const resultDiv = document.getElementById('sampleDetectionResult');

    placeholder.classList.add('d-none');
    spinner.classList.remove('d-none');
    resultDiv.classList.add('d-none');

    const formData = new FormData();
    formData.append('sample_pdf', file);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('<?= API_URL ?>/api/pengaturan/itas-parser/analyze-sample', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        spinner.classList.add('d-none');
        placeholder.classList.remove('d-none');

        if (!res.success) {
            Swal.fire('Analisis Gagal', res.message || res.error || 'Gagal membaca teks dari PDF sampel.', 'error');
            return;
        }

        const det = res.detected;
        
        // Update Form Fields Otomatis
        if (det.name_template) {
            document.getElementById('edit_prof_name').value = det.name_template;
        }
        if (det.description) {
            document.getElementById('edit_prof_desc').value = det.description;
        }
        if (det.identifier_keywords_str) {
            document.getElementById('edit_prof_keywords').value = det.identifier_keywords_str;
        }
        if (det.name_mode) {
            document.getElementById('edit_prof_name_mode').value = det.name_mode;
            toggleNameRegexField();
        }
        if (det.name_regex) {
            document.getElementById('edit_prof_name_regex').value = det.name_regex;
        }
        if (det.permit_regex) {
            document.getElementById('edit_prof_permit_regex').value = det.permit_regex;
        }
        if (det.expiry_regex) {
            document.getElementById('edit_prof_expiry_regex').value = det.expiry_regex;
        }
        if (det.passport_regex) {
            document.getElementById('edit_prof_passport_regex').value = det.passport_regex;
        }

        // Tampilkan Visual Preview Hasil Deteksi
        document.getElementById('sampleFileName').textContent = file.name;
        document.getElementById('det_no_itas').textContent = det.sample_permit || '(Tidak Ditemukan)';
        document.getElementById('det_exp_itas').textContent = det.sample_expiry || '(Tidak Ditemukan)';
        document.getElementById('det_nama').textContent = det.sample_name || '(Tidak Ditemukan)';
        document.getElementById('det_paspor').textContent = det.sample_passport || '(Opsional / -)';
        
        resultDiv.classList.remove('d-none');

        // Notifikasi Cepat
        Swal.fire({
            icon: 'success',
            title: 'Sampel Berhasil Dianalisis! ✨',
            text: 'Semua pola regex dan nama template telah diisi secara otomatis.',
            timer: 2000,
            showConfirmButton: false
        });
    })
    .catch(err => {
        spinner.classList.add('d-none');
        placeholder.classList.remove('d-none');
        Swal.fire('Error', 'Gagal memproses file sampel: ' + err.message, 'error');
    });
}

function saveProfileModal() {
    const id = document.getElementById('edit_prof_id').value;
    const name = document.getElementById('edit_prof_name').value.trim();
    if (!name) {
        Swal.fire('Perhatian', 'Nama template tidak boleh kosong.', 'warning');
        return;
    }

    const enabled = document.getElementById('edit_prof_enabled').value === '1';
    const desc = document.getElementById('edit_prof_desc').value.trim();
    const kwStr = document.getElementById('edit_prof_keywords').value.trim();
    const keywords = kwStr ? kwStr.split(',').map(s => s.trim()).filter(s => s !== '') : [];
    const nameMode = document.getElementById('edit_prof_name_mode').value;
    const nameRegex = document.getElementById('edit_prof_name_regex').value.trim();
    const permitRegex = document.getElementById('edit_prof_permit_regex').value.trim();
    const expiryRegex = document.getElementById('edit_prof_expiry_regex').value.trim();
    const passportRegex = document.getElementById('edit_prof_passport_regex').value.trim();

    if (!permitRegex || !expiryRegex) {
        Swal.fire('Perhatian', 'Regex Nomor ITAS dan Expiry Date wajib diisi.', 'warning');
        return;
    }

    if (id) {
        // Edit existing
        const prof = itasConfigState.profiles.find(p => p.id === id);
        if (prof) {
            prof.name = name;
            prof.enabled = enabled;
            prof.description = desc;
            prof.identifier_keywords = keywords;
            prof.name_mode = nameMode;
            prof.name_regex = nameRegex;
            prof.permit_regex = permitRegex;
            prof.expiry_regex = expiryRegex;
            prof.passport_regex = passportRegex;
        }
    } else {
        // Create new
        const newId = 'itas_custom_' + Date.now();
        itasConfigState.profiles.push({
            id: newId,
            name: name,
            is_system: false,
            enabled: enabled,
            description: desc,
            identifier_keywords: keywords,
            name_mode: nameMode,
            name_regex: nameRegex,
            permit_regex: permitRegex,
            expiry_regex: expiryRegex,
            passport_regex: passportRegex
        });
    }

    modalItasProfileInstance.hide();
    renderItasProfiles();
    Swal.fire({
        icon: 'success',
        title: 'Aturan Diterapkan',
        text: 'Jangan lupa klik tombol "Simpan Konfigurasi ITAS" di bawah untuk menyimpan permanen.',
        timer: 2000,
        showConfirmButton: false
    });
}

function deleteProfile(id) {
    Swal.fire({
        title: 'Hapus Template Ini?',
        text: 'Template format ITAS ini akan dihapus dari daftar.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545'
    }).then((r) => {
        if (r.isConfirmed) {
            itasConfigState.profiles = itasConfigState.profiles.filter(p => p.id !== id);
            renderItasProfiles();
        }
    });
}

function runLiveTestParser(file) {
    if (!file) return;
    if (!file.name.toLowerCase().endsWith('.pdf')) {
        Swal.fire('File Tidak Valid', 'Silakan pilih file PDF.', 'warning');
        return;
    }

    const resultBox = document.getElementById('testResultBox');
    resultBox.classList.remove('d-none');
    resultBox.innerHTML = `
        <div class="bg-white p-3 rounded-3 border text-center shadow-sm">
            <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
            <span class="fw-bold text-dark">Menganalisis file "${escapeHtml(file.name)}"...</span>
        </div>
    `;

    const formData = new FormData();
    formData.append('test_pdf', file);
    formData.append('config', JSON.stringify(itasConfigState));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('<?= API_URL ?>/api/pengaturan/itas-parser/test', {
        method: 'POST',
        headers: { 'X-CSRF-Token': csrfToken },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            resultBox.innerHTML = `
                <div class="alert alert-danger rounded-3 border-0 shadow-sm">
                    <strong><i class="bi bi-x-circle-fill me-2"></i>Gagal Memproses PDF:</strong> ${escapeHtml(res.message || 'Error')}
                </div>
            `;
            return;
        }

        const data = res.result;
        const santri = data.matched_santri;
        const logsHtml = (data.logs || []).map(l => `<div class="font-monospace" style="font-size:0.75rem;">${escapeHtml(l)}</div>`).join('');

        let santriMatchHtml = '';
        if (santri) {
            santriMatchHtml = `
                <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-3">
                    <div class="d-flex align-items-center gap-2 text-success fw-bold">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span>SANTRI BERHASIL DICOCOKKAN DI DATABASE!</span>
                    </div>
                    <div class="row mt-2 small text-dark">
                        <div class="col-sm-6"><strong>Nama di DB:</strong> ${escapeHtml(santri.nama)}</div>
                        <div class="col-sm-6"><strong>KDS / Stambuk:</strong> ${escapeHtml(santri.kds)} / ${escapeHtml(santri.stambuk || '-')}</div>
                        <div class="col-sm-6"><strong>Kepengurusan:</strong> ${escapeHtml(santri.kepengurusan || '-')}</div>
                        <div class="col-sm-6"><strong>Metode Cocok:</strong> <span class="badge bg-success">${escapeHtml(data.match_method)}</span></div>
                    </div>
                </div>
            `;
        } else {
            santriMatchHtml = `
                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 mb-3">
                    <div class="d-flex align-items-center gap-2 text-warning-emphasis fw-bold">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span>TIDAK COCOK DENGAN SANTRI DI DATABASE</span>
                    </div>
                    <div class="small text-muted mt-1">Data berhasil diekstrak dari PDF, namun nama <code>"${escapeHtml(data.extracted_name)}"</code> / No Paspor <code>"${escapeHtml(data.no_paspor)}"</code> tidak ditemukan pada tabel <code>master_santri</code>.</div>
                </div>
            `;
        }

        resultBox.innerHTML = `
            <div class="bg-white p-4 rounded-3 border shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-dark">Hasil Analisis: <span class="text-primary">${escapeHtml(file.name)}</span></h6>
                    <span class="badge bg-primary px-3 py-2 rounded-pill">Format Terdeteksi: ${escapeHtml(data.matched_profile_name || 'Tidak Cocok')}</span>
                </div>

                ${santriMatchHtml}

                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Field Dokumen</th>
                                <th>Nilai Hasil Ekstraksi Mesin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Nama Santri</td>
                                <td><span class="fw-bold text-dark">${escapeHtml(data.extracted_name || '-')}</span></td>
                                <td>${data.extracted_name ? '<span class="text-success fw-bold">✓ Terdeteksi</span>' : '<span class="text-danger">✗ Kosong</span>'}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Nomor ITAS (Permit No)</td>
                                <td><code class="text-dark">${escapeHtml(data.no_itas || '-')}</code></td>
                                <td>${data.no_itas ? '<span class="text-success fw-bold">✓ Terdeteksi</span>' : '<span class="text-danger">✗ Kosong</span>'}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Masa Berlaku (Expiry Date)</td>
                                <td><span class="badge bg-info-subtle text-info border">${escapeHtml(data.exp_itas || '-')}</span></td>
                                <td>${data.exp_itas ? '<span class="text-success fw-bold">✓ Terdeteksi</span>' : '<span class="text-danger">✗ Kosong</span>'}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Nomor Paspor (Fallback)</td>
                                <td><code class="text-dark">${escapeHtml(data.no_paspor || '-')}</code></td>
                                <td>${data.no_paspor ? '<span class="text-success fw-bold">✓ Terdeteksi</span>' : '<span class="text-muted">-</span>'}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="accordion" id="accTestLogs">
                    <div class="accordion-item border rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogs">
                                <i class="bi bi-terminal me-2"></i> Lihat Log Pemrosesan Lengkap & Teks Mentah PDF
                            </button>
                        </h2>
                        <div id="collapseLogs" class="accordion-collapse collapse">
                            <div class="accordion-body p-3 bg-dark text-light rounded-bottom">
                                <div class="fw-bold text-warning mb-1" style="font-size:0.8rem;">--- LOG EKSEKUSI ---</div>
                                ${logsHtml}
                                <div class="fw-bold text-warning mt-3 mb-1" style="font-size:0.8rem;">--- TEKS RAW PDF (Halaman 1) ---</div>
                                <pre class="text-white-50 small mb-0" style="max-height:200px; overflow-y:auto; white-space:pre-wrap;">${escapeHtml(data.raw_text || '')}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    })
    .catch(err => {
        resultBox.innerHTML = `
            <div class="alert alert-danger rounded-3 border-0 shadow-sm">
                <strong>Error Koneksi:</strong> ${escapeHtml(err.message)}
            </div>
        `;
    });
}

function saveItasParserConfig() {
    Swal.fire({
        title: 'Menyimpan Konfigurasi ITAS...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('<?= API_URL ?>/api/pengaturan/itas-parser/save', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ config: itasConfigState })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan! 🎉',
                text: res.message || 'Konfigurasi parser ITAS telah aktif dan diterapkan.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Gagal Menyimpan', res.message || 'Terjadi kesalahan.', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
    });
}

function resetItasConfigToDefault() {
    Swal.fire({
        title: 'Reset ke Standar Pabrik?',
        text: 'Semua aturan format ITAS akan dikembalikan ke template bawaan sistem (Ditjen Imigrasi Modern & Kemenkumham Klasik).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((r) => {
        if (r.isConfirmed) {
            itasConfigState = <?= json_encode(\App\Shared\ItasParserEngine::getDefaultConfig(), JSON_UNESCAPED_UNICODE) ?>;
            document.getElementById('mode_auto').checked = true;
            document.getElementById('itas_fallback_passport').checked = true;
            onItasModeChange();
            renderItasProfiles();
            saveItasParserConfig();
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

