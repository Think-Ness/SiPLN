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
        
        // Helper universal untuk menyelesaikan path file di berbagai environment server
        $resolveFilePath = function(?string $pathUrl) {
            if (empty($pathUrl)) return null;

            $path = $pathUrl;
            if (str_starts_with($path, '/serve.php') || str_starts_with($path, 'serve.php')) {
                $parsed = parse_url($path);
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $queryData);
                    if (isset($queryData['path'])) {
                        $path = $queryData['path'];
                    }
                }
            }

            $normalized = str_replace('\\', '/', $path);

            if (file_exists($normalized) && is_file($normalized)) {
                return $normalized;
            }

            $publicDirs = [
                dirname(__DIR__, 3) . '/public',
                '//sipln/FOREIGN-PC1/02. Aplikasi/XAMPP/htdocs/webapp/public',
                'd:/XAMPP/htdocs/webapp/public'
            ];

            foreach ($publicDirs as $publicDir) {
                // 1. Cek relatif terhadap folder public/
                $candidate1 = $publicDir . '/' . ltrim($normalized, '/');
                if (file_exists($candidate1) && is_file($candidate1)) {
                    return $candidate1;
                }

                // 2. Cek jika mengandung /public/uploads/
                $uploadsPos = strpos($normalized, '/public/uploads/');
                if ($uploadsPos !== false) {
                    $relUpload = substr($normalized, $uploadsPos + strlen('/public/uploads/'));
                    $candidate2 = $publicDir . '/uploads/' . $relUpload;
                    if (file_exists($candidate2) && is_file($candidate2)) {
                        return $candidate2;
                    }
                }

                // 3. Cek jika mengandung /uploads/
                $uploadsPos2 = strpos($normalized, '/uploads/');
                if ($uploadsPos2 !== false) {
                    $relUpload = substr($normalized, $uploadsPos2 + strlen('/uploads/'));
                    $candidate3 = $publicDir . '/uploads/' . $relUpload;
                    if (file_exists($candidate3) && is_file($candidate3)) {
                        return $candidate3;
                    }
                }

                // 4. Fallback pencarian nama file di public/uploads
                $baseUploads = $publicDir . '/uploads';
                $filename = basename($normalized);
                if (!empty($filename) && is_dir($baseUploads)) {
                    $matches = @glob($baseUploads . '/*/*/' . $filename) ?: [];
                    if (!empty($matches) && file_exists($matches[0])) {
                        return $matches[0];
                    }
                    $matches2 = @glob($baseUploads . '/*/' . $filename) ?: [];
                    if (!empty($matches2) && file_exists($matches2[0])) {
                        return $matches2[0];
                    }
                }
            }

            return null;
        };

        $role = $_SESSION['role'] ?? '';
        $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
        if (!$targetInstansiId && $role === 'super_admin') {
            $targetInstansiId = (int) $db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
        }
        
        $params = [];
        $whereConds = [];
        
        // Filter Santri ketat per instansi jika bukan Super Admin
        if ($role !== 'super_admin' && !empty($_SESSION['instansi_id'])) {
            $kepengurusanStr = $db->createCommand("SELECT def_kepengurusan FROM master_instansi WHERE kode = :kode", [':kode' => (int)$_SESSION['instansi_id']])->queryScalar();
            if ($kepengurusanStr) {
                $whereConds[] = "s.kepengurusan = :kepengurusan";
                $params[':kepengurusan'] = $kepengurusanStr;
            }
        }

        if ($search !== '') {
            $whereConds[] = "(s.nama LIKE :q OR s.kds LIKE :q OR p.no_paspor LIKE :q OR s.negara LIKE :q)";
            $params[':q'] = "%$search%";
        }
        
        $where = !empty($whereConds) ? "WHERE " . implode(" AND ", $whereConds) : "";

        // Fetch Santri (Hanya join dengan paspor dan ITAS yang aktif untuk data utama)
        $santris = $db->createCommand(
            "SELECT s.kds, s.kode, s.nama, s.kepengurusan, p.no_paspor, s.negara, s.kelas,
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
                
                // Gunakan resolveFilePath agar file fisik tervalidasi
                $physicalPath = $resolveFilePath($b['path_file']);
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
                    $b['kategori'] = 'Dokumen Berkas';
                    $b['nama_unik'] = $namaBerkas;
                    $b['tahun'] = null;
                    $b['path_file'] = '/serve.php?path=' . urlencode(str_replace('\\', '/', $physicalPath));
                    $berkasMap[$kds][] = $b;
                }
            }

            // Tambahkan Seluruh Riwayat ITAS (diurutkan dari masa berlaku paling baru)
            $itasHistory = $db->createCommand(
                "SELECT id, kds, no_itas, level_itas, path_file, aktif, exp_itas FROM mtb_itas WHERE kds IN ($inKds) ORDER BY (exp_itas IS NULL OR exp_itas = '' OR exp_itas = '0000-00-00') ASC, exp_itas DESC, id DESC"
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

        // Ambil Dokumen Instansi dari mtb_berkas_penting (milik instansi sendiri atau yang berstatus public = 1)
        $instansiParams = [];
        $instansiWhere = "";
        if ($role !== 'super_admin' && !empty($_SESSION['instansi_id'])) {
            $instansiWhere = "WHERE (b.is_public = 1 OR b.kode = :myKode)";
            $instansiParams[':myKode'] = (string)$_SESSION['instansi_id'];
        }

        $instansiDocList = $db->createCommand(
            "SELECT b.id, b.kode as instansi_kode, b.nama_berkas, b.path_file, b.is_public, i.nama_instansi, i.def_kepengurusan
             FROM mtb_berkas_penting b
             JOIN master_instansi i ON b.kode = i.kode
             $instansiWhere
             ORDER BY b.id DESC",
            $instansiParams
        )->queryAll();

        $instansiBerkas = [];
        foreach ($instansiDocList as $b) {
            $realPath = $resolveFilePath($b['path_file']);
            // Daftarkan dokumen ke daftar berkas instansi
            $b['kategori'] = 'Dokumen Instansi';
            $b['nama_unik'] = $b['nama_berkas'];
            $b['tahun'] = null;
            $b['real_path'] = $realPath ?: $b['path_file'];
            $b['path_file'] = $realPath ? ('/serve.php?path=' . urlencode(str_replace('\\', '/', $realPath))) : $b['path_file'];
            $instansiBerkas[] = $b;
        }

        // Hubungkan dokumen instansi ke seluruh santri di berkasMap
        if (!empty($santris)) {
            foreach ($santris as $s) {
                $kds = $s['kds'];
                foreach ($instansiBerkas as $b) {
                    $berkasMap[$kds][] = $b;
                }
            }
        } else {
            // Jika belum ada santri terdaftar, masukkan ke dummy key agar tetap muncul di Step 1
            foreach ($instansiBerkas as $b) {
                $berkasMap[0][] = $b;
            }
        }

        $baseBerkasDir = null;
        if ($targetInstansiId) {
            $baseBerkasDir = \App\Shared\UploadPath::getBase($db, $targetInstansiId);
        }
        if (!$baseBerkasDir) {
            $baseBerkasDir = dirname(__DIR__, 4) . '/berkas';
        }
        $baseBerkasDir = rtrim(str_replace('\\', '/', $baseBerkasDir), '/');
        
        $fotoDir = $baseBerkasDir . '/foto santri';
        $pasporDir = $baseBerkasDir . '/paspor';
        $itasDir = $baseBerkasDir . '/itas';

        $isValidPath = function($pathUrl) use ($resolveFilePath) {
            if (empty($pathUrl)) return false;
            $p = $resolveFilePath($pathUrl);
            return !empty($p) && file_exists($p);
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

        // Helper untuk ekstrak jenis dokumen generik dari nama file santri
        $extractDocType = function(string $basename, string $namaSantri = '', string $kodeSantri = ''): string {
            $rawName = pathinfo($basename, PATHINFO_FILENAME);
            $rawName = str_replace(['_', '-'], ' ', $rawName);

            if (stripos($rawName, 'Curriculum Vitae') === 0 || stripos($rawName, 'CV') === 0) {
                return 'Curriculum Vitae';
            } elseif (stripos($rawName, 'Scan IC Ayah') === 0 || stripos($rawName, 'IC Ayah') === 0) {
                return 'Scan IC Ayah';
            } elseif (stripos($rawName, 'Scan IC Ibu') === 0 || stripos($rawName, 'IC Ibu') === 0) {
                return 'Scan IC Ibu';
            } elseif (stripos($rawName, 'Scan IC Santri') === 0 || stripos($rawName, 'IC Santri') === 0 || stripos($rawName, 'Scan IC Calon') === 0) {
                return 'Scan IC Santri';
            } elseif (stripos($rawName, 'Ijazah Rapor') === 0 || stripos($rawName, 'Ijazah') === 0 || stripos($rawName, 'Rapor') === 0) {
                return 'Ijazah Rapor';
            } elseif (stripos($rawName, 'Surat Beranak') === 0 || stripos($rawName, 'Akta') === 0 || stripos($rawName, 'Kelahiran') === 0) {
                return 'Surat Beranak';
            } elseif (stripos($rawName, 'Surat Kesanggupan Biaya') === 0 || stripos($rawName, 'Kesanggupan Biaya') === 0) {
                return 'Surat Kesanggupan Biaya';
            } elseif (stripos($rawName, 'Surat Pelajar Asing') === 0 || stripos($rawName, 'Pelajar Asing') === 0) {
                return 'Surat Pelajar Asing';
            } elseif (stripos($rawName, 'Surat Sehat') === 0 || stripos($rawName, 'Kesehatan') === 0) {
                return 'Surat Sehat';
            } elseif (stripos($rawName, 'Surat Permohonan') === 0) {
                return 'Surat Permohonan';
            } elseif (stripos($rawName, 'Surat Jaminan') === 0) {
                return 'Surat Jaminan';
            } elseif (stripos($rawName, 'Surat Keterangan') === 0) {
                return 'Surat Keterangan';
            } elseif (stripos($rawName, 'Surat Tugas') === 0) {
                return 'Surat Tugas';
            }

            $clean = $rawName;
            if (!empty($kodeSantri)) {
                $clean = preg_replace('/\b' . preg_quote($kodeSantri, '/') . '\b/i', '', $clean);
            }
            if (!empty($namaSantri)) {
                $nameParts = preg_split('/\s+/', trim($namaSantri));
                foreach ($nameParts as $p) {
                    if (strlen($p) >= 3) {
                        $clean = preg_replace('/\b' . preg_quote($p, '/') . '\b/i', '', $clean);
                    }
                }
            }
            $clean = preg_replace('/\b\d{6,}\b/', '', $clean);
            $clean = trim(preg_replace('/\s+/', ' ', $clean));
            return !empty($clean) ? ucwords($clean) : 'Dokumen Lainnya';
        };

        $tipeLabels = [
            'SP' => 'Surat Permohonan',
            'SK' => 'Surat Keterangan',
            'SJ' => 'Surat Jaminan',
            'ST' => 'Surat Tugas',
            'Surat_Permohonan' => 'Surat Permohonan',
            'Surat_Keterangan' => 'Surat Keterangan',
            'Surat_Jaminan' => 'Surat Jaminan',
            'Surat_Tugas' => 'Surat Tugas',
        ];

        $suratDbByKds = [];
        if (!empty($kdsList)) {
            $inKds = implode(",", array_map('intval', $kdsList));
            try {
                $suratDbRows = $db->createCommand("
                    SELECT ms.kds, s.nama as nama_santri, s.kode as kode_santri,
                           sg.id as surat_id, sg.tipe_surat, sg.nomor_surat, sg.tanggal_surat,
                           m.id as mailing_id, m.mode, m.jenis_pengajuan_id,
                           COALESCE(jp.jenis_pengajuan, 'Umum') as jenis_pengajuan,
                           jp.kantor, jp.output_path
                    FROM surat_mailing_santri ms
                    JOIN master_santri s ON ms.kds = s.kds
                    JOIN surat_mailing m ON ms.mailing_id = m.id
                    JOIN surat_generated sg ON m.id = sg.mailing_id
                    LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
                    WHERE ms.kds IN ($inKds)
                    ORDER BY sg.id DESC
                ")->queryAll();

                foreach ($suratDbRows as $sdb) {
                    $k = (int)$sdb['kds'];
                    if (!isset($suratDbByKds[$k])) $suratDbByKds[$k] = [];
                    $suratDbByKds[$k][] = $sdb;
                }
            } catch (\Throwable $e) {}
        }

        foreach ($santris as &$s) {
            $kds = $s['kds'];
            $kode = $s['kode'];
            
            // Check Foto
            if (!empty($s['path_foto']) && $isValidPath($s['path_foto'])) {
                $berkasMap[$kds][] = ['id' => 'foto_' . $kds, 'nama_berkas' => 'Pas Foto', 'kategori' => 'Dokumen ITAS & Paspor', 'nama_unik' => 'Pas Foto', 'path_file' => $s['path_foto']];
            } else {
                // Fallback scan
                $files = @glob($fotoDir . '/*_' . $kode . '_*.*') ?: [];
                if (empty($files)) $files = @glob($fotoDir . '/*' . str_replace(' ', '_', $s['nama']) . '*.*') ?: [];
                if (!empty($files)) {
                    $berkasMap[$kds][] = ['id' => 'foto_' . $kds, 'nama_berkas' => 'Pas Foto', 'kategori' => 'Dokumen ITAS & Paspor', 'nama_unik' => 'Pas Foto', 'path_file' => '/serve.php?path=' . urlencode($files[0])];
                }
            }

            // Check Paspor
            if (!empty($s['path_paspor']) && $isValidPath($s['path_paspor'])) {
                $berkasMap[$kds][] = ['id' => 'paspor_' . $kds, 'nama_berkas' => 'Scan Paspor', 'kategori' => 'Dokumen ITAS & Paspor', 'nama_unik' => 'Scan Paspor', 'path_file' => $s['path_paspor']];
            } else {
                // Fallback scan
                $files = @glob($pasporDir . '/*_' . $kode . '_*.*') ?: [];
                if (empty($files)) $files = @glob($pasporDir . '/*' . str_replace(' ', '_', $s['nama']) . '*.*') ?: [];
                if (!empty($files)) {
                    $berkasMap[$kds][] = ['id' => 'paspor_' . $kds, 'nama_berkas' => 'Scan Paspor', 'kategori' => 'Dokumen ITAS & Paspor', 'nama_unik' => 'Scan Paspor', 'path_file' => '/serve.php?path=' . urlencode($files[0])];
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
                    $berkasMap[$kds][] = ['id' => 'itas_' . $kds, 'nama_berkas' => 'Scan ITAS (Aktif)', 'kategori' => 'Dokumen ITAS & Paspor', 'nama_unik' => 'Scan ITAS (Aktif)', 'path_file' => '/serve.php?path=' . urlencode($files[0])];
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
                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                        continue; // Skip Thumbs.db, tmp, dll
                    }
                    $basename = basename($f);
                    $docType = $extractDocType($basename, $s['nama'], $kode);
                    
                    // Check if not already in DB
                    $alreadyExists = false;
                    if (isset($berkasMap[$kds])) {
                        foreach ($berkasMap[$kds] as $bm) {
                            if (str_contains((string)$bm['path_file'], urlencode($f)) || str_contains((string)$bm['path_file'], $basename) || ($bm['nama_berkas'] === $docType)) {
                                $alreadyExists = true;
                                break;
                            }
                        }
                    }
                    if (!$alreadyExists) {
                        $virtualId = 'file_' . $kds . '_' . md5($basename);
                        
                        $berkasMap[$kds][] = [
                            'id' => $virtualId, 
                            'nama_berkas' => $docType, 
                            'nama_unik' => $docType,
                            'kategori' => 'Dokumen Berkas', 
                            'path_file' => '/serve.php?path=' . urlencode($f)
                        ];
                    }
                }
            }

            // Check Exported / Generated Letters from Database & Filesystem
            $suratAddedMap = []; // [nama_unik => true]

            // 1. Ambil dari Database Surat Generator (Paling Akurat)
            if (!empty($suratDbByKds[$kds])) {
                foreach ($suratDbByKds[$kds] as $sdb) {
                    $tipeRaw = $sdb['tipe_surat'];
                    $docType = $tipeLabels[$tipeRaw] ?? ucwords(str_replace('_', ' ', $tipeRaw));
                    $kategori = trim($sdb['jenis_pengajuan'] ?? '');
                    if (empty($kategori)) $kategori = 'Surat Generator';
                    
                    $namaUnik = $docType . ' (' . $kategori . ')';
                    $tahunSurat = !empty($sdb['tanggal_surat']) ? date('Y', strtotime($sdb['tanggal_surat'])) : null;
                    
                    $safeJenis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kategori);
                    $safeTipeSurat = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tipeRaw);
                    if (isset($tipeLabels[$tipeRaw])) {
                        $safeTipeSurat = str_replace(' ', '_', $tipeLabels[$tipeRaw]);
                    }
                    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s['nama']);
                    
                    $kantor = $sdb['kantor'] ?: 'Kemenag';
                    $publicSuratDir = $baseBerkasDir . '/Surat_Menyurat/' . $kantor;
                    
                    $tahunItas = $tahunSurat ?: date('Y');
                    $bulanItas = !empty($sdb['tanggal_surat']) ? date('m', strtotime($sdb['tanggal_surat'])) : date('m');
                    
                    $candidatePaths = [
                        $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/' . $safeTipeSurat . '_' . $safeName . '.pdf',
                        $publicSuratDir . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/Sekaligus/' . $safeTipeSurat . '_Sekaligus_' . $safeJenis . '.pdf',
                        $baseBerkasDir . '/Export Data/' . $safeJenis . '/' . $safeTipeSurat . '_' . $safeName . '.pdf',
                    ];
                    if (!empty($sdb['output_path'])) {
                        $up = str_replace('\\', '/', trim($sdb['output_path']));
                        if (preg_match('/^[a-zA-Z]:/', $up)) {
                            $candidatePaths[] = rtrim($up, '/') . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/' . $safeTipeSurat . '_' . $safeName . '.pdf';
                        } else {
                            $candidatePaths[] = $baseBerkasDir . '/' . ltrim($up, '/') . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $tahunItas . '/' . $bulanItas . '/' . $safeTipeSurat . '_' . $safeName . '.pdf';
                        }
                    }

                    $matchedFile = null;
                    foreach ($candidatePaths as $cp) {
                        if (file_exists($cp)) {
                            $matchedFile = $cp;
                            break;
                        }
                    }

                    $serveUrl = $matchedFile 
                        ? ('/serve.php?path=' . urlencode(str_replace('\\', '/', $matchedFile)))
                        : ((defined('API_URL') ? API_URL : '/webapp/public') . '/api/surat/view/' . $sdb['surat_id'] . '?file=' . urlencode($safeTipeSurat . '_' . $safeName . '.pdf'));

                    $virtualId = 'surat_db_' . $sdb['surat_id'] . '_' . $kds;
                    $suratAddedMap[$namaUnik] = true;

                    $berkasMap[$kds][] = [
                        'id' => $virtualId, 
                        'nama_berkas' => $docType, 
                        'kategori' => $kategori,
                        'nama_unik' => $namaUnik,
                        'tahun' => $tahunSurat,
                        'path_file' => $serveUrl
                    ];
                }
            }

            // 2. Check Fallback Filesystem Scan
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s['nama']);
            $searchSuffix = '_' . $safeName . '.pdf';
            
            foreach ($allGeneratedFiles as $f) {
                if (str_ends_with(strtolower($f), strtolower($searchSuffix))) {
                    $basename = basename($f);
                    $normalizedPath = str_replace('\\', '/', $f);
                    
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
                    
                    $namaUnik = $docType . ' (' . $kategori . ')';
                    
                    if (isset($suratAddedMap[$namaUnik])) {
                        continue; // Skip jika sudah dimasukkan dari database
                    }

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
                        $serveUrl = '/serve.php?path=' . urlencode(str_replace('\\', '/', $f));
                        $suratAddedMap[$namaUnik] = true;
                        
                        $berkasMap[$kds][] = [
                            'id' => $virtualId, 
                            'nama_berkas' => $docType, 
                            'kategori' => $kategori,
                            'nama_unik' => $namaUnik,
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
