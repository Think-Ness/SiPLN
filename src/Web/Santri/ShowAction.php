<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class ShowAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds = (int) $currentRoute->getArgument('kds');

        $santri = $db->createCommand(
            "SELECT * FROM master_santri WHERE kds = :kds",
            [':kds' => $kds]
        )->queryOne();

        if (!$santri) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // Fix scientific notation from Excel imports
        if (isset($santri['no_sktt']) && strpos((string)$santri['no_sktt'], 'E') !== false) {
            $santri['no_sktt'] = number_format((float)$santri['no_sktt'], 0, '', '');
        }
        if (isset($santri['no_ic']) && strpos((string)$santri['no_ic'], 'E') !== false) {
            $santri['no_ic'] = number_format((float)$santri['no_ic'], 0, '', '');
        }

        $pasporList = $db->createCommand(
            "SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY id DESC",
            [':kds' => $kds]
        )->queryAll();

        $itasList = $db->createCommand(
            "SELECT * FROM mtb_itas WHERE kds = :kds ORDER BY id DESC",
            [':kds' => $kds]
        )->queryAll();

        $barang = $db->createCommand(
            "SELECT * FROM mtb_barang_terlarang WHERE kds = :kds ORDER BY id ASC",
            [':kds' => $kds]
        )->queryAll();

        $pasporBaru = [];
        $pasporLama = [];
        foreach ($pasporList as $p) {
            if ($p['aktif'] == 1) {
                $pasporBaru = $p;
            } elseif (empty($pasporLama)) {
                $pasporLama = $p;
            }
        }

        $itasBaru = [];
        foreach ($itasList as $i) {
            if ($i['aktif'] == 1) {
                $itasBaru = $i;
                break;
            }
        }

        $berkas = [];
        $namaCleanFolder = preg_replace('/[^A-Za-z0-9]/', '_', $santri['nama']);
        $santriFolder = rtrim($namaCleanFolder, '_') . '_' . $santri['kode'];
        
        $targetFolder = null;
        
        // Try session instansi first
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
        // Add all other instansis
        foreach ($instansis as $inst) {
            $p = rtrim($inst['path_folder'], '\\/');
            if (!in_array($p, $pathsToCheck)) {
                $pathsToCheck[] = $p;
            }
        }
        // Add default capel
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

        if ($targetFolder && is_dir($targetFolder)) {
            $files = scandir($targetFolder);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $berkas[] = [
                        'nama' => $file,
                        'url' => API_URL . '/api/santri/view-berkas/' . $kds . '/' . rawurlencode($file)
                    ];
                }
            }
        }

        return JsonResponse::create([
            'success' => true,
            'santri'  => $santri,
            'paspor'  => $pasporBaru,
            'paspor_lama' => $pasporLama,
            'r_paspor'=> $pasporList,
            'itas'    => $itasBaru,
            'r_itas'  => $itasList,
            'barang'  => $barang,
            'berkas'  => $berkas,
        ]);
    }
}
