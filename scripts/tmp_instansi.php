<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root','');
$rows = $pdo->query('SELECT kode, path_folder FROM master_instansi')->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
