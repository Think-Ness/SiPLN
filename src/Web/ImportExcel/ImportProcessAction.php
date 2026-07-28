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

final class ImportProcessAction
{
    /**
     * POST /api/import-excel/parse
     * Upload Excel, parse headers + preview rows → JSON
     */
    public function parse(
        ServerRequestInterface $request
    ): ResponseInterface {
        $files = $request->getUploadedFiles();

        if (!isset($files['excel_file']) || $files['excel_file']->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'File tidak ditemukan atau gagal diupload.'], 422);
        }

        $uploadedFile = $files['excel_file'];
        $ext = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv'], 422);
        }

        // Save to temp
        $tmpDir = sys_get_temp_dir();
        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'import_' . uniqid() . '.' . $ext;
        $uploadedFile->moveTo($tmpFile);

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);

            if (count($data) < 2) {
                @unlink($tmpFile);
                return JsonResponse::create(['success' => false, 'message' => 'File kosong atau hanya berisi header.'], 422);
            }

            // First row = headers
            $headerRow = array_shift($data);
            $headers = [];
            foreach ($headerRow as $colLetter => $val) {
                $val = trim((string)($val ?? ''));
                if ($val !== '') {
                    $headers[] = ['col' => $colLetter, 'name' => $val];
                }
            }

            // Collect preview rows (max 200)
            $rows = [];
            $rowIndex = 0;
            foreach ($data as $row) {
                if ($rowIndex >= 200) break;
                $rowData = [];
                foreach ($headers as $h) {
                    $cellVal = $row[$h['col']] ?? '';
                    $rowData[] = trim((string)($cellVal ?? ''));
                }
                // Skip completely empty rows
                $hasData = false;
                foreach ($rowData as $v) { if ($v !== '') { $hasData = true; break; } }
                if ($hasData) {
                    $rows[] = $rowData;
                    $rowIndex++;
                }
            }

            // Store tmp file path in session for execute step
            $_SESSION['import_tmp_file'] = $tmpFile;
            $_SESSION['import_headers'] = $headers;

            return JsonResponse::create([
                'success' => true,
                'headers' => array_map(fn($h) => $h['name'], $headers),
                'rows' => $rows,
                'totalRows' => count($rows),
                'fileName' => $uploadedFile->getClientFilename(),
            ]);

        } catch (\Throwable $e) {
            @unlink($tmpFile);
            return JsonResponse::create(['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/import-excel/execute
     * Execute the actual import with mapping
     */
    public function execute(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        set_time_limit(0); // Prevent timeout during bulk Firebase sync
        
        $body = json_decode((string)$request->getBody(), true);

        $mapping = $body['mapping'] ?? []; // { excelColIndex => dbFieldKey }
        $requiredFields = $body['requiredFields'] ?? []; // [dbFieldKey, ...]
        $duplicateMode = $body['duplicateMode'] ?? 'skip'; // 'skip' | 'update'
        $stambukColIndex = null;

        // Find which excel column is mapped to 'stambuk'
        foreach ($mapping as $excelIdx => $dbField) {
            if ($dbField === 'stambuk') {
                $stambukColIndex = (int)$excelIdx;
                break;
            }
        }

        // Re-read the file from session
        $tmpFile = $_SESSION['import_tmp_file'] ?? '';
        $headers = $_SESSION['import_headers'] ?? [];
        if (!$tmpFile || !file_exists($tmpFile)) {
            return JsonResponse::create(['success' => false, 'message' => 'Session expired. Silakan upload ulang.'], 422);
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
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

            // Separate field groups
            $santriFields = ['stambuk','nama','kelas','rayon','negara','tempat_lahir','tanggal_lahir',
                'jenis_kelamin','alamat','pondok','kepengurusan','no_sktt','no_ic','ukuran_baju',
                'keberadaan_paspor','nama_ayah','nama_ibu','no_ayah','no_ibu','no_hp_alternatif'];
            $pasporFields = ['no_paspor','exp_paspor','tempat_paspor','tgl_paspor'];
            $itasFields = ['no_itas','exp_itas','level_itas'];
            $kdsToSync = [];
            $instansiIdUtama = IdGenerator::getSessionInstansiId();

            $db->transaction(function() use ($db, $rows, $mapping, $requiredFields, $duplicateMode, $stambukColIndex, $santriFields, $pasporFields, $itasFields, &$stats, &$kdsToSync, $instansiIdUtama) {
                foreach ($rows as $ri => $row) {
                    $rowNum = $ri + 2; // Excel row number (1-indexed + header)

                    // Build mapped data
                    $mapped = [];
                    foreach ($mapping as $excelIdx => $dbField) {
                        if ($dbField && $dbField !== '' && $dbField !== '-') {
                            $mapped[$dbField] = $row[(int)$excelIdx] ?? '';
                        }
                    }

                    // Validate required fields
                    $missingFields = [];
                    foreach ($requiredFields as $rf) {
                        if (empty($mapped[$rf])) {
                            $missingFields[] = $rf;
                        }
                    }
                    if (!empty($missingFields)) {
                        $stats['failed']++;
                        $stats['errors'][] = "Baris $rowNum: Field wajib kosong (" . implode(', ', $missingFields) . ")";
                        continue;
                    }

                    // Check duplicate by stambuk
                    $stambuk = (int)($mapped['stambuk'] ?? 0);
                    $existingKds = null;
                    if ($stambuk > 0) {
                        $existingKds = $db->createCommand("SELECT kds FROM master_santri WHERE stambuk = :s", [':s' => $stambuk])->queryScalar();
                    }

                    if ($existingKds && $duplicateMode === 'skip') {
                        $stats['skipped']++;
                        continue;
                    }

                    // Prepare santri data
                    $santriData = [];
                    foreach ($santriFields as $sf) {
                        if (isset($mapped[$sf])) {
                            $santriData[$sf] = $mapped[$sf];
                        }
                    }

                    // Fallback PONDOK dan KEPENGURUSAN ke setting Instansi jika kosong
                    if (empty($santriData['pondok']) && !empty($_SESSION['def_pondok'])) {
                        $santriData['pondok'] = $_SESSION['def_pondok'];
                    }
                    if (empty($santriData['kepengurusan']) && !empty($_SESSION['def_kepengurusan'])) {
                        $santriData['kepengurusan'] = $_SESSION['def_kepengurusan'];
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

                    // Handle date fields
                    if (isset($santriData['tanggal_lahir']) && $santriData['tanggal_lahir'] !== '') {
                        $santriData['tanggal_lahir'] = self::parseDate($santriData['tanggal_lahir']);
                    }

                    try {
                        if ($existingKds && $duplicateMode === 'update') {
                            // Update existing
                            unset($santriData['stambuk']); // Don't update stambuk
                            if (!empty($santriData)) {
                                $db->createCommand()->update('master_santri', $santriData, ['kds' => $existingKds])->execute();
                            }
                            $kds = $existingKds;
                            $stats['updated']++;
                        } else {
                            // Insert new
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
                            $insertItas = ['kds' => $kds, 'aktif' => 1];
                            if (isset($itasData['no_itas'])) $insertItas['no_itas'] = $itasData['no_itas'];
                            if (isset($itasData['exp_itas'])) $insertItas['exp_itas'] = self::parseDate($itasData['exp_itas']);
                            if (isset($itasData['level_itas'])) $insertItas['level_itas'] = (int)$itasData['level_itas'];
                            $db->createCommand()->insert('mtb_itas', $insertItas)->execute();
                        }

                        AuditLogger::log($db, 'IMPORT', 'SANTRI', $kds, null, "Import Excel - KDS: $kds");

                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        $stats['errors'][] = "Baris $rowNum: " . $e->getMessage();
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
                    error_log("Gagal sync import excel santri ke Firebase: " . $e->getMessage());
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

    /**
     * Parse various date formats into Y-m-d
     */
    private static function parseDate(string $val): string
    {
        $val = trim($val);
        if (empty($val)) return date('Y-m-d');

        // Already Y-m-d?
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;

        // d/m/Y or d-m-Y
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $val, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        // Excel serial number
        if (is_numeric($val) && (int)$val > 30000 && (int)$val < 60000) {
            $unix = ((int)$val - 25569) * 86400;
            return date('Y-m-d', $unix);
        }

        // Try strtotime
        $ts = strtotime($val);
        if ($ts !== false) return date('Y-m-d', $ts);

        return $val;
    }
}
