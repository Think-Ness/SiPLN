<?php
$dir = new RecursiveDirectoryIterator('d:/01. Project/04. Website/pln/webapp/src');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    // Replace fetch(`/api/...`) with fetch(`<?= API_URL ?>/api/...`)
    $replaced = preg_replace('/fetch\(\s*`\/api\//', 'fetch(`<?= API_URL ?>/api/', $content);
    
    // Replace `/api/...` string literals inside JS (usually in `let url = ...`)
    $replaced = preg_replace('/`\/api\//', '`<?= API_URL ?>/api/', $replaced);
    
    if ($replaced !== $content) {
        file_put_contents($path, $replaced);
        echo "Fixed hardcoded /api/ in: $path\n";
    }
}
?>
