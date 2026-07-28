<?php
$root = dirname(__DIR__);
if (!file_exists($root . '/config/installed.lock')) {
    header('Location: install.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan SiPLN (Guidebook)</title>
    <!-- Offline Bootstrap 5 -->
    <link href="assets/offline/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/offline/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background-color: #f8f9fa; position: relative; }
        .sidebar { position: sticky; top: 80px; height: calc(100vh - 100px); overflow-y: auto; }
        .sidebar nav .nav-link { color: #495057; padding: 0.25rem 1rem; font-size: 0.9rem; border-left: 2px solid transparent; }
        .sidebar nav .nav-link.active { color: #0d6efd; font-weight: 600; border-left-color: #0d6efd; background-color: rgba(13,110,253,0.05); }
        .sidebar nav .nav-link:hover { color: #0d6efd; background-color: rgba(13,110,253,0.05); }
        .sidebar nav .nav-link.level-3 { padding-left: 2rem; font-size: 0.85rem; }
        .content-area { background: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05); }
        .content-area h1, .content-area h2, .content-area h3 { color: #212529; margin-top: 2rem; margin-bottom: 1rem; font-weight: 700; }
        .content-area h2 { padding-bottom: 0.5rem; border-bottom: 1px solid #dee2e6; }
        .content-area img { max-width: 100%; height: auto; border-radius: 0.5rem; border: 1px solid #dee2e6; margin: 1rem 0; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05); }
        .content-area table { width: 100%; margin-bottom: 1rem; color: #212529; border-collapse: collapse; }
        .content-area table th, .content-area table td { padding: 0.75rem; vertical-align: top; border-top: 1px solid #dee2e6; }
        .content-area table thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; background-color: #f8f9fa; }
        .content-area table tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,0.02); }
        .content-area pre { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; border: 1px solid #dee2e6; overflow-x: auto; }
        .content-area code { color: #d63384; font-size: 0.875em; word-wrap: break-word; }
        .content-area pre code { color: #212529; }
        .content-area blockquote { padding: 1rem; margin: 1rem 0; background-color: #fff3cd; border-left: 5px solid #ffc107; border-radius: 0.25rem; }
        
        .simulation-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1.5rem; margin: 1.5rem 0; }
        .simulation-title { font-size: 0.9rem; font-weight: bold; text-transform: uppercase; color: #6c757d; letter-spacing: 1px; margin-bottom: 1rem; }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#toc-sidebar" data-bs-offset="100" tabindex="0">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-book-half me-2"></i> Panduan SiPLN</a>
        <div class="d-flex">
            <a href="index.php" class="btn btn-light btn-sm fw-bold"><i class="bi bi-box-arrow-left me-1"></i> Kembali ke Beranda</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4 px-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 d-none d-md-block">
            <div class="sidebar bg-white p-3 rounded-4 shadow-sm border">
                <h6 class="text-uppercase fw-bold text-muted mb-3 px-3">Daftar Isi</h6>
                <nav id="toc-sidebar" class="nav nav-pills flex-column"></nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div id="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat panduan...</p>
            </div>
            
            <div id="content" class="content-area d-none"></div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/offline/js/bootstrap.bundle.min.js"></script>
<script src="assets/offline/js/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch and render markdown
    fetch('assets/docs/GUIDEBOOK_SiPLN.md')
        .then(response => response.text())
        .then(text => {
            // Render markdown using marked.js
            const html = marked.parse(text);
            const contentDiv = document.getElementById('content');
            contentDiv.innerHTML = html;
            
            // Generate Table of Contents
            const toc = document.getElementById('toc-sidebar');
            const headings = contentDiv.querySelectorAll('h2, h3');
            
            headings.forEach((heading, index) => {
                // Ignore "DAFTAR ISI" heading
                if (heading.textContent.trim().toUpperCase() === 'DAFTAR ISI') {
                    heading.style.display = 'none'; // hide in content too
                    return;
                }
                
                // Create unique ID for the heading
                const id = 'heading-' + index;
                heading.id = id;
                
                // Create nav link
                const link = document.createElement('a');
                link.className = 'nav-link' + (heading.tagName === 'H3' ? ' level-3' : '');
                link.href = '#' + id;
                link.textContent = heading.textContent;
                
                toc.appendChild(link);
            });
            
            // Format elements for Bootstrap
            contentDiv.querySelectorAll('table').forEach(table => {
                table.classList.add('table', 'table-bordered', 'table-hover');
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });
            
            // Inject Simulations
            injectSimulations(contentDiv);
            
            // Hide loading, show content
            document.getElementById('loading').classList.add('d-none');
            contentDiv.classList.remove('d-none');
            
            // Initialize scrollspy (must do it after DOM is fully populated)
            var scrollSpy = new bootstrap.ScrollSpy(document.body, {
                target: '#toc-sidebar',
                offset: 120
            });
        })
        .catch(err => {
            document.getElementById('loading').innerHTML = '<div class="alert alert-danger">Gagal memuat panduan. Pastikan file GUIDEBOOK_SiPLN.md ada.</div>';
            console.error(err);
        });
        
    function injectSimulations(container) {
        const headings = container.querySelectorAll('h3');
        
        headings.forEach(h => {
            // Simulasi Aksi Massal
            if (h.textContent.includes('Aksi Massal')) {
                const simHtml = `
                <div class="simulation-box shadow-sm">
                    <div class="simulation-title"><i class="bi bi-play-circle-fill text-primary"></i> Simulasi: Tombol Aksi Massal (Loading State)</div>
                    <p class="small text-muted mb-3">Cobalah klik tombol "Nonaktifkan Terpilih" di bawah ini untuk melihat indikator loading saat proses berlangsung.</p>
                    <div class="d-flex gap-2">
                        <button id="sim-btn-massal" class="btn btn-warning fw-bold"><i class="bi bi-archive"></i> Nonaktifkan Terpilih</button>
                    </div>
                </div>`;
                h.insertAdjacentHTML('afterend', simHtml);
                
                document.getElementById('sim-btn-massal').addEventListener('click', function(e) {
                    const btn = e.target.closest('button');
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
                    btn.disabled = true;
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        alert('Simulasi: Berhasil menonaktifkan data santri secara massal.');
                    }, 2000);
                });
            }
            
            // Simulasi Surat Generator
            if (h.textContent.includes('Cara Kerja')) {
                // Only if inside the Surat Generator section
                const simHtml = `
                <div class="simulation-box shadow-sm">
                    <div class="simulation-title"><i class="bi bi-play-circle-fill text-primary"></i> Simulasi: Pilih Jenis Surat</div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Jenis Pengajuan</label>
                            <select class="form-select" id="sim-jenis-surat">
                                <option value="">-- Pilih --</option>
                                <option value="sp">Surat Permohonan ITAS</option>
                                <option value="sj">Surat Jaminan</option>
                                <option value="st">Surat Tugas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tujuan Instansi</label>
                            <select class="form-select" id="sim-tujuan">
                                <option value="Imigrasi Kairo">Imigrasi Kairo</option>
                                <option value="Imigrasi Alexandria">Imigrasi Alexandria</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button id="sim-btn-surat" class="btn btn-primary w-100 fw-bold"><i class="bi bi-file-earmark-word"></i> Generate Word</button>
                        </div>
                    </div>
                    <div id="sim-surat-result" class="mt-3 text-success fw-bold d-none">
                        <i class="bi bi-check-circle-fill"></i> File Surat_Permohonan_Kairo.docx berhasil di-generate!
                    </div>
                </div>`;
                h.insertAdjacentHTML('afterend', simHtml);
                
                document.getElementById('sim-btn-surat').addEventListener('click', function(e) {
                    const jenis = document.getElementById('sim-jenis-surat').value;
                    if (!jenis) {
                        alert('Silakan pilih jenis pengajuan terlebih dahulu.');
                        return;
                    }
                    
                    const btn = e.target.closest('button');
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Generating...';
                    btn.disabled = true;
                    document.getElementById('sim-surat-result').classList.add('d-none');
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        document.getElementById('sim-surat-result').classList.remove('d-none');
                    }, 1500);
                });
            }
        });
    }
});
</script>
</body>
</html>
