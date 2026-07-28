<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('DESCRIBE mtb_paspor')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('DESCRIBE mtb_itas')->fetchAll(PDO::FETCH_ASSOC));
