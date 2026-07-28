<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Memulai migrasi CAPEL...\n";

    // 1. Buat tabel mtb_capel_draft
    echo "1. Membuat tabel mtb_capel_draft...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mtb_capel_draft (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp DATETIME NOT NULL,
            email VARCHAR(255) NULL,
            nama_lengkap VARCHAR(255) NOT NULL,
            tanggal_lahir DATE NULL,
            data_json JSON NULL,
            file_links JSON NULL,
            status_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Tambah ENUM status_santri di master_santri (jika belum ada)
    echo "2. Memperbarui kolom status_santri di master_santri...\n";
    try {
        $pdo->exec("
            ALTER TABLE master_santri 
            MODIFY COLUMN status_santri ENUM('Aktif', 'Inaktif', 'Lulus', 'Capel Syawwal', 'Capel Penampungan', 'Alumni') DEFAULT 'Aktif';
        ");
        
        // Migrate old CAPEL to Capel Syawwal (default)
        $pdo->exec("UPDATE master_santri SET status_santri = 'Capel Syawwal' WHERE kelas = 'CAPEL' AND status_santri = 'Aktif'");
        // Migrate old Graduation to Alumni
        $pdo->exec("UPDATE master_santri SET status_santri = 'Alumni' WHERE kelas = 'Graduation' AND status_santri = 'Aktif'");

    } catch (PDOException $e) {
        echo "Error alter master_santri (mungkin kolom tidak ada/berbeda): " . $e->getMessage() . "\n";
    }

    echo "\n=== Migrasi Database CAPEL Selesai! ===\n";

} catch (PDOException $e) {
    echo "Error saat migrasi: " . $e->getMessage() . "\n";
}
