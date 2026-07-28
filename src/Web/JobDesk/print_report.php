<?php
/**
 * @var array $cases
 * @var string $statusFilter
 * @var string $processFilter
 * @var array $processes
 * @var string $orientation
 * @var array|null $instansi
 * @var array $masterSteps
 * @var array $overallStats
 * @var array $bottlenecks
 */

$kopSuratPath = '';
if (!empty($instansi['kop_surat']) && file_exists(__DIR__ . '/../../../../public/uploads/instansi/' . $instansi['kop_surat'])) {
    $kopSuratPath = '/uploads/instansi/' . $instansi['kop_surat'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif Progres Job Desk</title>
    <!-- Use Bootstrap CSS for quick grid and table, but customize it for print -->
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

        /* Kop Surat */
        .kop-surat img { max-width: 100%; max-height: 100px; }
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
        .exec-card.aktif { border-left: 5px solid #3b82f6; }
        .exec-card.selesai { border-left: 5px solid #10b981; }
        .exec-card.kendala { border-left: 5px solid #ef4444; }
        
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

        /* Matrix Specifics */
        .cell-check { color: #10b981; font-weight: bold; font-size: 12pt; text-align: center; }
        .cell-wait { color: #f59e0b; font-weight: bold; font-size: 11pt; text-align: center; }
        .cell-block { color: #ef4444; font-weight: bold; font-size: 12pt; text-align: center; }
        .cell-empty { color: #cbd5e1; font-weight: bold; text-align: center; }
        
        .progress-pct { font-weight: bold; text-align: right; }
        .section-title { font-size: 12pt; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px;}

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
        
        <!-- KOP SURAT -->
        <div class="kop-surat no-print">
            <?php if (!empty($kopSuratPath)): ?>
                <img src="<?= $kopSuratPath ?>" alt="Kop Surat Instansi">
            <?php else: ?>
                <h2 style="font-weight: 800; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($instansi['nama_instansi'] ?? 'YAYASAN PENDIDIKAN ISLAM') ?></h2>
                <p style="margin:0; font-size:10pt; font-family: 'Times New Roman', serif;"><?= htmlspecialchars($instansi['alamat'] ?? 'Alamat belum diatur.') ?></p>
            <?php endif; ?>
        </div>

        <div class="report-title no-print">
            EXECUTIVE SUMMARY: PROGRES JOB DESK<br>
            <span style="font-size: 10pt; font-weight: normal; color: #64748b; letter-spacing: 0;">
                Diperbarui pada: <?= date('d F Y') ?>
            </span>
        </div>

        <!-- EXECUTIVE SUMMARY BLOCKS -->
        <div class="row g-3 mb-4 no-print">
            <div class="col">
                <div class="exec-card aktif">
                    <div class="val" style="color: #3b82f6;"><?= $overallStats['aktif'] ?></div>
                    <div class="lbl">Kasus Aktif</div>
                </div>
            </div>
            <div class="col">
                <div class="exec-card selesai">
                    <div class="val" style="color: #10b981;"><?= $overallStats['selesai'] ?></div>
                    <div class="lbl">Selesai</div>
                </div>
            </div>
            <div class="col">
                <div class="exec-card kendala">
                    <div class="val" style="color: #ef4444;"><?= $overallStats['blocked'] ?></div>
                    <div class="lbl">Terkendala (Blocked)</div>
                </div>
            </div>
        </div>

        <?php if (empty($cases)): ?>
            <div class="text-center my-5 border p-4" style="background: #f8fafc; border-radius: 8px;">
                <em>Tidak ada data kasus untuk ditampilkan berdasarkan filter saat ini.</em>
            </div>
        <?php else: ?>
            
            <?php
            // Group cases by process
            $groupedCases = [];
            foreach ($cases as $c) {
                $pid = $c['process_id'];
                if (!isset($groupedCases[$pid])) $groupedCases[$pid] = [];
                $groupedCases[$pid][] = $c;
            }
            ?>

            <?php foreach ($groupedCases as $pid => $processCases): ?>
                <?php 
                $processName = $processCases[0]['nama_proses'];
                $mSteps = $masterSteps[$pid] ?? [];
                $bStats = $bottlenecks[$pid] ?? [];
                ?>
                
                <div style="page-break-inside: avoid; margin-bottom: 40px;">
                    <div class="section-title text-primary" style="border-color: #3b82f6;">
                        <span style="font-size: 14pt;">&#9632;</span> <?= htmlspecialchars($processName) ?>
                    </div>

                    <!-- BOTTLENECK ANALYSIS & ACTION REQUIRED -->
                    <div class="row mb-3 g-3">
                        <div class="col-md-5">
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; height: 100%;">
                                <div style="font-weight: 700; font-size: 9pt; text-transform: uppercase; margin-bottom: 8px; color: #475569;">Analisis Hambatan (Bottleneck)</div>
                                <table class="table-modern" style="margin-bottom: 0;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Tahapan</th>
                                            <th style="width: 110px; text-align: center;">Telah Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalProcessCases = count($processCases);
                                        foreach ($mSteps as $ms): 
                                            $u = $ms['urut'];
                                            $count = $bStats[$u]['count'] ?? 0;
                                        ?>
                                        <tr>
                                            <td>P<?= $u ?>: <?= htmlspecialchars($ms['step_nama']) ?></td>
                                            <td style="text-align: center; font-weight: bold; font-size: 10.5pt; color: #0f172a;">
                                                <?php if ($count === 0): ?>
                                                    <span style="color: #94a3b8; font-size: 10.5pt;">-</span>
                                                <?php else: ?>
                                                    <?= $count ?> <span style="font-size: 8pt; color: #64748b; font-weight: normal;">/ <?= $totalProcessCases ?> org</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; padding: 10px; height: 100%; display: flex; flex-direction: column;">
                                <div style="font-weight: 700; font-size: 9pt; text-transform: uppercase; margin-bottom: 8px; color: #e11d48;">Perlu Perhatian (Terkendala)</div>
                                <div style="flex-grow: 1; font-size: 9pt; color: #4c0519;">
                                    <?php
                                        $blockedCases = array_filter($processCases, function($c) { return $c['status'] === 'batal' || in_array('blocked', $c['matrix']); });
                                        if (empty($blockedCases)):
                                    ?>
                                        <div class="d-flex align-items-center justify-content-center h-100" style="opacity: 0.6;">
                                            <em>Semua proses berjalan lancar.</em>
                                        </div>
                                    <?php else: ?>
                                        <ul style="padding-left: 15px; margin: 0;">
                                            <?php foreach (array_slice($blockedCases, 0, 5) as $bc): // Limit up to 5 cases ?>
                                                <?php
                                                    $blockedStepName = '';
                                                    foreach ($mSteps as $ms) {
                                                        if ($ms['urut'] == $bc['current_step_urut']) {
                                                            $blockedStepName = $ms['step_nama'];
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <li style="margin-bottom: 6px; font-size: 8pt; line-height: 1.3;">
                                                    <strong style="font-size: 8.5pt;"><?= htmlspecialchars($bc['nama']) ?></strong> 
                                                    <span style="color: #9f1239;">(<?= htmlspecialchars($bc['kds']) ?>)</span><br>
                                                    <span style="color: #e11d48;">P<?= $bc['current_step_urut'] ?>: <?= htmlspecialchars($blockedStepName) ?></span> &mdash; 
                                                    <?php if (!empty($bc['notes'])): ?>
                                                        <em style="color: #4c0519;">"<?= htmlspecialchars($bc['notes'][0]['isi']) ?>"</em>
                                                    <?php else: ?>
                                                        <em style="color: #94a3b8;">(Tidak ada catatan staf)</em>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if (count($blockedCases) > 5): ?>
                                            <div style="margin-top: 5px; font-size: 8pt; font-style: italic;">...dan <?= count($blockedCases) - 5 ?> antrean lainnya.</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- EXPLANATION FOR MATRIX (Moved to bottom of right col) -->
                                <div style="margin-top: 15px; padding: 8px 10px; border-radius: 4px; border-left: 3px solid #f59e0b; font-size: 8.5pt; color: #475569; background: #fffbeb;">
                                    <strong>Legenda Matriks:</strong>&nbsp;&nbsp;
                                    <span class="cell-check">&#10004;</span> Selesai &nbsp;|&nbsp;
                                    <span class="cell-wait">&#10710;</span> Proses Aktif &nbsp;|&nbsp;
                                    <span class="cell-block">&#10006;</span> Terkendala &nbsp;|&nbsp;
                                    <span class="cell-empty">-</span> Belum
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MATRIX TABLE -->
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="width: 30px;">No</th>
                                <th style="text-align: left; width: 180px;">Nama Santri</th>
                                <?php foreach ($mSteps as $ms): ?>
                                    <th style="width: 40px;" title="<?= htmlspecialchars($ms['step_nama']) ?>">P<?= $ms['urut'] ?></th>
                                <?php endforeach; ?>
                                <th style="width: 70px;">Progres</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processCases as $index => $case): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $index + 1 ?></td>
                                    <td style="font-weight: 600; font-size: 9pt;"><?= htmlspecialchars($case['nama']) ?></td>
                                    
                                    <?php foreach ($mSteps as $ms): 
                                        $u = $ms['urut'];
                                        $status = $case['matrix']["P{$u}"] ?? null;
                                        
                                        $cellHtml = '<td class="cell-empty">-</td>';
                                        if ($status === 'done') $cellHtml = '<td class="cell-check">&#10004;</td>';
                                        elseif ($status === 'process') $cellHtml = '<td class="cell-wait">&#10710;</td>';
                                        elseif ($status === 'blocked') $cellHtml = '<td class="cell-block">&#10006;</td>';
                                        
                                        echo $cellHtml;
                                    endforeach; ?>
                                    
                                    <td class="progress-pct"><?= $case['progress_pct'] ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <!-- Signature Section -->
        <div class="signature-box">
            <p style="margin-bottom: 60px;">
                ..............................., <?= date('d F Y') ?><br>
                Mengetahui,
            </p>
            <p style="font-weight: bold; text-decoration: underline; margin-bottom: 0;">
                ( ......................................... )
            </p>
            <p style="margin-top: 0;">
                Ketua Kantor / Pimpinan
            </p>
        </div>
        
        <!-- Clear floats -->
        <div style="clear: both;"></div>

    </div>

</body>
</html>
