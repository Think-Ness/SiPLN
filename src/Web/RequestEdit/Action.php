<?php
declare(strict_types=1);

namespace App\Web\RequestEdit;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myInstansiKode = $_SESSION['instansi_id'] ?? '';

        // Ensure is_read and old_values columns exist
        try {
            $db->createCommand("ALTER TABLE santri_edit_requests ADD COLUMN is_read TINYINT(1) DEFAULT 0")->execute();
        } catch (\Throwable $t) {}
        try {
            $db->createCommand("ALTER TABLE santri_edit_requests ADD COLUMN old_values TEXT DEFAULT NULL")->execute();
        } catch (\Throwable $t) {}

        // Mark outgoing approved/rejected requests as read since the user is visiting the page
        if ($myInstansiKode !== '') {
            $db->createCommand("UPDATE santri_edit_requests SET is_read = 1 WHERE requested_by_instansi_id = :myInstansi AND status IN ('approved', 'rejected') AND is_read = 0", [
                ':myInstansi' => $myInstansiKode
            ])->execute();
        }

        // 1. INCOMING REQUESTS (Yang menunggu persetujuan Anda)
        $sqlIn = "
            SELECT 
                r.*, 
                s.stambuk, s.nama, s.kelas, s.rayon, s.pondok, s.negara, s.kewarganegaraan, s.jenis_kelamin, s.kepengurusan, s.tempat_lahir, s.tanggal_lahir, s.nama_ayah, s.nama_ibu, s.no_ayah, s.no_ibu, s.no_hp_alternatif, s.alamat, s.no_sktt as nik, s.no_ic, s.keberadaan_paspor, s.ukuran_baju,
                p.no_paspor, p.tempat_dikeluarkan as tempat_paspor, p.tanggal_dikeluarkan as tgl_paspor, p.exp_paspor, p.no_paspor as no_paspor_lama,
                it.no_itas, it.exp_itas, it.level_itas,
                i.def_pondok as instansi_pengaju,
                i.def_kepengurusan as kep_pengaju
            FROM santri_edit_requests r
            JOIN master_santri s ON r.kds = s.kds
            LEFT JOIN mtb_paspor p ON r.kds = p.kds AND p.aktif = 1
            LEFT JOIN mtb_itas it ON r.kds = it.kds AND it.aktif = 1
            LEFT JOIN master_instansi i ON r.requested_by_instansi_id = i.kode
            WHERE r.status = 'pending'
        ";

        $paramsIn = [];
        if ($myKepengurusan !== '') {
            $sqlIn .= " AND r.target_kepengurusan = :myKep";
            $paramsIn[':myKep'] = trim($myKepengurusan);
        }
        $sqlIn .= " ORDER BY r.created_at DESC";
        $incomingRequests = $db->createCommand($sqlIn, $paramsIn)->queryAll();

        // 2. OUTGOING REQUESTS (Pengajuan Saya ke kepengurusan lain)
        $sqlOut = "
            SELECT 
                r.*, 
                s.stambuk, s.nama, s.kelas, s.rayon, s.pondok, s.negara, s.kewarganegaraan, s.jenis_kelamin, s.kepengurusan, s.tempat_lahir, s.tanggal_lahir, s.nama_ayah, s.nama_ibu, s.no_ayah, s.no_ibu, s.no_hp_alternatif, s.alamat, s.no_sktt as nik, s.no_ic, s.keberadaan_paspor, s.ukuran_baju,
                p.no_paspor, p.tempat_dikeluarkan as tempat_paspor, p.tanggal_dikeluarkan as tgl_paspor, p.exp_paspor, p.no_paspor as no_paspor_lama,
                it.no_itas, it.exp_itas, it.level_itas,
                r.target_kepengurusan as kep_tujuan
            FROM santri_edit_requests r
            JOIN master_santri s ON r.kds = s.kds
            LEFT JOIN mtb_paspor p ON r.kds = p.kds AND p.aktif = 1
            LEFT JOIN mtb_itas it ON r.kds = it.kds AND it.aktif = 1
            WHERE r.requested_by_instansi_id = :myInstansiKode
            ORDER BY r.created_at DESC
        ";
        $outgoingRequests = $db->createCommand($sqlOut, [':myInstansiKode' => $myInstansiKode])->queryAll();

        // 3. HISTORY REQUESTS (Riwayat Persetujuan yang sudah diproses)
        $sqlHist = "
            SELECT 
                r.*, 
                s.stambuk, s.nama, s.kelas, s.rayon, s.pondok, s.negara, s.kewarganegaraan, s.jenis_kelamin, s.kepengurusan, s.tempat_lahir, s.tanggal_lahir, s.nama_ayah, s.nama_ibu, s.no_ayah, s.no_ibu, s.no_hp_alternatif, s.alamat, s.no_sktt as nik, s.no_ic, s.keberadaan_paspor, s.ukuran_baju,
                p.no_paspor, p.tempat_dikeluarkan as tempat_paspor, p.tanggal_dikeluarkan as tgl_paspor, p.exp_paspor, p.no_paspor as no_paspor_lama,
                it.no_itas, it.exp_itas, it.level_itas,
                i.def_pondok as instansi_pengaju,
                i.def_kepengurusan as kep_pengaju
            FROM santri_edit_requests r
            JOIN master_santri s ON r.kds = s.kds
            LEFT JOIN mtb_paspor p ON r.kds = p.kds AND p.aktif = 1
            LEFT JOIN mtb_itas it ON r.kds = it.kds AND it.aktif = 1
            LEFT JOIN master_instansi i ON r.requested_by_instansi_id = i.kode
            WHERE r.status != 'pending'
        ";
        $paramsHist = [];
        if ($myKepengurusan !== '') {
            $sqlHist .= " AND r.target_kepengurusan = :myKep";
            $paramsHist[':myKep'] = trim($myKepengurusan);
        }
        $sqlHist .= " ORDER BY r.created_at DESC LIMIT 50";
        $historyRequests = $db->createCommand($sqlHist, $paramsHist)->queryAll();

        return $viewRenderer->render(__DIR__ . '/template', [
            'requests' => $incomingRequests,
            'outgoingRequests' => $outgoingRequests,
            'historyRequests' => $historyRequests,
            'myKepengurusan' => $myKepengurusan
        ]);
    }
}
