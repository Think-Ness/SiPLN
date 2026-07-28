<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Shared\FirebaseSync;

// Start session manually if needed to read user ID
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;
$instansiId = $_SESSION['instansi_id'] ?? null;
$namaLengkap = $_SESSION['nama_lengkap'] ?? null;

// LOG FOR DEBUGGING
file_put_contents(__DIR__ . '/presence.log', date('Y-m-d H:i:s') . " - Session: " . json_encode($_SESSION) . "\n", FILE_APPEND);

if (!$userId || !$instansiId || $instansiId === '0') {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$action = $_GET['action'] ?? 'online';

if ($action === 'offline') {
    // Tandai waktu mencoba offline
    $offlineTime = time();
    
    // Lepaskan lock session agar navigasi halaman baru tidak tersumbat
    session_write_close();
    
    // Beri jeda 4 detik untuk melihat apakah user pindah ke halaman lain di web kita
    sleep(4);
    
    // Buka kembali session untuk mengecek ping terakhir
    session_start();
    $lastOnline = $_SESSION['last_online_ping'] ?? 0;
    session_write_close();
    
    // Jika ada ping online baru SETELAH dia mencoba offline, berarti dia cuma pindah halaman (bukan tutup web)
    if ($lastOnline > $offlineTime) {
        exit;
    }
    
    try {
        FirebaseSync::removePresence((string)$instansiId, (string)$userId);
    } catch (\Exception $e) {}
    exit;
}

// Action = Online
$_SESSION['last_online_ping'] = time();
session_write_close();

try {
    FirebaseSync::updatePresence(
        (string)$instansiId, 
        (string)$userId, 
        (string)$namaLengkap, 
        (string)$role
    );
    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
