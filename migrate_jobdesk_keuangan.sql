-- ================================================================
-- MIGRASI: Sistem Keuangan Birokrasi Job Desk
-- Tanggal: 2026-06-11
-- Deskripsi: Menambahkan tabel tarif per proses dan pencatatan
--            pembayaran per case (santri → staf → instansi)
-- ================================================================

-- 1. Tabel Tarif Default Per Proses
-- Menyimpan biaya resmi ke pemerintah dan biaya yang ditagih ke santri
CREATE TABLE IF NOT EXISTS `jobdesk_process_tarif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `biaya_instansi` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya resmi ke pemerintah',
  `biaya_santri` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya yang ditagih ke santri',
  `keterangan` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_process_tarif` (`process_id`),
  CONSTRAINT `fk_jpt_process` FOREIGN KEY (`process_id`) 
    REFERENCES `jobdesk_master_process` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Pembayaran Per Case (Per Santri Per Proses)
-- Setiap kali case dibuat, 1 record payment otomatis di-insert
CREATE TABLE IF NOT EXISTS `jobdesk_case_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `nominal_santri` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Uang yang diterima dari santri',
  `nominal_instansi` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Uang yang dibayarkan ke instansi pemerintah',
  `selisih_operasional` decimal(15,2) GENERATED ALWAYS AS 
    (`nominal_santri` - `nominal_instansi`) STORED COMMENT 'Selisih masuk kas operasional (auto)',
  `status_bayar_santri` enum('belum','lunas') NOT NULL DEFAULT 'belum',
  `tgl_bayar_santri` date DEFAULT NULL,
  `status_bayar_instansi` enum('belum','lunas') NOT NULL DEFAULT 'belum',
  `tgl_bayar_instansi` date DEFAULT NULL,
  `bukti_bayar_path` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_case_payment` (`case_id`),
  CONSTRAINT `fk_jcp_case` FOREIGN KEY (`case_id`) 
    REFERENCES `jobdesk_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed tarif default untuk proses yang sudah ada (jika ada)
-- Perpanjangan ITAS: biaya instansi 4.5 juta, biaya santri 5 juta
INSERT IGNORE INTO `jobdesk_process_tarif` (`process_id`, `biaya_instansi`, `biaya_santri`, `keterangan`)
SELECT id, 4500000, 5000000, 'Tarif default perpanjangan ITAS'
FROM `jobdesk_master_process` WHERE id = 1;

-- Visa Baru: biaya instansi 3 juta, biaya santri 3.5 juta (contoh)
INSERT IGNORE INTO `jobdesk_process_tarif` (`process_id`, `biaya_instansi`, `biaya_santri`, `keterangan`)
SELECT id, 3000000, 3500000, 'Tarif default pengajuan visa baru'
FROM `jobdesk_master_process` WHERE id = 2;

-- 4. Auto-insert payment records untuk case yang sudah ada tapi belum punya record payment
INSERT IGNORE INTO `jobdesk_case_payment` (`case_id`, `nominal_santri`, `nominal_instansi`, `created_by`)
SELECT j.id, COALESCE(t.biaya_santri, 0), COALESCE(t.biaya_instansi, 0), 'Migrasi Sistem'
FROM `jobdesk_cases` j
LEFT JOIN `jobdesk_process_tarif` t ON j.process_id = t.process_id
WHERE j.id NOT IN (SELECT case_id FROM `jobdesk_case_payment`);
