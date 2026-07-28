<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/webapp/public/api/firebase/presence");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $res . "\n";
curl_close($ch);
