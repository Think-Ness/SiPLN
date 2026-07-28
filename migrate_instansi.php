<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $queries = [
        "ALTER TABLE master_instansi ADD COLUMN def_kepengurusan VARCHAR(255) DEFAULT ''",
        "ALTER TABLE master_instansi ADD COLUMN def_pondok VARCHAR(50) DEFAULT ''",
        "ALTER TABLE master_instansi ADD COLUMN def_jenis_kelamin VARCHAR(20) DEFAULT ''",
        "ALTER TABLE master_instansi ADD COLUMN kop_surat VARCHAR(255) DEFAULT ''",
    ];

    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
            echo "SUCCESS: $query\n";
        } catch (Exception $e) {
            echo "SKIPPED/ERROR on $query: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Connection Error: " . $e->getMessage();
}
