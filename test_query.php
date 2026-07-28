<?php
require 'vendor/autoload.php';
$db = require 'config/db.php';
$pdo = new PDO($db['dsn'], $db['username'], $db['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $stmt = $pdo->query("SELECT s.kds, p.no_paspor FROM master_santri s LEFT JOIN mtb_paspor p ON p.kds = s.kds AND p.aktif = 1 GROUP BY s.kds LIMIT 1");
    print_r($stmt->fetchAll());
    echo 'OK';
} catch (Exception $e) {
    echo $e->getMessage();
}
