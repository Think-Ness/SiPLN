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
        $dbIdsSuratDirect = [];
        
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
            } elseif (str_starts_with($id, 'surat_db_')) {
                $parts = explode('_', $id);
                if (count($parts) >= 4) {
                    $suratId = (int)$parts[2];
                    $kds = (int)$parts[3];
                    $dbIdsSuratDirect[] = ['surat_id' => $suratId, 'kds' => $kds];
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
            
            // Pisahkan mana dokumen Instansi (Global maupun Khusus Instansi)
            $instansiCheck = $db->createCommand("SELECT id FROM mtb_berkas_penting WHERE id IN ($inList) AND (is_public = 1 OR kode IN (SELECT kode FROM master_instansi))")->queryColumn();
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
            $rows = $db->createCommand("
                SELECT i.id, i.path_file, CONCAT('Scan ITAS Lvl ', i.level_itas) as nama_berkas, 'Riwayat ITAS' as nama_unik, s.kds, s.nama as nama_santri, i.exp_itas as exp_date
                FROM mtb_itas i 
                JOIN master_santri s ON i.kds = s.kds 
                WHERE i.id IN ($inList) AND i.path_file IS NOT NULL
                ORDER BY (i.exp_itas IS NULL OR i.exp_itas = '' OR i.exp_itas = '0000-00-00') ASC, i.exp_itas DESC, i.id DESC
            ")->queryAll();
            $berkas = array_merge($berkas, $rows);
        }

        // Resolving dbIdsVirtual (file_ / surat_)
        if (!empty($dbIdsVirtual) || !empty($dbIdsSurat)) {
            $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
            if (!$targetInstansiId) {
                $targetInstansiId = (int) $db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
            }

            $baseBerkasDir = null;
            if ($targetInstansiId) {
                $baseBerkasDir = \App\Shared\UploadPath::getBase($db, $targetInstansiId);
            }
            if (!$baseBerkasDir) {
                $baseBerkasDir = dirname(__DIR__, 4) . '/berkas';
            }
            $baseBerkasDir = rtrim(str_replace('\\', '/', $baseBerkasDir), '/');
            
            // Resolve file_
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
                    $parts = preg_split('/\s+/', trim($namaSantri));
                    foreach ($parts as $p) {
                        if (strlen($p) >= 3) {
                            $clean = preg_replace('/\b' . preg_quote($p, '/') . '\b/i', '', $clean);
                        }
                    }
                }
                $clean = preg_replace('/\b\d{6,}\b/', '', $clean);
                $clean = trim(preg_replace('/\s+/', ' ', $clean));
                return !empty($clean) ? ucwords(strtolower($clean)) : ucwords(strtolower($rawName));
            };

            foreach ($dbIdsVirtual as $v) {
                $kds = $v['kds'];
                $md5 = $v['md5'];
                $s = $db->createCommand("SELECT kode, nama FROM master_santri WHERE kds = :kds")->bindValue(':kds', $kds)->queryOne();
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
                                $docType = $extractDocType($basename, $s['nama'], $s['kode'] ?? '');
                                
                                $berkas[] = [
                                    'path_file' => '/serve.php?path=' . urlencode($f), 
                                    'nama_berkas' => $docType, 
                                    'nama_unik' => $docType, 
                                    'kds' => $kds, 
                                    'nama_santri' => $s['nama']
                                ];
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
                $scanPdfs($suratMenyuratDir);

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

        // Helper universal untuk menemukan file Surat Generator di disk
        $findSuratFile = function(array $suratInfo, ?string $baseDir, ConnectionInterface $dbConnection): ?string {
            $tipeLabels = [
                'SP' => 'Surat_Permohonan',
                'SK' => 'Surat_Keterangan',
                'SJ' => 'Surat_Jaminan',
                'ST' => 'Surat_Tugas',
                'Surat_Permohonan' => 'Surat_Permohonan',
                'Surat_Keterangan' => 'Surat_Keterangan',
                'Surat_Jaminan' => 'Surat_Jaminan',
                'Surat_Tugas' => 'Surat_Tugas',
            ];

            $tipeRaw = $suratInfo['tipe_surat'] ?? '';
            $safeTipeSurat = $tipeLabels[$tipeRaw] ?? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tipeRaw);
            $safeJenis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $suratInfo['jenis_pengajuan'] ?? 'Umum');
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $suratInfo['nama_santri'] ?? '');
            $kantor = !empty($suratInfo['kantor']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $suratInfo['kantor']) : 'Kemenag';

            $tanggalSurat = $suratInfo['tanggal_surat'] ?? date('Y-m-d');
            $tahunSurat = !empty($tanggalSurat) ? date('Y', strtotime($tanggalSurat)) : date('Y');
            $bulanSurat = !empty($tanggalSurat) ? date('m', strtotime($tanggalSurat)) : date('m');

            $tahunItas = $tahunSurat;
            $bulanItas = $bulanSurat;
            if (!empty($suratInfo['mailing_id'])) {
                try {
                    $firstSantri = $dbConnection->createCommand(
                        "SELECT i.exp_itas 
                         FROM surat_mailing_santri ms
                         LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON ms.kds = i.kds
                         WHERE ms.mailing_id = :mid ORDER BY ms.id ASC LIMIT 1",
                        [':mid' => (int)$suratInfo['mailing_id']]
                    )->queryOne();

                    if ($firstSantri && !empty($firstSantri['exp_itas']) && $firstSantri['exp_itas'] !== '-') {
                        $tahunItas = date('Y', strtotime($firstSantri['exp_itas']));
                        $bulanItas = date('m', strtotime($firstSantri['exp_itas']));
                    }
                } catch (\Throwable $e) {}
            }

            $candidateDirs = [];
            $baseDirs = array_filter([
                $baseDir,
                dirname(__DIR__, 3) . '/public/uploads',
                'd:/XAMPP/htdocs/webapp/public/uploads',
                '//sipln/FOREIGN-PC1/02. Aplikasi/XAMPP/htdocs/webapp/public/uploads'
            ]);

            foreach ($baseDirs as $bd) {
                $bdNorm = rtrim(str_replace('\\', '/', $bd), '/');
                foreach ([[$tahunItas, $bulanItas], [$tahunSurat, $bulanSurat]] as [$th, $bl]) {
                    $candidateDirs[] = $bdNorm . '/Surat_Menyurat/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl;
                    $candidateDirs[] = $bdNorm . '/Surat_Menyurat/' . $kantor . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl;
                    $candidateDirs[] = $bdNorm . '/Surat_Menyurat/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl . '/Sekaligus';
                    $candidateDirs[] = $bdNorm . '/Surat_Menyurat/' . $kantor . '/Output/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl . '/Sekaligus';
                    $candidateDirs[] = $bdNorm . '/Surat_Menyurat/' . $kantor . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl;
                }
                $candidateDirs[] = $bdNorm . '/Export Data/' . $safeJenis;
            }

            if (!empty($suratInfo['output_path'])) {
                $up = str_replace('\\', '/', trim($suratInfo['output_path']));
                foreach ([[$tahunItas, $bulanItas], [$tahunSurat, $bulanSurat]] as [$th, $bl]) {
                    if (preg_match('/^[a-zA-Z]:/', $up)) {
                        $candidateDirs[] = rtrim($up, '/') . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl;
                        $candidateDirs[] = rtrim($up, '/') . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl . '/Sekaligus';
                    } else {
                        foreach ($baseDirs as $bd) {
                            $bdNorm = rtrim(str_replace('\\', '/', $bd), '/');
                            $candidateDirs[] = $bdNorm . '/' . ltrim($up, '/') . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl;
                            $candidateDirs[] = $bdNorm . '/' . ltrim($up, '/') . '/' . $safeJenis . '/' . $safeTipeSurat . '/' . $th . '/' . $bl . '/Sekaligus';
                        }
                    }
                }
            }

            $candidateFileNames = array_filter([
                $safeTipeSurat . '_' . $safeName . '.pdf',
                $safeTipeSurat . '_Sekaligus_' . $safeJenis . '.pdf',
                $safeTipeSurat . '_' . $safeJenis . '.pdf',
                $safeName . '.pdf'
            ]);

            foreach ($candidateDirs as $cd) {
                if (!is_dir($cd)) continue;
                foreach ($candidateFileNames as $cfn) {
                    $p = $cd . '/' . $cfn;
                    if (file_exists($p) && is_file($p)) {
                        return $p;
                    }
                }
            }

            // Fallback: search with glob in Surat_Menyurat and Export Data
            foreach ($baseDirs as $bd) {
                $bdNorm = rtrim(str_replace('\\', '/', $bd), '/');
                $searchDirs = [
                    $bdNorm . '/Surat_Menyurat',
                    $bdNorm . '/Export Data'
                ];
                foreach ($searchDirs as $sDir) {
                    if (!is_dir($sDir)) continue;
                    if (!empty($safeName)) {
                        $matches = @glob($sDir . '/*/*/*/*/*/*' . $safeName . '*.pdf') ?: [];
                        if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                        $matches = @glob($sDir . '/*/*/*/*/*/' . $safeName . '*.pdf') ?: [];
                        if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                        $matches = @glob($sDir . '/*/*/*/*/' . $safeName . '*.pdf') ?: [];
                        if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                        $matches = @glob($sDir . '/*/*/*/' . $safeName . '*.pdf') ?: [];
                        if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                    }
                    $matches = @glob($sDir . '/*/*/*/*/*/*' . $safeTipeSurat . '*Sekaligus*.pdf') ?: [];
                    if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                    $matches = @glob($sDir . '/*/*/*/*/*' . $safeTipeSurat . '*Sekaligus*.pdf') ?: [];
                    if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                    $matches = @glob($sDir . '/*/*/*/*' . $safeTipeSurat . '*Sekaligus*.pdf') ?: [];
                    if (!empty($matches) && file_exists($matches[0])) return $matches[0];
                }
            }

            return null;
        };

        // Process Direct DB Generated Surat (surat_db_{surat_id}_{kds})
        if (!empty($dbIdsSuratDirect)) {
            $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
            if (!$targetInstansiId) {
                $targetInstansiId = (int) $db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
            }

            $baseBerkasDir = null;
            if ($targetInstansiId) {
                $baseBerkasDir = \App\Shared\UploadPath::getBase($db, $targetInstansiId);
            }
            if (!$baseBerkasDir) {
                $baseBerkasDir = dirname(__DIR__, 4) . '/berkas';
            }
            $baseBerkasDir = rtrim(str_replace('\\', '/', $baseBerkasDir), '/');

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

            foreach ($dbIdsSuratDirect as $v) {
                $suratId = $v['surat_id'];
                $kds = $v['kds'];

                $row = $db->createCommand("
                    SELECT sg.id, sg.tipe_surat, sg.nomor_surat, sg.tanggal_surat,
                           s.nama as nama_santri, s.kode as kode_santri,
                           m.id as mailing_id, m.mode,
                           COALESCE(jp.jenis_pengajuan, 'Umum') as jenis_pengajuan,
                           jp.kantor, jp.output_path
                    FROM surat_generated sg
                    JOIN surat_mailing m ON sg.mailing_id = m.id
                    JOIN master_santri s ON s.kds = :kds
                    LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
                    WHERE sg.id = :sid
                ", [':sid' => $suratId, ':kds' => $kds])->queryOne();

                if ($row) {
                    $tipeRaw = $row['tipe_surat'];
                    $docType = $tipeLabels[$tipeRaw] ?? ucwords(str_replace('_', ' ', $tipeRaw));
                    $kategori = trim($row['jenis_pengajuan'] ?? '');
                    if (empty($kategori)) $kategori = 'Surat Generator';
                    $namaUnik = $docType . ' (' . $kategori . ')';

                    $safeTipeSurat = $tipeLabels[$tipeRaw] ?? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tipeRaw);
                    if (isset($tipeLabels[$tipeRaw])) {
                        $safeTipeSurat = str_replace(' ', '_', $tipeLabels[$tipeRaw]);
                    }
                    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $row['nama_santri']);

                    $foundPath = $findSuratFile($row, $baseBerkasDir, $db);

                    if ($foundPath && file_exists($foundPath)) {
                        $berkas[] = [
                            'path_file' => '/serve.php?path=' . urlencode(str_replace('\\', '/', $foundPath)),
                            'nama_berkas' => $docType,
                            'nama_unik' => $namaUnik,
                            'kds' => $kds,
                            'nama_santri' => $row['nama_santri']
                        ];
                    } else {
                        // Masukkan dengan URL view surat sebagai fallback jika resolver nanti mencari lagi
                        $serveUrl = (defined('API_URL') ? API_URL : '/webapp/public') . '/api/surat/view/' . $row['id'] . '?file=' . urlencode($safeTipeSurat . '_' . $safeName . '.pdf');
                        $berkas[] = [
                            'path_file' => $serveUrl,
                            'nama_berkas' => $docType,
                            'nama_unik' => $namaUnik,
                            'kds' => $kds,
                            'nama_santri' => $row['nama_santri']
                        ];
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
                        if (($a['kds'] ?? 0) !== ($b['kds'] ?? 0)) {
                            return ($a['kds'] ?? 0) <=> ($b['kds'] ?? 0);
                        }
                        if (isset($a['exp_date']) || isset($b['exp_date'])) {
                            $dateA = !empty($a['exp_date']) ? strtotime((string)$a['exp_date']) : 0;
                            $dateB = !empty($b['exp_date']) ? strtotime((string)$b['exp_date']) : 0;
                            if ($dateA !== $dateB) {
                                return $dateB <=> $dateA; // Exp date paling baru di atas
                            }
                        }
                        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
                    }
                    return $posA <=> $posB;
                });
            }

            $resolveFilePath = function(?string $pathUrl) use ($findSuratFile, $baseBerkasDir, $db) {
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

                // Cek jika path berupa API view surat generator (/api/surat/view/{id}?file=...)
                if (str_contains($path, '/api/surat/view/') || str_contains($path, 'api/surat/view/')) {
                    $parsed = parse_url($path);
                    $pathParts = explode('/api/surat/view/', $parsed['path'] ?? $path);
                    if (count($pathParts) < 2) {
                        $pathParts = explode('api/surat/view/', $parsed['path'] ?? $path);
                    }
                    if (count($pathParts) >= 2) {
                        $suratId = (int)explode('/', trim($pathParts[1], '/'))[0];
                        $fileName = '';
                        if (isset($parsed['query'])) {
                            parse_str($parsed['query'], $qData);
                            $fileName = $qData['file'] ?? '';
                        }
                        if ($suratId > 0) {
                            $sRow = $db->createCommand("
                                SELECT sg.id, sg.tipe_surat, sg.nomor_surat, sg.tanggal_surat,
                                       m.id as mailing_id, m.mode,
                                       COALESCE(jp.jenis_pengajuan, 'Umum') as jenis_pengajuan,
                                       jp.kantor, jp.output_path
                                FROM surat_generated sg
                                JOIN surat_mailing m ON sg.mailing_id = m.id
                                LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
                                WHERE sg.id = :sid
                            ", [':sid' => $suratId])->queryOne();

                            if ($sRow) {
                                if (!empty($fileName)) {
                                    $sRow['nama_santri'] = pathinfo($fileName, PATHINFO_FILENAME);
                                }
                                $found = $findSuratFile($sRow, $baseBerkasDir, $db);
                                if ($found && file_exists($found)) {
                                    return $found;
                                }
                            }
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

            $resolvePdfTool = function(string $name, array $customPaths = []): ?string {
                foreach ($customPaths as $p) {
                    if (file_exists($p)) return $p;
                }
                $out = [];
                @exec('where.exe ' . escapeshellarg($name) . ' 2>NUL', $out, $ret);
                if ($ret === 0 && !empty($out[0]) && file_exists(trim($out[0]))) {
                    return trim($out[0]);
                }
                return null;
            };

            $buildPdfContent = function($berkasList) use ($resolveFilePath, $resolvePdfTool) {
                $pdf = new Fpdi();
                $pdf->SetAutoPageBreak(false);
                $tempFiles = [];

                $pdftocairoBin = $resolvePdfTool('pdftocairo', [
                    'C:/Program Files/poppler-24.08.0/Library/bin/pdftocairo.exe',
                    'C:/Program Files/poppler/bin/pdftocairo.exe'
                ]);

                $pdftkBin = $resolvePdfTool('pdftk', [
                    'C:/Program Files (x86)/PDFtk/bin/pdftk.exe',
                    'C:/Program Files/PDFtk/bin/pdftk.exe',
                    'C:/PDFtk/bin/pdftk.exe'
                ]);

                $gsBin = $resolvePdfTool('gswin32c', [
                    'C:/Program Files (x86)/gs/gs8.64/bin/gswin32c.exe',
                    'C:/Program Files/gs/gs*/bin/gswin64c.exe'
                ]);

                $pdftoppmBin = $resolvePdfTool('pdftoppm', [
                    'C:/Program Files/poppler-24.08.0/Library/bin/pdftoppm.exe',
                    'C:/Program Files/poppler/bin/pdftoppm.exe'
                ]);

                foreach ($berkasList as $b) {
                    $pathUrl = $b['path_file'];
                    if (empty($pathUrl)) continue;

                    $physicalPath = $resolveFilePath($pathUrl);
                    if (empty($physicalPath) || !file_exists($physicalPath)) continue;

                    $ext = strtolower(pathinfo($physicalPath, PATHINFO_EXTENSION));

                    if ($ext === 'pdf') {
                        $pageCount = 0;
                        $importedFile = $physicalPath;
                        $isRenderedImages = false;
                        $renderedImages = [];

                        try {
                            $pageCount = $pdf->setSourceFile($physicalPath);
                        } catch (\Throwable $e) {
                            $fallbackSuccess = false;
                            $fallbackErrors = [];

                            // 1. Coba konversi via pdftocairo (sangat akurat menangani PDF 1.5+ kompresi object stream)
                            if ($pdftocairoBin) {
                                $tempPdfCairo = tempnam(sys_get_temp_dir(), 'pdf_cairo_') . '.pdf';
                                $cmd = escapeshellarg($pdftocairoBin) . ' -pdf ' . escapeshellarg($physicalPath) . ' ' . escapeshellarg($tempPdfCairo) . ' 2>&1';
                                $out = [];
                                exec($cmd, $out, $ret);
                                if ($ret === 0 && file_exists($tempPdfCairo) && filesize($tempPdfCairo) > 0) {
                                    try {
                                        $pageCount = $pdf->setSourceFile($tempPdfCairo);
                                        $importedFile = $tempPdfCairo;
                                        $tempFiles[] = $tempPdfCairo;
                                        $fallbackSuccess = true;
                                    } catch (\Throwable $ex) {
                                        $fallbackErrors[] = 'pdftocairo parse error: ' . $ex->getMessage();
                                        @unlink($tempPdfCairo);
                                    }
                                } else {
                                    $fallbackErrors[] = 'pdftocairo failed: ' . implode(' ', $out);
                                }
                            }

                            // 2. Coba dekompresi via pdftk
                            if (!$fallbackSuccess && $pdftkBin) {
                                $tempPdfTk = tempnam(sys_get_temp_dir(), 'pdf_fix_') . '.pdf';
                                $cmd = escapeshellarg($pdftkBin) . ' ' . escapeshellarg($physicalPath) . ' output ' . escapeshellarg($tempPdfTk) . ' uncompress 2>&1';
                                $out = [];
                                exec($cmd, $out, $ret);
                                if ($ret === 0 && file_exists($tempPdfTk) && filesize($tempPdfTk) > 0) {
                                    try {
                                        $pageCount = $pdf->setSourceFile($tempPdfTk);
                                        $importedFile = $tempPdfTk;
                                        $tempFiles[] = $tempPdfTk;
                                        $fallbackSuccess = true;
                                    } catch (\Throwable $ex) {
                                        $fallbackErrors[] = 'pdftk parse error: ' . $ex->getMessage();
                                        @unlink($tempPdfTk);
                                    }
                                } else {
                                    $fallbackErrors[] = 'pdftk failed: ' . implode(' ', $out);
                                }
                            }

                            // 3. Coba via Ghostscript
                            if (!$fallbackSuccess && $gsBin) {
                                $tempPdfGs = tempnam(sys_get_temp_dir(), 'pdf_gs_') . '.pdf';
                                $cmd = escapeshellarg($gsBin) . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' . escapeshellarg($tempPdfGs) . ' ' . escapeshellarg($physicalPath) . ' 2>&1';
                                $out = [];
                                exec($cmd, $out, $ret);
                                if ($ret === 0 && file_exists($tempPdfGs) && filesize($tempPdfGs) > 0) {
                                    try {
                                        $pageCount = $pdf->setSourceFile($tempPdfGs);
                                        $importedFile = $tempPdfGs;
                                        $tempFiles[] = $tempPdfGs;
                                        $fallbackSuccess = true;
                                    } catch (\Throwable $ex) {
                                        $fallbackErrors[] = 'Ghostscript parse error: ' . $ex->getMessage();
                                        @unlink($tempPdfGs);
                                    }
                                } else {
                                    $fallbackErrors[] = 'Ghostscript failed: ' . implode(' ', $out);
                                }
                            }

                            // 4. Fallback rendering raster jika seluruh parser vector gagal
                            if (!$fallbackSuccess && ($pdftoppmBin || $pdftocairoBin)) {
                                $imgPrefix = tempnam(sys_get_temp_dir(), 'pdf_page_');
                                if ($pdftoppmBin) {
                                    $cmd = escapeshellarg($pdftoppmBin) . ' -png -r 150 ' . escapeshellarg($physicalPath) . ' ' . escapeshellarg($imgPrefix) . ' 2>&1';
                                } else {
                                    $cmd = escapeshellarg($pdftocairoBin) . ' -png -r 150 ' . escapeshellarg($physicalPath) . ' ' . escapeshellarg($imgPrefix) . ' 2>&1';
                                }
                                exec($cmd, $out, $ret);
                                $matchedImgs = @glob($imgPrefix . '-*.png') ?: (@glob($imgPrefix . '*.png') ?: []);
                                if (!empty($matchedImgs)) {
                                    sort($matchedImgs, SORT_NATURAL);
                                    $renderedImages = $matchedImgs;
                                    $isRenderedImages = true;
                                    $fallbackSuccess = true;
                                    foreach ($matchedImgs as $mImg) {
                                        $tempFiles[] = $mImg;
                                    }
                                } else {
                                    $fallbackErrors[] = 'Rasterize failed: ' . implode(' ', $out);
                                }
                            }

                            if (!$fallbackSuccess) {
                                throw new \Exception("Gagal memproses file PDF ($physicalPath): " . $e->getMessage() . " | Fallback attempts: " . implode(" ; ", $fallbackErrors));
                            }
                        }

                        if ($isRenderedImages) {
                            foreach ($renderedImages as $imgPath) {
                                $imgSize = @getimagesize($imgPath);
                                $orientation = ($imgSize && $imgSize[0] > $imgSize[1]) ? 'L' : 'P';
                                $pdf->AddPage($orientation, 'A4');
                                $pdf->Image($imgPath, 0, 0, ($orientation === 'L' ? 297 : 210), ($orientation === 'L' ? 210 : 297));
                            }
                        } else {
                            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                                $templateId = $pdf->importPage($pageNo);
                                $size = $pdf->getTemplateSize($templateId);
                                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                                $pdf->useTemplate($templateId);
                            }
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
                $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
                if (!$targetInstansiId) {
                    $targetInstansiId = (int) $db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
                }

                $baseBerkasDir = null;
                if ($targetInstansiId) {
                    $baseBerkasDir = \App\Shared\UploadPath::getBase($db, $targetInstansiId);
                }
                if (!$baseBerkasDir) {
                    $baseBerkasDir = dirname(__DIR__, 4) . '/berkas';
                }
                $baseBerkasDir = rtrim(str_replace('\\', '/', $baseBerkasDir), '/');
                
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

                // List of generated files for download/preview
                $fileListHtml = '';
                $savedFiles = @glob($saveDir . '*.pdf') ?: [];
                foreach ($savedFiles as $sf) {
                    $sfName = basename($sf);
                    $sfUrl = API_URL . '/serve.php?path=' . urlencode(str_replace('\\', '/', $sf));
                    $fileListHtml .= "<li style='margin-bottom: 6px;'><a href='{$sfUrl}' target='_blank' style='color: #0d6efd; text-decoration: none; font-weight: 500;'>📄 {$sfName}</a></li>";
                }

                $winSaveDir = str_replace('/', '\\', $saveDir);
                $safeDirJs = json_encode($saveDir);
                $winDirJs = json_encode($winSaveDir);
                $apiUrl = API_URL;
                $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Berhasil Disimpan | SIPLN</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .card { background: white; padding: 36px 32px; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); text-align: center; max-width: 540px; width: 100%; border: 1px solid #e2e8f0; }
        .success-icon { width: 64px; height: 64px; background: #dcfce7; color: #16a34a; font-size: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; font-weight: bold; }
        h2 { margin: 0 0 8px; color: #0f172a; font-size: 22px; font-weight: 700; }
        p { color: #64748b; margin-bottom: 16px; line-height: 1.5; font-size: 14px; }
        .path-box { background: #f8fafc; padding: 12px 14px; border-radius: 10px; font-family: monospace; word-break: break-all; margin-bottom: 16px; color: #334155; border: 1px solid #cbd5e1; text-align: left; font-size: 13px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 18px; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13px; transition: all 0.2s; cursor: pointer; border: none; }
        .btn-success { background: #16a34a; }
        .btn-success:hover { background: #15803d; }
        .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-primary { background: #2563eb; }
        .btn-primary:hover { background: #1d4ed8; }
        .file-list-box { max-height: 140px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; text-align: left; font-size: 13px; margin-bottom: 20px; }
        .file-list-box ul { margin: 0; padding-left: 18px; }
        .toast-msg { display: none; margin-top: 10px; font-size: 12px; color: #16a34a; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-icon">✓</div>
        <h2>Berhasil Disimpan!</h2>
        <p>File gabungan PDF individual per santri telah berhasil dibuat dan disimpan langsung ke server instansi pada folder:</p>
        
        <div class="path-box">
            <span id="pathText">{$winSaveDir}</span>
            <button type="button" onclick="salinPath()" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11px; flex-shrink: 0;" title="Salin Path">📋 Salin</button>
        </div>
        
        <div class="file-list-box">
            <div style="font-weight: 600; color: #475569; margin-bottom: 6px; font-size: 12px;">Daftar File PDF Hasil Gabungan:</div>
            <ul>{$fileListHtml}</ul>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <button onclick="bukaFolder()" class="btn btn-success" id="btnBuka">📂 Buka Folder</button>
            <button onclick="salinPath()" class="btn btn-secondary">📋 Salin Path</button>
            <a href="javascript:window.close();" class="btn btn-primary">Tutup Tab Ini</a>
        </div>
        <div id="toastMsg" class="toast-msg">✓ Path folder berhasil disalin ke clipboard!</div>
    </div>
    
    <script>
        function salinPath() {
            var path = {$winDirJs};
            navigator.clipboard.writeText(path).then(() => {
                var toast = document.getElementById('toastMsg');
                toast.style.display = 'block';
                setTimeout(() => toast.style.display = 'none', 3000);
            }).catch(() => {
                alert('Silakan salin path secara manual: ' + path);
            });
        }

        function bukaFolder() {
            var path = {$safeDirJs};
            var btn = document.getElementById('btnBuka');
            btn.textContent = '⏳ Membuka...';
            btn.disabled = true;

            fetch('{$apiUrl}/api/pemberkasan/open-folder?path=' + encodeURIComponent(path))
                .then(r => r.json())
                .then(data => {
                    btn.textContent = '📂 Buka Folder';
                    btn.disabled = false;
                    if (!data.success) {
                        alert(data.message || 'Gagal membuka folder di server. Anda dapat menggunakan tombol "Salin Path" dan membuka di File Explorer.');
                    }
                }).catch(e => {
                    btn.textContent = '📂 Buka Folder';
                    btn.disabled = false;
                    salinPath();
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
