<?php
$ch = curl_init('http://localhost:8080/api/capel/list?status=Pending');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Status: $status\nResponse: $resp";
