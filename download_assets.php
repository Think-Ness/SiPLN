<?php
$baseDir = __DIR__ . '/public/assets/offline';

$assets = [
    'css/bootstrap.min.css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'css/bootstrap-icons.css' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
    'css/sweetalert2.min.css' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    'css/dataTables.bootstrap5.min.css' => 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
    'css/buttons.bootstrap5.min.css' => 'https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css',
    
    'js/jquery.min.js' => 'https://code.jquery.com/jquery-3.7.1.min.js',
    'js/bootstrap.bundle.min.js' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
    'js/sweetalert2.all.min.js' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'js/jquery.dataTables.min.js' => 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
    'js/dataTables.bootstrap5.min.js' => 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js',
    'js/dataTables.buttons.min.js' => 'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js',
    'js/buttons.bootstrap5.min.js' => 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js',
    'js/jszip.min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
    'js/buttons.html5.min.js' => 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js',
    
    'js/Sortable.min.js' => 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
    'js/chart.umd.min.js' => 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    'js/apexcharts.js' => 'https://cdn.jsdelivr.net/npm/apexcharts',
    'js/tesseract.min.js' => 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js',
    'js/html2canvas.min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js',
    'js/jspdf.umd.min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
    'js/firebase-app-compat.js' => 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js',
    'js/firebase-auth-compat.js' => 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js',
    'css/jquery-jvectormap.min.css' => 'https://cdn.jsdelivr.net/npm/jvectormap@2.0.4/jquery-jvectormap.min.css',
    'js/jquery-jvectormap.min.js' => 'https://cdn.jsdelivr.net/npm/jvectormap@2.0.4/jquery-jvectormap.min.js',
    'js/jquery-jvectormap-world-mill-en.js' => 'https://cdn.jsdelivr.net/npm/jvectormap@2.0.4/tests/assets/jquery-jvectormap-world-mill-en.js',
    
    // 3D Globe & Maps
    'js/three.min.js' => 'https://unpkg.com/three@0.147.0/build/three.min.js',
    'js/globe.gl.min.js' => 'https://unpkg.com/globe.gl@2.28.1/dist/globe.gl.min.js',
    'js/topojson-client.min.js' => 'https://unpkg.com/topojson-client@3',
    'json/countries-110m.json' => 'https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json',
    'img/earth-blue-marble.jpg' => 'https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg',
    'img/earth-night.jpg' => 'https://unpkg.com/three-globe/example/img/earth-night.jpg',
];

// Context for downloading Google Fonts CSS, mocking Chrome User-Agent to get WOFF2
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36\r\n"
    ]
]);

// Google Fonts - Inter
$googleFontsUrl = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap';
$cssFonts = file_get_contents($googleFontsUrl, false, $context);
if ($cssFonts) {
    preg_match_all('/url\((https:\/\/fonts\.gstatic\.com[^)]+)\)/', $cssFonts, $matches);
    foreach ($matches[1] as $url) {
        $fontName = basename(parse_url($url, PHP_URL_PATH));
        $fontPath = 'fonts/inter/' . $fontName;
        $fullFontPath = $baseDir . '/' . $fontPath;
        if (!is_dir(dirname($fullFontPath))) mkdir(dirname($fullFontPath), 0777, true);
        
        echo "Downloading Font: $fontName\n";
        file_put_contents($fullFontPath, file_get_contents($url));
        
        $cssFonts = str_replace($url, '../' . $fontPath, $cssFonts);
    }
    if (!is_dir($baseDir . '/css')) mkdir($baseDir . '/css', 0777, true);
    file_put_contents($baseDir . '/css/inter.css', $cssFonts);
    echo "Saved inter.css\n";
}

foreach ($assets as $localPath => $url) {
    $fullPath = $baseDir . '/' . $localPath;
    if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0777, true);
    
    echo "Downloading: $localPath from $url\n";
    $content = file_get_contents($url);
    if (!$content) {
        echo "FAILED: $localPath\n";
        continue;
    }

    // Rewrite URLs in CSS (like Bootstrap Icons)
    if (str_ends_with($localPath, '.css')) {
        preg_match_all('/url\([\'"]?(.*?)[\'"]?\)/', $content, $matches);
        foreach ($matches[1] as $fontUrl) {
            if (str_starts_with($fontUrl, 'data:')) continue; // Skip inline fonts
            
            // Clean up query strings like ?#iefix
            $cleanFontUrl = explode('?', $fontUrl)[0];
            $cleanFontUrl = explode('#', $cleanFontUrl)[0];
            $fontName = basename($cleanFontUrl);
            
            // Resolve absolute URL
            if (str_starts_with($fontUrl, './')) {
                $absUrl = dirname($url) . '/' . substr($fontUrl, 2);
            } else if (str_starts_with($fontUrl, '../')) {
                $absUrl = dirname(dirname($url)) . '/' . substr($fontUrl, 3);
            } else {
                $absUrl = dirname($url) . '/' . $fontUrl;
            }

            $fontLocalPath = 'fonts/' . $fontName;
            $fullFontLocalPath = $baseDir . '/' . $fontLocalPath;
            if (!is_dir(dirname($fullFontLocalPath))) mkdir(dirname($fullFontLocalPath), 0777, true);
            
            if (!file_exists($fullFontLocalPath)) {
                echo "  -> Downloading nested asset: $fontName from $absUrl\n";
                $fontData = @file_get_contents($absUrl);
                if ($fontData) file_put_contents($fullFontLocalPath, $fontData);
            }
            
            $content = str_replace($fontUrl, '../' . $fontLocalPath, $content);
        }
    }
    
    file_put_contents($fullPath, $content);
}

echo "All downloads completed!\n";
