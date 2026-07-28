<?php
declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var array $students
 */

$fmtDate = function($d) {
    if (!$d || strtotime($d) === false || $d === '0000-00-00') return '';
    return date('d-M-y', strtotime($d));
};

function renderField(string $label, $value, string $sublabel = '') {
    $val = trim((string)$value);
    if ($val === '0') $val = '';
    
    $displayVal = $val !== '' ? htmlspecialchars($val) : '&nbsp;';
    $sublabelHtml = $sublabel !== '' ? " <span class='field-sublabel'>{$sublabel}</span>" : '';
    
    return "
        <div class='form-field-box'>
            <label class='field-label'>{$label}{$sublabelHtml}</label>
            <div class='field-value'>{$displayVal}</div>
        </div>
    ";
}

if (empty($students)) {
    $students = [[
        'stambuk' => '',
        'nama' => '',
        'kelas' => '',
        'rayon' => '',
        'negara' => '',
        'kewarganegaraan' => '',
        'tempat_lahir' => '',
        'tanggal_lahir' => '',
        'nama_ibu' => '',
        'nama_ayah' => '',
        'no_ibu' => '',
        'no_ayah' => '',
        'no_hp_alternatif' => '',
        'alamat' => '',
        'path_foto' => '',
        'no_sktt' => '',
        'no_ic' => '',
        'foto_url' => '',
        'paspor_baru' => null,
        'paspor_lama' => null,
        'itas' => null,
        'atm' => '',
        'hp' => '',
        'barang_terlarang' => '',
    ]];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendataan Santri Luar Negeri</title>
    
    
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
            font-size: 8.5pt;
            line-height: 1.3;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4;
            margin: 0;
        }

        /* ── SCREEN-ONLY TOOLBAR ── */
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

        /* ── PRINT PAGES ── */
        .pages-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
        }
        
        .form-page {
            background: #fff;
            width: 210mm;
            height: 296.5mm; /* slightly less than 297 to avoid overflow page */
            padding: 12mm 15mm;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* ── HEADER ── */
        .form-header {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-header h1 {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .form-header .subtitle {
            font-size: 8.5pt;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        /* ── GRID LAYOUT ── */
        .form-grid {
            display: grid;
            grid-template-columns: 57% 40%;
            column-gap: 3%;
            flex-grow: 1;
            width: 100%;
        }

        .left-col, .right-col {
            display: flex;
            flex-direction: column;
            gap: 16px; /* spacing between sections */
            width: 100%;
        }

        /* ── SECTIONS ── */
        .form-section {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            position: relative;
            padding: 15px 12px 10px 12px;
            background: #fafafa;
        }
        .section-title {
            position: absolute;
            top: -10px;
            left: 12px;
            background: #0f172a;
            color: #fff;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── FIELDS ── */
        .form-field-box {
            border-bottom: 1px dashed #cbd5e1;
            padding: 4px 2px;
            margin-bottom: 6px;
            width: 100%;
        }
        .form-field-box:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
        .field-label {
            font-size: 6.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
            letter-spacing: 0.2px;
        }
        .field-sublabel {
            font-size: 5.5pt;
            font-weight: 500;
            color: #94a3b8;
            text-transform: none;
            font-style: italic;
        }
        .field-value {
            font-size: 9pt;
            font-weight: 600;
            color: #0f172a;
            min-height: 16px;
            word-wrap: break-word;
        }

        /* Row Layouts */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 6px;
            width: 100%;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 0.7fr;
            gap: 12px;
            margin-bottom: 6px;
            width: 100%;
        }

        /* ── PHOTO BOX ── */
        .photo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
            width: 100%;
        }
        .photo-box {
            width: 3.5cm;
            height: 4.5cm;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #fff;
            color: #94a3b8;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            overflow: hidden;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── SIGNATURE ── */
        .signature-section {
            margin-top: auto; /* Push to bottom of col */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }
        .sig-date {
            font-size: 8.5pt;
            margin-bottom: 85px;
            color: #0f172a;
        }
        .sig-line {
            width: 220px;
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 4px;
        }
        .sig-label {
            font-size: 8pt;
            font-weight: 700;
            color: #0f172a;
        }

        /* ── FOOTER ── */
        .form-footer {
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            color: #64748b;
            width: 100%;
            margin-top: 15px;
        }
        .footer-left, .footer-right { font-weight: 600; }

        /* ── PRINT STYLES ── */
        @media print {
            body { background: #fff; }
            .print-toolbar { display: none !important; }
            .pages-wrapper { padding: 0; gap: 0; }
            .form-page {
                box-shadow: none;
                width: 210mm;
                height: 296.5mm;
                page-break-after: always;
                break-after: page;
                padding: 12mm 15mm;
            }
            .form-page:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }
            .form-section {
                border-color: #000;
                background: transparent;
            }
            .section-title {
                background: #000;
                color: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .form-field-box { border-bottom-color: #94a3b8; }
            .form-header { border-bottom-color: #000; }
            .form-header h1 { color: #000; }
            .field-value { color: #000; }
        }
    </style>
</head>
<body>

<!-- ── SCREEN-ONLY TOOLBAR ── -->
<div class="print-toolbar">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    <span class="toolbar-info">
        📄 Formulir Pendataan &nbsp;|&nbsp; Total: <?= count($students) ?> Halaman
    </span>
</div>

<div class="pages-wrapper">
    <?php foreach ($students as $s): ?>
    <div class="form-page">
        <div>
            <!-- Header -->
            <div class="form-header">
                <h1>Formulir Pendataan Santri Luar Negeri</h1>
                <?php 
                    $myKep = $_SESSION['def_kepengurusan'] ?? '';
                    $sKep  = trim((string)($s['kepengurusan'] ?? ''));
                    if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0): 
                ?>
                <div style="margin-top: 10px; padding: 5px 15px; background: #fffbeb; color: #b45309; border: 1px dashed #b45309; font-size: 8pt; font-weight: 700; border-radius: 6px; text-transform: uppercase;">
                    <i class="bi bi-exclamation-triangle"></i> Dokumen Santri Titipan (Pindahan dari <?= htmlspecialchars($sKep) ?>)
                </div>
                <?php endif; ?>
            </div>

            <!-- Grid Layout -->
            <div class="form-grid">
                
                <!-- Left Column (58%) -->
                <div class="left-col">
                    
                    <!-- Biodata Lengkap -->
                    <div class="form-section">
                        <span class="section-title">Biodata Lengkap</span>
                        
                        <?= renderField('Regno', $s['stambuk']) ?>
                        <?= renderField('Nama Lengkap', $s['nama'], '*Sesuai Paspor') ?>
                        
                        <div class="form-row">
                            <?= renderField('Kelas', $s['kelas']) ?>
                            <?= renderField('Rayon', $s['rayon']) ?>
                        </div>
                        
                        <?= renderField('Kewarganegaraan', $s['negara']) ?>
                        
                        <div class="form-row">
                            <?= renderField('Tempat Lahir', $s['tempat_lahir']) ?>
                            <?= renderField('Tanggal Lahir', $fmtDate($s['tanggal_lahir'])) ?>
                        </div>
                        
                        <!-- Alamat Box -->
                        <div class="form-field-box">
                            <label class="field-label">Alamat</label>
                            <div class="field-value" style="min-height: 48px; line-height: 1.35; overflow: hidden;">
                                <?php if (!empty(trim((string)$s['alamat']))): ?>
                                    <?= nl2br(htmlspecialchars(trim((string)$s['alamat']))) ?>
                                <?php else: ?>
                                    &nbsp;
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tentang Paspor -->
                    <div class="form-section">
                        <span class="section-title">Tentang Paspor</span>
                        
                        <div class="form-row">
                            <?= renderField('No Paspor Baru', $s['paspor_baru']['no_paspor'] ?? '') ?>
                            <?= renderField('No Paspor Lama', $s['paspor_lama']['no_paspor'] ?? '') ?>
                        </div>
                        
                        <div class="form-row-3">
                            <?= renderField('Exp Paspor', $fmtDate($s['paspor_baru']['exp_paspor'] ?? '')) ?>
                            <?= renderField('Exp ITAS', $fmtDate($s['itas']['exp_itas'] ?? '')) ?>
                            <?= renderField('Lvl ITAS', ($s['itas']['level_itas'] ?? '') !== '' ? (string)$s['itas']['level_itas'] : '') ?>
                        </div>
                        
                        <div class="form-row">
                            <?= renderField('No ITAS', $s['itas']['no_itas'] ?? '') ?>
                            <?= renderField('No IC Santri', $s['no_ic']) ?>
                        </div>
                    </div>

                    <!-- Signature Block -->
                    <div class="signature-section">
                        <div class="sig-date">Gontor, ...........................................</div>
                        <div class="sig-line"></div>
                        <div class="sig-label">Ttd dan Nama Terang</div>
                    </div>

                </div>

                <!-- Right Column (40%) -->
                <div class="right-col">
                    
                    <!-- Foto -->
                    <div class="photo-wrapper">
                        <div class="photo-box">
                            <?php if (!empty($s['foto_url'])): ?>
                                <img src="<?= htmlspecialchars($s['foto_url']) ?>" alt="Foto Santri">
                            <?php else: ?>
                                <span>Foto</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Data Orang Tua -->
                    <div class="form-section">
                        <span class="section-title">Data Orang Tua</span>
                        
                        <?= renderField('Nama Ibu', $s['nama_ibu']) ?>
                        <?= renderField('Nama Ayah', $s['nama_ayah']) ?>
                        
                        <?= renderField('No Ayah (Aktif/WA)', $s['no_ayah']) ?>
                        <?= renderField('No Ibu (Aktif/WA)', $s['no_ibu']) ?>
                        <?= renderField('No Wali/Darurat (Aktif/WA)', $s['no_hp_alternatif']) ?>
                    </div>

                    <!-- Barang Bawaan -->
                    <div class="form-section">
                        <span class="section-title">Barang Bawaan</span>
                        
                        <?= renderField('Bank ATM', $s['atm']) ?>
                        <?= renderField('Merek HP', $s['hp']) ?>
                        
                        <!-- Barang Terlarang Box -->
                        <div class="form-field-box">
                            <label class="field-label">Keterangan Barang Terlarang Yang Dibawa</label>
                            <div class="field-value" style="min-height: 48px; line-height: 1.35; overflow: hidden;">
                                <?php if (!empty($s['barang_terlarang'])): ?>
                                    <?= htmlspecialchars($s['barang_terlarang']) ?>
                                <?php else: ?>
                                    &nbsp;
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="form-footer">
            <span class="footer-left"><?= date('l, d F Y') ?></span>
            <span class="footer-right"><?= date('H:i:s') ?></span>
        </div>
    </div>
    <?php endforeach; ?>
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
