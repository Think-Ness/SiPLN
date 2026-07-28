<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->exec('ALTER TABLE master_instansi ADD COLUMN spreadsheet_id VARCHAR(255) NULL');
$pdo->exec('ALTER TABLE capel_download_queue ADD COLUMN instansi_id INT(11) NULL');
echo "Tables altered successfully.";
