<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("ALTER TABLE master_santri MODIFY tanggal_lahir DATE NULL DEFAULT NULL");
    echo "tanggal_lahir altered to NULL.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
