<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8mb4", "root", "");
$stmt = $pdo->query("SELECT kode, nama_instansi, def_kepengurusan, def_pondok FROM master_instansi");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
