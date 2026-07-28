<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SELECT * FROM mtb_capel_draft WHERE nama LIKE "%Hasbiyallah%" LIMIT 1')->fetchAll(PDO::FETCH_ASSOC));
