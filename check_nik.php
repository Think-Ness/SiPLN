<?php
$db = new PDO('mysql:host=localhost;dbname=si_foreign_db', 'root', '');
$stmt = $db->query("SELECT stambuk, no_sktt, no_ic FROM master_santri WHERE stambuk = '88396'");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
