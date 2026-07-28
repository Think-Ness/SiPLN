<?php
$content = file_get_contents('d:\01. Project\04. Website\pln\webapp\src\Web\Santri\template.php');
$start = strpos($content, '<div class="modal fade" id="santriModal"');
$end = strpos($content, '<!-- ===== END MODAL TAMBAH/EDIT SANTRI ===== -->');
if ($start !== false && $end !== false) {
    file_put_contents('d:\01. Project\04. Website\pln\webapp\src\tmp_modal.html', substr($content, $start, $end - $start));
    echo "Extracted modal HTML.";
} else {
    echo "Not found.";
}
