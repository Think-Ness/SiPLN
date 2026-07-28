<?php
$srcDir = __DIR__ . '/src';
$publicDir = '/assets/offline';

$replacements = [
    // CSS
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' => "$publicDir/css/bootstrap.min.css",
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css' => "$publicDir/css/bootstrap-icons.css",
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css' => "$publicDir/css/sweetalert2.min.css",
    'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css' => "$publicDir/css/dataTables.bootstrap5.min.css",
    'https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css' => "$publicDir/css/buttons.bootstrap5.min.css",
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' => "$publicDir/css/bootstrap.min.css", // mapping alternative version
    
    // JS
    'https://code.jquery.com/jquery-3.7.1.min.js' => "$publicDir/js/jquery.min.js",
    'https://code.jquery.com/jquery-3.7.0.min.js' => "$publicDir/js/jquery.min.js",
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js' => "$publicDir/js/bootstrap.bundle.min.js",
    'https://cdn.jsdelivr.net/npm/sweetalert2@11' => "$publicDir/js/sweetalert2.all.min.js",
    'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js' => "$publicDir/js/jquery.dataTables.min.js",
    'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js' => "$publicDir/js/dataTables.bootstrap5.min.js",
    'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js' => "$publicDir/js/dataTables.buttons.min.js",
    'https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js' => "$publicDir/js/buttons.bootstrap5.min.js",
    'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js' => "$publicDir/js/jszip.min.js",
    'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js' => "$publicDir/js/buttons.html5.min.js",
    
    'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js' => "$publicDir/js/Sortable.min.js",
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js' => "$publicDir/js/chart.umd.min.js",
    'https://cdn.jsdelivr.net/npm/chart.js' => "$publicDir/js/chart.umd.min.js",
    'https://cdn.jsdelivr.net/npm/apexcharts' => "$publicDir/js/apexcharts.js",
    'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js' => "$publicDir/js/tesseract.min.js",
    
    // Fonts Google
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap' => "$publicDir/css/inter.css",
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap' => "$publicDir/css/inter.css",
    
    // Remove preconnects
    '<link rel="preconnect" href="https://fonts.googleapis.com">' => '',
    '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' => '',
];

function processDirectory($dir, $replacements) {
    $files = scandir($dir);
    $count = 0;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            $count += processDirectory($path, $replacements);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            $modified = false;
            
            foreach ($replacements as $search => $replace) {
                if (str_contains($content, $search)) {
                    $content = str_replace($search, $replace, $content);
                    $modified = true;
                }
            }
            
            if ($modified) {
                file_put_contents($path, $content);
                echo "Updated: $path\n";
                $count++;
            }
        }
    }
    return $count;
}

$total = processDirectory($srcDir, $replacements);
echo "\nTotal files updated: $total\n";
