<?php
declare(strict_types=1);

namespace App\Shared;

use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * FirebaseSync - Sinkronisasi data lokal ke Firebase Firestore (Real-Time Dual-Write)
 * 
 * Menggunakan Firestore REST API langsung via cURL.
 * Tidak memerlukan composer package tambahan.
 */
final class FirebaseSync
{
    private static ?array $config = null;
    private static ?string $idToken = null;
    private static int $tokenExpiry = 0;

    /**
     * Load konfigurasi Firebase
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            $configPath = dirname(__DIR__, 2) . '/config/firebase.php';
            if (file_exists($configPath)) {
                self::$config = require $configPath;
            } else {
                self::$config = ['enabled' => false];
            }
        }
        return self::$config;
    }

    /**
     * Cek apakah Firebase sync aktif
     */
    public static function isEnabled(): bool
    {
        $config = self::getConfig();
        return ($config['enabled'] ?? false) === true
            && !empty($config['project_id'])
            && $config['project_id'] !== 'GANTI_DENGAN_PROJECT_ID';
    }

    /**
     * Dapatkan instansi kode dari session saat ini
     */
    private static function getInstansiKode(): string
    {
        $kode = $_SESSION['instansi_id'] ?? '0';
        return (string)$kode;
    }

    /**
     * Autentikasi ke Firebase Auth dan dapatkan ID Token
     */
    private static function authenticate(): ?string
    {
        // Gunakan cached token jika masih berlaku
        if (self::$idToken && time() < self::$tokenExpiry) {
            return self::$idToken;
        }

        $config = self::getConfig();
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . $config['api_key'];

        $response = self::httpPost($url, [
            'email' => $config['sync_email'],
            'password' => $config['sync_password'],
            'returnSecureToken' => true,
        ]);

        if ($response && isset($response['idToken'])) {
            self::$idToken = $response['idToken'];
            // Token berlaku 1 jam, kita refresh 5 menit sebelumnya
            self::$tokenExpiry = time() + 3300;
            return self::$idToken;
        }

        error_log('[FirebaseSync] Autentikasi Firebase gagal: ' . json_encode($response));
        return null;
    }

    /**
     * Dapatkan daftar kode instansi berdasarkan kepengurusan dan pondok santri
     */
    private static function getTargetKodes(?string $kepengurusan, ?string $pondok): array
    {
        static $instansiMap = null;
        if ($instansiMap === null) {
            $instansiMap = [];
            try {
                // Gunakan koneksi database dari konfigurasi
                $dbConfigFile = dirname(__DIR__, 2) . '/config/common/di/db.php';
                $dbContent = file_get_contents($dbConfigFile);
                if (preg_match("/new Driver\(\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)'\s*\)/", $dbContent, $matches)) {
                    $dsn = stripslashes($matches[1]);
                    $dbUser = stripslashes($matches[2]);
                    $dbPass = stripslashes($matches[3]);
                    $pdo = new \PDO($dsn, $dbUser, $dbPass);
                } else {
                    $pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8mb4', 'root', '');
                }
                $stmt = $pdo->query("SELECT kode, def_kepengurusan, def_pondok FROM master_instansi");
                $instansiMap = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        $kodes = [];
        $sessionKode = self::getInstansiKode();
        if ($sessionKode && $sessionKode !== '0') {
            $kodes[] = $sessionKode;
        }

        foreach ($instansiMap as $inst) {
            $kode = (string)$inst['kode'];
            if (($kepengurusan && $inst['def_kepengurusan'] === $kepengurusan) ||
                ($pondok && $inst['def_pondok'] === $pondok)) {
                $kodes[] = $kode;
            }
        }
        return array_unique($kodes);
    }

    // ================================================
    // PUBLIC API: Sync Individual Records (Dual-Write)
    // ================================================

    /**
     * Sync satu record santri ke Firestore
     * @param string $kds
     * @param array $data - data santri dari master_santri
     * @param string|null $instansiKode
     * @param array|null $pasporData - data paspor aktif (opsional, untuk embed)
     * @param array|null $itasData - data itas aktif (opsional, untuk embed)
     */
    public static function syncSantri(string $kds, array $data, ?string $instansiKode = null, ?array $pasporData = null, ?array $itasData = null): void
    {
        if (!self::isEnabled()) return;
        
        $kodes = self::getTargetKodes($data['kepengurusan'] ?? null, $data['pondok'] ?? null);
        if ($instansiKode && !in_array($instansiKode, $kodes)) {
            $kodes[] = $instansiKode;
        }

        try {
            $doc = [
                'kds' => (int)$kds,
                'stambuk' => (int)preg_replace('/[^0-9]/', '', (string)($data['stambuk'] ?? '0')),
                'nama' => $data['nama'] ?? '',
                'kelas' => $data['kelas'] ?? '',
                'rayon' => $data['rayon'] ?? '',
                'negara' => $data['negara'] ?? '',
                'kewarganegaraan' => $data['kewarganegaraan'] ?? '',
                'pondok' => $data['pondok'] ?? '',
                'kepengurusan' => $data['kepengurusan'] ?? '',
                'status_santri' => $data['status_santri'] ?? 'Aktif',
                'aktif' => (int)($data['aktif'] ?? 1),
                // Embed paspor info
                'no_paspor' => $pasporData['no_paspor'] ?? ($data['no_paspor'] ?? ''),
                'exp_paspor' => $pasporData['exp_paspor'] ?? ($data['exp_paspor'] ?? ''),
                'status_lokasi' => $pasporData['status_lokasi'] ?? ($data['status_lokasi'] ?? 'di_kantor'),
                // Embed itas info
                'no_itas' => $itasData['no_itas'] ?? ($data['no_itas'] ?? ''),
                'exp_itas' => $itasData['exp_itas'] ?? ($data['exp_itas'] ?? ''),
                'level_itas' => (int)($itasData['level_itas'] ?? ($data['level_itas'] ?? 0)),
                'synced_at' => date('c'),
            ];

            foreach ($kodes as $kode) {
                self::putDocument("instansi/{$kode}/santri/{$kds}", $doc);
            }
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal sync santri KDS=' . $kds . ': ' . $e->getMessage());
        }
    }

    /**
     * Hapus record santri dari Firestore
     */
    public static function deleteSantri(string $kds, ?string $instansiKode = null): void
    {
        if (!self::isEnabled()) return;
        
        // Coba baca dari DB jika bisa (meskipun seringkali data sudah terhapus)
        // Cara paling aman, kita hapus dari instansi aktif + jika $instansiKode disediakan
        $kodes = [];
        $sessionKode = self::getInstansiKode();
        if ($sessionKode && $sessionKode !== '0') {
            $kodes[] = $sessionKode;
        }
        if ($instansiKode && !in_array($instansiKode, $kodes)) {
            $kodes[] = $instansiKode;
        }

        try {
            foreach ($kodes as $kode) {
                self::deleteDocument("instansi/{$kode}/santri/{$kds}");
            }
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal hapus santri KDS=' . $kds . ': ' . $e->getMessage());
        }
    }

    /**
     * Sync satu record paspor ke Firestore
     */
    public static function syncPaspor(string $id, array $data, ?string $instansiKode = null): void
    {
        if (!self::isEnabled()) return;
        $kode = $instansiKode ?? self::getInstansiKode();

        try {
            $doc = [
                'id' => (int)$id,
                'kds' => (int)($data['kds'] ?? 0),
                'no_paspor' => $data['no_paspor'] ?? '',
                'exp_paspor' => $data['exp_paspor'] ?? '',
                'status_lokasi' => $data['status_lokasi'] ?? 'di_kantor',
                'aktif' => (int)($data['aktif'] ?? 1),
                'synced_at' => date('c'),
            ];

            self::putDocument("instansi/{$kode}/paspor/{$id}", $doc);
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal sync paspor ID=' . $id . ': ' . $e->getMessage());
        }
    }

    /**
     * Sync satu record ITAS ke Firestore
     */
    public static function syncItas(string $id, array $data, ?string $instansiKode = null): void
    {
        if (!self::isEnabled()) return;
        $kode = $instansiKode ?? self::getInstansiKode();

        try {
            $doc = [
                'id' => (int)$id,
                'kds' => (int)($data['kds'] ?? 0),
                'no_itas' => $data['no_itas'] ?? '',
                'exp_itas' => $data['exp_itas'] ?? '',
                'level_itas' => (int)($data['level_itas'] ?? 0),
                'aktif' => (int)($data['aktif'] ?? 1),
                'synced_at' => date('c'),
            ];

            self::putDocument("instansi/{$kode}/itas/{$id}", $doc);
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal sync ITAS ID=' . $id . ': ' . $e->getMessage());
        }
    }

    /**
     * Sync satu record user ke Firestore (TANPA password)
     */
    public static function syncUser(string $id, array $data, ?string $instansiKode = null): void
    {
        if (!self::isEnabled()) return;
        $kode = $instansiKode ?? self::getInstansiKode();

        try {
            $doc = [
                'id' => (int)$id,
                'username' => $data['username'] ?? '',
                'nama_lengkap' => $data['nama_lengkap'] ?? '',
                'role' => $data['role'] ?? '',
                'instansi_id' => $data['instansi_id'] ?? null,
                'synced_at' => date('c'),
            ];

            self::putDocument("instansi/{$kode}/users/{$id}", $doc);
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal sync user ID=' . $id . ': ' . $e->getMessage());
        }
    }

    /**
     * Hapus record user dari Firestore
     */
    public static function deleteUser(string $id, ?string $instansiKode = null): void
    {
        if (!self::isEnabled()) return;
        $kode = $instansiKode ?? self::getInstansiKode();

        try {
            self::deleteDocument("instansi/{$kode}/users/{$id}");
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal hapus user ID=' . $id . ': ' . $e->getMessage());
        }
    }

    // ================================================
    // PUBLIC API: Instansi Sync (Cloud ↔ Local)
    // ================================================

    /**
     * Push satu instansi ke Firestore
     */
    public static function syncInstansi(string $kode, array $data): void
    {
        if (!self::isEnabled()) return;

        try {
            $doc = [
                'kode' => (int)$kode,
                'nama_instansi' => $data['nama_instansi'] ?? '',
                'kode_instansi' => $data['kode_instansi'] ?? '',
                'def_kepengurusan' => $data['def_kepengurusan'] ?? '',
                'def_pondok' => $data['def_pondok'] ?? '',
                'synced_at' => date('c'),
            ];

            self::putDocument("instansi/{$kode}", $doc);
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal sync instansi kode=' . $kode . ': ' . $e->getMessage());
        }
    }

    /**
     * Hapus instansi dari Firestore
     */
    public static function deleteInstansi(string $kode): void
    {
        if (!self::isEnabled()) return;

        try {
            self::deleteDocument("instansi/{$kode}");
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Gagal hapus instansi kode=' . $kode . ': ' . $e->getMessage());
        }
    }

    /**
     * Pull semua instansi dari Firestore ke MySQL lokal (Cloud → Local)
     * Dijalankan saat login atau saat admin menekan tombol sync.
     */
    public static function pullInstansiFromFirestore(ConnectionInterface $db): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'message' => 'Firebase sync belum dikonfigurasi'];
        }

        $token = self::authenticate();
        if (!$token) {
            return ['success' => false, 'message' => 'Gagal autentikasi ke Firebase'];
        }

        try {
            $config = self::getConfig();
            $projectId = $config['project_id'];
            $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/instansi";

            $response = self::httpRequest('GET', $url, null, [
                'Authorization: Bearer ' . $token,
            ]);

            if (!$response || !isset($response['documents'])) {
                return ['success' => false, 'message' => 'Tidak ada data instansi di Firestore', 'synced' => 0];
            }

            $synced = 0;
            foreach ($response['documents'] as $doc) {
                $fields = $doc['fields'] ?? [];
                $kode = self::firestoreFieldToPhp($fields['kode'] ?? null);
                if (!$kode) continue;

                $instansiData = [
                    'nama_instansi' => self::firestoreFieldToPhp($fields['nama_instansi'] ?? null) ?? '',
                    'kode_instansi' => self::firestoreFieldToPhp($fields['kode_instansi'] ?? null) ?? '',
                    'def_kepengurusan' => self::firestoreFieldToPhp($fields['def_kepengurusan'] ?? null) ?? '',
                    'def_pondok' => self::firestoreFieldToPhp($fields['def_pondok'] ?? null) ?? '',
                    'kepengurusan' => self::firestoreFieldToPhp($fields['def_kepengurusan'] ?? null) ?? '',
                    'pondok' => self::firestoreFieldToPhp($fields['def_pondok'] ?? null) ?? '',
                ];

                // UPSERT: Cek apakah instansi sudah ada di lokal
                $exists = $db->createCommand(
                    "SELECT kode FROM master_instansi WHERE kode = :kode",
                    [':kode' => $kode]
                )->queryScalar();

                if ($exists) {
                    $db->createCommand()->update('master_instansi', $instansiData, ['kode' => $kode])->execute();
                } else {
                    $db->createCommand()->insert('master_instansi', array_merge(
                        $instansiData,
                        ['kode' => (int)$kode, 'email' => '', 'no_Instansi' => 0, 'path_folder' => '']
                    ))->execute();
                }
                $synced++;
            }

            return ['success' => true, 'message' => "Berhasil sinkronisasi {$synced} instansi dari Firebase", 'synced' => $synced];
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Pull instansi error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ================================================
    // PUBLIC API: Firebase Authentication Management
    // ================================================

    /**
     * Buat user baru di Firebase Authentication
     * @return string|null Firebase UID jika berhasil
     */
    public static function createFirebaseAuthUser(string $email, string $password, string $displayName = ''): ?string
    {
        if (!self::isEnabled()) return null;

        $config = self::getConfig();
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . $config['api_key'];

        try {
            $response = self::httpPost($url, [
                'email' => $email,
                'password' => $password,
                'displayName' => $displayName,
                'returnSecureToken' => true,
            ]);

            if ($response && isset($response['localId'])) {
                return $response['localId']; // Firebase UID
            }

            error_log('[FirebaseSync] Gagal buat Firebase Auth user: ' . json_encode($response));
            return null;
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Error buat Firebase Auth user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifikasi Firebase ID Token dan ambil data user
     * @return array|null User data jika token valid
     */
    public static function verifyIdToken(string $idToken): ?array
    {
        $config = self::getConfig();
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $config['api_key'];

        try {
            $response = self::httpPost($url, [
                'idToken' => $idToken,
            ]);

            if ($response && !empty($response['users'])) {
                return $response['users'][0];
            }

            return null;
        } catch (\Throwable $e) {
            error_log('[FirebaseSync] Token verification error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Konversi Firestore field value ke PHP native type
     */
    private static function firestoreFieldToPhp($field)
    {
        if ($field === null) return null;
        if (isset($field['stringValue'])) return $field['stringValue'];
        if (isset($field['integerValue'])) return (int)$field['integerValue'];
        if (isset($field['doubleValue'])) return (float)$field['doubleValue'];
        if (isset($field['booleanValue'])) return (bool)$field['booleanValue'];
        if (isset($field['nullValue'])) return null;
        return null;
    }

    // ================================================
    // PUBLIC API: Full Sync (Push All Data)
    // ================================================

    /**
     * Pratinjau penarikan data (Review & Select)
     */
    public static function previewFromFirebase(ConnectionInterface $db, ?string $overrideKode = null): array
    {
        $config = self::getConfig();
        $projectId = $config['project_id'];
        $sessionKode = $overrideKode ?? self::getInstansiKode();

        if (!$sessionKode || $sessionKode === '0') {
            return ['success' => false, 'message' => 'Instansi kode tidak valid untuk penarikan data'];
        }

        $token = self::authenticate();
        if (!$token) {
            return ['success' => false, 'message' => 'Gagal mendapatkan akses token Firebase'];
        }

        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/instansi/{$sessionKode}/santri";
        $documents = [];
        $nextPageToken = null;
        
        do {
            $pageUrl = $url . ($nextPageToken ? "?pageToken=" . urlencode($nextPageToken) : "");
            $response = self::httpRequest('GET', $pageUrl, null, [
                'Authorization: Bearer ' . $token,
            ]);
            
            if ($response === null) {
                return ['success' => false, 'message' => 'Gagal mengambil data dari Firebase'];
            }
            
            if (isset($response['documents'])) {
                $documents = array_merge($documents, $response['documents']);
            }
            $nextPageToken = $response['nextPageToken'] ?? null;
        } while ($nextPageToken);

        $previewData = [];
        
        foreach ($documents as $doc) {
            $fields = $doc['fields'] ?? [];
            
            $kds = self::firestoreFieldToPhp($fields['kds'] ?? null);
            if (!$kds) continue;
            
            $nama = self::firestoreFieldToPhp($fields['nama'] ?? null);
            $pondok = self::firestoreFieldToPhp($fields['pondok'] ?? null);
            $kelas = self::firestoreFieldToPhp($fields['kelas'] ?? null);
            $rayon = self::firestoreFieldToPhp($fields['rayon'] ?? null);
            $kewarganegaraan = self::firestoreFieldToPhp($fields['kewarganegaraan'] ?? null);
            $no_paspor = self::firestoreFieldToPhp($fields['no_paspor'] ?? null);
            $exp_paspor = self::firestoreFieldToPhp($fields['exp_paspor'] ?? null);
            $no_itas = self::firestoreFieldToPhp($fields['no_itas'] ?? null);
            $exp_itas = self::firestoreFieldToPhp($fields['exp_itas'] ?? null);
            
            $exist = $db->createCommand("SELECT kds FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryScalar();
            
            $previewData[] = [
                'kds' => $kds,
                'nama' => $nama,
                'pondok' => $pondok,
                'kelas' => $kelas,
                'rayon' => $rayon,
                'kewarganegaraan' => $kewarganegaraan,
                'no_paspor' => $no_paspor,
                'exp_paspor' => $exp_paspor,
                'no_itas' => $no_itas,
                'exp_itas' => $exp_itas,
                'status' => $exist ? 'update' : 'new'
            ];
        }

        return [
            'success' => true,
            'data' => $previewData,
            'message' => 'Preview data berhasil diambil.'
        ];
    }

    /**
     * Tarik data dari firebase
     */
    public static function pullFromFirebase(ConnectionInterface $db, ?array $selectedKds = null, ?string $overrideKode = null): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'message' => 'Firebase sync belum dikonfigurasi'];
        }

        $token = self::authenticate();
        if (!$token) {
            return ['success' => false, 'message' => 'Gagal autentikasi ke Firebase'];
        }

        $config = self::getConfig();
        $projectId = $config['project_id'];
        $sessionKode = $overrideKode ?? self::getInstansiKode();

        if (!$sessionKode || $sessionKode === '0') {
            return ['success' => false, 'message' => 'Instansi kode tidak valid untuk penarikan data'];
        }

        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/instansi/{$sessionKode}/santri";
        $documents = [];
        $nextPageToken = null;
        
        do {
            $pageUrl = $url . ($nextPageToken ? "?pageToken=" . urlencode($nextPageToken) : "");
            $response = self::httpRequest('GET', $pageUrl, null, [
                'Authorization: Bearer ' . $token,
            ]);
            
            if ($response === null) {
                return ['success' => false, 'message' => 'Gagal mengambil data dari Firebase'];
            }
            
            if (isset($response['documents'])) {
                $documents = array_merge($documents, $response['documents']);
            }
            $nextPageToken = $response['nextPageToken'] ?? null;
        } while ($nextPageToken);

        $inserted = 0;
        $updated = 0;
        
        $transaction = $db->beginTransaction();
        try {
            foreach ($documents as $doc) {
                $fields = $doc['fields'] ?? [];
                
                $kds = self::firestoreFieldToPhp($fields['kds'] ?? null);
                if (!$kds) continue;
                
                // Jika selectedKds diberikan, skip kds yang tidak dipilih
                if ($selectedKds !== null && !in_array($kds, $selectedKds)) {
                    continue;
                }
                
                $rawStambuk = self::firestoreFieldToPhp($fields['stambuk'] ?? null);
                $stambuk = (int)preg_replace('/[^0-9]/', '', (string)$rawStambuk);
                $nama = self::firestoreFieldToPhp($fields['nama'] ?? null);
                $kelas = self::firestoreFieldToPhp($fields['kelas'] ?? null);
                $rayon = self::firestoreFieldToPhp($fields['rayon'] ?? null);
                $negara = self::firestoreFieldToPhp($fields['negara'] ?? null);
                $kewarganegaraan = self::firestoreFieldToPhp($fields['kewarganegaraan'] ?? null);
                $pondok = self::firestoreFieldToPhp($fields['pondok'] ?? null);
                $kepengurusan = self::firestoreFieldToPhp($fields['kepengurusan'] ?? null);
                $status_santri = self::firestoreFieldToPhp($fields['status_santri'] ?? null);
                $aktif = self::firestoreFieldToPhp($fields['aktif'] ?? null);
                
                $no_paspor = self::firestoreFieldToPhp($fields['no_paspor'] ?? null);
                $exp_paspor = self::firestoreFieldToPhp($fields['exp_paspor'] ?? null);
                $status_lokasi = self::firestoreFieldToPhp($fields['status_lokasi'] ?? null);
                
                $no_itas = self::firestoreFieldToPhp($fields['no_itas'] ?? null);
                $exp_itas = self::firestoreFieldToPhp($fields['exp_itas'] ?? null);
                $level_itas = self::firestoreFieldToPhp($fields['level_itas'] ?? null);
                
                $exist = $db->createCommand("SELECT kds FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryScalar();
                
                $santriData = [
                    'stambuk' => $stambuk,
                    'nama' => $nama,
                    'kelas' => $kelas,
                    'rayon' => $rayon,
                    'negara' => $negara,
                    'kewarganegaraan' => $kewarganegaraan,
                    'pondok' => $pondok,
                    'kepengurusan' => $kepengurusan,
                    'status_santri' => $status_santri,
                    'aktif' => $aktif ? 1 : 0
                ];
                
                if ($exist) {
                    $db->createCommand()->update('master_santri', $santriData, ['kds' => $kds])->execute();
                    $updated++;
                } else {
                    $santriData['kds'] = $kds;
                    $db->createCommand()->insert('master_santri', $santriData)->execute();
                    $inserted++;
                }
                
                if ($no_paspor) {
                    $pasporExist = $db->createCommand("SELECT id FROM mtb_paspor WHERE kds = :kds AND no_paspor = :np", [':kds' => $kds, ':np' => $no_paspor])->queryScalar();
                    if (!$pasporExist) {
                        $db->createCommand()->insert('mtb_paspor', [
                            'kds' => $kds,
                            'no_paspor' => $no_paspor,
                            'exp_paspor' => $exp_paspor ?: null,
                            'status_lokasi' => $status_lokasi ?: 'di_kantor',
                            'aktif' => 1,
                            'dibuat_pada' => date('Y-m-d H:i:s')
                        ])->execute();
                    } else {
                        $db->createCommand()->update('mtb_paspor', [
                            'exp_paspor' => $exp_paspor ?: null,
                            'status_lokasi' => $status_lokasi ?: 'di_kantor'
                        ], ['id' => $pasporExist])->execute();
                    }
                }
                
                if ($no_itas) {
                    $itasExist = $db->createCommand("SELECT id FROM mtb_itas WHERE kds = :kds AND no_itas = :ni", [':kds' => $kds, ':ni' => $no_itas])->queryScalar();
                    if (!$itasExist) {
                        $db->createCommand()->insert('mtb_itas', [
                            'kds' => $kds,
                            'no_itas' => $no_itas,
                            'exp_itas' => $exp_itas ?: null,
                            'level_itas' => (int)$level_itas,
                            'aktif' => 1,
                            'dibuat_pada' => date('Y-m-d H:i:s')
                        ])->execute();
                    } else {
                        $db->createCommand()->update('mtb_itas', [
                            'exp_itas' => $exp_itas ?: null,
                            'level_itas' => (int)$level_itas
                        ], ['id' => $itasExist])->execute();
                    }
                }
            }
            
            $transaction->commit();
            
            return [
                'success' => true,
                'message' => "Tarik data Firebase sukses. $inserted data baru, $updated data diperbarui.",
                'inserted' => $inserted,
                'updated' => $updated
            ];
            
        } catch (\Throwable $e) {
            if ($transaction->isActive()) $transaction->rollBack();
            error_log('[FirebaseSync] Pull Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan data ke database lokal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync semua data dari 4 tabel ke Firestore
     */
    public static function syncAll(ConnectionInterface $db, ?string $limitInstansiId = null): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'message' => 'Firebase sync belum dikonfigurasi'];
        }

        $token = self::authenticate();
        if (!$token) {
            return ['success' => false, 'message' => 'Gagal autentikasi ke Firebase'];
        }

        $results = ['santri' => 0, 'paspor' => 0, 'itas' => 0, 'users' => 0, 'errors' => []];

        // Ambil semua instansi
        $sql = "SELECT kode, nama_instansi, def_kepengurusan, def_pondok FROM master_instansi";
        $params = [];
        if ($limitInstansiId) {
            $sql .= " WHERE kode = :kode";
            $params[':kode'] = $limitInstansiId;
        }
        $instansis = $db->createCommand($sql, $params)->queryAll();

        foreach ($instansis as $inst) {
            $kode = (string)$inst['kode'];

            // Update info instansi di Firestore
            try {
                self::putDocument("instansi/{$kode}", [
                    'kode' => (int)$inst['kode'],
                    'nama_instansi' => $inst['nama_instansi'],
                    'def_kepengurusan' => $inst['def_kepengurusan'] ?? '',
                    'def_pondok' => $inst['def_pondok'] ?? '',
                    'last_full_sync' => date('c'),
                ]);
            } catch (\Throwable $e) {
                $results['errors'][] = "Instansi {$kode}: " . $e->getMessage();
                continue;
            }

            // Sync Santri
            $kepengurusan = $inst['def_kepengurusan'] ?? '';
            $pondok = $inst['def_pondok'] ?? '';

            $santriWhere = "1=0"; // Default false
            $params = [];
            
            if (!empty($pondok) && !empty($kepengurusan)) {
                $santriWhere = "(s.pondok = :pon OR s.kepengurusan = :kep)";
                $params[':pon'] = $pondok;
                $params[':kep'] = $kepengurusan;
            } elseif (!empty($pondok)) {
                $santriWhere = "s.pondok = :pon";
                $params[':pon'] = $pondok;
            } elseif (!empty($kepengurusan)) {
                $santriWhere = "s.kepengurusan = :kep";
                $params[':kep'] = $kepengurusan;
            }

            $santris = $db->createCommand("
                SELECT s.kds, s.stambuk, s.nama, s.kelas, s.rayon, s.negara, s.kewarganegaraan, 
                       s.pondok, s.kepengurusan, s.status_santri, s.aktif,
                       p.no_paspor, p.exp_paspor, COALESCE(p.status_lokasi, 'di_kantor') as status_lokasi,
                       i.no_itas, i.exp_itas, i.level_itas
                FROM master_santri s 
                LEFT JOIN mtb_paspor p ON p.kds = s.kds AND p.aktif = 1
                LEFT JOIN mtb_itas i ON i.kds = s.kds AND i.aktif = 1
                WHERE {$santriWhere}
            ", $params)->queryAll();

            foreach ($santris as $s) {
                // Semua data sudah termasuk paspor & itas dari JOIN
                self::syncSantri((string)$s['kds'], $s, $kode);
                $results['santri']++;
            }

            // Sync ITAS
            $itass = $db->createCommand("
                SELECT i.id, i.kds, i.no_itas, i.exp_itas, i.level_itas, i.aktif
                FROM mtb_itas i
                JOIN master_santri s ON i.kds = s.kds
                WHERE i.aktif = 1 AND {$santriWhere}
            ", $params)->queryAll();

            foreach ($itass as $i) {
                self::syncItas((string)$i['id'], $i, $kode);
                $results['itas']++;
            }

            // Sync Users (untuk instansi ini)
            $users = $db->createCommand("
                SELECT id, username, nama_lengkap, role, instansi_id
                FROM users WHERE instansi_id = :inst_id
            ", [':inst_id' => $inst['kode']])->queryAll();

            foreach ($users as $u) {
                self::syncUser((string)$u['id'], $u, $kode);
                $results['users']++;
            }
        }

        // Sync super admin users (no instansi)
        $superAdmins = $db->createCommand("
            SELECT id, username, nama_lengkap, role, instansi_id
            FROM users WHERE role = 'super_admin'
        ")->queryAll();

        foreach ($superAdmins as $u) {
            self::syncUser((string)$u['id'], $u, '0');
            $results['users']++;
        }

        $results['success'] = true;
        $results['message'] = sprintf(
            'Sync selesai: %d santri, %d paspor, %d ITAS, %d user',
            $results['santri'], $results['paspor'], $results['itas'], $results['users']
        );
        $results['timestamp'] = date('c');

        return $results;
    }

    /**
     * Cek status koneksi Firebase
     */
    public static function checkConnection(): array
    {
        if (!self::isEnabled()) {
            return ['connected' => false, 'message' => 'Firebase sync belum dikonfigurasi atau dinonaktifkan'];
        }

        $token = self::authenticate();
        if (!$token) {
            return ['connected' => false, 'message' => 'Gagal autentikasi ke Firebase'];
        }

        return ['connected' => true, 'message' => 'Terhubung ke Firebase'];
    }

    // ================================================
    // PRIVATE: Firestore REST API Methods
    // ================================================

    /**
     * PUT (create/update) dokumen ke Firestore
     */
    private static function putDocument(string $path, array $data): ?array
    {
        $config = self::getConfig();
        $token = self::authenticate();
        if (!$token) return null;

        $projectId = $config['project_id'];
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$path}";

        // Convert PHP array ke Firestore document format
        $fields = self::phpToFirestoreFields($data);

        $response = self::httpRequest('PATCH', $url, ['fields' => $fields], [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);

        return $response;
    }

    /**
     * DELETE dokumen dari Firestore
     */
    private static function deleteDocument(string $path): bool
    {
        $config = self::getConfig();
        $token = self::authenticate();
        if (!$token) return false;

        $projectId = $config['project_id'];
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$path}";

        self::httpRequest('DELETE', $url, null, [
            'Authorization: Bearer ' . $token,
        ]);

        return true;
    }

    /**
     * Konversi PHP array ke Firestore fields format
     */
    private static function phpToFirestoreFields(array $data): array
    {
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
            } elseif (is_array($value)) {
                $fields[$key] = ['mapValue' => ['fields' => self::phpToFirestoreFields($value)]];
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        return $fields;
    }



    // ================================================
    // PRIVATE: HTTP Helper Methods
    // ================================================

    /**
     * Update user online presence
     */
    public static function updatePresence(string $instansiId, string $userId, string $namaLengkap, string $role): void
    {
        if (!self::isEnabled()) return;
        
        $doc = [
            'user_id' => $userId,
            'nama_lengkap' => $namaLengkap,
            'role' => $role,
            'last_seen' => time(),
            'timestamp' => date('c')
        ];
        
        self::putDocument("instansi/{$instansiId}/online/{$userId}", $doc);
    }

    public static function removePresence(string $instansiId, string $userId): void
    {
        if (!self::isEnabled()) return;
        self::deleteDocument("instansi/{$instansiId}/online/{$userId}");
    }

    /**
     * HTTP POST request
     */
    private static function httpPost(string $url, array $data): ?array
    {
        return self::httpRequest('POST', $url, $data, ['Content-Type: application/json']);
    }

    /**
     * Generic HTTP request via cURL
     */
    private static function httpRequest(string $method, string $url, ?array $data = null, array $headers = []): ?array
    {
        $config = self::getConfig();
        $timeout = $config['timeout'] ?? 15;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("[FirebaseSync] cURL error: {$error}");
            return null;
        }

        if ($httpCode >= 400) {
            error_log("[FirebaseSync] HTTP {$httpCode}: {$response}");
        }

        return $response ? json_decode($response, true) : null;
    }
}
