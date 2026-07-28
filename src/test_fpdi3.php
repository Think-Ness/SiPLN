<?php
require __DIR__ . '/../vendor/autoload.php';
use setasign\Fpdi\Fpdi;

$pdfPath = 'd:\01. Project\04. Website\pln\berkas\berkas\Kor_Vy_Solahuddin_KOR26\Surat_Beranak_Kor_Vy_Solahuddin_KOR26_1781495374.pdf';

$pdf = new Fpdi();
$pdf->SetAutoPageBreak(false);

$tempPdf = tempnam(sys_get_temp_dir(), 'pdf_fix_');
$cmd = 'pdftk ' . escapeshellarg($pdfPath) . ' output ' . escapeshellarg($tempPdf) . ' uncompress 2>&1';
exec($cmd);

try {
    $pageCount = $pdf->setSourceFile($tempPdf);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }
} catch (\Exception $e) {
    echo "FPDI Error: " . $e->getMessage() . "\n";
}

$pdf->Output('F', 'd:\01. Project\04. Website\pln\webapp\src\test_output_fallback.pdf');
echo "Saved to test_output_fallback.pdf\n";
