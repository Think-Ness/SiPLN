<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE users ADD COLUMN ttl VARCHAR(255) NULL AFTER nama_lengkap");
    echo "Column ttl added successfully.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column ttl already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
