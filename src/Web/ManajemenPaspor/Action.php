<?php
declare(strict_types=1);

namespace App\Web\ManajemenPaspor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';

        return $viewRenderer->render(__DIR__ . '/template', [
            'instansiId' => $instansiId,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
