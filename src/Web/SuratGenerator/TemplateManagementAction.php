<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class TemplateManagementAction
{
    public function index(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer
    ): ResponseInterface {
        
        // Scan folder Surat_Menyurat/ to get existing instansi tujuan folders
        $suratDir = dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';
        $folders = [];
        if (is_dir($suratDir)) {
            $items = scandir($suratDir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..' || $item === 'Output') continue;
                if (is_dir($suratDir . '/' . $item)) {
                    $folders[] = $item;
                }
            }
            sort($folders);
        }

        return $viewRenderer
            ->withViewPath(__DIR__)
            ->render('template_management', [
                'folderList' => $folders,
                'role' => $_SESSION['role'] ?? ''
            ]);
    }
}
