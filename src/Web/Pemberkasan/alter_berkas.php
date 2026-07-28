<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->query("SHOW COLUMNS FROM mtb_berkas_penting LIKE 'is_public'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mtb_berkas_penting ADD COLUMN is_public TINYINT(1) DEFAULT 0 AFTER kode");
        echo "Column is_public added.\n";
    } else {
        echo "Column is_public already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
