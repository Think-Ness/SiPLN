<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\AuditLogger;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class UpdateAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $kds  = (int) $currentRoute->getArgument('kds');
        $data = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        if (isset($data['stambuk'])) {
            $data['stambuk'] = preg_replace('/[^0-9]/', '', (string)$data['stambuk']);
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

            $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
            $santriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
            $sKepengurusan = $santriDb ? $santriDb['kepengurusan'] : '';

            $isPindahan = false;
            if ($myKepengurusan !== '' && $sKepengurusan !== '' && strcasecmp(trim($myKepengurusan), trim($sKepengurusan)) !== 0) {
                $isPindahan = true;
            }

            $allowedFieldsJson = $db->createCommand("SELECT setting_value FROM app_settings WHERE setting_key = 'pindahan_allowed_fields'")->queryScalar();
            $allowedFields = $allowedFieldsJson ? json_decode($allowedFieldsJson, true) : ['kelas', 'rayon', 'pondok', 'kamar'];

            $updateData = [
                'stambuk'         => (int)($data['stambuk'] ?? 0),
                'nama'            => $data['nama'] ?? '',
                'kelas'           => $data['kelas'] ?? '',
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
                'pondok'          => $data['pondok'] ?? '',
                'jenis_kelamin'   => $data['jenis_kelamin'] ?? '',
                'kepengurusan'    => $data['kepengurusan'] ?? '',
                'no_sktt'         => $data['nik'] ?? '',
                'no_ic'           => $data['no_ic'] ?? '',
                'keberadaan_paspor'=> $data['keberadaan_paspor'] ?? '',
                'ukuran_baju'     => $data['ukuran_baju'] ?? '',
                'aktif'           => (int)($data['aktif'] ?? 1),
            ];

            $directUpdate = [];
            $requestedEdits = [];
            $oldValues = [];

            if ($isPindahan) {
                foreach ($updateData as $key => $val) {
                    if (in_array($key, $allowedFields)) {
                        $directUpdate[$key] = $val;
                    } else {
                        // Check if the value actually changed to avoid spamming requests
                        $oldVal = $santriDb[$key] ?? '';
                        if ((string)$oldVal !== (string)$val) {
                            $requestedEdits[$key] = $val;
                            $oldValues[$key] = $oldVal;
                        }
                    }
                }
            } else {
                $directUpdate = $updateData;
            }

            if (!empty($directUpdate)) {
                $db->createCommand()->update('master_santri', $directUpdate, ['kds' => $kds])->execute();
            }

            // Cari folder instansi berdasarkan kepengurusan santri
            $kepengurusanFolder = $data['kepengurusan'] ?? $sKepengurusan;
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
                $stambuk = $data['stambuk'] ?? '0';
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama'] ?? '') . '_' . $stambuk;
                $fullPath = $baseDir . DIRECTORY_SEPARATOR . $safeName . '.' . $ext;

                try {
                    $files['foto_santri']->moveTo($fullPath);
                    // Hapus foto lama jika ada
                    if (!empty($santriDb['path_foto']) && file_exists($santriDb['path_foto']) && $santriDb['path_foto'] !== $fullPath) {
                        @unlink($santriDb['path_foto']);
                    }
                    $db->createCommand()->update('master_santri', ['path_foto' => $fullPath], ['kds' => $kds])->execute();
                } catch (\Exception $e) {}
            } elseif (isset($data['hapus_foto']) && (int)$data['hapus_foto'] === 1) {
                // Hapus foto jika diminta
                $oldSantri = $db->createCommand("SELECT path_foto FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                if ($oldSantri && !empty($oldSantri['path_foto']) && file_exists($oldSantri['path_foto'])) {
                    @unlink($oldSantri['path_foto']);
                }
                $db->createCommand()->update('master_santri', ['path_foto' => null], ['kds' => $kds])->execute();
            }

            $isNewDoc = (int)($request->getParsedBody()['is_new_doc'] ?? 1);

            $latestPaspor = $db->createCommand(
                "SELECT id, no_paspor, exp_paspor, path_file, tempat_dikeluarkan, tanggal_dikeluarkan FROM mtb_paspor WHERE kds=:kds AND aktif=1 LIMIT 1",
                [':kds' => $kds]
            )->queryOne();

            $pasporNum = !empty($data['no_paspor']) ? $data['no_paspor'] : ($latestPaspor ? $latestPaspor['no_paspor'] : '');
            $expPaspor = !empty($data['exp_paspor']) ? $data['exp_paspor'] : ($latestPaspor ? $latestPaspor['exp_paspor'] : '');
            $hasPasporUpdate = !empty($pasporNum) || !empty($expPaspor) || isset($files['file_paspor']);

            if ($hasPasporUpdate) {
                $pathPaspor = $latestPaspor ? $latestPaspor['path_file'] : '';
                if (isset($files['file_paspor']) && $files['file_paspor']->getError() === UPLOAD_ERR_OK) {
                    $baseDirP = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads';
                    $baseDirP .= DIRECTORY_SEPARATOR . 'paspor';
                    if (!is_dir($baseDirP)) @mkdir($baseDirP, 0777, true);
                    $extP = pathinfo($files['file_paspor']->getClientFilename(), PATHINFO_EXTENSION);
                    $stambuk = $data['stambuk'] ?? '0';
                    $safeNameP = 'Paspor_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama'] ?? '') . '_' . $stambuk . '_' . time();
                    $fullPathP = $baseDirP . DIRECTORY_SEPARATOR . $safeNameP . '.' . $extP;
                    try {
                        $files['file_paspor']->moveTo($fullPathP);
                        $pathPaspor = $fullPathP;
                        // Hapus paspor lama
                        if (!empty($latestPaspor['path_file']) && file_exists($latestPaspor['path_file']) && $latestPaspor['path_file'] !== $fullPathP) {
                            @unlink($latestPaspor['path_file']);
                        }
                    } catch (\Exception $e) {}
                }

                $pasporData = [
                    'no_paspor'          => $pasporNum,
                    'tanggal_dikeluarkan'=> !empty($data['tgl_paspor']) ? $data['tgl_paspor'] : date('Y-m-d'),
                    'tempat_dikeluarkan' => $data['tempat_paspor'] ?? '',
                    'exp_paspor'         => !empty($expPaspor) ? $expPaspor : date('Y-m-d', strtotime('+5 years')),
                    'aktif'              => 1,
                    'path_file'          => $pathPaspor
                ];

                if ($isPindahan) {
                    $pChanged = false;
                    if (!$latestPaspor) {
                        $pChanged = true;
                    } elseif ($latestPaspor['no_paspor'] !== $pasporNum || (string)$latestPaspor['exp_paspor'] !== (string)$pasporData['exp_paspor'] || isset($files['file_paspor'])) {
                        $pChanged = true;
                    }
                    if ($pChanged) {
                        $requestedEdits['_paspor_data'] = $pasporData;
                        $requestedEdits['_paspor_is_new'] = $isNewDoc;
                        if ($latestPaspor) {
                            $requestedEdits['_paspor_latest_id'] = $latestPaspor['id'];
                        }
                        if (!$latestPaspor || $latestPaspor['no_paspor'] !== $pasporNum) {
                            $requestedEdits['No Paspor'] = $pasporNum;
                            $oldValues['No Paspor'] = $latestPaspor ? $latestPaspor['no_paspor'] : '';
                        }
                        if (!$latestPaspor || (string)$latestPaspor['tempat_dikeluarkan'] !== (string)$pasporData['tempat_dikeluarkan']) {
                            $requestedEdits['Tempat Keluar Paspor'] = $pasporData['tempat_dikeluarkan'];
                            $oldValues['Tempat Keluar Paspor'] = $latestPaspor ? $latestPaspor['tempat_dikeluarkan'] : '';
                        }
                        if (!$latestPaspor || (string)$latestPaspor['tanggal_dikeluarkan'] !== (string)$pasporData['tanggal_dikeluarkan']) {
                            $requestedEdits['Tgl Keluar Paspor'] = $pasporData['tanggal_dikeluarkan'];
                            $oldValues['Tgl Keluar Paspor'] = $latestPaspor ? $latestPaspor['tanggal_dikeluarkan'] : '';
                        }
                        if (!$latestPaspor || (string)$latestPaspor['exp_paspor'] !== (string)$pasporData['exp_paspor']) {
                            $requestedEdits['Exp Paspor'] = $pasporData['exp_paspor'];
                            $oldValues['Exp Paspor'] = $latestPaspor ? $latestPaspor['exp_paspor'] : '';
                        }
                    }
                } else {
                    if ($latestPaspor) {
                        $dbTempat = (string)($latestPaspor['tempat_dikeluarkan'] ?? '');
                        $dbTanggal = (string)($latestPaspor['tanggal_dikeluarkan'] ?? '');
                        $dbExp = (string)($latestPaspor['exp_paspor'] ?? '');

                        $pasporChanged = ($pasporNum !== $latestPaspor['no_paspor'] || 
                                          (string)($data['tempat_paspor'] ?? '') !== $dbTempat ||
                                          (string)($data['tgl_paspor'] ?? '') !== $dbTanggal ||
                                          (string)$pasporData['exp_paspor'] !== $dbExp || 
                                          isset($files['file_paspor']));
                                          
                        if ($isNewDoc === 1 && $pasporChanged) {
                            if ($pasporNum === $latestPaspor['no_paspor']) {
                                $pasporData['no_paspor'] = '';
                            }
                            if (($data['tempat_paspor'] ?? '') === $latestPaspor['tempat_dikeluarkan']) {
                                $pasporData['tempat_dikeluarkan'] = '';
                            }
                            if (($data['tgl_paspor'] ?? '') === $latestPaspor['tanggal_dikeluarkan']) {
                                $pasporData['tanggal_dikeluarkan'] = null;
                            }
                            if ($pasporData['exp_paspor'] === $latestPaspor['exp_paspor']) {
                                $pasporData['exp_paspor'] = null;
                            }
                            $db->createCommand()->update('mtb_paspor', ['aktif' => 0], ['kds' => $kds])->execute();
                            $db->createCommand()->insert('mtb_paspor', array_merge($pasporData, ['kds' => $kds]))->execute();
                        } elseif ($pasporChanged) {
                            $db->createCommand()->update('mtb_paspor', $pasporData, ['id' => $latestPaspor['id']])->execute();
                        }
                    } else {
                        $db->createCommand()->insert('mtb_paspor', array_merge($pasporData, ['kds' => $kds]))->execute();
                    }
                }
            }

            $latestItas = $db->createCommand(
                "SELECT id, no_itas, exp_itas, level_itas, path_file FROM mtb_itas WHERE kds=:kds AND aktif=1 LIMIT 1",
                [':kds' => $kds]
            )->queryOne();

            $itasNum = !empty($data['no_itas']) ? $data['no_itas'] : ($latestItas ? $latestItas['no_itas'] : '');
            $expItas = !empty($data['exp_itas']) ? $data['exp_itas'] : ($latestItas ? $latestItas['exp_itas'] : '');
            $hasItasUpdate = !empty($itasNum) || !empty($expItas) || isset($files['file_itas']);

            if ($hasItasUpdate) {
                $pathItas = $latestItas ? $latestItas['path_file'] : '';
                if (isset($files['file_itas']) && $files['file_itas']->getError() === UPLOAD_ERR_OK) {
                    $baseDirI = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads';
                    $baseDirI .= DIRECTORY_SEPARATOR . 'itas';
                    if (!is_dir($baseDirI)) @mkdir($baseDirI, 0777, true);
                    $extI = pathinfo($files['file_itas']->getClientFilename(), PATHINFO_EXTENSION);
                    $stambuk = $data['stambuk'] ?? '0';
                    $safeNameI = 'ITAS_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama'] ?? '') . '_' . $stambuk . '_' . time();
                    $fullPathI = $baseDirI . DIRECTORY_SEPARATOR . $safeNameI . '.' . $extI;
                    try {
                        $files['file_itas']->moveTo($fullPathI);
                        $pathItas = $fullPathI;
                        // Hapus ITAS lama
                        if (!empty($latestItas['path_file']) && file_exists($latestItas['path_file']) && $latestItas['path_file'] !== $fullPathI) {
                            @unlink($latestItas['path_file']);
                        }
                    } catch (\Exception $e) {}
                }

                $itasData = [
                    'no_itas'    => $itasNum,
                    'exp_itas'   => !empty($expItas) ? $expItas : date('Y-m-d', strtotime('+1 year')),
                    'level_itas' => (int)($data['level_itas'] ?? ($latestItas['level_itas'] ?? 0)),
                    'aktif'      => 1,
                    'path_file'  => $pathItas
                ];

                if ($isPindahan) {
                    $iChanged = false;
                    if (!$latestItas) {
                        $iChanged = true;
                    } elseif ($latestItas['no_itas'] !== $itasNum || (string)$latestItas['exp_itas'] !== (string)$itasData['exp_itas'] || (int)$latestItas['level_itas'] !== (int)$itasData['level_itas'] || isset($files['file_itas'])) {
                        $iChanged = true;
                    }
                    if ($iChanged) {
                        $requestedEdits['_itas_data'] = $itasData;
                        $requestedEdits['_itas_is_new'] = $isNewDoc;
                        if ($latestItas) {
                            $requestedEdits['_itas_latest_id'] = $latestItas['id'];
                        }
                        if (!$latestItas || $latestItas['no_itas'] !== $itasNum) {
                            $requestedEdits['No ITAS'] = $itasNum;
                            $oldValues['No ITAS'] = $latestItas ? $latestItas['no_itas'] : '';
                        }
                        if (!$latestItas || (int)$latestItas['level_itas'] !== (int)$itasData['level_itas']) {
                            $requestedEdits['Level ITAS'] = $itasData['level_itas'];
                            $oldValues['Level ITAS'] = $latestItas ? $latestItas['level_itas'] : '';
                        }
                        if (!$latestItas || $latestItas['exp_itas'] !== $itasData['exp_itas']) {
                            $requestedEdits['Exp ITAS'] = $itasData['exp_itas'];
                            $oldValues['Exp ITAS'] = $latestItas ? $latestItas['exp_itas'] : '';
                        }
                    }
                } else {
                    if ($latestItas) {
                        $dbExpItas = (string)($latestItas['exp_itas'] ?? '');

                        $itasChanged = ($itasNum !== $latestItas['no_itas'] || 
                                        (int)($data['level_itas'] ?? 0) !== (int)$latestItas['level_itas'] || 
                                        (string)$itasData['exp_itas'] !== $dbExpItas || 
                                        isset($files['file_itas']));

                        if ($isNewDoc === 1 && $itasChanged) {
                            if ($itasNum === $latestItas['no_itas']) {
                                $itasData['no_itas'] = '';
                            }
                            if ((int)$itasData['level_itas'] === (int)$latestItas['level_itas']) {
                                $itasData['level_itas'] = (int)$latestItas['level_itas'] + 1;
                            }
                            if ($itasData['exp_itas'] === $latestItas['exp_itas']) {
                                $itasData['exp_itas'] = null;
                            }
                            $db->createCommand()->update('mtb_itas', ['aktif' => 0], ['kds' => $kds])->execute();
                            $db->createCommand()->insert('mtb_itas', array_merge($itasData, ['kds' => $kds]))->execute();
                        } elseif ($itasChanged) {
                            $db->createCommand()->update('mtb_itas', $itasData, ['id' => $latestItas['id']])->execute();
                        }
                    } else {
                        $db->createCommand()->insert('mtb_itas', array_merge($itasData, ['kds' => $kds]))->execute();
                    }
                }
            }

            // Barang Bawaan (Delete and Re-insert)
            if (isset($data['barang_bawaan'])) {
                if ($isPindahan) {
                    // For pindahan, we probably shouldn't directly delete and re-insert, but it's low risk.
                    // For now, we'll just let it update directly since it's just items, or we can skip it.
                    // We'll let it directly update barang bawaan as it's not a critical restricted field.
                }
                $db->createCommand()->delete('mtb_barang_terlarang', ['kds' => $kds])->execute();
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

            if (!empty($requestedEdits)) {
                $instansiId = $_SESSION['instansi_id'] ?? 1;
                $existing = $db->createCommand("SELECT id FROM santri_edit_requests WHERE kds = :kds AND status = 'pending' AND requested_by_instansi_id = :instId", [
                    ':kds' => $kds,
                    ':instId' => $instansiId
                ])->queryScalar();

                if ($existing) {
                    $db->createCommand()->update('santri_edit_requests', [
                        'requested_changes' => json_encode($requestedEdits),
                        'old_values' => json_encode($oldValues),
                        'created_at' => date('Y-m-d H:i:s')
                    ], ['id' => $existing])->execute();
                } else {
                    $db->createCommand()->insert('santri_edit_requests', [
                        'kds' => $kds,
                        'requested_by_instansi_id' => $instansiId,
                        'target_kepengurusan' => trim((string)$sKepengurusan),
                        'requested_changes' => json_encode($requestedEdits),
                        'old_values' => json_encode($oldValues),
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s')
                    ])->execute();
                }
            }

            // Fetch new row to log exact changes (including photo path changes that happened below directUpdate)
            $newSantriDb = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
            
            // Only log if something changed in master_santri
            if ($santriDb && $newSantriDb) {
                $hasChanges = false;
                foreach ($newSantriDb as $k => $v) {
                    if ((string)$v !== (string)($santriDb[$k] ?? '')) {
                        $hasChanges = true;
                        break;
                    }
                }
                if ($hasChanges) {
                    AuditLogger::log($db, 'UPDATE', 'SANTRI', $kds, $santriDb, $newSantriDb, "Memperbarui Biodata Santri");
                }
            }
            
            if ($newSantriDb) {
                // FIREBASE DUAL-WRITE (embed paspor & itas ke dokumen santri)
                $latestPaspor = $db->createCommand("SELECT * FROM mtb_paspor WHERE kds=:kds AND aktif=1 ORDER BY id DESC LIMIT 1", [':kds' => $kds])->queryOne();
                $latestItas = $db->createCommand("SELECT * FROM mtb_itas WHERE kds=:kds AND aktif=1 ORDER BY id DESC LIMIT 1", [':kds' => $kds])->queryOne();
                
                \App\Shared\FirebaseSync::syncSantri(
                    (string)$kds, 
                    $newSantriDb, 
                    null,
                    $latestPaspor ?: null,
                    $latestItas ?: null
                );
            }

            return JsonResponse::create([
                'success' => true,
                'message' => 'Perubahan berhasil disimpan.',
                'requested_edits' => $requestedEdits ?? []
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
