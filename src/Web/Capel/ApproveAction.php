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
                $tipeCapelToUse = 'Program Persiapan';
            } elseif (stripos($rawProgram, 'Penerimaan') !== false || stripos($rawProgram, 'Syawwal') !== false) {
                $tipeCapelToUse = 'Program Penerimaan';
            } else {
                $tipeCapelToUse = $tipeCapel; // Fallback to radio button selection
            }

            $kelasBaru = (stripos($tipeCapelToUse, 'Persiapan') !== false) ? 'CAPEL PERSIAPAN' : 'CAPEL PENERIMAAN';
            // Tambahkan KDS Prefixing
            $instansiId = IdGenerator::getSessionInstansiId();
            $kds = IdGenerator::generateKds($db, $instansiId);

            // Insert into master_santri
            $db->createCommand()->insert('master_santri', [
                'kds' => $kds,
                'nama' => $draft['nama_lengkap'],
                'kelas' => $kelasBaru,
                'status_santri' => $tipeCapelToUse, // 'Program Penerimaan' or 'Program Persiapan'
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

            // $fileLinks is undefined in the original ApproveAction.php, so we'll skip the broken file loop
            // Alternatively, we can just leave it as it was but safely skip if not defined
            $fileLinks = [];
                // Example Google Drive link: https://drive.google.com/open?id=123ABCxyz
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $link, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
                    $fileId = $matches[1];
                    $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;
                    
                    $uploadDir = dirname(__DIR__, 4) . '/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    
                    $filename = "CAPEL_" . $kds . "_" . $fileId . ".pdf"; // Assumption: pdf or jpg. We can guess by contents later.
                    $filepath = $uploadDir . $filename;
                    
                    // Download file
                    $content = @file_get_contents($downloadUrl);
                    if ($content) {
                        file_put_contents($filepath, $content);
                        
                        // Insert to pemberkasan_santri
                        $db->createCommand()->insert('pemberkasan_santri', [
                            'kds' => $kds,
                            'jenis_dokumen' => 'Dokumen Pendaftaran',
                            'path_file' => 'uploads/' . $filename,
                            'keterangan' => 'Didownload otomatis dari Google Form'
                        ])->execute();
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

            // PUSH KE FIREBASE (Dual-Write)
            try {
                $newSantri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                if ($newSantri) FirebaseSync::syncSantri((string)$kds, $newSantri, $db);
            } catch (\Throwable $e) {
                error_log("Gagal sync santri ke Firebase di ApproveAction: " . $e->getMessage());
            }

            return JsonResponse::create(['success' => true, 'message' => 'Data Capel berhasil disetujui dan masuk ke master Santri.']);

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
