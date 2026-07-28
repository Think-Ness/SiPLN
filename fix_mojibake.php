<?php
$dir = new RecursiveDirectoryIterator('d:/01. Project/04. Website/pln/webapp/src/Web');
$ite = new RecursiveIteratorIterator($dir);
$replacements = [
    hex2bin('c3b0c5b8e2809dc28d') => '🔍',
    hex2bin('c3a2e2809ee280a6') => '...', // â€¦
    'â€”' => '—',
    'âœ…' => '✅',
    'âš ï¸ ' => '⚠️',
    'âš ï¸ ' => '⚠️',
    'ðŸ”Ž' => '🔍',
    'ðŸ” ' => '🔍',
    'â€º' => '›',
    'â€“' => '–',
    'Â«' => '«',
    'Â»' => '»',
    'â€¹' => '‹',
    'â†’' => '→',
    'âœˆï¸ ' => '✈️',
    'ðŸ’°' => '💰',
    'ðŸ“‹' => '📋',
    'ðŸ“ˆ' => '📈',
    'ðŸ”„' => '🔄',
    'â‰¤' => '≤',
    'â‰¥' => '≥'
];

foreach($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $new_content = strtr($content, $replacements);
        if ($content !== $new_content) {
            file_put_contents($file->getPathname(), $new_content);
            echo 'Fixed: ' . $file->getPathname() . "\n";
        }
    }
}
echo "Done.\n";
