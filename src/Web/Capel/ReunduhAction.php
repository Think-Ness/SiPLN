<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\JsonResponse;
use App\Shared\UploadPath;

final class ReunduhAction
{
    public function __invoke(
        ServerRequestInterface $request,
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $idArg = $currentRoute->getArgument('id');
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true) ?: [];
        }

        $ids = [];
        if ($idArg) {
            $ids[] = (int)$idArg;
        } elseif (!empty($body['ids']) && is_array($body['ids'])) {
            $ids = array_map('intval', $body['ids']);
        } elseif (!empty($body['id'])) {
            $ids[] = (int)$body['id'];
        }

        if (empty($ids)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data calon pelajar yang dipilih.'], 400);
        }

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

        $queuedCount = 0;
        $santriCount = 0;

        foreach ($ids as $draftId) {
            $draft = $db->createCommand("SELECT * FROM mtb_capel_draft WHERE id = :id", [':id' => $draftId])->queryOne();
            if (!$draft) continue;

            $dataJson = json_decode((string)$draft['data_json'], true) ?: [];
            if (empty($dataJson)) continue;

            // Cari kode santri
            $kdsApproved = $draft['kds_approved'] ?? null;
            $kodeSantri = null;
            $instansiId = !empty($draft['instansi_id']) ? (int)$draft['instansi_id'] : 1;

            if ($kdsApproved) {
                $santri = $db->createCommand("SELECT kode, kepengurusan FROM master_santri WHERE kds = :kds", [':kds' => $kdsApproved])->queryOne();
                if ($santri) {
                    $kodeSantri = $santri['kode'];
                }
            }

            if (!$kodeSantri) {
                // Fallback cari berdasarkan nama
                $santri = $db->createCommand("SELECT kode FROM master_santri WHERE nama = :nama ORDER BY kds DESC LIMIT 1", [':nama' => $draft['nama_lengkap']])->queryOne();
                if ($santri) {
                    $kodeSantri = $santri['kode'];
                }
            }

            if (!$kodeSantri) {
                $baseKode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $draft['nama_lengkap']) . 'XXX', 0, 3)) . date('y');
                $kodeSantri = $baseKode;
            }

            // Hapus antrean lama untuk draft ini agar tidak terjadi penumpukan antrean
            $db->createCommand("DELETE FROM capel_download_queue WHERE draft_id = :d OR kode_santri = :k", [
                ':d' => $draftId,
                ':k' => $kodeSantri
            ])->execute();

            $santriHasFiles = false;

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

                    $links = array_map('trim', explode(',', $val));
                    $linkIndex = 1;
                    $multipleLinks = count($links) > 1;

                    foreach ($links as $link) {
                        if (preg_match('/id=([a-zA-Z0-9_-]+)/', $link, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
                            $fileId = $matches[1];
                            $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;
                            $docLabel = $jenisDokumen . ($multipleLinks ? " Part $linkIndex" : "");

                            $db->createCommand()->insert('capel_download_queue', [
                                'draft_id' => $draftId,
                                'kode_santri' => $kodeSantri,
                                'file_url' => $downloadUrl,
                                'kategori_folder' => 'berkas',
                                'jenis_dokumen' => $docLabel,
                                'instansi_id' => $instansiId,
                                'status' => 'pending'
                            ])->execute();

                            $queuedCount++;
                            $santriHasFiles = true;
                            $linkIndex++;
                        }
                    }
                }
            }

            if ($santriHasFiles) {
                $santriCount++;
            }
        }

        // Trigger background download worker
        if ($queuedCount > 0) {
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

            return JsonResponse::create([
                'success' => true,
                'message' => "Proses re-unduh berhasil dimulai untuk $santriCount santri ($queuedCount berkas).",
                'queued_count' => $queuedCount
            ]);
        }

        return JsonResponse::create([
            'success' => false,
            'message' => 'Tidak ditemukan tautan berkas Google Drive yang valid pada data santri terpilih.'
        ]);
    }
}
