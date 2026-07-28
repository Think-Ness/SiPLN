<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SELECT * FROM mtb_berkas_penting WHERE path_file LIKE "%Hasbiyallah%"')->fetchAll(PDO::FETCH_ASSOC));
