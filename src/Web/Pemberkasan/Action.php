<?php
declare(strict_types=1);

namespace App\Web\Pemberkasan;

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
        $search = $request->getQueryParams()['q'] ?? '';
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;
        
        $params = [];
        $whereConds = [];
        if ($search !== '') {
            $whereConds[] = "(b.nama_berkas LIKE :q OR b.kode LIKE :q)";
            $params[':q'] = "%$search%";
        }
        
        if ($role !== 'super_admin') {
            $whereConds[] = "(b.is_public = 1 OR b.kode = :myKode)";
            $params[':myKode'] = $instansiId;
        }
        
        $where = !empty($whereConds) ? "WHERE " . implode(" AND ", $whereConds) : "";

        $berkas = $db->createCommand(
            "SELECT b.*, i.nama_instansi FROM mtb_berkas_penting b INNER JOIN master_instansi i ON b.kode = i.kode $where ORDER BY b.id DESC",
            $params
        )->queryAll();

        $semuaInstansi = [];
        if ($role === 'super_admin') {
            $semuaInstansi = $db->createCommand("SELECT kode, nama_instansi FROM master_instansi ORDER BY nama_instansi ASC")->queryAll();
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'berkas' => $berkas,
            'total'  => count($berkas),
            'search' => $search,
            'myRole' => $role,
            'semuaInstansi' => $semuaInstansi
        ]);
    }
}
