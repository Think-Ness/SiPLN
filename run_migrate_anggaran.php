<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = file_get_contents(__DIR__ . '/migrate_anggaran.sql');
    $db->exec($sql);
    echo "Anggaran Migration Success!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
