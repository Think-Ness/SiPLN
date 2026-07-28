CREATE TABLE IF NOT EXISTS master_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(50) NOT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(100) NOT NULL,
    permission_key VARCHAR(100) NOT NULL UNIQUE,
    urut INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data (Sesuai dengan layout.php eksisting)
INSERT INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut) VALUES
-- Main Menu
('main', 'Auto Rekap', '/auto-rekap', 'bi bi-file-earmark-bar-graph', 'menu_auto_rekap', 10),
('main', 'Job Desk', '/job-desk', 'bi bi-kanban', 'menu_job_desk', 20),
('main', 'Pemberkasan', '/pemberkasan', 'bi bi-folder-fill', 'menu_pemberkasan', 30),

-- Master Data
('masterdata', 'Data Santri', '/master-data', 'bi bi-database', 'menu_master_data', 10),
('masterdata', 'Data Santri Inaktif', '/inaktif-data', 'bi bi-archive', 'menu_inaktif_data', 20),
('masterdata', 'Persetujuan (Request)', '/request-edit', 'bi bi-check2-square', 'action_edit_santri', 30),

-- Master Print
('masterprint', 'Generator Surat', '/surat-generator', 'bi bi-envelope-paper', 'menu_surat_generator', 10),

-- Lain-lain
('lainlain', 'Pengaturan Job Desk', '/job-desk/settings', 'bi bi-gear-wide-connected', 'menu_pengaturan_jobdesk', 10),
('lainlain', 'Profil Instansi', '/profil-instansi', 'bi bi-building', 'menu_profil_instansi', 20),
('lainlain', 'Anggota Kamar', '/anggota-kamar', 'bi bi-people-fill', 'menu_anggota_kamar', 30),

-- Manajemen Sistem
('sistem', 'Manajemen Instansi', '/manajemen-instansi', 'bi bi-buildings', 'menu_manajemen_instansi', 10),
('sistem', 'Manajemen Pengguna', '/manajemen-user', 'bi bi-person-lines-fill', 'menu_manajemen_pengguna', 20),
('sistem', 'Pengaturan Sistem', '/pengaturan', 'bi bi-sliders', 'menu_pengaturan_sistem', 30),
('sistem', 'Log Aktivitas', '/audit-log', 'bi bi-activity', 'menu_audit_log', 40);
