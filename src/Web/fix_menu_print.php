<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if 'Menu Print' already exists to avoid duplicates
    $exists = $db->query("SELECT id FROM master_menus WHERE url = '/master-print/menu-print'")->fetchColumn();
    if (!$exists) {
        // Insert the missing Menu Print
        $stmt = $db->prepare("INSERT INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut) VALUES ('masterprint', 'Menu Print', '/master-print/menu-print', 'bi bi-printer', 'menu_master_print', 10)");
        $stmt->execute();
    }
    
    // Move Surat Generator back to lainlain
    $stmt2 = $db->prepare("UPDATE master_menus SET kategori = 'lainlain', urut = 40 WHERE url = '/surat-generator'");
    $stmt2->execute();
    
    echo "Menu Print fixed.\n";
    
    // We should also run the fix_admin_perms again because we just introduced 'menu_master_print'
    $stmt3 = $db->query("SELECT permission_key FROM master_menus");
    $allPerms = $stmt3->fetchAll(PDO::FETCH_COLUMN);
    $allEditPerms = array_map(fn($p) => $p . '_edit', $allPerms);
    $fullPerms = array_merge($allPerms, $allEditPerms);
    $fullPermsJson = json_encode($fullPerms);
    
    $stmt4 = $db->prepare("UPDATE users SET permissions = :perms WHERE role IN ('super_admin', 'admin_instansi')");
    $stmt4->execute([':perms' => $fullPermsJson]);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
