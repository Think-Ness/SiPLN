<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$c = $pdo->exec("UPDATE master_santri SET kelas='Alumni' WHERE kelas IN ('Grad', 'GRADE')");
echo "Updated $c rows.";
