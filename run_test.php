<?php
require __DIR__ . '/vendor/autoload.php';
// Boot up yii db connection and try to run the code
// ... this is too complex.

// Just write a standalone db update test
$host = '127.0.0.1';
$db   = 'si_foreign_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$caseId = 69;

try {
    // SELECT
    $stmt = $pdo->prepare("SELECT id FROM jobdesk_case_payment WHERE case_id = :cid");
    $stmt->execute(['cid' => $caseId]);
    $existing = $stmt->fetch();
    
    $updateFields = [
        'status_bayar_santri' => 'lunas',
        'tgl_bayar_santri' => date('Y-m-d')
    ];
    
    if ($existing) {
        // UPDATE
        $set = [];
        foreach ($updateFields as $k => $v) $set[] = "$k = :$k";
        $sql = "UPDATE jobdesk_case_payment SET " . implode(', ', $set) . " WHERE case_id = :case_id";
        $updateFields['case_id'] = $caseId;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateFields);
        echo "Update OK\n";
    } else {
        // INSERT
        $updateFields['case_id'] = $caseId;
        $updateFields['created_by'] = 'Staf';
        $cols = implode(', ', array_keys($updateFields));
        $vals = ':' . implode(', :', array_keys($updateFields));
        $sql = "INSERT INTO jobdesk_case_payment ($cols) VALUES ($vals)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateFields);
        echo "Insert OK\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
