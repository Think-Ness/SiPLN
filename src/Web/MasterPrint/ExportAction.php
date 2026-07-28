<?php
declare(strict_types=1);

namespace App\Web\MasterPrint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

final class ExportAction
{
    public function __invoke(
        ServerRequestInterface $request,
        DataResponseFactoryInterface $responseFactory,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $selectedKds = $body['kds'] ?? [];
        $outputType = $body['outputType'] ?? 'excel';
        $reportType = $body['reportType'] ?? 'umum';

        if (empty($selectedKds) && $reportType !== 'formulir_pendataan') {
            return $responseFactory->createResponse('Tidak ada data yang dipilih.');
        }

        // ==============================================================
        // AUTOMATIC SORTING: SANTRI ASLI FIRST, SANTRI PINDAHAN LAST
        // ==============================================================
        if (!empty($selectedKds)) {
            $myKep = $_SESSION['def_kepengurusan'] ?? '';
            $kdsIn = implode(',', array_map('intval', $selectedKds));
            $santriSortList = $db->createCommand("SELECT kds, kepengurusan, nama FROM master_santri WHERE kds IN ($kdsIn)")->queryAll();
            
            $asliList = [];
            $pindahanList = [];
            foreach ($santriSortList as $sRow) {
                $sKep = trim((string)($sRow['kepengurusan'] ?? ''));
                if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0) {
                    $pindahanList[] = $sRow;
                } else {
                    $asliList[] = $sRow;
                }
            }
            
            usort($asliList, fn($a, $b) => strcmp($a['nama'], $b['nama']));
            usort($pindahanList, fn($a, $b) => strcmp($a['nama'], $b['nama']));
            
            $selectedKds = [];
            foreach ($asliList as $s) { $selectedKds[] = $s['kds']; }
            foreach ($pindahanList as $s) { $selectedKds[] = $s['kds']; }
        }
        // ==============================================================

        if ($reportType === 'formulir_pendataan') {
            $students = [];
            
            if (!empty($selectedKds)) {
                foreach ($selectedKds as $kds) {
                    $kdsVal = (int) $kds;
                    $santri = $db->createCommand(
                        "SELECT * FROM master_santri WHERE kds = :kds",
                        [':kds' => $kdsVal]
                    )->queryOne();
                    
                    if (!$santri) {
                        continue;
                    }
                    
                    // Fix scientific notation from Excel imports
                    if (isset($santri['no_sktt']) && strpos((string)$santri['no_sktt'], 'E') !== false) {
                        $santri['no_sktt'] = number_format((float)$santri['no_sktt'], 0, '', '');
                    }
                    if (isset($santri['no_ic']) && strpos((string)$santri['no_ic'], 'E') !== false) {
                        $santri['no_ic'] = number_format((float)$santri['no_ic'], 0, '', '');
                    }
                    
                    $pasporList = $db->createCommand(
                        "SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY id DESC",
                        [':kds' => $kdsVal]
                    )->queryAll();
            
                    $itasList = $db->createCommand(
                        "SELECT * FROM mtb_itas WHERE kds = :kds ORDER BY id DESC",
                        [':kds' => $kdsVal]
                    )->queryAll();
            
                    $barang = $db->createCommand(
                        "SELECT * FROM mtb_barang_terlarang WHERE kds = :kds ORDER BY id ASC",
                        [':kds' => $kdsVal]
                    )->queryAll();
            
                    $pasporBaru = [];
                    $pasporLama = [];
                    foreach ($pasporList as $p) {
                        if ($p['aktif'] == 1) {
                            $pasporBaru = $p;
                        } elseif (empty($pasporLama)) {
                            $pasporLama = $p;
                        }
                    }
            
                    $itasBaru = [];
                    foreach ($itasList as $i) {
                        if ($i['aktif'] == 1) {
                            $itasBaru = $i;
                            break;
                        }
                    }
                    
                    $atmList = [];
                    $hpList = [];
                    $otherBarang = [];
                    foreach ($barang as $b) {
                        $name = strtolower(trim((string)$b['nama_barang']));
                        if (strpos($name, 'atm') !== false || strpos($name, 'bank') !== false) {
                            $atmList[] = $b['nama_barang'] . ($b['jumlah_barang'] > 1 ? ' (' . $b['jumlah_barang'] . 'x)' : '');
                        } elseif (strpos($name, 'hp') !== false || strpos($name, 'handphone') !== false || strpos($name, 'telepon') !== false || strpos($name, 'ponsel') !== false) {
                            $hpList[] = $b['nama_barang'] . ($b['jumlah_barang'] > 1 ? ' (' . $b['jumlah_barang'] . 'x)' : '');
                        } else {
                            $otherBarang[] = $b['nama_barang'] . ($b['jumlah_barang'] > 1 ? ' (' . $b['jumlah_barang'] . 'x)' : '');
                        }
                    }
                    
                    $foto_url = '';
                    if (!empty($santri['path_foto'])) {
                        $foto_url = "/santri/" . $santri['kds'] . "/photo";
                    }
                    
                    $students[] = array_merge($santri, [
                        'foto_url' => $foto_url,
                        'paspor_baru' => $pasporBaru ?: null,
                        'paspor_lama' => $pasporLama ?: null,
                        'itas' => $itasBaru ?: null,
                        'atm' => implode(', ', $atmList),
                        'hp' => implode(', ', $hpList),
                        'barang_terlarang' => implode(', ', $otherBarang)
                    ]);
                }
            }
            
            return $viewRenderer
                ->withLayout(null)
                ->render(__DIR__ . '/print_formulir', [
                    'students' => $students
                ]);
        }

        if ($reportType === 'foto_personal') {
            $cols = (int)($body['absen_frekuensi'] ?? 5);
            if ($cols < 1) $cols = 1;
            
            $kolom = $body['absen_kolom'] ?? '1,0';
            $showOptions = explode(',', $kolom);
            $showNama = ($showOptions[0] ?? '1') === '1';
            $showGaris = ($showOptions[1] ?? '0') === '1';
            
            $students = [];
            if (!empty($selectedKds)) {
                $kdsIn = implode(',', array_map('intval', $selectedKds));
                $students = $db->createCommand("SELECT kds, stambuk, nama, path_foto FROM master_santri WHERE kds IN ($kdsIn) ORDER BY nama ASC")->queryAll();
            }
            
            if ($outputType === 'zip') {
                $zipFile = sys_get_temp_dir() . '/Foto_Personal_' . date('Ymd_His') . '.zip';
                $zip = new \ZipArchive();
                if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach ($students as $s) {
                        if (!empty($s['path_foto'])) {
                            $fotoPath = $s['path_foto'];
                            $fullPath = '';
                            
                            if (strpos($fotoPath, '/serve.php?path=') === 0) {
                                $fullPath = urldecode(substr($fotoPath, strlen('/serve.php?path=')));
                            } else {
                                $path = ltrim($fotoPath, '/');
                                $fullPath = __DIR__ . '/../../../../public/' . $path;
                                if (!file_exists($fullPath)) {
                                    $fullPath = __DIR__ . '/../../../../' . $path;
                                }
                            }
                            
                            if (!empty($fullPath) && file_exists($fullPath)) {
                                $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                                $cleanName = preg_replace('/[^A-Za-z0-9\- ]/', '', $s['nama']);
                                $zipName = $s['stambuk'] . ' - ' . $cleanName . '.' . $ext;
                                $zip->addFile($fullPath, $zipName);
                            }
                        }
                    }
                    $zip->close();
                    
                    if (file_exists($zipFile)) {
                        $stream = fopen($zipFile, 'r');
                        $psrStream = new \HttpSoft\Message\Stream($stream);
                        return new \HttpSoft\Message\Response(200, [
                            'Content-Type' => 'application/zip',
                            'Content-Disposition' => 'attachment; filename="Foto_Personal_Santri.zip"',
                            'Content-Length' => (string)filesize($zipFile),
                            'Cache-Control' => 'max-age=0'
                        ], $psrStream);
                    }
                }
                // Fallback to error
                return $responseFactory->createResponse('Gagal membuat file ZIP atau tidak ada foto yang bisa diunduh.');
            }
            
            return $viewRenderer
                ->withLayout(null)
                ->render(__DIR__ . '/print_foto', [
                    'students' => $students,
                    'cols' => $cols,
                    'showNama' => $showNama,
                    'showGaris' => $showGaris
                ]);
        }

        if ($reportType === 'ukuran_baju') {
            $judul = $body['absen_judul'] ?? 'Laporan Rekapitulasi Ukuran Baju';
            $orientasi = $body['absen_orientasi'] ?? 'portrait';
            $tampilKop = $body['absen_tampil_kop'] ?? '1';
            
            $instansiId = $body['absen_instansi'] ?? $_SESSION['instansi_id'] ?? 0;
            $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :id", [':id' => (int)$instansiId])->queryOne() ?: [];

            $students = [];
            if (!empty($selectedKds)) {
                $kdsIn = implode(',', array_map('intval', $selectedKds));
                $students = $db->createCommand("SELECT stambuk, nama, kelas, rayon, ukuran_baju FROM master_santri WHERE kds IN ($kdsIn) ORDER BY nama ASC")->queryAll();
            }
            
            // Group by ukuran_baju
            $grouped = [];
            $rekap = [];
            foreach ($students as $s) {
                $size = trim(strtoupper((string)($s['ukuran_baju'] ?? '')));
                if ($size === '') $size = 'TIDAK ADA UKURAN';
                
                if (!isset($grouped[$size])) {
                    $grouped[$size] = [];
                    $rekap[$size] = 0;
                }
                $grouped[$size][] = $s;
                $rekap[$size]++;
            }
            
            ksort($grouped);
            ksort($rekap);
            
            if ($outputType === 'excel') {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Ukuran Baju');
                
                $sheet->setCellValue('A1', strtoupper($judul));
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                
                $rowNum = 3;
                
                foreach ($grouped as $size => $list) {
                    $sheet->setCellValue("A{$rowNum}", "UKURAN: " . $size);
                    $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
                    $rowNum++;
                    
                    // Headers
                    $sheet->setCellValue("A{$rowNum}", "No");
                    $sheet->setCellValue("B{$rowNum}", "Stambuk");
                    $sheet->setCellValue("C{$rowNum}", "Nama Santri");
                    $sheet->setCellValue("D{$rowNum}", "Kelas");
                    $sheet->setCellValue("E{$rowNum}", "Rayon");
                    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
                    $rowStart = $rowNum;
                    $rowNum++;
                    
                    $no = 1;
                    foreach ($list as $s) {
                        $sheet->setCellValue("A{$rowNum}", $no++);
                        $sheet->setCellValueExplicit("B{$rowNum}", (string)$s['stambuk'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("C{$rowNum}", (string)$s['nama'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("D{$rowNum}", (string)$s['kelas'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("E{$rowNum}", (string)$s['rayon'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $rowNum++;
                    }
                    $sheet->getStyle("A{$rowStart}:E" . ($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $rowNum += 2; // spacing
                }
                
                // Rekapitulasi
                $sheet->setCellValue("A{$rowNum}", "REKAPITULASI UKURAN BAJU");
                $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
                $rowNum++;
                
                $sheet->setCellValue("A{$rowNum}", "Ukuran");
                $sheet->setCellValue("B{$rowNum}", "Jumlah (Santri)");
                $sheet->getStyle("A{$rowNum}:B{$rowNum}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowNum}:B{$rowNum}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
                $rowStart = $rowNum;
                $rowNum++;
                
                $total = 0;
                foreach ($rekap as $size => $count) {
                    $sheet->setCellValue("A{$rowNum}", $size);
                    $sheet->setCellValue("B{$rowNum}", $count);
                    $total += $count;
                    $rowNum++;
                }
                $sheet->setCellValue("A{$rowNum}", "TOTAL");
                $sheet->setCellValue("B{$rowNum}", $total);
                $sheet->getStyle("A{$rowNum}:B{$rowNum}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowStart}:B{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $stream = fopen('php://temp', 'r+');
                $writer->save($stream);
                rewind($stream);
                $psrStream = new \HttpSoft\Message\Stream($stream);
                $filename = 'Laporan_Ukuran_Baju_' . date('Ymd_His') . '.xlsx';
                
                return new \HttpSoft\Message\Response(200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0'
                ], $psrStream);
            }
            
            return $viewRenderer
                ->withLayout(null)
                ->render(__DIR__ . '/print_ukuran_baju', [
                    'judul' => $judul,
                    'instansi' => $instansi,
                    'orientasi' => $orientasi,
                    'tampilKop' => $tampilKop,
                    'grouped' => $grouped,
                    'rekap' => $rekap
                ]);
        }

        if ($reportType === 'absen_anggota') {
            $judul = $body['absen_judul'] ?? 'Daftar Hadir Anggota';
            $frek = (int)($body['absen_frekuensi'] ?? 14);
            $kolomRaw = $body['absen_kolom'] ?? '';
            $kolomPilihan = $kolomRaw ? explode(',', $kolomRaw) : [];
            
            $instansiId = $body['absen_instansi'] ?? '';
            $orientasi = $body['absen_orientasi'] ?? 'landscape';
            $tampilKop = $body['absen_tampil_kop'] ?? '1';
            
            if (empty($instansiId)) {
                $instansiId = $_SESSION['instansi_id'] ?? 0;
            }
            $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :id", [':id' => (int)$instansiId])->queryOne() ?: [];

            $students = [];
            if (!empty($selectedKds)) {
                foreach ($selectedKds as $kds) {
                    $kdsVal = (int) $kds;
                    $santri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kdsVal])->queryOne();
                    if (!$santri) continue;
                    
                    $pasporList = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY id DESC", [':kds' => $kdsVal])->queryAll();
                    $itasList = $db->createCommand("SELECT * FROM mtb_itas WHERE kds = :kds ORDER BY id DESC", [':kds' => $kdsVal])->queryAll();
                    
                    $paspor = [];
                    foreach ($pasporList as $p) {
                        if ($p['aktif'] == 1) { $paspor = $p; break; } elseif (empty($paspor)) { $paspor = $p; }
                    }
                    $itas = [];
                    foreach ($itasList as $i) {
                        if ($i['aktif'] == 1) { $itas = $i; break; } elseif (empty($itas)) { $itas = $i; }
                    }
                    
                    $santri['paspor'] = $paspor;
                    $santri['itas'] = $itas;
                    $students[] = $santri;
                }
            }
            
            $keysStr = $body['absen_sort_keys'] ?? '';
            $sortKeys = $keysStr ? explode(',', $keysStr) : [];
            
            $getVal = function($s, $key) {
                if ($key === 'no_paspor' || $key === 'exp_paspor') return $s['paspor'][$key] ?? '';
                if ($key === 'no_itas' || $key === 'level_itas' || $key === 'exp_itas') return $s['itas'][$key] ?? '';
                return $s[$key] ?? '';
            };
            
            usort($students, function($a, $b) use ($sortKeys, $getVal) {
                foreach ($sortKeys as $key) {
                    $valA = $getVal($a, $key); 
                    $valB = $getVal($b, $key);
                    if ($valA !== $valB) return strcasecmp((string)$valA, (string)$valB);
                }
                return strcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
            });
            
            if ($outputType === 'excel') {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Daftar Hadir');
                
                $sheet->setCellValue('A1', strtoupper($judul));
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                
                $colMap = [
                    'stambuk' => 'Stambuk', 
                    'kelas' => 'Kelas', 
                    'rayon' => 'Rayon',
                    'pondok' => 'Pondok/Asrama',
                    'status_santri' => 'Status Santri',
                    'jenis_kelamin' => 'Jenis Kelamin',
                    'negara' => 'Asal Negara', 
                    'kewarganegaraan' => 'Kewarganegaraan',
                    'tempat_lahir' => 'Tempat Lahir',
                    'tanggal_lahir' => 'Tanggal Lahir', 
                    'alamat' => 'Alamat',
                    'no_ic' => 'No IC',
                    'no_sktt' => 'No SKTT',
                    'ukuran_baju' => 'Ukuran Baju',
                    'keberadaan_paspor' => 'Keberadaan Paspor',
                    'no_paspor' => 'No Paspor', 
                    'exp_paspor' => 'Exp Paspor',
                    'no_itas' => 'No ITAS', 
                    'level_itas' => 'Level ITAS', 
                    'exp_itas' => 'Exp ITAS',
                    'nama_ayah' => 'Nama Ayah', 
                    'nama_ibu' => 'Nama Ibu', 
                    'no_hp' => 'No HP'
                ];
                
                $headers = [];
                foreach ($kolomPilihan as $k) {
                    if (isset($colMap[$k])) $headers[$k] = $colMap[$k];
                }
                
                $startCol = 1;
                $sheet->setCellValueExplicit([$startCol++, 3], 'No', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit([$startCol++, 3], 'Nama Santri', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                foreach ($headers as $label) {
                    $sheet->setCellValueExplicit([$startCol++, 3], $label, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                for ($i = 1; $i <= $frek; $i++) {
                    $sheet->setCellValueExplicit([$startCol++, 3], (string)$i, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                
                $maxColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol - 1);
                $sheet->mergeCells("A1:{$maxColLetter}1");
                $sheet->getStyle("A1:{$maxColLetter}1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A3:{$maxColLetter}3")->getFont()->setBold(true);
                $sheet->getStyle("A3:{$maxColLetter}3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A3:{$maxColLetter}3")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
                
                $rowNum = 4;
                if (empty($students)) {
                    $sheet->setCellValue("A4", "Tidak ada data santri");
                    $sheet->mergeCells("A4:{$maxColLetter}4");
                    $rowNum++;
                } else {
                    $no = 1;
                    foreach ($students as $s) {
                        $colIdx = 1;
                        $sheet->setCellValue([$colIdx++, $rowNum], $no++);
                        $sheet->setCellValue([$colIdx++, $rowNum], $s['nama'] ?? '');
                        foreach ($headers as $key => $label) {
                            $val = '';
                            if ($key === 'no_paspor') $val = $s['paspor']['no_paspor'] ?? '';
                            elseif ($key === 'exp_paspor') $val = $s['paspor']['exp_paspor'] ?? '';
                            elseif ($key === 'no_itas') $val = $s['itas']['no_itas'] ?? '';
                            elseif ($key === 'level_itas') $val = $s['itas']['level_itas'] ?? '';
                            elseif ($key === 'exp_itas') $val = $s['itas']['exp_itas'] ?? '';
                            elseif ($key === 'no_hp') {
                                $hp = array_filter([$s['no_ayah'] ?? '', $s['no_ibu'] ?? '', $s['no_hp_alternatif'] ?? '']);
                                $val = reset($hp) ?: '';
                            } elseif ($key === 'ukuran_baju') {
                                $val = strtoupper((string)($s['ukuran_baju'] ?? ''));
                            } else $val = $s[$key] ?? '';
                            
                            // FORMAT TANGGAL
                            if (in_array($key, ['tanggal_lahir', 'exp_paspor', 'exp_itas']) && !empty($val) && $val !== '0000-00-00') {
                                try {
                                    $dateObj = new DateTime($val);
                                    $val = $dateObj->format('d-M-Y');
                                } catch (Exception $e) {
                                    // Ignore format error
                                }
                            }
                            
                            $sheet->setCellValueExplicit([$colIdx++, $rowNum], (string)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                        $rowNum++;
                    }
                }
                
                $sheet->getStyle("A3:{$maxColLetter}" . ($rowNum - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                
                $stream = fopen('php://temp', 'r+');
                $writer->save($stream);
                rewind($stream);
                
                $psrStream = new \HttpSoft\Message\Stream($stream);
                $filename = 'Absen_Anggota_' . date('Ymd_His') . '.xlsx';
                
                return new \HttpSoft\Message\Response(200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0'
                ], $psrStream);
            }
            
            return $viewRenderer
                ->withLayout(null)
                ->render(__DIR__ . '/print_absen', [
                    'students' => $students,
                    'judul' => $judul,
                    'frek' => $frek,
                    'kolomPilihan' => $kolomPilihan,
                    'instansi' => $instansi,
                    'orientasi' => $orientasi,
                    'tampilKop' => $tampilKop
                ]);
        }

        $kdsString = implode(',', $selectedKds);

        if ($outputType === 'pdf') {
            return $responseFactory->createResponse("PDF Generation for KDS: $kdsString with report type: $reportType. (PDF Export will be fully integrated later)");
        } else {
            // Excel Export using PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            if ($reportType === 'laporan_vertikal') {
                $sheet->setTitle('Laporan Vertikal');
                $sheet->setShowGridlines(false);
                
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(3);
                $sheet->getColumnDimension('D')->setWidth(50);
                
                $row = 1;
                $no = 1;
                $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                foreach ($selectedKds as $kds) {
                    $kdsVal = (int) $kds;
                    $santri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kdsVal])->queryOne();
                    if (!$santri) continue;
                    
                    $pasporList = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY id DESC", [':kds' => $kdsVal])->queryAll();
                    $paspor = [];
                    foreach ($pasporList as $p) {
                        if ((int)$p['aktif'] === 1) {
                            $paspor = $p;
                            break;
                        } elseif (empty($paspor)) {
                            $paspor = $p;
                        }
                    }
                    
                    $ttl = $santri['tempat_lahir'] ?? '';
                    if (!empty($santri['tanggal_lahir']) && $santri['tanggal_lahir'] !== '0000-00-00') {
                        $t = strtotime($santri['tanggal_lahir']);
                        $ttl .= ($ttl ? ', ' : '') . date('j', $t) . ' ' . $months[(int)date('n', $t)] . ' ' . date('Y', $t);
                    }
                    
                    $paspor_exp = '';
                    if (!empty($paspor['exp_paspor']) && $paspor['exp_paspor'] !== '0000-00-00') {
                        $t = strtotime($paspor['exp_paspor']);
                        $paspor_exp = date('j', $t) . ' ' . $months[(int)date('n', $t)] . ' ' . date('Y', $t);
                    }
                    
                    $paspor_tgl = '';
                    if (!empty($paspor['tanggal_dikeluarkan']) && $paspor['tanggal_dikeluarkan'] !== '0000-00-00') {
                        $t = strtotime($paspor['tanggal_dikeluarkan']);
                        $paspor_tgl = date('j', $t) . ' ' . $months[(int)date('n', $t)] . ' ' . date('Y', $t);
                    }
                    
                    $personData = [
                        ['Nama', $santri['nama']],
                        ['Tempat/Tanggal Lahir', $ttl],
                        ['Jenis Kelamin', (strtolower(substr($santri['jenis_kelamin'] ?? 'L', 0, 1)) === 'l' ? 'L' : 'P')],
                        ['Asal Negara', $santri['negara'] ?? 'Malaysia'],
                        ['Pekerjaan/Status', 'Student/Santri'],
                        ['Alamat Indonesia', 'Pondok Modern Darussalam Gontor'],
                        ['No Paspor', $paspor['no_paspor'] ?? '-'],
                        ['Berlaku s.d', $paspor_exp ?: '-'],
                        ['Dikeluarkan', $paspor['tempat_dikeluarkan'] ?? '-'],
                        ['Tanggal', $paspor_tgl ?: '-'],
                    ];
                    
                    $sheet->setCellValue('A'.$row, $no . '.');
                    $sheet->getStyle('A'.$row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                    
                    $startRow = $row;
                    foreach ($personData as $d) {
                        $sheet->setCellValue('B'.$row, $d[0]);
                        $sheet->setCellValue('C'.$row, ':');
                        $sheet->setCellValueExplicit('D'.$row, (string)$d[1], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $row++;
                    }
                    
                    $sheet->getStyle("A{$startRow}:D".($row - 1))->getFont()->setName('Arial')->setSize(11);
                    $row++; 
                    $no++;
                }
                $filename = 'Laporan_Vertikal_' . date('Ymd_His') . '.xlsx';
                
            } else {
                $sheet->setTitle('Data Santri');
            
            // Set Headers
            $headers = [
                'A1' => 'No',
                'B1' => 'Stambuk / Regno',
                'C1' => 'Nama Santri',
                'D1' => 'Kelas',
                'E1' => 'Rayon',
                'F1' => 'Konsulat',
                'G1' => 'Daerah / Negara',
                'H1' => 'Tempat Lahir',
                'I1' => 'Tanggal Lahir',
                'J1' => 'Pondok',
                'K1' => 'Kepengurusan',
                'L1' => 'Nama Ayah',
                'M1' => 'Nama Ibu',
                'N1' => 'No Telepon Ayah',
                'O1' => 'No Telepon Ibu',
                'P1' => 'No Alternatif / Wali',
                'Q1' => 'Alamat Lengkap',
                'R1' => 'No IC Santri',
                'S1' => 'No SKTT',
                'T1' => 'Paspor Baru (No)',
                'U1' => 'Paspor Baru (Exp)',
                'V1' => 'Paspor Lama (No)',
                'W1' => 'ITAS (No)',
                'X1' => 'ITAS (Level)',
                'Y1' => 'ITAS (Exp)',
                'Z1' => 'ATM / Bank',
                'AA1'=> 'Handphone',
                'AB1'=> 'Barang Terlarang Lainnya'
            ];
            
            foreach ($headers as $cell => $val) {
                $sheet->setCellValue($cell, $val);
            }
            
            // Style Headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A2035']
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                ]
            ];
            $sheet->getStyle('A1:AB1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(25);
            
            // Fill Data
            $row = 2;
            foreach ($selectedKds as $idx => $kds) {
                $kdsVal = (int) $kds;
                $santri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kdsVal])->queryOne();
                if (!$santri) continue;
                
                $pasporList = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY id DESC", [':kds' => $kdsVal])->queryAll();
                $itasList = $db->createCommand("SELECT * FROM mtb_itas WHERE kds = :kds ORDER BY id DESC", [':kds' => $kdsVal])->queryAll();
                $barang = $db->createCommand("SELECT * FROM mtb_barang_terlarang WHERE kds = :kds ORDER BY id ASC", [':kds' => $kdsVal])->queryAll();
                
                $pasporBaru = [];
                $pasporLama = [];
                foreach ($pasporList as $p) {
                    if ($p['aktif'] == 1) {
                        $pasporBaru = $p;
                    } elseif (empty($pasporLama)) {
                        $pasporLama = $p;
                    }
                }
        
                $itasBaru = [];
                foreach ($itasList as $i) {
                    if ($i['aktif'] == 1) {
                        $itasBaru = $i;
                        break;
                    }
                }
                
                $atmList = [];
                $hpList = [];
                $otherBarang = [];
                foreach ($barang as $b) {
                    $name = strtolower(trim((string)$b['nama_barang']));
                    if (strpos($name, 'atm') !== false || strpos($name, 'bank') !== false) {
                        $atmList[] = $b['nama_barang'] . ($b['jumlah_barang'] > 1 ? ' (' . $b['jumlah_barang'] . 'x)' : '');
                    } elseif (strpos($name, 'hp') !== false || strpos($name, 'handphone') !== false || strpos($name, 'telepon') !== false || strpos($name, 'ponsel') !== false) {
                        $hpList[] = $b['nama_barang'] . ($b['jumlah_barang'] > 1 ? ' (' . $b['jumlah_barang'] . 'x)' : '');
                    } else {
                        $otherBarang[] = $b['nama_barang'] . ($b['jumlah_barang'] > 1 ? ' (' . $b['jumlah_barang'] . 'x)' : '');
                    }
                }
                
                // Fix scientific notation from original DB
                $no_sktt = isset($santri['no_sktt']) && strpos((string)$santri['no_sktt'], 'E') !== false ? number_format((float)$santri['no_sktt'], 0, '', '') : ($santri['no_sktt'] ?? '');
                $no_ic = isset($santri['no_ic']) && strpos((string)$santri['no_ic'], 'E') !== false ? number_format((float)$santri['no_ic'], 0, '', '') : ($santri['no_ic'] ?? '');
                
                $sheet->setCellValue('A'.$row, $idx + 1);
                $sheet->setCellValueExplicit('B'.$row, (string)($santri['stambuk'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row, $santri['nama'] ?? '');
                $sheet->setCellValue('D'.$row, $santri['kelas'] ?? '');
                $sheet->setCellValue('E'.$row, $santri['rayon'] ?? '');
                $sheet->setCellValue('F'.$row, $santri['konsulat'] ?? '');
                $sheet->setCellValue('G'.$row, $santri['negara'] ?? '');
                $sheet->setCellValue('H'.$row, $santri['tempat_lahir'] ?? '');
                $sheet->setCellValue('I'.$row, $santri['tanggal_lahir'] ?? '');
                $sheet->setCellValue('J'.$row, $santri['pondok'] ?? '');
                $sheet->setCellValue('K'.$row, $santri['kepengurusan'] ?? '');
                $sheet->setCellValue('L'.$row, $santri['nama_ayah'] ?? '');
                $sheet->setCellValue('M'.$row, $santri['nama_ibu'] ?? '');
                $sheet->setCellValueExplicit('N'.$row, (string)($santri['no_ayah'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('O'.$row, (string)($santri['no_ibu'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('P'.$row, (string)($santri['no_hp_alternatif'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('Q'.$row, $santri['alamat'] ?? '');
                $sheet->setCellValueExplicit('R'.$row, (string)$no_ic, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('S'.$row, (string)$no_sktt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $sheet->setCellValueExplicit('T'.$row, (string)($pasporBaru['no_paspor'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('U'.$row, $pasporBaru['exp_paspor'] ?? '');
                $sheet->setCellValueExplicit('V'.$row, (string)($pasporLama['no_paspor'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $sheet->setCellValueExplicit('W'.$row, (string)($itasBaru['no_itas'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('X'.$row, $itasBaru['level_itas'] ?? '');
                $sheet->setCellValue('Y'.$row, $itasBaru['exp_itas'] ?? '');
                
                $sheet->setCellValue('Z'.$row, implode(', ', $atmList));
                $sheet->setCellValue('AA'.$row, implode(', ', $hpList));
                $sheet->setCellValue('AB'.$row, implode(', ', $otherBarang));
                
                $row++;
            }
            
            // Auto size columns
            foreach (range('A', 'Z') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getColumnDimension('AA')->setAutoSize(true);
            $sheet->getColumnDimension('AB')->setAutoSize(true);
            
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            if (!isset($filename)) {
                $filename = 'Export_Data_Santri_' . date('Ymd_His') . '.xlsx';
            }
            
            // Get Instansi Path
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
            $pathFolder = $instansi['path_folder'] ?? '';
            
            if (!empty($pathFolder) && is_dir($pathFolder)) {
                $exportFolder = rtrim($pathFolder, '/\\') . DIRECTORY_SEPARATOR . 'Export Data';
                if (!is_dir($exportFolder)) {
                    @mkdir($exportFolder, 0777, true);
                }
                
                $filePath = $exportFolder . DIRECTORY_SEPARATOR . $filename;
                try {
                    $writer->save($filePath);
                    
                    // Build HTML response
                    $jsonFilePath = json_encode($filePath);
                    $html = "<!DOCTYPE html><html><head><script src=\"" . ASSET_URL . "/assets/offline/js/sweetalert2.all.min.js\"></script></head><body style=\"background: #f1f5f9;\"><script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Berhasil!',
                            html: 'File Excel telah berhasil disimpan langsung ke folder instansi Anda:<br><br><code style=\"background:#e9ecef; padding:5px 10px; border-radius:4px; color:#d63384;\">' + {$jsonFilePath} + '</code>',
                            confirmButtonText: 'Kembali',
                            confirmButtonColor: '#0d6efd',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = \"" . API_URL . "/master-print/menu-print\";
                        });
                    </script></body></html>";

                    $response = $responseFactory->createResponse($html);
                    return $response->withHeader('Content-Type', 'text/html');
                } catch (\Exception $e) {
                    // Fallback to download if save fails
                }
            }

            // Fallback to download
            $stream = fopen('php://temp', 'r+');
            $writer->save($stream);
            rewind($stream);
            
            $psrStream = new \HttpSoft\Message\Stream($stream);
            
            return new \HttpSoft\Message\Response(200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0'
            ], $psrStream);
        }
    }
}
