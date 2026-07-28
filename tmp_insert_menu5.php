<?php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT * FROM master_menus WHERE url = '/job-desk/keuangan'");
if (!$stmt->fetch()) {
    $pdo->exec("INSERT INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut, is_active) VALUES ('keuangan', 'Operasional Birokrasi', '/job-desk/keuangan', 'bi bi-graph-up-arrow', 'menu_op_birokrasi', 20, 1)");
    echo "Inserted Operasional Birokrasi!";
} else {
    $pdo->exec("UPDATE master_menus SET kategori = 'keuangan', is_active = 1 WHERE url = '/job-desk/keuangan'");
    echo "Updated Operasional Birokrasi!";
}

// Update admin permission to ensure it can see it if not super_admin
$stmt = $pdo->query("SELECT id, permissions FROM master_users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    if ($u['permissions']) {
        $perms = json_decode($u['permissions'], true) ?: [];
        if (!in_array('menu_op_birokrasi', $perms)) {
            $perms[] = 'menu_op_birokrasi';
            $newPerms = json_encode($perms);
            $pdo->exec("UPDATE master_users SET permissions = '$newPerms' WHERE id = " . $u['id']);
        }
    }
}
echo " Permissions updated!";
