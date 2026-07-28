<?php
$doc = ['path_file' => '/serve.php?path=D%3A%5C01.+Project%5C04.+Website%5Cpln%5Cberkas%2Fpaspor%2FScan_ID_Paspor_Master_Hasbiyallah_Wangphon_MAS2601_1781411529.pdf'];

$path = $doc['path_file'];
if (str_starts_with($path, '/serve.php?path=')) {
    $path = urldecode(substr($path, 16));
} else if (str_starts_with($path, '/') && !file_exists($path)) {
    $path = dirname(__DIR__, 4) . '/public' . $path;
}

if (!$path || !file_exists($path) || !is_file($path)) {
    echo "Document not found.\n";
} else {
    echo "Found: " . $path . "\n";
}
