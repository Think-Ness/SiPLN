<?php
declare(strict_types=1);

namespace App\Web\Pemberkasan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class CetakBerkasAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $search = $request->getQueryParams()['q'] ?? '';
        
        $params = [];
        $whereConds = [];
        if ($search !== '') {
            $whereConds[] = "(s.nama LIKE :q OR s.kds LIKE :q OR p.no_paspor LIKE :q OR s.negara LIKE :q)";
            $params[':q'] = "%$search%";
        }
        
        $where = !empty($whereConds) ? "WHERE " . implode(" AND ", $whereConds) : "";

        // Fetch Santri
        // Fetch Santri (Hanya join dengan paspor dan ITAS yang aktif untuk data utama)
        $santris = $db->createCommand(
            "SELECT s.kds, s.kode, s.nama, p.no_paspor, s.negara, s.kelas,
                    p.exp_paspor, i.exp_itas,
                    s.path_foto, p.path_file as path_paspor, i.path_file as path_itas
             FROM master_santri s
             LEFT JOIN (SELECT * FROM mtb_paspor WHERE aktif = 1) p ON s.kds = p.kds
             LEFT JOIN (SELECT * FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
             $where 
             ORDER BY s.nama ASC",
            $params
        )->queryAll();

        $kdsList = array_column($santris, 'kds');
        $berkasMap = [];
        if (!empty($kdsList)) {
            $inKds = implode(",", array_map('intval', $kdsList));
            $berkasPentingList = $db->createCommand(
                "SELECT b.id, s.kds, b.kode, b.nama_berkas, b.path_file
                 FROM mtb_berkas_penting b
                 JOIN master_santri s ON b.kode = s.kode
                 WHERE s.kds IN ($inKds)"
            )->queryAll();

            foreach ($berkasPentingList as $b) {
                $kds = $b['kds'];
                $namaBerkas = $b['nama_berkas'];
                
                // Parse physical path
                $physicalPath = '';
                $parsed = parse_url($b['path_file']);
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $queryData);
                    if (isset($queryData['path'])) {
                        $physicalPath = $queryData['path'];
                    }
                }
                
                if (empty($physicalPath) || !file_exists($physicalPath)) {
                    continue; // Skip if file does not physically exist
                }
                
                $alreadyExists = false;
                if (isset($berkasMap[$kds])) {
                    foreach ($berkasMap[$kds] as $existing) {
                        if ($existing['nama_berkas'] === $namaBerkas) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                }
                
                if (!$alreadyExists) {
                    $berkasMap[$kds][] = $b;
                }
            }

            // Tambahkan Seluruh Riwayat ITAS
            $itasHistory = $db->createCommand(
                "SELECT id, kds, no_itas, level_itas, path_file, aktif FROM mtb_itas WHERE kds IN ($inKds) ORDER BY level_itas ASC"
            )->queryAll();
            
            foreach ($itasHistory as $it) {
                if (!empty($it['path_file'])) {
                    $kds = $it['kds'];
                    $lvl = (int) $it['level_itas'];
                    
                    // Jika ini ITAS aktif, tambahkan opsi "Scan ITAS (Aktif)"
                    if ($it['aktif'] == 1) {
                        $berkasMap[$kds][] = [
                            'id' => 'itas_doc_' . $it['id'],
                            'nama_berkas' => 'Scan ITAS (Aktif)',
                            'kategori' => 'Dokumen ITAS & Paspor',
                            'nama_unik' => 'Scan ITAS (Aktif)',
                            'tahun' => null,
                            'path_file' => $it['path_file']
                        ];
                    }
                    
                    // Selalu tambahkan ke opsi kumpulan "Seluruh Riwayat ITAS"
                    $berkasMap[$kds][] = [
                        'id' => 'itas_doc_' . $it['id'],
                        'nama_berkas' => 'Seluruh Riwayat ITAS',
                        'kategori' => 'Dokumen ITAS & Paspor',
                        'nama_unik' => 'Seluruh Riwayat ITAS',
                        'tahun' => null,
                        'path_file' => $it['path_file']
                    ];
                }
            }
        }

        $instansiDocList = $db->createCommand(
            "SELECT b.id, b.nama_berkas, b.path_file
             FROM mtb_berkas_penting b
             WHERE b.is_public = 1 OR b.kode IN (SELECT id FROM master_instansi)"
        )->queryAll();

        $instansiBerkas = [];
        foreach ($instansiDocList as $b) {
            $b['kategori'] = 'Dokumen Instansi (Global)';
            $b['nama_unik'] = $b['nama_berkas'];
            $b['tahun'] = null;
            $instansiBerkas[] = $b;
        }

        // Add them to every santri's berkasMap so JS will match it!
        foreach ($kdsList as $kds) {
            foreach ($instansiBerkas as $b) {
                // Ensure physical file exists
                $physicalPath = '';
                if (str_starts_with($b['path_file'], '/serve.php') || str_starts_with($b['path_file'], 'serve.php')) {
                    $parsed = parse_url($b['path_file']);
                    if (isset($parsed['query'])) {
                        parse_str($parsed['query'], $queryData);
                        if (isset($queryData['path'])) {
                            $physicalPath = $queryData['path'];
                        }
                    }
                } else {
                    $physicalPath = $b['path_file'];
                }
                if (empty($physicalPath) || !file_exists($physicalPath)) {
                    continue;
                }
                $berkasMap[$kds][] = $b;
            }
        }

        // Base fallback directory from instansi settings
        $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
        $baseBerkasDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/berkas';
        
        $fotoDir = $baseBerkasDir . '/foto santri';
        $pasporDir = $baseBerkasDir . '/paspor';
        $itasDir = $baseBerkasDir . '/itas';

        $isValidPath = function($pathUrl) {
            if (empty($pathUrl)) return false;
            $parsed = parse_url($pathUrl);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $queryData);
                if (isset($queryData['path']) && file_exists($queryData['path'])) {
                    return true;
                }
            }
            return false;
        };

        // Scan for Generated Letters
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

        // Scan custom output paths from Kelola Jenis Pengajuan
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
        } catch (\Exception $e) {
            // Ignore DB errors if table doesn't exist
        }

        foreach ($santris as &$s) {
            $kds = $s['kds'];
            $kode = $s['kode'];
            
            // Check Foto
            if (!empty($s['path_foto']) && $isValidPath($s['path_foto'])) {
                $berkasMap[$kds][] = ['id' => 'foto_' . $kds, 'nama_berkas' => 'Pas Foto', 'path_file' => $s['path_foto']];
            } else {
                // Fallback scan
                $files = @glob($fotoDir . '/*_' . $kode . '_*.*') ?: [];
                if (empty($files)) $files = @glob($fotoDir . '/*' . str_replace(' ', '_', $s['nama']) . '*.*') ?: [];
                if (!empty($files)) {
                    $berkasMap[$kds][] = ['id' => 'foto_' . $kds, 'nama_berkas' => 'Pas Foto', 'path_file' => '/serve.php?path=' . urlencode($files[0])];
                }
            }

            // Check Paspor
            if (!empty($s['path_paspor']) && $isValidPath($s['path_paspor'])) {
                $berkasMap[$kds][] = ['id' => 'paspor_' . $kds, 'nama_berkas' => 'Scan Paspor', 'kategori' => 'Dokumen ITAS & Paspor', 'path_file' => $s['path_paspor']];
            } else {
                // Fallback scan
                $files = @glob($pasporDir . '/*_' . $kode . '_*.*') ?: [];
                if (empty($files)) $files = @glob($pasporDir . '/*' . str_replace(' ', '_', $s['nama']) . '*.*') ?: [];
                if (!empty($files)) {
                    $berkasMap[$kds][] = ['id' => 'paspor_' . $kds, 'nama_berkas' => 'Scan Paspor', 'kategori' => 'Dokumen ITAS & Paspor', 'path_file' => '/serve.php?path=' . urlencode($files[0])];
                }
            }

            // Cek apakah ITAS sudah ada di map (dari query riwayat sebelumnya)
            $hasItasDB = false;
            if (isset($berkasMap[$kds])) {
                foreach ($berkasMap[$kds] as $b) {
                    if (str_contains((string)$b['nama_berkas'], 'ITAS')) {
                        $hasItasDB = true;
                        break;
                    }
                }
            }
            
            // Jika tidak ada di DB sama sekali, coba cari di folder fallback
            if (!$hasItasDB) {
                $files = @glob($itasDir . '/*_' . $kode . '_*.*') ?: [];
                if (empty($files)) $files = @glob($itasDir . '/*' . str_replace(' ', '_', $s['nama']) . '*.*') ?: [];
                if (!empty($files)) {
                    $berkasMap[$kds][] = ['id' => 'itas_' . $kds, 'nama_berkas' => 'Scan ITAS (Aktif)', 'kategori' => 'Dokumen ITAS & Paspor', 'path_file' => '/serve.php?path=' . urlencode($files[0])];
                }
            }
            
            // Check Berkas Penting Folder Fallback
            $santriFolder = @glob($baseBerkasDir . '/berkas/*' . str_replace(' ', '_', $s['nama']) . '*');
            if (empty($santriFolder)) {
                $santriFolder = @glob($baseBerkasDir . '/*' . str_replace(' ', '_', $s['nama']) . '*');
            }
            if (!empty($santriFolder) && is_dir($santriFolder[0])) {
                $allFiles = @glob($santriFolder[0] . '/*.*') ?: [];
                foreach ($allFiles as $idx => $f) {
                    $basename = basename($f);
                    // Check if not already in DB
                    $alreadyExists = false;
                    if (isset($berkasMap[$kds])) {
                        foreach ($berkasMap[$kds] as $bm) {
                            if (str_contains((string)$bm['path_file'], urlencode($f)) || str_contains((string)$bm['path_file'], $basename)) {
                                $alreadyExists = true;
                                break;
                            }
                        }
                    }
                    if (!$alreadyExists) {
                        $virtualId = 'file_' . $kds . '_' . md5($basename);
                        $prettyName = pathinfo($basename, PATHINFO_FILENAME);
                        $prettyName = ucwords(str_replace('_', ' ', $prettyName));
                        
                        $berkasMap[$kds][] = ['id' => $virtualId, 'nama_berkas' => $prettyName, 'kategori' => 'Dokumen Tambahan', 'path_file' => '/serve.php?path=' . urlencode($f)];
                    }
                }
            }

            // Check Exported / Generated Letters
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s['nama']);
            $searchSuffix = '_' . $safeName . '.pdf';
            
            foreach ($allGeneratedFiles as $f) {
                if (str_ends_with(strtolower($f), strtolower($searchSuffix))) {
                    $basename = basename($f);
                    $normalizedPath = str_replace('\\', '/', $f);
                    $alreadyExists = false;
                    if (isset($berkasMap[$kds])) {
                        foreach ($berkasMap[$kds] as $bm) {
                            if (str_contains(str_replace('\\', '/', (string)$bm['path_file']), urlencode($normalizedPath)) || 
                                str_contains(str_replace('\\', '/', (string)$bm['path_file']), $normalizedPath)) {
                                $alreadyExists = true;
                                break;
                            }
                        }
                    }
                    if (!$alreadyExists) {
                        $virtualId = 'surat_' . $kds . '_' . md5($normalizedPath);
                        
                        $docType = "Surat Generated";
                        $abbr = "";
                        if (str_starts_with($basename, 'Surat_Permohonan')) { $docType = "Surat Permohonan"; $abbr = "SP"; }
                        elseif (str_starts_with($basename, 'Surat_Jaminan')) { $docType = "Surat Jaminan"; $abbr = "SJ"; }
                        elseif (str_starts_with($basename, 'Surat_Keterangan')) { $docType = "Surat Keterangan"; $abbr = "SK"; }
                        elseif (str_starts_with($basename, 'Surat_Tugas')) { $docType = "Surat Tugas"; $abbr = "ST"; }
                        else {
                            $parts = explode('_' . $safeName, $basename);
                            if (count($parts) > 1) {
                                $docType = ucwords(str_replace('_', ' ', rtrim($parts[0], '_')));
                                $abbr = preg_replace('/[^A-Z]/', '', $docType);
                            }
                        }
                        
                        $tahunSurat = basename(dirname(dirname($f)));
                        $safeJenisPath = basename(dirname(dirname(dirname(dirname($f)))));
                        $kategori = 'Dokumen Hasil Export';
                        if (!empty($safeJenisPath) && !in_array(strtolower($safeJenisPath), ['export data', 'output', 'surat_menyurat', 'berkas'])) {
                            $kategori = ucwords(str_replace('_', ' ', $safeJenisPath));
                        }
                        
                        $serveUrl = '/serve.php?path=' . urlencode(str_replace('\\', '/', $f));
                        $berkasMap[$kds][] = [
                            'id' => $virtualId, 
                            'nama_berkas' => $docType, 
                            'kategori' => $kategori,
                            'nama_unik' => $docType . ' (' . $kategori . ')',
                            'tahun' => is_numeric($tahunSurat) ? $tahunSurat : null,
                            'path_file' => $serveUrl
                        ];
                    }
                }
            }
        }

        return $viewRenderer->render(__DIR__ . '/cetak_berkas', [
            'santris'   => $santris,
            'berkasMap' => $berkasMap,
            'search'    => $search,
            'total'     => count($santris)
        ]);
    }
}
