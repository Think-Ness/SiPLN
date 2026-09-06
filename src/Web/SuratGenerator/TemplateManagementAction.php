<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\UploadPath;

final class TemplateManagementAction
{
    public function index(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        
        // Scan folder Surat_Menyurat/ berdasarkan path_folder instansi
        $instansiBase = UploadPath::getBase($db);
        $suratDir = $instansiBase !== null ? $instansiBase . '/Surat_Menyurat' : dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';
        
        // Ensure default 3 folders exist
        $defaultFolders = ['Imigrasi', 'Kemenag', 'Lain-Lain'];
        if (!is_dir($suratDir)) {
            @mkdir($suratDir, 0777, true);
        }
        foreach ($defaultFolders as $df) {
            $dfPath = $suratDir . '/' . $df;
            if (!is_dir($dfPath)) {
                @mkdir($dfPath, 0777, true);
            }
        }

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
