<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$santris = $pdo->query('SELECT kds, kode, nama FROM master_santri WHERE nama LIKE "%Kor Vy%"')->fetchAll(PDO::FETCH_ASSOC);
print_r($santris);
if (!empty($santris)) {
    $kode = $santris[0]['kode'];
    $stmt = $pdo->prepare('SELECT id, kode, nama_berkas FROM mtb_berkas_penting WHERE kode = ?');
    $stmt->execute([$kode]);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
