<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("ALTER TABLE mtb_paspor MODIFY tanggal_dikeluarkan DATE NULL DEFAULT NULL");
    echo "tanggal_dikeluarkan altered to NULL.\n";

    $db->exec("ALTER TABLE mtb_paspor MODIFY exp_paspor DATE NULL DEFAULT NULL");
    echo "exp_paspor altered to NULL.\n";

    $db->exec("ALTER TABLE mtb_itas MODIFY exp_itas DATE NULL DEFAULT NULL");
    echo "exp_itas altered to NULL.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
