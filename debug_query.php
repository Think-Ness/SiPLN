<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("DESCRIBE mtb_paspor");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query("DESCRIBE mtb_itas");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
