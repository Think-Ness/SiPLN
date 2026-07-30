<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check current columns
$cols = $pdo->query('DESCRIBE surat_template_dinamis')->fetchAll(PDO::FETCH_COLUMN);
echo "Current columns: " . implode(', ', $cols) . "\n";

// Drop instansi_id if exists, add instansi_tujuan if not exists
if (in_array('instansi_id', $cols) && !in_array('instansi_tujuan', $cols)) {
    $pdo->exec('ALTER TABLE surat_template_dinamis DROP COLUMN instansi_id, ADD COLUMN instansi_tujuan VARCHAR(100) NOT NULL AFTER file_path');
    echo "OK: instansi_id dropped, instansi_tujuan added\n";
} elseif (!in_array('instansi_tujuan', $cols)) {
    $pdo->exec('ALTER TABLE surat_template_dinamis ADD COLUMN instansi_tujuan VARCHAR(100) NOT NULL AFTER file_path');
    echo "OK: instansi_tujuan added\n";
} else {
    echo "SKIP: instansi_tujuan already exists\n";
}

// Verify
$cols2 = $pdo->query('DESCRIBE surat_template_dinamis')->fetchAll(PDO::FETCH_COLUMN);
echo "Updated columns: " . implode(', ', $cols2) . "\n";
