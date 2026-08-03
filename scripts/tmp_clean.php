<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 

// 1. Find all kds from today's capel drafts that were approved
$stmt = $pdo->query("SELECT id, kds_approved FROM mtb_capel_draft WHERE status_approval = 'Approved' AND kds_approved IS NOT NULL");
$drafts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($drafts as $draft) {
    $kds = $draft['kds_approved'];
    
    // Find the kode from master_santri
    $kodeStmt = $pdo->prepare("SELECT kode FROM master_santri WHERE kds = ?");
    $kodeStmt->execute([$kds]);
    $kode = $kodeStmt->fetchColumn();
    
    if ($kode) {
        // Delete from mtb_berkas_penting
        $pdo->prepare("DELETE FROM mtb_berkas_penting WHERE kode = ?")->execute([$kode]);
    }
    
    // Delete from mtb_paspor
    $pdo->prepare("DELETE FROM mtb_paspor WHERE kds = ?")->execute([$kds]);
    
    // Delete from master_santri
    $pdo->prepare("DELETE FROM master_santri WHERE kds = ?")->execute([$kds]);
}

// 2. Reset drafts back to Pending
$pdo->query("UPDATE mtb_capel_draft SET status_approval = 'Pending', kds_approved = NULL WHERE status_approval = 'Approved'");

// 3. Clear the queue
$pdo->query("TRUNCATE capel_download_queue");

echo "Cleaned up bad data!";
