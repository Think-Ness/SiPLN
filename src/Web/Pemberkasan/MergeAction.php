<?php
declare(strict_types=1);

namespace App\Web\Pemberkasan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use HttpSoft\Message\Response;
use setasign\Fpdi\Fpdi;

final class MergeAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $berkasIds = $body['berkas_ids'] ?? [];

        if (empty($berkasIds)) {
            $response = new Response(400);
            $response->getBody()->write('Tidak ada berkas yang dipilih.');
            return $response;
        }

        $dbIds = [];
        $fotoKds = [];
        $pasporKds = [];
        $itasKds = [];
        $itasIds = [];
        $dbIdsVirtual = [];
        $dbIdsSurat = [];
        
        foreach ($berkasIds as $id) {
            if (is_numeric($id)) {
                $dbIds[] = (int)$id;
            } elseif (str_starts_with($id, 'foto_')) {
                $fotoKds[] = (int)str_replace('foto_', '', $id);
            } elseif (str_starts_with($id, 'paspor_')) {
                $pasporKds[] = (int)str_replace('paspor_', '', $id);
            } elseif (str_starts_with($id, 'itas_doc_')) {
                $itasIds[] = (int)str_replace('itas_doc_', '', $id);
            } elseif (str_starts_with($id, 'itas_')) {
                $itasKds[] = (int)str_replace('itas_', '', $id);
            } elseif (str_starts_with($id, 'file_')) {
                // To support file_ we need the actual path, but since we only get the ID, we'll scan the base dir
                // This is a bit hacky but it works since file_ has the kds and md5
                $parts = explode('_', $id);
                if (count($parts) >= 3) {
                    $kds = (int)$parts[1];
                    $md5 = $parts[2];
                    $dbIdsVirtual[] = ['kds' => $kds, 'md5' => $md5];
                }
            } elseif (str_starts_with($id, 'surat_')) {
                $parts = explode('_', $id);
                if (count($parts) >= 3) {
                    $kds = (int)$parts[1];
                    $md5 = $parts[2];
                    $dbIdsSurat[] = ['kds' => $kds, 'md5' => $md5];
                }
            }
        }

        $berkas = [];

        // Ambil data dari mtb_berkas_penting
        if (!empty($dbIds)) {
            $inList = implode(",", $dbIds);
            
            // Pisahkan mana dokumen Instansi (Global)
            $instansiCheck = $db->createCommand("SELECT id FROM mtb_berkas_penting WHERE id IN ($inList) AND (is_public = 1 OR kode IN (SELECT id FROM master_instansi))")->queryColumn();
            $dbIdsInstansi = array_map('intval', $instansiCheck);
            $dbIdsSantri = array_diff($dbIds, $dbIdsInstansi);
            
            if (!empty($dbIdsSantri)) {
                $inSantri = implode(",", $dbIdsSantri);
                $rows = $db->createCommand("SELECT b.path_file, b.nama_berkas, b.nama_berkas as nama_unik, s.kds, s.nama as nama_santri FROM mtb_berkas_penting b JOIN master_santri s ON b.kode = s.kode WHERE b.id IN ($inSantri)")->queryAll();
                $berkas = array_merge($berkas, $rows);
            }
            
            if (!empty($dbIdsInstansi)) {
                $inInstansi = implode(",", $dbIdsInstansi);
                $instansiRows = $db->createCommand("SELECT path_file, nama_berkas, nama_berkas as nama_unik FROM mtb_berkas_penting WHERE id IN ($inInstansi)")->queryAll();
                
                $santriKds = $body['santri_kds'] ?? [];
                $mergeMode = $body['merge_mode'] ?? 'sekaligus';
                
                if ($mergeMode === 'sekaligus') {
                    foreach ($instansiRows as $ir) {
                        $berkas[] = [
                            'path_file' => $ir['path_file'],
                            'nama_berkas' => $ir['nama_berkas'],
                            'nama_unik' => $ir['nama_unik'],
                            'kds' => 'global',
                            'nama_santri' => 'Global Document'
                        ];
                    }
                } else {
                    if (!empty($santriKds)) {
                        $inKds = implode(",", array_map('intval', $santriKds));
                        $santriInfoList = $db->createCommand("SELECT kds, nama FROM master_santri WHERE kds IN ($inKds)")->queryAll();
                        $santriDict = [];
                        foreach ($santriInfoList as $s) {
                            $santriDict[$s['kds']] = $s['nama'];
                        }
                        
                        foreach ($santriKds as $kds) {
                            foreach ($instansiRows as $ir) {
                                $berkas[] = [
                                    'path_file' => $ir['path_file'],
                                    'nama_berkas' => $ir['nama_berkas'],
                                    'nama_unik' => $ir['nama_unik'],
                                    'kds' => $kds,
                                    'nama_santri' => $santriDict[$kds] ?? 'Unknown'
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        // Ambil Foto
        if (!empty($fotoKds)) {
            $inList = implode(",", $fotoKds);
            $rows = $db->createCommand("SELECT path_foto as path_file, 'Pas Foto' as nama_berkas, 'Pas Foto' as nama_unik, kds, nama as nama_santri FROM master_santri WHERE kds IN ($inList) AND path_foto IS NOT NULL")->queryAll();
            $berkas = array_merge($berkas, $rows);
        }

        // Ambil Paspor
        if (!empty($pasporKds)) {
            $inList = implode(",", $pasporKds);
            $rows = $db->createCommand("SELECT p.path_file, 'Scan Paspor' as nama_berkas, 'Scan Paspor' as nama_unik, s.kds, s.nama as nama_santri FROM mtb_paspor p JOIN master_santri s ON p.kds = s.kds WHERE p.kds IN ($inList) AND p.path_file IS NOT NULL")->queryAll();
            $berkas = array_merge($berkas, $rows);
        }

        // Ambil ITAS (Fallback lama menggunakan KDS)
        if (!empty($itasKds)) {
            $inList = implode(",", $itasKds);
            $rows = $db->createCommand("SELECT i.path_file, 'Scan ITAS' as nama_berkas, 'Scan ITAS' as nama_unik, s.kds, s.nama as nama_santri FROM mtb_itas i JOIN master_santri s ON i.kds = s.kds WHERE i.kds IN ($inList) AND i.path_file IS NOT NULL")->queryAll();
            $berkas = array_merge($berkas, $rows);
        }

        // Ambil ITAS (Sistem Baru menggunakan ID spesifik)
        if (!empty($itasIds)) {
            $inList = implode(",", $itasIds);
            // Label nama_berkas akan menyesuaikan dengan level ITAS yang sesungguhnya di database
            $rows = $db->createCommand("SELECT i.path_file, CONCAT('Scan ITAS Lvl ', i.level_itas) as nama_berkas, 'Riwayat ITAS' as nama_unik, s.kds, s.nama as nama_santri FROM mtb_itas i JOIN master_santri s ON i.kds = s.kds WHERE i.id IN ($inList) AND i.path_file IS NOT NULL")->queryAll();
            $berkas = array_merge($berkas, $rows);
        }

        // Resolving dbIdsVirtual (file_ / surat_)
        if (!empty($dbIdsVirtual) || !empty($dbIdsSurat)) {
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
            $baseBerkasDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/berkas';
            
            // Resolve file_
            foreach ($dbIdsVirtual as $v) {
                $kds = $v['kds'];
                $md5 = $v['md5'];
                $s = $db->createCommand("SELECT nama FROM master_santri WHERE kds = :kds")->bindValue(':kds', $kds)->queryOne();
                if ($s) {
                    $santriFolder = @glob($baseBerkasDir . '/berkas/*' . str_replace(' ', '_', $s['nama']) . '*');
                    if (empty($santriFolder)) {
                        $santriFolder = @glob($baseBerkasDir . '/*' . str_replace(' ', '_', $s['nama']) . '*');
                    }
                    if (!empty($santriFolder) && is_dir($santriFolder[0])) {
                        $allFiles = @glob($santriFolder[0] . '/*.*') ?: [];
                        foreach ($allFiles as $f) {
                            if (md5(basename($f)) === $md5) {
                                $basename = basename($f);
                                $docType = pathinfo($basename, PATHINFO_FILENAME);
                                $docType = ucwords(str_replace('_', ' ', $docType));
                                if (str_starts_with($basename, 'Surat_Permohonan')) $docType = "Surat Permohonan";
                                elseif (str_starts_with($basename, 'Surat_Jaminan')) $docType = "Surat Jaminan";
                                elseif (str_starts_with($basename, 'Surat_Keterangan')) $docType = "Surat Keterangan";
                                elseif (str_starts_with($basename, 'Surat_Tugas')) $docType = "Surat Tugas";
                                
                                $namaUnik = $docType . ' (Dokumen Tambahan)';
                                $berkas[] = ['path_file' => '/serve.php?path=' . urlencode($f), 'nama_berkas' => $docType, 'nama_unik' => $namaUnik, 'kds' => $kds, 'nama_santri' => $s['nama']];
                                break;
                            }
                        }
                    }
                }
            }

            // Resolve surat_
            if (!empty($dbIdsSurat)) {
                $exportDataDir = $baseBerkasDir . '/Export Data';
                $suratMenyuratDir = $baseBerkasDir . '/Surat_Menyurat';
                $allGeneratedFiles = [];
                
                $scanPdfs = function($dir) use (&$scanPdfs, &$allGeneratedFiles) {
                    if (!is_dir($dir)) return;
                    try {
                        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS));
                        foreach ($iterator as $file) {
                            if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
                                $allGeneratedFiles[] = $file->getPathname();
                            }
                        }
                    } catch (\Exception $e) {}
                };
                
                $scanPdfs($exportDataDir);
                if (is_dir($suratMenyuratDir)) {
                    $kantors = @scandir($suratMenyuratDir);
                    if ($kantors) {
                        foreach ($kantors as $k) {
                            if ($k === '.' || $k === '..') continue;
                            $outDir = $suratMenyuratDir . DIRECTORY_SEPARATOR . $k . DIRECTORY_SEPARATOR . 'Output';
                            $scanPdfs($outDir);
                        }
                    }
                }

                // Scan custom output paths
                try {
                    $jenisPengajuans = $db->createCommand("SELECT * FROM surat_jenis_pengajuan")->queryAll();
                    foreach ($jenisPengajuans as $jp) {
                        if (!empty($jp['output_path'])) {
                            $userPath = str_replace('\\', '/', trim($jp['output_path']));
                            $safeJenis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $jp['jenis_pengajuan']);
                            if (preg_match('/^[a-zA-Z]:/', $userPath)) {
                                $customDir = rtrim($userPath, '/') . '/' . $safeJenis;
                            } else {
                                $customDir = $baseBerkasDir . '/' . ltrim($userPath, '/') . '/' . $safeJenis;
                            }
                            if (is_dir($customDir)) {
                                $scanPdfs($customDir);
                            }
                        }
                    }
                } catch (\Exception $e) {}

                // Match surat_ IDs
                foreach ($dbIdsSurat as $v) {
                    $kds = $v['kds'];
                    $md5 = $v['md5'];
                    $s = $db->createCommand("SELECT nama FROM master_santri WHERE kds = :kds")->bindValue(':kds', $kds)->queryOne();
                    $namaSantri = $s ? $s['nama'] : 'Unknown';
                    
                    foreach ($allGeneratedFiles as $f) {
                        $normalizedPath = str_replace('\\', '/', $f);
                        if (md5($normalizedPath) === $md5) {
                            $docType = "Surat Generated";
                            $basename = basename($f);
                            if (str_starts_with($basename, 'Surat_Permohonan')) $docType = "Surat Permohonan";
                            elseif (str_starts_with($basename, 'Surat_Jaminan')) $docType = "Surat Jaminan";
                            elseif (str_starts_with($basename, 'Surat_Keterangan')) $docType = "Surat Keterangan";
                            elseif (str_starts_with($basename, 'Surat_Tugas')) $docType = "Surat Tugas";
                            else {
                                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', basename($f));
                                $parts = explode('_', $basename);
                                if (count($parts) > 1) {
                                    $docType = ucwords(str_replace('_', ' ', $parts[0]));
                                }
                            }
                            $safeJenisPath = basename(dirname(dirname(dirname(dirname($f)))));
                            $kategori = 'Dokumen Hasil Export';
                            if (!empty($safeJenisPath) && !in_array(strtolower($safeJenisPath), ['export data', 'output', 'surat_menyurat', 'berkas'])) {
                                $kategori = ucwords(str_replace('_', ' ', $safeJenisPath));
                            }
                            $namaUnik = $docType . ' (' . $kategori . ')';

                            $berkas[] = ['path_file' => '/serve.php?path=' . urlencode(str_replace('\\', '/', $f)), 'nama_berkas' => $docType, 'nama_unik' => $namaUnik, 'kds' => $kds, 'nama_santri' => $namaSantri];
                            break;
                        }
                    }
                }
            }
        }

        if (empty($berkas)) {
            $response = new Response(404);
            $response->getBody()->write('Berkas tidak ditemukan.');
            return $response;
        }

        try {
            $actionBody = $request->getParsedBody();
            $mergeMode = $actionBody['merge_mode'] ?? 'sekaligus';
            
            // Sort berkas berdasarkan document_order
            $documentOrderJson = $actionBody['document_order'] ?? '[]';
            $documentOrder = json_decode($documentOrderJson, true);

            if (!empty($documentOrder) && is_array($documentOrder)) {
                usort($berkas, function($a, $b) use ($documentOrder) {
                    $posA = array_search($a['nama_unik'] ?? $a['nama_berkas'], $documentOrder);
                    $posB = array_search($b['nama_unik'] ?? $b['nama_berkas'], $documentOrder);
                    if ($posA === false) $posA = 999;
                    if ($posB === false) $posB = 999;
                    
                    if ($posA === $posB) {
                        return ($a['kds'] ?? 0) <=> ($b['kds'] ?? 0);
                    }
                    return $posA <=> $posB;
                });
            }

            $buildPdfContent = function($berkasList) {
                $pdf = new Fpdi();
                $pdf->SetAutoPageBreak(false);
                $tempFiles = [];

                foreach ($berkasList as $b) {
                    $pathUrl = $b['path_file'];
                    if (empty($pathUrl)) continue;

                    $physicalPath = '';
                    if (str_starts_with($pathUrl, '/serve.php') || str_starts_with($pathUrl, 'serve.php')) {
                        $parsed = parse_url($pathUrl);
                        if (isset($parsed['query'])) {
                            parse_str($parsed['query'], $queryData);
                            if (isset($queryData['path'])) {
                                $physicalPath = $queryData['path'];
                            }
                        }
                    } else {
                        $physicalPath = $pathUrl;
                    }
                    if (empty($physicalPath)) continue;

                    if (!file_exists($physicalPath)) continue;

                    $ext = strtolower(pathinfo($physicalPath, PATHINFO_EXTENSION));

                    if ($ext === 'pdf') {
                        $f = fopen($physicalPath, 'r');
                        $firstLine = fgets($f);
                        fclose($f);
                        $needsFallback = false;
                        
                        if (preg_match('/%PDF-1\.[5-9]/', $firstLine) || preg_match('/%PDF-[2-9]/', $firstLine)) {
                            $needsFallback = true;
                        }

                        try {
                            if ($needsFallback) throw new \Exception("PDF version is higher than 1.4, forcing fallback");
                            $pageCount = $pdf->setSourceFile($physicalPath);
                        } catch (\Exception $e) {
                            $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_fix_');
                            $cmd = 'pdftk ' . escapeshellarg($physicalPath) . ' output ' . escapeshellarg($tempPdf) . ' uncompress 2>&1';
                            exec($cmd, $output, $returnVar);
                            
                            if ($returnVar === 0 && file_exists($tempPdf) && filesize($tempPdf) > 0) {
                                $pageCount = $pdf->setSourceFile($tempPdf);
                            } else {
                                throw new \Exception("Gagal memproses file PDF ($physicalPath): " . $e->getMessage() . " | Fallback error: " . implode(" ", $output));
                            }
                        }

                        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                            $templateId = $pdf->importPage($pageNo);
                            $size = $pdf->getTemplateSize($templateId);
                            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdf->useTemplate($templateId);
                        }
                        
                        if (isset($tempPdf) && file_exists($tempPdf)) {
                            $tempFiles[] = $tempPdf;
                            unset($tempPdf);
                        }
                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $pdf->AddPage('P', 'A4');
                        $pdf->Image($physicalPath, 10, 10, 190);
                    }
                }

                $pdfContent = $pdf->Output('S');
                foreach ($tempFiles as $tmp) {
                    if (file_exists($tmp)) @unlink($tmp);
                }
                return $pdfContent;
            };

            if ($mergeMode === 'individual') {
                $berkasByKds = [];
                foreach ($berkas as $b) {
                    $kds = $b['kds'] ?? '0';
                    $berkasByKds[$kds][] = $b;
                }

                $zipFile = tempnam(sys_get_temp_dir(), 'zip_');
                $zip = new \ZipArchive();
                if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
                    throw new \Exception("Gagal membuat file ZIP.");
                }

                foreach ($berkasByKds as $kds => $santriBerkas) {
                    $namaSantri = $santriBerkas[0]['nama_santri'] ?? 'Santri_' . $kds;
                    $safeNama = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $namaSantri);
                    
                    $pdfContent = $buildPdfContent($santriBerkas);
                    $zip->addFromString("Berkas_{$safeNama}.pdf", $pdfContent);
                }

                $zip->close();

                $response = new Response(200);
                $response = $response->withHeader('Content-Type', 'application/zip')
                                     ->withHeader('Content-Disposition', 'attachment; filename="Berkas_Santri_Individual.zip"');
                
                $response->getBody()->write(file_get_contents($zipFile));
                @unlink($zipFile);
                return $response;

            } elseif ($mergeMode === 'save_storage') {
                $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
                $baseBerkasDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/berkas';
                
                $saveDir = $baseBerkasDir . '/Hasil_Merge/' . date('Y-m-d_H-i-s') . '/';
                if (!is_dir($saveDir)) {
                    @mkdir($saveDir, 0777, true);
                }

                $berkasByKds = [];
                foreach ($berkas as $b) {
                    $kds = $b['kds'] ?? '0';
                    $berkasByKds[$kds][] = $b;
                }

                foreach ($berkasByKds as $kds => $santriBerkas) {
                    $namaSantri = $santriBerkas[0]['nama_santri'] ?? 'Santri_' . $kds;
                    $safeNama = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $namaSantri);
                    
                    $pdfContent = $buildPdfContent($santriBerkas);
                    file_put_contents($saveDir . "Gabungan_Berkas_{$safeNama}.pdf", $pdfContent);
                }

                // Return HTML success response
                $safeDirJs = json_encode($saveDir);
                $apiUrl = API_URL;
                $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Berhasil Disimpan</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
        .success-icon { color: #198754; font-size: 64px; margin-bottom: 20px; line-height: 1; }
        h2 { margin: 0 0 10px; color: #212529; }
        p { color: #6c757d; margin-bottom: 20px; line-height: 1.5; }
        .path-box { background: #e9ecef; padding: 12px; border-radius: 6px; font-family: monospace; word-break: break-all; margin-bottom: 24px; color: #495057; border: 1px solid #ced4da; text-align: left; font-size: 14px;}
        .btn { display: inline-block; padding: 10px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.2s; }
        .btn:hover { background: #0b5ed7; }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-icon">&#10004;</div>
        <h2>Berhasil Disimpan!</h2>
        <p>File gabungan PDF individual per santri telah berhasil dibuat dan disimpan langsung ke storage server instansi Anda pada folder:</p>
        <div class="path-box">{$saveDir}</div>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="bukaFolder()" class="btn" style="background: #198754; border: none; cursor: pointer;">Buka Folder</button>
            <a href="javascript:window.close();" class="btn">Tutup Tab Ini</a>
        </div>
    </div>
    
    <script>
        function bukaFolder() {
            var path = {$safeDirJs};
            fetch('{$apiUrl}/api/pemberkasan/open-folder?path=' + encodeURIComponent(path))
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error("Invalid JSON: " + text.substring(0, 100));
                    }
                })
                .then(data => {
                    if(!data.success) alert('Gagal membuka folder. Pastikan folder tersebut belum dihapus.');
                }).catch(e => {
                    console.error(e);
                    alert('Gagal menghubungi server: ' + e.message);
                });
        }
    </script>
</body>
</html>
HTML;

                $response = new Response(200);
                $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
                $response->getBody()->write($html);
                return $response;

            } else {
                $pdfContent = $buildPdfContent($berkas);
                
                $actionType = $actionBody['action'] ?? 'print';
                $disposition = ($actionType === 'merge') ? 'attachment' : 'inline';

                $response = new Response(200);
                $response = $response->withHeader('Content-Type', 'application/pdf')
                                     ->withHeader('Content-Disposition', $disposition . '; filename="Gabungan_Berkas_Santri.pdf"');
                $response->getBody()->write($pdfContent);
                
                return $response;
            }

        } catch (\Exception $e) {
            $response = new Response(500);
            $response->getBody()->write('Terjadi kesalahan saat menggabungkan berkas: ' . $e->getMessage());
            return $response;
        }
    }
}
