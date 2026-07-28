<?php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// Cek apakah anggaran sudah ada
$stmt = $pdo->query("SELECT * FROM master_menus WHERE url = '/anggaran'");
$anggaran = $stmt->fetch();

if ($anggaran) {
    // Update ke kategori 'keuangan'
    $pdo->exec("UPDATE master_menus SET kategori = 'keuangan' WHERE id = " . $anggaran['id']);
} else {
    // Insert jika belum
    $pdo->exec("INSERT IGNORE INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut) VALUES 
    ('keuangan', 'Anggaran Operasional', '/anggaran', 'bi bi-wallet2', 'menu_anggaran', 10)");
}

// Cek job desk keuangan
$stmt = $pdo->query("SELECT * FROM master_menus WHERE url = '/job-desk/keuangan'");
$jobdeskKeu = $stmt->fetch();

if ($jobdeskKeu) {
    // Pastikan kategori keuangan
    $pdo->exec("UPDATE master_menus SET kategori = 'keuangan' WHERE id = " . $jobdeskKeu['id']);
} else {
    // Insert
    $pdo->exec("INSERT IGNORE INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut) VALUES 
    ('keuangan', 'Operasional Birokrasi', '/job-desk/keuangan', 'bi bi-graph-up-arrow', 'menu_job_desk', 20)");
}

echo "Done updating menus!";
