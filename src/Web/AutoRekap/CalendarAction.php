<?php
declare(strict_types=1);

namespace App\Web\AutoRekap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class CalendarAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $role = $_SESSION['role'] ?? '';
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myPondok = $_SESSION['def_pondok'] ?? '';

        $whereExt = "";
        $params = [];
        if ($role !== 'super_admin' && (!empty($myKepengurusan) || !empty($myPondok))) {
            $whereExt = " AND (s.kepengurusan = :my_kepengurusan OR s.pondok = :my_pondok)";
            $params[':my_kepengurusan'] = $myKepengurusan;
            $params[':my_pondok'] = $myPondok;
        }

        $whereClause = "WHERE s.aktif=1 $whereExt";

        // Fetch Kepengurusan list for filter dropdown
        $kepList = $db->createCommand(
            "SELECT DISTINCT s.kepengurusan FROM master_santri s WHERE s.aktif=1 AND s.kepengurusan != '' $whereExt ORDER BY s.kepengurusan",
            $params
        )->queryColumn();

        // ITAS Data with start processing date
        $sqlItas = "
            SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara AS daerah,
                   s.kepengurusan, i.exp_itas,
                   DATE_SUB(i.exp_itas, INTERVAL 3 MONTH) AS start_proses
            FROM master_santri s
            LEFT JOIN (
                SELECT kds, no_paspor FROM mtb_paspor
                WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)
            ) p ON s.kds = p.kds
            JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
            $whereClause
            ORDER BY i.exp_itas ASC
        ";
        $dataItas = $db->createCommand($sqlItas, $params)->queryAll();

        // Paspor Data with start processing date
        $sqlPaspor = "
            SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara AS daerah,
                   s.kepengurusan, p.exp_paspor,
                   DATE_SUB(p.exp_paspor, INTERVAL 18 MONTH) AS start_proses
            FROM master_santri s
            JOIN (
                SELECT kds, no_paspor, exp_paspor FROM mtb_paspor
                WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)
            ) p ON s.kds = p.kds
            $whereClause
            ORDER BY p.exp_paspor ASC
        ";
        $dataPaspor = $db->createCommand($sqlPaspor, $params)->queryAll();

        $activeItasJobs = [];
        try {
            $activeItasJobs = $db->createCommand("
                SELECT DISTINCT c.kds 
                FROM jobdesk_cases c 
                JOIN jobdesk_master_process p ON c.process_id = p.id 
                WHERE c.status IN ('aktif', 'kendala') 
                AND LOWER(p.nama_proses) LIKE '%itas%'
            ")->queryColumn();
        } catch (\Exception $e) {
            // Table might not exist
        }

        return $viewRenderer->render(__DIR__ . '/calendar_template', [
            'dataItas' => $dataItas,
            'dataPaspor' => $dataPaspor,
            'kepList' => $kepList,
            'activeItasJobs' => $activeItasJobs,
        ]);
    }
}
