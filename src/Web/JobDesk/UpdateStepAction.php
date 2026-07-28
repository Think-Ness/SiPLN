<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

class UpdateStepAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $stepId = $currentRoute->getArgument('id');
        $body = $request->getParsedBody();
        $status = $body['status'] ?? 'pending';
        $date = $body['date'] ?? null;
        $note = $body['note'] ?? '';

        if (!$date) $date = null;

        // SERVER-SIDE DEFENSE: Restriksi Job Desk bagi Pondok Pindahan
        $stepInfo = $db->createCommand("
            SELECT s.case_id, s.step_urut, c.kds, ms.kepengurusan 
            FROM jobdesk_case_steps s
            JOIN jobdesk_cases c ON s.case_id = c.id
            JOIN master_santri ms ON c.kds = ms.kds
            WHERE s.id = :id
        ", [':id' => $stepId])->queryOne();

        if ($stepInfo) {
            $myKep = $_SESSION['def_kepengurusan'] ?? '';
            $sKep = trim((string)($stepInfo['kepengurusan'] ?? ''));
            if ($myKep !== '' && strcasecmp($sKep, trim($myKep)) !== 0) {
                return JsonResponse::create(['success' => false, 'message' => 'Akses Ditolak: Anda berstatus Mode Pemantau dan tidak memiliki wewenang untuk mengeksekusi tahapan Job Desk milik kepengurusan lain.'], 403);
            }
        }

        $transaction = $db->beginTransaction();
        try {
            // Update step
            $db->createCommand()->update('jobdesk_case_steps', [
                'status' => $status,
                'tgl_realisasi' => $date,
                'updated_by' => 'Staf'
            ], ['id' => $stepId])->execute();

            // Jika ada note, masukkan ke log/catatan
            if (!empty(trim($note))) {
                $db->createCommand()->insert('jobdesk_step_notes', [
                    'step_id' => $stepId,
                    'tipe' => $status === 'selesai' ? 'selesai' : ($status === 'blocked' ? 'kekurangan' : 'info'),
                    'isi' => $note,
                    'created_by' => 'Staf'
                ])->execute();
            }

            // Check if all steps are done to close the case
            $stepInfo = $db->createCommand("SELECT case_id, step_urut FROM jobdesk_case_steps WHERE id = :id", [':id' => $stepId])->queryOne();
            if ($stepInfo) {
                $caseId = $stepInfo['case_id'];
                $stepUrut = $stepInfo['step_urut'];

                // Auto-complete previous steps
                if ($status === 'selesai' || $status === 'proses') {
                    $db->createCommand()->update('jobdesk_case_steps', [
                        'status' => 'selesai',
                        'tgl_realisasi' => $date ?? date('Y-m-d'),
                        'updated_by' => 'Staf (Auto)'
                    ], ['and', ['=', 'case_id', $caseId], ['<', 'step_urut', $stepUrut], ['!=', 'status', 'selesai']])->execute();
                }
                $totalSteps = $db->createCommand("SELECT COUNT(*) FROM jobdesk_case_steps WHERE case_id = :id", [':id' => $caseId])->queryScalar();
                $completed = $db->createCommand("SELECT COUNT(*) FROM jobdesk_case_steps WHERE case_id = :id AND status = 'selesai'", [':id' => $caseId])->queryScalar();
                
                if ($totalSteps > 0 && $completed == $totalSteps) {
                    $db->createCommand()->update('jobdesk_cases', ['status' => 'selesai'], ['id' => $caseId])->execute();
                } else {
                    $db->createCommand()->update('jobdesk_cases', ['status' => 'aktif'], ['id' => $caseId])->execute();
                }
            }

            if ($stepInfo) {
                AuditLogger::log($db, 'UPDATE', 'JOB_DESK', $stepId, null, "Mengubah status tahap ke-{$stepInfo['step_urut']} menjadi '$status' untuk proses ID {$stepInfo['case_id']}");
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
