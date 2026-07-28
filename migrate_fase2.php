<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Memulai migrasi Fase 2 (Menambahkan Kolom Otoritas)...\n";
    
    // 1. Tambah kolom permissions di tabel users
    echo "1. Menambah kolom permissions di tabel users...\n";
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN permissions TEXT NULL DEFAULT NULL 
        AFTER role;
    ");

    // 2. Isi default otoritas untuk Super Admin dan Admin Instansi agar tidak terblokir
    echo "2. Memberikan akses awal ke para Admin...\n";
    
    $fullAkses = json_encode([
        'menu_master_data', 
        'menu_auto_rekap', 
        'menu_job_desk', 
        'menu_pemberkasan', 
        'menu_lain_lain',
        'action_edit_santri'
    ]);
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET permissions = :akses 
        WHERE role IN ('super_admin', 'admin_instansi')
    ");
    $stmt->execute([':akses' => $fullAkses]);

    echo "\n=== Migrasi Database Fase 2 Selesai dengan Sukses! ===\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Info: Kolom permissions sudah ada. Lanjut ke proses berikutnya.\n";
    } else {
        echo "Error saat migrasi: " . $e->getMessage() . "\n";
    }
}
