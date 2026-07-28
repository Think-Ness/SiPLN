<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\FirebaseSync;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class PullAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        // Parsing body parameters manually karena getParsedBody kadang kosong jika application/json
        $rawBody = (string)$request->getBody();
        $body = json_decode($rawBody, true) ?? [];
        $selectedKds = isset($body['selected_kds']) && is_array($body['selected_kds']) ? $body['selected_kds'] : null;

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

        $result = FirebaseSync::pullFromFirebase($db, $selectedKds, $instansiKodeOverride);
        
        return JsonResponse::create($result);
    }
}
