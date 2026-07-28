<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 
$pdo->query("UPDATE capel_download_queue SET status = 'pending' WHERE id = 1");
