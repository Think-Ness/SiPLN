<?php
if (file_exists(__DIR__ . '/test_worker_out.txt')) {
    echo "Worker finished!";
} else {
    echo "Worker NOT finished!";
}
