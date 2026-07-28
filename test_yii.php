<?php
require 'vendor/autoload.php';
$db = require 'config/common/di/db.php';
$dbConnection = $db[\Yiisoft\Db\Connection\ConnectionInterface::class]['__construct()']['driver'];
$dbConnection = new \Yiisoft\Db\Mysql\Connection(
    new \Yiisoft\Db\Mysql\Driver('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '')
);
$processIds = [1, 2];
$placeholders = implode(',', array_fill(0, count($processIds), '?'));
try {
    $res = $dbConnection->createCommand("SELECT * FROM jobdesk_master_process WHERE instansi_id IS NULL AND id IN ($placeholders)", $processIds)->queryAll();
    print_r($res);
} catch (Exception $e) {
    echo $e->getMessage();
}
