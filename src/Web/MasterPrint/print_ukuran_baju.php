<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var array $grouped
 * @var array $rekap
 * @var string $judul
 * @var array $instansi
 * @var string $orientasi
 * @var string $tampilKop
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($judul) ?></title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e2e8f0;
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px 0;
        }

        .print-toolbar {
            max-width: <?= ($orientasi ?? 'portrait') === 'portrait' ? '210mm' : '277mm' ?>;
            margin: 0 auto 15px auto;
            background: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-print:hover { background: #0b5ed7; }
        
        .pages-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .form-page {
            background: #fff;
            width: <?= ($orientasi ?? 'portrait') === 'portrait' ? '210mm' : '277mm' ?>;
            max-width: 100%;
            min-height: <?= ($orientasi ?? 'portrait') === 'portrait' ? '290mm' : '190mm' ?>;
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
        .table-data {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
            table-layout: auto;
            word-wrap: break-word;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .table-data th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
        }
        .size-header {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #1e293b;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 3px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .print-toolbar { display: none !important; }
            .pages-wrapper { gap: 0; }
            .form-page {
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 0;
                page-break-after: always;
            }
            .form-page:last-child { page-break-after: auto; }
            .table-data th {
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
    <span class="toolbar-info fw-bold text-muted">
        ðŸ“„ <?= htmlspecialchars($judul) ?>
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

        <?php if (empty($grouped)): ?>
            <div style="text-align: center; padding: 20px;">Tidak ada data santri yang dipilih.</div>
        <?php else: ?>
            
            <?php foreach ($grouped as $size => $list): ?>
                <div class="size-header">UKURAN BAJU: <?= htmlspecialchars($size) ?></div>
                <table class="table-data">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 120px;">Stambuk</th>
                            <th>Nama Santri</th>
                            <th style="width: 100px;">Kelas</th>
                            <th style="width: 150px;">Rayon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($list as $s): ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++ ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)$s['stambuk']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars((string)$s['nama']) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)$s['kelas']) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)$s['rayon']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
            
            <!-- REKAPITULASI -->
            <div style="page-break-inside: avoid;">
                <div class="size-header" style="margin-top: 30px;">REKAPITULASI UKURAN BAJU</div>
                <table class="table-data" style="width: 50%; margin: 0;">
                    <thead>
                        <tr>
                            <th>Ukuran Baju</th>
                            <th>Jumlah (Santri)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; foreach ($rekap as $size => $count): $total += $count; ?>
                            <tr>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($size) ?></td>
                                <td style="text-align: center;"><?= $count ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th style="text-align: right; background: #e2e8f0;">TOTAL SANTRI</th>
                            <th style="text-align: center; background: #e2e8f0; font-size: 11pt;"><?= $total ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        <?php endif; ?>

    </div>
</div>

</body>
</html>
