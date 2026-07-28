<?php
$pdo = new PDO('sqlite:d:\01. Project\04. Website\pln\webapp\data\database.sqlite');
$stmt = $pdo->query("SELECT data_json FROM calon_pelajar WHERE nama_lengkap LIKE '%Hasbiyallah%'");
$row = $stmt->fetchColumn();
if ($row) {
    print_r(json_decode($row, true));
} else {
    echo "Not found";
}
