<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8mb4", "root", "");
$stmt = $pdo->query("SELECT kds, nama, kelas, status_santri, aktif FROM master_santri WHERE status_santri LIKE '%Program Pene%' OR status_santri LIKE '%Program Pers%' OR kelas LIKE '%CAPEL%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
