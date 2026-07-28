<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create app_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS app_settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel app_settings berhasil dibuat/diverifikasi.\n";

    // 2. Insert default settings for pindahan_allowed_fields
    $defaultFields = json_encode(['kelas', 'rayon', 'pondok', 'kamar']);
    $stmt = $pdo->prepare("
        INSERT INTO app_settings (setting_key, setting_value) 
        VALUES ('pindahan_allowed_fields', :val)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([':val' => $defaultFields]);
    echo "Pengaturan awal 'pindahan_allowed_fields' berhasil diinjeksi.\n";

    // 3. Create santri_edit_requests table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS santri_edit_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kds VARCHAR(50) NOT NULL,
            requested_by_instansi_id INT NOT NULL,
            target_kepengurusan VARCHAR(255) NOT NULL,
            requested_changes JSON NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_target_kepengurusan (target_kepengurusan, status),
            INDEX idx_kds (kds)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel santri_edit_requests berhasil dibuat/diverifikasi.\n";
    echo "\nMigrasi Fase Lanjutan SELESAI!\n";

} catch (Exception $e) {
    echo "GAGAL: " . $e->getMessage() . "\n";
}
