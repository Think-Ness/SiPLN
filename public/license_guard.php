<?php
// --- Hardware/License Guard (Dynamic Revocation) ---
if (!function_exists('redirectActivate')) {
    function redirectActivate() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        header("Location: $protocol://$host$baseDir/activate.php");
        exit;
    }
}

$guardRoot = dirname(__DIR__);
$licensePath = $guardRoot . '/config/license.json';

if (!file_exists($licensePath)) {
    redirectActivate();
}

$licenseData = json_decode(file_get_contents($licensePath), true);
if (!$licenseData) {
    redirectActivate();
}

// Get HWID
$output = @shell_exec('wmic csproduct get uuid');
if ($output) {
    $lines = explode("\n", trim($output));
    $hwid = isset($lines[1]) ? trim($lines[1]) : md5(php_uname('n') . php_uname('a'));
} else {
    $hwid = md5(php_uname('n') . php_uname('a'));
}

$expectedSig = hash_hmac('sha256', $licenseData['code'] . $hwid, 'SiPLN_Secret_Salt_2026');
if ($licenseData['signature'] !== $expectedSig || $licenseData['device_id'] !== $hwid) {
    // Copied to another PC or tampered!
    @unlink($licensePath);
    redirectActivate();
}

// Dynamic Revocation Check (Every 5 minutes)
$lastCheckFile = $guardRoot . '/config/last_check.txt';
$lastCheck = file_exists($lastCheckFile) ? (int)@file_get_contents($lastCheckFile) : 0;

if (time() - $lastCheck > 300) {
    $url = "https://firestore.googleapis.com/v1/projects/database-luar-negeri/databases/(default)/documents/aktivasi/" . urlencode($licenseData['code']);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Quick timeout to not block UI completely if offline
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $fields = $data['fields'] ?? [];
        $status = isset($fields['status']['booleanValue']) ? $fields['status']['booleanValue'] : true;
        
        if (!$status) {
            // Revoked by admin!
            @unlink($licensePath);
            redirectActivate();
        }
    } else if ($httpCode === 404) {
        // Document deleted by admin!
        @unlink($licensePath);
        redirectActivate();
    }
    
    @file_put_contents($lastCheckFile, time());
}

