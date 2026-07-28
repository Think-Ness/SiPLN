<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$pdo->query("INSERT INTO master_menus (kategori, nama_menu, url, icon, permission_key, urut, is_active) VALUES ('main', 'Cetak Berkas Individu', '/pemberkasan/cetak-berkas', 'bi bi-printer', 'menu_cetak_berkas', 31, 1)");
