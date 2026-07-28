<?php
$files = [
    'src/Web/MasterPrint/ExportAction.php',
    'src/Web/Pemberkasan/CetakBerkasAction.php',
    'src/Web/Pemberkasan/MergeAction.php',
    'src/Web/Pemberkasan/UploadAction.php',
    'src/Web/Santri/AutoUploadItasAction.php',
    'src/Web/Santri/StoreAction.php',
    'src/Web/Santri/UpdateAction.php',
    'src/Web/Santri/UploadDocAction.php',
];

$dir = 'd:/01. Project/04. Website/pln/webapp/';

foreach ($files as $file) {
    $path = $dir . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $replaced = preg_replace(
            '/SELECT\s+path_folder\s+FROM\s+master_instansi\s+LIMIT\s+1/i',
            'SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION[\'instansi_id\'] ?? 0) . " LIMIT 1',
            $content
        );
        
        $replaced = preg_replace(
            '/SELECT\s+kode,\s*path_folder\s+FROM\s+master_instansi\s+LIMIT\s+1/i',
            'SELECT kode, path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION[\'instansi_id\'] ?? 0) . " LIMIT 1',
            $replaced
        );
        
        if ($replaced !== $content) {
            file_put_contents($path, $replaced);
            echo "Updated: $file\n";
        }
    }
}
?>
