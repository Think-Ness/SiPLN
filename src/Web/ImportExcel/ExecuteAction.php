<?php
declare(strict_types=1);

namespace App\Web\ImportExcel;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Shared\AuditLogger;
use App\Shared\IdGenerator;
use App\Shared\FirebaseSync;

final class ExecuteAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = json_decode((string)$request->getBody(), true);

        $mapping = $body['mapping'] ?? [];
        $requiredFields = $body['requiredFields'] ?? [];
        $duplicateMode = $body['duplicateMode'] ?? 'skip';

        // Re-read the file from session
        $tmpFile = $_SESSION['import_tmp_file'] ?? '';
        $headers = $_SESSION['import_headers'] ?? [];
        if (!$tmpFile || !file_exists($tmpFile)) {
            return JsonResponse::create(['success' => false, 'message' => 'Session expired. Silakan upload ulang file Excel.'], 422);
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheetIndex = $_SESSION['import_sheet_index'] ?? 0;
            
            // Just in case sheet index is out of bounds
            if ($sheetIndex < 0 || $sheetIndex >= $spreadsheet->getSheetCount()) {
                $sheetIndex = 0;
            }
            
            $sheet = $spreadsheet->getSheet($sheetIndex);
            $allData = $sheet->toArray(null, true, true, true);
            array_shift($allData); // Remove header row

            // Parse all rows
            $rows = [];
            foreach ($allData as $row) {
                $rowData = [];
                foreach ($headers as $h) {
                    $rowData[] = trim((string)($row[$h['col']] ?? ''));
                }
                $hasData = false;
                foreach ($rowData as $v) { if ($v !== '') { $hasData = true; break; } }
                if ($hasData) $rows[] = $rowData;
            }

            $stats = ['success' => 0, 'skipped' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

            $santriFields = ['stambuk','nama','kelas','rayon','negara','tempat_lahir','tanggal_lahir',
                'jenis_kelamin','alamat','pondok','kepengurusan','no_sktt','no_ic','ukuran_baju',
                'keberadaan_paspor','nama_ayah','nama_ibu','no_ayah','no_ibu','no_hp_alternatif','aktif'];
            $pasporFields = ['no_paspor','exp_paspor','tempat_paspor','tgl_paspor'];
            $itasFields = ['no_itas','exp_itas','level_itas'];
            $kdsToSync = [];
            $instansiIdUtama = IdGenerator::getSessionInstansiId();

            $db->transaction(function() use ($db, $rows, $mapping, $requiredFields, $duplicateMode, $santriFields, $pasporFields, $itasFields, &$stats, &$kdsToSync, $instansiIdUtama) {
                foreach ($rows as $ri => $row) {
                    $rowNum = $ri + 2;

                    // Build mapped data
                    $mapped = [];
                    $capitalizeFields = ['nama', 'kelas', 'rayon', 'negara', 'tempat_lahir', 'pondok', 'kepengurusan', 'tempat_paspor'];
                    foreach ($mapping as $excelIdx => $dbField) {
                        if ($dbField && $dbField !== '' && $dbField !== '-') {
                            $val = trim((string)($row[(int)$excelIdx] ?? ''));
                            if (in_array($dbField, $capitalizeFields) && $val !== '') {
                                $val = ucwords(strtolower($val));
                            }
                            $mapped[$dbField] = $val;
                        }
                    }

                    $namaSantri = $mapped['nama'] ?? "Tanpa Nama";

                    // Validate required fields
                    $missingFields = [];
                    foreach ($requiredFields as $rf) {
                        if (empty($mapped[$rf])) {
                            $missingFields[] = $rf;
                        }
                    }
                    if (!empty($missingFields)) {
                        $stats['failed']++;
                        $stats['errors'][] = "Baris $rowNum ($namaSantri): Gagal karena field wajib kosong (" . implode(', ', $missingFields) . ")";
                        continue;
                    }

                    // Check duplicate by stambuk
                    $rawStambuk = (string)($mapped['stambuk'] ?? '0');
                    $stambuk = (int)preg_replace('/[^0-9]/', '', $rawStambuk);
                    $mapped['stambuk'] = $stambuk; // update mapped value to the cleaned one

                    $existingKds = null;
                    if ($stambuk > 0) {
                        $existingKds = $db->createCommand("SELECT kds FROM master_santri WHERE stambuk = :s", [':s' => $stambuk])->queryScalar();
                    }

                    // Fallback: Check duplicate by Nama (Nama Lengkap)
                    if (!$existingKds && !empty($namaSantri) && $namaSantri !== 'Tanpa Nama') {
                        $existingKds = $db->createCommand("SELECT kds FROM master_santri WHERE nama = :n", [':n' => $namaSantri])->queryScalar();
                    }

                    if ($existingKds && $duplicateMode === 'skip') {
                        $stats['skipped']++;
                        $stats['errors'][] = "Baris $rowNum ($namaSantri): Dilewati (Sudah ada di database).";
                        continue;
                    }

                    // Prepare santri data
                    $santriData = [];
                    foreach ($santriFields as $sf) {
                        if (isset($mapped[$sf])) {
                            $santriData[$sf] = $mapped[$sf];
                        }
                    }

                    // Auto-determine kewarganegaraan
                    $negara = $santriData['negara'] ?? 'Indonesia';
                    $kewarganegaraan = 'WNA';
                    if (strtolower(trim($negara)) === 'indonesia') {
                        $kewarganegaraan = 'WNI';
                    } elseif (empty($mapped['exp_itas'])) {
                        $kewarganegaraan = 'Affidavit';
                    }
                    $santriData['kewarganegaraan'] = $kewarganegaraan;

                    // Type casts
                    if (isset($santriData['stambuk'])) $santriData['stambuk'] = (int)$santriData['stambuk'];
                    if (isset($santriData['no_ayah'])) $santriData['no_ayah'] = (int)$santriData['no_ayah'];
                    if (isset($santriData['no_ibu'])) $santriData['no_ibu'] = (int)$santriData['no_ibu'];
                    if (isset($santriData['no_hp_alternatif'])) $santriData['no_hp_alternatif'] = (int)$santriData['no_hp_alternatif'];
                    if (isset($santriData['aktif'])) {
                        $val = strtolower(trim((string)$santriData['aktif']));
                        if ($val === 'inaktif' || $val === 'tidak' || $val === 'tidak aktif' || $val === '0' || $val === 'false' || $val === 'nonaktif') {
                            $santriData['aktif'] = 0;
                        } elseif ($val !== '') {
                            $santriData['aktif'] = 1;
                        }
                    }

                    // Handle date fields
                    if (isset($santriData['tanggal_lahir']) && $santriData['tanggal_lahir'] !== '') {
                        $santriData['tanggal_lahir'] = self::parseDate($santriData['tanggal_lahir']);
                    } else {
                        $santriData['tanggal_lahir'] = null;
                    }

                    try {
                        if ($existingKds && $duplicateMode === 'update') {
                            unset($santriData['stambuk']);
                            if (!empty($santriData)) {
                                $db->createCommand()->update('master_santri', $santriData, ['kds' => $existingKds])->execute();
                            }
                            $kds = $existingKds;
                            $stats['updated']++;
                        } else {
                            $kds = IdGenerator::generateKds($db, $instansiIdUtama);
                            $defaults = [
                                'kds' => $kds,
                                'path_foto' => '',
                                'aktif' => 1,
                                'kode' => strtoupper(substr($mapped['nama'] ?? 'XXX', 0, 3)) . date('y'),
                            ];
                            $santriData = array_merge($defaults, $santriData);
                            $db->createCommand()->insert('master_santri', $santriData)->execute();
                            $stats['success']++;
                        }
                        
                        $kdsToSync[] = $kds;

                        // Insert Paspor if mapped
                        $pasporData = [];
                        foreach ($pasporFields as $pf) {
                            if (isset($mapped[$pf]) && $mapped[$pf] !== '') {
                                $pasporData[$pf] = $mapped[$pf];
                            }
                        }
                        if (!empty($pasporData)) {
                            if ($existingKds && $duplicateMode === 'update') {
                                $db->createCommand()->update('mtb_paspor', ['aktif' => 0], ['kds' => $kds])->execute();
                            }
                            $insertPaspor = ['kds' => $kds, 'aktif' => 1];
                            if (isset($pasporData['no_paspor'])) $insertPaspor['no_paspor'] = $pasporData['no_paspor'];
                            if (isset($pasporData['exp_paspor'])) $insertPaspor['exp_paspor'] = self::parseDate($pasporData['exp_paspor']);
                            if (isset($pasporData['tempat_paspor'])) $insertPaspor['tempat_dikeluarkan'] = $pasporData['tempat_paspor'];
                            if (isset($pasporData['tgl_paspor'])) $insertPaspor['tanggal_dikeluarkan'] = self::parseDate($pasporData['tgl_paspor']);
                            $db->createCommand()->insert('mtb_paspor', $insertPaspor)->execute();
                        }

                        // Insert ITAS if mapped
                        $itasData = [];
                        foreach ($itasFields as $itf) {
                            if (isset($mapped[$itf]) && $mapped[$itf] !== '') {
                                $itasData[$itf] = $mapped[$itf];
                            }
                        }
                        if (!empty($itasData)) {
                            if ($existingKds && $duplicateMode === 'update') {
                                $db->createCommand()->update('mtb_itas', ['aktif' => 0], ['kds' => $kds])->execute();
                            }
                            $insertItas = ['kds' => $kds, 'aktif' => 1];
                            if (isset($itasData['no_itas'])) $insertItas['no_itas'] = $itasData['no_itas'];
                            if (isset($itasData['exp_itas'])) $insertItas['exp_itas'] = self::parseDate($itasData['exp_itas']);
                            if (isset($itasData['level_itas'])) $insertItas['level_itas'] = (int)$itasData['level_itas'];
                            $db->createCommand()->insert('mtb_itas', $insertItas)->execute();
                        }

                        AuditLogger::log($db, 'IMPORT', 'SANTRI', $kds, null, "Import Excel - KDS: $kds");

                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        $msg = $e->getMessage();
                        
                        // Parse common SQL errors to be more user friendly
                        if (str_contains($msg, 'cannot be null')) {
                            if (preg_match('/Column \'(.*?)\' cannot be null/', $msg, $m)) {
                                $col = $m[1];
                                $msg = "Kolom '$col' tidak boleh kosong/null.";
                            } else {
                                $msg = "Ada data wajib yang masih kosong/null.";
                            }
                        } elseif (str_contains($msg, 'Duplicate entry')) {
                            $msg = "Data ganda (duplikat).";
                        } elseif (str_contains($msg, 'Incorrect date value')) {
                            $msg = "Format tanggal tidak valid.";
                        } elseif (str_contains($msg, 'SQLSTATE')) {
                            $msg = "Terjadi kesalahan database (SQL error).";
                        }
                        
                        $stats['errors'][] = "Baris $rowNum ($namaSantri): Gagal disimpan. $msg";
                    }
                }
            });
            
            // Sinkronisasi Firebase setelah transaksi sukses
            $kdsToSync = array_unique($kdsToSync);
            foreach ($kdsToSync as $syncKds) {
                try {
                    $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $syncKds])->queryOne();
                    if ($santriDb) {
                        $latestPaspor = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds=:kds AND aktif=1 ORDER BY id DESC LIMIT 1", [':kds' => $syncKds])->queryOne();
                        FirebaseSync::syncSantri((string)$syncKds, $santriDb, null, $latestPaspor ?: null);
                    }
                } catch (\Throwable $e) {
                    error_log("Gagal sync import excel santri ke Firebase (ExecuteAction): " . $e->getMessage());
                }
            }

            // Cleanup temp file
            @unlink($tmpFile);
            unset($_SESSION['import_tmp_file'], $_SESSION['import_headers']);

            return JsonResponse::create([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }

    private static function parseDate(string $val): ?string
    {
        $val = trim($val);
        if (empty($val)) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;
        
        // Handle DD/MM/YYYY or MM/DD/YYYY
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $val, $m)) {
            $p1 = (int)$m[1];
            $p2 = (int)$m[2];
            $year = (int)$m[3];
            
            if ($p1 > 12) { // Definitely DD/MM/YYYY
                return sprintf('%04d-%02d-%02d', $year, $p2, $p1);
            } elseif ($p2 > 12) { // Definitely MM/DD/YYYY
                return sprintf('%04d-%02d-%02d', $year, $p1, $p2);
            } else {
                // Ambiguous. Let's try strtotime first.
                // PHP strtotime: '/' is MM/DD/YYYY, '-' is DD-MM-YYYY
                $ts = strtotime($val);
                if ($ts !== false) {
                    return date('Y-m-d', $ts);
                }
                // Fallback to DD/MM/YYYY if strtotime fails
                return sprintf('%04d-%02d-%02d', $year, $p2, $p1);
            }
        }
        
        if (is_numeric($val) && (int)$val > 30000 && (int)$val < 60000) {
            $unix = ((int)$val - 25569) * 86400;
            return date('Y-m-d', $unix);
        }
        $ts = strtotime($val);
        if ($ts !== false) return date('Y-m-d', $ts);
        return null;
    }
}
