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
            $parser = new Parser();
            $pdf = $parser->parseFile($tempPath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                @unlink($tempPath);
                return JsonResponse::create(['success' => false, 'message' => 'Gagal membaca teks dari PDF (kosong/gambar).'], 400);
            }
            $text = $pages[0]->getText();
            
            if (empty(trim($text))) {
                @unlink($tempPath);
                return JsonResponse::create(['success' => false, 'message' => 'Teks PDF tidak terdeteksi (berupa scan?).'], 400);
            }

            // Bersihkan teks
            $text = str_replace("\r", "", $text);
            $lines = explode("\n", $text);
            $lines = array_values(array_filter(array_map('trim', $lines), function($l) { return $l !== ''; }));
            
            if (empty($lines)) {
                @unlink($tempPath);
                return JsonResponse::create(['success' => false, 'message' => 'Tidak ada teks yang ditemukan.'], 400);
            }

            $nama = $lines[0];
            
            if (count($lines) > 1) {
                $line2 = $lines[1];
                $blockedWords = ["PERMIT", "EXPIRY", "DATE", "NUMBER", "PASSPORT", "NATIONALITY", "MALE", "FEMALE", "PONDOK", "YAYASAN"];
                
                $isBlocked = false;
                foreach ($blockedWords as $word) {
                    if (stripos($line2, $word) !== false) {
                        $isBlocked = true;
                        break;
                    }
                }
                
                $isUpper = (strtoupper($line2) === $line2);
                
                if ($isUpper && !$isBlocked) {
                    $nama .= " " . $line2;
                }
            }
            
            $nama = $this->cleanName($nama);
            
            if (empty($nama)) {
                @unlink($tempPath);
                return JsonResponse::create(['success' => false, 'message' => 'Nama tidak bisa diekstrak.'], 400);
            }

            $noItas = '';
            $expItas = '';

            // Extract PERMIT NUMBER
            if (preg_match('/PERMIT\s+NUMBER\s*:\s*([A-Z0-9\-]+)/i', $text, $matches)) {
                $noItas = trim($matches[1]);
            }
            
            // Extract STAY PERMIT EXPIRY (asumsi format DD/MM/YYYY)
            if (preg_match('/STAY\s+PERMIT\s+EXPIRY\s*:\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
                $dateParts = explode('/', trim($matches[1]));
                if (count($dateParts) === 3) {
                    $expItas = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            // Cari Santri
            // TAHAP 1: Cocok sama persis
            $santri = $db->createCommand("SELECT kds, nama, stambuk, kepengurusan FROM master_santri WHERE aktif = 1 AND LOWER(nama) = :nama", [':nama' => strtolower($nama)])->queryOne();
            
            if (!$santri) {
                // TAHAP 2: Pencarian menggunakan LIKE (jika ada gelar atau tambahan spasi)
                $santri = $db->createCommand("SELECT kds, nama, stambuk, kepengurusan FROM master_santri WHERE aktif = 1 AND LOWER(nama) LIKE :nama LIMIT 1", [':nama' => '%' . strtolower($nama) . '%'])->queryOne();
            }
            
            if (!$santri) {
                // TAHAP 3: Pencarian Nama Terbalik (Word-by-word)
                // Memecah nama menjadi kata-kata tunggal untuk mencocokkan nama yang susunannya terbalik (misal: "Ahmad Ali" di DB, tapi di ITAS "Ali Ahmad")
                $words = explode(' ', strtolower(preg_replace('/[^a-z ]/i', '', $nama)));
                // Hanya ambil kata yang panjangnya lebih dari 2 huruf untuk menghindari pencocokan salah pada kata sandang
                $words = array_filter($words, function($w) { return strlen(trim($w)) > 2; });
                
                if (count($words) > 0) {
                    $sql = "SELECT kds, nama, stambuk, kepengurusan FROM master_santri WHERE aktif = 1";
                    $params = [];
                    $i = 0;
                    foreach ($words as $word) {
                        $sql .= " AND LOWER(nama) LIKE :w$i";
                        $params[":w$i"] = '%' . trim($word) . '%';
                        $i++;
                    }
                    $sql .= " LIMIT 1";
                    
                    $santri = $db->createCommand($sql, $params)->queryOne();
                }
            }
            
            if (!$santri) {
                @unlink($tempPath);
                return JsonResponse::create([
                    'success' => false, 
                    'message' => "Tidak ada data santri atas nama '$nama'.",
                    'extracted_name' => $nama
                ], 404);
            }

            $kds = $santri['kds'];
            $stambuk = $santri['stambuk'];
            $kepengurusan = $santri['kepengurusan'];

            // Tentukan folder
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE nama_instansi = :k OR kode = :k", [':k' => $kepengurusan])->queryOne();
            if (!$instansi) {
                $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
            }

            $baseDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads';
            $baseDir .= DIRECTORY_SEPARATOR . 'itas';
            if (!is_dir($baseDir)) @mkdir($baseDir, 0777, true);

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
                'santri_name' => $santri['nama']
            ]);

        } catch (\Throwable $e) {
            @unlink($tempPath);
            return JsonResponse::create(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
