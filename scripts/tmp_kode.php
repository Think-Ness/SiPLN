<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT * FROM master_santri WHERE kode = 'MUH2604'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query("SELECT * FROM master_instansi WHERE kode = 'MUH2604'");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
