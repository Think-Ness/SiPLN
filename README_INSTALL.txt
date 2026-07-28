========================================================================
PANDUAN INSTALASI SISTEM INFORMASI PONDOK LUAR NEGERI (MULTI-INSTANSI)
========================================================================

Aplikasi ini dapat diinstal dan dijalankan di komputer cabang dengan 
menggunakan server lokal (seperti XAMPP, Laragon, atau WAMP) maupun 
server online (cPanel/VPS).

--- PERSYARATAN SISTEM ---
1. PHP Minimal versi 8.1
2. MySQL / MariaDB (sudah termasuk dalam XAMPP/Laragon)
3. Ekstensi PHP yang harus aktif:
   - pdo_mysql
   - curl
   - intl (opsional untuk format tanggal)

--- LANGKAH-LANGKAH INSTALASI ---

1. EKSTRAK APLIKASI
   Ekstrak file ZIP/RAR ini, dan letakkan folder aplikasinya (yang berisi folder 
   'public', 'src', dll) ke dalam folder root web server Anda:
   - Jika menggunakan XAMPP: taruh di folder `C:\xampp\htdocs\nama_folder\`
   - Jika menggunakan Laragon: taruh di folder `C:\laragon\www\nama_folder\`

2. BUAT DATABASE KOSONG
   - Buka phpMyAdmin (biasanya di http://localhost/phpmyadmin)
   - Buat satu database baru yang KOSONG (misalnya beri nama `si_foreign_db`).
   - Jangan import tabel apapun, biarkan Setup Wizard yang mengurusnya.

3. JALANKAN SETUP WIZARD
   - Buka browser Anda (Google Chrome / Firefox).
   - Ketik alamat aplikasi Anda, misalnya: 
     http://localhost/nama_folder/public/
   - Atau jika Anda ingin menjalankan dengan mudah (tanpa XAMPP), pastikan PHP 
     sudah terinstal, lalu klik ganda file `run_server.bat` dan buka 
     http://localhost:8080/ di browser.
   - Halaman Setup Wizard akan otomatis terbuka.

4. IKUTI PETUNJUK SETUP WIZARD
   - Wizard terdiri dari 7 langkah.
   - Di langkah ke-3, masukkan kredensial dan nama database KOSONG yang Anda buat.
   - Di langkah ke-5, pilih Instansi Cabang Anda dan buat akun Admin baru Anda.
   - Klik "Jalankan Instalasi" dan tunggu hingga selesai.

5. SELESAI
   Setelah instalasi sukses, Setup Wizard akan otomatis terkunci demi keamanan.
   Anda bisa langsung login dengan akun Admin yang baru saja dibuat.

------------------------------------------------------------------------
Catatan: 
Jika di kemudian hari Anda ingin mereset/menginstal ulang aplikasi dari awal, 
Anda hanya perlu menghapus file `installed.lock` yang ada di dalam folder 
`config/` dan mengosongkan kembali database Anda.
------------------------------------------------------------------------
