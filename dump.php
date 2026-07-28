<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/pln.db');
$stmt = $db->query("SELECT kode, nama_instansi, def_kepengurusan, def_pondok FROM master_instansi");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$out = "";
foreach ($rows as $row) {
    $out .= "{$row['kode']} | {$row['nama_instansi']} | kep: {$row['def_kepengurusan']} | pon: {$row['def_pondok']}\n";
}
file_put_contents('dump.txt', $out);
