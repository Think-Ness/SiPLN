<?php
/**
 * Firebase Full Sync Script
 * 
 * Jalankan script ini SATU KALI untuk mendorong SEMUA data lokal yang sudah ada
 * ke Firebase Firestore. Setelah ini, dual-write akan menjaga sinkronisasi otomatis.
 * 
 * Cara pakai: Buka di browser → http://localhost:8080/firebase_sync.php
 */


// Database connection - sesuaikan dengan config di webapp/config/common/di/db.php
$dsn = 'mysql:host=127.0.0.1;dbname=si_foreign_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    die("<h2>❌ Gagal koneksi database: " . $e->getMessage() . "</h2>");
}

// Load Firebase config
$firebaseConfig = require dirname(__DIR__) . '/config/firebase.php';

if (!$firebaseConfig['enabled'] || empty($firebaseConfig['project_id'])) {
    die("<h2>❌ Firebase belum dikonfigurasi. Edit webapp/config/firebase.php dulu.</h2>");
}

$projectId = $firebaseConfig['project_id'];
$apiKey = $firebaseConfig['api_key'];

// ============================================
// STEP 1: Authenticate to Firebase
// ============================================
echo "<h2>🔥 Firebase Full Sync</h2>";
echo "<p>Project: <strong>{$projectId}</strong></p>";
echo "<hr>";

$authUrl = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}";
$authData = json_encode([
    'email' => $firebaseConfig['sync_email'],
    'password' => $firebaseConfig['sync_password'],
    'returnSecureToken' => true,
]);

$ch = curl_init($authUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $authData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$authResp = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($authResp['idToken'])) {
    echo "<h3>❌ Gagal autentikasi Firebase!</h3>";
    echo "<pre>" . print_r($authResp, true) . "</pre>";
    die();
}

$token = $authResp['idToken'];
echo "<p>✅ Autentikasi berhasil sebagai: <strong>{$firebaseConfig['sync_email']}</strong></p>";

// ============================================
// STEP 2: Sync all Instansi
// ============================================
$instansis = $pdo->query("SELECT kode, nama_instansi, def_kepengurusan, def_pondok FROM master_instansi")->fetchAll(PDO::FETCH_ASSOC);
$counts = ['instansi' => 0, 'santri' => 0, 'paspor' => 0, 'itas' => 0, 'users' => 0, 'errors' => 0];

echo "<h3>📡 Mulai sinkronisasi...</h3>";
echo "<ul>";

foreach ($instansis as $inst) {
    $kode = $inst['kode'];
    $kep = $inst['def_kepengurusan'] ?? '';
    $pon = $inst['def_pondok'] ?? '';
    
    // Sync instansi document
    putFirestore($token, $projectId, "instansi/{$kode}", [
        'kode' => (int)$kode,
        'nama_instansi' => $inst['nama_instansi'],
        'def_kepengurusan' => $kep,
        'def_pondok' => $pon,
        'last_full_sync' => date('c'),
    ]);
    $counts['instansi']++;
    echo "<li>📌 Instansi: <strong>{$inst['nama_instansi']}</strong> ({$kep})</li>";
    
    if (empty($kep) && empty($pon)) continue;
    
    // Sync santri WITH paspor & itas JOIN
    $stmt = $pdo->prepare("
        SELECT s.kds, s.stambuk, s.nama, s.kelas, s.rayon, s.negara, s.kewarganegaraan, 
               s.pondok, s.kepengurusan, s.status_santri, s.aktif,
               p.no_paspor, p.exp_paspor, COALESCE(p.status_lokasi, 'di_kantor') as status_lokasi,
               i.no_itas, i.exp_itas, i.level_itas
        FROM master_santri s 
        LEFT JOIN mtb_paspor p ON p.kds = s.kds AND p.aktif = 1
        LEFT JOIN mtb_itas i ON i.kds = s.kds AND i.aktif = 1
        WHERE (s.kepengurusan = :kep OR s.pondok = :pon)
        GROUP BY s.kds
    ");
    $stmt->execute([':kep' => $kep, ':pon' => $pon]);
    $santris = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($santris as $s) {
        $doc = [
            'kds' => (int)$s['kds'],
            'stambuk' => (int)($s['stambuk'] ?? 0),
            'nama' => $s['nama'] ?? '',
            'kelas' => $s['kelas'] ?? '',
            'rayon' => $s['rayon'] ?? '',
            'negara' => $s['negara'] ?? '',
            'kewarganegaraan' => $s['kewarganegaraan'] ?? '',
            'pondok' => $s['pondok'] ?? '',
            'kepengurusan' => $s['kepengurusan'] ?? '',
            'status_santri' => $s['status_santri'] ?? 'Aktif',
            'aktif' => (int)($s['aktif'] ?? 1),
            'no_paspor' => $s['no_paspor'] ?? '',
            'exp_paspor' => $s['exp_paspor'] ?? '',
            'status_lokasi' => $s['status_lokasi'] ?? 'di_kantor',
            'no_itas' => $s['no_itas'] ?? '',
            'exp_itas' => $s['exp_itas'] ?? '',
            'level_itas' => (int)($s['level_itas'] ?? 0),
            'synced_at' => date('c'),
        ];
        putFirestore($token, $projectId, "instansi/{$kode}/santri/{$s['kds']}", $doc);
        $counts['santri']++;
    }
    echo "<li>&nbsp;&nbsp;&nbsp; → {$counts['santri']} santri (termasuk data paspor & ITAS)</li>";
    
    // Sync users
    $stmt = $pdo->prepare("SELECT id, username, nama_lengkap, role, instansi_id FROM users WHERE instansi_id = :inst_id");
    $stmt->execute([':inst_id' => $kode]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $u) {
        putFirestore($token, $projectId, "instansi/{$kode}/users/{$u['id']}", [
            'id' => (int)$u['id'],
            'username' => $u['username'] ?? '',
            'nama_lengkap' => $u['nama_lengkap'] ?? '',
            'role' => $u['role'] ?? '',
            'instansi_id' => $u['instansi_id'],
            'synced_at' => date('c'),
        ]);
        $counts['users']++;
    }
}

// Sync super admin users
$superAdmins = $pdo->query("SELECT id, username, nama_lengkap, role, instansi_id FROM users WHERE role = 'super_admin'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($superAdmins as $u) {
    putFirestore($token, $projectId, "instansi/0/users/{$u['id']}", [
        'id' => (int)$u['id'],
        'username' => $u['username'] ?? '',
        'nama_lengkap' => $u['nama_lengkap'] ?? '',
        'role' => $u['role'] ?? '',
        'instansi_id' => null,
        'synced_at' => date('c'),
    ]);
    $counts['users']++;
}

echo "</ul>";
echo "<hr>";
echo "<h3>✅ Sinkronisasi Selesai!</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;font-family:Arial;'>";
echo "<tr><th>Data</th><th>Jumlah</th></tr>";
echo "<tr><td>🏫 Instansi / Kampus</td><td><strong>{$counts['instansi']}</strong></td></tr>";
echo "<tr><td>👤 Santri (+ Paspor & ITAS)</td><td><strong>{$counts['santri']}</strong></td></tr>";
echo "<tr><td>👥 Users</td><td><strong>{$counts['users']}</strong></td></tr>";
echo "</table>";
echo "<br><p><em>Sekarang buka Firebase Dashboard untuk melihat datanya secara real-time!</em></p>";

// ============================================
// HELPER FUNCTION
// ============================================
function putFirestore($token, $projectId, $path, $data) {
    $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$path}";
    
    $fields = [];
    foreach ($data as $key => $value) {
        if ($value === null) {
            $fields[$key] = ['nullValue' => null];
        } elseif (is_bool($value)) {
            $fields[$key] = ['booleanValue' => $value];
        } elseif (is_int($value)) {
            $fields[$key] = ['integerValue' => (string)$value];
        } elseif (is_float($value)) {
            $fields[$key] = ['doubleValue' => $value];
        } else {
            $fields[$key] = ['stringValue' => (string)$value];
        }
    }
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => json_encode(['fields' => $fields]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 400) {
        echo "<li style='color:red'>❌ Error ({$httpCode}) pada path: {$path} - " . substr($resp, 0, 200) . "</li>";
    }
    
    return json_decode($resp, true);
}
?>
