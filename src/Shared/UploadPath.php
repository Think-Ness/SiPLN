<?php

declare(strict_types=1);

namespace App\Shared;

use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * Centralized upload path resolver per instansi/kepengurusan.
 * 
 * Standar penyimpanan berkas:
 * {ROOT}/public/uploads/{Nama_Instansi}/{subfolder}
 * Contoh:
 *   /public/uploads/Ponorogo/foto santri/
 *   /public/uploads/Ponorogo/paspor/
 *   /public/uploads/Ponorogo/itas/
 *   /public/uploads/Ponorogo/berkas penting/
 *   /public/uploads/Ponorogo/Surat_Menyurat/
 *   /public/uploads/Ponorogo/nota/
 *   /public/uploads/Ponorogo/profil_staf/
 */
final class UploadPath
{
    /**
     * Ambil base upload directory untuk instansi.
     * Otomatis mengarah ke public/uploads/{Nama_Instansi} jika belum diset atau path lama tidak ada.
     * 
     * @return string Absolute path tanpa trailing slash
     */
    public static function getBase(ConnectionInterface $db, int|string|null $instansiId = null): string
    {
        AutoMigrate::checkAndMigrate($db);

        $id = ($instansiId !== null && $instansiId !== '') ? (int)$instansiId : 0;
        if ($id <= 0) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $id = (int)($_SESSION['instansi_id'] ?? 0);
        }

        $instansi = null;
        if ($id > 0) {
            try {
                $instansi = $db->createCommand(
                    "SELECT kode, nama_instansi, kepengurusan, def_kepengurusan, path_folder FROM master_instansi WHERE kode = :kode",
                    [':kode' => $id]
                )->queryOne();
            } catch (\Throwable $e) {}
        }

        // Fallback jika tidak ditemukan instansi dari ID
        if (!$instansi) {
            try {
                $instansi = $db->createCommand(
                    "SELECT kode, nama_instansi, kepengurusan, def_kepengurusan, path_folder FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' OR kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1"
                )->queryOne();
            } catch (\Throwable $e) {}
        }

        // Tentukan nama folder instansi (contoh: Ponorogo, Mantingan, Kediri)
        $folderName = 'Ponorogo';
        if ($instansi) {
            $raw = !empty($instansi['def_kepengurusan']) ? $instansi['def_kepengurusan'] : (!empty($instansi['kepengurusan']) ? $instansi['kepengurusan'] : ($instansi['nama_instansi'] ?? 'Ponorogo'));
            $clean = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string)$raw));
            if ($clean !== '') {
                $folderName = $clean;
            }
        }

        $defaultUploadsRoot = str_replace('\\', '/', dirname(__DIR__, 2) . '/public/uploads');

        // Cek apakah ada konfigurasi custom path_folder dari database
        $configuredPath = !empty($instansi['path_folder']) ? trim(str_replace('\\', '/', (string)$instansi['path_folder'])) : '';
        if ($configuredPath !== '') {
            $configuredPath = rtrim($configuredPath, '/');
            // Jika path folder diisi tapi tidak berakhiran nama instansi, tambahkan nama instansi
            $baseName = basename($configuredPath);
            if (strcasecmp($baseName, $folderName) !== 0) {
                $target = $configuredPath . '/' . $folderName;
            } else {
                $target = $configuredPath;
            }

            // Pastikan direktori ada atau bisa dibuat
            if (is_dir($target) || @mkdir($target, 0777, true)) {
                return $target;
            }
        }

        // Default standar: {webapp}/public/uploads/{folderName}
        $finalBase = $defaultUploadsRoot . '/' . $folderName;
        if (!is_dir($finalBase)) {
            @mkdir($finalBase, 0777, true);
        }

        return $finalBase;
    }

    /**
     * Ambil subfolder di dalam base instansi dan pastikan foldernya sudah dibuat.
     */
    public static function getFolder(ConnectionInterface $db, string $subfolder, int|string|null $instansiId = null): string
    {
        $base = self::getBase($db, $instansiId);
        $cleanSub = trim(str_replace(['../', '..\\'], '', $subfolder), '/\\');
        $target = $base . '/' . $cleanSub;
        if (!is_dir($target)) {
            @mkdir($target, 0777, true);
        }
        return $target;
    }

    /**
     * Selalu mengembalikan base path yang valid.
     */
    public static function requireBase(ConnectionInterface $db, int|string|null $instansiId = null): string
    {
        return self::getBase($db, $instansiId);
    }

    /**
     * Pesan peringatan opsional jika diperlukan.
     */
    public static function notConfiguredMessage(): string
    {
        return 'Path Folder Instansi belum diatur. Silakan periksa di menu Profil Instansi.';
    }

    /**
     * Cek apakah sebuah path merupakan absolute path (Windows atau Linux).
     */
    public static function isAbsolutePath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        
        if (str_starts_with($normalized, '/uploads/') || str_starts_with($normalized, 'uploads/')) {
            return false;
        }

        return (bool)preg_match('/^[a-zA-Z]:\//', $normalized) || str_starts_with($normalized, '/');
    }
}

