<?php
$pdo = new PDO('mysql:host=localhost;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT * FROM anggaran_pengajuan ORDER BY id DESC LIMIT 1");
$p = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($p);

if ($p) {
    $stmt = $pdo->query("SELECT * FROM anggaran_pengajuan_items WHERE pengajuan_id = " . $p['id']);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
