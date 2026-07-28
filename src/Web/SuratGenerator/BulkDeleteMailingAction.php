<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use App\Shared\AuditLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class BulkDeleteMailingAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }
        
        $ids = $body['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada mailing yang dipilih.'], 400);
        }

        // Pastikan IDs valid
        $validIds = array_map('intval', $ids);
        
        $transaction = $db->beginTransaction();
        try {
            $deletedCount = 0;
            foreach ($validIds as $id) {
                // Check exist
                $mailing = $db->createCommand("SELECT kode_mailing FROM surat_mailing WHERE id = :id", [':id' => $id])->queryOne();
                if ($mailing) {
                    $db->createCommand("DELETE FROM surat_mailing WHERE id = :id", [':id' => $id])->execute();
                    AuditLogger::log($db, 'DELETE', 'SURAT_MAILING', (string)$id, null, "Menghapus mailing masal {$mailing['kode_mailing']}");
                    $deletedCount++;
                }
            }
            $transaction->commit();

            return JsonResponse::create([
                'success' => true,
                'message' => "Berhasil menghapus $deletedCount mailing terpilih.",
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}
