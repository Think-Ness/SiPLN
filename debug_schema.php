<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $stmt = $pdo->query('SHOW CREATE TABLE master_instansi');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Create Table'] . "\n";
} catch(Exception $e) {
    echo $e->getMessage();
}
