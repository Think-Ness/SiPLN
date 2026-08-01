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
            $messages = [];

            // Check if table exists
            $tableExists = $db->createCommand("SHOW TABLES LIKE 'surat_template_dinamis'")->queryScalar();

            if (!$tableExists) {
                // Create table
                $db->createCommand("
                    CREATE TABLE `surat_template_dinamis` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nama_template` varchar(255) NOT NULL,
                      `file_path` varchar(255) NOT NULL,
                      `instansi_tujuan` varchar(100) NOT NULL,
                      `peruntukan` enum('sekaligus','perseorangan') NOT NULL DEFAULT 'perseorangan',
                      `json_data_collection` text DEFAULT NULL,
                      `json_custom_inputs` text DEFAULT NULL,
                      `aktif` tinyint(1) NOT NULL DEFAULT 1,
                      `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                      `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
                ")->execute();
                $messages[] = 'Tabel surat_template_dinamis berhasil dibuat dengan struktur terbaru.';
            } else {
                // Check current columns
                $cols = $db->createCommand('DESCRIBE surat_template_dinamis')->queryColumn();
                
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
