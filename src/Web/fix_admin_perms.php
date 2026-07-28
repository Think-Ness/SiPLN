<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all permission keys from master_menus
    $stmt = $db->query("SELECT permission_key FROM master_menus");
    $allPerms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add _edit for each
    $allEditPerms = array_map(fn($p) => $p . '_edit', $allPerms);
    $fullPerms = array_merge($allPerms, $allEditPerms);
    $fullPermsJson = json_encode($fullPerms);
    
    // Update all super_admin and admin_instansi
    $stmtUpdate = $db->prepare("UPDATE users SET permissions = :perms WHERE role IN ('super_admin', 'admin_instansi')");
    $stmtUpdate->execute([':perms' => $fullPermsJson]);
    
    echo "Updated " . $stmtUpdate->rowCount() . " admin users with full permissions.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
