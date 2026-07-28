<?php
declare(strict_types=1);
namespace App\Web\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\FirebaseSync;

final class DoLoginAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $body = json_decode((string)$request->getBody(), true);
        $firebaseToken = $body['firebase_token'] ?? '';
        $username = trim($body['username'] ?? '');

        // === MODE 1: Firebase Auth (Primary — untuk produksi) ===
        if (!empty($firebaseToken)) {
            return $this->loginViaFirebase($firebaseToken, $username, $db);
        }

        // === MODE 2: Legacy MySQL Auth (Fallback — untuk transisi/migrasi) ===
        $password = $body['password'] ?? '';
        if (!empty($username) && !empty($password)) {
            return $this->loginViaMySQL($username, $password, $db);
        }

        return JsonResponse::create([
            'success' => false,
            'message' => 'Username dan Password wajib diisi.'
        ], 400);
    }

    /**
     * Login menggunakan Firebase ID Token
     * Flow: JS Firebase Auth → ID Token → PHP Verifikasi → Session
     */
    private function loginViaFirebase(string $idToken, string $username, ConnectionInterface $db): ResponseInterface
    {
        // Langkah 1: Verifikasi token ke Firebase
        $firebaseUser = FirebaseSync::verifyIdToken($idToken);

        if (!$firebaseUser) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Token Firebase tidak valid atau sudah kedaluwarsa. Silakan login ulang.'
            ], 401);
        }

        $firebaseEmail = $firebaseUser['email'] ?? '';
        $firebaseUid = $firebaseUser['localId'] ?? '';

        // Langkah 2: Cari user di MySQL lokal berdasarkan username atau firebase_uid
        $user = null;

        // Coba cari via firebase_uid dulu (paling akurat)
        if (!empty($firebaseUid)) {
            $user = $db->createCommand("
                SELECT u.*, i.nama_instansi, i.def_kepengurusan, i.def_pondok 
                FROM users u
                LEFT JOIN master_instansi i ON u.instansi_id = i.kode
                WHERE u.firebase_uid = :uid
            ", [':uid' => $firebaseUid])->queryOne();
        }

        // Fallback: cari via username (atau ekstrak dari email Firebase)
        $fallbackUsername = '';
        if (!empty($firebaseEmail)) {
            $parts = explode('@', $firebaseEmail);
            $fallbackUsername = $parts[0];
        }
        
        $searchUsername = (empty($username) || str_contains($username, '@')) ? $fallbackUsername : $username;
        if (empty($searchUsername)) {
            $searchUsername = $fallbackUsername;
        }

        if (!$user && !empty($searchUsername)) {
            $user = $db->createCommand("
                SELECT u.*, i.nama_instansi, i.def_kepengurusan, i.def_pondok 
                FROM users u
                LEFT JOIN master_instansi i ON u.instansi_id = i.kode
                WHERE u.username = :username
            ", [':username' => $searchUsername])->queryOne();

            // Jika masih tidak ketemu, coba fallback kedua menggunakan input asli jika berbeda
            if (!$user && $username !== $searchUsername && !empty($username)) {
                $user = $db->createCommand("
                    SELECT u.*, i.nama_instansi, i.def_kepengurusan, i.def_pondok 
                    FROM users u
                    LEFT JOIN master_instansi i ON u.instansi_id = i.kode
                    WHERE u.username = :username
                ", [':username' => $username])->queryOne();
            }

            // Simpan firebase_uid untuk pencarian berikutnya
            if ($user && !empty($firebaseUid)) {
                $db->createCommand()->update('users', 
                    ['firebase_uid' => $firebaseUid], 
                    ['id' => $user['id']]
                )->execute();
            }
        }

        if (!$user && $fallbackUsername === 'superadmin') {
            // Auto-create superadmin locally if authenticated via Firebase but missing locally
            $allPerms = [
                "action_edit_santri","menu_anggota_kamar","menu_audit_log","menu_auto_rekap","menu_inaktif_data",
                "menu_job_desk","menu_manajemen_instansi","menu_manajemen_pengguna","menu_master_data",
                "menu_master_print","menu_pemberkasan","menu_pengaturan_jobdesk","menu_pengaturan_sistem",
                "menu_profil_instansi","menu_surat_generator","action_edit_santri_edit","menu_anggota_kamar_edit",
                "menu_audit_log_edit","menu_auto_rekap_edit","menu_inaktif_data_edit","menu_job_desk_edit",
                "menu_manajemen_instansi_edit","menu_manajemen_pengguna_edit","menu_master_data_edit",
                "menu_master_print_edit","menu_pemberkasan_edit","menu_pengaturan_jobdesk_edit",
                "menu_pengaturan_sistem_edit","menu_profil_instansi_edit","menu_surat_generator_edit"
            ];
            $db->createCommand()->insert('users', [
                'username' => 'superadmin',
                'password_hash' => password_hash('superadmin', PASSWORD_DEFAULT),
                'nama_lengkap' => 'System Super Admin',
                'role' => 'super_admin',
                'permissions' => json_encode($allPerms),
                'instansi_id' => null,
                'firebase_uid' => $firebaseUid
            ])->execute();
            
            // Reload user
            $user = $db->createCommand("
                SELECT u.*, i.nama_instansi, i.def_kepengurusan, i.def_pondok 
                FROM users u
                LEFT JOIN master_instansi i ON u.instansi_id = i.kode
                WHERE u.username = 'superadmin'
            ")->queryOne();
        }

        if (!$user) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Akun Firebase terverifikasi, tetapi tidak ditemukan profil pengguna di sistem lokal. Hubungi administrator.'
            ], 404);
        }

        // Langkah 3: Sinkronisasi instansi dari Firestore (Cloud → Local)
        try {
            FirebaseSync::pullInstansiFromFirestore($db);
        } catch (\Throwable $e) {
            // Non-fatal: lanjut login meskipun sync gagal
            error_log('[DoLogin] Pull instansi gagal: ' . $e->getMessage());
        }

        // Langkah 4: Buat session
        $this->createSession($user);

        return JsonResponse::create([
            'success' => true,
            'message' => 'Akses Diberikan. Mengalihkan...'
        ]);
    }

    /**
     * Login legacy menggunakan MySQL password_hash
     * Dipertahankan sementara selama masa transisi ke Firebase Auth
     */
    private function loginViaMySQL(string $username, string $password, ConnectionInterface $db): ResponseInterface
    {
        $user = $db->createCommand("
            SELECT u.*, i.nama_instansi, i.def_kepengurusan, i.def_pondok 
            FROM users u
            LEFT JOIN master_instansi i ON u.instansi_id = i.kode
            WHERE u.username = :username
        ", [':username' => $username])->queryOne();

        if ($user && password_verify($password, $user['password_hash'])) {
            $this->createSession($user);

            return JsonResponse::create([
                'success' => true,
                'message' => 'Akses Diberikan. Mengalihkan...'
            ]);
        }

        return JsonResponse::create([
            'success' => false,
            'message' => 'Username atau Password salah.'
        ], 401);
    }

    /**
     * Tanamkan data user ke dalam PHP Session
     */
    private function createSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['instansi_id'] = $user['instansi_id'];
        $_SESSION['nama_instansi'] = $user['nama_instansi'] ?? 'Global System';
        $_SESSION['def_kepengurusan'] = $user['def_kepengurusan'] ?? '';
        $_SESSION['def_pondok'] = $user['def_pondok'] ?? '';
        
        // Tanamkan Matriks Otoritas (Permissions)
        $rawPerms = $user['permissions'] ?? '';
        $_SESSION['permissions'] = empty($rawPerms) ? [] : (json_decode($rawPerms, true) ?? []);
    }
}
