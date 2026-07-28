<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $queries = [
        // 1. Ensure master_instansi columns exist (Safe Alters)
        "ALTER TABLE master_instansi ADD COLUMN kode_instansi VARCHAR(50) NULL",
        "ALTER TABLE master_instansi ADD COLUMN def_kepengurusan VARCHAR(100) NULL",
        "ALTER TABLE master_instansi ADD COLUMN def_pondok VARCHAR(100) NULL",
        
        // 2. Create users table
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `nama_lengkap` VARCHAR(150) NOT NULL,
            `role` ENUM('super_admin', 'admin_instansi', 'staff_instansi') NOT NULL,
            `instansi_id` INT(11) NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`instansi_id`) REFERENCES `master_instansi`(`kode`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // 3. Create data_change_requests table
        "CREATE TABLE IF NOT EXISTS `data_change_requests` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kds_santri` VARCHAR(50) NOT NULL,
            `requested_by_instansi` INT(11) NOT NULL,
            `approved_by_instansi` INT(11) NOT NULL,
            `field_name` VARCHAR(100) NOT NULL,
            `old_value` TEXT NULL,
            `new_value` TEXT NULL,
            `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            `requested_by_user` INT NULL,
            `actioned_by_user` INT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`requested_by_instansi`) REFERENCES `master_instansi`(`kode`) ON DELETE CASCADE,
            FOREIGN KEY (`approved_by_instansi`) REFERENCES `master_instansi`(`kode`) ON DELETE CASCADE,
            FOREIGN KEY (`requested_by_user`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`actioned_by_user`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    echo "=== Memulai Migrasi Fase 1 ===\n";
    foreach ($queries as $i => $query) {
        try {
            $pdo->exec($query);
            echo "[SUKSES] Query $i dieksekusi.\n";
        } catch (PDOException $e) {
            // Ignore column exists errors for ALTER
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "[SKIPPED] Kolom sudah ada: $i\n";
            } else {
                echo "[INFO/ERROR] Query $i: " . $e->getMessage() . "\n";
            }
        }
    }

    // 4. Inject Default Super Admin (Password: password)
    $hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'superadmin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (username, password_hash, nama_lengkap, role, instansi_id) VALUES ('superadmin', '$hash', 'System Super Admin', 'super_admin', NULL)");
        echo "[SUKSES] Default Super Admin (superadmin:password) berhasil dibuat!\n";
    } else {
        echo "[SKIPPED] Super Admin sudah ada.\n";
    }

    echo "=== Migrasi Fase 1 Selesai ===\n";

} catch (PDOException $e) {
    echo "KONEKSI DATABASE GAGAL: " . $e->getMessage();
}
