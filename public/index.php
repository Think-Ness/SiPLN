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

// --- Hardware/License Guard ---
require_once __DIR__ . '/license_guard.php';

// --- Installation Guard ---
if (!file_exists($root . '/config/installed.lock')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    // Jika request adalah untuk aset statis (CSS/JS), kembalikan 404 langsung
    if (strpos($uri, '/assets/') !== false || preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $uri)) {
        http_response_code(404);
        exit('Asset not found');
    }
    
    // Redirect ke install.php dengan path absolut
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
    header('Location: ' . $basePath . '/install.php');
    exit;
}

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
