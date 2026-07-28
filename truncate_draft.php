<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->exec('TRUNCATE TABLE mtb_capel_draft');
echo "Table truncated successfully.";
