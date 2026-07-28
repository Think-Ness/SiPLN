<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\FirebaseSync;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class PreviewAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = json_decode((string)$request->getBody(), true) ?? [];
        $instansiKodeOverride = null;
        
        $role = $_SESSION['role'] ?? '';
        
        // Super admin bisa mengirim instansi_kode
        if ($role === 'super_admin') {
            if (empty($body['instansi_kode'])) {
                return JsonResponse::create([
                    'success' => false,
                    'message' => 'Super Admin harus memilih instansi terlebih dahulu.'
                ]);
            }
            $instansiKodeOverride = $body['instansi_kode'];
        } else {
            // Hanya admin instansi yang diizinkan tarik data secara mandiri
            if (empty($_SESSION['instansi_id']) || $_SESSION['instansi_id'] == '0') {
                return JsonResponse::create([
                    'success' => false,
                    'message' => 'Anda harus login sebagai Admin Instansi/Pondok untuk menarik data'
                ]);
            }
        }

        try {
            $result = FirebaseSync::previewFromFirebase($db, $instansiKodeOverride);
            return JsonResponse::create($result);
        } catch (\Throwable $e) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }
}
