<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=si_foreign_db", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$processIds = [1, 2];
$placeholders = implode(',', array_fill(0, count($processIds), '?'));
try {
    $stmt = $db->prepare("SELECT * FROM jobdesk_master_process WHERE instansi_id IS NULL AND id IN ($placeholders)");
    $stmt->execute($processIds);
    print_r($stmt->fetchAll());
} catch (Exception $e) {
    echo $e->getMessage();
}
