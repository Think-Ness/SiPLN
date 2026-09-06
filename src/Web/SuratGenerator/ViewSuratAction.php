<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\UploadPath;

final class ViewSuratAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface
    {
        $suratId = (int)($currentRoute->getArgument('id', '0') ?: $request->getAttribute('id') ?: 0);
        $namaFileTarget = $request->getQueryParams()['file'] ?? '';

        if ($suratId === 0 || empty($namaFileTarget)) {
            return $this->errorResponse("ID Surat atau Nama File tidak valid.");
        }

        // Ambil data surat
        $surat = $db->createCommand("SELECT * FROM surat_generated WHERE id = :id", [':id' => $suratId])->queryOne();
        if (!$surat) {
            return $this->errorResponse("Data surat tidak ditemukan di database.");
        }

        // Ambil data mailing & instansi
        $mailing = $db->createCommand(
            "SELECT m.*, jp.jenis_pengajuan, jp.kantor, jp.output_path 
             FROM surat_mailing m 
             LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
             WHERE m.id = :mid",
            [':mid' => $surat['mailing_id']]
        )->queryOne();

        if (!$mailing) {
            return $this->errorResponse("Data mailing tidak ditemukan.");
        }

        @session_start();
        $instansiId = $mailing['instansi_id'] ?? ($_SESSION['instansi_id'] ?? null);
        $instansi = null;
        if ($instansiId) {
            $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $instansiId])->queryOne();
        }

        // Resolusi Path Persis seperti DownloadSuratAction
        $basePath = '';
        if ($instansi && !empty($instansi['path_folder'])) {
            $basePath = rtrim(str_replace('\\', '/', $instansi['path_folder']), '/');
        } else {
            $basePath = dirname(__DIR__, 3) . '/public/uploads';
        }

        $tanggalSurat = $mailing['tanggal_surat'] ?? date('Y-m-d');
        $tahunItas = date('Y', strtotime($tanggalSurat));
        $bulanItas = date('m', strtotime($tanggalSurat));

        $firstSantri = $db->createCommand(
            "SELECT i.exp_itas 
             FROM surat_mailing_santri ms
             LEFT JOIN mtb_itas i ON ms.kds = i.kds AND i.aktif = 1
             WHERE ms.mailing_id = :mid ORDER BY ms.id ASC LIMIT 1",
            [':mid' => $mailing['id']]
        )->queryOne();

        if ($firstSantri && !empty($firstSantri['exp_itas']) && $firstSantri['exp_itas'] !== '-') {
            $tahunItas = date('Y', strtotime($firstSantri['exp_itas']));
            $bulanItas = date('m', strtotime($firstSantri['exp_itas']));
        }

        $safeJenis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $mailing['jenis_pengajuan'] ?? 'Umum');
        
        $tipeLabel = ['SP' => 'Surat_Permohonan', 'SK' => 'Surat_Keterangan', 'SJ' => 'Surat_Jaminan', 'ST' => 'Surat_Tugas'];
        $safeTipeSurat = $tipeLabel[$surat['tipe_surat']] ?? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $surat['tipe_surat']);

        // Resolve Surat_Menyurat base dari path_folder instansi
        $instansiBase = UploadPath::getBase($db, $instansiId ? (int)$instansiId : null);
        $publicSuratDir = $instansiBase !== null ? $instansiBase . '/Surat_Menyurat' : dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';
        $saveDir = '';

        if (!empty($mailing['output_path'])) {
            $userPath = str_replace('\\', '/', trim($mailing['output_path']));
            $baseUserPath = '';
            if (preg_match('/^[a-zA-Z]:/', $userPath)) {
                $baseUserPath = rtrim($userPath, '/');
                $saveDir = $baseUserPath . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
            } else {
                $baseUserPath = rtrim($basePath . '/' . ltrim($userPath, '/'), '/');
                $saveDir = $baseUserPath . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
            }
            if (!is_dir($baseUserPath)) {
                $saveDir = $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
            }
        } else {
            $saveDir = $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
        }

        $isSekaligus = (($mailing['mode'] ?? '') === 'sekaligus');
        if ($isSekaligus) {
            $saveDir = rtrim($saveDir, '/') . '/Sekaligus/';
        }

        $filePath = rtrim($saveDir, '/') . '/' . $namaFileTarget;

        if (!file_exists($filePath)) {
            // Coba urutan fallback jika file tidak langsung ketemu
            $possiblePaths = [
                // 1. Tanpa /Sekaligus/ di saveDir utama
                rtrim(str_replace('/Sekaligus/', '/', $saveDir), '/') . '/' . $namaFileTarget,
                // 2. Di publicSuratDir default dengan /Sekaligus/
                $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/Sekaligus/' . $namaFileTarget,
                // 3. Di publicSuratDir default tanpa /Sekaligus/
                $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/' . $namaFileTarget,
            ];

            $found = false;
            foreach ($possiblePaths as $p) {
                if (file_exists($p)) {
                    $filePath = $p;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return $this->errorResponse("File surat tidak ditemukan secara fisik di server: " . $namaFileTarget . "<br/><br/>Path yang dicari: <br/><code>" . htmlspecialchars($filePath) . "</code>");
            }
        }

        $content = file_get_contents($filePath);
        $response = new \HttpSoft\Message\Response(200);
        $response->getBody()->write($content);
        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'inline; filename="' . $namaFileTarget . '"')
            ->withHeader('Cache-Control', 'no-cache');
    }

    private function errorResponse(string $message): ResponseInterface
    {
        $html = "<!DOCTYPE html>
        <html>
        <head>
            <title>File Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f8d7da; color: #721c24; padding: 40px; text-align: center; }
                .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
            </style>
        </head>
        <body>
            <div class='box'>
                <h3 style='margin-top:0'>Opps! Terjadi Kesalahan.</h3>
                <p>{$message}</p>
                <button onclick='window.close()' style='padding:8px 16px; margin-top:20px; cursor:pointer;'>Tutup Halaman</button>
            </div>
        </body>
        </html>";
        $response = new \HttpSoft\Message\Response(404);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}