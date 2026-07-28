-- Database Export for Offline App Installer
-- Auto-generated schema with master data only

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for `anggaran_nota`
DROP TABLE IF EXISTS `anggaran_nota`;
CREATE TABLE `anggaran_nota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` int(11) NOT NULL,
  `nomor_nota` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `bagian` varchar(150) DEFAULT NULL,
  `total_belanja` decimal(15,2) NOT NULL DEFAULT 0.00,
  `file_path` varchar(255) DEFAULT NULL COMMENT 'Bukti foto nota',
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pengajuan_nota` (`pengajuan_id`),
  CONSTRAINT `fk_an_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `anggaran_pengajuan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `anggaran_nota_items`
DROP TABLE IF EXISTS `anggaran_nota_items`;
CREATE TABLE `anggaran_nota_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nota_id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `satuan` varchar(50) DEFAULT NULL,
  `bagian` varchar(100) DEFAULT NULL,
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_nota` (`nota_id`),
  CONSTRAINT `fk_ani_nota` FOREIGN KEY (`nota_id`) REFERENCES `anggaran_nota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `anggaran_pengajuan`
DROP TABLE IF EXISTS `anggaran_pengajuan`;
CREATE TABLE `anggaran_pengajuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instansi` varchar(100) NOT NULL COMMENT 'Kepengurusan / Instansi',
  `bulan_hijriah` varchar(50) NOT NULL COMMENT 'e.g., Muharram 1448 H',
  `status` varchar(50) DEFAULT 'draft',
  `tanggal_pengajuan` datetime DEFAULT NULL,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `total_ajuan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_disetujui` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan_admin` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_instansi` (`instansi`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `anggaran_pengajuan_items`
DROP TABLE IF EXISTS `anggaran_pengajuan_items`;
CREATE TABLE `anggaran_pengajuan_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` int(11) NOT NULL,
  `nama_item` varchar(255) NOT NULL,
  `qty` int(11) DEFAULT 1,
  `satuan` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT 0.00,
  `bagian` varchar(100) DEFAULT NULL,
  `nominal_ajuan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nominal_disetujui` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_pengajuan` (`pengajuan_id`),
  CONSTRAINT `fk_api_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `anggaran_pengajuan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `app_settings`
DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `app_settings`
INSERT INTO `app_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES 
('pindahan_allowed_fields', '[\"kelas\",\"rayon\",\"alamat\",\"nama_ayah\",\"nama_ibu\",\"no_ayah\",\"no_ibu\",\"aktif\",\"keberadaan_paspor\",\"ukuran_baju\"]', '2026-06-03 21:39:14');

-- Table structure for `audit_logs`
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `instansi_id` varchar(255) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `entity_id` varchar(255) DEFAULT NULL,
  `old_data` longtext DEFAULT NULL,
  `new_data` longtext DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=890 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `capel_download_queue`
DROP TABLE IF EXISTS `capel_download_queue`;
CREATE TABLE `capel_download_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `draft_id` int(11) NOT NULL,
  `kode_santri` varchar(100) NOT NULL,
  `file_url` text NOT NULL,
  `kategori_folder` varchar(255) DEFAULT NULL,
  `jenis_dokumen` varchar(100) NOT NULL,
  `status` enum('pending','downloading','completed','failed','paused') DEFAULT 'pending',
  `error_msg` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `instansi_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=919 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `data_change_requests`
DROP TABLE IF EXISTS `data_change_requests`;
CREATE TABLE `data_change_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kds_santri` varchar(50) NOT NULL,
  `requested_by_instansi` int(11) NOT NULL,
  `approved_by_instansi` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_by_user` int(11) DEFAULT NULL,
  `actioned_by_user` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `requested_by_instansi` (`requested_by_instansi`),
  KEY `approved_by_instansi` (`approved_by_instansi`),
  KEY `requested_by_user` (`requested_by_user`),
  KEY `actioned_by_user` (`actioned_by_user`),
  CONSTRAINT `data_change_requests_ibfk_1` FOREIGN KEY (`requested_by_instansi`) REFERENCES `master_instansi` (`kode`) ON DELETE CASCADE,
  CONSTRAINT `data_change_requests_ibfk_2` FOREIGN KEY (`approved_by_instansi`) REFERENCES `master_instansi` (`kode`) ON DELETE CASCADE,
  CONSTRAINT `data_change_requests_ibfk_3` FOREIGN KEY (`requested_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `data_change_requests_ibfk_4` FOREIGN KEY (`actioned_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `jobdesk_case_payment`
DROP TABLE IF EXISTS `jobdesk_case_payment`;
CREATE TABLE `jobdesk_case_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `nominal_santri` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Uang yang diterima dari santri',
  `nominal_instansi` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Uang yang dibayarkan ke instansi pemerintah',
  `selisih_operasional` decimal(15,2) GENERATED ALWAYS AS (`nominal_santri` - `nominal_instansi`) STORED COMMENT 'Selisih masuk kas operasional (auto)',
  `status_bayar_santri` enum('belum','lunas') NOT NULL DEFAULT 'belum',
  `tgl_bayar_santri` date DEFAULT NULL,
  `status_bayar_instansi` enum('belum','lunas') NOT NULL DEFAULT 'belum',
  `tgl_bayar_instansi` date DEFAULT NULL,
  `bukti_bayar_path` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_case_payment` (`case_id`),
  CONSTRAINT `fk_jcp_case` FOREIGN KEY (`case_id`) REFERENCES `jobdesk_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `jobdesk_case_steps`
DROP TABLE IF EXISTS `jobdesk_case_steps`;
CREATE TABLE `jobdesk_case_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `step_urut` tinyint(4) NOT NULL,
  `step_kode` varchar(50) NOT NULL,
  `step_nama` varchar(100) NOT NULL,
  `status` enum('pending','proses','selesai','blocked') DEFAULT 'pending',
  `tgl_target` date DEFAULT NULL,
  `tgl_realisasi` date DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `perpanjangan_id` (`case_id`),
  CONSTRAINT `jobdesk_case_steps_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `jobdesk_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `jobdesk_cases`
DROP TABLE IF EXISTS `jobdesk_cases`;
CREATE TABLE `jobdesk_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) DEFAULT NULL,
  `kds` varchar(50) NOT NULL,
  `tanggal_referensi` date DEFAULT NULL,
  `tgl_mulai_proses` date NOT NULL,
  `status` enum('aktif','selesai','batal') DEFAULT 'aktif',
  `catatan_umum` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kds` (`kds`),
  KEY `process_id` (`process_id`),
  CONSTRAINT `fk_jc_process` FOREIGN KEY (`process_id`) REFERENCES `jobdesk_master_process` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `jobdesk_kategori_pengeluaran`
DROP TABLE IF EXISTS `jobdesk_kategori_pengeluaran`;
CREATE TABLE `jobdesk_kategori_pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instansi_id` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-tag',
  `warna` varchar(20) DEFAULT '#6c757d',
  `urut` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `jobdesk_kategori_pengeluaran`
INSERT INTO `jobdesk_kategori_pengeluaran` (`id`, `instansi_id`, `nama`, `icon`, `warna`, `urut`, `is_active`, `created_at`) VALUES 
('1', NULL, 'ATK & Perlengkapan', 'bi-paperclip', '#0d6efd', '1', '1', '2026-06-27 16:04:04'),
('2', NULL, 'Transportasi', 'bi-truck', '#198754', '2', '1', '2026-06-27 16:04:04'),
('3', NULL, 'Konsumsi', 'bi-cup-hot', '#fd7e14', '3', '1', '2026-06-27 16:04:04'),
('4', NULL, 'Fotokopi & Cetak', 'bi-printer', '#6610f2', '4', '1', '2026-06-27 16:04:04'),
('5', NULL, 'Komunikasi', 'bi-telephone', '#20c997', '5', '1', '2026-06-27 16:04:04'),
('6', NULL, 'Lainnya', 'bi-box', '#6c757d', '6', '1', '2026-06-27 16:04:04');

-- Table structure for `jobdesk_master_process`
DROP TABLE IF EXISTS `jobdesk_master_process`;
CREATE TABLE `jobdesk_master_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_proses` varchar(100) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `urut` int(11) NOT NULL DEFAULT 1,
  `instansi_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `jobdesk_master_process`
INSERT INTO `jobdesk_master_process` (`id`, `nama_proses`, `aktif`, `created_at`, `urut`, `instansi_id`) VALUES 
('1', 'Perpanjangan ITAS', '1', '2026-06-01 10:14:17', '1', NULL),
('2', 'Pengajuan Visa Pelajar Baru', '1', '2026-06-01 10:14:17', '2', NULL),
('3', 'Perubahan No Paspor Baru', '1', '2026-06-01 20:44:51', '3', NULL),
('5', 'Perpanjangan ITAS', '1', '2026-06-27 00:20:38', '1', '1'),
('6', 'Pengajuan Visa Pelajar Baru', '1', '2026-06-27 00:20:38', '2', '1'),
('8', 'Perubahan No Paspor Baru', '1', '2026-06-27 10:58:35', '1', '3'),
('9', 'Perubahan No Paspor Baru', '1', '2026-06-27 14:47:30', '3', '1');

-- Table structure for `jobdesk_master_steps`
DROP TABLE IF EXISTS `jobdesk_master_steps`;
CREATE TABLE `jobdesk_master_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `urut` tinyint(4) NOT NULL,
  `step_kode` varchar(50) NOT NULL,
  `step_nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `process_id` (`process_id`),
  CONSTRAINT `fk_jms_process` FOREIGN KEY (`process_id`) REFERENCES `jobdesk_master_process` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `jobdesk_master_steps`
INSERT INTO `jobdesk_master_steps` (`id`, `process_id`, `urut`, `step_kode`, `step_nama`) VALUES 
('1', '1', '2', 'PENGAJUAN_REKOM_DOMISILI', 'Pengajuan Rekom Domisili'),
('2', '1', '3', 'TERBIT_REKOM_DOMISILI', 'Terbit Rekom Domisili'),
('3', '1', '4', 'PENGAJUAN_REKOM_SINDI__JAKARTA_', 'Pengajuan Rekom Sindi (Jakarta)'),
('4', '1', '5', 'TERBIT_REKOM_SINDI', 'Terbit Rekom Sindi'),
('5', '1', '6', 'PENGAJUAN_PERPANJANGAN___EXTEND_ITAS', 'Pengajuan Perpanjangan / Extend ITAS'),
('6', '1', '8', 'SESI_FOTO___PENENTUAN_TANGGAL', 'Sesi Foto & Penentuan Tanggal'),
('7', '1', '9', 'TERBIT_KARTU_ITAS_BARU', 'Terbit Kartu ITAS Baru'),
('8', '1', '10', 'UPDATE_DATA_DI_SISTEM', 'Update Data di Sistem'),
('9', '2', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN__T', 'Pembuatan Surat Permohonan, Keterangan, Jaminan, Tugas (opsional)'),
('10', '2', '2', 'PENGAJUAN_REKOM_DOMISILI', 'Pengajuan Rekom Domisili'),
('11', '2', '3', 'TERBIT_REKOM_DOMISILI', 'Terbit Rekom Domisili'),
('12', '2', '4', 'PENGAJUAN_REKOM_SINDI__JAKARTA_', 'Pengajuan Rekom Sindi (Jakarta)'),
('13', '2', '5', 'TERBIT_REKOM_SINDI', 'Terbit Rekom Sindi'),
('14', '2', '6', 'PENGAJUAN_VISA_PELAJAR', 'Pengajuan VISA Pelajar'),
('21', '1', '7', 'PEMBAYARAN_PERPANJAGAN_ITAS', 'Pembayaran Perpanjagan ITAS'),
('22', '2', '7', 'PEMBAYARAN_VISA_PELAJAR', 'Pembayaran VISA Pelajar'),
('23', '2', '8', 'TERBIT_VISA_PELAJAR', 'Terbit VISA Pelajar'),
('24', '1', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN__T', 'Pembuatan Surat Permohonan, Keterangan, Jaminan, Tugas (opsional)'),
('25', '1', '11', 'DOWNLOAD_PDF_ITAS_DAN_MERAPIKAN_DI_FOLDER_DOKUMEN_', 'Download Pdf ITAS dan Merapikan Di Folder Dokumen PLN'),
('26', '2', '9', 'DOWNLOAD_PDF_VISA_DAN_MERAPIKAN_DI_FOLDER_DOKUMEN_', 'Download Pdf VISA dan Merapikan Di Folder Dokumen PLN'),
('27', '3', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN_DA', 'Pembuatan Surat Permohonan, Keterangan, Jaminan dan KTP Sponsor'),
('28', '3', '2', 'PERSIAPAN_BERKAS_E_VISA__ITAS__PASPOR_LAMA_DAN_PAS', 'Persiapan Berkas E-VISA, ITAS, Paspor Lama dan Paspor Baru'),
('29', '3', '3', 'PENGAJUAN_PERUBAHAN_PASPOR', 'Pengajuan Perubahan Paspor'),
('30', '3', '4', 'TERBIT_HASIL_PENGAJUAN', 'Terbit Hasil Pengajuan'),
('32', '5', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN__T', 'Pembuatan Surat Permohonan, Keterangan, Jaminan, Tugas (opsional)'),
('33', '5', '2', 'PENGAJUAN_REKOM_DOMISILI', 'Pengajuan Rekom Domisili'),
('34', '5', '3', 'TERBIT_REKOM_DOMISILI', 'Terbit Rekom Domisili'),
('35', '5', '4', 'PENGAJUAN_REKOM_SINDI__JAKARTA_', 'Pengajuan Rekom Sindi (Jakarta)'),
('36', '5', '5', 'TERBIT_REKOM_SINDI', 'Terbit Rekom Sindi'),
('37', '5', '6', 'PENGAJUAN_PERPANJANGAN___EXTEND_ITAS', 'Pengajuan Perpanjangan / Extend ITAS'),
('38', '5', '7', 'PEMBAYARAN_PERPANJAGAN_ITAS', 'Pembayaran Perpanjagan ITAS'),
('39', '5', '8', 'SESI_FOTO___PENENTUAN_TANGGAL', 'Sesi Foto & Penentuan Tanggal'),
('40', '5', '9', 'TERBIT_KARTU_ITAS_BARU', 'Terbit Kartu ITAS Baru'),
('41', '5', '10', 'UPDATE_DATA_DI_SISTEM', 'Update Data di Sistem'),
('42', '5', '11', 'DOWNLOAD_PDF_ITAS_DAN_MERAPIKAN_DI_FOLDER_DOKUMEN_', 'Download Pdf ITAS dan Merapikan Di Folder Dokumen PLN'),
('43', '6', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN__T', 'Pembuatan Surat Permohonan, Keterangan, Jaminan, Tugas (opsional)'),
('44', '6', '2', 'PENGAJUAN_REKOM_DOMISILI', 'Pengajuan Rekom Domisili'),
('45', '6', '3', 'TERBIT_REKOM_DOMISILI', 'Terbit Rekom Domisili'),
('46', '6', '4', 'PENGAJUAN_REKOM_SINDI__JAKARTA_', 'Pengajuan Rekom Sindi (Jakarta)'),
('47', '6', '5', 'TERBIT_REKOM_SINDI', 'Terbit Rekom Sindi'),
('48', '6', '6', 'PENGAJUAN_VISA_PELAJAR', 'Pengajuan VISA Pelajar'),
('49', '6', '7', 'PEMBAYARAN_VISA_PELAJAR', 'Pembayaran VISA Pelajar'),
('50', '6', '8', 'TERBIT_VISA_PELAJAR', 'Terbit VISA Pelajar'),
('51', '6', '9', 'DOWNLOAD_PDF_VISA_DAN_MERAPIKAN_DI_FOLDER_DOKUMEN_', 'Download Pdf VISA dan Merapikan Di Folder Dokumen PLN'),
('56', '8', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN_DA', 'Pembuatan Surat Permohonan, Keterangan, Jaminan dan KTP Sponsor'),
('57', '8', '2', 'PERSIAPAN_BERKAS_E_VISA__ITAS__PASPOR_LAMA_DAN_PAS', 'Persiapan Berkas E-VISA, ITAS, Paspor Lama dan Paspor Baru'),
('58', '8', '3', 'PENGAJUAN_PERUBAHAN_PASPOR', 'Pengajuan Perubahan Paspor'),
('59', '8', '4', 'TERBIT_HASIL_PENGAJUAN', 'Terbit Hasil Pengajuan'),
('60', '9', '1', 'PEMBUATAN_SURAT_PERMOHONAN__KETERANGAN__JAMINAN_DA', 'Pembuatan Surat Permohonan, Keterangan, Jaminan dan KTP Sponsor'),
('61', '9', '2', 'PERSIAPAN_BERKAS_E_VISA__ITAS__PASPOR_LAMA_DAN_PAS', 'Persiapan Berkas E-VISA, ITAS, Paspor Lama dan Paspor Baru'),
('62', '9', '3', 'PENGAJUAN_PERUBAHAN_PASPOR', 'Pengajuan Perubahan Paspor'),
('63', '9', '4', 'TERBIT_HASIL_PENGAJUAN', 'Terbit Hasil Pengajuan');

-- Table structure for `jobdesk_payment_installment`
DROP TABLE IF EXISTS `jobdesk_payment_installment`;
CREATE TABLE `jobdesk_payment_installment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `tgl_bayar` date NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `jobdesk_pengeluaran_birokrasi`
DROP TABLE IF EXISTS `jobdesk_pengeluaran_birokrasi`;
CREATE TABLE `jobdesk_pengeluaran_birokrasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instansi_id` varchar(50) DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `kategori` varchar(100) DEFAULT 'Lainnya',
  `tanggal` date NOT NULL,
  `foto_nota` varchar(500) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `jobdesk_process_tarif`
DROP TABLE IF EXISTS `jobdesk_process_tarif`;
CREATE TABLE `jobdesk_process_tarif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `biaya_instansi` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya resmi ke pemerintah',
  `biaya_santri` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya yang ditagih ke santri',
  `keterangan` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_process_tarif` (`process_id`),
  CONSTRAINT `fk_jpt_process` FOREIGN KEY (`process_id`) REFERENCES `jobdesk_master_process` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `jobdesk_process_tarif`
INSERT INTO `jobdesk_process_tarif` (`id`, `process_id`, `biaya_instansi`, `biaya_santri`, `keterangan`, `updated_at`) VALUES 
('1', '1', '4500000.00', '5000000.00', 'Tarif default perpanjangan ITAS', '2026-06-11 09:18:12'),
('2', '2', '6000000.00', '7000000.00', 'Tarif default pengajuan visa baru', '2026-06-11 09:21:37'),
('3', '3', '0.00', '0.00', NULL, '2026-06-11 09:21:37'),
('4', '5', '4500000.00', '5000000.00', 'Tarif default perpanjangan ITAS', '2026-06-27 00:20:38'),
('5', '6', '6000000.00', '7000000.00', 'Tarif default pengajuan visa baru', '2026-06-27 00:20:38'),
('7', '8', '0.00', '0.00', NULL, '2026-06-27 10:58:35'),
('8', '9', '0.00', '0.00', NULL, '2026-06-27 14:47:30');

-- Table structure for `jobdesk_step_notes`
DROP TABLE IF EXISTS `jobdesk_step_notes`;
CREATE TABLE `jobdesk_step_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_id` int(11) NOT NULL,
  `tipe` enum('info','kekurangan','tindakan','selesai') DEFAULT 'info',
  `isi` text DEFAULT NULL,
  `berkas_kurang` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `step_id` (`step_id`),
  CONSTRAINT `jobdesk_step_notes_ibfk_1` FOREIGN KEY (`step_id`) REFERENCES `jobdesk_case_steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `list_angkatan`
DROP TABLE IF EXISTS `list_angkatan`;
CREATE TABLE `list_angkatan` (
  `id` tinyint(4) NOT NULL,
  `angka` varchar(20) DEFAULT NULL,
  `nama_indo` varchar(20) DEFAULT NULL,
  `nama_arab` varchar(20) DEFAULT NULL,
  `nama_arab_lengkap` varchar(30) DEFAULT NULL,
  `urutan` tinyint(4) DEFAULT NULL,
  `status_santri` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_santri` (`status_santri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for `list_angkatan`
INSERT INTO `list_angkatan` (`id`, `angka`, `nama_indo`, `nama_arab`, `nama_arab_lengkap`, `urutan`, `status_santri`) VALUES 
('1', '1', 'Kelas Satu', 'الأول', 'الفصل الأول', '1', '1'),
('2', '1 Int', 'Kelas Satu Intensif', 'الأول التكثيفي', 'الفصل الأول التكثيفي', '2', '1'),
('3', '2', 'Kelas Dua', 'الثاني', 'الفصل الثالث', '3', '1'),
('4', '3', 'Kelas Tiga', 'الثالث', 'الفصل الثالث', '4', '1'),
('5', '3 Int', 'Kelas Tiga Intensif', 'الثالث التكثيفي', 'الفصل الثالث التكثيفي', '5', '1'),
('6', '4', 'Kelas Empat', 'الرابع', 'الفصل الرابع', '6', '1'),
('7', '5', 'Kelas Lima', 'الخامس', 'الفصل الخامس', '7', '1'),
('8', '6', 'Kelas Enam', 'السادس', 'الفصل السادس', '8', '1'),
('9', 'B', 'Kelas B', NULL, NULL, '9', '2'),
('10', 'A', 'Kelas A', NULL, NULL, '10', '2');

-- Table structure for `log_paspor`
DROP TABLE IF EXISTS `log_paspor`;
CREATE TABLE `log_paspor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paspor_id` int(11) NOT NULL,
  `kds` int(11) NOT NULL,
  `tipe` enum('keluar','masuk') NOT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_aksi` date NOT NULL,
  `tanggal_rencana_kembali` date DEFAULT NULL,
  `dicatat_oleh` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `master_instansi`
DROP TABLE IF EXISTS `master_instansi`;
CREATE TABLE `master_instansi` (
  `kode` int(11) NOT NULL AUTO_INCREMENT,
  `nama_instansi` varchar(255) NOT NULL,
  `no_Instansi` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pondok` varchar(100) NOT NULL,
  `path_folder` varchar(255) NOT NULL,
  `kepengurusan` varchar(100) NOT NULL,
  `def_kepengurusan` varchar(255) DEFAULT '',
  `def_pondok` varchar(50) DEFAULT '',
  `def_jenis_kelamin` varchar(20) DEFAULT '',
  `kop_surat` varchar(255) DEFAULT '',
  `kode_instansi` varchar(50) DEFAULT NULL,
  `spreadsheet_id` varchar(255) DEFAULT NULL,
  `capel_spreadsheet_range` varchar(255) DEFAULT 'Form Responses 1',
  `capel_mapping` text DEFAULT NULL,
  `capel_spreadsheet_headers` text DEFAULT NULL,
  `capel_display_columns` text DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `master_instansi`
INSERT INTO `master_instansi` (`kode`, `nama_instansi`, `no_Instansi`, `email`, `pondok`, `path_folder`, `kepengurusan`, `def_kepengurusan`, `def_pondok`, `def_jenis_kelamin`, `kop_surat`, `kode_instansi`, `spreadsheet_id`, `capel_spreadsheet_range`, `capel_mapping`, `capel_spreadsheet_headers`, `capel_display_columns`) VALUES 
('1', 'Pembimbing Luar Negeri Pusat', '9789', '', 'G1', 'D:\\01. Project\\04. Website\\pln\\berkas', 'Ponorogo', 'Ponorogo', 'G1', 'Laki-laki', 'D:/01. Project/04. Website/pln/berkas/kop_surat_1782001506.png', '', '11hrUFG-sSfEuuKy0dJFiHXTEbPNpqiVXWTvRQahNdlU', 'Form Responses 1', '{\"col_email\":\"Email Address\",\"col_nama\":\"Nama Lengkap (Full Name)\",\"col_tgllahir\":\"Tanggal Lahir (Date Of Birth)\",\"col_kwn\":\"Kewarganegaraan (Nationality)\",\"col_tempatlahir\":\"Tempat Lahir (Place Of Birth)\",\"col_ayah\":\"Nama Lengkap Ayah (sesuai IC)\",\"col_ibu\":\"Nama Lengkap Ibu (sesuai IC)\",\"col_nohp\":\"Nomor HP Wali Santri (WA Aktif)\",\"col_alamat\":\"Alamat Rumah\",\"col_no_paspor\":\"Nomor ID Paspor (Identity Number)\",\"col_exp_paspor\":\"Tanggal Berakhir Paspor (Date of Expiry Min. 2 Year)\",\"col_tempat_paspor\":\"Tempat dikeluarkan Paspor (Issuing Office)\",\"col_tgl_keluaran_paspor\":\"Tanggal Dikeluarkan Paspor (Date of Issue)\",\"col_program\":\"Calon Pelajar\"}', '[\"Timestamp\",\"Email Address\",\"Nama Lengkap (Full Name)\",\"Select Status \",\"Tempat Lahir (Place Of Birth)\",\"Tanggal Lahir (Date Of Birth)\",\"Kewarganegaraan (Nationality)\",\"Tanggal Dikeluarkan Paspor (Date of Issue)\",\"Tanggal Berakhir Paspor (Date of Expiry Min. 2 Year)\",\"Tempat dikeluarkan Paspor (Issuing Office)\",\"Nomor ID Paspor (Identity Number)\",\"Nama Lengkap Ayah (sesuai IC)\",\"Nama Lengkap Ibu (sesuai IC)\",\"Nomor HP Wali Santri (WA Aktif)\",\"Alamat Rumah\",\"Scan ID Paspor (Date of Expiry min.18 months)\",\"Scan IC (Kartu Identitas) Santri\",\"Scan IC Ayah\",\"Scan IC Ibu\",\"Surat Beranak \\/ Surat Kelahiran\",\"Pas Foto 4x6 Terbaru \",\"Curriculum Vitae (Print and Scan)\",\"Scan Sertifikat Vaksin 1, 2 dan Booster\",\"Asuransi Kesehatan\",\"Scan Ijazah \\/ Rapor Terakhir\",\"Surat Pernyataan Kesanggupan Sehat\",\"Surat Pernyataan Kesanggupan Biaya\",\"Surat Keterangan Affidavit\",\"Surat Pernyataan Pelajar Asing (Print and Scan)\",\"Calon Pelajar\",\"Column 1\",\"Merged Doc ID - Surat Jaminan Sponsor Visa\",\"Merged Doc URL - Surat Jaminan Sponsor Visa\",\"Link to merged Doc - Surat Jaminan Sponsor Visa\",\"Document Merge Status - Surat Jaminan Sponsor Visa\",\"[FRD Response ID] DO NOT REMOVE\"]', '[\"Timestamp\",\"Email Address\",\"Nama Lengkap (Full Name)\",\"Select Status \",\"Tempat Lahir (Place Of Birth)\",\"Tanggal Lahir (Date Of Birth)\",\"Kewarganegaraan (Nationality)\",\"Tanggal Dikeluarkan Paspor (Date of Issue)\",\"Tanggal Berakhir Paspor (Date of Expiry Min. 2 Year)\",\"Tempat dikeluarkan Paspor (Issuing Office)\",\"Nomor ID Paspor (Identity Number)\",\"Nama Lengkap Ayah (sesuai IC)\",\"Nama Lengkap Ibu (sesuai IC)\",\"Nomor HP Wali Santri (WA Aktif)\",\"Alamat Rumah\",\"Scan ID Paspor (Date of Expiry min.18 months)\",\"Scan IC (Kartu Identitas) Santri\",\"Scan IC Ayah\",\"Scan IC Ibu\",\"Surat Beranak \\/ Surat Kelahiran\",\"Pas Foto 4x6 Terbaru \",\"Curriculum Vitae (Print and Scan)\",\"Scan Sertifikat Vaksin 1, 2 dan Booster\",\"Asuransi Kesehatan\",\"Scan Ijazah \\/ Rapor Terakhir\",\"Surat Pernyataan Kesanggupan Sehat\",\"Surat Pernyataan Kesanggupan Biaya\",\"Surat Keterangan Affidavit\",\"Surat Pernyataan Pelajar Asing (Print and Scan)\",\"Calon Pelajar\"]'),
('2', 'Pembimbing Luar Negeri Putri', '8908', '', 'GP', 'D:\\01. Project\\04. Website\\pln\\berkas', 'Mantingan', 'Mantingan', 'GP', 'Perempuan', 'D:\\01. Project\\04. Website\\pln\\webapp/public/uploads/instansi/kop_surat_1780508728.jpeg', '', NULL, 'Form Responses 1', NULL, NULL, NULL),
('3', 'Pembimbing Luar Negeri G3', '0', '', 'G3', 'D:\\10. Film\\PLN', 'Kediri', 'Kediri', 'G3', 'Laki-laki', 'D:/01. Project/04. Website/pln/berkas/kop_surat_1780660103.jpeg', '', '11hrUFG-sSfEuuKy0dJFiHXTEbPNpqiVXWTvRQahNdlU', 'Form Responses 1', '{\"col_email\":\"Email Address\",\"col_nama\":\"Nama Lengkap (Full Name)\",\"col_tgllahir\":\"Tanggal Lahir (Date Of Birth)\",\"col_kwn\":\"Kewarganegaraan (Nationality)\",\"col_tempatlahir\":\"Tempat Lahir (Place Of Birth)\",\"col_ayah\":\"Nama Lengkap Ayah (sesuai IC)\",\"col_ibu\":\"Nama Lengkap Ibu (sesuai IC)\",\"col_nohp\":\"Nomor HP Wali Santri (WA Aktif)\",\"col_alamat\":\"Alamat Rumah\",\"col_no_paspor\":\"Nomor ID Paspor (Identity Number)\",\"col_exp_paspor\":\"Scan ID Paspor (Date of Expiry min.18 months)\",\"col_tempat_paspor\":\"Tempat dikeluarkan Paspor (Issuing Office)\",\"col_tgl_keluaran_paspor\":\"Tanggal Dikeluarkan Paspor (Date of Issue)\",\"col_program\":\"Calon Pelajar\"}', '[\"Timestamp\",\"Email Address\",\"Nama Lengkap (Full Name)\",\"Select Status \",\"Tempat Lahir (Place Of Birth)\",\"Tanggal Lahir (Date Of Birth)\",\"Kewarganegaraan (Nationality)\",\"Tanggal Dikeluarkan Paspor (Date of Issue)\",\"Tanggal Berakhir Paspor (Date of Expiry Min. 2 Year)\",\"Tempat dikeluarkan Paspor (Issuing Office)\",\"Nomor ID Paspor (Identity Number)\",\"Nama Lengkap Ayah (sesuai IC)\",\"Nama Lengkap Ibu (sesuai IC)\",\"Nomor HP Wali Santri (WA Aktif)\",\"Alamat Rumah\",\"Scan ID Paspor (Date of Expiry min.18 months)\",\"Scan IC (Kartu Identitas) Santri\",\"Scan IC Ayah\",\"Scan IC Ibu\",\"Surat Beranak \\/ Surat Kelahiran\",\"Pas Foto 4x6 Terbaru \",\"Curriculum Vitae (Print and Scan)\",\"Scan Sertifikat Vaksin 1, 2 dan Booster\",\"Asuransi Kesehatan\",\"Scan Ijazah \\/ Rapor Terakhir\",\"Surat Pernyataan Kesanggupan Sehat\",\"Surat Pernyataan Kesanggupan Biaya\",\"Surat Keterangan Affidavit\",\"Surat Pernyataan Pelajar Asing (Print and Scan)\",\"Calon Pelajar\",\"Column 1\",\"Merged Doc ID - Surat Jaminan Sponsor Visa\",\"Merged Doc URL - Surat Jaminan Sponsor Visa\",\"Link to merged Doc - Surat Jaminan Sponsor Visa\",\"Document Merge Status - Surat Jaminan Sponsor Visa\",\"[FRD Response ID] DO NOT REMOVE\"]', NULL),
('4', 'Pembimbing Luar Negeri G2', '0', '', 'G2', 'D:\\01. Project\\04. Website\\pln\\berkas', 'Ponorogo', 'Ponorogo', 'G2', 'Laki-laki', '', '', NULL, 'Form Responses 1', NULL, NULL, NULL),
('5', 'Pembimbing Luar Negeri G4', '0', '', 'G4', '', 'Banyuwangi', 'Banyuwangi', 'G4', '', '', 'G4', NULL, 'Form Responses 1', NULL, NULL, NULL);

-- Table structure for `master_menus`
DROP TABLE IF EXISTS `master_menus`;
CREATE TABLE `master_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori` varchar(50) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `urut` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `master_menus`
INSERT INTO `master_menus` (`id`, `kategori`, `nama_menu`, `url`, `icon`, `permission_key`, `urut`, `is_active`) VALUES 
('1', 'main', 'Auto Rekap', '/auto-rekap', 'bi bi-file-earmark-bar-graph', 'menu_auto_rekap', '10', '1'),
('2', 'main', 'Job Desk', '/job-desk', 'bi bi-kanban', 'menu_job_desk', '20', '1'),
('3', 'main', 'Pemberkasan', '/pemberkasan', 'bi bi-folder-fill', 'menu_pemberkasan', '30', '1'),
('4', 'masterdata', 'Data Santri', '/master-data', 'bi bi-database', 'menu_master_data', '10', '1'),
('5', 'masterdata', 'Data Santri Inaktif', '/inaktif-data', 'bi bi-archive', 'menu_inaktif_data', '20', '1'),
('6', 'masterdata', 'Persetujuan (Request)', '/request-edit', 'bi bi-check2-square', 'action_edit_santri', '30', '1'),
('7', 'lainlain', 'Generator Surat', '/surat-generator', 'bi bi-envelope-paper', 'menu_surat_generator', '40', '1'),
('8', 'lainlain', 'Pengaturan Job Desk', '/job-desk/settings', 'bi bi-gear-wide-connected', 'menu_pengaturan_jobdesk', '10', '1'),
('9', 'lainlain', 'Profil Instansi', '/profil-instansi', 'bi bi-building', 'menu_profil_instansi', '20', '1'),
('10', 'lainlain', 'Anggota Kamar', '/anggota-kamar', 'bi bi-people-fill', 'menu_anggota_kamar', '30', '1'),
('11', 'sistem', 'Manajemen Instansi', '/manajemen-instansi', 'bi bi-buildings', 'menu_manajemen_instansi', '10', '1'),
('12', 'sistem', 'Manajemen Pengguna', '/manajemen-user', 'bi bi-person-lines-fill', 'menu_manajemen_pengguna', '20', '1'),
('13', 'sistem', 'Pengaturan Sistem', '/pengaturan', 'bi bi-sliders', 'menu_pengaturan_sistem', '30', '1'),
('14', 'sistem', 'Log Aktivitas', '/audit-log', 'bi bi-activity', 'menu_audit_log', '40', '1'),
('15', 'masterprint', 'Menu Print', '/master-print/menu-print', 'bi bi-printer', 'menu_master_print', '10', '1'),
('16', 'keuangan', 'Anggaran Operasional', '/anggaran', 'bi bi-wallet2', 'menu_anggaran', '40', '1'),
('21', 'keuangan', 'Operasional Birokrasi', '/job-desk/keuangan', 'bi bi-graph-up-arrow', 'menu_op_birokrasi', '20', '1'),
('23', 'masterdata', 'Pendaftaran CAPEL', '/pendaftaran-capel', 'bi bi-person-lines-fill', 'menu_capel', '15', '1'),
('24', 'main', 'Cetak Berkas Individu', '/pemberkasan/cetak-berkas', 'bi bi-printer', 'menu_cetak_berkas', '31', '1'),
('25', 'main', 'Manajemen Paspor', '/manajemen-paspor', 'bi bi-passport', 'menu_manajemen_paspor', '35', '1'),
('26', 'keuangan', 'Pengeluaran Operasional', 'job-desk/pengeluaran-operasional', 'bi bi-receipt-cutoff', 'jobdesk_pengeluaran', '2', '1');

-- Table structure for `master_santri`
DROP TABLE IF EXISTS `master_santri`;
CREATE TABLE `master_santri` (
  `kds` int(11) NOT NULL AUTO_INCREMENT,
  `stambuk` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kelas` varchar(100) NOT NULL,
  `status_santri` enum('Aktif','Inaktif','Lulus','Capel Syawwal','Capel Penampungan','Alumni') DEFAULT 'Aktif',
  `rayon` varchar(100) NOT NULL,
  `negara` varchar(100) NOT NULL,
  `kewarganegaraan` varchar(100) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `nama_ibu` varchar(255) NOT NULL,
  `nama_ayah` varchar(255) NOT NULL,
  `no_ibu` int(11) NOT NULL,
  `no_ayah` int(11) NOT NULL,
  `no_hp_alternatif` int(11) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `pondok` varchar(100) NOT NULL,
  `jenis_kelamin` varchar(50) NOT NULL,
  `path_foto` varchar(255) NOT NULL,
  `aktif` int(11) NOT NULL,
  `kode` varchar(100) NOT NULL,
  `kepengurusan` varchar(100) NOT NULL,
  `no_sktt` varchar(100) DEFAULT NULL,
  `no_ic` varchar(100) NOT NULL,
  `keberadaan_paspor` varchar(255) DEFAULT NULL,
  `ukuran_baju` varchar(100) NOT NULL,
  PRIMARY KEY (`kds`),
  KEY `idx_aktif` (`aktif`),
  KEY `idx_kelas` (`kelas`(50)),
  KEY `idx_negara` (`negara`(50)),
  KEY `idx_pondok` (`pondok`),
  KEY `idx_kepengurusan` (`kepengurusan`)
) ENGINE=InnoDB AUTO_INCREMENT=3000006 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `mtb_barang_terlarang`
DROP TABLE IF EXISTS `mtb_barang_terlarang`;
CREATE TABLE `mtb_barang_terlarang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kds` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `jenis_barang` varchar(255) NOT NULL,
  `jumlah_barang` int(11) NOT NULL,
  `satuan` varchar(100) NOT NULL,
  `detail` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `mtb_berkas_penting`
DROP TABLE IF EXISTS `mtb_berkas_penting`;
CREATE TABLE `mtb_berkas_penting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(100) NOT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `nama_berkas` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=737 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `mtb_capel_draft`
DROP TABLE IF EXISTS `mtb_capel_draft`;
CREATE TABLE `mtb_capel_draft` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data_json`)),
  `file_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`file_links`)),
  `status_approval` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `kds_approved` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `instansi_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `mtb_itas`
DROP TABLE IF EXISTS `mtb_itas`;
CREATE TABLE `mtb_itas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kds` int(11) NOT NULL,
  `no_itas` varchar(100) NOT NULL,
  `exp_itas` date DEFAULT NULL,
  `level_itas` int(11) NOT NULL,
  `aktif` int(11) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kds` (`kds`),
  KEY `idx_aktif` (`aktif`)
) ENGINE=InnoDB AUTO_INCREMENT=798 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `mtb_kelas`
DROP TABLE IF EXISTS `mtb_kelas`;
CREATE TABLE `mtb_kelas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode_kampus` varchar(4) NOT NULL,
  `angkatan_id` tinyint(4) DEFAULT NULL,
  `abjad` varchar(2) DEFAULT NULL,
  `nama` varchar(20) DEFAULT NULL,
  `ruang_kelas_id` int(10) unsigned DEFAULT NULL,
  `walikelas_1` int(10) unsigned DEFAULT NULL,
  `walikelas_2` int(10) unsigned DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ruang_kelas` (`ruang_kelas_id`) USING BTREE,
  KEY `angkatan_id` (`angkatan_id`),
  KEY `walikelas_1` (`walikelas_1`),
  KEY `walikelas_2` (`walikelas_2`),
  KEY `idx_kelas_kampus` (`kode_kampus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `mtb_paspor`
DROP TABLE IF EXISTS `mtb_paspor`;
CREATE TABLE `mtb_paspor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kds` int(11) NOT NULL,
  `no_paspor` varchar(255) NOT NULL,
  `tanggal_dikeluarkan` date DEFAULT NULL,
  `tempat_dikeluarkan` varchar(100) NOT NULL,
  `exp_paspor` date DEFAULT NULL,
  `aktif` int(11) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `status_lokasi` enum('di_kantor','keluar') DEFAULT 'di_kantor',
  PRIMARY KEY (`id`),
  KEY `idx_kds` (`kds`),
  KEY `idx_aktif` (`aktif`)
) ENGINE=InnoDB AUTO_INCREMENT=1050 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `santri_edit_requests`
DROP TABLE IF EXISTS `santri_edit_requests`;
CREATE TABLE `santri_edit_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kds` varchar(50) NOT NULL,
  `requested_by_instansi_id` int(11) NOT NULL,
  `target_kepengurusan` varchar(255) NOT NULL,
  `requested_changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`requested_changes`)),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `old_values` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target_kepengurusan` (`target_kepengurusan`,`status`),
  KEY `idx_kds` (`kds`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `surat_generated`
DROP TABLE IF EXISTS `surat_generated`;
CREATE TABLE `surat_generated` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailing_id` int(11) NOT NULL,
  `tipe_surat` enum('SP','SK','SJ','ST') NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `hal` varchar(255) NOT NULL DEFAULT '',
  `isi` text DEFAULT NULL,
  `kepada` varchar(255) NOT NULL DEFAULT '',
  `tempat` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mailing` (`mailing_id`),
  KEY `idx_tipe` (`tipe_surat`),
  CONSTRAINT `fk_sg_mailing` FOREIGN KEY (`mailing_id`) REFERENCES `surat_mailing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `surat_jenis_pengajuan`
DROP TABLE IF EXISTS `surat_jenis_pengajuan`;
CREATE TABLE `surat_jenis_pengajuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis_pengajuan` varchar(255) NOT NULL,
  `hal` varchar(255) NOT NULL DEFAULT '',
  `isi` text DEFAULT NULL,
  `kepada` varchar(255) NOT NULL DEFAULT '',
  `tempat` varchar(255) NOT NULL DEFAULT '',
  `surat_dibutuhkan` varchar(100) NOT NULL DEFAULT 'SP,SK,SJ,ST' COMMENT 'Comma-separated: SP,SK,SJ,ST',
  `kantor` varchar(255) NOT NULL DEFAULT '',
  `output_path` varchar(255) DEFAULT NULL,
  `template_path` varchar(255) DEFAULT NULL,
  `instansi_id` int(11) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_instansi` (`instansi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `surat_jenis_pengajuan`
INSERT INTO `surat_jenis_pengajuan` (`id`, `jenis_pengajuan`, `hal`, `isi`, `kepada`, `tempat`, `surat_dibutuhkan`, `kantor`, `output_path`, `template_path`, `instansi_id`, `aktif`, `created_at`) VALUES 
('1', 'Rekom PO Itas', 'PERMOHONAN REKOMENDASI PERPANJANGAN ITAS 1 TAHUN', 'Permohonan Rekomendasi Perpanjangan ITAS 1 Tahun', 'Biro Kopontren Kementerian Agama', 'Ponorogo', 'SP,SJ,SK,ST', 'Kemenag', 'D:\\10. Film\\PLN', NULL, NULL, '1', '2026-06-04 14:55:36'),
('2', 'Rekom PO Visa', 'PERMOHONAN REKOMENDASI PEMBUATAN VISA BELAJAR', 'Surat Rekomendasi Pembuatan VISA Belajar', 'Biro Kopontren Kementerian Agama', 'Ponorogo', 'SJ,SK', 'Kemenag', NULL, NULL, NULL, '1', '2026-06-04 14:55:36'),
('3', 'Rekom Sindi Visa', 'PERMOHONAN REKOMENDASI PEMBUATAN VISA BELAJAR', 'Surat Rekomendasi Pembuatan Visa Belajar', 'Biro Hukum dan Kerjasama Luar Negeri Sekretariat Jenderal Kementerian Agama', 'Jakarta', 'SP,SJ', 'Kemenag', NULL, NULL, NULL, '1', '2026-06-04 14:55:36'),
('4', 'Rekom Sindi ITAS', 'PERMOHONAN REKOMENDASI PERPANJANGAN ITAS', 'Surat Rekomendasi Perpanjangan ITAS', 'Biro Hukum dan Kerjasama Luar Negeri Kementerian Agama', 'Jakarta', 'SP,SJ', 'Kemenag', NULL, NULL, NULL, '1', '2026-06-04 14:55:36'),
('5', 'Perpanjangan ITAS Imigrasi', 'PERMOHONAN PERPANJANGAN IZIN TINGGAL TERBATAS 1 TAHUN', 'Perpanjangan ITAS 1 Tahun', 'Kepala Kantor Imigrasi Kelas II Non TPI', 'Ponorogo', 'SP,SJ,SK,ST', 'Imigrasi', NULL, NULL, NULL, '1', '2026-06-04 14:55:36'),
('6', 'Pembuatan VISA Belajar', 'PERMOHONAN REKOMENDASI PEMBUATAN VISA BELAJAR', 'Surat Rekomendasi Pembuatan Visa Belajar', 'Direktur Lalu Lintas Keimigrasian Up. Kasubdit Visa', 'Jakarta', 'SP,SJ,SK,ST', 'Imigrasi', 'D:\\01. Project\\04. Website\\pln\\berkas\\Export Data', NULL, NULL, '1', '2026-06-04 14:55:36'),
('7', 'Pengajuan EPO', 'PERMOHONAN EXIT PERMIT ONLY (EPO)', 'Exit Permit Only (EPO)', 'Kepala Kantor Imigrasi Kelas II Non TPI Ponorogo', 'Ponorogo', 'SP,SJ,SK,ST', 'Imigrasi', NULL, NULL, NULL, '1', '2026-06-04 14:55:36'),
('8', 'Perubahan Nomor Paspor', 'PERMOHONAN PERUBAHAN NOMOR PASPOR', 'Perubahan Nomor Paspor Baru', 'Kepala Kantor Imigrasi Kelas II Non TPI', 'Ponorogo', '', 'Imigrasi', NULL, NULL, NULL, '1', '2026-06-04 14:55:36');

-- Table structure for `surat_mailing`
DROP TABLE IF EXISTS `surat_mailing`;
CREATE TABLE `surat_mailing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_mailing` varchar(50) NOT NULL,
  `jenis_pengajuan_id` int(11) NOT NULL,
  `mode` enum('sekaligus','perseorangan') NOT NULL DEFAULT 'perseorangan',
  `tanggal_surat` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `sort_by` varchar(50) DEFAULT 'nama ASC',
  `instansi_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_jenis` (`jenis_pengajuan_id`),
  KEY `idx_instansi` (`instansi_id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `surat_mailing_santri`
DROP TABLE IF EXISTS `surat_mailing_santri`;
CREATE TABLE `surat_mailing_santri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailing_id` int(11) NOT NULL,
  `kds` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mailing` (`mailing_id`),
  KEY `idx_kds` (`kds`),
  CONSTRAINT `fk_sms_mailing` FOREIGN KEY (`mailing_id`) REFERENCES `surat_mailing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firebase_uid` varchar(128) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `ttl` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `role` enum('super_admin','admin_instansi','staff_instansi') NOT NULL,
  `permissions` text DEFAULT NULL,
  `instansi_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_firebase_uid` (`firebase_uid`),
  KEY `instansi_id` (`instansi_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`instansi_id`) REFERENCES `master_instansi` (`kode`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
