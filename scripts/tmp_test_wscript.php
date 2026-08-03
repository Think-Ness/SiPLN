<?php
$workerPath = __DIR__ . DIRECTORY_SEPARATOR . 'tmp_worker_test2.php';
file_put_contents($workerPath, "<?php file_put_contents(__DIR__ . '/test_output2.txt', 'Worker ran via COM at ' . time());");

$phpBin = PHP_BINARY;

try {
    $wsh = new \COM("WScript.Shell");
    // Run command invisibly (0) and asynchronously (false)
    $cmd = "\"$phpBin\" \"$workerPath\"";
    $wsh->Run($cmd, 0, false);
    echo "COM Run executed.\n";
} catch (\Throwable $e) {
    echo "COM Error: " . $e->getMessage() . "\n";
}

sleep(2);
if (file_exists(__DIR__ . '/test_output2.txt')) {
    echo "File created: " . file_get_contents(__DIR__ . '/test_output2.txt') . "\n";
} else {
    echo "File NOT created!\n";
}
