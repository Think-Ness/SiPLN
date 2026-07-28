<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simulate what store does
    $instansi = 'Test';
    $bulanHijriah = 'Muharram';
    $totalAjuan = 1000;
    
    // Insert master
    $stmt = $db->prepare("INSERT INTO anggaran_pengajuan (instansi, bulan_hijriah, status, total_ajuan, tanggal_pengajuan) VALUES (?, ?, 'diajukan', ?, ?)");
    $stmt->execute([$instansi, $bulanHijriah, $totalAjuan, date('Y-m-d H:i:s')]);
    
    $pengajuanId = $db->lastInsertId();
    echo "Master inserted. ID: $pengajuanId\n";
    
    // Insert item
    $stmt = $db->prepare("INSERT INTO anggaran_pengajuan_items (pengajuan_id, nama_item, qty, satuan, bagian, harga_satuan, nominal_ajuan, nominal_disetujui) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$pengajuanId, 'Test Item', 1, 'pcs', 'IT', 1000, 1000]);
    
    echo "Item inserted.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
