<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'd:/01. Project/04. Website/pln/Tbl_Database_LN.xlsx';

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);
    
    $headers = array_shift($data);
    $firstRow = array_shift($data);
    
    echo "Headers:\n";
    print_r($headers);
    echo "\nFirst Row:\n";
    print_r($firstRow);
    echo "\nTotal Rows (excluding header): " . count($data) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
