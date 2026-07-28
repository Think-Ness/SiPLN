<?php
require __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

$pdfPath = 'd:\01. Project\04. Website\pln\berkas\berkas\Kor_Vy_Solahuddin_KOR26\Surat_Beranak_Kor_Vy_Solahuddin_KOR26_1781495374.pdf';

try {
    $pdf = new Fpdi();
    $pdf->SetAutoPageBreak(false);
    
    $pageCount = $pdf->setSourceFile($pdfPath);
    echo "Success! Page count: $pageCount\n";
} catch (\Exception $e) {
    echo "FPDI Error: " . $e->getMessage() . "\n";
    
    $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_fix_');
    $cmd = 'pdftk ' . escapeshellarg($pdfPath) . ' output ' . escapeshellarg($tempPdf) . ' uncompress 2>&1';
    exec($cmd, $output, $returnVar);
    
    echo "pdftk return var: $returnVar\n";
    echo "pdftk output: " . implode("\n", $output) . "\n";
    
    try {
        $pageCount = $pdf->setSourceFile($tempPdf);
        echo "pdftk Success! Page count: $pageCount\n";
    } catch (\Exception $e2) {
        echo "pdftk Fallback Error: " . $e2->getMessage() . "\n";
    }
}
