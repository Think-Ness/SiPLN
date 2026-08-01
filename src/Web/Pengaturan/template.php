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
<!-- Database Migration / Update Section -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white p-4" data-bs-toggle="collapse" data-bs-target="#collapseMaintenance" style="cursor: pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-tools text-danger fs-5"></i>
                    <h5 class="fw-bold mb-0">Maintenance & Pembaruan Sistem</h5>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    Gunakan fitur di bawah ini hanya setelah melakukan pembaruan file source code aplikasi (Update Aplikasi).
                </p>
            </div>
            <i class="bi bi-chevron-expand fs-4 text-secondary"></i>
        </div>
    </div>
    <div id="collapseMaintenance" class="collapse border-top">
        <div class="card-body p-4 bg-white">
            <div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-3 border rounded-3">
                <div class="mb-3 mb-md-0 me-3">
                    <h6 class="fw-bold mb-1"><i class="bi bi-database-up text-primary me-2"></i>Suntik / Sinkronisasi Struktur Database</h6>
                    <p class="text-muted small mb-0" style="line-height: 1.5;">Terapkan perubahan skema database terbaru secara otomatis (misalnya penambahan kolom <code>instansi_tujuan</code> pada fitur Surat Generator / Pengaturan Template). Cukup tekan sekali setelah mengupdate aplikasi.</p>
                </div>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm flex-shrink-0" onclick="triggerDatabaseMigration()" id="btn-db-migrate">
                    <i class="bi bi-lightning-fill me-2"></i>Suntik Database Baru
                </button>
            </div>
        </div>
    </div>
</div>
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
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim data...';
            
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
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cloud-upload-fill me-2"></i>Kirim Semua Data ke Firebase';
                
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
                    resultDiv.classList.remove('d-none');
                    contentDiv.innerHTML = `<strong>✅ Sync terakhir:</strong> ${new Date().toLocaleString('id-ID')} — ${res.santri || 0} santri, ${res.paspor || 0} paspor, ${res.itas || 0} ITAS, ${res.users || 0} users`;
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat sync', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cloud-upload-fill me-2"></i>Kirim Semua Data ke Firebase';
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
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            
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
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lightning-fill me-2"></i>Suntik Database Baru';
                
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
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lightning-fill me-2"></i>Suntik Database Baru';
                Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
            });
        }
    });
}
</script>
