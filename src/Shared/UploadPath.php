<?php

declare(strict_types=1);

namespace App\Shared;

use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * Centralized upload path resolver per instansi/kepengurusan.
 * 
 * Semua file upload WAJIB menggunakan class ini sebagai acuan base directory.
 * Jika path_folder instansi belum diatur, akan return null agar caller bisa
 * menolak request dan mengarahkan user untuk setting path_folder dulu.
 */
final class UploadPath
{
    /**
     * Ambil base upload directory untuk instansi saat ini.
     * 
     * Prioritas:
     * 1. path_folder dari master_instansi (absolute path yang sudah dikonfigurasi admin)
     * 2. null — artinya belum diatur, caller harus menolak dan arahkan user ke Profil Instansi
     * 
     * @return string|null  Absolute path tanpa trailing slash, atau null jika belum diatur
     */
    public static function getBase(ConnectionInterface $db, ?int $instansiId = null): ?string
    {
        AutoMigrate::checkAndMigrate($db);

        if (!$instansiId) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $instansiId = (int)($_SESSION['instansi_id'] ?? 0);
        }

        if (!$instansiId) {
            return null;
        }

        try {
            $instansi = $db->createCommand(
                "SELECT path_folder FROM master_instansi WHERE kode = :kode",
                [':kode' => $instansiId]
            )->queryOne();

            if ($instansi && !empty(trim((string)($instansi['path_folder'] ?? '')))) {
                return rtrim(str_replace('\\', '/', trim($instansi['path_folder'])), '/');
            }
        } catch (\Throwable $e) {
            // Fallback gracefully if database column is being migrated
            return null;
        }

        return null;
    }

    /**
     * Ambil base upload directory, WAJIB sudah diatur.
     * Jika belum diatur, throw exception agar bisa ditangkap caller untuk response error.
     * 
     * @throws \RuntimeException jika path_folder belum dikonfigurasi
     */
    public static function requireBase(ConnectionInterface $db, ?int $instansiId = null): string
    {
        $base = self::getBase($db, $instansiId);
        if ($base === null) {
            throw new \RuntimeException(
                'Path Folder Instansi belum diatur. Silakan atur terlebih dahulu di menu Profil Instansi → Path Folder sebelum melakukan upload.'
            );
        }
        return $base;
    }

    /**
     * Pesan error standar ketika path_folder belum diatur.
     */
    public static function notConfiguredMessage(): string
    {
        return 'Path Folder Instansi belum diatur. Silakan atur terlebih dahulu di menu Profil Instansi → Path Folder sebelum melakukan upload/unduh file.';
    }

    /**
     * Cek apakah sebuah path merupakan absolute path (Windows atau Linux).
     * Path URL legacy seperti '/uploads/...' dianggap relative.
     */
    public static function isAbsolutePath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        
        // Jika path adalah legacy URL path, maka bukan absolute path system
        if (str_starts_with($normalized, '/uploads/') || str_starts_with($normalized, 'uploads/')) {
            return false;
        }

        return (bool)preg_match('/^[a-zA-Z]:\//', $normalized) || str_starts_with($normalized, '/');
    }
}
