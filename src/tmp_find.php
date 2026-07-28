<?php
$lines = file('d:\01. Project\04. Website\pln\webapp\src\Web\Santri\template.php');
for ($i = 319; $i < count($lines); $i++) {
    if (strpos($lines[$i], '<!-- ===== MODAL') !== false) {
        echo 'Next modal at: ' . $i;
        break;
    }
}
