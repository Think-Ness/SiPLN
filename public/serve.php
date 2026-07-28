<?php
declare(strict_types=1);

if (!isset($_GET['path'])) {
    http_response_code(400);
    exit("Path required.");
}

$path = urldecode($_GET['path']);

// Basic security check: must be absolute path on windows or linux
if (!preg_match('/^([a-zA-Z]:\\\\|\/)/', $path)) {
    http_response_code(403);
    exit("Invalid path format.");
}

// SECURITY FIX: Prevent Path Traversal & Sensitive File Read
$realPath = realpath($path);
if ($realPath === false) {
    http_response_code(404);
    exit("File not found.");
}

$lowerPath = strtolower(str_replace('\\', '/', $realPath));
if (str_ends_with($lowerPath, '.php') || str_ends_with($lowerPath, '.env') || str_ends_with($lowerPath, '.ini')) {
    http_response_code(403);
    exit("Akses ditolak: File jenis ini tidak diizinkan untuk dibaca.");
}
if (str_contains($lowerPath, '/config/') || str_contains($lowerPath, '/webapp/src/')) {
    http_response_code(403);
    exit("Akses ditolak: Direktori sensitif tidak diizinkan.");
}

$path = $realPath;

if (!file_exists($path)) {
    http_response_code(404);
    exit("File not found.");
}

$mime = mime_content_type($path);
if (!$mime) {
    $mime = 'application/octet-stream';
}

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));

readfile($path);
exit;
