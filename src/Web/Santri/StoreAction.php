<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;
use App\Shared\IdGenerator;

final class StoreAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $data = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        if (isset($data['stambuk'])) {
            $data['stambuk'] = preg_replace('/[^0-9]/', '', (string)$data['stambuk']);
        }

        $required = ['stambuk', 'nama', 'kelas', 'negara'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                return JsonResponse::create(['success' => false, 'message' => "Field '$f' wajib diisi"], 422);
            }
        }

        try {
            $negara = $data['negara'] ?? 'Indonesia';
            $exp_itas = $data['exp_itas'] ?? '';
            $kewarganegaraan = $data['kewarganegaraan'] ?? '';
            if (empty($kewarganegaraan)) {
                $kewarganegaraan = 'WNA';
                if (strtolower(trim($negara)) === 'indonesia') {
                    $kewarganegaraan = 'WNI';
                } elseif (empty($exp_itas)) {
                    $kewarganegaraan = 'Affidavit';
                }
            }

            // Generate KDS unik berbasis Branch Prefix (instansi_id)
            $instansiId = IdGenerator::getSessionInstansiId();
            $kds = IdGenerator::generateKds($db, $instansiId);

            $db->createCommand()->insert('master_santri', [
                'kds'             => $kds,
                'stambuk'         => (int)$data['stambuk'],
                'nama'            => $data['nama'],
                'kelas'           => $data['kelas'],
                'rayon'           => $data['rayon'] ?? '',
                'negara'          => $negara,
                'kewarganegaraan' => $kewarganegaraan,
                'tempat_lahir'    => $data['tempat_lahir'] ?? '',
                'tanggal_lahir'   => !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : date('Y-m-d'),
                'nama_ibu'        => $data['nama_ibu'] ?? '',
                'nama_ayah'       => $data['nama_ayah'] ?? '',
                'no_ibu'          => (int)($data['no_ibu'] ?? 0),
                'no_ayah'         => (int)($data['no_ayah'] ?? 0),
                'no_hp_alternatif'=> (int)($data['no_hp_alternatif'] ?? 0),
                'alamat'          => $data['alamat'] ?? '',
                'pondok'          => !empty($data['pondok']) ? $data['pondok'] : ($_SESSION['def_pondok'] ?? 'G1'),
                'jenis_kelamin'   => $data['jenis_kelamin'] ?? 'Laki-laki',
                'path_foto'       => '',
                'aktif'           => (int)($data['aktif'] ?? 1),
                'kode'            => $data['kode'] ?? strtoupper(substr($data['nama'], 0, 3)) . date('y'),
                'kepengurusan'    => !empty($data['kepengurusan']) ? $data['kepengurusan'] : ($_SESSION['def_kepengurusan'] ?? ''),
                'no_sktt'         => $data['nik'] ?? '',
                'no_ic'           => $data['no_ic'] ?? '',
                'keberadaan_paspor'=> $data['keberadaan_paspor'] ?? '',
                'ukuran_baju'     => $data['ukuran_baju'] ?? '',
            ])->execute();
            
            AuditLogger::log($db, 'CREATE', 'SANTRI', $kds, null, "Menambahkan data santri baru (KDS: $kds)");

            // Cari folder instansi berdasarkan kepengurusan santri
            $kepengurusanFolder = $data['kepengurusan'] ?? '';
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE nama_instansi = :k OR kode = :k", [':k' => $kepengurusanFolder])->queryOne();
            if (!$instansi) {
                $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
            }

            // Handle Photo Upload
            if (isset($files['foto_santri']) && $files['foto_santri']->getError() === UPLOAD_ERR_OK) {
                $baseDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads';
                $baseDir .= DIRECTORY_SEPARATOR . 'foto santri';
                if (!is_dir($baseDir)) @mkdir($baseDir, 0777, true);

                $ext = pathinfo($files['foto_santri']->getClientFilename(), PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama']) . '_' . $data['stambuk'];
                $fullPath = $baseDir . DIRECTORY_SEPARATOR . $safeName . '.' . $ext;

                try {
                    $files['foto_santri']->moveTo($fullPath);
                    $db->createCommand()->update('master_santri', ['path_foto' => $fullPath], ['kds' => $kds])->execute();
                } catch (\Exception $e) {}
            }

            $pasporInserted = null;
            $hasPasporUpdate = !empty($data['no_paspor']) || !empty($data['exp_paspor']) || isset($files['file_paspor']);
            if ($hasPasporUpdate) {
                $pathPaspor = '';
                if (isset($files['file_paspor']) && $files['file_paspor']->getError() === UPLOAD_ERR_OK) {
                    $baseDirP = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads';
                    $baseDirP .= DIRECTORY_SEPARATOR . 'paspor';
                    if (!is_dir($baseDirP)) @mkdir($baseDirP, 0777, true);
                    $extP = pathinfo($files['file_paspor']->getClientFilename(), PATHINFO_EXTENSION);
                    $safeNameP = 'Paspor_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama']) . '_' . $data['stambuk'];
                    $fullPathP = $baseDirP . DIRECTORY_SEPARATOR . $safeNameP . '.' . $extP;
                    try {
                        $files['file_paspor']->moveTo($fullPathP);
                        $pathPaspor = $fullPathP;
                    } catch (\Exception $e) {}
                }

                $pasporInserted = [
                    'kds'                => $kds,
                    'no_paspor'          => $data['no_paspor'] ?? '',
                    'tanggal_dikeluarkan'=> !empty($data['tgl_paspor']) ? $data['tgl_paspor'] : date('Y-m-d'),
                    'tempat_dikeluarkan' => $data['tempat_paspor'] ?? '',
                    'exp_paspor'         => !empty($data['exp_paspor']) ? $data['exp_paspor'] : date('Y-m-d', strtotime('+5 years')),
                    'aktif'              => 1,
                    'path_file'          => $pathPaspor,
                    'status_lokasi'      => 'di_kantor',
                ];
                $db->createCommand()->insert('mtb_paspor', $pasporInserted)->execute();
            }
            
            $itasInserted = null;
            $hasItasUpdate = !empty($data['no_itas']) || !empty($data['exp_itas']) || isset($files['file_itas']);
            if ($hasItasUpdate) {
                $pathItas = '';
                if (isset($files['file_itas']) && $files['file_itas']->getError() === UPLOAD_ERR_OK) {
                    $baseDirI = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads';
                    $baseDirI .= DIRECTORY_SEPARATOR . 'itas';
                    if (!is_dir($baseDirI)) @mkdir($baseDirI, 0777, true);
                    $extI = pathinfo($files['file_itas']->getClientFilename(), PATHINFO_EXTENSION);
                    $safeNameI = 'ITAS_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama']) . '_' . $data['stambuk'];
                    $fullPathI = $baseDirI . DIRECTORY_SEPARATOR . $safeNameI . '.' . $extI;
                    try {
                        $files['file_itas']->moveTo($fullPathI);
                        $pathItas = $fullPathI;
                    } catch (\Exception $e) {}
                }

                $itasInserted = [
                    'kds'        => $kds,
                    'no_itas'    => $data['no_itas'] ?? '',
                    'exp_itas'   => !empty($data['exp_itas']) ? $data['exp_itas'] : date('Y-m-d', strtotime('+1 year')),
                    'level_itas' => (int)($data['level_itas'] ?? 0),
                    'aktif'      => 1,
                    'path_file'  => $pathItas,
                ];
                $db->createCommand()->insert('mtb_itas', $itasInserted)->execute();
            }

            // FIREBASE DUAL-WRITE (setelah semua data termasuk paspor & itas tersimpan)
            $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
            if ($santriDb) {
                \App\Shared\FirebaseSync::syncSantri(
                    (string)$kds,
                    $santriDb,
                    null,
                    $pasporInserted,
                    $itasInserted
                );
            }

            // Barang Bawaan
            if (!empty($data['barang_bawaan'])) {
                $barang = json_decode($data['barang_bawaan'], true);
                if (is_array($barang)) {
                    foreach ($barang as $b) {
                        $db->createCommand()->insert('mtb_barang_terlarang', [
                            'kds' => $kds,
                            'nama_barang' => $b['nama'] ?? '-',
                            'jenis_barang' => 'Lainnya',
                            'jumlah_barang' => (int)($b['jumlah'] ?? 1),
                            'satuan' => 'pcs',
                            'detail' => '-'
                        ])->execute();
                    }
                }
            }
            return JsonResponse::create(['success' => true, 'message' => 'Data berhasil disimpan', 'kds' => $kds]);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
