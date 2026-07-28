<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 
print_r($pdo->query('SELECT status, count(*) FROM capel_download_queue GROUP BY status')->fetchAll(PDO::FETCH_ASSOC));
