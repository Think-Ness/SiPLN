<?php
declare(strict_types=1);

namespace App\Web\ImportExcel;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class ParseAction
{
    public function __invoke(
        ServerRequestInterface $request
    ): ResponseInterface {
        $files = $request->getUploadedFiles();
        $parsedBody = (array)$request->getParsedBody();

        $tmpFile = null;
        $fileName = '';
        $sheetIndex = isset($parsedBody['sheet_index']) ? (int)$parsedBody['sheet_index'] : 0;

        if (!empty($files['excel_file']) && $files['excel_file']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $files['excel_file'];
            $ext = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                return JsonResponse::create(['success' => false, 'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv'], 422);
            }

            // Save to temp
            $tmpDir = sys_get_temp_dir();
            $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'import_' . uniqid() . '.' . $ext;
            $uploadedFile->moveTo($tmpFile);
            $fileName = $uploadedFile->getClientFilename();

            $_SESSION['import_tmp_file'] = $tmpFile;
            $_SESSION['import_file_name'] = $fileName;
        } else {
            // Check if we are loading a different sheet from an already uploaded file
            if (isset($_SESSION['import_tmp_file']) && file_exists($_SESSION['import_tmp_file'])) {
                $tmpFile = $_SESSION['import_tmp_file'];
                $fileName = $_SESSION['import_file_name'] ?? 'File Excel';
            } else {
                return JsonResponse::create(['success' => false, 'message' => 'Tidak ada file yang diterima.'], 422);
            }
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheetNames = $spreadsheet->getSheetNames();
            
            // Validate sheet index
            if ($sheetIndex < 0 || $sheetIndex >= count($sheetNames)) {
                $sheetIndex = 0;
            }
            
            // Save selected sheet index to session for execute step
            $_SESSION['import_sheet_index'] = $sheetIndex;

            $sheet = $spreadsheet->getSheet($sheetIndex);
            $data = $sheet->toArray(null, true, true, true);

            if (count($data) < 2) {
                return JsonResponse::create(['success' => false, 'message' => 'File kosong atau hanya berisi header pada sheet ini.'], 422);
            }

            // First row = headers
            $headerRow = array_shift($data);
            $headers = [];
            foreach ($headerRow as $colLetter => $val) {
                $val = trim((string)($val ?? ''));
                if ($val !== '') {
                    $headers[] = ['col' => $colLetter, 'name' => $val];
                }
            }

            // Collect preview rows (max 200)
            $rows = [];
            $rowIndex = 0;
            foreach ($data as $row) {
                if ($rowIndex >= 200) break;
                $rowData = [];
                foreach ($headers as $h) {
                    $cellVal = $row[$h['col']] ?? '';
                    $rowData[] = trim((string)($cellVal ?? ''));
                }
                // Skip completely empty rows
                $hasData = false;
                foreach ($rowData as $v) { if ($v !== '') { $hasData = true; break; } }
                if ($hasData) {
                    $rows[] = $rowData;
                    $rowIndex++;
                }
            }

            $_SESSION['import_headers'] = $headers;

            return JsonResponse::create([
                'success' => true,
                'headers' => array_map(fn($h) => $h['name'], $headers),
                'rows' => $rows,
                'totalRows' => count($rows),
                'fileName' => $fileName,
                'sheetNames' => $sheetNames,
                'currentSheetIndex' => $sheetIndex
            ]);

        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()], 500);
        }
    }
}
