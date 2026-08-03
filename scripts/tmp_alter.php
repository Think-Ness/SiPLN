<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root','');
$pdo->exec('ALTER TABLE capel_download_queue MODIFY kategori_folder VARCHAR(255)');
echo "Altered table!";
