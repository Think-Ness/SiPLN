<?php
declare(strict_types=1);

namespace App\Web\JobDesk;

use App\Shared\ApplicationParams;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

class KeuanganDashboardAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db,
        ApplicationParams $applicationParams
    ): ResponseInterface {
        
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        $whereExt = "";
        $params = [];
        if ($role !== 'super_admin') {
            if (!empty($myKepengurusan) && !empty($myPondok)) {
                $whereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok) ";
                $params[':my_kepengurusan'] = $myKepengurusan;
                $params[':my_pondok'] = $myPondok;
            } else if (!empty($myKepengurusan)) {
                $whereExt = " AND s.kepengurusan = :my_kepengurusan ";
                $params[':my_kepengurusan'] = $myKepengurusan;
            } else if (!empty($myPondok)) {
                $whereExt = " AND s.pondok = :my_pondok ";
                $params[':my_pondok'] = $myPondok;
            }
        }

        // Auto-create installment table if not exists
        try {
            $db->createCommand("
                CREATE TABLE IF NOT EXISTS jobdesk_payment_installment (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    case_id INT NOT NULL,
                    nominal DECIMAL(12,2) NOT NULL,
                    tgl_bayar DATE NOT NULL,
                    catatan VARCHAR(255) DEFAULT NULL,
                    created_by VARCHAR(100) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ")->execute();
        } catch (\Exception $e) { /* Table might already exist */ }

        // Auto-create pengeluaran birokrasi table
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
        } catch (\Exception $e) { /* Table might already exist */ }

        // 1. Hitung Total Uang Operasional (Lunas Kedua Belah Pihak)
        $totalSurplus = $db->createCommand("
            SELECT SUM(p.selisih_operasional) as total
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            WHERE p.status_bayar_santri = 'lunas' AND p.status_bayar_instansi = 'lunas' $whereExt
        ", $params)->queryScalar() ?: 0;

        // 2. Hitung Piutang Santri (Belum dibayar oleh Santri)
        $piutangSantri = $db->createCommand("
            SELECT SUM(p.nominal_santri) as total
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            WHERE p.status_bayar_santri = 'belum' $whereExt
        ", $params)->queryScalar() ?: 0;

        // 3. Hitung Hutang Instansi (Belum disetor ke Instansi)
        $hutangInstansi = $db->createCommand("
            SELECT SUM(p.nominal_instansi) as total
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            WHERE p.status_bayar_instansi = 'belum' $whereExt
        ", $params)->queryScalar() ?: 0;

        // 4. Data Transaksi per Bulan (Trend)
        $monthlyTrendRaw = $db->createCommand("
            SELECT 
                DATE_FORMAT(p.updated_at, '%Y-%m') as month,
                SUM(p.selisih_operasional) as surplus,
                SUM(p.nominal_santri) as penerimaan
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            WHERE p.status_bayar_santri = 'lunas' AND p.status_bayar_instansi = 'lunas' $whereExt
            GROUP BY month
            ORDER BY month ASC
            LIMIT 12
        ", $params)->queryAll();

        $labels = [];
        $surplusData = [];
        $penerimaanData = [];
        foreach ($monthlyTrendRaw as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $surplusData[] = (float)$row['surplus'];
            $penerimaanData[] = (float)$row['penerimaan'];
        }

        // 5. Data Transaksi Lengkap (Tabel) + cicilan info
        $transactions = $db->createCommand("
            SELECT 
                p.*,
                c.kds, c.created_at as case_date,
                s.nama,
                m.nama_proses,
                COALESCE((SELECT SUM(i.nominal) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id), 0) as total_cicilan,
                (SELECT COUNT(i.id) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id) as jumlah_cicilan
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            JOIN jobdesk_master_process m ON c.process_id = m.id
            WHERE 1=1 $whereExt
            ORDER BY p.updated_at DESC
            LIMIT 1000
        ", $params)->queryAll();

        // 6. Data Menunggak (Piutang Santri) + cicilan
        $piutangSantriDetails = $db->createCommand("
            SELECT 
                p.id as payment_id, p.case_id, p.nominal_santri, p.nominal_instansi,
                p.status_bayar_santri, p.tgl_bayar_santri,
                c.kds, s.nama, m.nama_proses, m.id as process_id,
                COALESCE((SELECT SUM(i.nominal) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id), 0) as total_cicilan,
                (SELECT COUNT(i.id) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id) as jumlah_cicilan
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            JOIN jobdesk_master_process m ON c.process_id = m.id
            WHERE p.status_bayar_santri = 'belum' $whereExt
            ORDER BY s.nama ASC
        ", $params)->queryAll();

        // 7. Hutang Instansi per Case (untuk popup bayar lunas)
        $hutangInstansiDetails = $db->createCommand("
            SELECT 
                m.nama_proses,
                p.case_id,
                p.nominal_instansi,
                p.tgl_bayar_instansi,
                c.kds, s.nama as nama_santri
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            JOIN jobdesk_master_process m ON c.process_id = m.id
            WHERE p.status_bayar_instansi = 'belum' $whereExt
            ORDER BY m.nama_proses ASC, s.nama ASC
        ", $params)->queryAll();

        // 8. Summary per Jobdesk (untuk filter kumulatif)
        $summaryPerJobdesk = $db->createCommand("
            SELECT 
                m.nama_proses,
                m.id as process_id,
                COUNT(p.id) as jumlah_kasus,
                SUM(p.nominal_santri) as total_nominal_santri,
                SUM(CASE WHEN p.status_bayar_santri = 'lunas' THEN p.nominal_santri ELSE 0 END) as total_lunas_santri,
                SUM(CASE WHEN p.status_bayar_santri = 'belum' THEN p.nominal_santri ELSE 0 END) as total_belum_santri,
                SUM(p.nominal_instansi) as total_nominal_instansi,
                SUM(CASE WHEN p.status_bayar_instansi = 'lunas' THEN p.nominal_instansi ELSE 0 END) as total_lunas_instansi,
                SUM(CASE WHEN p.status_bayar_instansi = 'belum' THEN p.nominal_instansi ELSE 0 END) as total_belum_instansi,
                SUM(p.selisih_operasional) as total_selisih
            FROM jobdesk_case_payment p
            JOIN jobdesk_cases c ON p.case_id = c.id
            JOIN master_santri s ON c.kds = s.kds
            JOIN jobdesk_master_process m ON c.process_id = m.id
            WHERE 1=1 $whereExt
            GROUP BY m.id, m.nama_proses
            ORDER BY m.nama_proses ASC
        ", $params)->queryAll();

        // 9. Info Instansi (untuk kop surat print)
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $instansiInfo = [];
        $semuaInstansi = [];
        if ($instansiId) {
            $instansiInfo = $db->createCommand(
                "SELECT * FROM master_instansi WHERE kode = :kode LIMIT 1",
                [':kode' => $instansiId]
            )->queryOne() ?: [];
        } elseif ($role === 'super_admin') {
            $instansiInfo = $db->createCommand("SELECT * FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne() ?: [];
            $semuaInstansi = $db->createCommand("SELECT kode, nama_instansi FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
        }

        return $viewRenderer->render(__DIR__ . '/keuangan_dashboard', [
            'totalSurplus'          => (float)$totalSurplus,
            'piutangSantri'         => (float)$piutangSantri,
            'hutangInstansi'        => (float)$hutangInstansi,
            'chartLabels'           => json_encode($labels),
            'chartSurplusData'      => json_encode($surplusData),
            'chartPenerimaanData'   => json_encode($penerimaanData),
            'transactions'          => $transactions,
            'piutangSantriDetails'  => $piutangSantriDetails,
            'hutangInstansiDetails' => $hutangInstansiDetails,
            'summaryPerJobdesk'     => $summaryPerJobdesk,
            'instansiInfo'          => $instansiInfo,
            'semuaInstansi'         => $semuaInstansi,
            'applicationParams'     => $applicationParams,

        ]);
    }
}
