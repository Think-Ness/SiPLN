<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query('SELECT nama_berkas, path_file FROM mtb_berkas_penting WHERE kode = "KOR26"');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
