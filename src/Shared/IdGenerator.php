<?php
declare(strict_types=1);

namespace App\Shared;

use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * IdGenerator - Pembuat ID Unik Berbasis Branch Prefix
 * 
 * Setiap instansi/cabang memiliki "wilayah" KDS tersendiri:
 *   Instansi 1 (Pusat): 1.000.001 - 1.999.999
 *   Instansi 2 (G1):    2.000.001 - 2.999.999
 *   Instansi 3 (G2):    3.000.001 - 3.999.999
 *   dst.
 * 
 * Ini menjamin KDS tidak pernah bentrok antar cabang saat disinkronkan ke Firebase.
 */
final class IdGenerator
{
    /** Faktor pengali untuk menentukan wilayah prefix */
    private const RANGE_SIZE = 1_000_000;

    /**
     * Generate KDS baru yang unik berdasarkan instansi_id.
     *
     * @param ConnectionInterface $db Koneksi database lokal
     * @param int $instansiId ID instansi (dari session atau parameter)
     * @return int KDS baru yang terjamin unik dalam wilayah instansi
     * @throws \RuntimeException Jika instansiId tidak valid atau wilayah sudah penuh
     */
    public static function generateKds(ConnectionInterface $db, int $instansiId): int
    {
        if ($instansiId < 1) {
            throw new \RuntimeException('ID Instansi tidak valid (harus >= 1). Pastikan user sudah login dan terhubung ke instansi.');
        }

        $rangeMin = $instansiId * self::RANGE_SIZE + 1;   // misal: 1.000.001
        $rangeMax = ($instansiId + 1) * self::RANGE_SIZE;  // misal: 1.999.999 (eksklusif)

        // Cari KDS terbesar yang sudah ada di wilayah ini
        $maxKds = $db->createCommand(
            "SELECT MAX(kds) FROM master_santri WHERE kds >= :min AND kds < :max",
            [':min' => $rangeMin, ':max' => $rangeMax]
        )->queryScalar();

        if ($maxKds === false || $maxKds === null) {
            // Belum ada santri sama sekali di wilayah ini
            return $rangeMin;
        }

        $nextKds = (int)$maxKds + 1;

        if ($nextKds >= $rangeMax) {
            throw new \RuntimeException(
                "Wilayah KDS untuk Instansi #{$instansiId} sudah penuh " .
                "(range {$rangeMin} - " . ($rangeMax - 1) . "). " .
                "Hubungi administrator sistem."
            );
        }

        return $nextKds;
    }

    /**
     * Mendapatkan instansi ID dari session saat ini.
     * Fallback ke 1 (pusat) jika tidak ditemukan.
     */
    public static function getSessionInstansiId(): int
    {
        return (int)($_SESSION['instansi_id'] ?? 1);
    }

    /**
     * Menghitung range KDS untuk instansi tertentu.
     * Berguna untuk debugging dan reporting.
     *
     * @return array{min: int, max: int}
     */
    public static function getKdsRange(int $instansiId): array
    {
        return [
            'min' => $instansiId * self::RANGE_SIZE + 1,
            'max' => ($instansiId + 1) * self::RANGE_SIZE - 1,
        ];
    }
}
