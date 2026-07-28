<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT id, data_json FROM mtb_capel_draft WHERE tanggal_lahir IS NULL");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    $dataJson = json_decode($row['data_json'], true);
    if (!is_array($dataJson)) continue;

    $tglLahir = null;
    foreach ($dataJson as $k => $v) {
        if (str_contains(strtolower($k), 'tanggal lahir') || str_contains(strtolower($k), 'tgl lahir')) {
            $dateStr = trim((string)$v);
            if ($dateStr) {
                $dt = \DateTime::createFromFormat('d/m/Y', $dateStr);
                if ($dt !== false) {
                    $tglLahir = $dt->format('Y-m-d');
                } else {
                    $ts = strtotime($dateStr);
                    if ($ts) $tglLahir = date('Y-m-d', $ts);
                }
            }
        }
    }

    if ($tglLahir) {
        $update = $pdo->prepare("UPDATE mtb_capel_draft SET tanggal_lahir = ? WHERE id = ?");
        $update->execute([$tglLahir, $row['id']]);
        echo "Fixed ID {$row['id']} to {$tglLahir}\n";
    }
}
echo "Done fixing dates.\n";
