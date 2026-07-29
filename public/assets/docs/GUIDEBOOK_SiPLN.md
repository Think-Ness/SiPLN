# 📘 GUIDEBOOK — Sistem Informasi Pondok Luar Negeri (SiPLN)

**Versi Dokumen:** 1.0  
**Terakhir Diperbarui:** 26 Juli 2026  
**Untuk:** Seluruh Staf dan Admin Pondok Cabang

---

## DAFTAR ISI

1. [Pendahuluan](#1-pendahuluan)
2. [Instalasi & Persiapan Awal](#2-instalasi--persiapan-awal)
3. [Login & Navigasi Umum](#3-login--navigasi-umum)
4. [Dashboard Statistik](#4-dashboard-statistik)
5. [Auto Rekap (Perpanjangan ITAS & Paspor)](#5-auto-rekap)
6. [Job Desk (Manajemen Proses Birokrasi)](#6-job-desk)
7. [Pengeluaran Operasional](#7-pengeluaran-operasional)
8. [Pemberkasan Dokumen](#8-pemberkasan-dokumen)
9. [Data Santri (Master Data)](#9-data-santri)
10. [Data Santri Inaktif](#10-data-santri-inaktif)
11. [Persetujuan Usulan Data (Request Edit)](#11-persetujuan-usulan-data)
12. [Pendaftaran CAPEL (Calon Pelajar)](#12-pendaftaran-capel)
13. [Import Data dari Excel](#13-import-data-dari-excel)
14. [Generator Surat Resmi](#14-generator-surat-resmi)
15. [Anggaran Operasional](#15-anggaran-operasional)
16. [Menu Print (Cetak Laporan)](#16-menu-print)
17. [Manajemen Paspor](#17-manajemen-paspor)
18. [Kalender Expiry](#18-kalender-expiry)
19. [Anggota Kamar / Asrama](#19-anggota-kamar)
20. [Profil Instansi](#20-profil-instansi)
21. [Pengaturan Job Desk](#21-pengaturan-job-desk)
22. [Manajemen Instansi (Super Admin)](#22-manajemen-instansi)
23. [Manajemen Pengguna (User)](#23-manajemen-pengguna)
24. [Pengaturan Sistem](#24-pengaturan-sistem)
25. [Log Aktivitas (Audit Log)](#25-log-aktivitas)
26. [Firebase Cloud Sync & Monitoring](#26-firebase-cloud-sync--monitoring)
27. [Tips & Trik Penting](#27-tips--trik-penting)
28. [FAQ (Pertanyaan Umum)](#28-faq)

**LAMPIRAN**

29. [Daftar Tingkatan Kelas Santri](#lampiran-a-daftar-tingkatan-kelas-santri)
30. [Peta Penyimpanan File & Upload](#lampiran-b-peta-penyimpanan-file--upload)
31. [Istilah & Singkatan Penting](#lampiran-c-istilah--singkatan-penting)

---

## 1. Pendahuluan

**SiPLN (Sistem Informasi Pondok Luar Negeri)** adalah aplikasi web berbasis PHP yang dirancang khusus untuk mengelola data santri pondok pesantren yang sedang menempuh pendidikan di luar negeri. Aplikasi ini membantu mengelola:

- **Data Santri** — biodata, status ITAS/Paspor, kewarganegaraan, dan dokumen terkait.
- **Proses Birokrasi** — perpanjangan ITAS, pembuatan paspor, dan proses keimigrasian lainnya.
- **Surat Menyurat** — pembuatan surat resmi secara otomatis (Surat Permohonan, Keterangan, Jaminan, Tugas).
- **Keuangan** — pencatatan pengeluaran operasional dan pengajuan anggaran.
- **Pemberkasan** — penyimpanan dan pengelolaan berkas/dokumen digital.
- **Sinkronisasi Cloud** — data lokal disinkronkan ke Firebase untuk pemantauan jarak jauh secara real-time.

### Arsitektur Sistem

```
┌──────────────────────────────────────────────────────────────┐
│                    SiPLN Architecture                        │
│                                                              │
│  ┌──────────────┐       ┌──────────────┐                     │
│  │ PC Pondok    │       │ Firebase     │                     │
│  │ Cabang       │◄─────►│ Cloud Sync   │                     │
│  │ (Localhost)  │       │ (Monitoring) │                     │
│  └──────────────┘       └──────────────┘                     │
│         │                       ▲                            │
│         ▼                       │                            │
│  ┌──────────────┐       ┌──────────────┐                     │
│  │ MySQL DB     │       │ Super Admin  │                     │
│  │ (Data Lokal) │       │ (Dashboard)  │                     │
│  └──────────────┘       └──────────────┘                     │
└──────────────────────────────────────────────────────────────┘
```

---

## 2. Instalasi & Persiapan Awal

### 2.1 Persyaratan Minimum Sistem

Sebelum memasang SiPLN, pastikan komputer Anda telah memenuhi persyaratan berikut:

| Komponen | Keterangan | Cara Mengaktifkan |
|---|---|---|
| **PHP >= 8.1** | Versi PHP yang terinstal di XAMPP/Laragon | Pasang versi terbaru XAMPP atau Laragon |
| **PDO MySQL** | Koneksi database MySQL | Biasanya sudah aktif secara bawaan |
| **cURL Extension** | Untuk koneksi Firebase | Aktifkan `extension=curl` di `php.ini` |
| **COM (com_dotnet)** | Untuk fitur Surat Generator (Khusus Windows) | Tambahkan `extension=com_dotnet` di `php.ini` |
| **MBString** | Pemrosesan teks multibahasa | Aktifkan `extension=mbstring` di `php.ini` |
| **GD / Image** | Upload dan olah foto santri | Aktifkan `extension=gd` di `php.ini` |
| **ZIP** | Ekstraksi dan kompresi file | Aktifkan `extension=zip` di `php.ini` |
| **Fileinfo** | Deteksi tipe file (MIME) | Aktifkan `extension=fileinfo` di `php.ini` |
| **Intl Extension** | Format tanggal dan angka | Aktifkan `extension=intl` di `php.ini` |

> **💡 Tips:** Di halaman instalasi, terdapat tombol **"Lihat Cara Mengaktifkan Persyaratan (XAMPP / Laragon)"** yang menyediakan panduan lengkap langkah-demi-langkah untuk setiap ekstensi PHP.

### 2.2 Langkah Instalasi

1. **Salin folder aplikasi** ke dalam folder `htdocs` pada XAMPP (misal: `C:\xampp\htdocs\WEBAPP`).
2. **Jalankan XAMPP** — Nyalakan Apache dan MySQL.
3. **Buat Database Kosong** — Buka phpMyAdmin (`http://localhost/phpmyadmin`), buat database baru misalnya `si_foreign_db`.
4. **Buka halaman Instalasi** — Akses `http://localhost/WEBAPP/public/install.php` dari browser.
5. Ikuti langkah-langkah *Wizard Instalasi*:
   - **Step 1 — Welcome**: Halaman pembuka, klik "Mulai Instalasi".
   - **Step 2 — Sistem**: Cek persyaratan PHP. Pastikan semua bercentang hijau.
   - **Step 3 — Database**: Masukkan kredensial MySQL (Host, Port, Nama DB, Username, Password). Klik "Test Koneksi" untuk memastikan terhubung.
   - **Step 4 — Aplikasi**: Atur nama aplikasi, zona waktu, dan pilih instansi.
   - **Step 5 — Admin**: Buat akun Admin Instansi pertama (Nama, Username, Password).
   - **Step 6 — Install**: Sistem akan otomatis membuat tabel, mengimpor data, dan menyelesaikan proses.
   - **Step 7 — Selesai**: Instalasi berhasil! Klik "Buka Aplikasi" untuk masuk.

---

## 3. Login & Navigasi Umum

### 3.1 Halaman Login

Akses aplikasi melalui browser: `http://localhost/WEBAPP/public/`

- Masukkan **Username** dan **Password** yang telah dibuat saat instalasi.
- Klik tombol **"Masuk"**.
- Jika lupa password, hubungi Admin atau Super Admin untuk direset.

### 3.2 Navigasi Sidebar (Menu Samping)

Setelah login, Anda akan melihat **Sidebar** di sisi kiri layar. Menu-menu dikelompokkan sebagai berikut:

| Grup Menu | Menu | Keterangan |
|---|---|---|
| **Menu Utama** | Dashboard | Halaman utama statistik santri |
| | Auto Rekap | Rekapitulasi perpanjangan ITAS & Paspor |
| | Job Desk | Proses birokrasi (kanban/pipeline) |
| | Pemberkasan | Upload & kelola berkas dokumen |
| **Master Data** | Data Santri | Database lengkap semua santri aktif |
| | Data Santri Inaktif | Arsip data santri yang sudah dinonaktifkan |
| | Persetujuan (Request) | Persetujuan perubahan data lintas instansi |
| **Master Print** | Generator Surat | Buat surat resmi otomatis (SP, SJ, SK, ST) |
| **Lain-lain** | Pengaturan Job Desk | Konfigurasi template langkah-langkah proses |
| | Profil Instansi | Kelola identitas pondok/instansi |
| | Anggota Kamar | Pembagian kamar/asrama santri |
| **Manajemen Sistem** | Manajemen Instansi | Kelola daftar pondok cabang (Super Admin) |
| | Manajemen Pengguna | Kelola akun dan hak akses pengguna |
| | Pengaturan Sistem | Konfigurasi Firebase, sinkronisasi, dll. |
| | Log Aktivitas | Rekam jejak semua aksi pengguna |

> **💡 Tips:** Sidebar bisa diciutkan (collapsed) dengan mengklik ikon **☰** di pojok kiri atas. Menu akan berubah menjadi ikon-ikon saja untuk menghemat ruang layar.

### 3.3 Header Bar (Bilah Atas)

Di bilah atas layar, Anda akan menemukan:
- **Status Koneksi** — "Online" (hijau) atau "Server Lokal / Offline" (abu-abu).
- **Nama Instansi** — Nama pondok yang sedang aktif.
- **Tombol Monitoring** — Membuka dashboard Firebase untuk pemantauan real-time.
- **Tombol Logout** — Keluar dari sistem.

---

## 4. Dashboard Statistik

Halaman utama yang muncul setelah login. Menampilkan ringkasan data santri dalam bentuk visual.

### 4.1 Kartu Statistik

Terdapat beberapa kartu informasi utama:
- **Santri Aktif** — Jumlah total santri yang masih aktif.
- **Santri Inaktif** — Jumlah santri yang sudah dinonaktifkan.
- **Exp. Paspor Segera** — Jumlah santri yang paspornya akan segera kedaluwarsa.
- **Exp. ITAS Segera** — Jumlah santri yang ITAS-nya akan segera kedaluwarsa.
- **CAPEL** — Jumlah calon pelajar yang mendaftar.
- **Alumni** — Jumlah santri yang sudah lulus.
- **Pengabdian** — Jumlah dan rincian santri yang berstatus pengabdian.

### 4.2 Filter Data

- **Filter Status**: Klik tab "Aktif", "Non Aktif", atau "Semua" untuk memfilter tampilan.
- **Filter Instansi**: Untuk pengguna dengan akses multi-instansi, tersedia toggle "Instansi Saya" dan "Semua".

### 4.3 Grafik & Visualisasi

Dashboard menampilkan grafik-grafik interaktif:
- Distribusi santri per negara tujuan.
- Status kedaluwarsa dokumen.
- Grafik tren waktu.

---

## 5. Auto Rekap

**Fungsi:** Membuat rekapitulasi otomatis data santri yang memerlukan perpanjangan ITAS atau Paspor.

### 5.1 Tab ITAS
Menampilkan daftar santri yang ITAS-nya perlu diperpanjang, dikelompokkan berdasarkan bulan dan tahun kedaluwarsa.

### 5.2 Tab Paspor
Menampilkan daftar santri yang paspornya perlu diperpanjang, dikelompokkan berdasarkan bulan dan tahun kedaluwarsa.

### 5.3 Fitur Print
- Klik tombol **"Print ITAS"** atau **"Print Paspor"** untuk mencetak rekap.
- Pilih format output yang diinginkan pada jendela pop-up.
- Hasil cetakan akan terbuka di tab baru browser dalam format siap cetak.

---

## 6. Job Desk

**Fungsi:** Mengelola seluruh proses birokrasi santri (seperti perpanjangan ITAS, pembuatan visa, dll.) menggunakan sistem pipeline/kanban.

### 6.1 Dashboard Job Desk

Menampilkan tampilan kanban dengan kartu-kartu proses:
- **Statistik Cepat** — Total proses aktif, selesai, dan *bottleneck* (langkah terlama).
- **Kartu Proses** — Setiap santri yang sedang diproses ditampilkan sebagai kartu. Kartu menunjukkan nama, jenis proses, dan progress berupa titik-titik langkah (*step dots*).
  - 🟢 Hijau = Langkah selesai
  - 🔵 Biru = Langkah sedang dikerjakan
  - 🔴 Merah = Langkah terhambat/blocked
  - ⚪ Abu-abu = Belum dimulai

### 6.2 Membuat Proses Baru

1. Klik tombol **"+ Tambah Proses"**.
2. Pilih santri dari daftar.
3. Pilih jenis proses (misal: "Perpanjangan ITAS").
4. Sistem akan otomatis membuatkan langkah-langkah sesuai template yang telah dikonfigurasi.
5. Klik **"Simpan"**.

### 6.3 Mengelola Langkah Proses

Klik pada kartu proses untuk membuka detail:
- **Pindahkan langkah** — Klik tombol centang di setiap langkah untuk menandainya selesai dan melanjutkan ke langkah berikutnya.
- **Tambah Catatan** — Klik ikon catatan pada setiap langkah untuk menambahkan keterangan. Catatan bisa berisi teks atau informasi tambahan.
- **Tandai Blocked** — Jika langkah terhambat, klik tombol blokir untuk menandainya.

### 6.4 Aksi Massal (Bulk)

- **Pilih beberapa santri** dengan mencentang *checkbox* di tabel.
- Tombol-tombol massal akan muncul:
  - **"Buka Proses"** — Membuat proses baru untuk beberapa santri sekaligus.
  - **"Nonaktifkan"** — Menonaktifkan beberapa santri sekaligus.
  - **"Edit Massal"** — Mengubah data beberapa santri sekaligus.

> **⚠️ Catatan:** Saat proses massal berjalan, layar akan menampilkan indikator *loading* ("Sedang memproses... Mohon tunggu, jangan tutup halaman ini."). Tunggu hingga selesai.

---

## 7. Pengeluaran Operasional

**Fungsi:** Mencatat semua pengeluaran operasional kepengurusan (biaya birokrasi, transportasi, dll.).

### 7.1 Mencatat Pengeluaran Baru

1. Klik tombol **"+ Catat Pengeluaran"**.
2. Isi formulir:
   - **Tanggal** — Tanggal transaksi.
   - **Kategori** — Pilih kategori (misal: Birokrasi, Transportasi, dll.).
   - **Nominal** — Jumlah uang yang dikeluarkan.
   - **Keterangan** — Deskripsi pengeluaran.
   - **Foto Nota/Struk (Opsional)** — Upload foto bukti pengeluaran (JPG, PNG, WebP, PDF, maks 5MB).
3. Klik **"Simpan"**.

### 7.2 Mengelola Kategori

- Klik tombol pengaturan kategori untuk:
  - **Menambah Kategori Baru** — Tulis nama dan pilih warna.
  - **Menghapus Kategori** — Klik ikon hapus di samping kategori.

### 7.3 Grafik & Ringkasan

- **Grafik Bar Bulanan** — Menampilkan tren pengeluaran per bulan.
- **Ringkasan per Kategori** — Menampilkan total pengeluaran per kategori dalam bentuk *chip* berwarna.
- **Total Pengeluaran** — Ditampilkan di bagian atas halaman.

### 7.4 Edit & Hapus

- Klik ikon **Edit** pada baris pengeluaran untuk mengubah data.
- Klik ikon **Hapus** untuk menghapus catatan pengeluaran.
- Saat mengedit, foto nota yang sudah diupload sebelumnya akan tampil sebagai preview.

---

## 8. Pemberkasan Dokumen

**Fungsi:** Menyimpan dan mengelola berkas digital (PDF, foto, dokumen) secara terpusat.

### 8.1 Upload Berkas

1. Klik tombol **"Upload Berkas"**.
2. Pilih file dari komputer Anda.
3. Beri nama berkas dan tentukan apakah berkas bersifat **Publik** (bisa dilihat semua instansi) atau **Privat**.
4. Klik **"Upload"**.

### 8.2 Melihat & Mengunduh

- Klik ikon **Lihat** untuk membuka berkas di tab baru.
- Klik ikon **Download** untuk mengunduh berkas.

### 8.3 Edit & Hapus

- Klik ikon **Edit** untuk mengubah nama atau pengaturan publik/privat.
- Klik ikon **Hapus** untuk menghapus berkas.

### 8.4 Pencarian

Gunakan kotak pencarian di bagian atas untuk mencari berkas berdasarkan nama.

---

## 9. Data Santri

**Fungsi:** Database utama yang berisi seluruh data lengkap santri aktif.

### 9.1 Tabel Data Santri

Menampilkan seluruh santri dalam bentuk tabel dengan kolom-kolom:
- Nama, Kode Santri, Kelas, Pondok, Negara, No. Paspor, Exp. ITAS, Exp. Paspor, dan lainnya.
- Tabel ini mendukung **sorting** (pengurutan) dan **pencarian cepat** per kolom.

### 9.2 Menambah Santri Baru

1. Klik tombol **"+ Tambah Santri"**.
2. Isi formulir data santri lengkap.
3. Upload **foto santri** (opsional).
4. Klik **"Simpan"**.

### 9.3 Edit Data Santri

1. Klik nama santri pada tabel untuk membuka detail.
2. Ubah data yang diperlukan.
3. Klik **"Simpan Perubahan"**.

> **⚠️ Penting:** Jika Anda mengedit data santri yang berasal dari instansi lain, sistem akan otomatis membuat **Request Edit** (Permintaan Perubahan) yang harus disetujui terlebih dahulu oleh admin instansi asal santri tersebut.

### 9.4 Filter Pencarian Lanjutan

Klik panel **"Filter Pencarian Lanjutan"** untuk memfilter berdasarkan:
- Pondok / Kepengurusan
- Negara Tujuan
- Kewarganegaraan
- Rayon / Kelas
- Status Exp. ITAS (segera / sudah expired)
- Status Exp. Paspor (segera / sudah expired)

### 9.5 Aksi Massal

Centang beberapa santri, lalu pilih aksi:
- **Buka Proses** — Buat proses Job Desk untuk beberapa santri sekaligus.
- **Nonaktifkan Massal** — Nonaktifkan beberapa santri sekaligus (dipindahkan ke Data Inaktif).
- **Edit Massal** — Ubah data tertentu untuk beberapa santri sekaligus.

### 9.6 Tarik Data Firebase

Klik tombol **"Tarik Data Firebase"** untuk menarik data terbaru dari Firebase Cloud ke database lokal. Fitur ini berguna jika data telah diperbarui dari sumber eksternal.

### 9.7 Auto ITAS

Klik tombol **"Auto ITAS"** untuk mengisi data ITAS secara otomatis berdasarkan pola yang telah dikonfigurasi.

### 9.8 Import dari Excel

Klik tombol **"Import Excel"** untuk mengimpor data santri dari file spreadsheet. Anda akan diarahkan ke halaman Import Excel (lihat Bab 13).

---

## 10. Data Santri Inaktif

**Fungsi:** Menyimpan arsip data santri yang sudah dinonaktifkan (lulus, keluar, dll.).

### 10.1 Daftar Santri Inaktif

Menampilkan tabel santri yang berstatus tidak aktif. Anda bisa:
- **Melihat Detail** — Klik nama santri untuk melihat data lengkap dalam pop-up modal.
- **Mencari** — Gunakan kotak pencarian dan filter untuk menemukan santri tertentu.

### 10.2 Mengaktifkan Kembali

Klik tombol **"Aktifkan"** (hijau) pada baris santri untuk mengembalikan statusnya menjadi aktif. Data santri akan kembali muncul di halaman Data Santri.

### 10.3 Hapus Permanen

Klik tombol **"Hapus"** (merah) untuk menghapus data santri secara permanen dari database. **Aksi ini tidak bisa dibatalkan!**

### 10.4 Hapus Massal

1. Centang beberapa santri pada kolom *checkbox*.
2. Tombol **"Hapus Terpilih (N)"** akan muncul di atas tabel.
3. Klik tombol tersebut, lalu konfirmasi.
4. **Tunggu proses selesai** — Layar akan menampilkan indikator *loading* selama proses berlangsung.

---

## 11. Persetujuan Usulan Data

**Fungsi:** Sistem persetujuan (approval) untuk perubahan data santri lintas instansi.

### 11.1 Kapan Request Edit Terjadi?

Ketika seorang admin dari **Instansi A** mengedit data santri yang terdaftar di **Instansi B**, sistem tidak akan langsung menyimpan perubahan tersebut. Sebaliknya, sistem akan membuat **Request Edit** yang harus disetujui oleh admin Instansi B.

### 11.2 Tab Masuk (Incoming)

Menampilkan daftar permintaan perubahan data yang **masuk ke instansi Anda** dan **menunggu persetujuan Anda**.

Untuk setiap *request*:
- Anda bisa melihat **data lama vs data baru** yang diusulkan.
- Klik **"Setujui"** untuk menyetujui perubahan.
- Klik **"Tolak"** untuk menolak perubahan.

### 11.3 Tab Keluar (Outgoing)

Menampilkan daftar permintaan perubahan yang **Anda kirim** ke instansi lain. Anda bisa melihat statusnya (Menunggu / Disetujui / Ditolak).

### 11.4 Tab Riwayat

Menampilkan arsip semua *request* yang sudah diproses (baik disetujui maupun ditolak).

### 11.5 Badge Notifikasi

Di sidebar, menu **"Persetujuan (Request)"** akan menampilkan **badge angka merah** jika ada request yang menunggu persetujuan Anda.

---

## 12. Pendaftaran CAPEL

**Fungsi:** Mengelola data pendaftaran Calon Pelajar (CAPEL) dari Google Spreadsheet.

### 12.1 Tarik Data dari Google Spreadsheet

1. Klik tombol **"+ Sync Data"**.
2. Masukkan **Spreadsheet ID** (ID dari URL Google Spreadsheet).
3. Masukkan **Range** (misal: `Form Responses 1`).
4. Klik **"Cek Header"** untuk memverifikasi kolom-kolom spreadsheet.
5. Lakukan mapping kolom jika diperlukan.
6. Klik **"Mulai Sinkronisasi"** untuk menarik data.

### 12.2 Atur Tampilan Tabel

Klik tombol **"Atur Tabel"** untuk mengatur kolom-kolom mana saja yang ingin ditampilkan di tabel CAPEL.

### 12.3 Unduh Berkas Pendaftaran

Jika data CAPEL memiliki lampiran berkas (foto, dokumen), Anda bisa mengunduhnya langsung dari tabel.

---

## 13. Import Data dari Excel

**Fungsi:** Mengimpor data santri dari file Excel (.xlsx / .xls) ke dalam database.

### 13.1 Langkah-langkah Import

#### Step 1 — Upload File
1. Seret file Excel ke area **"Upload Zone"**, atau klik area tersebut untuk memilih file.
2. Sistem akan membaca file dan menampilkan *preview* kolom-kolomnya.

#### Step 2 — Mapping Kolom
1. Sistem akan menampilkan kolom-kolom dari Excel Anda.
2. Petakan (*mapping*) setiap kolom Excel ke kolom database yang sesuai (misal: kolom "NAMA" di Excel → kolom "Nama Santri" di database).
3. Kolom yang sudah cocok akan otomatis ter-*mapping*.

#### Step 3 — Preview & Validasi
1. Sistem menampilkan *preview* data yang akan diimpor.
2. **Baris Merah** — Data yang bermasalah (ada error validasi).
3. **Baris Kuning** — Data yang kemungkinan duplikat dengan data yang sudah ada.
4. Periksa data, lalu klik **"Import Sekarang"**.

#### Step 4 — Hasil Import
Menampilkan ringkasan hasil: berapa data berhasil diimpor, berapa yang gagal, dan berapa yang terdeteksi duplikat.

> **⚠️ Catatan:** Jika muncul notifikasi "Gagal menghubungi server", coba klik lagi. Ini terjadi jika file Excel sangat besar dan server memerlukan waktu lebih lama untuk memproses.

---

## 14. Generator Surat Resmi

**Fungsi:** Membuat surat resmi (Surat Permohonan, Jaminan, Keterangan, Tugas) secara otomatis menggunakan template Microsoft Word.

### 14.1 Alur Pembuatan Surat (Wizard 3 Langkah)

#### Step 1 — Pilih Santri & Mode
1. **Pilih Mode Surat:**
   - **Sekaligus (Kolektif)** — 1 surat berisi semua nama santri yang dipilih.
   - **Perseorangan** — Setiap santri mendapat 1 surat terpisah.
2. **Pilih Santri:** Centang santri dari tabel di bawahnya.
3. Klik **"Lanjut Pengajuan →"**.

#### Step 2 — Jenis Pengajuan
1. Pilih **Jenis Pengajuan** dari daftar yang telah dikonfigurasi (misal: "Surat Rekomendasi Pembuatan Visa Belajar").
2. Isi informasi tujuan surat:
   - **Kepada** — Nama pejabat/instansi tujuan.
   - **Instansi Tujuan** — Pilih dari daftar (misal: Imigrasi).
   - **Tempat** — Kota/Lokasi.
3. **Konfigurasi Output:**
   - Pilih jenis surat yang ingin di-*generate* (SP, SJ, SK, ST).
   - Atur **Path Folder Output** (opsional). Jika folder tidak ditemukan, sistem akan otomatis menyimpan ke folder `public/uploads/Surat_Menyurat/Output/`.
4. Klik **"Simpan Konfigurasi"**.

#### Step 3 — Generate & Cetak
1. Klik tombol **"Generate"** untuk setiap jenis surat.
2. Sistem akan membuka Microsoft Word dan membuat surat secara otomatis menggunakan template.
3. **Preview surat** akan ditampilkan di layar dengan format kertas A4 lengkap dengan Kop Surat.

### 14.2 Template Surat

Template surat berupa file `.docx` (Microsoft Word) yang disimpan di folder:
```
public/uploads/Surat_Menyurat/{Nama_Kantor}/
```

Contoh struktur folder:
```
uploads/Surat_Menyurat/
├── Imigrasi/
│   ├── Surat_Permohonan_Template.docx
│   ├── Surat_Keterangan_Template.docx
│   ├── Surat_Jaminan_Template.docx
│   └── Surat_Tugas_Template.docx
└── Output/
    └── (hasil generate surat otomatis masuk ke sini)
```

### 14.3 Riwayat Mailing

Klik tab **"Riwayat Mailing"** untuk melihat semua surat yang pernah di-*generate* sebelumnya, lengkap dengan informasi tanggal, santri terkait, dan status.

### 14.4 Kelola Jenis Pengajuan

Klik tombol **"Kelola Jenis Pengajuan"** untuk menambah, mengedit, atau menghapus jenis-jenis pengajuan surat.

### 14.5 Edit Template

Klik tombol **"Download Template"** pada jenis surat tertentu untuk membuka file template Word-nya agar bisa diedit sesuai kebutuhan.

---

## 15. Anggaran Operasional

**Fungsi:** Mengelola pengajuan dan pencatatan anggaran operasional kepengurusan.

### 15.1 Membuat Pengajuan Anggaran Baru

1. Klik **"+ Buat Pengajuan Baru"**.
2. Isi informasi pengajuan:
   - Judul / Nama Pengajuan
   - Total Anggaran yang Diajukan
   - Periode Anggaran
   - Rincian item anggaran
3. Klik **"Simpan"**.

### 15.2 Kartu Pengajuan

Setiap pengajuan ditampilkan sebagai kartu yang menunjukkan:
- Judul pengajuan
- Total anggaran vs Sisa anggaran (hijau jika surplus, merah jika minus)
- Status pengajuan

### 15.3 Aksi pada Pengajuan

- **Lihat Detail** — Klik tombol "Lihat Detail" untuk melihat rincian lengkap.
- **Edit** — Klik tombol "Edit" untuk mengubah pengajuan.
- **Duplikat** — Klik tombol "Salin" untuk menduplikasi pengajuan sebagai template baru.
- **Review** — Klik tombol "Review" untuk meninjau dan menyetujui pengajuan.
- **Hapus** — Klik tombol "Hapus" untuk menghapus pengajuan.

### 15.4 Print Pengajuan

Setiap pengajuan bisa dicetak dalam format resmi lengkap dengan **Kop Surat** instansi.

---

## 16. Menu Print

**Fungsi:** Mencetak dan mengekspor data santri dalam berbagai format laporan.

### 16.1 Tampilan

Halaman ini memiliki **Panel Print** di sisi kanan yang bisa dibuka/tutup. Panel ini berisi opsi-opsi konfigurasi cetakan.

### 16.2 Cara Mencetak

1. Pilih **template laporan** yang diinginkan dari panel kanan.
2. Tentukan filter (misal: hanya santri aktif, per pondok, per negara).
3. Klik **"Cetak"** atau **"Export"**.
4. Hasil cetakan akan terbuka di tab baru dalam format siap cetak (Print Preview).

---

## 17. Manajemen Paspor

**Fungsi:** Mencatat keluar-masuk paspor santri (apakah paspor sedang dipegang santri, kantor, atau pihak ketiga).

### 17.1 Tab Status Paspor

Menampilkan status semua paspor santri:
- **Di Kantor** — Paspor sedang disimpan oleh kepengurusan.
- **Di Santri** — Paspor sedang dipegang santri.
- **Di Pihak Ketiga** — Paspor sedang diproses di instansi lain (misal: Kedutaan, Imigrasi).

### 17.2 Tab Riwayat Log

Menampilkan riwayat semua perubahan status paspor (siapa yang mengambil, kapan dikembalikan, dll.).

### 17.3 Export Data

- **Print Status Paspor** — Cetak laporan status paspor.
- **Export ke Excel** — Unduh data status paspor atau riwayat log dalam format `.xlsx`.

---

## 18. Kalender Expiry

**Fungsi:** Menampilkan kalender visual yang menandai tanggal-tanggal kedaluwarsa ITAS dan Paspor santri.

### 18.1 Cara Membaca

- Tanggal-tanggal yang memiliki kedaluwarsa akan ditandai dengan warna.
- Klik pada tanggal untuk melihat daftar santri yang dokumennya kedaluwarsa pada tanggal tersebut.
- Menu ini berada di sidebar di bawah menu-menu grup.

---

## 19. Anggota Kamar

**Fungsi:** Mengelola pembagian kamar/asrama santri.

### 19.1 Cara Menggunakan

- Lihat daftar kamar dan anggota di masing-masing kamar.
- Tambahkan santri ke kamar tertentu.
- Pindahkan santri antar kamar.

---

## 20. Profil Instansi

**Fungsi:** Mengelola identitas dan konfigurasi pondok/instansi.

### 20.1 Informasi yang Bisa Diatur

- **Nama Instansi** — Nama resmi pondok cabang.
- **No. Registrasi / ID Instansi** — Nomor identifikasi unik.
- **Path Folder** — Lokasi folder berkas fisik di komputer (misal: `D:\berkas\G4`).
- **Kop Surat** — Upload gambar kop surat resmi instansi. Gambar ini akan digunakan pada semua surat dan dokumen yang dicetak.
- Dan informasi kontak lainnya.

### 20.2 Pilih Instansi (Super Admin)

Jika Anda login sebagai Super Admin, Anda bisa memilih instansi mana yang ingin dilihat/dikelola melalui dropdown di bagian atas halaman.

---

## 21. Pengaturan Job Desk

**Fungsi:** Mengonfigurasi template langkah-langkah (steps) untuk setiap jenis proses birokrasi.

### 21.1 Cara Menggunakan

1. Tambah atau edit **jenis proses** (misal: "Perpanjangan ITAS", "Pembuatan Visa").
2. Untuk setiap jenis proses, tentukan **langkah-langkah** yang harus dilalui:
   - Nama langkah (misal: "Serah Berkas ke Imigrasi")
   - Urutan langkah
   - Estimasi durasi
3. Template ini akan otomatis digunakan saat membuat proses baru di halaman Job Desk.

---

## 22. Manajemen Instansi

**Akses:** Khusus **Super Admin**.

**Fungsi:** Mengelola daftar seluruh pondok cabang (tenant) dalam sistem.

### 22.1 Menambah Instansi Baru

1. Klik **"+ Tambah Instansi"**.
2. Isi nama instansi, lokasi, dan informasi lainnya.
3. Klik **"Simpan"**.

### 22.2 Edit & Hapus Instansi

- Klik baris instansi untuk mengedit.
- Hapus instansi yang tidak lagi diperlukan.

---

## 23. Manajemen Pengguna

**Fungsi:** Mengelola akun pengguna dan hak akses (otorisasi) setiap staf.

### 23.1 Menambah Pengguna Baru

1. Klik **"+ Tambah Pengguna"**.
2. Isi:
   - **Nama Lengkap**
   - **Username** (minimal 4 karakter, huruf/angka/underscore)
   - **Password** (minimal 6 karakter)
   - **Role** — Pilih peran:
     - `super_admin` — Akses penuh ke seluruh fitur dan semua instansi.
     - `admin` — Akses penuh untuk instansi yang ditugaskan.
     - `staff` — Akses terbatas sesuai hak yang diberikan.
3. **Atur Hak Akses Menu** — Centang menu-menu mana saja yang boleh diakses oleh pengguna ini.
4. Klik **"Simpan"**.

### 23.2 Mengedit Pengguna

Klik baris pengguna, lalu ubah informasi atau hak aksesnya.

### 23.3 Reset Password

Admin bisa me-reset password pengguna lain jika diperlukan.

### 23.4 Hierarki Peran (Role)

```
Super Admin
  └── Bisa mengakses SEMUA fitur & SEMUA instansi
  └── Bisa mengelola instansi & pengguna
  └── Bisa mengubah pengaturan sistem

Admin Instansi
  └── Bisa mengakses fitur sesuai hak yang diberikan
  └── Hanya bisa melihat data instansi sendiri
  └── Bisa mengelola pengguna di instansinya

Staff
  └── Akses terbatas sesuai menu yang diizinkan
  └── Tidak bisa mengelola pengguna lain
```

---

## 24. Pengaturan Sistem

**Akses:** Bisa dilihat semua pengguna, **hanya bisa diubah oleh Super Admin**.

### 24.1 Informasi Sistem

Menampilkan versi aplikasi, konfigurasi server, dan informasi teknis lainnya.

### 24.2 Firebase Cloud Sync

Bagian ini mengelola sinkronisasi data ke Firebase Cloud:

- **Status Koneksi** — Menampilkan apakah Firebase terhubung atau tidak.
- **Real-time Sync** — Setiap perubahan data (tambah/edit/hapus) otomatis dikirim ke Firebase.
- **Full Sync (Manual)** — Kirim SEMUA data sekaligus ke Firebase. Gunakan saat:
  - Pertama kali setup sistem.
  - Ada data yang belum tersinkron.
  - Setelah import data massal.

### 24.3 Cara Menjalankan Full Sync

1. Klik tombol **"Sinkronisasi Sekarang"**.
2. Tunggu proses hingga selesai. Progress akan ditampilkan di layar.
3. Setelah selesai, periksa ringkasan hasil (berapa data berhasil, gagal, dll.).

---

## 25. Log Aktivitas

**Fungsi:** Mencatat seluruh aktivitas yang dilakukan pengguna di dalam sistem (audit trail).

### 25.1 Informasi yang Dicatat

Setiap baris log berisi:
- **Waktu** — Kapan aksi dilakukan.
- **Pengguna** — Siapa yang melakukan aksi.
- **Modul** — Di menu/halaman mana aksi dilakukan.
- **Aksi** — Jenis aksi (Tambah, Edit, Hapus, Login, dll.).
- **ID Entitas** — Identifikasi data yang terpengaruh.

### 25.2 Manfaat

- Melacak siapa yang mengubah data tertentu.
- Mendeteksi aktivitas mencurigakan.
- Sebagai bukti audit untuk keperluan pelaporan.

---

## 26. Firebase Cloud Sync & Monitoring

### 26.1 Apa Itu Firebase Sync?

Firebase adalah layanan cloud (awan) dari Google yang digunakan SiPLN untuk menyinkronkan data dari komputer lokal pondok cabang ke server internet. Dengan ini, pimpinan pusat bisa **memantau data semua cabang secara real-time** tanpa harus mengakses komputer lokal masing-masing cabang.

### 26.2 Data yang Disinkronkan

- Data Santri (biodata, status)
- Data Paspor (nomor, masa berlaku)
- Data ITAS (masa berlaku)
- Data Pengguna (akun login)

### 26.3 Cara Kerja

```
Komputer Cabang ──► [Internet] ──► Firebase Cloud ──► Dashboard Monitoring
      │                                                     │
      │ Setiap tambah/edit/hapus                           │ Bisa diakses dari
      │ otomatis dikirim                                    │ mana saja (online)
      ▼                                                     ▼
  Database Lokal                                    Super Admin / Pusat
```

### 26.4 Tombol Monitoring

Di pojok kanan atas header, klik tombol **"Monitoring"** untuk membuka **Firebase Dashboard** — halaman web terpisah yang menampilkan data semua cabang secara real-time.

---

## 27. Tips & Trik Penting

### Shortcut & Efisiensi

1. **Sidebar bisa diciutkan** — Klik ikon menu (tiga garis) untuk memperluas area kerja.
2. **Filter selalu tersimpan** — Pengaturan filter Anda akan diingat oleh sistem.
3. **Pencarian per kolom** — Hampir semua tabel mendukung pencarian per kolom di baris header.
4. **Tab/Tombol berpindah warna** — Tab/tombol yang sedang aktif berwarna biru, yang tidak aktif berwarna abu-abu.

### Hal yang Perlu Diperhatikan

1. **Jangan tutup browser saat proses massal berlangsung** — Proses seperti Nonaktifkan Massal, Hapus Massal, atau Full Sync memerlukan waktu. Tunggu indikator *loading* selesai.
2. **Backup database secara berkala** — Gunakan phpMyAdmin untuk mengekspor database ke file `.sql`.
3. **Periksa koneksi internet** — Fitur Firebase Sync memerlukan koneksi internet yang stabil.
4. **Gunakan browser modern** — Disarankan menggunakan Google Chrome, Microsoft Edge, atau Mozilla Firefox versi terbaru.

### Keamanan

1. **Ganti password secara berkala** — Minimal setiap 3 bulan.
2. **Jangan bagikan akun** — Setiap staf harus memiliki akun sendiri.
3. **Logout setelah selesai** — Selalu klik tombol Logout (ikon Power) di pojok kanan atas setelah selesai bekerja.

---

## 28. FAQ (Pertanyaan Umum)

### Q: Apa bedanya "Admin" dan "Super Admin"?
**A:** Super Admin bisa mengakses SEMUA fitur dan SEMUA data dari SEMUA instansi. Admin hanya bisa mengakses data instansi yang ditugaskan kepadanya.

### Q: Bagaimana jika saya lupa password?
**A:** Hubungi Admin atau Super Admin untuk mereset password akun Anda.

### Q: Kenapa foto/gambar tidak muncul?
**A:** Pastikan ekstensi GD sudah diaktifkan di `php.ini` dan server Apache sudah di-restart.

### Q: Kenapa Surat Generator error?
**A:** Pastikan:
1. Ekstensi `com_dotnet` sudah diaktifkan di `php.ini`.
2. Microsoft Word sudah terinstal di komputer.
3. File template `.docx` sudah ada di folder `public/uploads/Surat_Menyurat/{Nama_Kantor}/`.

### Q: Bagaimana cara backup data?
**A:** Buka phpMyAdmin → Pilih database → Tab "Export" → Klik "Go". File `.sql` akan terunduh.

### Q: Kenapa data tidak muncul di Dashboard Monitoring (Firebase)?
**A:** Coba lakukan **Full Sync** dari menu Pengaturan Sistem → Firebase Cloud Sync → Klik "Sinkronisasi Sekarang".

### Q: Apa yang terjadi jika internet mati?
**A:** Aplikasi tetap berjalan normal di komputer lokal. Data akan tetap tersimpan di database lokal. Hanya fitur sinkronisasi Firebase yang akan tertunda hingga internet pulih kembali.

### Q: Bagaimana cara menambah jenis proses baru di Job Desk?
**A:** Masuk ke menu **Pengaturan Job Desk** → Tambah jenis proses baru → Tentukan langkah-langkahnya.

### Q: Bisakah saya menggunakan aplikasi ini di beberapa komputer sekaligus?
**A:** Aplikasi ini dirancang untuk berjalan di **1 komputer per pondok cabang** (localhost). Namun, dalam jaringan lokal yang sama, komputer lain bisa mengakses melalui IP address komputer server (misal: `http://192.168.1.100/WEBAPP/public/`).

---

> **Butuh Bantuan?**
> Jika Anda mengalami kendala teknis yang tidak tercakup dalam panduan ini, silakan hubungi tim pengembang atau Super Admin pusat.

---

---

## LAMPIRAN A: Daftar Tingkatan Kelas Santri

Berikut adalah daftar lengkap jenjang/tingkatan kelas yang digunakan di dalam sistem SiPLN. Kelas diurutkan dari awal masuk hingga lulus:

### A.1 Tahap Calon Pelajar (CAPEL)

| Kelas | Keterangan |
|---|---|
| **CAPEL PENERIMAAN** | Calon pelajar yang baru diterima dan sedang dalam proses administrasi penerimaan awal. |
| **CAPEL PERSIAPAN** | Calon pelajar yang sudah diterima dan sedang dalam masa persiapan/penampungan sebelum berangkat ke luar negeri. |

### A.2 Kelas Reguler (Jenjang Pendidikan)

Setiap kelas reguler memiliki **pembagian abjad** (A, B, C, dst.) sesuai jumlah rombel (rombongan belajar) di masing-masing pondok.

| Kelas | Contoh dengan Abjad | Keterangan |
|---|---|---|
| **Kelas 1** | 1A, 1B, 1C, ... | Kelas tahun pertama |
| **Kelas 1 Int** | 1 Int A, 1 Int B, ... | Kelas 1 program Internasional |
| **Kelas 2** | 2A, 2B, 2C, ... | Kelas tahun kedua |
| **Kelas 3** | 3A, 3B, 3C, ... | Kelas tahun ketiga |
| **Kelas 3 Int** | 3 Int A, 3 Int B, ... | Kelas 3 program Internasional |
| **Kelas 4** | 4A, 4B, 4C, ... | Kelas tahun keempat |
| **Kelas 5** | 5A, 5B, 5C, ... | Kelas tahun kelima |
| **Kelas 6** | 6A, 6B, 6C, ... | Kelas tahun keenam (tahun terakhir) |

> **Catatan:** Pembagian abjad (A, B, C, dst.) mengikuti kebijakan masing-masing pondok cabang. Tidak semua pondok memiliki jumlah rombel yang sama.

### A.3 Tahap Pasca-Lulus

| Kelas | Keterangan |
|---|---|
| **Pengabdian** | Santri yang sudah menyelesaikan jenjang pendidikan dan sedang menjalani masa pengabdian di pondok. Dikelompokkan sesuai pondok penempatan masing-masing. |
| **Alumni** | Santri yang sudah selesai menjalani seluruh jenjang pendidikan dan masa pengabdian. |

### A.4 Diagram Alur Jenjang

```
CAPEL PENERIMAAN → CAPEL PERSIAPAN → Kelas 1/1 Int → Kelas 2 → Kelas 3/3 Int
                                                                      ↓
                                         Alumni ← Pengabdian ← Kelas 6 ← Kelas 5 ← Kelas 4
```

---

## LAMPIRAN B: Peta Penyimpanan File & Upload

Berikut adalah penjelasan lengkap **di mana** semua file yang di-upload atau di-generate oleh sistem disimpan di dalam komputer.

### B.1 Lokasi Utama

Semua file upload tersimpan di dalam folder **`public/uploads/`** (di dalam folder aplikasi `htdocs/WEBAPP/`), kecuali jika pondok telah mengatur **"Path Folder"** khusus di Profil Instansi (misal: `D:\berkas\G4`).

```
htdocs/WEBAPP/public/uploads/
├── foto santri/          ← Foto profil santri
├── paspor/               ← Scan/foto halaman paspor santri
├── berkas/               ← Berkas umum yang diupload via menu Pemberkasan
├── instansi/             ← File milik instansi (kop surat, nota, dll.)
│   ├── kop_surat_G4.png  ← Gambar Kop Surat yang diupload di Profil Instansi
│   └── {nama_instansi}/
│       └── nota/
│           ├── operasional birokrasi/  ← Foto nota Pengeluaran Operasional
│           └── anggaran_operasional/   ← Foto nota Anggaran Operasional
├── Surat_Menyurat/       ← Template & Output Surat Generator
│   ├── {Nama_Kantor}/    ← Template .docx per kantor tujuan
│   │   ├── Surat_Permohonan_Template.docx
│   │   ├── Surat_Keterangan_Template.docx
│   │   ├── Surat_Jaminan_Template.docx
│   │   └── Surat_Tugas_Template.docx
│   └── Output/           ← Hasil generate surat otomatis
│       └── {Jenis_Pengajuan}/
│           └── {Tipe_Surat}/
│               └── {Tahun}/{Bulan}/
│                   └── Surat_XXX.docx
├── anggaran/             ← File terkait anggaran
└── templates/            ← Template upload Jenis Pengajuan Surat
```

### B.2 Penjelasan Detail per Jenis File

| Jenis File | Lokasi Penyimpanan | Keterangan |
|---|---|---|
| **Foto Santri** | `uploads/foto santri/` atau `{Path Folder Instansi}/foto santri/` | Foto profil yang diupload saat menambah/edit data santri |
| **Scan Paspor** | `uploads/paspor/` atau `{Path Folder Instansi}/paspor/` | Foto/scan halaman paspor santri |
| **Scan ITAS** | `{Path Folder Instansi}/itas/` | Foto/scan kartu ITAS santri |
| **Berkas Santri (Individu)** | `{Path Folder Instansi}/berkas/{Nama_Kode_Santri}/` | Berkas-berkas penting per santri (Akta, Ijazah, dll.) |
| **Berkas Umum (Pemberkasan)** | `uploads/berkas/` atau `{Path Folder Instansi}/` | Dokumen yang diupload via menu Pemberkasan |
| **Kop Surat Instansi** | `uploads/instansi/` | Gambar kop surat yang diupload di Profil Instansi |
| **Foto Nota Pengeluaran** | `uploads/instansi/{folder_instansi}/nota/operasional birokrasi/` | Foto bukti pengeluaran operasional |
| **Foto Nota Anggaran** | `uploads/instansi/{folder_instansi}/nota/anggaran_operasional/` | Foto bukti pengeluaran anggaran |
| **Template Surat (.docx)** | `uploads/Surat_Menyurat/{Nama_Kantor}/` | Template Microsoft Word untuk generate surat |
| **Output Surat (Hasil Generate)** | `uploads/Surat_Menyurat/Output/{Jenis}/{Tipe}/{Tahun}/{Bulan}/` | Surat yang sudah di-generate otomatis oleh sistem |
| **Berkas CAPEL** | `uploads/berkas/` | Berkas pendaftaran calon pelajar yang diunduh dari spreadsheet |

### B.3 Tentang "Path Folder Instansi"

Jika di menu **Profil Instansi** Anda mengisi kolom **"Path Folder"** (misal: `D:\berkas\G4`), maka:
- Foto santri, scan paspor, ITAS, dan berkas individu santri akan disimpan ke folder tersebut.
- Jika kolom tersebut **dikosongkan**, sistem akan menyimpan ke folder bawaan `public/uploads/`.

> **💡 Tips:** Mengisi Path Folder sangat disarankan untuk pondok yang memiliki banyak data. Dengan begitu, file-file besar tidak akan menumpuk di dalam folder aplikasi dan lebih mudah di-backup secara terpisah.

### B.4 Tentang Output Surat Generator

Surat yang di-generate akan disimpan berdasarkan urutan berikut:
1. **Jika "Path Folder Output" diisi** di Konfigurasi Jenis Pengajuan → file disimpan di folder tersebut.
2. **Jika folder dari Path tersebut tidak ditemukan** → file otomatis disimpan ke `uploads/Surat_Menyurat/Output/`.
3. **Jika dikosongkan** → file juga masuk ke `uploads/Surat_Menyurat/Output/`.

---

## LAMPIRAN C: Istilah & Singkatan Penting

| Istilah / Singkatan | Kepanjangan / Arti |
|---|---|
| **SiPLN** | Sistem Informasi Pondok Luar Negeri |
| **CAPEL** | Calon Pelajar — santri yang baru mendaftar dan belum resmi masuk |
| **ITAS** | Izin Tinggal Terbatas — dokumen izin tinggal di negara tujuan |
| **KDS** | Kode Santri — kode unik yang mengidentifikasi setiap santri |
| **SP** | Surat Permohonan |
| **SJ** | Surat Jaminan |
| **SK** | Surat Keterangan |
| **ST** | Surat Tugas |
| **Kop Surat** | Header/kepala surat resmi instansi berisi logo dan identitas |
| **Mailing** | Satu paket pengiriman surat (bisa berisi banyak surat sekaligus) |
| **Tenant** | Instansi/pondok cabang — setiap cabang adalah 1 tenant |
| **Firebase** | Layanan cloud Google untuk sinkronisasi data real-time |
| **Firestore** | Database cloud Firebase yang digunakan untuk menyimpan data sync |
| **Full Sync** | Proses mengirim seluruh data lokal ke Firebase sekaligus |
| **Real-time Sync** | Proses mengirim perubahan data secara otomatis saat terjadi |
| **Request Edit** | Permintaan perubahan data santri lintas instansi yang perlu disetujui |
| **Bulk Action** | Aksi massal — melakukan tindakan terhadap banyak data sekaligus |
| **Pipeline / Kanban** | Sistem alur kerja bertahap yang digunakan di fitur Job Desk |
| **Bottleneck** | Langkah proses yang paling lama / sering terhambat |
| **Blocked** | Status langkah proses yang terhambat dan tidak bisa dilanjutkan |
| **Path Folder** | Lokasi folder fisik di komputer untuk menyimpan berkas instansi |
| **php.ini** | File konfigurasi PHP — tempat mengaktifkan/menonaktifkan ekstensi |
| **phpMyAdmin** | Aplikasi web untuk mengelola database MySQL |
| **XAMPP** | Paket software yang berisi Apache, MySQL, dan PHP |
| **Laragon** | Alternatif XAMPP — lingkungan pengembangan web untuk Windows |
| **Exp.** | Expiry / Kedaluwarsa — tanggal habis masa berlaku dokumen |
| **Rayon** | Pembagian wilayah/area dalam struktur organisasi pondok |
| **Stambuk** | Nomor induk / nomor registrasi santri |

---

*Dokumen ini adalah panduan resmi penggunaan SiPLN. Harap simpan dengan baik dan bagikan kepada seluruh staf yang menggunakan sistem ini.*

**© 2026 — Sistem Informasi Pondok Luar Negeri (SiPLN)**
