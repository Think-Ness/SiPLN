<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 
print_r($pdo->query("SELECT kode, nama, nama_ayah, path_foto FROM master_santri WHERE kode LIKE '%MUH26%'")->fetchAll(PDO::FETCH_ASSOC));
