<?php declare(strict_types=1);
namespace App\Web\InaktifData;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class HardDeleteAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        $myKep = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        if (empty($role)) {
            return JsonResponse::create(['success' => false, 'message' => 'Unauthorized - Silakan login terlebih dahulu'], 403);
        }

        $kds = (int) $currentRoute->getArgument('kds');

        // Check ownership if not super_admin
        if ($role !== 'super_admin' && (!empty($myKep) || !empty($myPondok))) {
            $santri = $db->createCommand("SELECT kepengurusan, pondok FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
            if (!$santri) {
                return JsonResponse::create(['success' => false, 'message' => 'Data santri tidak ditemukan'], 404);
            }
            
            $isAllowed = false;
            if (!empty($myKep) && strcasecmp(trim((string)($santri['kepengurusan'] ?? '')), trim((string)$myKep)) === 0) {
                $isAllowed = true;
            }
            if (!empty($myPondok) && strcasecmp(trim((string)($santri['pondok'] ?? '')), trim((string)$myPondok)) === 0) {
                $isAllowed = true;
            }

            if (!$isAllowed) {
                return JsonResponse::create(['success' => false, 'message' => 'Anda tidak memiliki hak untuk menghapus data santri milik instansi/pondok lain'], 403);
            }
        }

        try {
            $db->transaction(function() use ($db, $kds) {
                // Delete related data first
                $db->createCommand()->delete('mtb_paspor', ['kds' => $kds])->execute();
                $db->createCommand()->delete('mtb_itas', ['kds' => $kds])->execute();
                
                // Jobdesk
                $cases = $db->createCommand("SELECT id FROM jobdesk_cases WHERE kds = :kds", [':kds' => $kds])->queryColumn();
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
                    $db->createCommand("DELETE FROM jobdesk_cases WHERE kds = :kds", [':kds' => $kds])->execute();
                }

                // FIREBASE DUAL-WRITE
                $santriDb = $db->createCommand("SELECT kepengurusan FROM master_santri WHERE kds = :kds", [':kds' => $kds])->queryOne();
                
                // Delete santri
                $db->createCommand()->delete('master_santri', ['kds' => $kds])->execute();
                
                if ($santriDb) {
                    \App\Shared\FirebaseSync::deleteSantri((string)$kds, $santriDb['kepengurusan'] ?? '');
                }
            });

            AuditLogger::log($db, 'DELETE', 'SANTRI', $kds, null, "Hard delete data santri inaktif (KDS: $kds)");
            return JsonResponse::create(['success' => true, 'message' => 'Data santri berhasil dihapus permanen']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
