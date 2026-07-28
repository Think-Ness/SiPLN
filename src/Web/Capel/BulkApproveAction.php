<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\IdGenerator;
use App\Shared\FirebaseSync;

final class BulkApproveAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        
        $ids = $body['ids'] ?? [];
        $tipeCapel = $body['tipe_capel'] ?? 'Program Penerimaan';
        $instansiIdParam = $body['instansi_id'] ?? null;

        if (empty($ids) || !is_array($ids)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $successCount = 0;
        $errorMessages = [];
        $hasDownloads = false;

        // Fetch default instansi details
        $instansiId = $instansiIdParam ?: ($_SESSION['instansi_id'] ?? 0);
        $defPondok = '';
        $defKepengurusan = '';
        $defJenisKelamin = '';

        if ($instansiId) {
            $instansi = $db->createCommand("SELECT def_pondok, def_kepengurusan, def_jenis_kelamin, path_folder, capel_mapping FROM master_instansi WHERE kode = :id", [':id' => $instansiId])->queryOne();
            if ($instansi) {
                $mappingData = json_decode((string)($instansi['capel_mapping'] ?? ''), true) ?: null;
                $defPondok = $instansi['def_pondok'] ?? '';
                $defKepengurusan = $instansi['def_kepengurusan'] ?? '';
                $defJenisKelamin = $instansi['def_jenis_kelamin'] ?? '';
                $pathFolderInstansi = $instansi['path_folder'] ?? 'capel';
            }
        }

        foreach ($ids as $id) {
            $draft = $db->createCommand("SELECT * FROM mtb_capel_draft WHERE id = :id", [':id' => $id])->queryOne();

            if (!$draft || $draft['status_approval'] !== 'Pending') {
                $errorMessages[] = "ID $id tidak valid atau sudah diproses.";
                continue;
            }

            $transaction = $db->beginTransaction();
            $newKdsForSync = []; // Menyimpan kds untuk di-sync setelah commit
            try {
                $dataJson = json_decode($draft['data_json'], true) ?: [];

                // Update status draft
                $db->createCommand("UPDATE mtb_capel_draft SET status_approval = 'Approved' WHERE id = :id", [':id' => $id])->execute();

                // Build master_santri record
                $nama = $draft['nama_lengkap'];
                
                $findVal = function($keys) use ($dataJson) {
                    foreach ($dataJson as $k => $v) {
                        foreach ($keys as $key) {
                            if (stripos($k, $key) !== false && !empty(trim((string)$v))) {
                                return trim((string)$v);
                            }
                        }
                    }
                    return null;
                };

                $parseDate = function($dateStr) {
                    if (!$dateStr) return null;
                    $dateStr = trim($dateStr);
                    // Try DD/MM/YYYY
                    $dt = \DateTime::createFromFormat('d/m/Y', $dateStr);
                    if ($dt !== false) return $dt->format('Y-m-d');
                    // Try MM/DD/YYYY or YYYY-MM-DD
                    $ts = strtotime($dateStr);
                    return $ts ? date('Y-m-d', $ts) : null;
                };

                $tanggalLahir = $parseDate(
                    ($mappingData && !empty($mappingData['col_tgllahir']) && !empty($dataJson[$mappingData['col_tgllahir']])) 
                        ? $dataJson[$mappingData['col_tgllahir']] 
                        : $findVal(['tanggal lahir', 'date of birth'])
                );

                $negara = ($mappingData && !empty($mappingData['col_kwn']) && !empty($dataJson[$mappingData['col_kwn']])) 
                            ? trim((string)$dataJson[$mappingData['col_kwn']]) 
                            : $findVal(['kewarganegaraan', 'nationality']);
                $kewarganegaraan = (strtolower((string)$negara) === 'indonesia') ? 'WNI' : 'WNA';
                
                $rawProgram = ($mappingData && !empty($mappingData['col_program']) && !empty($dataJson[$mappingData['col_program']])) 
                                ? trim((string)$dataJson[$mappingData['col_program']]) 
                                : ($findVal(['calon pelajar', 'program capel', 'pilihan program']) ?: '');
                                
                if (stripos($rawProgram, 'Persiapan') !== false || stripos($rawProgram, 'Penampungan') !== false) {
                    $tipeCapelToUse = 'Program Persiapan';
                } elseif (stripos($rawProgram, 'Penerimaan') !== false || stripos($rawProgram, 'Syawwal') !== false) {
                    $tipeCapelToUse = 'Program Penerimaan';
                } else {
                    $tipeCapelToUse = $tipeCapel; // Fallback to radio button selection
                }

                $kelasBaru = (stripos($tipeCapelToUse, 'Persiapan') !== false) ? 'CAPEL PERSIAPAN' : 'CAPEL PENERIMAAN';
                
                $tempatLahir = ($mappingData && !empty($mappingData['col_tempatlahir']) && !empty($dataJson[$mappingData['col_tempatlahir']])) 
                                ? trim((string)$dataJson[$mappingData['col_tempatlahir']]) 
                                : $findVal(['tempat lahir', 'place of birth']);
                                
                $namaAyah = ($mappingData && !empty($mappingData['col_ayah']) && !empty($dataJson[$mappingData['col_ayah']])) 
                                ? trim((string)$dataJson[$mappingData['col_ayah']]) 
                                : $findVal(['nama lengkap ayah', 'nama ayah', 'ayah', 'father', 'father name']);
                                
                $namaIbu = ($mappingData && !empty($mappingData['col_ibu']) && !empty($dataJson[$mappingData['col_ibu']])) 
                                ? trim((string)$dataJson[$mappingData['col_ibu']]) 
                                : $findVal(['nama lengkap ibu', 'nama ibu', 'ibu', 'mother', 'mother name']);
                                
                $noHpRaw = ($mappingData && !empty($mappingData['col_nohp']) && !empty($dataJson[$mappingData['col_nohp']])) 
                                ? trim((string)$dataJson[$mappingData['col_nohp']]) 
                                : $findVal(['nomor hp wali', 'wa aktif', 'phone', 'telepon']);
                $noAyah = (int)preg_replace('/[^0-9]/', '', (string)$noHpRaw);
                
                $alamatVal = ($mappingData && !empty($mappingData['col_alamat']) && !empty($dataJson[$mappingData['col_alamat']])) 
                                ? trim((string)$dataJson[$mappingData['col_alamat']]) 
                                : $findVal(['alamat rumah']);

                $santriData = [
                    'nama' => $nama,
                    'kelas' => $kelasBaru,
                    'status_santri' => $tipeCapelToUse,
                    'aktif' => '1',
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'kewarganegaraan' => $kewarganegaraan,
                    'negara' => $negara,
                    'nama_ayah' => $namaAyah,
                    'nama_ibu' => $namaIbu,
                    'no_ayah' => $noAyah,
                    'alamat' => $alamatVal,
                    'pondok' => $defPondok,
                    'kepengurusan' => $defKepengurusan,
                    'jenis_kelamin' => $defJenisKelamin,
                    'path_foto' => 'Menunggu unduhan...'
                ];
                
                $baseKode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nama) . 'XXX', 0, 3)) . date('y');
                $kode = $baseKode;
                $counter = 1;
                while ($db->createCommand("SELECT 1 FROM master_santri WHERE kode = :k", [':k' => $kode])->queryScalar()) {
                    $kode = $baseKode . sprintf('%02d', $counter);
                    $counter++;
                }
                $santriData['kode'] = $kode;

                // KDS Prefixing
                $instansiIdUtama = IdGenerator::getSessionInstansiId();
                $kds = IdGenerator::generateKds($db, $instansiIdUtama);
                $santriData['kds'] = $kds;

                $db->createCommand()->insert('master_santri', $santriData)->execute();

                $newKdsForSync[] = $kds;

                // Process Paspor
                $noPaspor = strtoupper($findVal(['nomor id paspor', 'identity number']) ?: '');
                if ($noPaspor) {
                    $tglK = $parseDate($findVal(['tanggal dikeluarkan paspor', 'date of issue']));
                    $tglB = $parseDate($findVal(['tanggal berakhir paspor', 'date of expiry']));

                    $db->createCommand()->insert('mtb_paspor', [
                        'kds' => $kds,
                        'no_paspor' => $noPaspor,
                        'tempat_dikeluarkan' => $findVal(['tempat dikeluarkan paspor', 'issuing office']) ?: '-',
                        'tanggal_dikeluarkan' => $tglK,
                        'exp_paspor' => $tglB,
                        'aktif' => 1,
                        'path_file' => 'Menunggu unduhan...'
                    ])->execute();
                }

                // Process Files (queue them)
                // Process Files (queue them)
                $fileCategories = [
                    'Scan ID Paspor' => ['scan id paspor', 'passport'],
                    'Scan IC Santri' => ['scan ic (kartu identitas) santri', 'scan ic santri', 'kartu identitas santri', 'scan of id card'],
                    'Scan IC Ayah' => ['scan ic ayah', 'father\'s scan of id card', 'father\'s scan'],
                    'Scan IC Ibu' => ['scan ic ibu', 'mother\'s scan of id card', 'mother\'s scan'],
                    'Surat Beranak' => ['surat beranak', 'surat kelahiran', 'birth certificate'],
                    'Pas Foto' => ['pas foto', 'pasfoto', 'recent photograph', 'photograph'],
                    'Curriculum Vitae' => ['curriculum vitae', 'cv'],
                    'Sertifikat Vaksin' => ['scan sertifikat vaksin', 'vaccine'],
                    'Asuransi Kesehatan' => ['asuransi kesehatan', 'health insurance'],
                    'Ijazah / Rapor' => ['scan ijazah', 'rapor terakhir', 'diploma', 'report card'],
                    'Surat Sehat' => ['kesanggupan sehat', 'surat sehat', 'bebas penyakit menular', 'certificate of health'],
                    'Surat Kesanggupan Biaya' => ['kesanggupan biaya', 'financial capability'],
                    'Affidavit' => ['affidavit'],
                    'Surat Pelajar Asing' => ['pelajar asing']
                ];

                foreach ($dataJson as $header => $val) {
                    if (is_string($val) && str_contains($val, 'drive.google.com')) {
                        // Find matching category
                        $jenisDokumen = 'Dokumen Pendaftaran Lainnya';
                        $namaCleanFolder = preg_replace('/[^A-Za-z0-9]/', '_', $nama);
                        $santriFolder = rtrim($namaCleanFolder, '_') . '_' . $santriData['kode'];
                        $kategoriFolder = "berkas/$santriFolder"; // Default folder per santri
                        
                        foreach ($fileCategories as $catName => $keywords) {
                            foreach ($keywords as $kw) {
                                if (stripos($header, $kw) !== false) {
                                    $jenisDokumen = $catName;
                                    if ($catName === 'Pas Foto') $kategoriFolder = 'foto santri';
                                    elseif ($catName === 'Scan ID Paspor') $kategoriFolder = 'paspor';
                                    break 2;
                                }
                            }
                        }

                        $kategoriFolder = ($pathFolderInstansi ?? 'capel') . '/' . $kategoriFolder;

                        $links = array_map('trim', explode(',', $val));
                        foreach ($links as $link) {
                            if (preg_match('/id=([a-zA-Z0-9_-]+)/', $link, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
                                $fileId = $matches[1];
                                $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;
                                
                                $db->createCommand()->insert('capel_download_queue', [
                                    'draft_id' => $id,
                                    'kode_santri' => $santriData['kode'],
                                    'file_url' => $downloadUrl,
                                    'kategori_folder' => $kategoriFolder,
                                    'jenis_dokumen' => $jenisDokumen,
                                    'instansi_id' => $instansiId
                                ])->execute();
                                $hasDownloads = true;
                            }
                        }
                    }
                }

                // Update draft with kds_approved
                $db->createCommand("UPDATE mtb_capel_draft SET kds_approved = :kds WHERE id = :id", [
                    ':kds' => $kds,
                    ':id' => $draft['id']
                ])->execute();

                // DUAL-WRITE TO FIREBASE
                $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                if ($santriDb) {
                    $latestPaspor = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds=:kds AND aktif=1 ORDER BY id DESC LIMIT 1", [':kds' => $kds])->queryOne();
                    \App\Shared\FirebaseSync::syncSantri((string)$kds, $santriDb, null, $latestPaspor ?: null);
                }
                $transaction->commit();
                $successCount++;

                // Sync ke Firebase setelah commit sukses
                foreach ($newKdsForSync as $syncKds) {
                    try {
                        $newSantri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $syncKds])->queryOne();
                        if ($newSantri) \App\Shared\FirebaseSync::syncSantri((string)$syncKds, $newSantri, $db);
                    } catch (\Throwable $e) {
                        error_log("Gagal sync bulk santri ke Firebase: " . $e->getMessage());
                    }
                }

            } catch (\Throwable $e) {
                if (isset($transaction) && $transaction->isActive()) {
                    $transaction->rollBack();
                }
                $errorMessages[] = "Gagal memproses ID $id: " . $e->getMessage();
            }
        }
        
        // Trigger background worker if there are downloads
        if ($hasDownloads) {
            $workerPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'download_worker.php';
            $phpBin = PHP_BINARY;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $phpExe = dirname(ini_get('extension_dir')) . DIRECTORY_SEPARATOR . 'php.exe';
                $phpBin = file_exists($phpExe) ? $phpExe : 'php';
                try {
                    $wsh = new \COM("WScript.Shell");
                    $wsh->Run("\"$phpBin\" \"$workerPath\"", 0, false);
                } catch (\Throwable $e) {
                    pclose(popen("start /B \"\" \"$phpBin\" \"$workerPath\" > NUL", "r"));
                }
            } else {
                exec("\"$phpBin\" \"$workerPath\" > /dev/null 2>&1 &");
            }
        }

        if ($successCount > 0) {
            $msg = "Berhasil menerima $successCount data CAPEL.";
            if (!empty($errorMessages)) {
                $msg .= " Namun ada beberapa error: " . implode(' | ', $errorMessages);
            }
            return JsonResponse::create(['success' => true, 'message' => $msg]);
        } else {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal memproses semua data: ' . implode(' | ', $errorMessages)]);
        }
    }
}
