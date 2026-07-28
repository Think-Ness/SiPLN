<?php
$phpBin = PHP_BINARY;
$workerPath = __DIR__ . '/test_worker.php';
file_put_contents($workerPath, "<?php sleep(5); file_put_contents(__DIR__ . '/test_worker_out2.txt', 'Done');");

$start = microtime(true);
pclose(popen("start /B \"\" \"$phpBin\" \"$workerPath\" > NUL", "r"));
$time = microtime(true) - $start;

echo "Executed in $time seconds.\n";
