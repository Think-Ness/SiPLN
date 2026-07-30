<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use ZipArchive;

final class DownloadSuratAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface
    {
        $mailingId = (int)$currentRoute->getArgument('id', '0');
        $tipeSurat = $request->getQueryParams()['tipe'] ?? '';
        $withLampiran = (int)($request->getQueryParams()['with_lampiran'] ?? 1);

        // Load mailing first (needed to determine mode for template selection)
        $mailing = $db->createCommand("SELECT * FROM surat_mailing WHERE id = :id", [':id' => $mailingId])->queryOne();
        if (!$mailing) {
            return $this->errorResponse("Mailing ID #{$mailingId} tidak ditemukan.", 404);
        }

        // Map template filenames based on mode + tipe surat (match actual files in folder)
        $isSekaligus = ($mailing['mode'] === 'sekaligus');
        $suffix = $isSekaligus ? '_Banyak_Orang' : '_Satu_Orang';

        $templateFiles = [
            'SP' => 'Surat_Permohonan' . $suffix . '.docx',
            'SK' => 'Surat_Keterangan' . $suffix . '.docx',
            'SJ' => 'Surat_Jaminan' . $suffix . '.docx',
            'ST' => 'Surat_Tugas.docx'  // Surat Tugas always single
        ];

        if (isset($templateFiles[$tipeSurat])) {
            $templateFileName = $templateFiles[$tipeSurat];
        } else {
            // Dynamic Template from Kelola Template
            // $tipeSurat is the core_name, e.g. "Surat_Perizinan"
            if (empty($tipeSurat)) {
                return $this->errorResponse("Parameter tipe surat tidak valid atau tidak ada. Contoh: ?tipe=SP atau ?tipe=Surat_Perizinan", 400);
            }
            $templateFileName = $tipeSurat . $suffix . '.docx';
        }

        // Load jenis pengajuan
        $jenisPengajuan = $db->createCommand(
            "SELECT * FROM surat_jenis_pengajuan WHERE id = :id",
            [':id' => $mailing['jenis_pengajuan_id']]
        )->queryOne();
        if (!$jenisPengajuan) {
            return $this->errorResponse("Jenis Pengajuan tidak ditemukan.", 404);
        }

        // Tentukan instansi_id: dari mailing, atau fallback ke session user
        @session_start();
        $instansiId = $mailing['instansi_id'] ?? null;
        if (empty($instansiId)) {
            $instansiId = $_SESSION['instansi_id'] ?? null;
        }
        if (empty($instansiId)) {
            return $this->errorResponse("Instansi tidak dapat ditentukan. Pastikan mailing memiliki instansi atau Anda sudah login dengan akun instansi.", 400);
        }

        // Load instansi
        $instansi = $db->createCommand(
            "SELECT * FROM master_instansi WHERE kode = :kode",
            [':kode' => $instansiId]
        )->queryOne();
        if (!$instansi || empty($instansi['path_folder'])) {
            return $this->errorResponse("Path Folder Instansi belum diatur. Silakan atur di menu Profil Instansi.", 400);
        }

        $basePath = rtrim(str_replace('\\', '/', $instansi['path_folder']), '/');
        $kantor = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $jenisPengajuan['kantor']);
        $publicSuratDir = dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';

        // Locate template file — use Windows-style backslash path for COM
        $templatePathSlash = $publicSuratDir . '/' . $kantor . '/' . $templateFileName;
        $templatePath = str_replace('/', '\\', $templatePathSlash); // COM requires backslash
        
        if (!file_exists($templatePathSlash)) {
            return $this->errorResponse(
                "File template tidak ditemukan: {$templatePathSlash}\n\nPastikan file '{$templateFileName}' ada di folder:\n" . str_replace('/', '\\', $publicSuratDir) . "\\{$kantor}\\"
            );
        }

        // Tentukan save directory berdasarkan Tahun dan Bulan ITAS
        $tanggalSurat = $mailing['tanggal_surat'] ?? date('Y-m-d');
        
        $tahunItas = date('Y', strtotime($tanggalSurat));
        $bulanItas = date('m', strtotime($tanggalSurat));
        
        // Cek data santri pertama untuk menentukan Tahun & Bulan ITAS
        $firstSantri = $db->createCommand(
            "SELECT i.exp_itas 
             FROM surat_mailing_santri ms
             LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON ms.kds = i.kds
             WHERE ms.mailing_id = :mid ORDER BY ms.id ASC LIMIT 1",
            [':mid' => $mailingId]
        )->queryOne();
        
        if ($firstSantri && !empty($firstSantri['exp_itas']) && $firstSantri['exp_itas'] !== '-') {
            $tahunItas = date('Y', strtotime($firstSantri['exp_itas']));
            $bulanItas = date('m', strtotime($firstSantri['exp_itas']));
        }
        
        $safeJenis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $jenisPengajuan['jenis_pengajuan']);
        $tipeLabel = ['SP' => 'Surat_Permohonan', 'SK' => 'Surat_Keterangan', 'SJ' => 'Surat_Jaminan', 'ST' => 'Surat_Tugas'];
        
        if (isset($tipeLabel[$tipeSurat])) {
            $safeTipeSurat = $tipeLabel[$tipeSurat];
        } else {
            // Dynamic template safe name
            $safeTipeSurat = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tipeSurat);
        }

        $saveDir = '';
        if (!empty($jenisPengajuan['output_path'])) {
            $userPath = str_replace('\\', '/', trim($jenisPengajuan['output_path']));
            
            $baseUserPath = '';
            if (preg_match('/^[a-zA-Z]:/', $userPath)) {
                $baseUserPath = rtrim($userPath, '/');
                $saveDir = $baseUserPath . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
            } else {
                $baseUserPath = rtrim($basePath . '/' . ltrim($userPath, '/'), '/');
                $saveDir = $baseUserPath . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
            }

            // Fallback jika folder utama dari path konfigurasi tidak ada
            if (!is_dir($baseUserPath)) {
                $saveDir = $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
            }
        } else {
            // Default auto-path
            $saveDir = $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/';
        }

        if (!is_dir($saveDir)) {
            @mkdir($saveDir, 0777, true);
        }

        // Load santri list
        $santris = $db->createCommand(
            "SELECT s.*, 
                    COALESCE(p.no_paspor, '-') as no_paspor, 
                    COALESCE(p.exp_paspor, '-') as exp_paspor,
                    COALESCE(p.tanggal_dikeluarkan, '-') as tanggal_dikeluarkan,
                    COALESCE(p.tempat_dikeluarkan, '-') as tempat_dikeluarkan,
                    COALESCE(i.exp_itas, '-') as exp_itas
             FROM surat_mailing_santri ms
             JOIN master_santri s ON ms.kds = s.kds
             LEFT JOIN (SELECT kds, no_paspor, exp_paspor, tanggal_dikeluarkan, tempat_dikeluarkan FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
             LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
             WHERE ms.mailing_id = :mid ORDER BY ms.id ASC",
            [':mid' => $mailingId]
        )->queryAll();

        if (empty($santris)) {
            return $this->errorResponse("Tidak ada santri dalam mailing ini.", 400);
        }

        // Load nomor surat and dynamic data
        $suratData = $db->createCommand(
            "SELECT nomor_surat, json_dynamic_data FROM surat_generated WHERE mailing_id = :mid AND tipe_surat = :tipe",
            [':mid' => $mailingId, ':tipe' => $tipeSurat]
        )->queryOne();
        $nomorSurat = $suratData ? $suratData['nomor_surat'] : '---/---/---/---/2026';
        $jsonDynamicData = ($suratData && !empty($suratData['json_dynamic_data'])) ? json_decode($suratData['json_dynamic_data'], true) : null;

        // Load user data for staf bookmarks
        $userStaf = null;
        if ($tipeSurat === 'Surat_Tugas') {
            $petugasId = (int)($mailing['kepada'] ?? 0);
            if ($petugasId) {
                $userStaf = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $petugasId])->queryOne();
            } else {
                $userStaf = $db->createCommand("SELECT * FROM users WHERE id = :id", [':id' => $_SESSION['user_id'] ?? 0])->queryOne();
            }
        }

        // COM operations can take a while for 50+ santris, prevent PHP from timing out
        set_time_limit(300);
        
        $progressId = $request->getQueryParams()['progress_id'] ?? null;
        $progressPath = $progressId ? __DIR__ . '/../../../public/assets/progress/progress_' . preg_replace('/[^a-zA-Z0-9_]/', '', $progressId) . '.json' : null;
        
        $logProgress = function($msg) use ($progressPath) {
            if ($progressPath) {
                $dir = dirname($progressPath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                @file_put_contents($progressPath, json_encode(['message' => $msg]));
            }
        };

        // Clean up orphan word processes before starting
        @shell_exec("taskkill /F /IM WINWORD.EXE > NUL 2>&1");
        
        if ($logProgress) $logProgress("Menginisiasi Microsoft Word...");
        
        // --- PROCESS DYNAMIC DATA WITH PHPWORD TEMPLATEPROCESSOR ---
        $tempFiles = [];
        if ($jsonDynamicData && is_array($jsonDynamicData)) {
            try {
                if ($logProgress) $logProgress("Memproses tabel dinamis (Data Collection)...");
                $tempPath = tempnam(sys_get_temp_dir(), 'tpl_') . '.docx';
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
                
                foreach ($jsonDynamicData as $macroName => $rows) {
                    if (empty($rows)) continue;
                    // cloneRow requires the exact macro name
                    $templateProcessor->cloneRow($macroName, count($rows));
                    
                    $rowIndex = 1;
                    foreach ($rows as $row) {
                        // Hapus macro identifier
                        $templateProcessor->setValue($macroName . '#' . $rowIndex, '');
                        // Isi data per kolom
                        foreach ($row as $colName => $val) {
                            $templateProcessor->setValue($colName . '#' . $rowIndex, htmlspecialchars((string)$val));
                        }
                        $rowIndex++;
                    }
                }
                
                $templateProcessor->saveAs($tempPath);
                $templatePath = $tempPath;
                $tempFiles[] = $tempPath; // mark for cleanup
            } catch (\Exception $e) {
                error_log("Error processing PhpWord template: " . $e->getMessage());
                // Silently fallback to COM without dynamic data if PhpWord fails
            }
        }

        // Initiate Word COM
        error_log("[".date('Y-m-d H:i:s')."] 1. Initiating COM...", 3, sys_get_temp_dir() . '/surat.log');
        if (!class_exists('COM')) {
            return $this->errorResponse("Class COM tidak ditemukan. PHP INI: " . php_ini_loaded_file() . " | PHP EXE: " . PHP_BINARY . " | SAPI: " . php_sapi_name(), 500);
        }
        try {
            $wd = new \COM("Word.Application");
            $wd->DisplayAlerts = 0; // wdAlertsNone
        } catch (\Throwable $e) {
            return $this->errorResponse("Gagal membuka Microsoft Word. Error: " . $e->getMessage(), 500);
        }
        $wd->Visible = false;
        error_log("[".date('Y-m-d H:i:s')."] 2. COM Ready.", 3, sys_get_temp_dir() . '/surat.log');

        $generatedFiles = [];

        try {
            if ($isSekaligus) {
                // Open existing template (not Add new blank doc)
                error_log("\n[".date('Y-m-d H:i:s')."] 3. Opening doc...", 3, sys_get_temp_dir() . '/surat.log');
                if ($logProgress) $logProgress("Membuka template surat...");
                $doc = $wd->Documents->Open($templatePath, false, true);
                
                $pagesBefore = $doc->ComputeStatistics(2); // 2 = wdStatisticPages
                
                $lampiranPages = 0;
                if ($withLampiran) {
                    error_log("\n[".date('Y-m-d H:i:s')."] 5. Filling sekaligus list...", 3, sys_get_temp_dir() . '/surat.log');
                    if ($logProgress) $logProgress("Mempersiapkan data " . count($santris) . " santri...");
                    $this->fillSekaligusList($doc, $santris, $logProgress, $kantor);
                    
                    $pagesAfter = $doc->ComputeStatistics(2);
                    $lampiranPages = max(1, $pagesAfter - $pagesBefore);
                }
                
                error_log("\n[".date('Y-m-d H:i:s')."] 4. Filling headers...", 3, sys_get_temp_dir() . '/surat.log');
                if ($logProgress) $logProgress("Menyesuaikan template header...");
                $this->fillHeaderBookmarks($doc, $nomorSurat, $tanggalSurat, $jenisPengajuan, $userStaf, $withLampiran ? $lampiranPages : null);

                $fileNameBase = "{$safeTipeSurat}_Sekaligus_{$safeJenis}";
                $targetPdf = str_replace('/', '\\', $saveDir . $fileNameBase . '.pdf');
                
                error_log("\n[".date('Y-m-d H:i:s')."] 6. Exporting to PDF...", 3, sys_get_temp_dir() . '/surat.log');
                if ($logProgress) $logProgress("Mengekspor ke PDF (Tahap akhir)...");
                $doc->ExportAsFixedFormat($targetPdf, 17);
                error_log("\n[".date('Y-m-d H:i:s')."] 7. Export done.", 3, sys_get_temp_dir() . '/surat.log');
                $doc->Close(0); // 0 = do not save changes to template
                
                $generatedFiles[] = ['path' => str_replace('\\', '/', $targetPdf), 'name' => $fileNameBase . '.pdf'];
            } else {
                // Perseorangan: one PDF per santri
                $totalSantri = count($santris);
                $curSantri = 1;
                foreach ($santris as $s) {
                    if ($logProgress) $logProgress("Memproses surat $curSantri dari $totalSantri...");
                    $doc = $wd->Documents->Open($templatePath, false, true);
                    $this->fillHeaderBookmarks($doc, $nomorSurat, $tanggalSurat, $jenisPengajuan, $userStaf);
                    $this->fillPersonalBookmarks($doc, $s);

                    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s['nama']);
                    $fileNameBase = "{$safeTipeSurat}_{$safeName}";
                    $targetPdf = str_replace('/', '\\', $saveDir . $fileNameBase . '.pdf');
                    
                    $doc->ExportAsFixedFormat($targetPdf, 17);
                    $doc->Close(0); // 0 = do not save changes to template
                    
                    $generatedFiles[] = ['path' => str_replace('\\', '/', $targetPdf), 'name' => $fileNameBase . '.pdf'];
                    $curSantri++;
                }
            }
        } catch (\Throwable $e) {
            if ($progressPath) @unlink($progressPath);
            if (isset($wd)) {
                try { $wd->Quit(); } catch (\Throwable $ex) {}
            }
            @shell_exec("taskkill /F /IM WINWORD.EXE > NUL 2>&1");
            return $this->errorResponse("Gagal generate dokumen: " . $e->getMessage(), 500);
        }

        if ($progressPath) @unlink($progressPath);
        if (isset($wd)) {
            try { $wd->Quit(); } catch (\Throwable $ex) {}
        }
        @shell_exec("taskkill /F /IM WINWORD.EXE > NUL 2>&1");


        // Serve response
        if (count($generatedFiles) === 1) {
            $file = $generatedFiles[0];
            if (!file_exists($file['path'])) {
                return $this->errorResponse("File PDF gagal dibuat di: " . $file['path'], 500);
            }
            $content = file_get_contents($file['path']);
            $response = new \HttpSoft\Message\Response(200);
            $response->getBody()->write($content);
            return $response
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $file['name'] . '"')
                ->withHeader('Cache-Control', 'no-cache');
        } else {
            $zipPath = tempnam(sys_get_temp_dir(), 'surat_zip') . '.zip';
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE);
            foreach ($generatedFiles as $f) {
                if (file_exists($f['path'])) {
                    $zip->addFile($f['path'], $f['name']);
                }
            }
            $zip->close();

            $content = file_get_contents($zipPath);
            @unlink($zipPath);

            $response = new \HttpSoft\Message\Response(200);
            $response->getBody()->write($content);
            return $response
                ->withHeader('Content-Type', 'application/zip')
                ->withHeader('Content-Disposition', 'attachment; filename="Surat_Perseorangan_' . date('Ymd_His') . '.zip"')
                ->withHeader('Cache-Control', 'no-cache');
        }
    }

    /**
     * Returns a JSON error response (HTTP 200 so framework middleware does NOT
     * show its own 404 page — the JS fetch() handler reads the JSON and shows SweetAlert).
     */
    private function errorResponse(string $message, int $httpStatus = 400): ResponseInterface
    {
        return \App\Shared\JsonResponse::create(['success' => false, 'message' => $message], 200);
    }

    private function fillHeaderBookmarks($doc, $nomorSurat, $tanggalSurat, $jp, $userStaf, $lampiranPages = null)
    {
        $bookmarks = $doc->Bookmarks;
        $fill = function ($name, $val) use ($bookmarks) {
            try {
                if ($bookmarks->Exists($name)) {
                    $bookmarks->Item($name)->Range->Text = (string)$val;
                }
            } catch (\Exception $e) {}
        };

        $nomorAngka = explode('/', (string)$nomorSurat)[0];
        $fill("NO", $nomorAngka);
        $fill("Tanggal_Buat", $this->formatTglIndo($tanggalSurat));
        $fill("Bln_Romawi", $this->bulanRomawi((int)date('m', strtotime($tanggalSurat))));
        $fill("Tahun_Buat", date('Y', strtotime($tanggalSurat)));
        
        if ($lampiranPages !== null) {
            $fill("JML_LAMPIRAN", $lampiranPages . " Lembar");
        }
        $fill("Kepada", $jp['kepada'] ?? '');
        $fill("Tempat", $jp['tempat'] ?? '');
        $fill("Hal", $jp['hal'] ?? '');
        $fill("Isi", $jp['isi'] ?? '');

        // Staf data (for Surat Tugas)
        if ($userStaf) {
            $fill("Staf_Nama", $userStaf['nama_lengkap'] ?? '');
            $fill("Kepala_Staf", $userStaf['nama_lengkap'] ?? '');
            $fill("Staf_TTL", $userStaf['ttl'] ?? '');
            $fill("Staf_Alamat", "Pondok Modern Darussalam Gontor");
            $fill("Staf_Pekerjaan", "Guru Kulliyyatu-l-Mu'allimin Al-Islamiyyah (KMI)");
            
            // Generic fallback
            $fill("Nama", $userStaf['nama_lengkap'] ?? '');
            $fill("TTL", $userStaf['ttl'] ?? '');
            $fill("Alamat", "Pondok Modern Darussalam Gontor");
            $fill("Pekerjaan", "Guru Kulliyyatu-l-Mu'allimin Al-Islamiyyah (KMI)");
        }
    }

    private function fillPersonalBookmarks($doc, $s)
    {
        $bookmarks = $doc->Bookmarks;
        $fill = function ($name, $val) use ($bookmarks) {
            try {
                if ($bookmarks->Exists($name)) {
                    $bookmarks->Item($name)->Range->Text = (string)$val;
                }
            } catch (\Exception $e) {}
        };

        $fill("Nama_Santri", ucwords(strtolower($s['nama'] ?? '')));
        $fill("Tempat_Lahir", ucwords(strtolower($s['tempat_lahir'] ?? '')));
        $fill("Tgl_Lahir", $this->formatTglIndo($s['tanggal_lahir'] ?? ''));
        $fill("Jenis_Kelamin", ucwords(strtolower($s['jenis_kelamin'] ?? '')));
        $fill("Kewarganegaraan", ucwords(strtolower($s['kewarganegaraan'] ?? '')));
        $fill("Negara_Asal", ucwords(strtolower($s['negara'] ?? '')));
        $fill("Alamat_Idn", "Pondok Modern Darussalam Gontor");
        $fill("No_Paspor", $s['no_paspor'] ?? '');
        $fill("Tgl_Berlaku", $this->formatTglIndo($s['exp_paspor'] ?? ''));
        $fill("Tgl_Keluar_Paspor", $this->formatTglIndo($s['tanggal_dikeluarkan'] ?? ''));
        $fill("Tempat_Keluar", ucwords(strtolower($s['tempat_dikeluarkan'] ?? '')));
        $fill("Exp_ITAS", $this->formatTglIndo($s['exp_itas'] ?? ''));
    }

    private function fillSekaligusList($doc, $santris, $logProgress = null, $kantor = '')
    {
        try {
            // Hapus bookmark Lampiran agar tidak menimpa header di halaman 1
            $bookmarks = $doc->Bookmarks;
            if ($bookmarks->Exists("Lampiran")) {
                try {
                    $bookmarks->Item("Lampiran")->Range->Text = "";
                } catch (\Exception $e) {}
            }

            // Bangun HTML tabel untuk InsertFile (1000x lebih cepat daripada ribuan COM calls)
            // Menggunakan tabel terpisah per santri agar Word tidak crash (RPC Failed) saat me-render tabel raksasa beda halaman
            $html = '<html><head><meta charset="utf-8"></head><body>';
            
            $counter = 1;
            $totalSantris = count($santris);
            $maxPerPage = 4;
            
            foreach ($santris as $s) {
                // === LOGIKA PINTAR PAGINATION ===
                $shouldBreak = false;
                if ($counter > 1) {
                    $posOnPage = ($counter - 1) % $maxPerPage; // Urutan di halaman saat ini: 0, 1, 2, 3
                    
                    if ($kantor === 'Kemenag') {
                        // Jika ini santri terakhir, dan dia menempati posisi ke-4 di halaman
                        // Paksakan pindah ke halaman baru agar TTD Kemenag tidak sendirian (orphaned)
                        if ($counter === $totalSantris && $posOnPage === 3) {
                            $shouldBreak = true;
                        } elseif ($posOnPage === 0) {
                            // Normal break untuk urutan 5, 9, 13
                            $shouldBreak = true;
                        }
                    } else {
                        // Untuk Imigrasi, murni max 4 per halaman
                        if ($posOnPage === 0) {
                            $shouldBreak = true;
                        }
                    }
                }
                
                if ($shouldBreak) {
                    $html .= '<br clear="all" style="page-break-before:always" />';
                }
                
                // Gunakan table dengan font size 11pt, format rapi persis MS Access
                $html .= '<table style="width:100%; font-family: \'Times New Roman\'; font-size:11pt; border:none;" cellpadding="0" cellspacing="0">';
                
                $fields = [
                    ['Nama', ucwords(strtolower($s['nama'] ?? ''))],
                    ['Tempat/Tanggal Lahir', ucwords(strtolower($s['tempat_lahir'] ?? '')) . ', ' . $this->formatTglIndo($s['tanggal_lahir'] ?? '')],
                    ['Jenis Kelamin', strtoupper(substr($s['jenis_kelamin'] ?? 'L', 0, 1))],
                    ['Asal Negara', ucwords(strtolower($s['negara'] ?? ''))],
                    ['Pekerjaan/Status', 'Student/Santri'],
                    ['Alamat Indonesia', 'Pondok Modern Darussalam Gontor'],
                    ['No Paspor', $s['no_paspor'] ?? ''],
                    ['Berlaku s.d', $this->formatTglIndo($s['exp_paspor'] ?? '')],
                    ['Dikeluarkan', ucwords(strtolower($s['tempat_dikeluarkan'] ?? ''))],
                    ['Tanggal', $this->formatTglIndo($s['tanggal_dikeluarkan'] ?? '')]
                ];

                foreach ($fields as $idx => $f) {
                    $html .= '<tr>';
                    $html .= '<td style="width:5%; vertical-align:top;">' . ($idx === 0 ? $counter . '.' : '') . '</td>';
                    $html .= '<td style="width:30%; vertical-align:top;">' . htmlspecialchars($f[0]) . '</td>';
                    $html .= '<td style="width:2%; vertical-align:top;">:</td>';
                    $html .= '<td style="vertical-align:top;">' . htmlspecialchars($f[1]) . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</table>';
                
                // Beri jarak 1 baris kosong antar santri jika mereka masih di halaman yang sama
                // Jika setelah ini akan ada page break, tidak usah dikasih br
                $nextPosOnPage = $counter % $maxPerPage;
                $willBreak = false;
                if ($counter !== $totalSantris) {
                    if ($kantor === 'Kemenag' && ($counter + 1) === $totalSantris && $nextPosOnPage === 3) {
                        $willBreak = true;
                    } elseif ($nextPosOnPage === 0) {
                        $willBreak = true;
                    }
                }
                
                if ($counter !== $totalSantris && !$willBreak) {
                    $html .= '<br/>';
                }
                
                $counter++;
            }
            
            // Tambahkan Tanda Tangan Khusus Kemenag di paling akhir lampiran (sebagai tabel terpisah agar formatnya sempurna)
            if ($kantor === 'Kemenag') {
                $html .= '<br/><br/>';
                $html .= '<table style="width:100%; font-family:\'Times New Roman\'; font-size:11pt; border:none;" cellpadding="0" cellspacing="0">';
                $html .= '<tr>';
                $html .= '<td style="width:50%;"></td>';
                $html .= '<td style="width:50%; text-align:center;">';
                $html .= 'a.n. Direktur Kulliyatu-l-Mu\'allimin Al-Islamiyyah (KMI)<br/>';
                $html .= 'Pondok Modern Darussalam Gontor<br/>';
                $html .= 'Ponorogo<br/><br/><br/><br/><br/>';
                $html .= '<b>KH. Masyhudi Subari, MA</b>';
                $html .= '</td>';
                $html .= '</tr>';
                $html .= '</table>';
            }
            
            $html .= '</body></html>';
            
            if ($logProgress) $logProgress("Menyisipkan tabel data ke dalam Word...");
            
            // Simpan ke temp file
            $tmpHtml = tempnam(sys_get_temp_dir(), 'lampiran') . '.html';
            file_put_contents($tmpHtml, $html);
            
            // Sisipkan file HTML ke akhir dokumen dengan aman menggunakan object Selection
            // Object Selection mensimulasikan kursor manusia sehingga 100% aman dan tidak crash jika akhir dokumen adalah tabel
            $doc->Application->Selection->EndKey(6); // 6 = wdStory (Go to absolute end of document)
            
            // Ketik Page Break (Form Feed) dan enter
            $doc->Application->Selection->TypeText("\x0C\n");
            
            // Sisipkan HTML tepat di kursor saat ini
            $doc->Application->Selection->InsertFile($tmpHtml);
            
            // Bersihkan temp file
            @unlink($tmpHtml);

        } catch (\Throwable $e) {
            error_log("\n[".date('Y-m-d H:i:s')."] FATAL IN fillSekaligusList: " . $e->getMessage() . " at line " . $e->getLine(), 3, sys_get_temp_dir() . '/surat.log');
            throw $e;
        }
    }

    private function formatTglIndo($dateStr): string
    {
        if (empty($dateStr) || $dateStr === '-') return '-';
        $time = strtotime($dateStr);
        if (!$time) return '-';
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return date('j', $time) . ' ' . $bulan[date('n', $time) - 1] . ' ' . date('Y', $time);
    }

    private function bulanRomawi(int $bulan): string
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romawi[$bulan - 1] ?? (string)$bulan;
    }
}

