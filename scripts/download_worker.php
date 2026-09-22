<?php
declare(strict_types=1);

// Prevent timeout for background processing
set_time_limit(0);

$publicUploadsRoot = dirname(__DIR__) . '/public/uploads';
if (!is_dir($publicUploadsRoot)) {
    @mkdir($publicUploadsRoot, 0777, true);
}

try {
    $dbHost = '100.68.135.3';
    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname=si_foreign_db;charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
    } catch (\Throwable $e) {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    // Reset stuck downloading status from previous crashes
    $pdo->exec("UPDATE capel_download_queue SET status = 'pending' WHERE status = 'downloading'");

    $attempts = [];

    while (true) {
        $stmt = $pdo->query("SELECT * FROM capel_download_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 1");
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            // No more pending tasks, exit worker cleanly
            break;
        }

        // Mark as downloading
        $pdo->prepare("UPDATE capel_download_queue SET status = 'downloading' WHERE id = :id")
            ->execute([':id' => $task['id']]);

        $taskId = (int)$task['id'];
        $kodeSantri = trim((string)$task['kode_santri']);
        $url = trim((string)$task['file_url']);
        $jenisDokumen = trim((string)$task['jenis_dokumen']);
        $instansiId = !empty($task['instansi_id']) ? (int)$task['instansi_id'] : 1;

        // Fetch santri data
        $stmt = $pdo->prepare("SELECT kds, nama, kode, kepengurusan FROM master_santri WHERE kode = :k LIMIT 1");
        $stmt->execute([':k' => $kodeSantri]);
        $santri = $stmt->fetch(PDO::FETCH_ASSOC);

        $nama = $santri ? $santri['nama'] : '';
        $kds = $santri ? (int)$santri['kds'] : 0;
        $kepengurusan = $santri['kepengurusan'] ?? '';

        if (empty($nama) && !empty($task['draft_id'])) {
            $stmtDraft = $pdo->prepare("SELECT nama_lengkap, instansi_id FROM mtb_capel_draft WHERE id = :did LIMIT 1");
            $stmtDraft->execute([':did' => (int)$task['draft_id']]);
            $draftRow = $stmtDraft->fetch(PDO::FETCH_ASSOC);
            if ($draftRow && !empty($draftRow['nama_lengkap'])) {
                $nama = $draftRow['nama_lengkap'];
            }
        }
        if (empty($nama)) {
            $nama = 'Santri';
        }

        // Tentukan folder instansi (contoh: Ponorogo, Mantingan, Kediri)
        $folderInstansi = 'Ponorogo';
        if (!empty($kepengurusan)) {
            $folderInstansi = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $kepengurusan));
        } elseif ($instansiId > 0) {
            $instStmt = $pdo->prepare("SELECT def_kepengurusan, kepengurusan, nama_instansi, path_folder FROM master_instansi WHERE kode = :id");
            $instStmt->execute([':id' => $instansiId]);
            $instRow = $instStmt->fetch(PDO::FETCH_ASSOC);
            if ($instRow) {
                $raw = !empty($instRow['def_kepengurusan']) ? $instRow['def_kepengurusan'] : (!empty($instRow['kepengurusan']) ? $instRow['kepengurusan'] : $instRow['nama_instansi']);
                if ($raw) {
                    $folderInstansi = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string)$raw));
                }
            }
        }
        if (!$folderInstansi) {
            $folderInstansi = 'Ponorogo';
        }

        $baseUploadDir = $publicUploadsRoot . '/' . $folderInstansi;
        if (!is_dir($baseUploadDir)) {
            @mkdir($baseUploadDir, 0777, true);
        }

        $namaClean = trim(preg_replace('/[^A-Za-z0-9]/', '_', $nama), '_');
        $namaClean = trim(preg_replace('/_+/', '_', $namaClean), '_');
        $santriFolder = $namaClean . '_' . $kodeSantri;

        // Tentukan subfolder dan penamaan file deterministik dengan menyertakan nama lengkap
        $jenisLower = strtolower($jenisDokumen);
        if (str_contains($jenisLower, 'foto')) {
            $subfolder = 'foto santri';
            $targetDir = $baseUploadDir . '/' . $subfolder . '/';
            $filenameOnly = 'FOTO_' . $namaClean . '_' . $kodeSantri;
        } elseif (str_contains($jenisLower, 'paspor')) {
            $subfolder = 'paspor';
            $targetDir = $baseUploadDir . '/' . $subfolder . '/';
            $filenameOnly = 'PASPOR_' . $namaClean . '_' . $kodeSantri;
        } elseif (str_contains($jenisLower, 'itas') || str_contains($jenisLower, 'visa')) {
            $subfolder = 'itas';
            $targetDir = $baseUploadDir . '/' . $subfolder . '/';
            $filenameOnly = 'ITAS_' . $namaClean . '_' . $kodeSantri;
        } else {
            $subfolder = 'berkas/' . $santriFolder;
            $targetDir = $baseUploadDir . '/' . $subfolder . '/';
            $jenisClean = trim(preg_replace('/[^A-Za-z0-9]/', '_', ucwords($jenisDokumen)), '_');
            $jenisClean = trim(preg_replace('/_+/', '_', $jenisClean), '_');
            $filenameOnly = $jenisClean . '_' . $namaClean . '_' . $kodeSantri;
        }

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        // Unduh file dari URL (Google Drive)
        $content = false;
        $isNetworkError = false;

        // 1. Coba curl jika tersedia
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($content === false || $httpCode < 200 || $httpCode >= 300) {
                $content = false;
                $isNetworkError = true;
            }
        }

        // 2. Fallback file_get_contents
        if ($content === false) {
            $ctx = stream_context_create([
                'http' => ['timeout' => 120, 'follow_location' => 1, 'user_agent' => 'Mozilla/5.0']
            ]);
            $content = @file_get_contents($url, false, $ctx);
            if ($content === false) {
                $isNetworkError = true;
            }
        }

        // Validasi respon bukan HTML error dari Google Drive
        if ($content !== false && stripos(substr($content, 0, 500), '<html') !== false && stripos(substr($content, 0, 500), 'google') !== false) {
            // Google Drive returned HTML (misal limit atau virus scan)
            // Coba ambil link konfirmasi virus scan jika ada
            if (preg_match('/href="([^"]+confirm=[^"]+)"/i', $content, $mConfirm)) {
                $confirmUrl = html_entity_decode($mConfirm[1]);
                if (str_starts_with($confirmUrl, '/')) {
                    $confirmUrl = 'https://drive.google.com' . $confirmUrl;
                }
                $content = @file_get_contents($confirmUrl);
            } else {
                $content = false;
                $isNetworkError = true;
            }
        }

        if ($content !== false && strlen($content) > 0) {
            // Deteksi MIME type dan ekstensi file
            $mime = 'application/pdf';
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = @$finfo->buffer($content) ?: 'application/pdf';
            }

            $ext = match ($mime) {
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
                'image/webp' => '.webp',
                'image/gif' => '.gif',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
                'application/msword' => '.doc',
                default => '.pdf',
            };

            $filename = $filenameOnly . $ext;
            $filepath = $targetDir . $filename;
            $dbPath = 'uploads/' . $folderInstansi . '/' . $subfolder . '/' . $filename;

            // Simpan file (menimpa file lama secara bersih jika nama sama)
            file_put_contents($filepath, $content);

            // Update database berdasarkan kategori
            if (str_contains($jenisLower, 'foto')) {
                $pdo->prepare("UPDATE master_santri SET path_foto = :p WHERE kode = :k")
                    ->execute([':p' => $dbPath, ':k' => $kodeSantri]);
            } elseif (str_contains($jenisLower, 'paspor')) {
                if ($kds > 0) {
                    $pdo->prepare("UPDATE mtb_paspor SET path_file = :p WHERE kds = :kds ORDER BY aktif DESC, id DESC LIMIT 1")
                        ->execute([':p' => $dbPath, ':kds' => $kds]);
                }
            } elseif (str_contains($jenisLower, 'itas') || str_contains($jenisLower, 'visa')) {
                if ($kds > 0) {
                    $pdo->prepare("UPDATE mtb_itas SET path_file = :p WHERE kds = :kds ORDER BY aktif DESC, id DESC LIMIT 1")
                        ->execute([':p' => $dbPath, ':kds' => $kds]);
                }
            } else {
                // Berkas santri otomatis terbaca dari folder fisik oleh ShowAction / BerkasTrait
                // Juga sinkronkan ke mtb_berkas_penting
                $chk = $pdo->prepare("SELECT id FROM mtb_berkas_penting WHERE kode = :k AND nama_berkas = :n LIMIT 1");
                $chk->execute([':k' => $kodeSantri, ':n' => $jenisDokumen]);
                $existingId = $chk->fetchColumn();

                if ($existingId) {
                    $pdo->prepare("UPDATE mtb_berkas_penting SET path_file = :p WHERE id = :id")
                        ->execute([':p' => $dbPath, ':id' => $existingId]);
                } else {
                    $pdo->prepare("INSERT INTO mtb_berkas_penting (kode, is_public, nama_berkas, path_file) VALUES (:k, 0, :n, :p)")
                        ->execute([
                            ':k' => $kodeSantri,
                            ':n' => $jenisDokumen,
                            ':p' => $dbPath
                        ]);
                }
            }

            // Tandai task unduhan selesai
            $pdo->prepare("UPDATE capel_download_queue SET status = 'completed', error_msg = NULL WHERE id = :id")
                ->execute([':id' => $taskId]);

        } else {
            // Gagal unduh
            $checkStmt = $pdo->prepare("SELECT status FROM capel_download_queue WHERE id = :id");
            $checkStmt->execute([':id' => $taskId]);
            $currentStatus = $checkStmt->fetchColumn();

            if ($currentStatus !== 'failed') {
                if ($isNetworkError) {
                    $attempts[$taskId] = ($attempts[$taskId] ?? 0) + 1;
                    if ($attempts[$taskId] < 3) {
                        $pdo->prepare("UPDATE capel_download_queue SET status = 'pending' WHERE id = :id")
                            ->execute([':id' => $taskId]);
                        sleep(3);
                        continue;
                    }
                }

                $pdo->prepare("UPDATE capel_download_queue SET status = 'failed', error_msg = 'Gagal mengunduh file dari Google Drive setelah beberapa kali percobaan' WHERE id = :id")
                    ->execute([':id' => $taskId]);
            }
        }
    }

} catch (\Throwable $e) {
    @file_put_contents(__DIR__ . '/worker_error.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
}
