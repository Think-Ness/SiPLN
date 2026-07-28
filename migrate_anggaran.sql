-- ============================================
-- Migration: Fitur Anggaran Operasional
-- ============================================

-- 1. Master Pengajuan Anggaran (Per Bulan Hijriah)
CREATE TABLE IF NOT EXISTS `anggaran_pengajuan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `instansi` VARCHAR(100) NOT NULL COMMENT 'Kepengurusan / Instansi',
  `bulan_hijriah` VARCHAR(50) NOT NULL COMMENT 'e.g., Muharram 1448 H',
  `status` ENUM('draft', 'diajukan', 'disetujui', 'ditolak') NOT NULL DEFAULT 'draft',
  `total_ajuan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total_disetujui` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `catatan_admin` TEXT NULL,
  `created_by` INT(11) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_instansi` (`instansi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Item Pengajuan (Rincian Anggaran)
CREATE TABLE IF NOT EXISTS `anggaran_pengajuan_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` INT(11) NOT NULL,
  `nama_item` VARCHAR(255) NOT NULL,
  `nominal_ajuan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `nominal_disetujui` DECIMAL(15,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_pengajuan` (`pengajuan_id`),
  CONSTRAINT `fk_api_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `anggaran_pengajuan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Nota (Laporan Penggunaan Anggaran)
CREATE TABLE IF NOT EXISTS `anggaran_nota` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` INT(11) NOT NULL,
  `nomor_nota` VARCHAR(100) NOT NULL,
  `tanggal` DATE NOT NULL,
  `total_belanja` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `file_path` VARCHAR(255) NULL COMMENT 'Bukti foto nota',
  `keterangan` TEXT NULL,
  `created_by` INT(11) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pengajuan_nota` (`pengajuan_id`),
  CONSTRAINT `fk_an_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `anggaran_pengajuan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Item Nota (Rincian Belanja di Nota)
CREATE TABLE IF NOT EXISTS `anggaran_nota_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nota_id` INT(11) NOT NULL,
  `nama_barang` VARCHAR(255) NOT NULL,
  `qty` INT(11) NOT NULL DEFAULT 1,
  `harga_satuan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_nota` (`nota_id`),
  CONSTRAINT `fk_ani_nota` FOREIGN KEY (`nota_id`) REFERENCES `anggaran_nota`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Menambahkan Menu ke Sistem
INSERT INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut) 
VALUES ('main', 'Anggaran Operasional', '/anggaran', 'bi bi-wallet2', 'menu_anggaran', 40);
