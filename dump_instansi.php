<?php
$db = new PDO('sqlite:database/pln.db');
$stmt = $db->query("SELECT kode, nama_instansi, def_kepengurusan, def_pondok FROM master_instansi");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "{$row['kode']} | {$row['nama_instansi']} | kep: {$row['def_kepengurusan']} | pon: {$row['def_pondok']}\n";
}
