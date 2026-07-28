<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('DESCRIBE master_menus')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT * FROM master_menus WHERE kategori="main"')->fetchAll(PDO::FETCH_ASSOC));
