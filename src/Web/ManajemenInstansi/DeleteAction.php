<?php
declare(strict_types=1);

namespace App\Web\ManajemenInstansi;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

final class DeleteAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        if (($_SESSION['role'] ?? '') !== 'super_admin') {
            return JsonResponse::create(['success' => false, 'message' => 'Akses Ditolak'], 403);
        }

        $kode = (int) $request->getAttribute('kode', 0);
        
        if ($kode <= 0) {
            return JsonResponse::create(['success' => false, 'message' => 'ID Instansi tidak valid!'], 400);
        }

        // Cek apakah Instansi ini memiliki user aktif
        $usersCount = $db->createCommand("SELECT COUNT(*) FROM users WHERE instansi_id = :kode", [':kode' => $kode])->queryScalar();
        if ($usersCount > 0) {
            return JsonResponse::create([
                'success' => false, 
                'message' => "Tidak dapat menghapus Instansi ini karena masih ada $usersCount User/Staff yang terdaftar di bawahnya. Hapus user terlebih dahulu!"
            ], 400);
        }

        try {
            $instansi = $db->createCommand("SELECT nama_instansi FROM master_instansi WHERE kode = :kode", [':kode' => $kode])->queryOne();
            
            $db->createCommand()->delete('master_instansi', ['kode' => $kode])->execute();
            
            if ($instansi) {
                AuditLogger::log($db, 'DELETE', 'MANAJEMEN_INSTANSI', $kode, $instansi, "Menghapus Instansi: {$instansi['nama_instansi']}");
            }
            
            // FIREBASE DUAL-WRITE: Hapus instansi dari Firestore
            \App\Shared\FirebaseSync::deleteInstansi((string)$kode);
            
            return JsonResponse::create(['success' => true, 'message' => 'Profil Instansi berhasil dihapus!']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menghapus instansi: ' . $e->getMessage()], 500);
        }
    }
}
