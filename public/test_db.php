<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $db->query("SELECT * FROM mtb_paspor LIMIT 1");
print_r(array_keys($stmt->fetch(PDO::FETCH_ASSOC)));
