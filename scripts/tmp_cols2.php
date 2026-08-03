<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SHOW COLUMNS FROM master_santri')->fetchAll(PDO::FETCH_COLUMN));
