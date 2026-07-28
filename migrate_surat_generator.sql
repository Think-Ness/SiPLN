-- ============================================
-- Migration: Surat Generator Upgrade
-- ============================================

-- 1. Tambah kolom profil staf di tabel users
ALTER TABLE `users`
  ADD COLUMN `tempat_lahir` VARCHAR(100) NULL AFTER `nama_lengkap`,
  ADD COLUMN `tanggal_lahir` DATE NULL AFTER `tempat_lahir`,
  ADD COLUMN `nik` VARCHAR(50) NULL AFTER `tanggal_lahir`;

-- 2. Master Jenis Pengajuan
CREATE TABLE IF NOT EXISTS `surat_jenis_pengajuan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `jenis_pengajuan` VARCHAR(255) NOT NULL,
  `hal` VARCHAR(255) NOT NULL DEFAULT '',
  `isi` TEXT NULL,
  `kepada` VARCHAR(255) NOT NULL DEFAULT '',
  `tempat` VARCHAR(255) NOT NULL DEFAULT '',
  `surat_dibutuhkan` VARCHAR(100) NOT NULL DEFAULT 'SP,SK,SJ,ST' COMMENT 'Comma-separated: SP,SK,SJ,ST',
  `kantor` VARCHAR(255) NOT NULL DEFAULT '',
  `instansi_id` INT(11) NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_instansi` (`instansi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Mailing (batch surat)
CREATE TABLE IF NOT EXISTS `surat_mailing` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_mailing` VARCHAR(50) NOT NULL,
  `jenis_pengajuan_id` INT(11) NOT NULL,
  `mode` ENUM('sekaligus','perseorangan') NOT NULL DEFAULT 'perseorangan',
  `tanggal_surat` DATE NOT NULL,
  `catatan` TEXT NULL,
  `instansi_id` INT(11) NULL,
  `created_by` INT(11) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jenis` (`jenis_pengajuan_id`),
  KEY `idx_instansi` (`instansi_id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Santri dalam mailing
CREATE TABLE IF NOT EXISTS `surat_mailing_santri` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mailing_id` INT(11) NOT NULL,
  `kds` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mailing` (`mailing_id`),
  KEY `idx_kds` (`kds`),
  CONSTRAINT `fk_sms_mailing` FOREIGN KEY (`mailing_id`) REFERENCES `surat_mailing`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Surat yang di-generate
CREATE TABLE IF NOT EXISTS `surat_generated` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mailing_id` INT(11) NOT NULL,
  `tipe_surat` ENUM('SP','SK','SJ','ST') NOT NULL,
  `nomor_surat` VARCHAR(100) NOT NULL,
  `tanggal_surat` DATE NOT NULL,
  `hal` VARCHAR(255) NOT NULL DEFAULT '',
  `isi` TEXT NULL,
  `kepada` VARCHAR(255) NOT NULL DEFAULT '',
  `tempat` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mailing` (`mailing_id`),
  KEY `idx_tipe` (`tipe_surat`),
  CONSTRAINT `fk_sg_mailing` FOREIGN KEY (`mailing_id`) REFERENCES `surat_mailing`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
