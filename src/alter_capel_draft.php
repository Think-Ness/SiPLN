<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->exec('ALTER TABLE mtb_capel_draft ADD COLUMN instansi_id INT(11) NULL');
echo "Done";
