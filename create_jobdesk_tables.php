<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql1 = "
CREATE TABLE IF NOT EXISTS mtb_perpanjangan_itas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kds VARCHAR(50) NOT NULL,
    exp_itas_lama DATE NOT NULL,
    tgl_mulai_proses DATE NOT NULL,
    status ENUM('aktif', 'selesai', 'batal') DEFAULT 'aktif',
    catatan_umum TEXT,
    created_by VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_kds (kds)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$sql2 = "
CREATE TABLE IF NOT EXISTS mtb_perpanjangan_step (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perpanjangan_id INT NOT NULL,
    step_urut TINYINT NOT NULL,
    step_kode VARCHAR(50) NOT NULL,
    step_nama VARCHAR(100) NOT NULL,
    status ENUM('pending', 'proses', 'selesai', 'blocked') DEFAULT 'pending',
    tgl_target DATE,
    tgl_realisasi DATE,
    updated_by VARCHAR(100),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perpanjangan_id) REFERENCES mtb_perpanjangan_itas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$sql3 = "
CREATE TABLE IF NOT EXISTS mtb_catatan_step (
    id INT AUTO_INCREMENT PRIMARY KEY,
    step_id INT NOT NULL,
    tipe ENUM('info', 'kekurangan', 'tindakan', 'selesai') DEFAULT 'info',
    isi TEXT,
    berkas_kurang TEXT,
    created_by VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (step_id) REFERENCES mtb_perpanjangan_step(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql1);
    echo "Table mtb_perpanjangan_itas created.\n";
    $pdo->exec($sql2);
    echo "Table mtb_perpanjangan_step created.\n";
    $pdo->exec($sql3);
    echo "Table mtb_catatan_step created.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
