<?php
$content = file_get_contents('d:\01. Project\04. Website\pln\webapp\src\Web\InaktifData\template.php');
$html = file_get_contents('tmp_modal_clean.html');
$js = file_get_contents('tmp_js_clean.js');

$content = str_replace('<script>', $html . "\n<script>\n" . $js . "\n", $content);

$oldAksi = <<<'HTML'
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <button class="btn btn-sm btn-light text-success border rounded-pill px-3 shadow-sm" title="Aktifkan Kembali"
                                    onclick="reaktifkan(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Aktifkan
                                </button>
                                <?php if ($isSuperAdmin): ?>
                                <button class="btn btn-sm btn-light text-danger border rounded-pill px-2 shadow-sm" title="Hapus Permanen"
                                    onclick="hapusData(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
HTML;

$newAksi = <<<'HTML'
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center gap-1">
                                <button class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-sm text-nowrap" title="Lihat Detail"
                                    onclick="lihatSantri(<?= $s['kds'] ?>)">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap" title="Aktifkan Kembali"
                                    onclick="reaktifkan(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Aktifkan
                                </button>
                                <?php if ($isSuperAdmin): ?>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm text-nowrap" title="Hapus Permanen"
                                    onclick="hapusData(<?= $s['kds'] ?>, '<?= addslashes($s['nama']) ?>')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
HTML;

$content = str_replace($oldAksi, $newAksi, $content);

file_put_contents('d:\01. Project\04. Website\pln\webapp\src\Web\InaktifData\template.php', $content);
echo "Successfully appended modal and updated buttons.";
