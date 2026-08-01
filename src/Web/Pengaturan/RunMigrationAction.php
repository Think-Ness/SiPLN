<?php
declare(strict_types=1);

namespace App\Web\Pengaturan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class RunMigrationAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'super_admin') {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Hanya Super Admin yang berhak menjalankan sinkronisasi database.'
            ]);
        }

        try {
            // Check current columns
            $cols = $db->createCommand('DESCRIBE surat_template_dinamis')->queryColumn();
            
            $messages = [];
            
            // Drop instansi_id if exists, add instansi_tujuan if not exists
            if (in_array('instansi_id', $cols) && !in_array('instansi_tujuan', $cols)) {
                $db->createCommand('ALTER TABLE surat_template_dinamis DROP COLUMN instansi_id, ADD COLUMN instansi_tujuan VARCHAR(100) NOT NULL AFTER file_path')->execute();
                $messages[] = 'Kolom instansi_id dihapus, instansi_tujuan ditambahkan.';
            } elseif (!in_array('instansi_tujuan', $cols)) {
                $db->createCommand('ALTER TABLE surat_template_dinamis ADD COLUMN instansi_tujuan VARCHAR(100) NOT NULL AFTER file_path')->execute();
                $messages[] = 'Kolom instansi_tujuan berhasil ditambahkan.';
            } else {
                $messages[] = 'Struktur tabel sudah yang terbaru (tidak ada perubahan).';
            }

            return JsonResponse::create([
                'success' => true,
                'message' => implode(' ', $messages)
            ]);
        } catch (\Exception $e) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Gagal mensinkronisasi database: ' . $e->getMessage()
            ]);
        }
    }
}
