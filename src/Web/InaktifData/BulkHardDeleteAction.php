<?php declare(strict_types=1);
namespace App\Web\InaktifData;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class BulkHardDeleteAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        if (($_SESSION['role'] ?? '') !== 'super_admin') {
            return JsonResponse::create(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $body = json_decode((string)$request->getBody(), true);
        $kdsList = $body['kds'] ?? [];

        if (empty($kdsList) || !is_array($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang dipilih'], 400);
        }

        $kdsList = array_map('intval', $kdsList);
        $kdsList = array_filter($kdsList, fn($k) => $k > 0);

        if (empty($kdsList)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data valid yang dipilih'], 400);
        }

        try {
            $db->transaction(function() use ($db, $kdsList) {
                $kdsIn = implode(',', $kdsList);
                
                // Delete related data first
                $db->createCommand("DELETE FROM mtb_paspor WHERE kds IN ($kdsIn)")->execute();
                $db->createCommand("DELETE FROM mtb_itas WHERE kds IN ($kdsIn)")->execute();
                
                // Jobdesk
                $cases = $db->createCommand("SELECT id FROM jobdesk_cases WHERE kds IN ($kdsIn)")->queryColumn();
                if (!empty($cases)) {
                    $caseIn = implode(',', $cases);
                    
                    // Delete notes linked to steps
                    $stepIds = $db->createCommand("SELECT id FROM jobdesk_case_steps WHERE case_id IN ($caseIn)")->queryColumn();
                    if (!empty($stepIds)) {
                        $db->createCommand("DELETE FROM jobdesk_step_notes WHERE step_id IN (" . implode(',', $stepIds) . ")")->execute();
                    }

                    $db->createCommand("DELETE FROM jobdesk_case_steps WHERE case_id IN ($caseIn)")->execute();
                    $db->createCommand("DELETE FROM jobdesk_case_payment WHERE case_id IN ($caseIn)")->execute();
                    $db->createCommand("DELETE FROM jobdesk_payment_installment WHERE case_id IN ($caseIn)")->execute();
                    $db->createCommand("DELETE FROM jobdesk_cases WHERE kds IN ($kdsIn)")->execute();
                }

                // FIREBASE DUAL-WRITE
                $santris = $db->createCommand("SELECT kds, kepengurusan FROM master_santri WHERE kds IN ($kdsIn)")->queryAll();

                // Delete santri
                $db->createCommand("DELETE FROM master_santri WHERE kds IN ($kdsIn)")->execute();
                
                foreach ($santris as $s) {
                    \App\Shared\FirebaseSync::deleteSantri((string)$s['kds'], $s['kepengurusan'] ?? '');
                }
            });

            AuditLogger::log($db, 'DELETE', 'SANTRI', 0, null, "Bulk hard delete data santri inaktif (KDS: " . implode(',', $kdsList) . ")");
            return JsonResponse::create(['success' => true, 'message' => count($kdsList) . ' data santri berhasil dihapus permanen']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
