<?php
declare(strict_types=1);

namespace App\Web\Pemberkasan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class ApiAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;
        
        if ($role === 'super_admin') {
            $berkas = $db->createCommand(
                "SELECT b.*, i.nama_instansi FROM mtb_berkas_penting b INNER JOIN master_instansi i ON b.kode = i.kode ORDER BY b.id DESC"
            )->queryAll();
        } else {
            $berkas = $db->createCommand(
                "SELECT b.*, i.nama_instansi FROM mtb_berkas_penting b INNER JOIN master_instansi i ON b.kode = i.kode WHERE b.is_public = 1 OR b.kode = :kode ORDER BY b.id DESC",
                [':kode' => $instansiId]
            )->queryAll();
        }

        return JsonResponse::create([
            'success' => true,
            'data' => [
                'berkas' => $berkas
            ]
        ]);
    }
}
