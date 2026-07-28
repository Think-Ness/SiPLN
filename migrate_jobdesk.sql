-- 1. Buat Tabel Master
CREATE TABLE `jobdesk_master_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_proses` varchar(100) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jobdesk_master_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `urut` tinyint(4) NOT NULL,
  `step_kode` varchar(50) NOT NULL,
  `step_nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `process_id` (`process_id`),
  CONSTRAINT `fk_jms_process` FOREIGN KEY (`process_id`) REFERENCES `jobdesk_master_process` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Rename existing tables
RENAME TABLE `mtb_perpanjangan_itas` TO `jobdesk_cases`;
RENAME TABLE `mtb_perpanjangan_step` TO `jobdesk_case_steps`;
RENAME TABLE `mtb_catatan_step` TO `jobdesk_step_notes`;

-- 3. Alter jobdesk_cases
ALTER TABLE `jobdesk_cases` 
  ADD COLUMN `process_id` int(11) NULL AFTER `id`,
  CHANGE COLUMN `exp_itas_lama` `tanggal_referensi` date NULL,
  ADD KEY `process_id` (`process_id`),
  ADD CONSTRAINT `fk_jc_process` FOREIGN KEY (`process_id`) REFERENCES `jobdesk_master_process` (`id`) ON DELETE SET NULL;

-- 4. Alter jobdesk_case_steps
ALTER TABLE `jobdesk_case_steps`
  CHANGE COLUMN `perpanjangan_id` `case_id` int(11) NOT NULL;

-- Keep constraints clean for jobdesk_case_steps (assuming no FK existed, or we leave it. Let's add FK for integrity)
-- ALTER TABLE `jobdesk_case_steps` ADD CONSTRAINT `fk_jcs_case` FOREIGN KEY (`case_id`) REFERENCES `jobdesk_cases` (`id`) ON DELETE CASCADE;

-- 5. Seed Master Data
INSERT INTO `jobdesk_master_process` (`id`, `nama_proses`, `aktif`) VALUES
(1, 'Perpanjangan ITAS', 1),
(2, 'Visa Baru', 1);

INSERT INTO `jobdesk_master_steps` (`process_id`, `urut`, `step_kode`, `step_nama`) VALUES
(1, 1, 'REKOM_DOMISILI_PENGAJUAN', 'Pengajuan Rekom Domisili'),
(1, 2, 'REKOM_DOMISILI_TERBIT', 'Terbit Rekom Domisili'),
(1, 3, 'REKOM_SINDI_PENGAJUAN', 'Pengajuan Rekom Sindi (Jakarta)'),
(1, 4, 'REKOM_SINDI_TERBIT', 'Terbit Rekom Sindi'),
(1, 5, 'ITAS_PENGAJUAN', 'Pengajuan Perpanjangan / Extend ITAS'),
(1, 6, 'SESI_FOTO', 'Sesi Foto & Penentuan Tanggal'),
(1, 7, 'ITAS_TERBIT', 'Terbit Kartu ITAS Baru'),
(1, 8, 'UPDATE_SISTEM', 'Update Data di Sistem'),

(2, 1, 'PENGAJUAN_BERKAS', 'Pengajuan Berkas ke Sindi'),
(2, 2, 'PEMBAYARAN_VISA', 'Pembayaran PNBP Visa'),
(2, 3, 'TERBIT_TELEX', 'Terbit E-Visa (Telex)'),
(2, 4, 'LAPOR_KEDUTAAN', 'Lapor Kedutaan & Pemasukan Paspor'),
(2, 5, 'STAMP_VISA', 'Stamp Visa di Paspor'),
(2, 6, 'UPDATE_SISTEM', 'Update Data di Sistem');

-- 6. Update existing cases to be ITAS
UPDATE `jobdesk_cases` SET `process_id` = 1 WHERE `process_id` IS NULL;
