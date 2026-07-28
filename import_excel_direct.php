<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'd:/01. Project/04. Website/pln/Tbl_Database_LN.xlsx';

function parseDateStr($val) {
    if (empty($val)) return '0000-00-00';
    $val = trim($val);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;
    if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $val, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[1], (int)$m[2]); 
    }
    if (is_numeric($val) && (int)$val > 30000 && (int)$val < 60000) {
        $unix = ((int)$val - 25569) * 86400;
        return date('Y-m-d', $unix);
    }
    $ts = strtotime($val);
    if ($ts !== false) return date('Y-m-d', $ts);
    return '0000-00-00';
}

try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);
    
    array_shift($data); // Remove headers
    
    $stats = ['success' => 0, 'updated' => 0, 'failed' => 0];
    
    $db->beginTransaction();
    try {
        foreach ($data as $ri => $row) {
            try {
                $stambuk = (int)($row['E'] ?? 0);
                if (!$stambuk) continue; 
                
                $nama = trim($row['F'] ?? '');
                if (!$nama) continue;
                
                $jk = trim($row['AR'] ?? '');
                if ($jk == 'L') $jk = 'Laki-laki';
                elseif ($jk == 'P') $jk = 'Perempuan';
                
                $negara = trim($row['H'] ?? 'Indonesia');
                $kewarganegaraan = (strtolower($negara) == 'indonesia') ? 'WNI' : 'WNA';
                if (empty($row['M'])) $kewarganegaraan = 'Affidavit'; 
                
                $tanggalLahir = parseDateStr($row['O'] ?? '');
                
                $santriData = [
                    'stambuk' => $stambuk,
                    'nama' => $nama,
                    'kelas' => trim($row['G'] ?? ''),
                    'rayon' => trim($row['I'] ?? ''),
                    'negara' => $negara,
                    'tempat_lahir' => trim($row['N'] ?? ''),
                    'tanggal_lahir' => $tanggalLahir,
                    'jenis_kelamin' => $jk,
                    'alamat' => trim($row['T'] ?? ''),
                    'pondok' => trim($row['Z'] ?? ''),
                    'kepengurusan' => trim($row['AI'] ?? ''),
                    'no_sktt' => trim($row['AK'] ?? ''),
                    'no_ic' => trim($row['S'] ?? ''),
                    'ukuran_baju' => trim($row['AB'] ?? ''),
                    'keberadaan_paspor' => trim($row['AA'] ?? ''),
                    'nama_ayah' => trim($row['Q'] ?? ''),
                    'nama_ibu' => trim($row['P'] ?? ''),
                    'no_ayah' => (int)preg_replace('/[^0-9]/', '', $row['AU'] ?? ''),
                    'no_ibu' => (int)preg_replace('/[^0-9]/', '', $row['AV'] ?? ''),
                    'kewarganegaraan' => $kewarganegaraan,
                    'aktif' => (strtolower(trim($row['AC'] ?? '')) == 'aktif') ? 1 : 0
                ];
                
                $stmt = $db->prepare("SELECT kds FROM master_santri WHERE stambuk = ?");
                $stmt->execute([$stambuk]);
                $existingKds = $stmt->fetchColumn();
                
                if ($existingKds) {
                    $setCols = [];
                    $values = [];
                    foreach ($santriData as $k => $v) {
                        $setCols[] = "`$k` = ?";
                        $values[] = $v;
                    }
                    $values[] = $existingKds;
                    $db->prepare("UPDATE master_santri SET " . implode(', ', $setCols) . " WHERE kds = ?")->execute($values);
                    $kds = $existingKds;
                    $stats['updated']++;
                } else {
                    $santriData['kode'] = strtoupper(substr($nama, 0, 3)) . date('y');
                    $santriData['path_foto'] = '';
                    $cols = array_keys($santriData);
                    $placeholders = array_fill(0, count($cols), '?');
                    $db->prepare("INSERT INTO master_santri (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")")->execute(array_values($santriData));
                    $kds = $db->lastInsertId();
                    $stats['success']++;
                }
                
                // Clear and recreate
                $db->prepare("DELETE FROM mtb_paspor WHERE kds = ?")->execute([$kds]);
                $db->prepare("DELETE FROM mtb_itas WHERE kds = ?")->execute([$kds]);
                
                // Paspor
                $noPaspor = trim($row['J'] ?? '');
                if ($noPaspor) {
                    $db->prepare("INSERT INTO mtb_paspor (kds, no_paspor, exp_paspor, tanggal_dikeluarkan, tempat_dikeluarkan, aktif) VALUES (?, ?, ?, ?, ?, 1)")
                        ->execute([
                            $kds, 
                            $noPaspor, 
                            parseDateStr($row['L'] ?? ''), 
                            parseDateStr($row['AP'] ?? ''), 
                            trim($row['AQ'] ?? '')
                        ]);
                }
                
                // ITAS
                $noItas = trim($row['X'] ?? '');
                if ($noItas) {
                    $db->prepare("INSERT INTO mtb_itas (kds, no_itas, exp_itas, level_itas, aktif) VALUES (?, ?, ?, ?, 1)")
                        ->execute([
                            $kds, 
                            $noItas, 
                            parseDateStr($row['M'] ?? ''), 
                            (int)($row['Y'] ?? 0)
                        ]);
                }
                
            } catch (\Exception $e) {
                echo "Error on row {$ri}: " . $e->getMessage() . "\n";
                $stats['failed']++;
            }
        }
        $db->commit();
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
    echo "Import selesai.\n";
    print_r($stats);
    
} catch (\Exception $e) {
    echo "Global Error: " . $e->getMessage() . "\n";
}
