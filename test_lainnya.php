<?php
require "vendor/autoload.php";
$pdo = new PDO("mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8mb4", "root", "");
$sql = "SELECT nama, kelas, pondok, kepengurusan FROM master_santri WHERE aktif=1 AND kelas NOT LIKE '%penerimaan%' AND kelas NOT LIKE '%persiapan%' AND kelas NOT LIKE '%alumni%' AND kelas NOT LIKE '%pengabdian%' AND kelas NOT LIKE '%1%' AND kelas NOT LIKE '%2%' AND kelas NOT LIKE '%3%' AND kelas NOT LIKE '%4%' AND kelas NOT LIKE '%%5%' AND kelas NOT LIKE '%6%' OR kelas IS NULL OR TRIM(kelas) = ''";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo "- {".$r["nama"].} (Kelas: '".$r["kelas"]."') [".$r["kepengurusan"]."]\n";
}
echo "\nTotal: " . count($rows);
