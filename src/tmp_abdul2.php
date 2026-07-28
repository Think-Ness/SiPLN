<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SELECT kds, kode, nama, path_foto FROM master_santri WHERE nama LIKE "%Abdul Aziz%"')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT * FROM mtb_paspor WHERE kds IN (SELECT kds FROM master_santri WHERE nama LIKE "%Abdul Aziz%")')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT * FROM mtb_itas WHERE kds IN (SELECT kds FROM master_santri WHERE nama LIKE "%Abdul Aziz%")')->fetchAll(PDO::FETCH_ASSOC));
