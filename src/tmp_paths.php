<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SELECT kds, path_foto FROM master_santri WHERE path_foto IS NOT NULL AND path_foto != "" LIMIT 2')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT kds, path_paspor FROM mtb_paspor WHERE path_paspor IS NOT NULL AND path_paspor != "" LIMIT 2')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT kds, path_itas FROM mtb_itas WHERE path_itas IS NOT NULL AND path_itas != "" LIMIT 2')->fetchAll(PDO::FETCH_ASSOC));
