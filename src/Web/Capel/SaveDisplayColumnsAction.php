<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class SaveDisplayColumnsAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        @session_start();
        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
        $instansiId = $_SESSION['instansi_id'] ?? 0;

        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        
        if ($isSuperAdmin && !empty($body['instansi_id'])) {
            $instansiId = (int)$body['instansi_id'];
        }

        if (!$instansiId) {
            return JsonResponse::create(['success' => false, 'message' => 'Unauthorized'])->withStatus(401);
        }
        
        $columns = $body['columns'] ?? [];
        
        if (!is_array($columns)) {
            return JsonResponse::create(['success' => false, 'message' => 'Format kolom tidak valid'])->withStatus(400);
        }

        $columnsJson = json_encode(array_values($columns));
        
        $db->createCommand("UPDATE master_instansi SET capel_display_columns = :cols WHERE kode = :id", [
            ':cols' => $columnsJson,
            ':id' => $instansiId
        ])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Tampilan kolom berhasil disimpan.']);
    }
}
