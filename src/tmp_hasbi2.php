<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SELECT kds, nama, status_santri, path_foto FROM master_santri WHERE nama LIKE "%Hasbi%" OR kode LIKE "%MAS26%"')->fetchAll(PDO::FETCH_ASSOC));
