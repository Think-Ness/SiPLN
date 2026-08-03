<?php
$workerPath = __DIR__ . DIRECTORY_SEPARATOR . 'tmp_worker_test.php';
file_put_contents($workerPath, "<?php file_put_contents(__DIR__ . '/test_output.txt', 'Worker ran at ' . time());");

$phpBin = PHP_BINARY;
echo "PHP_BINARY: $phpBin\n";

$cmd = "start /B \"\" \"$phpBin\" \"$workerPath\" > NUL";
echo "Command: $cmd\n";

$handle = popen($cmd, "r");
if ($handle === false) {
    echo "popen failed\n";
} else {
    pclose($handle);
    echo "popen succeeded\n";
}

sleep(2);
if (file_exists(__DIR__ . '/test_output.txt')) {
    echo "File created: " . file_get_contents(__DIR__ . '/test_output.txt') . "\n";
} else {
    echo "File NOT created!\n";
}
