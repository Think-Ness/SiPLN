<?php
declare(strict_types=1);

namespace App\Web\ManajemenInstansi;

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
        if (($_SESSION['role'] ?? '') !== 'super_admin') {
            return JsonResponse::create(['success' => false, 'message' => 'Akses Ditolak'], 403);
        }

        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }

        $kode = !empty($body['kode']) ? (int)$body['kode'] : null;
        $nama_instansi = trim($body['nama_instansi'] ?? '');
        $kode_instansi = trim($body['kode_instansi'] ?? '');
        $def_kepengurusan = trim($body['def_kepengurusan'] ?? '');
        $def_pondok = trim($body['def_pondok'] ?? '');
        
        if (empty($nama_instansi) || empty($def_kepengurusan) || empty($def_pondok)) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama Instansi, Kepengurusan, dan Pondok wajib diisi!'], 400);
        }

        $data = [
            'nama_instansi' => $nama_instansi,
            'kode_instansi' => $kode_instansi,
            'def_kepengurusan' => $def_kepengurusan,
            'def_pondok' => $def_pondok,
            'kepengurusan' => $def_kepengurusan, // Backward compatibility
            'pondok' => $def_pondok,             // Backward compatibility
            'email' => '',
            'no_Instansi' => 0,
            'path_folder' => ''
        ];

        try {
            if ($kode) {
                // Update
                $oldInstansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $kode])->queryOne();
                $db->createCommand()->update('master_instansi', $data, ['kode' => $kode])->execute();
                $newInstansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $kode])->queryOne();
                AuditLogger::log($db, 'UPDATE', 'MANAJEMEN_INSTANSI', $kode, $oldInstansi, $newInstansi, "Mengubah Profil Instansi: $nama_instansi");
                
                // FIREBASE DUAL-WRITE: Push instansi ke Firestore
                \App\Shared\FirebaseSync::syncInstansi((string)$kode, $newInstansi);
                
                return JsonResponse::create(['success' => true, 'message' => 'Profil Instansi berhasil diperbarui!']);
            } else {
                // Create
                $db->createCommand()->insert('master_instansi', $data)->execute();
                $newId = $db->getLastInsertID();
                $newInstansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $newId])->queryOne();
                AuditLogger::log($db, 'CREATE', 'MANAJEMEN_INSTANSI', $newId, null, $newInstansi, "Menambahkan Profil Instansi baru: $nama_instansi");
                
                // FIREBASE DUAL-WRITE: Push instansi baru ke Firestore
                \App\Shared\FirebaseSync::syncInstansi((string)$newId, $newInstansi);
                
                return JsonResponse::create(['success' => true, 'message' => 'Profil Instansi baru berhasil ditambahkan!']);
            }
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
