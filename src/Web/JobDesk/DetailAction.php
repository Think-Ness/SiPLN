<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Router\CurrentRoute;

class DetailAction
{
    public function __invoke(
        CurrentRoute $currentRoute,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = $currentRoute->getArgument('id');

        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';
        
        $whereExt = "";
        $params = [':id' => $id];
        if ($role !== 'super_admin') {
            if (!empty($myKepengurusan) && !empty($myPondok)) {
                $whereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok) ";
                $params[':my_kepengurusan'] = $myKepengurusan;
                $params[':my_pondok'] = $myPondok;
            } else if (!empty($myKepengurusan)) {
                $whereExt = " AND s.kepengurusan = :my_kepengurusan ";
                $params[':my_kepengurusan'] = $myKepengurusan;
            } else if (!empty($myPondok)) {
                $whereExt = " AND s.pondok = :my_pondok ";
                $params[':my_pondok'] = $myPondok;
            }
        }

        $sql = "
            SELECT 
                j.*, 
                s.nama, s.pondok, s.kelas, s.negara as daerah, s.kds, s.kepengurusan,
                p.no_paspor, p.exp_paspor,
                its.exp_itas,
                pr.nama_proses
            FROM jobdesk_cases j
            JOIN master_santri s ON j.kds = s.kds
            LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
            LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) its ON s.kds = its.kds
            LEFT JOIN jobdesk_master_process pr ON j.process_id = pr.id
            WHERE j.id = :id $whereExt
        ";
        $case = $db->createCommand($sql, $params)->queryOne();

        if (!$case) {
            return $viewRenderer->render(__DIR__ . '/not_found');
        }

        $sqlSteps = "SELECT * FROM jobdesk_case_steps WHERE case_id = :id ORDER BY step_urut ASC";
        $steps = $db->createCommand($sqlSteps, [':id' => $id])->queryAll();

        $sqlNotes = "
            SELECT c.*, s.step_urut, s.step_nama 
            FROM jobdesk_step_notes c
            JOIN jobdesk_case_steps s ON c.step_id = s.id
            WHERE s.case_id = :id
            ORDER BY c.created_at DESC
        ";
        $notes = $db->createCommand($sqlNotes, [':id' => $id])->queryAll();

        // Fetch full passport info
        $sqlPaspor = "SELECT * FROM mtb_paspor WHERE kds = :kds ORDER BY id DESC LIMIT 1";
        $paspor = $db->createCommand($sqlPaspor, [':kds' => $case['kds']])->queryOne();

        // Fetch full ITAS info
        $sqlItas = "SELECT * FROM mtb_itas WHERE kds = :kds ORDER BY id DESC LIMIT 1";
        $itas = $db->createCommand($sqlItas, [':kds' => $case['kds']])->queryOne();

        // Fetch payment data for this case (with cicilan info)
        $payment = null;
        try {
            $payment = $db->createCommand(
                "SELECT p.*,
                    COALESCE((SELECT SUM(i.nominal) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id), 0) as total_cicilan,
                    (SELECT COUNT(i.id) FROM jobdesk_payment_installment i WHERE i.case_id = p.case_id) as jumlah_cicilan
                 FROM jobdesk_case_payment p WHERE p.case_id = :cid",
                [':cid' => $id]
            )->queryOne();
        } catch (\Exception $e) {
            // Table might not exist yet — try simple query
            try {
                $payment = $db->createCommand(
                    "SELECT * FROM jobdesk_case_payment WHERE case_id = :cid",
                    [':cid' => $id]
                )->queryOne();
            } catch (\Exception $e2) {}
        }

        // Fetch payment history for this santri (all processes, all years)
        $paymentHistory = [];
        try {
            $paymentHistory = $db->createCommand("
                SELECT 
                    pay.*,
                    j.process_id, j.tanggal_referensi, j.tgl_mulai_proses, 
                    j.status as case_status, j.created_at as case_created_at,
                    pr.nama_proses
                FROM jobdesk_case_payment pay
                JOIN jobdesk_cases j ON pay.case_id = j.id
                LEFT JOIN jobdesk_master_process pr ON j.process_id = pr.id
                WHERE j.kds = :kds
                ORDER BY j.created_at DESC
            ", [':kds' => $case['kds']])->queryAll();
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        // Info Instansi for kop surat
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $instansiInfo = [];
        if ($instansiId) {
            $instansiInfo = $db->createCommand(
                "SELECT * FROM master_instansi WHERE kode = :kode LIMIT 1",
                [':kode' => $instansiId]
            )->queryOne() ?: [];
        } elseif ($role === 'super_admin') {
            $instansiInfo = $db->createCommand("SELECT * FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne() ?: [];
        }

        return $viewRenderer->render(__DIR__ . '/detail', [
                'case' => $case,
                'steps' => $steps,
                'notes' => $notes,
                'paspor' => $paspor,
                'itas' => $itas,
                'payment' => $payment,
                'paymentHistory' => $paymentHistory,
                'instansiInfo' => $instansiInfo,
            ]);
    }
}


