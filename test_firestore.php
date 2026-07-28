<?php
require 'vendor/autoload.php';

use App\Shared\FirebaseSync;

// Bootstrapping the app configuration if needed
$config = require 'config/common/params.php';

// Try to read from Firestore directly
function getFirestoreOnline() {
    $reflection = new \ReflectionClass(FirebaseSync::class);
    $methodAuth = $reflection->getMethod('authenticate');
    $methodAuth->setAccessible(true);
    $token = $methodAuth->invoke(null);
    if (!$token) {
        echo "Failed to authenticate\n";
        return;
    }
    
    $projectId = $config['yiisoft/yii-web']['app']['firebase']['project_id'] ?? 'none';
    if ($projectId === 'none') {
        // use reflection to get config from FirebaseSync
        $reflection = new \ReflectionClass(FirebaseSync::class);
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);
        $config = $method->invoke(null);
        $projectId = $config['project_id'];
    }

    $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/instansi/3/online";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    echo $response;
    curl_close($ch);
}

getFirestoreOnline();
