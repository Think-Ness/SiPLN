<?php
declare(strict_types=1);

namespace App\Web\HomePage;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class ApiAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $viewFilter = $request->getQueryParams()['view'] ?? 'my'; // 'my' or 'all'
        
        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        $whereExt = "";
        $params = [];
        
        // Only apply isolation if view is 'my', OR if the user is forced (e.g. no bypass allowed, but you requested they can see it)
        if ($viewFilter === 'my' && (!empty($myKepengurusan) || !empty($myPondok))) {
            $whereExt = " AND (kepengurusan = :my_kepengurusan OR pondok = :my_pondok) ";
            $sWhereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok) ";
            $params[':my_kepengurusan'] = $myKepengurusan;
            $params[':my_pondok'] = $myPondok;
        } else {
            $sWhereExt = $whereExt;
        }

        // Stats
        $total     = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE 1=1 $whereExt", $params)->queryScalar();
        $aktif     = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE aktif=1 AND status_santri != 'Program Penerimaan' $whereExt", $params)->queryScalar();
        $capel      = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE status_santri = 'Program Penerimaan' $whereExt", $params)->queryScalar();
        $alumni     = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE kelas = 'Alumni' $whereExt", $params)->queryScalar();
        $pengabdian = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE kelas = 'Pengabdian' $whereExt", $params)->queryScalar();
        $pengabdianBreakdown = $db->createCommand("SELECT pondok, COUNT(*) as count FROM master_santri WHERE kelas = 'Pengabdian' $whereExt GROUP BY pondok ORDER BY count DESC", $params)->queryAll();
        $inaktif    = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE aktif=0 AND status_santri != 'Program Penerimaan' $whereExt", $params)->queryScalar();

        // Santri Asli vs Pindahan (Only relevant if view = 'my' and user has instansi)
        $santriAsli = 0;
        $santriPindahan = 0;
        
        if ($viewFilter === 'my' && !empty($myKepengurusan)) {
            $asliParams = [':my_kepengurusan' => $myKepengurusan];
            $santriAsli = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE aktif=1 AND kepengurusan = :my_kepengurusan", $asliParams)->queryScalar();
            
            if (!empty($myPondok)) {
                $pindahParams = [':my_kepengurusan' => $myKepengurusan, ':my_pondok' => $myPondok];
                $santriPindahan = (int) $db->createCommand("SELECT COUNT(*) FROM master_santri WHERE aktif=1 AND kepengurusan != :my_kepengurusan AND pondok = :my_pondok", $pindahParams)->queryScalar();
            }
        }

        $status = $request->getQueryParams()['status'] ?? '1';
        $whereStatus = "WHERE 1=1 $sWhereExt";
        if ($status === '1') {
            $whereStatus = "WHERE s.aktif = 1 $sWhereExt";
        } elseif ($status === '0') {
            $whereStatus = "WHERE s.aktif = 0 $sWhereExt";
        }

        // Raw Data for Client-Side Aggregation & Drill-down
        $santris = $db->createCommand("
            SELECT s.kds, s.stambuk, s.nama, s.kelas, s.negara, s.kewarganegaraan, s.pondok, s.kepengurusan, s.aktif,
                   p.no_paspor, p.exp_paspor, i.no_itas, i.exp_itas
            FROM master_santri s
            LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            LEFT JOIN (SELECT kds, no_itas, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
            $whereStatus
        ", $params)->queryAll();

        // Paspor segera expired (30 hari)
        $expPasporSoon = (int) $db->createCommand(
            "SELECT COUNT(p.id) FROM mtb_paspor p JOIN master_santri s ON p.kds = s.kds WHERE p.exp_paspor <= DATE_ADD(CURDATE(), INTERVAL 1 MONTH) AND p.exp_paspor >= CURDATE() $sWhereExt", $params
        )->queryScalar();

        // ITAS segera expired (3 bulan)
        $expItasSoon = (int) $db->createCommand(
            "SELECT COUNT(i.id) FROM mtb_itas i JOIN master_santri s ON i.kds = s.kds WHERE i.exp_itas <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH) AND i.exp_itas >= CURDATE() $sWhereExt", $params
        )->queryScalar();

        return JsonResponse::create([
            'success' => true,
            'data' => [
                'total'          => $total,
                'aktif'          => $aktif,
                'inaktif'        => $inaktif,
                'capel'          => $capel,
                'alumni'         => $alumni,
                'pengabdian'     => $pengabdian,
                'pengabdianBreakdown' => $pengabdianBreakdown,
                'expPasporSoon'  => $expPasporSoon,
                'expItasSoon'    => $expItasSoon,
                'santris'        => $santris,
                'santriAsli'     => $santriAsli,
                'santriPindahan' => $santriPindahan,
                'status'         => $status,
                'view'           => $viewFilter,
            ]
        ]);
    }
}

