<?php
if (class_exists('IntlDateFormatter')) {
    $fmtMonth = new \IntlDateFormatter('id_ID@calendar=islamic', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'Asia/Jakarta', \IntlDateFormatter::TRADITIONAL, 'MMMM');
    $fmtYear = new \IntlDateFormatter('id_ID@calendar=islamic', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'Asia/Jakarta', \IntlDateFormatter::TRADITIONAL, 'yyyy');
    echo $fmtMonth->format(time()) . " " . $fmtYear->format(time());
} else {
    echo "Intl not found";
}
