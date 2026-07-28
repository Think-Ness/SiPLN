<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var array $students
 * @var int $cols
 * @var bool $showNama
 * @var bool $showGaris
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Foto Personal 3x4</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/assets/offline/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e2e8f0;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px 0;
        }

        .print-toolbar {
            max-width: 210mm; /* A4 Portrait */
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
            align-items: center;
        }

        .form-page {
            background: #fff;
            width: 210mm; /* A4 Portrait fixed */
            min-height: 297mm;
            padding: 15mm;
            box-sizing: border-box;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            margin-bottom: 20px;
        }

        /* â”€â”€ GRID FOTO â”€â”€ */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(<?= $cols ?>, 30mm);
            gap: 5mm;
            justify-content: center;
            align-content: start;
        }

        .photo-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .img-wrapper {
            width: 30mm;
            height: 40mm;
            background: #f8f9fa;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        <?php if ($showGaris): ?>
        .img-wrapper {
            border: 0.5pt dashed #999;
        }
        <?php endif; ?>

        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-photo {
            font-size: 7pt;
            color: #adb5bd;
            text-align: center;
            padding: 5px;
        }

        .photo-label {
            font-size: 6.5pt;
            text-align: center;
            width: 30mm;
            margin-top: 2mm;
            line-height: 1.2;
            word-wrap: break-word;
            font-weight: 600;
        }
        .photo-stambuk {
            font-size: 6pt;
            font-weight: normal;
            color: #475569;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .print-toolbar { display: none !important; }
            .form-page {
                box-shadow: none;
                margin: 0;
                border: none;
                page-break-after: always;
            }
            .form-page:last-child { page-break-after: auto; }
            .img-wrapper {
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
        Cetak Foto Personal 3x4 (Total: <?= count($students) ?>)
    </span>
</div>

<div class="pages-wrapper">
    <div class="form-page">
        <?php if (empty($students)): ?>
            <div style="text-align: center; padding: 50px; color: #64748b;">Tidak ada data santri yang dipilih.</div>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($students as $s): ?>
                    <div class="photo-item">
                        <div class="img-wrapper">
                            <?php if (!empty($s['path_foto'])): ?>
                                <img src="<?= API_URL ?>/santri/<?= urlencode((string)$s['kds']) ?>/photo" alt="Foto">
                            <?php else: ?>
                                <div class="no-photo">Tidak Ada Foto</div>
                            <?php endif; ?>
                        </div>
                        <?php if ($showNama): ?>
                            <div class="photo-label">
                                <?= htmlspecialchars((string)$s['nama']) ?>
                                <div class="photo-stambuk"><?= htmlspecialchars((string)$s['stambuk']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
