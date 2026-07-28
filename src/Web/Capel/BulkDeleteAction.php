<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class BulkDeleteAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        
        $ids = $body['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
        }

        $successCount = 0;
        $errorMessages = [];

        foreach ($ids as $id) {
            $transaction = $db->beginTransaction();
            try {
                $draft = $db->createCommand("SELECT * FROM mtb_capel_draft WHERE id = :id", [':id' => $id])->queryOne();

                if (!$draft) {
                    $errorMessages[] = "ID $id tidak ditemukan.";
                    $transaction->rollBack();
                    continue;
                }

                // If approved, cleanup related data
                if (!empty($draft['kds_approved'])) {
                    $kds = $draft['kds_approved'];
                    
                    // Delete from queue
                    $db->createCommand("DELETE FROM capel_download_queue WHERE draft_id = :id", [':id' => $id])->execute();
                    
                    // Get kode santri
                    $santri = $db->createCommand("SELECT kode FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                    
                    if ($santri) {
                        $kode = $santri['kode'];
                        // Delete berkas
                        $db->createCommand("DELETE FROM mtb_berkas_penting WHERE kode = :kode", [':kode' => $kode])->execute();
                    }
                    
                    // Delete paspor
                    $db->createCommand("DELETE FROM mtb_paspor WHERE kds = :kds", [':kds' => $kds])->execute();
                    
                    // Delete santri
                    $db->createCommand("DELETE FROM master_santri WHERE kds = :kds", [':kds' => $kds])->execute();
                }

                // Delete draft
                $db->createCommand("DELETE FROM mtb_capel_draft WHERE id = :id", [':id' => $id])->execute();

                $transaction->commit();
                $successCount++;
            } catch (\Throwable $e) {
                if (isset($transaction) && $transaction->isActive()) {
                    $transaction->rollBack();
                }
                $errorMessages[] = "Gagal menghapus ID $id: " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            $msg = "Berhasil menghapus $successCount data CAPEL.";
            if (!empty($errorMessages)) {
                $msg .= " Beberapa gagal: " . implode(', ', $errorMessages);
            }
            return JsonResponse::create(['success' => true, 'message' => $msg]);
        }

        return JsonResponse::create(['success' => false, 'message' => implode(', ', $errorMessages)]);
    }
}
