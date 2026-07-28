<?php
declare(strict_types=1);

namespace App\Web\Santri;

use Yiisoft\Db\Connection\ConnectionInterface;

trait BerkasTrait
{
    private function getTargetFolder(ConnectionInterface $db, array $santri, bool $createIfMissing = false): ?string
    {
        $namaCleanFolder = preg_replace('/[^A-Za-z0-9]/', '_', $santri['nama']);
        $santriFolder = rtrim($namaCleanFolder, '_') . '_' . $santri['kode'];
        
        $targetFolder = null;
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        $instansis = $db->createCommand("SELECT path_folder FROM master_instansi WHERE path_folder IS NOT NULL AND path_folder != ''")->queryAll();
        $pathsToCheck = [];
        if ($instansiId) {
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :id", [':id' => $instansiId])->queryOne();
            if ($instansi && !empty($instansi['path_folder'])) {
                $pathsToCheck[] = rtrim($instansi['path_folder'], '\\/');
            }
        }
        
        foreach ($instansis as $inst) {
            $p = rtrim($inst['path_folder'], '\\/');
            if (!in_array($p, $pathsToCheck)) {
                $pathsToCheck[] = $p;
            }
        }
        $pathsToCheck[] = 'capel';

        foreach ($pathsToCheck as $p) {
            $kategoriFolder = $p . '/berkas/' . $santriFolder;
            $isAbsolute = str_starts_with(str_replace('\\', '/', $kategoriFolder), 'D:/') || str_starts_with(str_replace('\\', '/', $kategoriFolder), 'C:/') || str_starts_with($kategoriFolder, '/');
            if ($isAbsolute) {
                $testPath = rtrim($kategoriFolder, '\\/') . '/';
            } else {
                $testPath = dirname(__DIR__, 4) . '/public/uploads/' . $kategoriFolder . '/';
            }
            if (is_dir($testPath)) {
                $targetFolder = $testPath;
                break;
            }
        }

        if (!$targetFolder && $createIfMissing) {
            // Create in the first path to check (which is the user's instansi, or capel)
            $p = $pathsToCheck[0];
            $kategoriFolder = $p . '/berkas/' . $santriFolder;
            $isAbsolute = str_starts_with(str_replace('\\', '/', $kategoriFolder), 'D:/') || str_starts_with(str_replace('\\', '/', $kategoriFolder), 'C:/') || str_starts_with($kategoriFolder, '/');
            if ($isAbsolute) {
                $targetFolder = rtrim($kategoriFolder, '\\/') . '/';
            } else {
                $targetFolder = dirname(__DIR__, 4) . '/public/uploads/' . $kategoriFolder . '/';
            }
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }
        }

        return $targetFolder;
    }
}
