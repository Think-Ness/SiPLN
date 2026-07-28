<?php
declare(strict_types=1);

namespace App\Web\ManajemenInstansi;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

final class ListAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db,
        DataResponseFactoryInterface $responseFactory
    ): ResponseInterface {
        // Proteksi Lapis Kedua: Hanya Super Admin yang boleh akses!
        if (($_SESSION['role'] ?? '') !== 'super_admin') {
            return $responseFactory->createResponse('', 302)->withHeader('Location', '/');
        }

        // Ambil semua daftar instansi
        $instansis = $db->createCommand("SELECT * FROM master_instansi ORDER BY kode DESC")->queryAll();

        return $viewRenderer->render(__DIR__ . '/template', [
            'instansis' => $instansis
        ]);
    }
}
