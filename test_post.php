<?php
$ch = curl_init('http://localhost:8080/api/capel/sync');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['spreadsheet_id' => '11hrUFG-sSfEuuKyOdJFiHXTEbPNpqiVXWTvRQahNdlU', 'sheet_range' => 'Form Responses 1']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
$resp = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Status: $status\nResponse: $resp";
