<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root','');
$rows = $pdo->query('SELECT id, nama_lengkap FROM mtb_capel_draft LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
