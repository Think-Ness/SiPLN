<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=si_foreign_db", "root", "");
$stmt = $db->query("DESCRIBE master_instansi");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
