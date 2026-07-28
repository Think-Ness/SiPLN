<?php
declare(strict_types=1);

namespace App\Web\MasterPrint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class MenuPrintAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $params = $request->getQueryParams();
        
        $pondok = $params['pondok'] ?? '';
        $negara = $params['negara'] ?? '';
        $kepengurusan = $params['kepengurusan'] ?? '';
        $kelas = $params['kelas'] ?? '';
        $itas = $params['itas'] ?? '';
        $paspor = $params['paspor'] ?? '';

        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        $saasWhere = "s.aktif = 1";
        $saasParams = [];
        $role = $_SESSION['role'] ?? '';
        $myKep = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        if ($role !== 'super_admin' && (!empty($myKep) || !empty($myPondok))) {
            $saasWhere .= " AND (s.kepengurusan = :my_kep OR s.pondok = :my_pondok)";
            $saasParams[':my_kep'] = $myKep;
            $saasParams[':my_pondok'] = $myPondok;
        }

        $where = [$saasWhere];
        $queryParams = $saasParams;

        if ($pondok !== '') {
            $where[] = "s.pondok = :pondok";
            $queryParams[':pondok'] = $pondok;
        }
        if ($negara !== '') {
            $where[] = "s.negara = :negara";
            $queryParams[':negara'] = $negara;
        }
        if ($kepengurusan !== '') {
            $where[] = "s.kepengurusan = :kep";
            $queryParams[':kep'] = $kepengurusan;
        }
        if ($kelas !== '') {
            $where[] = "s.kelas = :kelas";
            $queryParams[':kelas'] = $kelas;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT s.kds, s.stambuk, s.nama, s.kelas, s.rayon, s.negara, s.pondok, s.kepengurusan,
                   p.no_paspor, p.exp_paspor, i.no_itas, i.exp_itas, i.level_itas as lvl
            FROM master_santri s
            LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            LEFT JOIN (SELECT kds, no_itas, exp_itas, level_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
            WHERE $whereClause
            ORDER BY s.nama ASC
        ";

        $santris = $db->createCommand($sql, $queryParams)->queryAll();

        // Apply ITAS/Paspor filtering in PHP (like previous logic)
        if ($itas !== '' || $paspor !== '') {
            $filteredSantris = [];
            foreach ($santris as $s) {
                $match = true;
                if ($itas !== '') {
                    $ym = substr((string)$s['exp_itas'], 0, 7);
                    if ($ym !== $itas) $match = false;
                }
                if ($paspor !== '') {
                    $y = substr((string)$s['exp_paspor'], 0, 4);
                    if ($y !== $paspor) $match = false;
                }
                if ($match) {
                    $filteredSantris[] = $s;
                }
            }
            $santris = $filteredSantris;
        }

        // Apply Isolation to Dropdowns too
        $pondokList       = $db->createCommand("SELECT DISTINCT pondok FROM master_santri s WHERE $saasWhere AND pondok IS NOT NULL AND pondok != '' ORDER BY pondok", $saasParams)->queryColumn();
        $kelasList        = $db->createCommand("SELECT DISTINCT kelas FROM master_santri s WHERE $saasWhere AND kelas IS NOT NULL AND kelas != '' ORDER BY kelas", $saasParams)->queryColumn();
        $negaraList       = $db->createCommand("SELECT DISTINCT negara FROM master_santri s WHERE $saasWhere AND negara IS NOT NULL AND negara != '' ORDER BY negara", $saasParams)->queryColumn();
        $kepengurusanList = $db->createCommand("SELECT DISTINCT kepengurusan FROM master_santri s WHERE $saasWhere AND kepengurusan IS NOT NULL AND kepengurusan != '' ORDER BY kepengurusan", $saasParams)->queryColumn();

        // Generate EXP List
        $allSantriForExp = $db->createCommand("
            SELECT p.exp_paspor, i.exp_itas 
            FROM master_santri s
            LEFT JOIN mtb_paspor p ON s.kds = p.kds
            LEFT JOIN mtb_itas i ON s.kds = i.kds
            WHERE $saasWhere
        ", $saasParams)->queryAll();

        $expItasList = [];
        $expPasporList = [];
        foreach ($allSantriForExp as $s) {
            if (!empty($s['exp_itas'])) {
                $ym = substr((string)$s['exp_itas'], 0, 7);
                $expItasList[$ym] = $ym;
            }
            if (!empty($s['exp_paspor'])) {
                $y = substr((string)$s['exp_paspor'], 0, 4);
                $expPasporList[$y] = $y;
            }
        }
        krsort($expItasList);
        krsort($expPasporList);

        // Super Admin & Instansi Logic
        $isSuperAdmin = ($role === 'super_admin');
        $instansiList = [];
        if ($isSuperAdmin) {
            $instansiList = $db->createCommand("SELECT kode as id, nama_instansi FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'santris' => $santris,
            'pondokList' => $pondokList,
            'kelasList' => $kelasList,
            'negaraList' => $negaraList,
            'kepengurusanList' => $kepengurusanList,
            'expItasList' => array_values($expItasList),
            'expPasporList' => array_values($expPasporList),
            'params' => $params,
            'isSuperAdmin' => $isSuperAdmin,
            'instansiList' => $instansiList
        ]);
    }
}

