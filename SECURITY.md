# Panduan Standar Keamanan Aplikasi Web (Security Checklist & Best Practices)

Dokumen ini berisi standar keamanan menyeluruh berbasis **20 Security Checks Sebelum Launch Aplikasi Web**. Dokumen ini dapat digunakan sebagai acuan audit, checklist peluncuran aplikasi (*launch readiness*), serta standar baku pembuatan aplikasi web baru.

---

## Ringkasan 20 Security Checks

| No | Kategori | Item Checklist Keamanan | Status di Web App Ini |
|:---:|:---|:---|:---:|
| **1** | Kredensial & Rahasia | **API Key Aman** (Disimpan di environment, bukan client-side) | ✅ Terproteksi |
| **2** | Kredensial & Rahasia | **.env Jangan Public** (Blokir akses web ke `.env`) | ✅ Terproteksi |
| **3** | Kredensial & Rahasia | **No Hardcoded Secrets** (Tidak ada password/token statis di kode) | ✅ Terproteksi |
| **4** | Kredensial & Rahasia | **Cek Secret di Git** (`.gitignore` mengecualikan file kredensial) | ✅ Terproteksi |
| **5** | Server & Lingkungan | **Debug Mode OFF di Production** (`APP_DEBUG=false`, `display_errors=0`) | ✅ Terproteksi |
| **6** | Server & Lingkungan | **Error Jangan Bocor** (Generic error untuk user, detail ke server log) | ✅ Terproteksi |
| **7** | Validasi & Input Data | **Validasi Input** (Cek tipe data, panjang, rentang, whitelist nilai) | ✅ Terproteksi |
| **8** | Validasi & Input Data | **Sanitasi Input** (`trim`, type casting, pembersihan tag) | ✅ Terproteksi |
| **9** | Proteksi Database | **Anti SQL Injection** (100% Prepared Statements & Parameter Binding) | ✅ Terproteksi |
| **10** | Proteksi Frontend | **Anti XSS** (Output escaping dengan `htmlspecialchars` / `escHtml`) | ✅ Terproteksi |
| **11** | Autentikasi | **Server-Side Auth** (Otentikasi diverifikasi ketat di backend) | ✅ Terproteksi |
| **12** | Otorisasi & Akses | **Cek Akses User (ACL/RBAC)** (Verifikasi izin per menu/endpoint) | ✅ Terproteksi |
| **13** | Otorisasi & Akses | **Role Admin Aman** (Proteksi rute super admin & isolasi tenant) | ✅ Terproteksi |
| **14** | Keamanan Database | **Database Jangan Public** (Bind `127.0.0.1` / Private Network VPN) | ✅ Terproteksi |
| **15** | Keamanan Database | **Database Permission Ketat** (User aplikasi non-root, hak terbatas) | ✅ Terproteksi |
| **16** | Kriptografi | **Hash Password Kuat** (`password_hash` BCRYPT / ARGON2ID) | ✅ Terproteksi |
| **17** | Manajemen Sesi | **Session Aman** (`HttpOnly`, `SameSite`, `Strict`, Regenerate ID) | ✅ Terproteksi |
| **18** | Manajemen Akun | **Reset Password Aman** (Token kriptografis acak + masa kedaluwarsa) | ✅ Terproteksi |
| **19** | Penanganan File | **Batasi Upload File** (Whitelist ekstensi ketat & limit ukuran file) | ✅ Terproteksi |
| **20** | Penanganan File | **Scan & Cegah Eksekusi Upload** (Non-executable folder & anti-script) | ✅ Terproteksi |

---

## Penjelasan Detail 20 Checklist & Implementasi Teknis

### 1. API Key Aman
- **Prinsip**: API key backend (Database, Google Cloud, Firebase Admin SDK, Gateway) dilarang diletakkan di kode JavaScript frontend yang dapat di-*inspect* oleh pengguna browser.
- **Implementasi**:
  ```php
  // Gunakan variabel environment
  $apiKey = $_ENV['FIREBASE_PRIVATE_KEY'] ?? getenv('FIREBASE_PRIVATE_KEY');
  ```

### 2. .env Jangan Public
- **Prinsip**: File `.env` berisi kredensial sensitif. Web server dilarang melayani file berawalan titik (`.*`).
- **Implementasi Apache (`.htaccess`)**:
  ```apache
  <FilesMatch "(^\.env|^\.git|\.lock|\.json|\.yml|\.yaml|\.sql|\.log)$">
      Order Deny,Allow
      Deny from all
  </FilesMatch>
  ```
- **Implementasi Nginx**:
  ```nginx
  location ~ /\.(env|git|ht) {
      deny all;
      return 404;
  }
  ```

### 3. No Hardcode Secret
- **Prinsip**: Jangan pernah menuliskan password DB, token rahasia, atau salt enkripsi langsung di file `.php` atau `.js`.
- **Standar**: Pindahkan seluruh konfigurasi ke `.env` atau database configuration terenkripsi.

### 4. Cek Secret di Git (.gitignore)
- **Prinsip**: Pastikan file kredensial tidak ter-*commit* ke Git repository.
- **Standar `.gitignore`**:
  ```gitignore
  .env
  *.lock
  config/google_service_account.json
  config/license.json
  runtime/
  public/uploads/*
  !public/uploads/.gitkeep
  *.log
  ```

### 5. Debug Mode OFF di Production
- **Prinsip**: Mode debug menampilkan informasi struktur folder, file system, dan trace internal.
- **Implementasi**:
  ```php
  if (Environment::isProd()) {
      ini_set('display_errors', '0');
      ini_set('display_startup_errors', '0');
      error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
  }
  ```

### 6. Error Jangan Bocor ke Pengguna
- **Prinsip**: Jika terjadi exception / DB crash, tampilkan pesan ramah kepada user ("Terjadi kendala sistem, silakan coba lagi nanti"), dan catat detail stack trace ke file log server (`runtime/logs/app.log`).

### 7. Validasi Input
- **Prinsip**: *Never trust user input*. Semua data dari `$_GET`, `$_POST`, atau JSON body wajib divalidasi tipe data, batas minimal/maksimal, dan formatnya.
- **Contoh**:
  ```php
  $id = filter_var($body['id'] ?? null, FILTER_VALIDATE_INT);
  if ($id === false || $id <= 0) {
      return JsonResponse::create(['success' => false, 'message' => 'ID tidak valid'], 400);
  }
  ```

### 8. Sanitasi Input
- **Prinsip**: Bersihkan whitespace berlebih, buang tag berbahaya, dan lakukan type-casting sebelum diproses.
- **Contoh**:
  ```php
  $nama = trim(strip_tags((string)($body['nama'] ?? '')));
  $isAktif = (int)($body['aktif'] ?? 0);
  ```

### 9. Anti SQL Injection
- **Prinsip**: **Wajib 100% menggunakan Prepared Statements** dengan PDO parameter binding. Dilarang menggabungkan variabel langsung ke dalam query string (`SELECT ... WHERE id = " . $id`).
- **Contoh Aman**:
  ```php
  $stmt = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds AND aktif = :aktif", [
      ':kds' => $kds,
      ':aktif' => 1
  ])->queryOne();
  ```

### 10. Anti XSS (Cross-Site Scripting)
- **Prinsip**: Setiap variabel teks dari database atau input user yang dirender ke HTML wajib di-*escape*.
- **Contoh PHP**:
  ```php
  <?= htmlspecialchars((string)$santri['nama'], ENT_QUOTES, 'UTF-8') ?>
  ```
- **Contoh JavaScript**:
  ```javascript
  function escHtml(str) {
      if (!str) return '';
      return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
  }
  ```

### 11. Server-Side Authentication
- **Prinsip**: Status login tidak boleh hanya diperiksa di frontend. Setiap pemanggilan halaman atau endpoint API wajib dicek melalui `AuthMiddleware` di backend.
- **Contoh**:
  ```php
  if (!isset($_SESSION['user_id'])) {
      return JsonResponse::create(['success' => false, 'message' => 'Unauthorized'], 401);
  }
  ```

### 12. Cek Akses User (Authorization & Multi-Tenancy)
- **Prinsip**: Pengguna hanya dapat melihat dan mengubah data miliknya / instansinya sendiri (*Horizontal Privilege Escalation Prevention*).
- **Contoh Scoping**:
  ```php
  $where = "WHERE s.aktif = 1";
  if ($role !== 'super_admin') {
      $where .= " AND s.instansi_id = :myInstansi";
      $params[':myInstansi'] = $_SESSION['instansi_id'];
  }
  ```

### 13. Role Admin Aman
- **Prinsip**: Fitur sensitif (Manajemen Pengguna, Pengaturan Sistem, Akses Antar-Instansi) wajib memiliki pemeriksaan izin eksplisit `role === 'super_admin'` atau matriks permission `in_array('menu_pengaturan', $permissions)`.

### 14. Database Jangan Terbuka ke Publik
- **Prinsip**: Database MySQL/PostgreSQL tidak boleh diekspos port-nya (misal `0.0.0.0:3306`) ke internet terbuka tanpa perlindungan.
- **Standar**:
  - Dengarkan pada `127.0.0.1` (localhost) atau IP jaringan privat (VPN / Tailscale / LAN).
  - Gunakan SSH Tunneling jika perlu diakses dari luar kantor.

### 15. Hak Akses Database (DB Permission) Ketat
- **Prinsip**: Buat user MySQL khusus aplikasi web (bukan `root`) dengan hak akses yang dibutuhkan saja (`SELECT, INSERT, UPDATE, DELETE`).
- **Contoh**:
  ```sql
  CREATE USER 'sipln_app'@'localhost' IDENTIFIED BY 'PasswordKuat_123!@#';
  GRANT SELECT, INSERT, UPDATE, DELETE ON si_foreign_db.* TO 'sipln_app'@'localhost';
  FLUSH PRIVILEGES;
  ```

### 16. Hash Password Modern & Kuat
- **Prinsip**: Dilarang menggunakan plain text, MD5, atau SHA1 untuk menyimpan password.
- **Standar**:
  ```php
  // Membuat hash saat register / ganti password
  $hash = password_hash($password, PASSWORD_DEFAULT); // Default BCRYPT / ARGON2ID

  // Verifikasi saat login
  if (password_verify($inputPassword, $user['password_hash'])) {
      // Login sukses
  }
  ```

### 17. Konfigurasi Session Aman
- **Prinsip**:
  - `session.cookie_httponly = 1` (Mencegah cookie dibaca via script XSS).
  - `session.cookie_samesite = 'Lax'` (Mencegah serangan Cross-Site Request Forgery / CSRF).
  - `session.use_strict_mode = 1` (Mencegah Session Fixation).
  - `session_regenerate_id(true)` dipanggil tepat saat login berhasil.
- **Implementasi**:
  ```php
  ini_set('session.use_only_cookies', '1');
  ini_set('session.use_strict_mode', '1');
  ini_set('session.cookie_httponly', '1');
  ini_set('session.cookie_samesite', 'Lax');
  ```

### 18. Reset Password Aman
- **Prinsip**:
  - Gunakan generator acak kriptografis `bin2hex(random_bytes(32))`.
  - Simpan hash token di DB dan beri kolom `token_expires_at` (maksimal 15-30 menit).
  - Hapus / invalidasi token segera setelah password berhasil diganti.

### 19. Batasi Upload File
- **Prinsip**:
  - Whitelist ekstensi yang diperbolehkan (misal: hanya `.pdf`, `.docx`, `.jpg`, `.png`).
  - Tolak ekstensi executable (`.php`, `.phtml`, `.exe`, `.bat`, `.sh`, `.vbs`).
  - Batasi ukuran file (misal maksimal 5MB per berkas).
  - Gunakan nama file unik acak di server untuk mencegah path traversal / overwrite.
- **Contoh**:
  ```php
  $allowedExts = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
  $ext = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExts, true)) {
      throw new \Exception('Format file tidak didukung.');
  }
  $cleanFilename = 'dok_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  ```

### 20. Scan & Cegah Eksekusi File di Folder Upload
- **Prinsip**: Folder tempat menyimpan file upload dilarang memiliki izin eksekusi script server.
- **Implementasi `.htaccess` pada folder upload**:
  ```apache
  <FilesMatch "\.(php|phtml|php3|php4|php5|php7|php8|phps|cgi|pl|py|sh|bat|exe|cmd|msi|vbs)$">
      Order Deny,Allow
      Deny from all
  </FilesMatch>
  Options -ExecCGI -Indexes
  ```

---

## Template Checklist Audit Sebelum Go-Live

Gunakan tabel checklist di bawah ini untuk proyek web mendatang:

```markdown
### Checklist Pra-Peluncuran (Pre-Launch Security Audit)
- [ ] 1. Apakah file `.env` sudah di-protect dan tidak bisa dibuka dari browser?
- [ ] 2. Apakah semua API Key dan Database Password sudah disimpan di `.env`?
- [ ] 3. Apakah `.gitignore` sudah lengkap mengecualikan file rahasia & folder upload?
- [ ] 4. Apakah `APP_DEBUG` sudah dimatikan (`false`) dan `display_errors = 0`?
- [ ] 5. Apakah seluruh query database menggunakan prepared statement PDO?
- [ ] 6. Apakah semua output data dinamis di frontend telah di-escape (anti-XSS)?
- [ ] 7. Apakah session cookie sudah berstatus `HttpOnly`, `Strict/Lax`, dan diregenerasi saat login?
- [ ] 8. Apakah password user dienkripsi menggunakan `password_hash()`?
- [ ] 9. Apakah setiap endpoint API memeriksa otentikasi dan otorisasi role di backend?
- [ ] 10. Apakah folder upload file sudah dipasangi anti-eksekusi script (`.htaccess`)?
- [ ] 11. Apakah upload file memvalidasi whitelist ekstensi dan ukuran maksimal?
- [ ] 12. Apakah port database ditutup dari akses internet publik?
```
