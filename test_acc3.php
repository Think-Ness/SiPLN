<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = 3;
$status = 'disetujui';
$items = [
    ['id' => 4, 'nominal_disetujui' => 6000],
    ['id' => 5, 'nominal_disetujui' => 2342],
    ['id' => 6, 'nominal_disetujui' => 2342423],
];

$pdo->beginTransaction();
try {
    $totalDisetujui = 0;
    
    foreach ($items as $item) {
        $nominal = (float) ($item['nominal_disetujui'] ?? 0);
        $totalDisetujui += $nominal;
        $stmt = $pdo->prepare("UPDATE anggaran_pengajuan_items SET nominal_disetujui = ? WHERE id = ? AND pengajuan_id = ?");
        $stmt->execute([$nominal, $item['id'], $id]);
    }
    
    $stmt = $pdo->prepare("UPDATE anggaran_pengajuan SET status = ?, total_disetujui = ? WHERE id = ?");
    $stmt->execute([$status, $totalDisetujui, $id]);
    echo "Rows affected: " . $stmt->rowCount() . "\n";
    $pdo->commit();
    echo "Success\n";
} catch (\Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
