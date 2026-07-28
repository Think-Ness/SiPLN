<?php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("INSERT IGNORE INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut) VALUES 
('keuangan', 'Anggaran Operasional', '/anggaran', 'bi bi-wallet2', 'menu_anggaran', 10), 
('keuangan', 'Operasional Birokrasi', '/job-desk/keuangan', 'bi bi-graph-up-arrow', 'menu_job_desk', 20)");
$stmt->execute();
echo "Inserted into master_menus.";
