<?php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// Cek jobdesk_keuangan menu
$stmt = $pdo->query("SELECT * FROM master_menus WHERE url IN ('/anggaran', '/job-desk/keuangan')");
print_r($stmt->fetchAll(\PDO::FETCH_ASSOC));

// Force update
$pdo->exec("UPDATE master_menus SET kategori = 'keuangan', is_active = 1 WHERE url IN ('/anggaran', '/job-desk/keuangan')");
echo "Updated is_active to 1";
