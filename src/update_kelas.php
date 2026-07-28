<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->exec("UPDATE master_santri SET kelas = 'CAPEL PERSIAPAN' WHERE kelas = 'CAPEL' AND (status_santri LIKE '%Persiapan%' OR status_santri LIKE '%Penampungan%')");
$pdo->exec("UPDATE master_santri SET kelas = 'CAPEL PENERIMAAN' WHERE kelas = 'CAPEL' AND (status_santri LIKE '%Penerimaan%' OR status_santri LIKE '%Syawwal%')");
echo 'Done';
