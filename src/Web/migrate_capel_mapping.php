<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE master_instansi ADD COLUMN capel_spreadsheet_range VARCHAR(255) NULL DEFAULT 'Form Responses 1' AFTER spreadsheet_id");
    echo "Added capel_spreadsheet_range column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column capel_spreadsheet_range already exists.\n";
    } else {
        echo "Error adding capel_spreadsheet_range: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("ALTER TABLE master_instansi ADD COLUMN capel_mapping TEXT NULL AFTER capel_spreadsheet_range");
    echo "Added capel_mapping column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column capel_mapping already exists.\n";
    } else {
        echo "Error adding capel_mapping: " . $e->getMessage() . "\n";
    }
}
