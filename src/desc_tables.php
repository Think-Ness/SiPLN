<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
echo "master_instansi:\n";
print_r($pdo->query('DESCRIBE master_instansi')->fetchAll(PDO::FETCH_ASSOC));
echo "\nusers:\n";
print_r($pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC));
