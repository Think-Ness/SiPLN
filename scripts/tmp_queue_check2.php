<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 
print_r($pdo->query('SELECT kategori_folder FROM capel_download_queue LIMIT 5')->fetchAll(PDO::FETCH_ASSOC));
