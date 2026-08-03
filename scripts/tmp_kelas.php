<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT DISTINCT kelas FROM master_santri");
$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($rows);
$stmt = $pdo->query("SELECT DISTINCT aktif FROM master_santri");
$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($rows);
