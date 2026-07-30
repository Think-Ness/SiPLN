-- 1. Tabel untuk menyimpan master template
CREATE TABLE IF NOT EXISTS `surat_template_dinamis` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_template` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `instansi_id` INT(11) NOT NULL,
  `peruntukan` ENUM('sekaligus','perseorangan') NOT NULL DEFAULT 'perseorangan',
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_instansi` (`instansi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Pivot tabel antara jenis_pengajuan dan template yang digunakan
CREATE TABLE IF NOT EXISTS `surat_jenis_pengajuan_template` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `jenis_pengajuan_id` INT(11) NOT NULL,
  `template_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_jenis` (`jenis_pengajuan_id`),
  KEY `idx_template` (`template_id`),
  CONSTRAINT `fk_sjpt_jenis` FOREIGN KEY (`jenis_pengajuan_id`) REFERENCES `surat_jenis_pengajuan`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sjpt_template` FOREIGN KEY (`template_id`) REFERENCES `surat_template_dinamis`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Modifikasi tabel surat_generated untuk menampung ID template yang dipakai
ALTER TABLE `surat_generated` 
  ADD COLUMN `template_id` INT(11) NULL AFTER `mailing_id`;

-- Karena tipe_surat sebelumnya ENUM('SP','SK','SJ','ST') NOT NULL, kita ubah jadi VARCHAR untuk fleksibilitas log lama
ALTER TABLE `surat_generated`
  MODIFY COLUMN `tipe_surat` VARCHAR(50) NULL;
