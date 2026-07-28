<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query('SELECT data_json FROM mtb_capel_draft LIMIT 3');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
