<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT stambuk, nama, aktif FROM master_santri WHERE nama LIKE '%Solahuddin%' OR nama LIKE '%Mahdi Dasuki%' OR nama LIKE '%Master Hasbiyallah%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);

    // Also check for duplicate stambuks in the table
    $stmt2 = $db->query("SELECT stambuk, COUNT(*) as count FROM master_santri GROUP BY stambuk HAVING count > 1");
    $duplicates = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "\nDuplicate stambuks in DB:\n";
    print_r($duplicates);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
