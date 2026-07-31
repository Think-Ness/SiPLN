<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

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
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;

        // Santri aktif (filtered by instansi for non-super_admin)
        $whereExt = "";
        $params = [':status' => 1];
        if ($role !== 'super_admin' && $instansiId) {
            $myKep = $_SESSION['def_kepengurusan'] ?? '';
            $myPondok = $_SESSION['def_pondok'] ?? '';
            if (!empty($myKep) || !empty($myPondok)) {
                $whereExt = " AND (s.kepengurusan = :my_kep OR s.pondok = :my_pondok)";
                $params[':my_kep'] = $myKep;
                $params[':my_pondok'] = $myPondok;
            }
        }

        $santris = $db->createCommand(
            "SELECT s.kds, s.stambuk, s.nama, s.kelas, s.pondok, s.negara, s.kewarganegaraan,
                    s.tempat_lahir, s.tanggal_lahir, s.alamat, s.kepengurusan,
                    p.no_paspor, p.exp_paspor, i.exp_itas
             FROM master_santri s
             LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
             LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
             WHERE s.aktif = :status $whereExt
            ORDER BY s.nama ASC",
            $params
        )->queryAll();

        $expItasList = [];
        $kelasList = [];
        $pondokList = [];
        $negaraList = [];
        foreach ($santris as $s) {
            if (!empty($s['exp_itas'])) {
                $ym = substr((string)$s['exp_itas'], 0, 7); // YYYY-MM
                $expItasList[$ym] = $ym;
            }
            if (!empty($s['kelas'])) $kelasList[$s['kelas']] = $s['kelas'];
            if (!empty($s['pondok'])) $pondokList[$s['pondok']] = $s['pondok'];
            if (!empty($s['negara'])) $negaraList[$s['negara']] = $s['negara'];
        }
        krsort($expItasList);
        ksort($kelasList);
        ksort($pondokList);
        ksort($negaraList);

        // Jenis Pengajuan
        $jenisPengajuan = $db->createCommand(
            "SELECT * FROM surat_jenis_pengajuan WHERE aktif = 1 ORDER BY jenis_pengajuan ASC"
        )->queryAll();

        // Riwayat Mailing (last 50)
        $mailingWhere = "";
        $mailingParams = [];
        if ($role !== 'super_admin' && $instansiId) {
            $mailingWhere = "WHERE m.instansi_id = :instId";
            $mailingParams[':instId'] = $instansiId;
        }

        $mailings = $db->createCommand(
            "SELECT m.*, jp.jenis_pengajuan, u.nama_lengkap as created_by_name,
                    (SELECT COUNT(*) FROM surat_mailing_santri WHERE mailing_id = m.id) as jumlah_santri,
                    (SELECT COUNT(*) FROM surat_generated WHERE mailing_id = m.id) as jumlah_surat
             FROM surat_mailing m
             LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
             LEFT JOIN users u ON m.created_by = u.id
             $mailingWhere
             ORDER BY m.created_at DESC
             LIMIT 50",
            $mailingParams
        )->queryAll();

        // Instansi data (for kop surat)
        $instansiList = [];
        $instansi = null;
        if ($role === 'super_admin') {
            $instansiList = $db->createCommand("SELECT kode, nama_instansi FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
            $instansi = $db->createCommand("SELECT * FROM master_instansi ORDER BY kode LIMIT 1")->queryOne();
        } elseif ($instansiId) {
            $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $instansiId])->queryOne();
        }

        // Current user data (for ST)
        $currentUser = $db->createCommand(
            "SELECT * FROM users WHERE id = :id",
            [':id' => $_SESSION['user_id'] ?? 0]
        )->queryOne();

        // Nomor surat counters (max for each type this year)
        $year = date('Y');
        $suratCounters = [];
        foreach (['SP', 'SK', 'SJ', 'ST'] as $tipe) {
            $maxNo = $db->createCommand(
                "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(IFNULL(nomor_akhir, nomor_surat), '/', 1) AS UNSIGNED)), 0) 
                 FROM surat_generated 
                 WHERE tipe_surat = :tipe AND YEAR(tanggal_surat) = :year",
                [':tipe' => $tipe, ':year' => $year]
            )->queryScalar();
            $suratCounters[$tipe] = (int)$maxNo + 1;
        }

        // Pegawai List untuk Surat Tugas (Dropdown Petugas)
        $pegawaiList = [];
        if (!empty($_SESSION['instansi_id'])) {
            $pegawaiList = $db->createCommand(
                "SELECT id, nama_lengkap, ttl FROM users WHERE instansi_id = :instansi_id ORDER BY nama_lengkap ASC",
                [':instansi_id' => $_SESSION['instansi_id']]
            )->queryAll();
        }

        $activeJobDeskRows = $db->createCommand("
            SELECT 
                j.kds, 
                p.nama_proses,
                (
                    SELECT jcs.step_nama 
                    FROM jobdesk_case_steps jcs 
                    WHERE jcs.case_id = j.id AND jcs.status != 'selesai' 
                    ORDER BY jcs.step_urut ASC LIMIT 1
                ) as tahap_saat_ini
            FROM jobdesk_cases j
            LEFT JOIN jobdesk_master_process p ON j.process_id = p.id
            WHERE j.status = 'aktif'
        ")->queryAll();

        $activeJobDeskMap = [];
        foreach ($activeJobDeskRows as $r) {
            if (!isset($activeJobDeskMap[$r['kds']])) {
                $activeJobDeskMap[$r['kds']] = [];
            }
            $activeJobDeskMap[$r['kds']][] = [
                'nama_proses' => $r['nama_proses'],
                'tahap_saat_ini' => $r['tahap_saat_ini']
            ];
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'santris' => $santris,
            'jenisPengajuan' => $jenisPengajuan,
            'mailings' => $mailings,
            'instansi' => $instansi ?: [],
            'currentUser' => $currentUser ?: [],
            'suratCounters' => $suratCounters,
            'expItasList' => array_values($expItasList),
            'kelasList' => array_values($kelasList),
            'pondokList' => array_values($pondokList),
            'negaraList' => array_values($negaraList),
            'pegawaiList' => $pegawaiList,
            'activeJobDeskMap' => $activeJobDeskMap,
            'instansiList' => $instansiList,
        ]);
    }
}

