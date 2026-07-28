<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT * FROM mtb_berkas_penting");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
