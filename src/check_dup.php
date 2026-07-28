<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
print_r($pdo->query('SELECT kode, nama_berkas, COUNT(*) as c FROM mtb_berkas_penting GROUP BY kode, nama_berkas HAVING c > 1 LIMIT 5')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT kode, COUNT(*) as c FROM master_santri GROUP BY kode HAVING c > 1 LIMIT 5')->fetchAll(PDO::FETCH_ASSOC));
