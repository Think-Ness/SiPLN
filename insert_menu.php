<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT COUNT(*) FROM master_menus WHERE url = '/pendaftaran-capel'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO master_menus (kategori, nama_menu, url, icon, permission_key, is_active, urut) VALUES ('masterdata', 'Pendaftaran CAPEL', '/pendaftaran-capel', 'bi bi-person-lines-fill', 'menu_capel', 1, 15)");
        echo "Menu added successfully.\n";
        
        // Let's grant 'menu_capel' permission to role 1 (Super Admin) and role 2 (Admin) if needed, but Super Admin bypasses it.
        // We will insert into sys_role_permissions if that table exists
        try {
            $db->exec("INSERT IGNORE INTO sys_role_permissions (role_id, permission_key) VALUES (1, 'menu_capel')");
        } catch (Exception $e) {}
    } else {
        echo "Menu already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
