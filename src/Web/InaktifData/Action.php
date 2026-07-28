<?php
declare(strict_types=1);

namespace App\Web\InaktifData;

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
        
        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter - Cross Tenant)
        // ==========================================
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        $params = [];
        $where = "WHERE s.aktif = 0";
        
        if ($role !== 'super_admin' && (!empty($myKepengurusan) || !empty($myPondok))) {
            $conditions = [];
            if (!empty($myKepengurusan)) {
                $conditions[] = "s.kepengurusan = :my_kepengurusan";
                $params[':my_kepengurusan'] = $myKepengurusan;
            }
            if (!empty($myPondok)) {
                $conditions[] = "s.pondok = :my_pondok";
                $params[':my_pondok'] = $myPondok;
            }
            if (!empty($conditions)) {
                $where .= " AND (" . implode(" OR ", $conditions) . ") ";
            }
        }

        if ($search !== '') {
            $where .= " AND (s.nama LIKE :q OR s.stambuk LIKE :q)";
            $params[':q'] = "%$search%";
        }
        $santris = $db->createCommand(
            "SELECT s.*, p.no_paspor, p.exp_paspor FROM master_santri s LEFT JOIN mtb_paspor p ON s.kds=p.kds $where ORDER BY s.nama ASC",
            $params
        )->queryAll();

        $total = count($santris);
        return $viewRenderer->render(__DIR__ . '/template', ['santris' => $santris, 'total' => $total, 'search' => $search]);
    }
}
