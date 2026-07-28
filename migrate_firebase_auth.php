<?php
/**
 * Script Migrasi User ke Firebase Authentication
 * 
 * CARA MENJALANKAN:
 *   php migrate_firebase_auth.php
 * 
 * Script ini akan:
 * 1. Menambahkan kolom `firebase_uid` ke tabel `users` (jika belum ada)
 * 2. Membaca semua user dari MySQL lokal
 * 3. Mendaftarkan mereka ke Firebase Authentication dengan email format: username@pln.local
 * 4. Menyimpan Firebase UID kembali ke MySQL
 * 
 * Password default untuk semua user yang dimigrasi: NamaPondok123!
 * User WAJIB mengganti password setelah login pertama kali.
 */

require __DIR__ . '/vendor/autoload.php';

// Koneksi Database
$configPath = __DIR__ . '/config/common/params.php';
if (!file_exists($configPath)) {
    echo "ERROR: File konfigurasi tidak ditemukan!\n";
    exit(1);
}

$params = require $configPath;
$dbConfig = $params['yiisoft/db-mysql'] ?? null;

if (!$dbConfig) {
    // Fallback: koneksi langsung
    $host = '127.0.0.1';
    $dbname = 'si_foreign_db';
    $user = 'root';
    $pass = '';
} else {
    // Parse DSN dari config
    $dsn = $dbConfig['dsn'] ?? '';
    preg_match('/host=([^;]+)/', $dsn, $hostMatch);
    preg_match('/dbname=([^;]+)/', $dsn, $dbMatch);
    $host = $hostMatch[1] ?? '127.0.0.1';
    $dbname = $dbMatch[1] ?? 'si_foreign_db';
    $user = $dbConfig['username'] ?? 'root';
    $pass = $dbConfig['password'] ?? '';
}

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Koneksi database berhasil.\n\n";
} catch (PDOException $e) {
    echo "❌ Gagal koneksi database: " . $e->getMessage() . "\n";
    exit(1);
}

// Load Firebase config
$fbConfig = require __DIR__ . '/config/firebase.php';
$apiKey = $fbConfig['api_key'] ?? '';
$projectId = $fbConfig['project_id'] ?? '';

if (empty($apiKey) || empty($projectId)) {
    echo "❌ Konfigurasi Firebase belum lengkap (api_key atau project_id kosong).\n";
    exit(1);
}

echo "🔧 Firebase Project: {$projectId}\n\n";

// ==========================================
// LANGKAH 1: Tambah kolom firebase_uid
// ==========================================
echo "--- LANGKAH 1: Memastikan kolom firebase_uid ada ---\n";
try {
    $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'firebase_uid'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN firebase_uid VARCHAR(128) NULL DEFAULT NULL AFTER id");
        $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX idx_firebase_uid (firebase_uid)");
        echo "✅ Kolom `firebase_uid` berhasil ditambahkan ke tabel `users`.\n\n";
    } else {
        echo "ℹ️  Kolom `firebase_uid` sudah ada.\n\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Gagal alter table (mungkin kolom sudah ada): " . $e->getMessage() . "\n\n";
}

// ==========================================
// LANGKAH 2: Baca semua user
// ==========================================
echo "--- LANGKAH 2: Membaca daftar user ---\n";
$users = $pdo->query("SELECT id, username, nama_lengkap, firebase_uid FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "📋 Total user: " . count($users) . "\n\n";

// ==========================================
// LANGKAH 3: Daftarkan ke Firebase Auth
// ==========================================
echo "--- LANGKAH 3: Mendaftarkan ke Firebase Auth ---\n";
$defaultPassword = 'NamaPondok123!';
$emailDomain = '@pln.local';
$success = 0;
$skipped = 0;
$failed = 0;

foreach ($users as $u) {
    $uid = $u['id'];
    $username = $u['username'];
    $namaLengkap = $u['nama_lengkap'];
    $existingFirebaseUid = $u['firebase_uid'];

    // Skip jika sudah ada Firebase UID
    if (!empty($existingFirebaseUid)) {
        echo "  ⏭️  [{$uid}] {$username} — sudah terdaftar (UID: {$existingFirebaseUid})\n";
        $skipped++;
        continue;
    }

    $email = $username . $emailDomain;

    // Daftarkan ke Firebase Auth
    $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key={$apiKey}";
    $payload = json_encode([
        'email' => $email,
        'password' => $defaultPassword,
        'displayName' => $namaLengkap,
        'returnSecureToken' => true,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['localId'])) {
        $firebaseUid = $result['localId'];

        // Simpan Firebase UID ke MySQL
        $stmt = $pdo->prepare("UPDATE users SET firebase_uid = :uid WHERE id = :id");
        $stmt->execute([':uid' => $firebaseUid, ':id' => $uid]);

        echo "  ✅ [{$uid}] {$username} → {$email} (UID: {$firebaseUid})\n";
        $success++;
    } elseif (isset($result['error']['message']) && $result['error']['message'] === 'EMAIL_EXISTS') {
        // User sudah ada di Firebase, coba login untuk dapatkan UID
        $loginUrl = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}";
        $loginPayload = json_encode([
            'email' => $email,
            'password' => $defaultPassword,
            'returnSecureToken' => true,
        ]);

        $ch2 = curl_init($loginUrl);
        curl_setopt_array($ch2, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $loginPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
        ]);
        $loginResponse = curl_exec($ch2);
        curl_close($ch2);
        $loginResult = json_decode($loginResponse, true);

        if (isset($loginResult['localId'])) {
            $firebaseUid = $loginResult['localId'];
            $stmt = $pdo->prepare("UPDATE users SET firebase_uid = :uid WHERE id = :id");
            $stmt->execute([':uid' => $firebaseUid, ':id' => $uid]);
            echo "  🔗 [{$uid}] {$username} → sudah ada di Firebase, UID disimpan: {$firebaseUid}\n";
            $success++;
        } else {
            echo "  ⚠️  [{$uid}] {$username} → sudah ada di Firebase tapi gagal login untuk ambil UID\n";
            $failed++;
        }
    } else {
        $errMsg = $result['error']['message'] ?? 'Unknown error';
        echo "  ❌ [{$uid}] {$username} → Gagal: {$errMsg}\n";
        $failed++;
    }

    // Rate limiting (hindari 429 dari Firebase)
    usleep(200000); // 200ms delay
}

echo "\n";
echo "==========================================\n";
echo "📊 RINGKASAN MIGRASI\n";
echo "==========================================\n";
echo "  ✅ Berhasil: {$success}\n";
echo "  ⏭️  Dilewati (sudah ada): {$skipped}\n";
echo "  ❌ Gagal: {$failed}\n";
echo "  📋 Total: " . count($users) . "\n";
echo "\n";
echo "📌 Password default untuk semua user baru: {$defaultPassword}\n";
echo "⚠️  Minta semua user mengganti password setelah login pertama!\n";
echo "==========================================\n";
