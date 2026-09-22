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
    use BerkasTrait;

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
            "SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY (exp_paspor IS NULL OR exp_paspor = '' OR exp_paspor = '0000-00-00') ASC, exp_paspor DESC, id DESC",
            [':kds' => $kds]
        )->queryAll();

        $itasList = $db->createCommand(
            "SELECT * FROM mtb_itas WHERE kds = :kds ORDER BY (exp_itas IS NULL OR exp_itas = '' OR exp_itas = '0000-00-00') ASC, exp_itas DESC, id DESC",
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
        $targetFolder = $this->getTargetFolder($db, $santri, false);

        if ($targetFolder && is_dir($targetFolder)) {
            $files = scandir($targetFolder);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($targetFolder . $file)) {
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
