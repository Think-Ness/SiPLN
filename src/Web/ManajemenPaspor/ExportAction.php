<?php
declare(strict_types=1);

namespace App\Web\ManajemenPaspor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use HttpSoft\Message\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class ExportAction
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        $isSuperAdmin = $role === 'super_admin';

        $params = $request->getQueryParams();
        $type = $params['type'] ?? 'status'; // 'status' or 'log'
        $format = $params['format'] ?? 'xlsx'; // 'xlsx' or 'print'

        $db = $this->db;
        $where = "WHERE s.status_santri = 'Aktif'";
        $bindParams = [];

        if (!$isSuperAdmin && (!empty($myKepengurusan) || !empty($myPondok))) {
            $conditions = [];
            if (!empty($myKepengurusan)) {
                $conditions[] = "s.kepengurusan = :my_kepengurusan";
                $bindParams[':my_kepengurusan'] = $myKepengurusan;
            }
            if (!empty($myPondok)) {
                $conditions[] = "s.pondok = :my_pondok";
                $bindParams[':my_pondok'] = $myPondok;
            }
            if (!empty($conditions)) {
                $where .= " AND (" . implode(" OR ", $conditions) . ") ";
            }
        }

        if ($type === 'log') {
            // Export log
            $sql = "SELECT l.tanggal_aksi, s.nama, p.no_paspor, 
                           CASE l.tipe WHEN 'keluar' THEN 'KELUAR' ELSE 'KEMBALI' END as tipe,
                           l.alasan, l.tanggal_rencana_kembali, l.catatan, l.dicatat_oleh
                    FROM log_paspor l
                    JOIN master_santri s ON l.kds = s.kds
                    JOIN mtb_paspor p ON l.paspor_id = p.id
                    $where
                    ORDER BY l.created_at DESC";
            $rows = $db->createCommand($sql, $bindParams)->queryAll();

            $headers = ['Tanggal', 'Nama Santri', 'No Paspor', 'Tipe', 'Alasan', 'Rencana Kembali', 'Catatan', 'Dicatat Oleh'];
            $filename = 'Log_Paspor_' . date('Y-m-d');
            $title = "Riwayat Log Paspor";
        } else {
            // Export status
            $sql = "SELECT s.nama, s.kelas, s.rayon, s.negara,
                           p.no_paspor, p.exp_paspor,
                           COALESCE(p.status_lokasi, 'di_kantor') as status_lokasi,
                           l.alasan as last_alasan, l.tanggal_aksi as last_tanggal_keluar,
                           l.tanggal_rencana_kembali
                    FROM master_santri s
                    JOIN mtb_paspor p ON s.kds = p.kds AND p.aktif = 1
                    LEFT JOIN (
                        SELECT lp.* FROM log_paspor lp
                        INNER JOIN (SELECT paspor_id, MAX(id) as max_id FROM log_paspor WHERE tipe='keluar' GROUP BY paspor_id) latest
                        ON lp.id = latest.max_id
                    ) l ON p.id = l.paspor_id AND p.status_lokasi = 'keluar'
                    $where
                    ORDER BY s.nama ASC";
            $rows = $db->createCommand($sql, $bindParams)->queryAll();

            // Ubah format status agar lebih rapi untuk laporan
            foreach ($rows as &$row) {
                if ($row['status_lokasi'] === 'di_kantor') {
                    $row['status_lokasi'] = 'Di Kantor';
                } elseif ($row['status_lokasi'] === 'keluar') {
                    // Check if terlambat
                    $isTerlambat = false;
                    if (!empty($row['tanggal_rencana_kembali'])) {
                        $rencana = strtotime($row['tanggal_rencana_kembali']);
                        $now = strtotime(date('Y-m-d'));
                        if ($now > $rencana) {
                            $isTerlambat = true;
                        }
                    }
                    $row['status_lokasi'] = $isTerlambat ? 'Terlambat Kembali' : 'Sedang Keluar';
                }
            }

            $headers = ['Nama Santri', 'Kelas', 'Rayon', 'Negara', 'No Paspor', 'Exp Paspor', 'Status', 'Alasan Keluar', 'Tanggal Keluar', 'Rencana Kembali'];
            $filename = 'Status_Paspor_' . date('Y-m-d');
            $title = "Laporan Status Paspor";
        }

        if ($format === 'print') {
            $html = '<!DOCTYPE html><html><head><title>'.$title.'</title>';
            $html .= '<style>
                        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap");
                        body { font-family: "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 11px; margin: 20px; color: #333; }
                        h2 { text-align: center; margin-bottom: 15px; font-weight: 700; color: #111; font-size: 18px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #e0e0e0; padding: 6px 8px; text-align: left; vertical-align: middle; }
                        th { background-color: #f8f9fa; font-weight: 600; color: #444; border-bottom: 2px solid #d0d0d0; white-space: nowrap; }
                        tr:nth-child(even) { background-color: #fcfcfc; }
                        .text-center { text-align: center; }
                        .badge { display: inline-block; padding: 3px 6px; font-size: 10px; font-weight: 600; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
                        .bg-success { background: #d1e7dd; color: #0f5132; }
                        .bg-warning { background: #fff3cd; color: #664d03; }
                        .bg-danger { background: #f8d7da; color: #842029; }
                        .bg-secondary { background: #e2e3e5; color: #41464b; }
                        @media print {
                            @page { margin: 10mm; size: landscape; }
                            body { margin: 0; }
                            button { display: none !important; }
                            th, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        }
                      </style></head><body>';
            
            $html .= '<h2>' . htmlspecialchars($title) . '<br><small style="font-size:11px; font-weight:normal; color:#666;">Per Tanggal: ' . date('d-M-Y H:i') . '</small></h2>';
            $html .= '<div style="margin-bottom: 15px; text-align: right;"><button onclick="window.print()" style="padding:6px 14px; cursor:pointer; background:#0d6efd; color:#fff; border:none; border-radius:4px; font-size:12px; font-weight:600; font-family:inherit;">Cetak Laporan</button></div>';
            
            $html .= '<table><thead><tr>';
            foreach ($headers as $h) {
                $html .= '<th>' . htmlspecialchars($h) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            
            if (empty($rows)) {
                $html .= '<tr><td colspan="' . count($headers) . '" class="text-center">Tidak ada data</td></tr>';
            } else {
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach (array_values($row) as $v) {
                        $val = (string)($v ?? '-');
                        
                        // Format Tanggal (YYYY-MM-DD -> DD-MMM-YYYY)
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                            $val = date('d-M-Y', strtotime($val));
                        } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) {
                            $val = date('d-M-Y H:i', strtotime($val));
                        }
                        
                        // Berikan Badge modern pada Status atau Tipe
                        if ($val === 'Di Kantor' || $val === 'KEMBALI') {
                            $val = '<span class="badge bg-success">' . htmlspecialchars($val) . '</span>';
                        } elseif ($val === 'Sedang Keluar' || $val === 'KELUAR') {
                            $val = '<span class="badge bg-warning">' . htmlspecialchars($val) . '</span>';
                        } elseif ($val === 'Terlambat Kembali') {
                            $val = '<span class="badge bg-danger">' . htmlspecialchars($val) . '</span>';
                        } else {
                            $val = htmlspecialchars($val);
                        }
                        
                        $html .= '<td>' . $val . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
            
            $html .= '</tbody></table>';
            $html .= '<script>window.onload = function() { window.print(); }</script>';
            $html .= '</body></html>';

            $response = new Response(200);
            $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            $response->getBody()->write($html);
            return $response;
        }

        // Export Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));

        // Judul Laporan
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1');
        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '2');
        $sheet->setCellValue('A2', 'Tanggal Cetak: ' . date('d F Y H:i:s'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel
        $colIndex = 1;
        foreach ($headers as $h) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . '4', $h);
            $colIndex++;
        }

        // Style Header Tabel
        $headerRange = 'A4:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '4';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Isi Data
        $rowIndex = 5;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach (array_values($row) as $v) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValueExplicit($colLetter . $rowIndex, (string)($v ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colIndex++;
            }
            $rowIndex++;
        }

        // Style Body Tabel
        if ($rowIndex > 5) {
            $bodyRange = 'A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . ($rowIndex - 1);
            $sheet->getStyle($bodyRange)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Auto Size Columns
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Output Excel
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_clean();

        $response = new Response(200);
        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                             ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"')
                             ->withHeader('Cache-Control', 'max-age=0');
        $response->getBody()->write($excelData);
        return $response;
    }
}

