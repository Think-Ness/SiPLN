<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$stmt = $pdo->query("SELECT id, data_json FROM mtb_capel_draft");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    $dataJson = json_decode($row['data_json'], true);
    if (!is_array($dataJson)) continue;

    $updated = false;
    foreach ($dataJson as $k => $v) {
        if (is_string($v) && !str_contains($v, 'drive.google.com') && !str_contains(strtolower($k), 'email')) {
            $newVal = ucwords(strtolower(trim($v)));
            if ($newVal !== $v) {
                $dataJson[$k] = $newVal;
                $updated = true;
            }
        }
    }

    if ($updated) {
        $update = $pdo->prepare("UPDATE mtb_capel_draft SET data_json = ? WHERE id = ?");
        $update->execute([json_encode($dataJson), $row['id']]);
        echo "Fixed caps for ID {$row['id']}\n";
    }
}
echo "Done fixing caps.\n";
