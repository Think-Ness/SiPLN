<?php
$phpBin = PHP_BINARY;
$workerPath = __DIR__ . '/test_worker.php';
file_put_contents($workerPath, "<?php sleep(5); file_put_contents(__DIR__ . '/test_worker_out.txt', 'Done');");

$start = microtime(true);
pclose(popen("\"$phpBin\" \"$workerPath\" > NUL 2>&1", "r"));
$time = microtime(true) - $start;

echo "Executed in $time seconds.\n";
