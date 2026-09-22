<?php
declare(strict_types=1);

namespace App\Web\Santri;

use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\UploadPath;

trait BerkasTrait
{
    private function getTargetFolder(ConnectionInterface $db, array $santri, bool $createIfMissing = false): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $rawNama = (string)($santri['nama'] ?? '');
        $rawKode = (string)($santri['kode'] ?? '');

        $namaExact = trim(preg_replace('/[^A-Za-z0-9]/', '_', $rawNama), '_');
        $namaSingleUnderscore = trim(preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9]/', '_', $rawNama)), '_');
        $kodeClean = trim($rawKode);

        $folderVariations = [];
        if ($kodeClean !== '') {
            $folderVariations[] = $namaExact . '_' . $kodeClean;
            $folderVariations[] = $namaSingleUnderscore . '_' . $kodeClean;
        }
        $folderVariations[] = $namaExact . '_';
        $folderVariations[] = $namaSingleUnderscore . '_';
        $folderVariations[] = $namaExact;
        $folderVariations[] = $namaSingleUnderscore;
        $folderVariations = array_values(array_unique($folderVariations));

        // Tentukan instansi santri berdasarkan kepengurusan
        $instansiId = null;
        if (!empty($santri['kepengurusan'])) {
            try {
                $row = $db->createCommand(
                    "SELECT kode FROM master_instansi WHERE def_kepengurusan = :k OR kepengurusan = :k OR nama_instansi = :k LIMIT 1",
                    [':k' => $santri['kepengurusan']]
                )->queryOne();
                if ($row) {
                    $instansiId = (int)$row['kode'];
                }
            } catch (\Throwable $e) {}
        }
        if (!$instansiId) {
            $instansiId = (int)($_SESSION['instansi_id'] ?? 0);
        }

        $baseBases = [];
        if ($instansiId > 0) {
            $baseBases[] = UploadPath::getBase($db, $instansiId);
        }

        // Cek semua instansi
        try {
            $allInstansi = $db->createCommand("SELECT kode FROM master_instansi")->queryAll();
            foreach ($allInstansi as $inst) {
                $b = UploadPath::getBase($db, (int)$inst['kode']);
                if (!in_array($b, $baseBases)) {
                    $baseBases[] = $b;
                }
            }
        } catch (\Throwable $e) {}

        // Tambah default folder uploads jika belum ada
        $defaultUploadsRoot = str_replace('\\', '/', dirname(__DIR__, 4) . '/public/uploads');
        if (is_dir($defaultUploadsRoot)) {
            $subdirs = scandir($defaultUploadsRoot);
            foreach ($subdirs as $sd) {
                if ($sd === '.' || $sd === '..') continue;
                $sdPath = $defaultUploadsRoot . '/' . $sd;
                if (is_dir($sdPath) && !in_array($sdPath, $baseBases)) {
                    $baseBases[] = $sdPath;
                }
            }
        }

        // 1. Cari apakah folder santri sudah ada di salah satu base dan variasi
        foreach ($baseBases as $base) {
            foreach ($folderVariations as $fv) {
                $candidate = rtrim($base, '/\\') . '/berkas/' . $fv . '/';
                if (is_dir($candidate)) {
                    return $candidate;
                }
            }
        }

        // 2. Cek path lama capel
        foreach ($folderVariations as $fv) {
            $capelOld = dirname(__DIR__, 4) . '/public/uploads/capel/berkas/' . $fv . '/';
            if (is_dir($capelOld)) {
                return $capelOld;
            }
        }

        // 3. Jika belum ada dan diminta dibuatkan
        if ($createIfMissing) {
            $targetBase = !empty($baseBases) ? $baseBases[0] : (dirname(__DIR__, 4) . '/public/uploads/Ponorogo');
            $preferredFolder = $folderVariations[0] ?? ($namaSingleUnderscore . ($kodeClean !== '' ? '_' . $kodeClean : ''));
            $newTarget = rtrim($targetBase, '/\\') . '/berkas/' . $preferredFolder . '/';
            if (!is_dir($newTarget)) {
                @mkdir($newTarget, 0777, true);
            }
            return $newTarget;
        }

        return null;
    }
}
