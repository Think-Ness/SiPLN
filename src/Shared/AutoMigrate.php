<?php

declare(strict_types=1);

namespace App\Shared;

use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * Auto-migration runner to ensure database schema compatibility across environments.
 */
final class AutoMigrate
{
    private static bool $hasRun = false;

    public static function checkAndMigrate(ConnectionInterface $db): void
    {
        if (self::$hasRun) {
            return;
        }
        self::$hasRun = true;

        $flagFile = dirname(__DIR__, 2) . '/config/.migration_schema_v3.lock';
        if (file_exists($flagFile)) {
            return;
        }

        try {
            // Helper to check column
            $hasColumn = function (string $table, string $column) use ($db): bool {
                try {
                    $row = $db->createCommand("SHOW COLUMNS FROM `{$table}` LIKE :col", [':col' => $column])->queryOne();
                    return !empty($row);
                } catch (Throwable $e) {
                    return false;
                }
            };

            // Helper to check table
            $hasTable = function (string $table) use ($db): bool {
                try {
                    $row = $db->createCommand("SHOW TABLES LIKE :tbl", [':tbl' => $table])->queryOne();
                    return !empty($row);
                } catch (Throwable $e) {
                    return false;
                }
            };

            // 1. users table
            if ($hasTable('users')) {
                if (!$hasColumn('users', 'is_active')) {
                    $db->createCommand("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`")->execute();
                }
                if (!$hasColumn('users', 'foto_profile')) {
                    $db->createCommand("ALTER TABLE `users` ADD COLUMN `foto_profile` VARCHAR(255) NULL AFTER `nik`")->execute();
                }
                if (!$hasColumn('users', 'ttl')) {
                    $db->createCommand("ALTER TABLE `users` ADD COLUMN `ttl` VARCHAR(255) NULL AFTER `nama_lengkap`")->execute();
                }
            }

            // 2. master_instansi table
            if ($hasTable('master_instansi')) {
                $cols = [
                    'path_folder' => "ALTER TABLE `master_instansi` ADD COLUMN `path_folder` VARCHAR(255) DEFAULT ''",
                    'kop_surat' => "ALTER TABLE `master_instansi` ADD COLUMN `kop_surat` VARCHAR(255) DEFAULT ''",
                    'def_kepengurusan' => "ALTER TABLE `master_instansi` ADD COLUMN `def_kepengurusan` VARCHAR(255) DEFAULT ''",
                    'def_pondok' => "ALTER TABLE `master_instansi` ADD COLUMN `def_pondok` VARCHAR(50) DEFAULT ''",
                    'def_jenis_kelamin' => "ALTER TABLE `master_instansi` ADD COLUMN `def_jenis_kelamin` VARCHAR(20) DEFAULT ''",
                ];
                foreach ($cols as $col => $sql) {
                    if (!$hasColumn('master_instansi', $col)) {
                        $db->createCommand($sql)->execute();
                    }
                }
            }

            // 3. jobdesk_master_process table
            if ($hasTable('jobdesk_master_process') && !$hasColumn('jobdesk_master_process', 'instansi_id')) {
                $db->createCommand("ALTER TABLE `jobdesk_master_process` ADD COLUMN `instansi_id` INT NULL")->execute();
            }

            // 4. anggaran_nota_items table
            if ($hasTable('anggaran_nota_items')) {
                if (!$hasColumn('anggaran_nota_items', 'satuan')) {
                    $db->createCommand("ALTER TABLE `anggaran_nota_items` ADD COLUMN `satuan` VARCHAR(50) DEFAULT NULL AFTER `qty`")->execute();
                }
                if (!$hasColumn('anggaran_nota_items', 'bagian')) {
                    $db->createCommand("ALTER TABLE `anggaran_nota_items` ADD COLUMN `bagian` VARCHAR(100) DEFAULT NULL AFTER `satuan`")->execute();
                }
            }

            // 5. surat_template_dinamis table
            if (!$hasTable('surat_template_dinamis')) {
                $db->createCommand("CREATE TABLE `surat_template_dinamis` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `nama_template` varchar(255) NOT NULL,
                    `file_path` varchar(255) NOT NULL,
                    `instansi_tujuan` varchar(100) DEFAULT NULL,
                    `peruntukan` enum('sekaligus','perseorangan') NOT NULL DEFAULT 'perseorangan',
                    `json_data_collection` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_data_collection`)),
                    `json_custom_inputs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_custom_inputs`)),
                    `aktif` tinyint(1) NOT NULL DEFAULT 1,
                    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                    `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;")->execute();

                $sqlFile = dirname(__DIR__, 2) . '/insert_templates.sql';
                if (file_exists($sqlFile)) {
                    $queries = file_get_contents($sqlFile);
                    if (!empty($queries)) {
                        $db->createCommand($queries)->execute();
                    }
                }
            } else {
                if (!$hasColumn('surat_template_dinamis', 'instansi_tujuan')) {
                    $db->createCommand("ALTER TABLE `surat_template_dinamis` ADD COLUMN `instansi_tujuan` VARCHAR(100) DEFAULT NULL AFTER `file_path`")->execute();
                }
            }

            @file_put_contents($flagFile, date('Y-m-d H:i:s'));
        } catch (Throwable $e) {
            // Silently log or continue to prevent breaking application flow
            @error_log("[AutoMigrate Error] " . $e->getMessage());
        }
    }
}
