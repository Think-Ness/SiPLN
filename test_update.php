<?php
require 'vendor/autoload.php';

use App\Shared\FirebaseSync;

$reflection = new \ReflectionClass(FirebaseSync::class);
$methodConfig = $reflection->getMethod('getConfig');
$methodConfig->setAccessible(true);
$config = $methodConfig->invoke(null);
$projectId = $config['project_id'];

$doc = [
    'user_id' => '1',
    'nama_lengkap' => 'Test User',
    'role' => 'Admin',
    'last_seen' => time(),
    'timestamp' => date('c')
];

$methodFields = $reflection->getMethod('phpToFirestoreFields');
$methodFields->setAccessible(true);
$fields = $methodFields->invoke(null, $doc);

$methodAuth = $reflection->getMethod('authenticate');
$methodAuth->setAccessible(true);
$token = $methodAuth->invoke(null);

$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/instansi/3/online/1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['fields' => $fields]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
echo curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $response;
curl_close($ch);
