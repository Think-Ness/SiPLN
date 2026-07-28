<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$sql = "
CREATE TABLE IF NOT EXISTS `capel_download_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `draft_id` int(11) NOT NULL,
  `kode_santri` varchar(100) NOT NULL,
  `file_url` text NOT NULL,
  `kategori_folder` varchar(50) NOT NULL,
  `jenis_dokumen` varchar(100) NOT NULL,
  `status` enum('pending','downloading','completed','failed') DEFAULT 'pending',
  `error_msg` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$pdo->exec($sql);
echo "Tabel capel_download_queue berhasil dibuat.";
