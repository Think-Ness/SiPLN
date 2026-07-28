<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->exec("ALTER TABLE capel_download_queue MODIFY COLUMN status ENUM('pending', 'downloading', 'completed', 'failed', 'paused') DEFAULT 'pending'");
$pdo->exec("UPDATE capel_download_queue SET status = 'paused' WHERE status NOT IN ('pending', 'downloading', 'completed', 'failed', 'paused') OR status IS NULL OR status = ''");
echo 'Fixed DB ENUM and recovered lost downloads.';
