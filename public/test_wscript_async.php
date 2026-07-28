<?php
$phpBin = PHP_BINARY;
$workerPath = __DIR__ . '/test_worker.php';

$start = microtime(true);
$wsh = new \COM("WScript.Shell");
$wsh->Run("\"$phpBin\" \"$workerPath\"", 0, false);
$time = microtime(true) - $start;

echo "WScript executed in $time seconds.\n";
