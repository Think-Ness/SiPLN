<?php
try {
    $wsh = new \COM("WScript.Shell");
    echo "COM is supported.";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
