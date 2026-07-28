<?php
$dsn = 'mysql:host=127.0.0.1;dbname=si_foreign_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(255) NULL,
        instansi_id VARCHAR(255) NULL,
        action VARCHAR(50) NOT NULL,
        module VARCHAR(100) NOT NULL,
        entity_id VARCHAR(255) NULL,
        old_data LONGTEXT NULL,
        new_data LONGTEXT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Tabel audit_logs berhasil dibuat!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
