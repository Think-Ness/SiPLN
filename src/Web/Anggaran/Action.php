<?php

declare(strict_types=1);

namespace App\Web\Anggaran;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

class Action
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['role'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        $isAdmin = $role === 'super_admin';

        // Fetch Hijri Months for dropdown (Next 6 months logic could be dynamic, but let's hardcode some for now or use a basic list)
        $hijriMonths = [
            'Muharram', 'Safar', 'Rabiul Awal', 'Rabiul Akhir', 'Jumadil Awal', 'Jumadil Akhir',
            'Rajab', 'Syaban', 'Ramadhan', 'Syawal', 'Dzulqaidah', 'Dzulhijjah'
        ];
        
        // Calculate current approximate Hijri month/year
        $diffDays = floor((time() - strtotime('2024-07-07')) / 86400); // 1 Muharram 1446
        $currY = 1446 + floor($diffDays / 354.367);
        $daysInYear = $diffDays - (floor($diffDays / 354.367) * 354.367);
        $currMIdx = floor($daysInYear / 29.5305);
        if ($currMIdx > 11) $currMIdx = 11;
        if ($currMIdx < 0) { $currMIdx += 12; $currY--; }
        $currentHijriMonth = $hijriMonths[(int)$currMIdx];
        $currentHijriYear = $currY;

        // Fetch Pondok list if Super Admin
        $pondokList = [];
        if ($isAdmin) {
            $stmtP = $db->createCommand("SELECT DISTINCT pondok FROM master_santri WHERE pondok IS NOT NULL AND pondok != '' ORDER BY pondok")->queryAll();
            foreach ($stmtP as $s) {
                if (!empty($s['pondok'])) $pondokList[] = ['nama' => $s['pondok']];
            }
        }

        // Fetch pengajuan data
        $query = "SELECT * FROM anggaran_pengajuan ";
        $params = [];
        if (!$isAdmin) {
            $query .= " WHERE instansi = :pondok ";
            $params[':pondok'] = $myPondok;
        }
        $query .= " ORDER BY id DESC";

        // Fetch Instansi Map for Kop Surat and Real Name
        $instansiMap = [];
        try {
            $instansis = $db->createCommand("SELECT kode, nama_instansi, pondok FROM master_instansi")->queryAll();
            foreach ($instansis as $ins) {
                if (!empty($ins['pondok'])) {
                    $instansiMap[$ins['pondok']] = $ins;
                }
            }
        } catch (\Exception $e) {
            // Ignore if table doesn't exist
        }

        $pengajuanRaw = $db->createCommand($query, $params)->queryAll();

        $pengajuanList = [];
        foreach ($pengajuanRaw as $p) {
            // Get items
            $items = $db->createCommand("SELECT * FROM anggaran_pengajuan_items WHERE pengajuan_id = :id", [':id' => $p['id']])->queryAll();
            
            // Get total used from notas
            $totalUsed = (float) $db->createCommand("SELECT SUM(total_belanja) FROM anggaran_nota WHERE pengajuan_id = :id", [':id' => $p['id']])->queryScalar();
            
            $p['items'] = $items;
            $p['total_digunakan'] = $totalUsed ?: 0;
            $p['sisa_anggaran'] = $p['total_disetujui'] - $p['total_digunakan'];
            
            // Calculate durations
            $p['durasi_cair'] = '-';
            if (!empty($p['tanggal_pengajuan']) && !empty($p['tanggal_disetujui'])) {
                $diff = strtotime($p['tanggal_disetujui']) - strtotime($p['tanggal_pengajuan']);
                $p['durasi_cair'] = max(1, round($diff / 86400)) . ' Hari';
            }
            
            $p['durasi_lapor'] = '-';
            if (!empty($p['tanggal_disetujui'])) {
                $endTime = !empty($p['tanggal_selesai']) ? strtotime($p['tanggal_selesai']) : time();
                $diff = $endTime - strtotime($p['tanggal_disetujui']);
                $p['durasi_lapor'] = max(1, round($diff / 86400)) . ' Hari';
            }

            $pengajuanList[] = $p;
        }

        return $viewRenderer
            ->withViewPath(__DIR__)
            ->render('template', [
                'pengajuanList' => $pengajuanList,
                'isAdmin' => $isAdmin,
                'myPondok' => $myPondok,
                'hijriMonths' => $hijriMonths,
                'currentHijriMonth' => $currentHijriMonth,
                'currentHijriYear' => $currentHijriYear,
                'pondokList' => $pondokList,
                'instansiMap' => $instansiMap
            ]);
    }
}
