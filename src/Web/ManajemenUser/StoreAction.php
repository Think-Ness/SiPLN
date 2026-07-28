<?php
declare(strict_types=1);

namespace App\Web\ManajemenUser;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

final class StoreAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $myRole = $_SESSION['role'] ?? '';
        if ($myRole !== 'super_admin' && $myRole !== 'admin_instansi') {
            return JsonResponse::create(['success' => false, 'message' => 'Akses Ditolak'], 403);
        }

        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }

        $id = !empty($body['id']) ? (int)$body['id'] : null;
        $username = trim($body['username'] ?? '');
        $nama_lengkap = trim($body['nama_lengkap'] ?? '');
        $ttl = trim($body['ttl'] ?? '');
        $password = $body['password'] ?? '';
        $role = $body['role'] ?? 'staff_instansi';
        $instansi_id = !empty($body['instansi_id']) ? (int)$body['instansi_id'] : null;

        // Validasi Role
        if ($myRole === 'admin_instansi') {
            if ($role === 'super_admin') {
                return JsonResponse::create(['success' => false, 'message' => 'Anda tidak berhak membuat akun Super Admin!'], 403);
            }
            // Paksa instansi sesuai milik Admin Instansi pembuat
            $instansi_id = $_SESSION['instansi_id'];
        }

        if (empty($username) || empty($nama_lengkap)) {
            return JsonResponse::create(['success' => false, 'message' => 'Username dan Nama Lengkap wajib diisi!'], 400);
        }

        // Cek duplikasi username
        $cekParams = [':user' => $username];
        $cekQuery = "SELECT id FROM users WHERE username = :user";
        if ($id) {
            $cekQuery .= " AND id != :id";
            $cekParams[':id'] = $id;
        }
        $isDuplicate = $db->createCommand($cekQuery, $cekParams)->queryScalar();
        if ($isDuplicate) {
            return JsonResponse::create(['success' => false, 'message' => 'Username ini sudah digunakan, silakan pilih yang lain.'], 400);
        }

        $data = [
            'username' => $username,
            'nama_lengkap' => $nama_lengkap,
            'ttl' => $ttl,
            'role' => $role,
            'instansi_id' => $role === 'super_admin' ? null : $instansi_id,
        ];

        // Manajemen Otoritas (Permissions)
        if ($role === 'super_admin') {
            // Super Admin selalu mendapat akses penuh (Full Access)
            $allPerms = $db->createCommand("SELECT permission_key FROM master_menus")->queryColumn();
            $allEditPerms = array_map(fn($p) => $p . '_edit', $allPerms);
            $data['permissions'] = json_encode(array_merge($allPerms, $allEditPerms));
        } else {
            // Staff & Admin Instansi mendapat akses sesuai kotak centang (Checkbox) yang dipilih
            $permissions = $body['permissions'] ?? [];
            if (!is_array($permissions)) $permissions = [];
            $data['permissions'] = json_encode($permissions);
        }

        // Jika password diisi (untuk buat baru atau ganti sandi lama)
        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        } else if (!$id) {
            return JsonResponse::create(['success' => false, 'message' => 'Password wajib diisi untuk pengguna baru!'], 400);
        }

        try {
            if ($id) {
                // Update pastikan Admin Instansi hanya mengubah usernya sendiri
                if ($myRole === 'admin_instansi') {
                    $target = $db->createCommand("SELECT instansi_id FROM users WHERE id = :id", [':id' => $id])->queryScalar();
                    if ($target != $_SESSION['instansi_id']) {
                        return JsonResponse::create(['success' => false, 'message' => 'Anda tidak berhak mengubah akun ini.'], 403);
                    }
                }
                
                // Fetch old user data
                $oldUser = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $id])->queryOne();
                
                $db->createCommand()->update('users', $data, ['id' => $id])->execute();
                
                // Fetch new user data
                $newUser = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $id])->queryOne();
                
                AuditLogger::log($db, 'UPDATE', 'MANAJEMEN_PENGGUNA', $id, $oldUser, $newUser, "Mengubah data pengguna: $username");
                
                // FIREBASE DUAL-WRITE
                \App\Shared\FirebaseSync::syncUser((string)$id, $newUser, (string)($newUser['instansi_id'] ?? '0'));

                return JsonResponse::create(['success' => true, 'message' => 'Data pengguna berhasil diperbarui!']);
            } else {
                // === Daftarkan ke Firebase Auth terlebih dahulu ===
                $firebaseEmail = $username . '@pln.local';
                $firebaseUid = \App\Shared\FirebaseSync::createFirebaseAuthUser(
                    $firebaseEmail, 
                    $password, 
                    $nama_lengkap
                );
                
                if ($firebaseUid) {
                    $data['firebase_uid'] = $firebaseUid;
                }

                $db->createCommand()->insert('users', $data)->execute();
                $newId = $db->getLastInsertID();
                $newUser = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $newId])->queryOne();
                AuditLogger::log($db, 'CREATE', 'MANAJEMEN_PENGGUNA', $newId, null, $newUser, "Menambahkan pengguna baru: $username");

                // FIREBASE DUAL-WRITE
                \App\Shared\FirebaseSync::syncUser((string)$newId, $newUser, (string)($newUser['instansi_id'] ?? '0'));

                $extraMsg = $firebaseUid 
                    ? ' (Terdaftar di Firebase Auth)' 
                    : ' (Peringatan: Gagal mendaftar ke Firebase Auth, login online belum tersedia)';
                
                return JsonResponse::create(['success' => true, 'message' => 'Pengguna baru berhasil ditambahkan!' . $extraMsg]);
            }
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}
