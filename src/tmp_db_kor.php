<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT data_json FROM mtb_capel_draft WHERE nama_lengkap LIKE '%Kor Vy Solahuddin%' LIMIT 1");
$row = $stmt->fetchColumn();
if ($row) {
    print_r(json_decode($row, true));
} else {
    echo "Not found";
}
