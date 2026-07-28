<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query('SELECT nama, path_foto FROM santri LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
