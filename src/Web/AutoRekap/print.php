<?php
declare(strict_types=1);

/**
 * @var string $type
 * @var array $groupedData
 * @var string $urgency
 * @var string $kep
 * @var string $pondok
 */

$isItas = $type === 'itas';
$title = $isItas ? 'TANGGAL KADALUARSA ITAS SANTRI LUAR NEGERI' : 'TANGGAL KADALUARSA PASPOR SANTRI LUAR NEGERI';
$subtitle = $isItas
    ? 'Rekap Perpanjangan ITAS — Proses dimulai <strong>3 Bulan</strong> sebelum habis'
    : 'Rekap Perpanjangan Paspor — Proses dimulai <strong>18 Bulan</strong> sebelum habis';

$isInvalidGroup = fn($d) => $d === 'Belum Ada Data' || $d === 'Belum Ada Data (WNI)' || $d === 'Belum Ada Data (WNA)';
$fmtDate = fn($d) => ($d && !$isInvalidGroup($d) && strtotime($d) !== false) ? date('d-M-Y', strtotime($d)) : '–';
$fmtShort = fn($d) => ($d && !$isInvalidGroup($d) && strtotime($d) !== false) ? date('d-M-Y', strtotime($d)) : '–';
$isExpiredDate = fn($d) => ($d && !$isInvalidGroup($d) && strtotime($d) !== false && strtotime(date('Y-m-d', strtotime($d))) < strtotime(date('Y-m-d')));
$calcPengajuan = function($d, $type) use ($isInvalidGroup) {
    if ($d === 'Belum Ada Data (WNI)') return 'WNI';
    if ($d === 'Belum Ada Data (WNA)') return 'Affidavit';
    if (!$d || $isInvalidGroup($d) || strtotime($d) === false) return '–';
    $months = $type === 'itas' ? -3 : -18;
    return date('d M Y', strtotime("$months months", strtotime($d)));
};
$calcPengajuanNote = function($d, $type) use ($isInvalidGroup) {
    if ($d === 'Belum Ada Data (WNI)') return 'WNI (Tidak Perlu)';
    if ($d === 'Belum Ada Data (WNA)') return 'Affidavit';
    if (!$d || $isInvalidGroup($d) || strtotime($d) === false) return '–';
    $months = $type === 'itas' ? -3 : -18;
    $bulanId = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts  = strtotime("$months months", strtotime($d));
    return date('d', $ts) . ' ' . $bulanId[(int)date('n', $ts)] . ' ' . date('Y', $ts);
};
$docLabel = $isItas ? 'REKOM Kemenag' : 'perpanjangan paspor';
$intervalLabel = $isItas ? '3 Bulan sebelum ITAS habis' : '18 Bulan sebelum paspor habis';

$totalSantri = array_sum(array_map('count', $groupedData));
$totalGrup   = count($groupedData);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <!-- Modern Bootstrap CSS -->
    <link href="<?= ASSET_URL ?>/assets/offline/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Base Print Styles */
        body {
            background-color: #eef2f6; 
            color: #1e293b;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 10pt;
        }

        .page-container {
            background: white;
            margin: 20px auto;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            max-width: 297mm; min-height: 210mm; /* A4 Landscape for screen */
        }

        @media print {
            body { background-color: white; margin: 0; padding: 0; }
            .page-container { margin: 0; padding: 0; box-shadow: none; width: 100%; max-width: none; }
            .no-print { display: none !important; }
            
            /* FORCE LANDSCAPE */
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }
            .page-break { page-break-before: always; }
        }

        /* Kop Surat & Header */
        .kop-surat { border-bottom: 3px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        
        .report-title {
            text-align: center;
            font-weight: 800;
            font-size: 16pt;
            letter-spacing: 1px;
            margin-bottom: 25px;
            text-transform: uppercase;
            color: #0f172a;
        }

        /* Executive Summary Cards */
        .exec-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .exec-card.grup { border-left: 5px solid #3b82f6; }
        .exec-card.santri { border-left: 5px solid #10b981; }
        .exec-card.proses { border-left: 5px solid #8b5cf6; }
        .exec-card.tahun { border-left: 5px solid #f59e0b; }
        
        .exec-card .val { font-size: 24pt; font-weight: 800; line-height: 1; }
        .exec-card .lbl { font-size: 9pt; font-weight: 600; text-transform: uppercase; color: #64748b; margin-top: 5px; }

        /* Tables */
        .table-modern {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }
        .table-modern th, .table-modern td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            vertical-align: middle;
        }
        .table-modern th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: 700;
            text-align: center;
        }
        .table-modern tbody tr:nth-child(even) { background-color: #f8fafc; }

        .section-title { font-size: 12pt; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px;}

        /* Badges */
        .badge-custom {
            display: inline-block;
            background: #e2e8f0;
            color: #334155;
            font-size: 8pt;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 5px;
        }

        .text-danger-modern { color: #e11d48 !important; }
        .text-warning-modern { color: #d97706 !important; }

        /* Signature Box */
        .signature-box { float: right; width: 250px; text-align: center; margin-top: 40px; page-break-inside: avoid; }
    </style>
</head>
<body>

    <!-- Control Bar (Not printed) -->
    <div class="no-print bg-dark text-white p-3 d-flex justify-content-between align-items-center sticky-top shadow">
        <div>
            <strong>Pusat Cetak Laporan Eksekutif</strong> <span class="badge bg-secondary ms-2">AUTO LANDSCAPE</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer me-1" viewBox="0 0 16 16">
                  <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                  <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                </svg>
                Cetak Laporan
            </button>
            <button onclick="window.close()" class="btn btn-outline-light btn-sm">Tutup</button>
        </div>
    </div>

    <!-- Printable Document -->
    <div class="page-container">
        
        <!-- Modern Header -->
        <div class="report-title no-print" style="margin-top: 10px;">
            <div style="font-size: 18pt; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; margin-bottom: 5px;">
                <?= htmlspecialchars($title) ?>
            </div>
            <div style="font-size: 9.5pt; font-weight: 500; color: #64748b;">
                <span class="badge bg-light text-secondary border me-2">PEMBIMBING LUAR NEGERI</span>
                Diperbarui pada: <?= date('d F Y') ?>
            </div>
        </div>

        <!-- SUMMARY BLOCKS (Hidden in print) -->
        <?php if (!empty($groupedData)): ?>
        <div class="row g-3 mb-4 no-print">
            <div class="col">
                <div class="exec-card santri">
                    <div class="val" style="color: #10b981;"><?= $totalSantri ?></div>
                    <div class="lbl">Total Santri</div>
                </div>
            </div>
            <div class="col">
                <div class="exec-card grup">
                    <div class="val" style="color: #3b82f6;"><?= $totalGrup ?></div>
                    <div class="lbl">Grup Expired</div>
                </div>
            </div>
            <div class="col">
                <div class="exec-card proses">
                    <div class="val" style="color: #8b5cf6;"><?= $isItas ? '3 Bln' : '18 Bln' ?></div>
                    <div class="lbl">Rentang Proses</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- FILTER INFO -->
        <div class="mb-4 no-print text-center">
            <?php
                $filterTags = [];
                if (!empty($kep))     $filterTags[] = 'Kep: ' . htmlspecialchars($kep);
                if (!empty($pondok))  $filterTags[] = 'Pondok: ' . htmlspecialchars($pondok);
                $urgencyLabels = ['period' => 'Periode Normal', 'urgent' => 'URGENT', 'all' => 'Semua Santri'];
                $filterTags[] = ($urgencyLabels[$urgency ?? 'period'] ?? 'Periode Normal');
            ?>
            <?php foreach ($filterTags as $tag): ?>
                <span class="badge bg-secondary me-2"><?= $tag ?></span>
            <?php endforeach; ?>
        </div>

        <!-- GROUPS -->
        <?php if (empty($groupedData)): ?>
            <div class="text-center my-5 border p-4" style="background: #f8fafc; border-radius: 8px;">
                <em>Tidak ada data santri yang perlu diproses berdasarkan filter saat ini.</em>
            </div>
        <?php else: ?>
            <?php foreach ($groupedData as $groupKey => $groupData): ?>
                <?php
                    $santriAsli = $groupData['Asli'] ?? [];
                    $santriPindahan = $groupData['Pindahan'] ?? [];
                    $jumlah = count($santriAsli) + count($santriPindahan);

                    if ($isItas) {
                        $tglHabis   = $fmtDate($groupKey);
                        $tglPengRef = $groupKey;
                    } else {
                        $tglHabis   = $isInvalidGroup($groupKey) ? '–' : date('F Y', strtotime($groupKey . '-01'));
                        $tglPengRef = $isInvalidGroup($groupKey) ? $groupKey : $groupKey . '-01';
                    }

                    $tglPengajuan = $calcPengajuan($tglPengRef, $type);
                    $tglPengNote  = $calcPengajuanNote($tglPengRef, $type);
                    
                    if ($groupKey === 'Belum Ada Data (WNI)') {
                        $noteText = "Santri WNI tidak memerlukan pengajuan {$docLabel}.";
                    } elseif ($groupKey === 'Belum Ada Data (WNA)') {
                        $noteText = "Santri WNA berkewarganegaraan ganda (Gunakan fasilitas Affidavit).";
                    } else {
                        $noteText = "Mulai proses {$docLabel} pada {$tglPengNote}";
                    }
                ?>
                <div style="page-break-inside: avoid; margin-bottom: 30px;">
                    
                    <!-- Modern Group Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #3b82f6; padding-bottom: 8px; margin-bottom: 12px;">
                        <div>
                            <?php $isGroupExpired = $isItas && $isExpiredDate($groupKey); ?>
                            <span style="font-size: 14pt; font-weight: 800; color: #0f172a;">Expired: <span class="text-danger-modern"><?= $tglHabis ?></span>
                                <?php if ($isGroupExpired): ?>
                                    <span style="font-size:8pt; background:#e11d48; color:#fff; padding:3px 6px; border-radius:4px; vertical-align:middle; margin-left:10px;">KADALUARSA</span>
                                <?php endif; ?>
                            </span>
                            <span class="badge-custom"><?= $jumlah ?> Santri</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 8pt; color: #64748b; font-weight: 700; text-transform: uppercase;">Tanggal Pengajuan</div>
                            <div style="font-size: 12pt; font-weight: 800; color: #10b981;"><?= $tglPengajuan ?></div>
                            <div style="font-size: 7.5pt; color: #475569;"><em><?= $noteText ?></em></div>
                        </div>
                    </div>

                    <!-- Detail Table Asli -->
                    <?php if (!empty($santriAsli)): ?>
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 5px; font-size: 10pt; text-transform: uppercase;">
                        <i class="bi bi-shield-check me-1 text-success"></i> Santri Utama (Asli)
                    </div>
                    <table class="table-modern" style="margin-bottom: 20px;">
                        <thead>
                            <tr>
                                <th style="width:4%">#</th>
                                <th style="width:8%">Pondok</th>
                                <th style="width:14%">No Paspor</th>
                                <th style="width:12%">Exp Paspor</th>
                                <th style="width:6%">ITAS</th>
                                <th style="text-align: left;">Nama Santri</th>
                                <th style="width:7%">Kelas</th>
                                <th style="width:12%">Negara</th>
                                <th style="width:10%">Rayon</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($santriAsli as $i => $s): 
                                $expPasporWarn = !empty($s['exp_paspor']) && strtotime($s['exp_paspor']) <= strtotime('+6 months');
                                $isExp = $isExpiredDate((string)$s['exp_paspor']);
                            ?>
                            <tr>
                                <td style="text-align: center; color: #64748b;"><?= $i + 1 ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['pondok'] ?? '–')) ?></td>
                                <td style="text-align: center; font-family: monospace; font-size: 9pt;"><?= htmlspecialchars((string)($s['no_paspor'] ?? '–')) ?></td>
                                <td style="text-align: center;" class="<?= ($expPasporWarn || $isExp) ? 'text-danger-modern fw-bold' : '' ?>">
                                    <?= $fmtShort((string)$s['exp_paspor']) ?>
                                    <?php if ($isExp): ?><br><span style="font-size:7pt; background:#e11d48; color:#fff; padding:1px 4px; border-radius:3px;">KADALUARSA</span><?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($s['itas_lvl'])): ?>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars((string)$s['itas_lvl']) ?></span>
                                    <?php else: ?>–<?php endif; ?>
                                </td>
                                <td style="font-weight: 600;">
                                    <?= htmlspecialchars((string)$s['nama']) ?>
                                </td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['kelas'] ?? '–')) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['daerah'] ?? '–')) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['rayon'] ?? '–')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- Detail Table Pindahan -->
                    <?php if (!empty($santriPindahan)): ?>
                    <div style="font-weight: 700; color: #b45309; margin-bottom: 5px; font-size: 10pt; text-transform: uppercase;">
                        <i class="bi bi-box-arrow-in-right me-1 text-warning"></i> Santri Pindahan
                    </div>
                    <table class="table-modern" style="border: 1px solid #fcd34d;">
                        <thead style="background: #fffbeb; color: #92400e;">
                            <tr>
                                <th style="width:4%; background: transparent; color: inherit;">#</th>
                                <th style="width:8%; background: transparent; color: inherit;">Pondok</th>
                                <th style="width:14%; background: transparent; color: inherit;">No Paspor</th>
                                <th style="width:12%; background: transparent; color: inherit;">Exp Paspor</th>
                                <th style="width:6%; background: transparent; color: inherit;">ITAS</th>
                                <th style="text-align: left; background: transparent; color: inherit;">Nama Santri</th>
                                <th style="width:7%; background: transparent; color: inherit;">Kelas</th>
                                <th style="width:12%; background: transparent; color: inherit;">Negara</th>
                                <th style="width:10%; background: transparent; color: inherit;">Rayon</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($santriPindahan as $i => $s): 
                                $expPasporWarn = !empty($s['exp_paspor']) && strtotime($s['exp_paspor']) <= strtotime('+6 months');
                                $isExp = $isExpiredDate((string)$s['exp_paspor']);
                            ?>
                            <tr style="background: #fffbeb;">
                                <td style="text-align: center; color: #92400e;"><?= $i + 1 ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['pondok'] ?? '–')) ?></td>
                                <td style="text-align: center; font-family: monospace; font-size: 9pt;"><?= htmlspecialchars((string)($s['no_paspor'] ?? '–')) ?></td>
                                <td style="text-align: center;" class="<?= ($expPasporWarn || $isExp) ? 'text-danger-modern fw-bold' : '' ?>">
                                    <?= $fmtShort((string)$s['exp_paspor']) ?>
                                    <?php if ($isExp): ?><br><span style="font-size:7pt; background:#e11d48; color:#fff; padding:1px 4px; border-radius:3px;">KADALUARSA</span><?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($s['itas_lvl'])): ?>
                                        <span class="badge bg-warning text-dark border border-warning"><?= htmlspecialchars((string)$s['itas_lvl']) ?></span>
                                    <?php else: ?>–<?php endif; ?>
                                </td>
                                <td style="font-weight: 600;">
                                    <?= htmlspecialchars((string)$s['nama']) ?>
                                    <span class="badge bg-warning text-dark border border-warning ms-1" style="font-size: 0.6rem; padding: 1px 3px;">Pindahan (<?= htmlspecialchars((string)$s['kepengurusan']) ?>)</span>
                                </td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['kelas'] ?? '–')) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['daerah'] ?? '–')) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars((string)($s['rayon'] ?? '–')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- RINGKASAN LAPORAN -->
        <?php if (!empty($groupedData)):
            $byNegara = [];
            $totalAsli = 0;
            $totalPindahan = 0;
            foreach ($groupedData as $dateGroup => $statuses) {
                foreach ($statuses as $status => $rows) {
                    foreach ($rows as $r) {
                        $neg = $r['daerah'] ?? 'Tidak Diketahui';
                        if (empty(trim($neg))) $neg = 'Tidak Diketahui';
                        $byNegara[$neg] = ($byNegara[$neg] ?? 0) + 1;
                        
                        if ($status === 'Asli') $totalAsli++;
                        if ($status === 'Pindahan') $totalPindahan++;
                    }
                }
            }
            arsort($byNegara);
        ?>
        <div style="page-break-inside:avoid; margin-top: 40px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <div style="background: #f8fafc; padding: 12px 20px; border-bottom: 1px solid #e2e8f0;">
                <strong style="font-size:10pt; text-transform:uppercase; color:#0f172a;">Ringkasan Data Laporan</strong>
            </div>
            <div class="row g-0">
                <div class="col-md-6" style="padding: 15px 20px; border-right: 1px solid #e2e8f0;">
                    <div style="font-size:8pt; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:10px;">Distribusi Per Negara</div>
                    <table style="width:100%; font-size:9pt; border-collapse: collapse;">
                        <?php foreach ($byNegara as $neg => $cnt): ?>
                        <tr>
                            <td style="padding: 4px 0;"><?= htmlspecialchars($neg) ?></td>
                            <td style="padding: 4px 0; text-align:right; font-weight:700;"><?= $cnt ?> santri</td>
                            <td style="padding: 4px 0 4px 15px; width:40%;">
                                <div style="background:#e2e8f0; height:6px; border-radius:3px; overflow:hidden;">
                                    <div style="background:#3b82f6; height:6px; width:<?= round($cnt/$totalSantri*100) ?>%;"></div>
                                </div>
                            </td>
                            <td style="padding: 4px 0 4px 10px; font-size:7.5pt; color:#64748b; text-align: right;"><?= round($cnt/$totalSantri*100) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="col-md-6" style="padding: 15px 20px; background: #fdfdfd;">
                    <div style="font-size:8pt; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:10px;">Informasi Dokumen</div>
                    <table style="font-size:9pt; width: 100%;">
                        <tr><td style="padding:4px 0; color:#64748b;">Jenis Laporan</td><td style="text-align: right; font-weight:700;"><?= $isItas ? 'ITAS (Izin Tinggal Terbatas)' : 'PASPOR' ?></td></tr>
                        <tr><td style="padding:4px 0; color:#64748b;">Masa Berlaku</td><td style="text-align: right; font-weight:700;"><?= $isItas ? '3 Bulan sebelum expired' : '18 Bulan sebelum expired' ?></td></tr>
                        <tr><td style="padding:4px 0; color:#64748b;">Total Terfilter</td><td style="text-align: right; font-weight:700;"><?= $totalSantri ?> Santri (<?= $totalGrup ?> Grup)</td></tr>
                        <tr><td style="padding:4px 0; color:#64748b;">Status Santri</td><td style="text-align: right; font-weight:700;"><span style="color:#10b981;"><?= $totalAsli ?> Asli</span> &middot; <span style="color:#f59e0b;"><?= $totalPindahan ?> Pindahan</span></td></tr>
                        <tr><td style="padding:4px 0; color:#64748b;">Dicetak Pada</td><td style="text-align: right; font-weight:700;"><?= date('d F Y, H:i') ?> WIB</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clear floats -->
        <div style="clear: both;"></div>
    </div>

    <!-- Auto Print Script -->
    <script>
    window.onload = function() {
        setTimeout(function() { window.print(); }, 800);
    };
    </script>
</body>
</html>
