<?php
$content = file_get_contents('d:\01. Project\04. Website\pln\webapp\src\Web\InaktifData\template.php');
$badgeCode = "document.getElementById('modalTitle').innerHTML = '<i class=\"bi bi-person-lines-fill me-2\"></i>Detail Data Santri <span class=\"badge bg-danger ms-2 align-middle\" style=\"font-size: 0.65rem; padding: 4px 6px; border-radius: 6px; vertical-align: text-top;\"><i class=\"bi bi-x-circle me-1\"></i>SANTRI INAKTIF</span>';\n            myModal.show();";
$content = str_replace('myModal.show();', $badgeCode, $content);
file_put_contents('d:\01. Project\04. Website\pln\webapp\src\Web\InaktifData\template.php', $content);
echo "Badge added.";
