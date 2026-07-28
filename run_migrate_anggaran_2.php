<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop existing tables just to be clean, since there's no production data yet or we can alter
    // The previous migration was just run a few minutes ago. Let's just drop and recreate or alter.
    // Alter is safer
    
    $sql = "
    ALTER TABLE anggaran_pengajuan 
      ADD COLUMN tanggal_pengajuan DATETIME NULL AFTER status,
      ADD COLUMN tanggal_disetujui DATETIME NULL AFTER tanggal_pengajuan,
      ADD COLUMN tanggal_selesai DATETIME NULL AFTER tanggal_disetujui;

    ALTER TABLE anggaran_pengajuan_items
      ADD COLUMN qty INT DEFAULT 1 AFTER nama_item,
      ADD COLUMN satuan VARCHAR(50) NULL AFTER qty,
      ADD COLUMN harga_satuan DECIMAL(15,2) DEFAULT 0 AFTER satuan,
      ADD COLUMN bagian VARCHAR(100) NULL AFTER harga_satuan;
      
    ALTER TABLE anggaran_pengajuan CHANGE status status ENUM('draft', 'diajukan', 'disetujui', 'ditolak', 'selesai') NOT NULL DEFAULT 'draft';
    ";
    
    $db->exec($sql);
    echo "Anggaran Migration 2 Success!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
