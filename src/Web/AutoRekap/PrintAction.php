<?php

declare(strict_types=1);

namespace App\Web\AutoRekap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class PrintAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $q       = $request->getQueryParams()['q']       ?? '';
        $kep     = $request->getQueryParams()['kep']     ?? '';
        $pondok  = $request->getQueryParams()['pondok']  ?? '';
        $urgency = $request->getQueryParams()['urgency'] ?? 'period'; // period | urgent | all
        $type    = $request->getQueryParams()['type']    ?? 'itas';

        $role = $_SESSION['role'] ?? '';
        $myKep = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        $whereClause = "WHERE s.aktif=1";
        $params = [];
        if ($q !== '') {
            $whereClause .= " AND (s.nama LIKE :q OR p.no_paspor LIKE :q OR s.stambuk LIKE :q)";
            $params[':q'] = "%$q%";
        }
        if ($kep !== '') {
            $whereClause .= " AND s.kepengurusan = :kep";
            $params[':kep'] = $kep;
        }
        if ($pondok !== '') {
            $whereClause .= " AND s.pondok = :pondok";
            $params[':pondok'] = $pondok;
        }

        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        if ($role !== 'super_admin' && (!empty($myKep) || !empty($myPondok))) {
            $whereClause .= " AND (s.kepengurusan = :my_kep OR s.pondok = :my_pondok)";
            $params[':my_kep'] = $myKep;
            $params[':my_pondok'] = $myPondok;
        }

        $groupedData = [];

        if ($type === 'itas') {
            // Determine date range for ITAS based on urgency
            $itasCondition = match($urgency) {
                'urgent'  => "i.exp_itas <= DATE_ADD(CURDATE(), INTERVAL 1 MONTH)",
                'all'     => "1=1",
                default   => "i.exp_itas <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)",
            };

            $sqlItas = "
                SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara as daerah, s.rayon, s.pondok as pondok, s.kepengurusan, i.exp_itas, p.exp_paspor, i.level_itas as itas_lvl
                FROM master_santri s
                LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
                LEFT JOIN (SELECT kds, exp_itas, level_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
                $whereClause AND $itasCondition
                ORDER BY i.exp_itas ASC, s.negara ASC, s.kelas ASC, s.pondok ASC, s.nama ASC
            ";
            $data = $db->createCommand($sqlItas, $params)->queryAll();

            // Group by exact exp_itas date
            foreach ($data as $row) {
                if (!$row['exp_itas']) {
                    $isWNI = strtolower(trim((string)$row['daerah'])) === 'indonesia';
                    $dateGroup = $isWNI ? 'Belum Ada Data (WNI)' : 'Belum Ada Data (WNA)';
                } else {
                    $dateGroup = $row['exp_itas'];
                }
                
                $sKep = trim((string)($row['kepengurusan'] ?? ''));
                $statusPindahan = ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0) ? 'Pindahan' : 'Asli';

                if (!isset($groupedData[$dateGroup])) {
                    $groupedData[$dateGroup] = ['Asli' => [], 'Pindahan' => []];
                }
                $groupedData[$dateGroup][$statusPindahan][] = $row;
            }
        } else {
            // Determine date range for Paspor based on urgency
            $pasporCondition = match($urgency) {
                'urgent'  => "p.exp_paspor <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)",
                'all'     => "1=1",
                default   => "p.exp_paspor <= DATE_ADD(CURDATE(), INTERVAL 18 MONTH)",
            };

            $sqlPaspor = "
                SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara as daerah, s.rayon, s.pondok as pondok, s.kepengurusan, p.exp_paspor, i.exp_itas, i.level_itas as itas_lvl
                FROM master_santri s
                LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
                LEFT JOIN (SELECT kds, exp_itas, level_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
                $whereClause AND $pasporCondition
                ORDER BY p.exp_paspor ASC, s.negara ASC, s.kelas ASC, s.pondok ASC, s.nama ASC
            ";
            $data = $db->createCommand($sqlPaspor, $params)->queryAll();

            // Group by Month & Year of exp_paspor
            foreach ($data as $row) {
                if (!$row['exp_paspor']) {
                    $isWNI = strtolower(trim((string)$row['daerah'])) === 'indonesia';
                    $dateGroup = $isWNI ? 'Belum Ada Data (WNI)' : 'Belum Ada Data (WNA)';
                } else {
                    $dateGroup = date('Y-m', strtotime($row['exp_paspor']));
                }
                
                $sKep = trim((string)($row['kepengurusan'] ?? ''));
                $statusPindahan = ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0) ? 'Pindahan' : 'Asli';

                if (!isset($groupedData[$dateGroup])) {
                    $groupedData[$dateGroup] = ['Asli' => [], 'Pindahan' => []];
                }
                $groupedData[$dateGroup][$statusPindahan][] = $row;
            }
        }

        // Move 'Belum Ada Data (WNA)' and 'Belum Ada Data (WNI)' to the end
        $reordered = [];
        $specialGroups = [];
        foreach ($groupedData as $k => $v) {
            if ($k === 'Belum Ada Data (WNI)' || $k === 'Belum Ada Data (WNA)') {
                $specialGroups[$k] = $v;
            } else {
                $reordered[$k] = $v;
            }
        }
        
        // Ensure WNA comes before WNI in the special groups if both exist
        if (isset($specialGroups['Belum Ada Data (WNA)'])) {
            $reordered['Belum Ada Data (WNA)'] = $specialGroups['Belum Ada Data (WNA)'];
        }
        if (isset($specialGroups['Belum Ada Data (WNI)'])) {
            $reordered['Belum Ada Data (WNI)'] = $specialGroups['Belum Ada Data (WNI)'];
        }
        
        $groupedData = $reordered;

        return $viewRenderer
            ->withLayout(null)
            ->render(__DIR__ . '/print', [
                'type'        => $type,
                'groupedData' => $groupedData,
                'urgency'     => $urgency,
                'kep'         => $kep,
                'pondok'      => $pondok,
            ]);
    }
}

