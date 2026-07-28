<?php
declare(strict_types=1);

// Prevent timeout
set_time_limit(0);

$uploadDir = dirname(__DIR__) . '/public/uploads/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if another worker is already running (simple lock mechanism)
    // For simplicity we will just process pending items one by one.
    // If a worker is running, it will eventually clear the queue.
    
    while (true) {
        $stmt = $pdo->query("SELECT * FROM capel_download_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 1");
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task) {
            // No more pending tasks, exit worker
            break;
        }
        
        // Mark as downloading
        $pdo->prepare("UPDATE capel_download_queue SET status = 'downloading' WHERE id = :id")
            ->execute([':id' => $task['id']]);
            
        $kodeSantri = $task['kode_santri'];
        $url = $task['file_url'];
        $kategoriFolder = $task['kategori_folder'];
        $jenisDokumen = $task['jenis_dokumen'];
        $stmt = $pdo->prepare("SELECT nama FROM master_santri WHERE kode = :k");
        $stmt->execute([':k' => $kodeSantri]);
        $santri = $stmt->fetch();
        $nama = $santri ? $santri['nama'] : 'Santri';
        $namaClean = preg_replace('/[^A-Za-z0-9]/', '_', $nama);
        $jenisClean = preg_replace('/[^A-Za-z0-9]/', '_', ucwords($jenisDokumen));
        
        $baseFilename = $jenisClean . '_' . $namaClean . '_' . $kodeSantri . '_' . time();
        
        $isAbsolute = str_starts_with(str_replace('\\', '/', $kategoriFolder), 'D:/') || str_starts_with(str_replace('\\', '/', $kategoriFolder), 'C:/') || str_starts_with($kategoriFolder, '/');
        
        if ($isAbsolute) {
            $targetFolder = rtrim($kategoriFolder, '\\/') . '/';
        } else {
            $targetFolder = $uploadDir . $kategoriFolder . '/';
        }
        
        if (!is_dir($targetFolder)) @mkdir($targetFolder, 0777, true);
        
        // Download file
        $content = @file_get_contents($url);
        $isNetworkError = !isset($http_response_header) || empty($http_response_header);
        
        if ($content === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($content !== false && $httpCode >= 200 && $httpCode < 300) {
                $isNetworkError = false;
            } else {
                $content = false;
                $isNetworkError = true;
            }
        }
        
        static $attempts = [];
        
        if ($content !== false) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = @$finfo->buffer($content) ?: 'application/pdf';
            $ext = match($mime) {
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
                'image/gif' => '.gif',
                'application/pdf' => '.pdf',
                default => '.pdf'
            };
            
            $filename = $baseFilename . $ext;
            $filepath = $targetFolder . $filename;
            
            if ($isAbsolute) {
                $dbPath = '/serve.php?path=' . urlencode($filepath);
            } else {
                $dbPath = '/uploads/' . $kategoriFolder . '/' . $filename;
            }
            
            file_put_contents($filepath, $content);
            
            // Update database based on category
            if (str_contains($kategoriFolder, 'foto santri')) {
                $pdo->prepare("UPDATE master_santri SET path_foto = :p WHERE kode = :k")
                    ->execute([':p' => $dbPath, ':k' => $kodeSantri]);
            } elseif (str_contains($kategoriFolder, 'paspor')) {
                // Find kds
                $kdsStmt = $pdo->prepare("SELECT kds FROM master_santri WHERE kode = :k");
                $kdsStmt->execute([':k' => $kodeSantri]);
                $kdsRes = $kdsStmt->fetchColumn();
                
                if ($kdsRes) {
                    $pdo->prepare("UPDATE mtb_paspor SET path_file = :p WHERE kds = :kds")
                        ->execute([':p' => $dbPath, ':kds' => $kdsRes]);
                }
            } else {
                // Berkas penting
                $pdo->prepare("INSERT INTO mtb_berkas_penting (kode, is_public, nama_berkas, path_file) VALUES (:k, 0, :n, :p)")
                    ->execute([
                        ':k' => $kodeSantri,
                        ':n' => $jenisDokumen,
                        ':p' => $dbPath
                    ]);
            }
            
            $pdo->prepare("UPDATE capel_download_queue SET status = 'completed' WHERE id = :id")
                ->execute([':id' => $task['id']]);
                
        } else {
            // Check if user cancelled it
            $checkStmt = $pdo->prepare("SELECT status FROM capel_download_queue WHERE id = :id");
            $checkStmt->execute([':id' => $task['id']]);
            $currentStatus = $checkStmt->fetchColumn();
            
            if ($currentStatus !== 'failed') { // not cancelled
                if ($isNetworkError) {
                    $attempts[$task['id']] = ($attempts[$task['id']] ?? 0) + 1;
                    if ($attempts[$task['id']] < 3) {
                        // Put it back to pending and sleep to wait for internet
                        $pdo->prepare("UPDATE capel_download_queue SET status = 'pending' WHERE id = :id")->execute([':id' => $task['id']]);
                        sleep(5);
                        continue;
                    }
                }
                
                $pdo->prepare("UPDATE capel_download_queue SET status = 'failed', error_msg = 'Gagal mengunduh file dari Google Drive setelah beberapa kali percobaan' WHERE id = :id")
                    ->execute([':id' => $task['id']]);
            }
        }
    }

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/worker_error.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
}
