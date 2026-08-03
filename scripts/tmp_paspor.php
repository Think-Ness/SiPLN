<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 
print_r($pdo->query('SELECT * FROM mtb_paspor WHERE kds = 952')->fetch(PDO::FETCH_ASSOC));
print_r($pdo->query('SELECT kode, nama FROM master_santri WHERE kds = 952')->fetch(PDO::FETCH_ASSOC));
