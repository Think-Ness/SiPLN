<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap.php';
$db = $app->get(\Yiisoft\Db\Connection\ConnectionInterface::class);

$p = $db->createCommand("SELECT * FROM anggaran_pengajuan ORDER BY id DESC LIMIT 1")->queryOne();
print_r($p);

$items = $db->createCommand("SELECT * FROM anggaran_pengajuan_items WHERE pengajuan_id = " . $p['id'])->queryAll();
print_r($items);
