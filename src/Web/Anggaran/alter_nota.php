<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $db->exec("ALTER TABLE anggaran_nota ADD COLUMN bagian VARCHAR(150) NULL AFTER tanggal");
    echo "Column added";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
