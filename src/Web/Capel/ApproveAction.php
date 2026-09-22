<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\JsonResponse;
use App\Shared\IdGenerator;
use App\Shared\FirebaseSync;
use App\Shared\UploadPath;

final class ApproveAction
{
    public function __invoke(
        ServerRequestInterface $request,
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = $currentRoute->getArgument('id');
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        $tipeCapel = $body['tipe_capel'] ?? 'Program Penerimaan';

        $draft = $db->createCommand("SELECT * FROM mtb_capel_draft WHERE id = :id", [':id' => $id])->queryOne();

        if (!$draft || $draft['status_approval'] !== 'Pending') {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak valid atau sudah diproses.']);
        }

        $transaction = $db->beginTransaction();
        try {
            // Update status draft
            $db->createCommand("UPDATE mtb_capel_draft SET status_approval = 'Approved' WHERE id = :id", [':id' => $id])->execute();

            $dataJson = json_decode($draft['data_json'], true) ?: [];

            $rawProgram = $this->findValue($dataJson, ['calon pelajar', 'program capel', 'pilihan program']) ?: '';
            if (stripos($rawProgram, 'Persiapan') !== false || stripos($rawProgram, 'Penampungan') !== false) {
                $tipeCapelToUse = 'Capel Penampungan';
            } elseif (stripos($rawProgram, 'Penerimaan') !== false || stripos($rawProgram, 'Syawwal') !== false) {
                $tipeCapelToUse = 'Capel Syawwal';
            } else {
                // Fallback to radio button selection mapped to enum
                if (stripos($tipeCapel, 'Persiapan') !== false || stripos($tipeCapel, 'Penampungan') !== false) {
                    $tipeCapelToUse = 'Capel Penampungan';
                } else {
                    $tipeCapelToUse = 'Capel Syawwal';
                }
            }

            $kelasBaru = (stripos($tipeCapelToUse, 'Penampungan') !== false) ? 'CAPEL PERSIAPAN' : 'CAPEL PENERIMAAN';
            // Tambahkan KDS Prefixing
            $instansiId = IdGenerator::getSessionInstansiId();
            $kds = IdGenerator::generateKds($db, $instansiId);

            // Insert into master_santri
            $db->createCommand()->insert('master_santri', [
                'kds' => $kds,
                'nama' => $draft['nama_lengkap'],
                'kelas' => $kelasBaru,
                'status_santri' => 'Aktif',
                'aktif' => '1',
                // extract other basic fields from dataJson if possible
                'tempat_lahir' => $this->findValue($dataJson, ['tempat_lahir', 'tempat lahir', 'place of birth']),
                'kewarganegaraan' => $this->findValue($dataJson, ['kewarganegaraan', 'nationality']),
                'kode' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $draft['nama_lengkap']) . 'XXX', 0, 3)) . date('y'),
            ])->execute();

            $parseDate = function($dateStr) {
                if (!$dateStr) return null;
                $dateStr = trim($dateStr);
                $dt = \DateTime::createFromFormat('d/m/Y', $dateStr);
                if ($dt !== false) return $dt->format('Y-m-d');
                $ts = strtotime($dateStr);
                return $ts ? date('Y-m-d', $ts) : null;
            };

            // Process Paspor
            $noPaspor = strtoupper($this->findValue($dataJson, ['nomor id paspor', 'identity number']) ?: '');
            if ($noPaspor) {
                $tglK = $parseDate($this->findValue($dataJson, ['tanggal dikeluarkan paspor', 'date of issue']));
                $tglB = $parseDate($this->findValue($dataJson, ['tanggal berakhir paspor', 'date of expiry']));

                $db->createCommand()->insert('mtb_paspor', [
                    'kds' => $kds,
                    'no_paspor' => $noPaspor,
                    'tempat_dikeluarkan' => $this->findValue($dataJson, ['tempat dikeluarkan paspor', 'issuing office']) ?: '-',
                    'tanggal_dikeluarkan' => $tglK,
                    'exp_paspor' => $tglB,
                    'aktif' => 1,
                    'path_file' => 'Menunggu unduhan...'
                ])->execute();
            }

            // Process Files (queue them)
            $fileCategories = [
                'Scan ID Paspor' => ['scan id paspor', 'paspor', 'passport'],
                'Scan IC Santri' => ['scan ic (kartu identitas) santri', 'scan ic santri', 'kartu identitas santri', 'scan of id card', 'ktp santri', 'ic santri', 'kartu identitas', 'identity card'],
                'Scan IC Ayah' => ['scan ic ayah', 'father\'s scan of id card', 'father\'s scan', 'ktp ayah', 'ic ayah', 'identitas ayah'],
                'Scan IC Ibu' => ['scan ic ibu', 'mother\'s scan of id card', 'mother\'s scan', 'ktp ibu', 'ic ibu', 'identitas ibu'],
                'Surat Beranak' => ['surat beranak', 'surat kelahiran', 'birth certificate', 'akta lahir', 'akta kelahiran'],
                'Pas Foto' => ['pas foto', 'pasfoto', 'recent photograph', 'photograph', 'foto', 'photo'],
                'Curriculum Vitae' => ['curriculum vitae', 'cv', 'riwayat hidup'],
                'Sertifikat Vaksin' => ['scan sertifikat vaksin', 'sertifikat vaksin', 'kartu vaksin', 'vaccine', 'vaksin'],
                'Asuransi Kesehatan' => ['asuransi kesehatan', 'health insurance', 'asuransi', 'medical insurance'],
                'Ijazah / Rapor' => ['scan ijazah', 'rapor terakhir', 'diploma', 'report card', 'ijazah', 'rapor', 'transkrip', 'skl', 'skhun', 'certificate of education'],
                'Surat Sehat' => ['kesanggupan sehat', 'surat sehat', 'bebas penyakit menular', 'certificate of health', 'surat keterangan sehat', 'medical check up'],
                'Surat Kesanggupan Biaya' => ['kesanggupan biaya', 'financial capability', 'surat kesanggupan', 'financial statement', 'pernyataan biaya'],
                'Affidavit' => ['affidavit', 'kewarganegaraan ganda'],
                'Surat Pelajar Asing' => ['pelajar asing', 'izin belajar', 'rekomendasi kementerian'],
                'Kartu Keluarga' => ['kartu keluarga', 'kk', 'family card'],
                'Surat Rekomendasi' => ['rekomendasi', 'surat rekomendasi', 'recommendation letter'],
                'Surat Pernyataan' => ['pernyataan', 'surat pernyataan', 'statement letter'],
                'ITAS' => ['itas', 'visa', 'izin tinggal', 'kitas']
            ];

            $hasDownloads = false;
            $kodeSantri = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $draft['nama_lengkap']) . 'XXX', 0, 3)) . date('y');

            foreach ($dataJson as $header => $val) {
                if (is_string($val) && str_contains($val, 'drive.google.com')) {
                    $matchedCat = null;
                    foreach ($fileCategories as $catName => $keywords) {
                        foreach ($keywords as $kw) {
                            if (stripos((string)$header, $kw) !== false) {
                                $matchedCat = $catName;
                                break 2;
                            }
                        }
                    }

                    if ($matchedCat !== null) {
                        $jenisDokumen = $matchedCat;
                    } else {
                        $rawHeader = trim((string)$header);
                        $cleanTitle = trim(preg_replace('/^(unggah|upload|silakan lampirkan|lampiran|scan|dokumen)\s+/i', '', $rawHeader));
                        $cleanTitle = ucwords(preg_replace('/[^a-zA-Z0-9\s\-_]/', ' ', $cleanTitle));
                        $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle));
                        $jenisDokumen = !empty($cleanTitle) ? $cleanTitle : 'Dokumen Pendaftaran';
                    }

                    $kategoriFolder = 'berkas';
                    if ($jenisDokumen === 'Pas Foto') {
                        $kategoriFolder = 'foto santri';
                    } elseif ($jenisDokumen === 'Scan ID Paspor') {
                        $kategoriFolder = 'paspor';
                    } elseif ($jenisDokumen === 'ITAS') {
                        $kategoriFolder = 'itas';
                    }

                    $links = array_map('trim', explode(',', $val));
                    $linkIndex = 1;
                    $multipleLinks = count($links) > 1;

                    foreach ($links as $link) {
                        if (preg_match('/id=([a-zA-Z0-9_-]+)/', $link, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
                            $fileId = $matches[1];
                            $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;
                            $docLabel = $jenisDokumen . ($multipleLinks ? " Part $linkIndex" : "");

                            $db->createCommand()->insert('capel_download_queue', [
                                'draft_id' => $id,
                                'kode_santri' => $kodeSantri,
                                'file_url' => $downloadUrl,
                                'kategori_folder' => $kategoriFolder,
                                'jenis_dokumen' => $docLabel,
                                'instansi_id' => $instansiId,
                                'status' => 'pending'
                            ])->execute();
                            $hasDownloads = true;
                            $linkIndex++;
                        }
                    }
                }
            }

            // Update draft
            $db->createCommand("UPDATE mtb_capel_draft SET status_approval = 'Approved', kds_approved = :kds WHERE id = :id", [
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

            // Trigger background download worker
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

            return JsonResponse::create(['success' => true, 'message' => 'Data Capel berhasil disetujui dan masuk ke antrean unduh berkas.']);

        } catch (\Throwable $e) {
            if (isset($transaction) && $transaction->isActive()) {
                $transaction->rollBack();
            }
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function findValue(array $json, array $keys): ?string
    {
        foreach ($json as $k => $v) {
            foreach ($keys as $key) {
                if (stripos($k, $key) !== false) {
                    return (string)$v;
                }
            }
        }
        return null;
    }
}
