<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE master_instansi ADD COLUMN capel_spreadsheet_headers TEXT NULL AFTER capel_mapping");
    echo "Added capel_spreadsheet_headers column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column capel_spreadsheet_headers already exists.\n";
    } else {
        echo "Error adding capel_spreadsheet_headers: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("ALTER TABLE master_instansi ADD COLUMN capel_display_columns TEXT NULL AFTER capel_spreadsheet_headers");
    echo "Added capel_display_columns column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column capel_display_columns already exists.\n";
    } else {
        echo "Error adding capel_display_columns: " . $e->getMessage() . "\n";
    }
}

