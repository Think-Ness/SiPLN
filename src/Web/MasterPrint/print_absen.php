<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var array $students
 * @var string $judul
 * @var int $frek
 * @var array $kolomPilihan
 * @var array $instansi
 * @var string $orientasi
 */

$colMap = [
    'stambuk' => 'Stambuk', 
    'kelas' => 'Kelas', 
    'rayon' => 'Rayon',
    'pondok' => 'Pondok/Asrama',
    'status_santri' => 'Status Santri',
    'jenis_kelamin' => 'Jenis Kelamin',
    'negara' => 'Asal Negara', 
    'kewarganegaraan' => 'Kewarganegaraan',
    'tempat_lahir' => 'Tempat Lahir',
    'tanggal_lahir' => 'Tanggal Lahir', 
    'alamat' => 'Alamat',
    'no_ic' => 'No IC',
    'no_sktt' => 'No SKTT',
    'ukuran_baju' => 'Ukuran Baju',
    'keberadaan_paspor' => 'Keberadaan Paspor',
    'no_paspor' => 'No Paspor', 
    'exp_paspor' => 'Exp Paspor',
    'no_itas' => 'No ITAS', 
    'level_itas' => 'Level ITAS', 
    'exp_itas' => 'Exp ITAS',
    'nama_ayah' => 'Nama Ayah', 
    'nama_ibu' => 'Nama Ibu', 
    'no_hp' => 'No HP'
];

$headers = [];
foreach ($kolomPilihan as $k) {
    if (isset($colMap[$k])) {
        $headers[$k] = $colMap[$k];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($judul) ?></title>
    
    <link href="<?= ASSET_URL ?>/assets/offline/css/inter.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 9pt;
            line-height: 1.3;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 <?= htmlspecialchars($orientasi ?? 'landscape') ?>;
            margin: 10mm;
        }

        /* â”€â”€ SCREEN-ONLY TOOLBAR â”€â”€ */
        .print-toolbar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            padding: 12px;
            background: #0f172a;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .btn-print {
            padding: 8px 20px;
            font-weight: 600;
            font-size: 9.5pt;
            cursor: pointer;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #2563eb; }
        .toolbar-info { font-size: 9pt; opacity: 0.85; }

        /* â”€â”€ PRINT PAGES â”€â”€ */
        .pages-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
        }
        
        .form-page {
            background: #fff;
            width: <?= ($orientasi ?? 'landscape') === 'portrait' ? '210mm' : '277mm' ?>;
            max-width: 100%;
            min-height: <?= ($orientasi ?? 'landscape') === 'portrait' ? '290mm' : '190mm' ?>;
            padding: 12mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            position: relative;
            overflow: hidden;
        }

        /* â”€â”€ KOP SURAT â”€â”€ */
        .kop-surat-container {
            width: 100%;
            margin-bottom: 20px;
            text-align: center;
        }
        .kop-surat-img {
            max-width: 100%;
            height: auto;
        }

        /* â”€â”€ TITLE â”€â”€ */
        .report-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* â”€â”€ TABLE â”€â”€ */
        .table-absen {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: auto;
            word-wrap: break-word;
        }
        .table-absen th, .table-absen td {
            border: 1px solid #000;
            padding: 6px 4px;
        }
        .table-absen th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
        }
        .table-absen td {
            vertical-align: middle;
        }
        
        .col-no { width: 30px; text-align: center; }
        .col-nama { min-width: 150px; }
        .col-frek { width: 35px; text-align: center; }

        @media print {
            body { background: #fff; }
            .print-toolbar { display: none !important; }
            .pages-wrapper { padding: 0; gap: 0; }
            .form-page {
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 0;
            }
            .table-absen th {
                background-color: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="print-toolbar">
    <button class="btn-print" onclick="window.print()">ðŸ–¨ï¸ Cetak / Simpan PDF</button>
    <span class="toolbar-info">
        ðŸ“„ <?= htmlspecialchars($judul) ?> &nbsp;|&nbsp; Total: <?= count($students) ?> Santri
    </span>
</div>

<div class="pages-wrapper">
    <div class="form-page">
        
        <!-- KOP SURAT -->
        <?php if (!empty($instansi['kop_surat']) && ($tampilKop ?? '1') !== '0'): ?>
        <div class="kop-surat-container">
            <img src="<?= API_URL ?>/profil-instansi/kop-surat/view?kode=<?= urlencode((string)$instansi['kode']) ?>" class="kop-surat-img" alt="Kop Surat">
        </div>
        <?php endif; ?>

        <div class="report-title"><?= htmlspecialchars($judul) ?></div>

        <table class="table-absen">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Nama Santri</th>
                    <?php foreach ($headers as $h): ?>
                        <th><?= htmlspecialchars($h) ?></th>
                    <?php endforeach; ?>
                    <?php for ($i = 1; $i <= $frek; $i++): ?>
                        <th class="col-frek"><?= $i ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="<?= 2 + count($headers) + $frek ?>" style="text-align: center; padding: 20px;">
                            Tidak ada data santri yang dipilih.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($students as $s): ?>
                        <tr>
                            <td class="col-no"><?= $no++ ?></td>
                            <td class="col-nama fw-semibold"><?= htmlspecialchars($s['nama'] ?? '') ?></td>
                            
                            <?php foreach ($headers as $key => $label): ?>
                                <td>
                                    <?php 
                                        $val = '';
                                        if ($key === 'no_paspor') $val = $s['paspor']['no_paspor'] ?? '';
                                        elseif ($key === 'exp_paspor') $val = $s['paspor']['exp_paspor'] ?? '';
                                        elseif ($key === 'no_itas') $val = $s['itas']['no_itas'] ?? '';
                                        elseif ($key === 'level_itas') $val = $s['itas']['level_itas'] ?? '';
                                        elseif ($key === 'exp_itas') $val = $s['itas']['exp_itas'] ?? '';
                                        elseif ($key === 'no_hp') {
                                            $hp = array_filter([$s['no_ayah'] ?? '', $s['no_ibu'] ?? '', $s['no_hp_alternatif'] ?? '']);
                                            $val = reset($hp) ?: '';
                                        }
                                        elseif ($key === 'ukuran_baju') {
                                            $val = strtoupper((string)($s['ukuran_baju'] ?? ''));
                                        }
                                        else $val = $s[$key] ?? '';
                                        
                                        // FORMAT TANGGAL
                                        if (in_array($key, ['tanggal_lahir', 'exp_paspor', 'exp_itas']) && !empty($val) && $val !== '0000-00-00') {
                                            try {
                                                $dateObj = new DateTime($val);
                                                $val = $dateObj->format('d-M-Y');
                                            } catch (Exception $e) {
                                                // Invalid date, ignore
                                            }
                                        }
                                        
                                        echo htmlspecialchars((string)$val);
                                    ?>
                                </td>
                            <?php endforeach; ?>

                            <?php for ($i = 1; $i <= $frek; $i++): ?>
                                <td></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 600);
};
</script>
</body>
</html>
