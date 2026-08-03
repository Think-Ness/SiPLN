<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$baseUploadDir = dirname(__DIR__) . '/public';
$targetBaseDir = 'D:\\01. Project\\04. Website\\pln\\berkas';

if (!is_dir($targetBaseDir)) @mkdir($targetBaseDir, 0777, true);

function cleanString($str) {
    return preg_replace('/[^A-Za-z0-9]/', '_', $str);
}

// 1. master_santri (foto santri)
$stmt = $pdo->query("SELECT kode, nama, path_foto FROM master_santri WHERE path_foto LIKE '/uploads/%'");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $oldPath = $baseUploadDir . $row['path_foto'];
    if (file_exists($oldPath)) {
        $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        $newName = cleanString('Foto Santri') . '_' . cleanString($row['nama']) . '_' . $row['kode'] . '_' . time() . '.' . $ext;
        $newFolder = $targetBaseDir . '\\foto santri\\';
        if (!is_dir($newFolder)) @mkdir($newFolder, 0777, true);
        $newPath = $newFolder . $newName;
        
        rename($oldPath, $newPath);
        $dbPath = '/serve.php?path=' . urlencode(str_replace('\\', '/', $newPath));
        
        $pdo->prepare("UPDATE master_santri SET path_foto = ? WHERE kode = ?")->execute([$dbPath, $row['kode']]);
        echo "Moved foto: " . $newName . "\n";
    }
}

// 2. mtb_paspor (paspor)
$stmt = $pdo->query("SELECT p.kds, s.kode, s.nama, p.path_file FROM mtb_paspor p JOIN master_santri s ON p.kds = s.kds WHERE p.path_file LIKE '/uploads/%'");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $oldPath = $baseUploadDir . $row['path_file'];
    if (file_exists($oldPath)) {
        $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        $newName = cleanString('Paspor') . '_' . cleanString($row['nama']) . '_' . $row['kode'] . '_' . time() . '.' . $ext;
        $newFolder = $targetBaseDir . '\\paspor\\';
        if (!is_dir($newFolder)) @mkdir($newFolder, 0777, true);
        $newPath = $newFolder . $newName;
        
        rename($oldPath, $newPath);
        $dbPath = '/serve.php?path=' . urlencode(str_replace('\\', '/', $newPath));
        
        $pdo->prepare("UPDATE mtb_paspor SET path_file = ? WHERE kds = ?")->execute([$dbPath, $row['kds']]);
        echo "Moved paspor: " . $newName . "\n";
    }
}

// 3. mtb_berkas_penting (berkas)
$stmt = $pdo->query("SELECT b.id, s.kode, s.nama, b.nama_berkas, b.path_file FROM mtb_berkas_penting b JOIN master_santri s ON b.kode = s.kode WHERE b.path_file LIKE '/uploads/%'");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $oldPath = $baseUploadDir . $row['path_file'];
    if (file_exists($oldPath)) {
        $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        $newName = cleanString($row['nama_berkas']) . '_' . cleanString($row['nama']) . '_' . $row['kode'] . '_' . time() . '.' . $ext;
        $newFolder = $targetBaseDir . '\\berkas penting\\';
        if (!is_dir($newFolder)) @mkdir($newFolder, 0777, true);
        $newPath = $newFolder . $newName;
        
        rename($oldPath, $newPath);
        $dbPath = '/serve.php?path=' . urlencode(str_replace('\\', '/', $newPath));
        
        $pdo->prepare("UPDATE mtb_berkas_penting SET path_file = ? WHERE id = ?")->execute([$dbPath, $row['id']]);
        echo "Moved berkas: " . $newName . "\n";
    }
}

echo "Done migrating files!";
