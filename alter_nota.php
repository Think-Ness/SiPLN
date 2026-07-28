<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->exec("ALTER TABLE anggaran_nota_items ADD COLUMN satuan VARCHAR(50) DEFAULT NULL AFTER qty");
    $pdo->exec("ALTER TABLE anggaran_nota_items ADD COLUMN bagian VARCHAR(100) DEFAULT NULL AFTER satuan");
    echo "Columns added successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
