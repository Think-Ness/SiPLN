<?php
try {
    $wsh = new \COM("WScript.Shell");
    file_put_contents(__DIR__ . '/com_out.txt', "COM is supported.");
} catch (\Throwable $e) {
    file_put_contents(__DIR__ . '/com_out.txt', "Error: " . $e->getMessage());
}
