<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use App\Shared\FirebaseSync;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Smalot\PdfParser\Parser;

final class AutoUploadItasAction
{
    private function cleanName($nama): string
    {
        $illegal = ['<','>',':','"','/','\\','|','?','*'];
        return trim(str_replace($illegal, '', $nama));
    }

    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $files = $request->getUploadedFiles();
        if (!isset($files['itas_file'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada file yang diupload.'], 400);
        }

        $file = $files['itas_file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengupload file.'], 400);
        }

        $filename = $file->getClientFilename();
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'pdf') {
            return JsonResponse::create(['success' => false, 'message' => 'File harus berupa PDF.'], 400);
        }

        // Simpan sementara untuk di-parse
        $tempPath = sys_get_temp_dir() . '/' . uniqid('itas_') . '.pdf';
        try {
            $file->moveTo($tempPath);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal memproses file sementara: ' . $e->getMessage()], 500);
        }

        try {
            $parseResult = \App\Shared\ItasParserEngine::parsePdf($tempPath, $db);

            if (!$parseResult['success'] || empty($parseResult['matched_santri'])) {
                @unlink($tempPath);
                $errMsg = $parseResult['error'] ?? 'Gagal mencocokkan data ITAS dengan database santri.';
                return JsonResponse::create([
                    'success' => false,
                    'message' => $errMsg,
                    'extracted_name' => $parseResult['extracted_name'] ?? '',
                    'no_itas' => $parseResult['no_itas'] ?? '',
                    'exp_itas' => $parseResult['exp_itas'] ?? '',
                    'no_paspor' => $parseResult['no_paspor'] ?? '',
                    'matched_profile' => $parseResult['matched_profile_name'] ?? null
                ], 404);
            }

            $santri = $parseResult['matched_santri'];
            $nama = !empty($parseResult['extracted_name']) ? $parseResult['extracted_name'] : $santri['nama'];
            $noItas = $parseResult['no_itas'] ?? '';
            $expItas = $parseResult['exp_itas'] ?? '';
            $matchMethod = $parseResult['match_method'] ?? 'exact_name';
            $profileName = $parseResult['matched_profile_name'] ?? 'Auto-Detect';

            $kds = $santri['kds'];
            $stambuk = $santri['stambuk'];
            $kepengurusan = $santri['kepengurusan'];

            // Tentukan folder instansi
            $instansi = $db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan = :k OR kepengurusan = :k OR nama_instansi = :k LIMIT 1", [':k' => $kepengurusan])->queryOne();
            $targetInstansiKode = $instansi ? (int)$instansi['kode'] : (int)($_SESSION['instansi_id'] ?? 0);
            $baseDir = \App\Shared\UploadPath::getFolder($db, 'itas', $targetInstansiKode);

            // Tambahkan timestamp di nama file agar tidak bentrok jika file sama di-upload lagi
            $safeName = 'ITAS_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $santri['nama']) . '_' . $stambuk . '_' . time();
            $newFilename = $safeName . '.pdf';
            $fullPath = $baseDir . DIRECTORY_SEPARATOR . $newFilename;

            // Rename file
            rename($tempPath, $fullPath);

            // Logika Update Database mtb_itas
            // Ambil semua data ITAS santri ini
            $semuaItas = $db->createCommand("SELECT * FROM mtb_itas WHERE kds = :kds", [':kds' => $kds])->queryAll();
            
            $updatedExisting = false;
            foreach ($semuaItas as &$itas) {
                if (!empty($itas['exp_itas']) && !empty($expItas) && $itas['exp_itas'] === $expItas) {
                    $db->createCommand()->update('mtb_itas', [
                        'path_file' => $fullPath,
                        'no_itas' => empty($noItas) ? $itas['no_itas'] : $noItas
                    ], ['id' => $itas['id']])->execute();
                    $itas['path_file'] = $fullPath;
                    $itas['no_itas'] = empty($noItas) ? $itas['no_itas'] : $noItas;
                    $updatedExisting = true;
                    break;
                }
            }
            unset($itas);

            if (!$updatedExisting) {
                // Masukkan data baru
                $db->createCommand()->insert('mtb_itas', [
                    'kds' => $kds,
                    'no_itas' => $noItas,
                    'exp_itas' => empty($expItas) ? date('Y-m-d', strtotime('+1 year')) : $expItas,
                    'level_itas' => 0, 
                    'aktif' => 0, 
                    'path_file' => $fullPath
                ])->execute();
                
                // Ambil lagi semua data termasuk yang baru
                $semuaItas = $db->createCommand("SELECT * FROM mtb_itas WHERE kds = :kds", [':kds' => $kds])->queryAll();
            }

            // Hitung Ulang Level dan Aktif berdasarkan exp_itas
            // Urutkan berdasarkan exp_itas ASC (dari terlama ke terbaru)
            usort($semuaItas, function($a, $b) {
                $timeA = empty($a['exp_itas']) ? 0 : strtotime($a['exp_itas']);
                $timeB = empty($b['exp_itas']) ? 0 : strtotime($b['exp_itas']);
                return $timeA <=> $timeB;
            });

            $total = count($semuaItas);
            
            // Tahap 1: Anchor Fill (Mengisi level_itas yang 0)
            $hasAnchor = false;
            foreach ($semuaItas as $itas) {
                if ($itas['level_itas'] > 0) { $hasAnchor = true; break; }
            }
            if (!$hasAnchor && $total > 0) {
                $semuaItas[$total-1]['level_itas'] = $total;
            }
            
            $changed = true;
            while ($changed) {
                $changed = false;
                for ($i = 1; $i < $total; $i++) {
                    if ($semuaItas[$i]['level_itas'] == 0 && $semuaItas[$i-1]['level_itas'] > 0) {
                        $semuaItas[$i]['level_itas'] = $semuaItas[$i-1]['level_itas'] + 1;
                        $changed = true;
                    }
                }
                for ($i = $total - 2; $i >= 0; $i--) {
                    if ($semuaItas[$i]['level_itas'] == 0 && $semuaItas[$i+1]['level_itas'] > 0) {
                        $semuaItas[$i]['level_itas'] = $semuaItas[$i+1]['level_itas'] - 1;
                        $changed = true;
                    }
                }
            }

            // Tahap 2: Sanitize (Memastikan level masuk akal dan tidak ada duplikat)
            if ($total > 0) {
                // A. Pastikan ITAS terbaru (aktif) minimal selevel dengan jumlah file yang ada
                $semuaItas[$total-1]['level_itas'] = max((int)$semuaItas[$total-1]['level_itas'], $total);
                
                // B. Backward pass: Cegah duplikat / urutan yang terbalik
                for ($i = $total - 2; $i >= 0; $i--) {
                    if ($semuaItas[$i]['level_itas'] >= $semuaItas[$i+1]['level_itas']) {
                        $semuaItas[$i]['level_itas'] = $semuaItas[$i+1]['level_itas'] - 1;
                    }
                }
                
                // C. Forward pass: Pastikan minimal level 1 dan benar-benar tidak ada sisa duplikat
                $semuaItas[0]['level_itas'] = max(1, (int)$semuaItas[0]['level_itas']);
                for ($i = 1; $i < $total; $i++) {
                    if ($semuaItas[$i]['level_itas'] <= $semuaItas[$i-1]['level_itas']) {
                        $semuaItas[$i]['level_itas'] = $semuaItas[$i-1]['level_itas'] + 1;
                    }
                }
            }

            // Tahap 3: Update ke Database
            foreach ($semuaItas as $index => $itas) {
                $isAktif = ($index === $total - 1) ? 1 : 0; // Yang expiry paling jauh menjadi yang aktif
                
                $db->createCommand()->update('mtb_itas', [
                    'level_itas' => $itas['level_itas'],
                    'aktif' => $isAktif
                ], ['id' => $itas['id']])->execute();
            }

            // Sync ke Firebase karena ITAS sudah diperbarui
            try {
                FirebaseSync::syncSantriData($kds);
            } catch (\Throwable $e) {
                // Ignore firebase error
            }

            return JsonResponse::create([
                'success' => true, 
                'message' => 'Selesai: ' . $filename . ' -> ' . $santri['nama'],
                'extracted_name' => $nama,
                'santri_name' => $santri['nama'],
                'no_itas' => $noItas,
                'exp_itas' => $expItas,
                'profile_name' => $profileName,
                'match_method' => $matchMethod
            ]);

        } catch (\Throwable $e) {
            @unlink($tempPath);
            return JsonResponse::create(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
