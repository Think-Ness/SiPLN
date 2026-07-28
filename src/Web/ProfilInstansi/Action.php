<?php
declare(strict_types=1);

namespace App\Web\ProfilInstansi;

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
        $myRole = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;
        
        $semuaInstansi = [];
        $instansi = null;
        
        if ($myRole === 'super_admin') {
            $semuaInstansi = $db->createCommand("SELECT * FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
            $queryParams = $request->getQueryParams();
            $kodeSelected = $queryParams['kode'] ?? null;
            
            if ($kodeSelected) {
                $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $kodeSelected])->queryOne();
            }
            if (!$instansi && !empty($semuaInstansi)) {
                $instansi = $semuaInstansi[0];
            }
        } else {
            if ($instansiId) {
                $instansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $instansiId])->queryOne();
            }
        }
        
        return $viewRenderer->render(__DIR__ . '/template', [
            'instansi' => $instansi ?: [],
            'semuaInstansi' => $semuaInstansi,
            'myRole' => $myRole
        ]);
    }
}
