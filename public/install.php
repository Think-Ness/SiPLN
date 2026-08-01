<?php
/**
 * ============================================================
 *  INSTALLATION WIZARD — Sistem Informasi Pondok Luar Negeri
 * ============================================================
 * 
 * Standalone installer (no framework dependencies).
 * Guides the user through first-time setup.
 */

// --- Security: Block access if already installed ---
$rootPath = dirname(__DIR__);
$lockFile = $rootPath . '/config/installed.lock';

if (file_exists($lockFile)) {
    http_response_code(403);
    echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akses Ditolak</title>
    <base href="./">
    <link href="assets/offline/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/offline/css/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center p-5 bg-white shadow-sm rounded-4 border">
        <h1 class="text-danger mb-3"><i class="bi bi-shield-lock-fill"></i> Akses Ditolak</h1>
        <p class="text-muted">Aplikasi sudah diinstal. Harap hapus folder <code>config/installed.lock</code> jika ingin menginstal ulang.</p>
        <a href="index.php" class="btn btn-primary mt-3">Kembali ke Beranda</a>
    </div>
</body>
</html>';
    exit;
}

// --- Helper Functions ---
function checkRequirements(): array {
    return [
        [
            'name' => 'PHP Version >= 8.1',
            'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'current' => PHP_VERSION,
            'type' => 'required'
        ],
        [
            'name' => 'PDO MySQL Extension',
            'status' => extension_loaded('pdo_mysql'),
            'current' => extension_loaded('pdo_mysql') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'required'
        ],
        [
            'name' => 'cURL Extension (Firebase Auth)',
            'status' => extension_loaded('curl'),
            'current' => extension_loaded('curl') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'required'
        ],
        [
            'name' => 'COM (com_dotnet) - Khusus Windows',
            'status' => extension_loaded('com_dotnet'),
            'current' => extension_loaded('com_dotnet') ? 'Aktif' : 'Tidak Aktif (Wajib untuk Surat/PDF)',
            'type' => 'required'
        ],
        [
            'name' => 'MBString (Pemrosesan String)',
            'status' => extension_loaded('mbstring'),
            'current' => extension_loaded('mbstring') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'required'
        ],
        [
            'name' => 'GD / Image (Upload Foto)',
            'status' => extension_loaded('gd'),
            'current' => extension_loaded('gd') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'required'
        ],
        [
            'name' => 'ZIP (Ekstraksi/Kompresi File)',
            'status' => extension_loaded('zip'),
            'current' => extension_loaded('zip') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'required'
        ],
        [
            'name' => 'Fileinfo (Deteksi MIME Tipe)',
            'status' => extension_loaded('fileinfo'),
            'current' => extension_loaded('fileinfo') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'required'
        ],
        [
            'name' => 'Intl Extension (Format Tanggal)',
            'status' => extension_loaded('intl'),
            'current' => extension_loaded('intl') ? 'Aktif' : 'Tidak Aktif',
            'type' => 'optional'
        ]
    ];
}

$requirements = checkRequirements();
$allRequiredPassed = true;
foreach ($requirements as $req) {
    if ($req['type'] === 'required' && !$req['status']) {
        $allRequiredPassed = false;
        break;
    }
}

// Fetch master_instansi dynamically from Firestore if online
$instansiList = [];
$firebaseConfigPath = $rootPath . '/config/firebase.php';
if (file_exists($firebaseConfigPath)) {
    $firebaseConfig = require $firebaseConfigPath;
    if (!empty($firebaseConfig['project_id']) && ($firebaseConfig['enabled'] ?? false)) {
        $url = "https://firestore.googleapis.com/v1/projects/{$firebaseConfig['project_id']}/databases/(default)/documents/instansi?pageSize=100";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['documents'])) {
                foreach ($data['documents'] as $doc) {
                    $fields = $doc['fields'] ?? [];
                    $kode = isset($fields['kode']['integerValue']) ? (int)$fields['kode']['integerValue'] : 0;
                    if ($kode === 0 && isset($fields['kode']['stringValue'])) {
                        $kode = (int)$fields['kode']['stringValue'];
                    }
                    $nama = $fields['nama_instansi']['stringValue'] ?? '';
                    $pondok = $fields['def_pondok']['stringValue'] ?? '';
                    $kepengurusan = $fields['def_kepengurusan']['stringValue'] ?? '';
                    
                    if ($kode > 0 && !empty($nama)) {
                        $instansiList[] = [
                            'kode' => $kode,
                            'nama_instansi' => $nama,
                            'pondok' => $pondok,
                            'kepengurusan' => $kepengurusan
                        ];
                    }
                }
                // Sort by kode ascending
                usort($instansiList, function($a, $b) {
                    return $a['kode'] <=> $b['kode'];
                });
            }
        }
    }
}

// Fallback if offline or failed to fetch
if (empty($instansiList)) {
    $instansiList = [
        ['kode' => 1, 'nama_instansi' => 'Pembimbing Luar Negeri Pusat', 'pondok' => 'G1', 'kepengurusan' => 'Ponorogo'],
        ['kode' => 2, 'nama_instansi' => 'Pembimbing Luar Negeri Putri', 'pondok' => 'GP', 'kepengurusan' => 'Mantingan'],
        ['kode' => 3, 'nama_instansi' => 'Pembimbing Luar Negeri G3', 'pondok' => 'G3', 'kepengurusan' => 'Kediri'],
        ['kode' => 4, 'nama_instansi' => 'Pembimbing Luar Negeri G2', 'pondok' => 'G2', 'kepengurusan' => 'Ponorogo'],
        ['kode' => 5, 'nama_instansi' => 'Pembimbing Luar Negeri G4', 'pondok' => 'G4', 'kepengurusan' => 'Banyuwangi']
    ];
}

// --- AJAX Handlers ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    switch ($action) {
        case 'test_db':
            try {
                getDbConnection($input);
                echo json_encode(['success' => true, 'message' => 'Koneksi ke database berhasil!']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
        case 'run_install':
            $step = $input['step'] ?? '';
            runInstallStep($step, $rootPath, $input, $instansiList);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

function runInstallStep(string $step, string $rootPath, array $input, array $instansiList = []): void
{
    switch ($step) {
        case 'write_config':
            runWriteConfig($rootPath, $input);
            break;
        case 'import_sql':
            runImportSql($rootPath, $input);
            break;
        case 'run_migrations':
            runMigrations($rootPath, $input);
            break;
        case 'create_admin':
            runCreateAdmin($rootPath, $input, $instansiList);
            break;
        case 'finalize':
            runFinalize($rootPath, $input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => "Unknown step: $step"]);
    }
}

function getDbConnection(array $input): PDO
{
    $host = $input['db_host'] ?? '127.0.0.1';
    $port = $input['db_port'] ?? '3306';
    $dbname = $input['db_name'] ?? 'si_foreign_db';
    $username = $input['db_user'] ?? 'root';
    $password = $input['db_pass'] ?? '';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function runWriteConfig(string $rootPath, array $input): void
{
    $host = $input['db_host'] ?? '127.0.0.1';
    $port = $input['db_port'] ?? '3306';
    $dbname = $input['db_name'] ?? 'si_foreign_db';
    $username = $input['db_user'] ?? 'root';
    $password = $input['db_pass'] ?? '';

    $dsn = "mysql:host=$host" . ($port !== '3306' ? ";port=$port" : '') . ";dbname=$dbname";
    $escapedUser = addcslashes($username, "'\\");
    $escapedPass = addcslashes($password, "'\\");

    $configContent = "<?php\n\ndeclare(strict_types=1);\n\nuse Yiisoft\\Db\\Connection\\ConnectionInterface;\nuse Yiisoft\\Db\\Mysql\\Connection;\nuse Yiisoft\\Db\\Mysql\\Driver;\n\nreturn [\n    ConnectionInterface::class => [\n        'class' => Connection::class,\n        '__construct()' => [\n            'driver' => new Driver('$dsn', '$escapedUser', '$escapedPass'),\n        ],\n    ],\n];\n";

    $configDir = $rootPath . '/config/common/di';
    if (!is_dir($configDir)) {
        mkdir($configDir, 0755, true);
    }

    $result = file_put_contents($configDir . '/db.php', $configContent);
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Gagal menulis file konfigurasi database.']);
        return;
    }

    echo json_encode(['success' => true, 'message' => 'Konfigurasi database berhasil ditulis.']);
}

function runImportSql(string $rootPath, array $input): void
{
    $sqlFile = $rootPath . '/db_install.sql';
    if (!file_exists($sqlFile)) {
        echo json_encode(['success' => false, 'message' => 'File db_install.sql tidak ditemukan!']);
        return;
    }

    try {
        $pdo = getDbConnection($input);
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            echo json_encode(['success' => false, 'message' => 'Gagal membaca file SQL.']);
            return;
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec($sql);
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $count = count($tables);

        echo json_encode([
            'success' => true,
            'message' => "Database berhasil diimpor. $count tabel dibuat.",
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
    }
}

function runMigrations(string $rootPath, array $input): void
{
    try {
        $pdo = getDbConnection($input);
        $messages = [];

        // Migration 1: Add indexes for performance
        $indexes = [
            "ALTER TABLE `master_santri` ADD INDEX IF NOT EXISTS `idx_aktif` (`aktif`)",
            "ALTER TABLE `master_santri` ADD INDEX IF NOT EXISTS `idx_kelas` (`kelas`)",
            "ALTER TABLE `master_santri` ADD INDEX IF NOT EXISTS `idx_negara` (`negara`)",
            "ALTER TABLE `master_santri` ADD INDEX IF NOT EXISTS `idx_pondok` (`pondok`)",
            "ALTER TABLE `master_santri` ADD INDEX IF NOT EXISTS `idx_kepengurusan` (`kepengurusan`)",
            "ALTER TABLE `master_santri` ADD INDEX IF NOT EXISTS `idx_status_santri` (`status_santri`)",
            "ALTER TABLE `mtb_paspor` ADD INDEX IF NOT EXISTS `idx_paspor_kds` (`kds`)",
            "ALTER TABLE `mtb_itas` ADD INDEX IF NOT EXISTS `idx_itas_kds` (`kds`)",
        ];
        foreach ($indexes as $sql) {
            try { $pdo->exec($sql); } catch (PDOException $e) {}
        }
        $messages[] = 'Index database ditambahkan.';

        // Migration 2: Add firebase_uid column to users if not exists
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'firebase_uid'")->fetchAll();
            if (empty($cols)) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `firebase_uid` VARCHAR(128) DEFAULT NULL AFTER `instansi_id`");
                $messages[] = 'Kolom firebase_uid ditambahkan.';
            } else {
                $messages[] = 'Kolom firebase_uid sudah ada.';
            }
        } catch (PDOException $e) {
            $messages[] = 'Warning firebase_uid: ' . $e->getMessage();
        }

        echo json_encode(['success' => true, 'message' => implode(' ', $messages)]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error migrasi: ' . $e->getMessage()]);
    }
}

function runCreateAdmin(string $rootPath, array $input, array $instansiList = []): void
{
    try {
        $pdo = getDbConnection($input);

        $namaLengkap = trim($input['admin_nama'] ?? '');
        $username = trim($input['admin_username'] ?? '');
        $password = $input['admin_password'] ?? '';
        $instansiId = (int)($input['admin_instansi_id'] ?? 0);
        $appName = trim($input['app_name'] ?? 'Sistem Informasi Pondok Luar Negeri');
        $timezone = trim($input['app_timezone'] ?? 'Asia/Jakarta');

        if (empty($namaLengkap) || empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Data admin tidak lengkap.']);
            return;
        }

        // Get all permission keys from master_menus
        $permissions = $pdo->query("SELECT permission_key FROM master_menus WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
        $allPerms = [];
        foreach ($permissions as $p) {
            $allPerms[] = $p;
            $allPerms[] = $p . '_edit';
        }
        
        $firebaseUid = null;

        // --- 1. DAFTAR/CEK FIREBASE DAHULU ---
        $firebaseMsg = '';
        try {
            $firebaseConfig = require $rootPath . '/config/firebase.php';
            if (!empty($firebaseConfig['api_key']) && ($firebaseConfig['enabled'] ?? false)) {
                $firebaseEmail = str_contains($username, '@') ? $username : $username . '@pln.local';
                $url = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . $firebaseConfig['api_key'];
                
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode([
                        'email' => $firebaseEmail,
                        'password' => $password,
                        'displayName' => $namaLengkap,
                        'returnSecureToken' => true,
                    ]),
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($response !== false) {
                    $resData = json_decode($response, true);
                    
                    if ($httpCode === 400 && isset($resData['error']['message']) && $resData['error']['message'] === 'EMAIL_EXISTS') {
                        // Username sudah dipakai di Firebase. 
                        // Verifikasi apakah passwordnya benar (artinya ini pemilik akun yang instal ulang)
                        $signInUrl = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . $firebaseConfig['api_key'];
                        $ch2 = curl_init($signInUrl);
                        curl_setopt_array($ch2, [
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                            CURLOPT_POSTFIELDS => json_encode([
                                'email' => $firebaseEmail,
                                'password' => $password,
                                'returnSecureToken' => true,
                            ]),
                            CURLOPT_TIMEOUT => 10,
                            CURLOPT_SSL_VERIFYPEER => false,
                        ]);
                        $res2 = curl_exec($ch2);
                        $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                        curl_close($ch2);
                        
                        if ($httpCode2 === 200) {
                            $res2Data = json_decode($res2, true);
                            $firebaseUid = $res2Data['localId'] ?? null;
                            $firebaseMsg = ' (Akun Firebase lama berhasil ditautkan)';
                        } else {
                            // Password salah! Akun ini milik orang lain.
                            echo json_encode([
                                'success' => false, 
                                'message' => "Username '{$username}' sudah dipakai oleh instansi lain. Silakan gunakan username yang unik, atau masukkan password yang tepat jika ini akun Anda."
                            ]);
                            return; // ABORT INSTALLATION!
                        }
                    } elseif ($httpCode === 200) {
                        // Registrasi baru berhasil
                        $firebaseUid = $resData['localId'] ?? null;
                        $firebaseMsg = ' (Akun Firebase baru dibuat)';
                    }
                }
            }
        } catch (Exception $e) {
            // Abaikan jika tidak ada internet
        }

        // --- 1.5. PASTIKAN INSTANSI ADA DI LOKAL DULU ---
        if ($instansiId > 0) {
            $stmtCheck = $pdo->prepare("SELECT kode FROM master_instansi WHERE kode = ?");
            $stmtCheck->execute([$instansiId]);
            if (!$stmtCheck->fetch()) {
                // Instansi belum ada di tabel lokal (karena db_install.sql lama). Insert sekarang!
                foreach ($instansiList as $inst) {
                    if ((int)$inst['kode'] === $instansiId) {
                        $stmtInsert = $pdo->prepare("INSERT INTO master_instansi (kode, nama_instansi, pondok, kepengurusan, def_kepengurusan, def_pondok) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtInsert->execute([
                            $inst['kode'], 
                            $inst['nama_instansi'], 
                            $inst['pondok'], 
                            $inst['kepengurusan'], 
                            $inst['kepengurusan'], 
                            $inst['pondok']
                        ]);
                        break;
                    }
                }
            }
        }

        // --- 2. INSERT KE DATABASE LOKAL ---
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password_hash`, `nama_lengkap`, `role`, `permissions`, `instansi_id`, `firebase_uid`) VALUES (:username, :password_hash, :nama_lengkap, 'admin_instansi', :permissions, :instansi_id, :firebase_uid)");
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => $passwordHash,
            ':nama_lengkap' => $namaLengkap,
            ':permissions' => json_encode($allPerms),
            ':instansi_id' => $instansiId > 0 ? $instansiId : null,
            ':firebase_uid' => $firebaseUid,
        ]);

        echo json_encode(['success' => true, 'message' => 'Akun Admin berhasil dibuat' . $firebaseMsg . '.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error membuat akun: ' . $e->getMessage()]);
    }
}

function runFinalize(string $rootPath, array $input): void
{
    $lockFile = $rootPath . '/config/installed.lock';
    
    $info = [
        'installed_at' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'app_name' => $input['app_name'] ?? 'Sistem Informasi PLN'
    ];
    
    if (file_put_contents($lockFile, json_encode($info, JSON_PRETTY_PRINT)) !== false) {
        echo json_encode(['success' => true, 'message' => 'Sistem berhasil dikunci dan siap digunakan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat file installed.lock. Anda mungkin perlu membuatnya manual.']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi — Sistem Informasi PLN</title>
    <base href="./">
    <link rel="icon" href="assets/logopln.png" type="image/png">
    <link href="assets/offline/css/inter.css" rel="stylesheet">
    <link href="assets/offline/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/offline/css/bootstrap-icons.css" rel="stylesheet">
    <style>
        .modal { display: none; } /* Fallback jika bootstrap CSS gagal dimuat */
        :root {
            --accent: #0d6efd;
            --success: #198754;
            --warning: #ffc107;
            --danger: #dc3545;
            --text-primary: #212529;
            --text-secondary: #6c757d;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f3f8fc 0%, #e1e9f4 100%);
            min-height: 100vh;
            margin: 0;
            position: relative;
            overflow-x: hidden;
            color: var(--text-primary);
        }
        
        /* Background Orbs */
        .orb-1 { position: fixed; width: 450px; height: 450px; background: linear-gradient(135deg, rgba(13, 110, 253, 0.15), rgba(13, 202, 240, 0.15)); border-radius: 50%; top: -100px; right: -100px; filter: blur(70px); z-index: -1; }
        .orb-2 { position: fixed; width: 350px; height: 350px; background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 201, 151, 0.1)); border-radius: 50%; bottom: -50px; left: -50px; filter: blur(50px); z-index: -1; }
        
        .installer-wrapper { max-width: 760px; margin: 0 auto; padding: 2rem 1rem; position: relative; z-index: 10; }
        
        /* Glassmorphism Card */
        .installer-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            margin-bottom: 2rem;
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        
        .installer-header { text-align: center; margin-bottom: 2.5rem; }
        .brand-icon { width: 70px; height: 70px; background: linear-gradient(135deg, #0d6efd, #0dcaf0); border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; color: white; box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3); margin-bottom: 1rem; }
        .installer-header h1 { font-size: 1.6rem; font-weight: 800; color: #212529; margin: 0; letter-spacing: -0.5px; }
        .installer-header .subtitle { color: #6c757d; font-size: 0.95rem; margin-top: 0.25rem; font-weight: 500; }
        
        /* Steps */
        .step-indicator { display: flex; justify-content: space-between; position: relative; margin-bottom: 2.5rem; padding: 0 1rem; }
        .step-indicator::before { content: ''; position: absolute; top: 18px; left: 30px; right: 30px; height: 3px; background: rgba(0,0,0,0.06); z-index: 0; border-radius: 2px; }
        .step-indicator::after { content: ''; position: absolute; top: 18px; left: 30px; width: var(--progress-width, 0%); height: 3px; background: var(--success); z-index: 1; transition: width 0.4s ease; border-radius: 2px; }
        .step-dot-wrap { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; }
        .step-dot { width: 38px; height: 38px; border-radius: 50%; background: #fff; border: 2px solid rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; color: #adb5bd; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.03); }
        .step-dot-wrap.active .step-dot { border-color: var(--accent); background: var(--accent); color: white; box-shadow: 0 8px 15px rgba(13, 110, 253, 0.3); transform: scale(1.1); }
        .step-dot-wrap.done .step-dot { border-color: var(--success); background: var(--success); color: white; }
        .step-label { font-size: 0.75rem; font-weight: 600; color: #adb5bd; margin-top: 10px; position: absolute; top: 38px; white-space: nowrap; transition: color 0.3s ease; }
        .step-dot-wrap.active .step-label { color: var(--accent); }
        .step-dot-wrap.done .step-label { color: var(--success); }
        
        .installer-card h2 { font-size: 1.3rem; font-weight: 700; color: #212529; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .card-desc { color: #6c757d; font-size: 0.95rem; margin-bottom: 2rem; line-height: 1.6; }
        
        .form-label { font-size: 0.85rem; font-weight: 600; color: #495057; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.03em; }
        .form-control, .form-select { background: rgba(255, 255, 255, 0.9) !important; border: 1px solid rgba(0, 0, 0, 0.1) !important; padding: 0.8rem 1rem; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02); }
        .form-control:focus, .form-select:focus { background: #ffffff !important; border-color: var(--accent) !important; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15) !important; outline: none; }
        
        .btn-primary { background: linear-gradient(135deg, #0d6efd, #0a58ca); border: none; border-radius: 0.75rem; padding: 0.8rem 1.75rem; font-weight: 600; font-size: 1rem; box-shadow: 0 8px 15px rgba(13, 110, 253, 0.25); transition: all 0.3s ease; color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 20px rgba(13, 110, 253, 0.35); color: white; }
        .btn-primary:disabled { opacity: 0.65; transform: none; box-shadow: none; background: #6c757d; }
        .btn-outline-secondary { background: rgba(255,255,255,0.8); border: 1px solid rgba(0,0,0,0.1); color: #495057; border-radius: 0.75rem; padding: 0.8rem 1.5rem; font-weight: 600; transition: all 0.2s ease; }
        .btn-outline-secondary:hover { background: #f8f9fa; border-color: #adb5bd; }
        
        .nav-buttons { display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 1.5rem; }
        
        .req-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; margin-top: -0.5rem; }
        .req-table td { padding: 0.8rem 1rem; background: rgba(255,255,255,0.5); font-size: 0.9rem; font-weight: 500; border: 1px solid rgba(0,0,0,0.03); border-width: 1px 0; }
        .req-table tr td:first-child { border-radius: 0.5rem 0 0 0.5rem; width: 45px; text-align: center; border-left-width: 1px; }
        .req-table tr td:last-child { border-radius: 0 0.5rem 0.5rem 0; text-align: right; font-weight: 600; border-right-width: 1px; }
        
        .alert-box { border-radius: 0.75rem; padding: 1rem 1.25rem; font-size: 0.95rem; display: none; font-weight: 500; border-left: 4px solid transparent; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .alert-box.success { background: rgba(25, 135, 84, 0.05); border-left-color: var(--success); color: #146c43; }
        .alert-box.error { background: rgba(220, 53, 69, 0.05); border-left-color: var(--danger); color: #b02a37; }
        
        .install-step-list { list-style: none; padding: 0; margin: 0; background: rgba(255,255,255,0.5); border-radius: 1rem; padding: 0.5rem; border: 1px solid rgba(0,0,0,0.03); }
        .install-step-list li { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px dashed rgba(0,0,0,0.08); }
        .install-step-list li:last-child { border-bottom: none; }
        .install-step-list .step-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; background: rgba(0,0,0,0.05); color: #adb5bd; }
        .install-step-list .running .step-icon { background: rgba(13,110,253,0.1); color: var(--accent); }
        .install-step-list .done .step-icon { background: rgba(25,135,84,0.1); color: var(--success); }
        .install-step-list .failed .step-icon { background: rgba(220,53,69,0.1); color: var(--danger); }
        .install-step-list .step-text { flex: 1; font-weight: 600; font-size: 0.95rem; }
        .install-step-list .step-msg { font-size: 0.8rem; color: #6c757d; font-weight: 400; margin-top: 2px; }
        
        .install-progress { height: 8px; background: rgba(0,0,0,0.05); border-radius: 4px; margin-bottom: 2rem; overflow: hidden; }
        .install-progress-bar { height: 100%; background: linear-gradient(90deg, var(--accent), #0dcaf0); border-radius: 4px; transition: width 0.5s ease; width: 0; }
        
        .welcome-hero { text-align: center; padding: 2rem 0; }
        .welcome-hero .hero-icon { font-size: 4rem; color: var(--accent); margin-bottom: 1.5rem; display: inline-block; padding: 1.5rem; background: rgba(13,110,253,0.05); border-radius: 50%; }
        .welcome-hero p { color: #6c757d; font-size: 1.05rem; line-height: 1.7; max-width: 500px; margin: 0 auto 2.5rem; font-weight: 500; }
        
        .summary-box { background: rgba(255,255,255,0.6); border-radius: 1rem; padding: 1.5rem; text-align: left; margin: 2rem auto; max-width: 420px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05); }
        .summary-box .label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; font-weight: 700; }
        .summary-box .row-item { display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.95rem; border-bottom: 1px dashed rgba(0,0,0,0.1); padding-bottom: 0.8rem; }
        .summary-box .row-item:last-child { border: none; margin: 0; padding: 0; }
        .summary-box .val { font-weight: 600; color: #212529; text-align: right; max-width: 250px; }
        
        .completion-icon { font-size: 5rem; color: var(--success); display: block; text-align: center; margin-bottom: 1rem; animation: bounceIn 0.6s ease; }
        @keyframes bounceIn { 0% { transform: scale(0); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }
    </style>
</head>
<body>
    <div class="orb-1"></div>
    <div class="orb-2"></div>

<div class="installer-wrapper">
    <div class="installer-header">
        <div class="brand-icon" style="background: transparent; box-shadow: none; width: 90px; height: 90px;">
            <img src="assets/logopln.png" alt="PLN Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <h1>Setup Wizard</h1>
        <div class="subtitle">Sistem Informasi Luar Negeri V.3</div>
    </div>
    
    <div class="step-indicator" id="stepIndicator">
        <div class="step-dot-wrap active" data-step="1"><div class="step-dot active">1</div><div class="step-label">Welcome</div></div>
        <div class="step-dot-wrap" data-step="2"><div class="step-dot">2</div><div class="step-label">Sistem</div></div>
        <div class="step-dot-wrap" data-step="3"><div class="step-dot">3</div><div class="step-label">Database</div></div>
        <div class="step-dot-wrap" data-step="4"><div class="step-dot">4</div><div class="step-label">Aplikasi</div></div>
        <div class="step-dot-wrap" data-step="5"><div class="step-dot">5</div><div class="step-label">Admin</div></div>
        <div class="step-dot-wrap" data-step="6"><div class="step-dot">6</div><div class="step-label">Install</div></div>
        <div class="step-dot-wrap" data-step="7"><div class="step-dot">7</div><div class="step-label">Selesai</div></div>
    </div>

    <!-- STEP 1: Welcome -->
    <div class="installer-card step-panel" id="step1">
        <div class="welcome-hero">
            <div class="hero-icon"><i class="bi bi-rocket-takeoff"></i></div>
            <h2 class="justify-content-center mb-3">Selamat Datang</h2>
            <p>Wizard ini akan memandu Anda untuk menyiapkan konfigurasi awal <strong>Informasi Luar Negeri V.3</strong> di komputer lokal Anda.</p>
            <button class="btn btn-primary btn-lg px-5 mt-2" onclick="goToStep(2)"><i class="bi bi-arrow-right-short me-1 fs-5 align-middle"></i> Mulai Instalasi</button>
        </div>
    </div>

    <!-- STEP 2: System Check -->
    <div class="installer-card step-panel" id="step2" style="display:none">
        <h2><i class="bi bi-shield-check text-primary"></i> Pemeriksaan Sistem</h2>
        <p class="card-desc">Memastikan server Anda memenuhi seluruh persyaratan minimum.</p>
        <table class="req-table">
            <?php foreach ($requirements as $check): ?>
            <tr>
                <td>
                    <?php if ($check['status']): ?>
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <?php elseif ($check['type'] === 'optional'): ?>
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($check['name']) ?></td>
                <td style="color:<?= $check['status'] ? 'var(--success)' : ($check['type']==='optional' ? 'var(--warning)' : 'var(--danger)') ?>"><?= htmlspecialchars($check['current']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <button type="button" class="btn btn-sm btn-outline-info w-100 mt-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#helpExtModal">
            <i class="bi bi-info-circle me-1"></i> Lihat Cara Mengaktifkan Persyaratan (XAMPP / Laragon)
        </button>

        <div class="nav-buttons">
            <button class="btn btn-outline-secondary" onclick="goToStep(1)"><i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali</button>
            <?php if ($allRequiredPassed): ?>
                <button class="btn btn-primary" onclick="goToStep(3)">Lanjut <i class="bi bi-arrow-right-short fs-5 align-middle"></i></button>
            <?php else: ?>
                <button class="btn btn-primary" disabled>Persyaratan Belum Terpenuhi</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- STEP 3: Database Config -->
    <div class="installer-card step-panel" id="step3" style="display:none">
        <h2><i class="bi bi-database text-primary"></i> Konfigurasi Database</h2>
        <p class="card-desc">Masukkan kredensial MySQL. Pastikan database kosong telah dibuat sebelumnya melalui phpMyAdmin.</p>
        <div class="row g-4">
            <div class="col-md-8"><label class="form-label">Host Server</label><input type="text" class="form-control" id="dbHost" value="127.0.0.1"></div>
            <div class="col-md-4"><label class="form-label">Port</label><input type="text" class="form-control" id="dbPort" value="3306"></div>
            <div class="col-12"><label class="form-label">Nama Database</label><input type="text" class="form-control" id="dbName" value="si_foreign_db"></div>
            <div class="col-md-6"><label class="form-label">Username DB</label><input type="text" class="form-control" id="dbUser" value="root"></div>
            <div class="col-md-6"><label class="form-label">Password DB</label><input type="password" class="form-control" id="dbPass" value="" placeholder="Kosongkan jika tidak ada"></div>
        </div>
        <div class="mt-4"><button class="btn btn-outline-secondary w-100" id="btnTestDb" onclick="testDbConnection()"><i class="bi bi-plug me-2"></i>Test Koneksi Server</button></div>
        <div class="alert-box success mt-3" id="dbTestSuccess"></div>
        <div class="alert-box error mt-3" id="dbTestError"></div>
        <div class="nav-buttons">
            <button class="btn btn-outline-secondary" onclick="goToStep(2)"><i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali</button>
            <button class="btn btn-primary" id="btnStep3Next" onclick="goToStep(4)" disabled>Lanjut <i class="bi bi-arrow-right-short fs-5 align-middle"></i></button>
        </div>
    </div>

    <!-- STEP 4: App Config -->
    <div class="installer-card step-panel" id="step4" style="display:none">
        <h2><i class="bi bi-sliders text-primary"></i> Pengaturan Aplikasi</h2>
        <p class="card-desc">Tentukan identitas dan zona waktu lokal sistem.</p>
        <div class="row g-4">
            <div class="col-12"><label class="form-label">Nama Aplikasi</label><input type="text" class="form-control" id="appName" value="Sistem Informasi Pondok Luar Negeri"></div>
            <div class="col-12"><label class="form-label">Zona Waktu (Timezone)</label>
                <select class="form-select" id="appTimezone">
                    <option value="Asia/Jakarta" selected>Asia/Jakarta (WIB, UTC+7)</option>
                    <option value="Asia/Makassar">Asia/Makassar (WITA, UTC+8)</option>
                    <option value="Asia/Jayapura">Asia/Jayapura (WIT, UTC+9)</option>
                    <option value="Asia/Kuala_Lumpur">Asia/Kuala_Lumpur (MYT, UTC+8)</option>
                    <option value="Asia/Bangkok">Asia/Bangkok (ICT, UTC+7)</option>
                </select>
            </div>
        </div>
        <div class="nav-buttons">
            <button class="btn btn-outline-secondary" onclick="goToStep(3)"><i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali</button>
            <button class="btn btn-primary" onclick="goToStep(5)">Lanjut <i class="bi bi-arrow-right-short fs-5 align-middle"></i></button>
        </div>
    </div>

    <!-- STEP 5: Admin Account -->
    <div class="installer-card step-panel" id="step5" style="display:none">
        <h2><i class="bi bi-person-badge text-primary"></i> Administrator Instansi</h2>
        <p class="card-desc">Buat akun untuk mengelola platform ini. Akun akan terhubung dengan Firebase Auth secara otomatis.</p>
        <div class="row g-4">
            <div class="col-12"><label class="form-label">Pilih Instansi</label>
                <select class="form-select" id="adminInstansi">
                    <?php foreach ($instansiList as $inst): ?>
                    <option value="<?= $inst['kode'] ?>"><?= htmlspecialchars($inst['nama_instansi']) ?> (<?= $inst['pondok'] ?> — <?= $inst['kepengurusan'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12"><label class="form-label">Nama Lengkap</label><input type="text" class="form-control" id="adminNama" placeholder="Contoh: Admin Pusat"></div>
            <div class="col-12">
                <label class="form-label">Username / Email</label>
                <div class="position-relative">
                    <input type="text" class="form-control pe-5" id="adminUsername" placeholder="Min. 4 karakter">
                    <span class="position-absolute top-50 end-0 translate-middle-y text-muted fw-semibold me-3" id="adminDomainSuffix" style="pointer-events: none; transition: opacity 0.2s; font-size: 0.9rem;">@pln.local</span>
                </div>
                <div class="form-text text-secondary mt-2" style="font-size:0.8rem" id="adminDomainInfo">
                    <i class="bi bi-info-circle me-1"></i>Akan didaftarkan ke Firebase sebagai <code id="adminDomainPreview">username@pln.local</code>
                </div>
                <script>
                    document.getElementById('adminUsername').addEventListener('input', function() {
                        const val = this.value;
                        const domainSuffix = document.getElementById('adminDomainSuffix');
                        const domainPreview = document.getElementById('adminDomainPreview');
                        
                        if (val.includes('@')) {
                            domainSuffix.style.opacity = '0';
                            domainPreview.textContent = val || 'username';
                        } else {
                            domainSuffix.style.opacity = '1';
                            domainPreview.textContent = (val || 'username') + '@pln.local';
                        }
                    });
                </script>
            </div>
            <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" id="adminPassword" placeholder="Min. 6 karakter"></div>
            <div class="col-md-6"><label class="form-label">Ulangi Password</label><input type="password" class="form-control" id="adminPasswordConfirm" placeholder="Ketik ulang password"></div>
        </div>
        <div class="alert-box error mt-4" id="adminError"></div>
        <div class="nav-buttons">
            <button class="btn btn-outline-secondary" onclick="goToStep(4)"><i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali</button>
            <button class="btn btn-primary" onclick="validateAndProceed()">Jalankan Instalasi <i class="bi bi-cpu ms-1"></i></button>
        </div>
    </div>

    <!-- STEP 6: Installation Progress -->
    <div class="installer-card step-panel" id="step6" style="display:none">
        <h2><i class="bi bi-gear-wide-connected text-primary"></i> Memproses Instalasi</h2>
        <p class="card-desc">Mohon tidak menutup halaman ini selama proses berlangsung.</p>
        <div class="install-progress"><div class="install-progress-bar" id="installProgressBar"></div></div>
        <ul class="install-step-list" id="installStepList">
            <li class="pending" data-istep="write_config"><div class="step-icon"><i class="bi bi-file-earmark-code"></i></div><div class="step-text">Menulis konfigurasi database<div class="step-msg"></div></div></li>
            <li class="pending" data-istep="import_sql"><div class="step-icon"><i class="bi bi-database-add"></i></div><div class="step-text">Mengimpor skema & data master<div class="step-msg"></div></div></li>
            <li class="pending" data-istep="run_migrations"><div class="step-icon"><i class="bi bi-wrench-adjustable"></i></div><div class="step-text">Menjalankan optimasi & indeks<div class="step-msg"></div></div></li>
            <li class="pending" data-istep="create_admin"><div class="step-icon"><i class="bi bi-person-check"></i></div><div class="step-text">Membuat kredensial administrator<div class="step-msg"></div></div></li>
            <li class="pending" data-istep="finalize"><div class="step-icon"><i class="bi bi-lock"></i></div><div class="step-text">Mengunci instalasi sistem<div class="step-msg"></div></div></li>
        </ul>
        <div id="installErrorNav" style="display:none;" class="mt-4 text-center">
            <button class="btn btn-outline-danger px-4" onclick="goToStep(5)"><i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali & Perbaiki Username</button>
        </div>
    </div>

    <!-- STEP 7: Complete -->
    <div class="installer-card step-panel" id="step7" style="display:none">
        <div style="text-align:center; padding: 1rem 0">
            <i class="bi bi-check-circle-fill completion-icon"></i>
            <h2 class="justify-content-center text-success mb-2">Instalasi Selesai!</h2>
            <p class="card-desc" style="margin-bottom:1.5rem">Platform kolaborasi Anda telah berhasil dikonfigurasi dan siap digunakan.</p>
            
            <div class="summary-box">
                <div class="label">Ringkasan Setup</div>
                <div class="row-item"><span>Instansi</span> <span class="val" id="summaryInstansi">-</span></div>
                <div class="row-item"><span>Username</span> <span class="val" id="summaryUsername">-</span></div>
                <div class="row-item"><span>Database</span> <span class="val" id="summaryDb">-</span></div>
            </div>
            
            <div><a href="./" class="btn btn-primary btn-lg px-5 mt-2">Buka Aplikasi <i class="bi bi-box-arrow-in-right ms-2"></i></a></div>
        </div>
    </div>
</div>

<script src="assets/offline/js/bootstrap.bundle.min.js"></script>
<script>
let currentStep = 1;

function updateProgressIndicator(n) {
    const totalSteps = 7;
    const percentage = ((n - 1) / (totalSteps - 1)) * 100;
    document.querySelector('.step-indicator').style.setProperty('--progress-width', percentage + '%');
}

function goToStep(n) {
    document.querySelectorAll('.step-panel').forEach(p => p.style.display = 'none');
    document.getElementById('step' + n).style.display = 'block';
    
    updateProgressIndicator(n);
    
    document.querySelectorAll('.step-dot-wrap').forEach(w => {
        const s = parseInt(w.dataset.step);
        const dot = w.querySelector('.step-dot');
        w.classList.remove('active', 'done');
        dot.classList.remove('active', 'done');
        if (s < n) { w.classList.add('done'); dot.classList.add('done'); dot.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>'; }
        else if (s === n) { w.classList.add('active'); dot.classList.add('active'); dot.textContent = s; }
        else { dot.textContent = s; }
    });
    currentStep = n;
    if (n === 6) setTimeout(runInstallation, 500);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

updateProgressIndicator(1);

async function testDbConnection() {
    const btn = document.getElementById('btnTestDb');
    const successBox = document.getElementById('dbTestSuccess');
    const errorBox = document.getElementById('dbTestError');
    const nextBtn = document.getElementById('btnStep3Next');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghubungkan...';
    successBox.style.display = 'none';
    errorBox.style.display = 'none';
    try {
        const res = await fetch('install.php?action=test_db', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ db_host: document.getElementById('dbHost').value, db_port: document.getElementById('dbPort').value, db_name: document.getElementById('dbName').value, db_user: document.getElementById('dbUser').value, db_pass: document.getElementById('dbPass').value }),
        });
        const data = await res.json();
        if (data.success) { successBox.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + data.message; successBox.style.display = 'block'; nextBtn.disabled = false; }
        else { errorBox.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>' + data.message; errorBox.style.display = 'block'; nextBtn.disabled = true; }
    } catch (e) { errorBox.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Gagal menghubungi server: ' + e.message; errorBox.style.display = 'block'; }
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-plug me-2"></i>Test Koneksi Server';
}

function validateAndProceed() {
    const errorBox = document.getElementById('adminError');
    const nama = document.getElementById('adminNama').value.trim();
    const username = document.getElementById('adminUsername').value.trim();
    const password = document.getElementById('adminPassword').value;
    const confirm = document.getElementById('adminPasswordConfirm').value;
    errorBox.style.display = 'none';
    if (!nama) { showAdminError('Nama lengkap wajib diisi.'); return; }
    if (username.length < 4) { showAdminError('Username minimal 4 karakter.'); return; }
    if (!/^[a-zA-Z0-9_@.-]+$/.test(username)) { showAdminError('Username/Email mengandung karakter tidak valid.'); return; }
    if (username.includes('@') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(username)) { showAdminError('Format email yang Anda masukkan tidak valid.'); return; }
    if (password.length < 6) { showAdminError('Password minimal 6 karakter.'); return; }
    if (password !== confirm) { showAdminError('Password dan konfirmasi tidak cocok.'); return; }
    goToStep(6);
}
function showAdminError(msg) { const box = document.getElementById('adminError'); box.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + msg; box.style.display = 'block'; }

function getInstallPayload() {
    return {
        db_host: document.getElementById('dbHost').value, db_port: document.getElementById('dbPort').value,
        db_name: document.getElementById('dbName').value, db_user: document.getElementById('dbUser').value,
        db_pass: document.getElementById('dbPass').value, app_name: document.getElementById('appName').value,
        app_timezone: document.getElementById('appTimezone').value,
        admin_nama: document.getElementById('adminNama').value.trim(),
        admin_username: document.getElementById('adminUsername').value.trim(),
        admin_password: document.getElementById('adminPassword').value,
        admin_instansi_id: document.getElementById('adminInstansi').value,
    };
}

async function runInstallation() {
    const steps = ['write_config', 'import_sql', 'run_migrations', 'create_admin', 'finalize'];
    const total = steps.length;
    const progressBar = document.getElementById('installProgressBar');
    const payload = getInstallPayload();
    const errorNav = document.getElementById('installErrorNav');
    if (errorNav) errorNav.style.display = 'none';
    let allSuccess = true;
    for (let i = 0; i < steps.length; i++) {
        const stepName = steps[i];
        const li = document.querySelector(`[data-istep="${stepName}"]`);
        const msgEl = li.querySelector('.step-msg');
        li.className = 'running';
        li.querySelector('.step-icon').innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        progressBar.style.width = ((i / total) * 100) + '%';
        try {
            const res = await fetch('install.php?action=run_install', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: stepName, ...payload }),
            });
            const data = await res.json();
            if (data.success) { li.className = 'done'; li.querySelector('.step-icon').innerHTML = '<i class="bi bi-check-lg"></i>'; msgEl.textContent = data.message; msgEl.style.color = 'var(--success)'; }
            else { li.className = 'failed'; li.querySelector('.step-icon').innerHTML = '<i class="bi bi-x-lg"></i>'; msgEl.textContent = data.message; msgEl.style.color = 'var(--danger)'; allSuccess = false; break; }
        } catch (e) { li.className = 'failed'; li.querySelector('.step-icon').innerHTML = '<i class="bi bi-x-lg"></i>'; msgEl.textContent = 'Network error: ' + e.message; msgEl.style.color = 'var(--danger)'; allSuccess = false; break; }
        await new Promise(r => setTimeout(r, 400));
    }
    progressBar.style.width = allSuccess ? '100%' : progressBar.style.width;
    if (allSuccess) {
        const selInstansi = document.getElementById('adminInstansi');
        document.getElementById('summaryInstansi').textContent = selInstansi.options[selInstansi.selectedIndex].text;
        document.getElementById('summaryUsername').textContent = document.getElementById('adminUsername').value;
        document.getElementById('summaryDb').textContent = document.getElementById('dbName').value;
        setTimeout(() => goToStep(7), 800);
    } else {
        if (errorNav) errorNav.style.display = 'block';
    }
}
</script>
<!-- Modal Bantuan Ekstensi PHP -->
<div class="modal fade" id="helpExtModal" tabindex="-1" aria-labelledby="helpExtModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="helpExtModalLabel"><i class="bi bi-question-circle text-info me-2"></i>Cara Mengaktifkan Ekstensi PHP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        <p class="text-muted small mb-4">Pilih jenis peladen lokal (Localhost) yang Anda gunakan di komputer ini:</p>
        
        <ul class="nav nav-pills mb-3 nav-fill gap-2" id="pills-tab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold" id="pills-xampp-tab" data-bs-toggle="pill" data-bs-target="#pills-xampp" type="button" role="tab" aria-controls="pills-xampp" aria-selected="true">XAMPP</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold" id="pills-laragon-tab" data-bs-toggle="pill" data-bs-target="#pills-laragon" type="button" role="tab" aria-controls="pills-laragon" aria-selected="false">Laragon</button>
          </li>
        </ul>
        
        <div class="tab-content border rounded p-3 bg-light" id="pills-tabContent">
          <!-- XAMPP Instructions -->
          <div class="tab-pane fade show active" id="pills-xampp" role="tabpanel" aria-labelledby="pills-xampp-tab">
            <h6 class="fw-bold mb-3">Cara Mengaktifkan di XAMPP:</h6>
            <ol class="mb-0">
                <li class="mb-2">Buka aplikasi <strong>XAMPP Control Panel</strong>.</li>
                <li class="mb-2">Pada baris <strong>Apache</strong>, klik tombol <strong>Config</strong>, lalu pilih <strong>PHP (php.ini)</strong>. File teks akan terbuka.</li>
                <li class="mb-2">Tekan <code>Ctrl + F</code> dan cari nama ekstensi yang <strong>Tidak Aktif</strong> (misal: <code>extension=gd</code>, <code>extension=curl</code>, <code>extension=pdo_mysql</code>, dsb).</li>
                <li class="mb-2">Hapus tanda titik koma ( <code>;</code> ) di depan baris tersebut. <br><em>Contoh: <code>;extension=gd</code> diubah menjadi <code>extension=gd</code></em>.</li>
                <li class="mb-2">Simpan file tersebut (<code>Ctrl + S</code>) lalu tutup.</li>
                <li class="mb-2">Kembali ke XAMPP, klik tombol <strong>Stop</strong> pada Apache, lalu klik <strong>Start</strong> kembali.</li>
                <li><strong>Muat Ulang (Refresh)</strong> halaman instalasi ini.</li>
            </ol>
          </div>
          
          <!-- Laragon Instructions -->
          <div class="tab-pane fade" id="pills-laragon" role="tabpanel" aria-labelledby="pills-laragon-tab">
            <h6 class="fw-bold mb-3">Cara Mengaktifkan di Laragon:</h6>
            <ol class="mb-0">
                <li class="mb-2">Buka aplikasi <strong>Laragon</strong>.</li>
                <li class="mb-2">Klik Kanan di mana saja pada layar Laragon (atau klik tombol Menu).</li>
                <li class="mb-2">Pilih <strong>PHP</strong> &gt; <strong>Quick Settings</strong>.</li>
                <li class="mb-2">Cari dan klik nama ekstensi yang diperlukan (seperti <code>gd</code>, <code>curl</code>, <code>pdo_mysql</code>, <code>com_dotnet</code>, <code>intl</code>).</li>
                <li class="mb-2">Laragon akan otomatis memuat ulang peladen (Reloading Apache).</li>
                <li><strong>Muat Ulang (Refresh)</strong> halaman instalasi ini.</li>
            </ol>
          </div>
        </div>

        <div class="alert alert-warning mt-4 small mb-0">
            <strong>Catatan Khusus COM (com_dotnet):</strong> Ekstensi ini diperlukan untuk fitur Surat Generator dan hanya tersedia di sistem operasi <strong>Windows</strong>.
            <hr class="my-2 border-warning opacity-50">
            Jika saat Anda mencari <code>extension=com_dotnet</code> di file <code>php.ini</code> tidak ditemukan (kosong sama sekali), Anda bisa menambahkan baris kode berikut ini secara manual di baris paling bawah file <code>php.ini</code> Anda:
            <div class="d-flex align-items-center mt-2 bg-white rounded p-1 border border-warning" style="user-select: all;">
                <code class="ms-2 me-auto user-select-all" style="color: #d63384;">extension=com_dotnet</code>
                <button type="button" class="btn btn-sm btn-light border" onclick="navigator.clipboard.writeText('extension=com_dotnet'); this.innerHTML='<i class=\'bi bi-check2\'></i>'; setTimeout(()=>this.innerHTML='<i class=\'bi bi-clipboard\'></i>', 2000);" title="Salin kode"><i class="bi bi-clipboard"></i></button>
            </div>
        </div>

      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>