<?php
$dir = new RecursiveDirectoryIterator('d:/01. Project/04. Website/pln/webapp/src');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $replaced = preg_replace(
        '/FROM\s+master_instansi\s+LIMIT\s+1/i',
        'FROM master_instansi WHERE kode = " . (int)($_SESSION[\'instansi_id\'] ?? 0) . " LIMIT 1',
        $content
    );
    if ($replaced !== $content && strpos($path, 'UpdateAction.php') === false && strpos($path, 'ExportAction.php') === false) {
        file_put_contents($path, $replaced);
        echo "Updated: $path\n";
    }
}
?>
