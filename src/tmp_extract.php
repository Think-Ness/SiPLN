<?php
$content = file_get_contents('d:\01. Project\04. Website\pln\webapp\src\Web\Santri\template.php');
$start = strpos($content, 'function editSantri(kds)');
$end = strpos($content, 'function collectForm()', $start);
echo substr($content, $start, $end - $start);
