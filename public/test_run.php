<?php
try {
    $wsh = new \COM("WScript.Shell");
    $phpBin = PHP_BINARY;
    $workerPath = __DIR__ . '/test_worker.php';
    $wsh->Run("\"$phpBin\" \"$workerPath\"", 0, false);
    file_put_contents(__DIR__ . '/run_out.txt', "Run executed successfully.");
} catch (\Throwable $e) {
    file_put_contents(__DIR__ . '/run_out.txt', "Run Error: " . $e->getMessage());
}
