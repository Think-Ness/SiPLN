<?php
header('Content-Type: text/plain');
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'none') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'none') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'none') . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'none') . "\n";
