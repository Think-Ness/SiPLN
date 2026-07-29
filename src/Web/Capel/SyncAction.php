<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Google\Client;
use Google\Service\Sheets;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class SyncAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        @session_start();
        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        if ($isSuperAdmin && !empty($body['instansi_id'])) {
            $instansiId = (int)$body['instansi_id'];
        }

        $spreadsheetId = $body['spreadsheet_id'] ?? '';
        $range = $body['sheet_range'] ?? 'Form Responses 1';
        $colEmailConfig = $body['col_email'] ?? '';
        $colNamaConfig = $body['col_nama'] ?? '';
        $colTglLahirConfig = $body['col_tgllahir'] ?? '';
        
        $colKwnConfig = $body['col_kwn'] ?? '';
        $colTempatLahirConfig = $body['col_tempatlahir'] ?? '';
        $colAyahConfig = $body['col_ayah'] ?? '';
        $colIbuConfig = $body['col_ibu'] ?? '';
        $colNoHpConfig = $body['col_nohp'] ?? '';
        $colAlamatConfig = $body['col_alamat'] ?? '';
        $colNoPasporConfig = $body['col_no_paspor'] ?? '';
        $colExpPasporConfig = $body['col_exp_paspor'] ?? '';
        $colTempatPasporConfig = $body['col_tempat_paspor'] ?? '';
        $colTglKeluaranPasporConfig = $body['col_tgl_keluaran_paspor'] ?? '';
        $colProgramConfig = $body['col_program'] ?? '';

        if (empty($spreadsheetId)) {
            return JsonResponse::create(['success' => false, 'message' => 'Spreadsheet ID tidak boleh kosong.'])->withStatus(400);
        }

        $credentialsPath = dirname(__DIR__, 3) . '/config/google_service_account.json';
        $encPath = dirname(__DIR__, 3) . '/config/gcp_key.txt';
        
        // Auto-decode obfuscated key
        if (!file_exists($credentialsPath) && file_exists($encPath)) {
            @file_put_contents($credentialsPath, base64_decode(file_get_contents($encPath)));
        }

        if (!file_exists($credentialsPath)) {
            return JsonResponse::create(['success' => false, 'message' => 'File google_service_account.json tidak ditemukan di folder config/! Silakan ikuti panduan pengaturan terlebih dahulu.'])->withStatus(400);
        }

        try {
            $client = new Client();
            $client->setApplicationName('SI Santri Capel Sync');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig($credentialsPath);
            // $client->setAccessType('offline');

            $service = new Sheets($client);
            $fetchRange = str_contains($range, '!') ? $range : "'{$range}'";
            $response = $service->spreadsheets_values->get($spreadsheetId, $fetchRange);
            $values = $response->getValues();

            if (empty($values) || count($values) < 2) {
                return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data ditemukan di Spreadsheet.']);
            }

            $headers = array_shift($values);
            // lowercase all headers for matching
            $headersLower = array_map('strtolower', $headers);
            
            $inserted = 0;
            $skipped = 0;

            $updated = 0;

            foreach ($values as $row) {
                // Determine fields based on header names
                $email = '';
                $nama = 'Tanpa Nama';
                $tglLahir = null;
                $fileLinks = [];
                $dataJson = [];

                foreach ($headersLower as $index => $headerName) {
                    $val = $row[$index] ?? '';
                    
                    // Title case formating except for email and links
                    if (is_string($val) && !str_contains($val, 'drive.google.com') && !str_contains($headerName, 'email')) {
                        $val = ucwords(strtolower(trim($val)));
                    }
                    
                    // Handle Exact Column Mapping
                    $originalHeader = $headers[$index];
                    
                    if ($colEmailConfig !== '' && $originalHeader === $colEmailConfig) {
                        $email = strtolower(trim($val));
                    }
                    if ($colNamaConfig !== '' && $originalHeader === $colNamaConfig) {
                        $nama = $val;
                    }
                    if ($colTglLahirConfig !== '' && $originalHeader === $colTglLahirConfig) {
                        $dateStr = trim((string)$val);
                        
                        // Smart Split: If mapped to the same column, separate Place and Date
                        if ($colTempatLahirConfig === $colTglLahirConfig && $dateStr !== '') {
                            if (str_contains($dateStr, ',')) {
                                $parts = explode(',', $dateStr);
                                $possibleDate = trim(array_pop($parts));
                                if (preg_match('/\d/', $possibleDate)) {
                                    $dateStr = $possibleDate;
                                    $val = trim(implode(',', $parts));
                                }
                            } else {
                                if (preg_match('/^([^\d]+)\s+(\d.*)$/', $dateStr, $matches)) {
                                    $val = trim($matches[1]);
                                    $dateStr = trim($matches[2]);
                                }
                            }
                        }

                        if ($dateStr) {
                            $dt = \DateTime::createFromFormat('d/m/Y', $dateStr);
                            if ($dt !== false) {
                                $tglLahir = $dt->format('Y-m-d');
                            } else {
                                $ts = strtotime($dateStr);
                                if ($ts) $tglLahir = date('Y-m-d', $ts);
                            }
                        }
                    }
                    
                    // Fallback to old heuristic if mapping is not configured
                    if ($colEmailConfig === '' && $colNamaConfig === '') {
                        if (str_contains($headerName, 'email')) {
                            if (empty($email) || !str_contains($headerName, 'bila ada')) {
                                $email = strtolower(trim($val));
                            }
                        } elseif ((str_contains($headerName, 'nama lengkap') || str_contains($headerName, 'full name') || str_contains($headerName, 'nama calon santri') || str_contains($headerName, 'nama santri'))
                                && !str_contains($headerName, 'ayah') && !str_contains($headerName, 'ibu') && !str_contains($headerName, 'father') && !str_contains($headerName, 'mother') && !str_contains($headerName, 'wali')) {
                            $nama = $val;
                        } elseif ((str_contains($headerName, 'tanggal lahir') || str_contains($headerName, 'tgl lahir') || str_contains($headerName, 'date of birth') || str_contains($headerName, 'dob'))
                                && !str_contains($headerName, 'ayah') && !str_contains($headerName, 'ibu')) {
                            $dateStr = trim((string)$val);
                            if ($dateStr) {
                                $dt = \DateTime::createFromFormat('d/m/Y', $dateStr);
                                if ($dt !== false) {
                                    $tglLahir = $dt->format('Y-m-d');
                                } else {
                                    $ts = strtotime($dateStr);
                                    if ($ts) $tglLahir = date('Y-m-d', $ts);
                                }
                            }
                        }
                    }

                    if (is_string($val) && str_contains($val, 'drive.google.com')) {
                        $links = array_map('trim', explode(',', $val));
                        $fileLinks = array_merge($fileLinks, $links);
                    }
                    
                    // Put everything in JSON
                    if (!isset($originalHeader)) {
                         $originalHeader = $headers[$index];
                    }
                    $dataJson[$originalHeader] = $val;
                }

                $formTimestamp = $row[0] ?? date('Y-m-d H:i:s'); 
                
                $existing = $db->createCommand("SELECT id, status_approval FROM mtb_capel_draft WHERE nama_lengkap = :n AND email = :e AND instansi_id = :inst", [
                    ':n' => $nama,
                    ':e' => $email,
                    ':inst' => $instansiId ?: null
                ])->queryOne();

                if ($existing) {
                    // Update the existing record if it is still Pending
                    if ($existing['status_approval'] === 'Pending') {
                        $db->createCommand()->update('mtb_capel_draft', [
                            'tanggal_lahir' => $tglLahir,
                            'data_json' => json_encode($dataJson),
                            'file_links' => json_encode($fileLinks),
                        ], ['id' => $existing['id']])->execute();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                // Insert
                $db->createCommand()->insert('mtb_capel_draft', [
                    'timestamp' => date('Y-m-d H:i:s', strtotime((string)($row[0] ?? 'now')) ?: time()),
                    'email' => $email,
                    'nama_lengkap' => $nama,
                    'tanggal_lahir' => $tglLahir,
                    'data_json' => json_encode($dataJson),
                    'file_links' => json_encode($fileLinks),
                    'status_approval' => 'Pending',
                    'instansi_id' => $instansiId ?: null
                ])->execute();
                
                $inserted++;
            }

            if ($instansiId && !empty($spreadsheetId)) {
                $mappingData = json_encode([
                    'col_email' => $colEmailConfig,
                    'col_nama' => $colNamaConfig,
                    'col_tgllahir' => $colTglLahirConfig,
                    'col_kwn' => $colKwnConfig,
                    'col_tempatlahir' => $colTempatLahirConfig,
                    'col_ayah' => $colAyahConfig,
                    'col_ibu' => $colIbuConfig,
                    'col_nohp' => $colNoHpConfig,
                    'col_alamat' => $colAlamatConfig,
                    'col_no_paspor' => $colNoPasporConfig,
                    'col_exp_paspor' => $colExpPasporConfig,
                    'col_tempat_paspor' => $colTempatPasporConfig,
                    'col_tgl_keluaran_paspor' => $colTglKeluaranPasporConfig,
                    'col_program' => $colProgramConfig
                ]);
                $headersJson = json_encode($headers);
                $db->createCommand("UPDATE master_instansi SET spreadsheet_id = :sid, capel_spreadsheet_range = :range, capel_mapping = :map, capel_spreadsheet_headers = :headers WHERE kode = :id", [
                    ':sid' => $spreadsheetId,
                    ':range' => $range,
                    ':map' => $mappingData,
                    ':headers' => $headersJson,
                    ':id' => $instansiId
                ])->execute();
            }

            return JsonResponse::create([
                'success' => true, 
                'message' => "Sinkronisasi selesai. $inserted data baru, $updated data diperbarui (Pending), $skipped data dilewati."
            ]);

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            // Simplify common Google API errors
            if (str_contains($msg, 'Requested entity was not found')) {
                $msg = "Spreadsheet tidak ditemukan atau Robot belum diberi akses 'Viewer' ke file tersebut.";
            } elseif (str_contains($msg, 'Unable to parse range')) {
                $msg = "Nama/Range Sheet tidak valid. Pastikan nama tab benar (contoh: Form Responses 1).";
            }
            
            return JsonResponse::create([
                'success' => false, 
                'message' => 'Gagal: ' . $msg
            ]);
        }
    }
}
