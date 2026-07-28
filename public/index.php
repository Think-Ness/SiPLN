<?php

declare(strict_types=1);

use App\Environment;
use Psr\Log\LogLevel;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\Log\Logger;
use Yiisoft\Log\StreamTarget;
use Yiisoft\Yii\Runner\Http\HttpApplicationRunner;

$root = dirname(__DIR__);

// --- Installation Guard ---
if (!file_exists($root . '/config/installed.lock')) {
    header('Location: install.php');
    exit;
}

// --- Hardware/License Guard (Dynamic Revocation) ---
session_start();
$licensePath = $root . '/config/license.json';
if (!file_exists($licensePath)) {
    header('Location: activate.php');
    exit;
}

$licenseData = json_decode(file_get_contents($licensePath), true);
if (!$licenseData) {
    header('Location: activate.php');
    exit;
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
    header('Location: activate.php');
    exit;
}

// Dynamic Revocation Check (Every 30 detik)
$lastCheck = $_SESSION['last_license_check'] ?? 0;
if (time() - $lastCheck > 30) {
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
            session_destroy();
            header('Location: activate.php');
            exit;
        }
    } else if ($httpCode === 404) {
        // Document deleted by admin!
        @unlink($licensePath);
        session_destroy();
        header('Location: activate.php');
        exit;
    }
    
    $_SESSION['last_license_check'] = time();
}
// ----------------------------------------------------

// --- Ensure Runtime Directory Exists ---
$runtimeDir = $root . '/runtime';
if (!is_dir($runtimeDir)) {
    @mkdir($runtimeDir, 0777, true);
}

require_once $root . '/src/bootstrap.php';

if (Environment::appC3()) {
    $c3 = $root . '/c3.php';
    if (file_exists($c3)) {
        require_once $c3;
    }
}

/**
 * @psalm-var string $_SERVER['REQUEST_URI']
 */
// PHP built-in server routing.
if (PHP_SAPI === 'cli-server') {
    // Serve static files as is.
    /** @var string $path */
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file(__DIR__ . $path)) {
        return false;
    }

    // Explicitly set for URLs with dot.
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// --- Subfolder Routing Fix ---
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$assetUrl = '';
$apiUrl = '';
if ($scriptName !== '') {
    $basePath = str_replace('\\', '/', dirname($scriptName));
    if ($basePath === '/' || $basePath === '\\') $basePath = '';
    
    $assetUrl = rtrim($basePath, '/');
    $apiUrl = $scriptName;
    
    define('ASSET_URL', $assetUrl);
    define('API_URL', $apiUrl);
    
    if ($basePath !== '') {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Coba strip SCRIPT_NAME utuh dulu (contoh: /WEBAPP/public/index.php/login)
        if (strpos($uri, $scriptName) === 0) {
            $newUri = substr($uri, strlen($scriptName));
        } 
        // Jika tidak ada index.php, strip base path-nya (contoh: /WEBAPP/public/login)
        elseif (strpos($uri, $basePath) === 0) {
            $newUri = substr($uri, strlen($basePath));
        } else {
            $newUri = $uri;
        }

        if ($newUri === '' || $newUri[0] !== '/') {
            $newUri = '/' . ltrim($newUri, '/');
        }
        $_SERVER['REQUEST_URI'] = $newUri;
    }
}

// Run HTTP application runner
$runner = new HttpApplicationRunner(
    rootPath: $root,
    debug: Environment::appDebug(),
    checkEvents: Environment::appDebug(),
    environment: Environment::appEnv(),
    temporaryErrorHandler: new ErrorHandler(
        new Logger(
            [
                (new StreamTarget())->setLevels([
                    LogLevel::EMERGENCY,
                    LogLevel::ERROR,
                    LogLevel::WARNING,
                ]),
            ],
        ),
        new HtmlRenderer(),
    ),
);
$runner->run();
