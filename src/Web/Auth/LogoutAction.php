<?php
declare(strict_types=1);
namespace App\Web\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

final class LogoutAction
{
    public function __invoke(
        ServerRequestInterface $request,
        DataResponseFactoryInterface $responseFactory
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $instansiId = $_SESSION['instansi_id'] ?? null;
        
        if ($userId && $instansiId && $instansiId !== '0') {
            try {
                \App\Shared\FirebaseSync::removePresence((string)$instansiId, (string)$userId);
            } catch (\Exception $e) {
                // Ignore errors during logout
            }
        }
        
        session_destroy();
        
        // Alihkan (Redirect) kembali ke halaman Login
        return $responseFactory->createResponse('', 302)->withHeader('Location', API_URL . '/login');
    }
}
