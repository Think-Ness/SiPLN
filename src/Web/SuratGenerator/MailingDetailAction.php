<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class MailingDetailAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int)$currentRoute->getArgument('id', '0');

        $mailing = $db->createCommand(
            "SELECT m.*, jp.jenis_pengajuan, jp.hal, jp.isi, jp.kepada, jp.tempat, jp.surat_dibutuhkan, jp.kantor,
                    u.nama_lengkap as created_by_name
             FROM surat_mailing m
             LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
             LEFT JOIN users u ON m.created_by = u.id
             WHERE m.id = :id",
            [':id' => $id]
        )->queryOne();

        if (!$mailing) {
            return JsonResponse::create(['success' => false, 'message' => 'Mailing tidak ditemukan.'], 404);
        }

        $sortBy = $mailing['sort_by'] ?? 's.nama ASC';
        if ($sortBy === 'nama ASC') $sortBy = 's.nama ASC';
        if ($sortBy === 'negara ASC') $sortBy = 's.negara ASC';
        if ($sortBy === 'exp_itas ASC') $sortBy = 'i.exp_itas ASC';
        if ($sortBy === 'no_paspor ASC') $sortBy = 'p.no_paspor ASC';
        if ($sortBy === 'manual') $sortBy = 'ms.id ASC';

        $santris = $db->createCommand(
            "SELECT s.kds, s.nama, s.kelas, s.pondok, s.negara, s.kewarganegaraan,
                    s.tempat_lahir, s.tanggal_lahir, s.alamat, s.kepengurusan,
                    p.no_paspor, p.exp_paspor, i.exp_itas
             FROM surat_mailing_santri ms
             JOIN master_santri s ON ms.kds = s.kds
             LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
             LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
             WHERE ms.mailing_id = :mid
             ORDER BY $sortBy",
            [':mid' => $id]
        )->queryAll();

        $surats = $db->createCommand(
            "SELECT * FROM surat_generated WHERE mailing_id = :mid ORDER BY tipe_surat",
            [':mid' => $id]
        )->queryAll();

        // Instansi data
        $instansi = null;
        if (!empty($mailing['instansi_id'])) {
            $instansi = $db->createCommand(
                "SELECT * FROM master_instansi WHERE kode = :kode",
                [':kode' => $mailing['instansi_id']]
            )->queryOne();
        }

        // Current user data for ST
        $currentUser = $db->createCommand(
            "SELECT * FROM users WHERE id = :id",
            [':id' => $_SESSION['user_id'] ?? 0]
        )->queryOne();

        $kantor = $mailing['kantor'] ?? '';
        $templateSchemas = [];
        if ($kantor) {
            $schemas = $db->createCommand(
                "SELECT nama_template, json_data_collection 
                 FROM surat_template_dinamis 
                 WHERE instansi_tujuan = :kantor",
                [':kantor' => $kantor]
            )->queryAll();
            
            foreach ($schemas as $row) {
                $safeTplName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $row['nama_template']);
                $templateSchemas[$safeTplName] = $row['json_data_collection'] ? json_decode($row['json_data_collection'], true) : null;
            }
        }

        $generatedFiles = [];
        if (!empty($surats)) {
            $tanggalSurat = $mailing['tanggal_surat'] ?? date('Y-m-d');
            $tahunItas = date('Y', strtotime($tanggalSurat));
            $bulanItas = date('m', strtotime($tanggalSurat));
            
            if (!empty($santris) && !empty($santris[0]['exp_itas']) && $santris[0]['exp_itas'] !== '-') {
                $tahunItas = date('Y', strtotime($santris[0]['exp_itas']));
                $bulanItas = date('m', strtotime($santris[0]['exp_itas']));
            }
            
            $safeJenis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $mailing['jenis_pengajuan'] ?? 'Umum');
            
            foreach ($surats as $s) {
                $tipeLabel = ['SP' => 'Surat_Permohonan', 'SK' => 'Surat_Keterangan', 'SJ' => 'Surat_Jaminan', 'ST' => 'Surat_Tugas'];
                $safeTipeSurat = $tipeLabel[$s['tipe_surat']] ?? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s['tipe_surat']);
                
                $baseUrl = '/webapp/public/uploads/Surat_Menyurat/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
                
                if ($mailing['mode'] === 'sekaligus') {
                    $fileNameBase = "{$safeTipeSurat}_Sekaligus_{$safeJenis}";
                    $generatedFiles[] = [
                        'tipe_surat' => $s['tipe_surat'],
                        'nomor_surat' => $s['nomor_surat'],
                        'name' => "Surat " . ($s['tipe_surat']) . " (Kolektif)",
                        'url' => $baseUrl . $fileNameBase . '.pdf'
                    ];
                } else {
                    if (!empty($santris)) {
                        $baseNoStr = explode('/', $s['nomor_surat'])[0];
                        $baseNo = (int)$baseNoStr;
                        $suffix = substr($s['nomor_surat'], strlen($baseNoStr));
                        
                        foreach ($santris as $i => $santri) {
                            $currentNo = $baseNo + $i;
                            $nomorSurat = str_pad((string)$currentNo, 3, '0', STR_PAD_LEFT) . $suffix;
                            
                            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $santri['nama']);
                            $fileNameBase = "{$safeTipeSurat}_{$safeName}";
                            
                            $generatedFiles[] = [
                                'tipe_surat' => $s['tipe_surat'],
                                'nomor_surat' => $nomorSurat,
                                'name' => "Surat " . ($s['tipe_surat']) . " - " . $santri['nama'],
                                'url' => $baseUrl . $fileNameBase . '.pdf'
                            ];
                        }
                    }
                }
            }
        }

        return JsonResponse::create([
            'success' => true,
            'mailing' => $mailing,
            'santris' => $santris,
            'surats' => $surats,
            'generatedFiles' => $generatedFiles,
            'instansi' => $instansi ?: [],
            'currentUser' => $currentUser ?: [],
            'templateSchemas' => $templateSchemas,
        ]);
    }
}

