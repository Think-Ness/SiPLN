<?php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT kategori, nama_menu, url, is_active FROM master_menus WHERE kategori = 'keuangan'");
print_r($stmt->fetchAll(\PDO::FETCH_ASSOC));
