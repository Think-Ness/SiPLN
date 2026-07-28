<?php
declare(strict_types=1);
namespace App\Web\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Csrf\CsrfTokenInterface;

final class LoginAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        DataResponseFactoryInterface $responseFactory,
        CsrfTokenInterface $csrfToken
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Jika sudah login, lempar kembali ke Dashboard
        if (isset($_SESSION['user_id'])) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            return $responseFactory->createResponse('', 302)->withHeader('Location', $scriptName === '' ? '/' : $scriptName);
        }

        // Render Halaman Login tanpa layout default (karena UI-nya akan dibuat full-page khusus)
        return $viewRenderer
            ->withLayout(null)
            ->render(__DIR__ . '/template', [
                'csrf' => $csrfToken->getValue()
            ]);
    }
}
