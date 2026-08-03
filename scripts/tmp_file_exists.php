<?php
$path = urldecode('D%3A%5C01.+Project%5C04.+Website%5Cpln%5Cberkas%2Fpaspor%2FScan_ID_Paspor_Master_Hasbiyallah_Wangphon_MAS2601_1781411529.pdf');
echo $path . "\n";
var_dump(file_exists($path));
var_dump(is_file($path));
