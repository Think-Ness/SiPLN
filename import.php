<?php
require __DIR__ . '/vendor/autoload.php';

use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\DatabaseConnection; // We will use PDO directly if needed, or instantiate DB.

// Let's just use raw PDO for a one-off import script to be fast and simple.
$host = '127.0.0.1';
$db   = 'si_foreign_db';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Truncate tables
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE master_santri');
$pdo->exec('TRUNCATE TABLE mtb_paspor');
$pdo->exec('TRUNCATE TABLE mtb_itas');
$pdo->exec('TRUNCATE TABLE mtb_barang_terlarang');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$json = file_get_contents(__DIR__ . '/../data_import.json');
$data = json_decode($json, true);

$countSantri = 0;
foreach ($data as $row) {
    // Basic sanitization
    $stambuk = $row['Regno'] ?? 0;
    if (!$stambuk) continue; // Skip if no stambuk

    $nama = $row['Nama'] ?? '-';
    $kelas = $row['Kelas'] ?? '-';
    $rayon = $row['Rayon'] ?? '-';
    $negara = $row['Daerah'] ?? 'Indonesia';
    $exp_itas_raw = $row['EXP_ITAS'] ?? null;
    if (strtolower(trim($negara)) === 'indonesia') {
        $kewarganegaraan = 'WNI';
    } else {
        if ($exp_itas_raw === 'None' || empty($exp_itas_raw)) {
            $kewarganegaraan = 'Affidavit';
        } else {
            $kewarganegaraan = 'WNA';
        }
    }

    $tempat_lahir = $row['Tempat Lahir'] ?? '-';
    $tanggal_lahir = $row['Tanggal Lahir'] ?? '2000-01-01';
    if ($tanggal_lahir === 'None' || empty($tanggal_lahir)) $tanggal_lahir = '2000-01-01';
    else $tanggal_lahir = date('Y-m-d', strtotime($tanggal_lahir));

    $nama_ibu = $row['Nama Ibu'] ?? '-';
    $nama_ayah = $row['Nama Ayah'] ?? '-';
    
    // Parse ints for phone numbers (warning: int(11) limit is 2147483647)
    $no_ibu = (int) preg_replace('/[^0-9]/', '', (string)($row['No_Ibu'] ?? '0'));
    $no_ayah = (int) preg_replace('/[^0-9]/', '', (string)($row['No_Ayah'] ?? '0'));
    $hp = (int) preg_replace('/[^0-9]/', '', (string)($row['Hp'] ?? '0'));
    if ($no_ibu > 2147483647) $no_ibu = 2147483647;
    if ($no_ayah > 2147483647) $no_ayah = 2147483647;
    if ($hp > 2147483647) $hp = 2147483647;

    $alamat = $row['Alamat'] ?? '-';
    $pondok = $row['Pondok'] ?? 'G1';
    $jenis_kelamin = $row['Jenis_Kelamin'] ?? 'Laki-laki';
    $kepengurusan = $row['Kepengurusan'] ?? '-';
    $nik = $row['NIK (SKTT)'] ?? '';
    $no_ic = $row['NO_IC'] ?? '';
    $keberadaan_paspor = $row['Keberadaan Pasport'] ?? '';
    $ukuran_baju = $row['Ukuran_Baju'] ?? '';
    
    $status_str = strtolower(trim($row['Status'] ?? 'aktif'));
    $aktif = ($status_str === 'inaktif') ? 0 : 1;

    $stmt = $pdo->prepare("INSERT INTO master_santri (stambuk, nama, kelas, rayon, negara, kewarganegaraan, tempat_lahir, tanggal_lahir, nama_ibu, nama_ayah, no_ibu, no_ayah, no_hp_alternatif, alamat, pondok, jenis_kelamin, path_foto, aktif, kode, kepengurusan, no_sktt, no_ic, keberadaan_paspor, ukuran_baju) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, '', ?, ?, ?, ?, ?)");
    $stmt->execute([
        $stambuk, $nama, $kelas, $rayon, $negara, $kewarganegaraan, $tempat_lahir, $tanggal_lahir, 
        $nama_ibu, $nama_ayah, $no_ibu, $no_ayah, $hp, $alamat, $pondok, $jenis_kelamin, $aktif, $kepengurusan,
        $nik, $no_ic, $keberadaan_paspor, $ukuran_baju
    ]);
    
    $kds = $pdo->lastInsertId();
    $countSantri++;

    // Paspor
    $no_paspor_lama = $row['No_Pasport_Lama'] ?? null;
    if ($no_paspor_lama && $no_paspor_lama !== 'None') {
        $stmtP = $pdo->prepare("INSERT INTO mtb_paspor (kds, no_paspor, tanggal_dikeluarkan, tempat_dikeluarkan, exp_paspor, aktif) VALUES (?, ?, '2020-01-01', '-', '2030-01-01', 1)");
        $stmtP->execute([$kds, $no_paspor_lama]);
    }

    $no_paspor = $row['No_Pasport_Baru'] ?? null;
    if (!$no_paspor || $no_paspor === 'None') {
        // Fallback if somehow there's only old passport
        if ($no_paspor_lama && $no_paspor_lama !== 'None') {
            // Already inserted as lama, do nothing else
        }
    } else {
        $exp = $row['EXP_PASPORT'] ?? null;
        if ($exp === 'None' || empty($exp)) $exp = '2030-01-01';
        else $exp = date('Y-m-d', strtotime($exp));
        
        $tgl_dikeluarkan = $row['Tanggal_Paspor_Dikeluarkan'] ?? '2020-01-01';
        if ($tgl_dikeluarkan === 'None' || empty($tgl_dikeluarkan)) $tgl_dikeluarkan = '2020-01-01';
        else $tgl_dikeluarkan = date('Y-m-d', strtotime($tgl_dikeluarkan));

        $tempat_dikeluarkan = $row['Tempat_Paspor_Dikeluarkan'] ?? '-';

        $stmtP = $pdo->prepare("INSERT INTO mtb_paspor (kds, no_paspor, tanggal_dikeluarkan, tempat_dikeluarkan, exp_paspor, aktif) VALUES (?, ?, ?, ?, ?, 1)");
        $stmtP->execute([$kds, $no_paspor, $tgl_dikeluarkan, $tempat_dikeluarkan, $exp]);
    }

    // ITAS
    $no_itas = $row['NO_ITAS'] ?? null;
    if ($no_itas && $no_itas !== 'None') {
        $exp_itas = $row['EXP_ITAS'] ?? null;
        if ($exp_itas === 'None' || empty($exp_itas)) $exp_itas = '2030-01-01';
        else $exp_itas = date('Y-m-d', strtotime($exp_itas));
        
        $lvl = (int) ($row['LVL_ITAS'] ?? 0);

        $stmtI = $pdo->prepare("INSERT INTO mtb_itas (kds, no_itas, exp_itas, level_itas, aktif, path_file) VALUES (?, ?, ?, ?, 1, '')");
        $stmtI->execute([$kds, $no_itas, $exp_itas, $lvl]);
    }

    // Barang Terlarang
    $brg = $row['Barang Terlarang'] ?? null;
    if ($brg && $brg !== 'None') {
        $stmtB = $pdo->prepare("INSERT INTO mtb_barang_terlarang (kds, nama_barang, jenis_barang, jumlah_barang, satuan, detail) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtB->execute([$kds, $brg, 'Lainnya', 1, 'pcs', '-']);
    }
}

echo "Imported $countSantri santri records and related data successfully.\n";
