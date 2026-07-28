<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use App\Shared\ApplicationParams;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

class PengeluaranDashboardAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db,
        ApplicationParams $applicationParams
    ): ResponseInterface {
        
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;

        // Auto-create tables
        try {
            $db->createCommand("
                CREATE TABLE IF NOT EXISTS jobdesk_pengeluaran_birokrasi (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    instansi_id VARCHAR(50) DEFAULT NULL,
                    nominal DECIMAL(15,2) NOT NULL DEFAULT 0,
                    keterangan TEXT DEFAULT NULL,
                    kategori VARCHAR(100) DEFAULT 'Lainnya',
                    tanggal DATE NOT NULL,
                    foto_nota VARCHAR(500) DEFAULT NULL,
                    created_by VARCHAR(100) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ")->execute();
        } catch (\Exception $e) {}

        try {
            $db->createCommand("
                CREATE TABLE IF NOT EXISTS jobdesk_kategori_pengeluaran (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    instansi_id VARCHAR(50) DEFAULT NULL,
                    nama VARCHAR(150) NOT NULL,
                    icon VARCHAR(50) DEFAULT 'bi-tag',
                    warna VARCHAR(20) DEFAULT '#6c757d',
                    urut INT DEFAULT 0,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ")->execute();

            // Seed default categories if empty
            $count = (int)$db->createCommand("SELECT COUNT(*) FROM jobdesk_kategori_pengeluaran")->queryScalar();
            if ($count === 0) {
                $defaults = [
                    ['ATK & Perlengkapan', 'bi-paperclip', '#0d6efd', 1],
                    ['Transportasi', 'bi-truck', '#198754', 2],
                    ['Konsumsi', 'bi-cup-hot', '#fd7e14', 3],
                    ['Fotokopi & Cetak', 'bi-printer', '#6610f2', 4],
                    ['Komunikasi', 'bi-telephone', '#20c997', 5],
                    ['Lainnya', 'bi-box', '#6c757d', 6],
                ];
                foreach ($defaults as $d) {
                    $db->createCommand()->insert('jobdesk_kategori_pengeluaran', [
                        'instansi_id' => null,
                        'nama' => $d[0],
                        'icon' => $d[1],
                        'warna' => $d[2],
                        'urut' => $d[3],
                    ])->execute();
                }
            }
        } catch (\Exception $e) {}

        // Fetch categories
        $kategoris = [];
        $katWhere = "WHERE is_active = 1 AND (instansi_id IS NULL";
        $katParams = [];
        if ($instansiId) {
            $katWhere .= " OR instansi_id = :kat_inst_id";
            $katParams[':kat_inst_id'] = $instansiId;
        }
        $katWhere .= ")";
        $kategoris = $db->createCommand("SELECT * FROM jobdesk_kategori_pengeluaran $katWhere ORDER BY urut ASC, nama ASC", $katParams)->queryAll();

        // Build category color map
        $kategoriColors = [];
        $kategoriIcons = [];
        foreach ($kategoris as $k) {
            $kategoriColors[$k['nama']] = $k['warna'];
            $kategoriIcons[$k['nama']] = $k['icon'];
        }

        // Pengeluaran filter
        $pengeluaranWhere = "WHERE 1=1";
        $pengeluaranParams = [];
        if ($role !== 'super_admin' && $instansiId) {
            $pengeluaranWhere .= " AND p.instansi_id = :p_instansi_id";
            $pengeluaranParams[':p_instansi_id'] = $instansiId;
        }

        // Total Pemasukan (from jobdesk payments - lunas)
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        $whereExt = "";
        $pemasukanParams = [];
        if ($role !== 'super_admin') {
            if (!empty($myKepengurusan) && !empty($myPondok)) {
                $whereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok) ";
                $pemasukanParams[':my_kepengurusan'] = $myKepengurusan;
                $pemasukanParams[':my_pondok'] = $myPondok;
            } else if (!empty($myKepengurusan)) {
                $whereExt = " AND s.kepengurusan = :my_kepengurusan ";
                $pemasukanParams[':my_kepengurusan'] = $myKepengurusan;
            } else if (!empty($myPondok)) {
                $whereExt = " AND s.pondok = :my_pondok ";
                $pemasukanParams[':my_pondok'] = $myPondok;
            }
        }

        $totalPemasukan = (float)($db->createCommand("
            SELECT COALESCE(SUM(p.selisih_operasional), 0) as total
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            WHERE p.status_bayar_santri = 'lunas' AND p.status_bayar_instansi = 'lunas' $whereExt
        ", $pemasukanParams)->queryScalar() ?: 0);

        $totalPengeluaran = (float)($db->createCommand("
            SELECT COALESCE(SUM(p.nominal), 0) FROM jobdesk_pengeluaran_birokrasi p $pengeluaranWhere
        ", $pengeluaranParams)->queryScalar() ?: 0);

        $saldoOperasional = $totalPemasukan - $totalPengeluaran;

        // Pengeluaran list
        $pengeluaranList = $db->createCommand("
            SELECT p.* FROM jobdesk_pengeluaran_birokrasi p
            $pengeluaranWhere
            ORDER BY p.tanggal DESC, p.created_at DESC
            LIMIT 500
        ", $pengeluaranParams)->queryAll();

        // Per kategori summary
        $pengeluaranPerKategori = $db->createCommand("
            SELECT p.kategori, SUM(p.nominal) as total, COUNT(*) as jumlah
            FROM jobdesk_pengeluaran_birokrasi p
            $pengeluaranWhere
            GROUP BY p.kategori
            ORDER BY total DESC
        ", $pengeluaranParams)->queryAll();

        // Per bulan trend
        $pengeluaranBulanan = $db->createCommand("
            SELECT DATE_FORMAT(p.tanggal, '%Y-%m') as month, SUM(p.nominal) as total
            FROM jobdesk_pengeluaran_birokrasi p
            $pengeluaranWhere
            GROUP BY month ORDER BY month ASC LIMIT 12
        ", $pengeluaranParams)->queryAll();

        $bulananLabels = [];
        $bulananData = [];
        foreach ($pengeluaranBulanan as $row) {
            $bulananLabels[] = date('M Y', strtotime($row['month'] . '-01'));
            $bulananData[] = (float)$row['total'];
        }

        return $viewRenderer->render(__DIR__ . '/pengeluaran_dashboard', [
            'totalPemasukan'        => $totalPemasukan,
            'totalPengeluaran'      => $totalPengeluaran,
            'saldoOperasional'      => $saldoOperasional,
            'pengeluaranList'       => $pengeluaranList,
            'pengeluaranPerKategori' => $pengeluaranPerKategori,
            'pengeluaranBulanan'    => $pengeluaranBulanan,
            'bulananLabels'         => json_encode($bulananLabels),
            'bulananData'           => json_encode($bulananData),
            'kategoris'             => $kategoris,
            'kategoriColors'        => $kategoriColors,
            'kategoriIcons'         => $kategoriIcons,
            'applicationParams'     => $applicationParams,
        ]);
    }
}
