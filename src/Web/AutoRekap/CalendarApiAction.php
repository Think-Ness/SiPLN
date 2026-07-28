<?php
declare(strict_types=1);

namespace App\Web\AutoRekap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class CalendarApiAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        // ==========================================
        // DYNAMIC DATA ISOLATION (SaaS Filter)
        // ==========================================
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

        $queryParams = $request->getQueryParams();
        $filterKep = $queryParams['kepengurusan'] ?? '';
        if (!empty($filterKep)) {
            $whereExt .= " AND s.kepengurusan = :filter_kep";
            $params[':filter_kep'] = $filterKep;
        }

        $whereClause = "WHERE s.aktif=1 $whereExt";

        // Fetch Kepengurusan list for filter dropdown
        $kepList = $db->createCommand(
            "SELECT DISTINCT s.kepengurusan FROM master_santri s WHERE s.aktif=1 AND s.kepengurusan != '' $whereExt ORDER BY s.kepengurusan",
            $params
        )->queryColumn();

        // ==========================================
        // ITAS Data: All active ITAS with expiry dates
        // ==========================================
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

        // ==========================================
        // Paspor Data: All active santri with passport
        // ==========================================
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

        // ==========================================
        // Build calendar events
        // ==========================================
        $events = [];
        $today = date('Y-m-d');
        $currentMonth = date('Y-m');

        // Stats counters
        $stats = [
            'itasProcessThisMonth' => 0,
            'pasporProcessThisMonth' => 0,
            'itasExpiredTotal' => 0,
            'pasporExpiredTotal' => 0,
            'itasExpThisMonth' => 0,
            'pasporExpThisMonth' => 0,
            'totalEvents' => 0,
        ];

        foreach ($dataItas as $row) {
            $expDate = $row['exp_itas'];
            $startDate = $row['start_proses'];

            // Start processing event
            $events[] = [
                'id' => 'itas-start-' . $row['kds'],
                'type' => 'itas_start',
                'date' => $startDate,
                'label' => 'Proses ITAS: ' . $row['nama'],
                'santri' => [
                    'kds' => $row['kds'],
                    'nama' => $row['nama'],
                    'kelas' => $row['kelas'],
                    'daerah' => $row['daerah'],
                    'kepengurusan' => $row['kepengurusan'],
                ],
                'no_paspor' => $row['no_paspor'] ?? '-',
                'exp_date' => $expDate,
                'start_date' => $startDate,
                'is_expired' => $expDate < $today,
            ];

            // Expiry event
            $events[] = [
                'id' => 'itas-exp-' . $row['kds'],
                'type' => 'itas_exp',
                'date' => $expDate,
                'label' => 'Exp ITAS: ' . $row['nama'],
                'santri' => [
                    'kds' => $row['kds'],
                    'nama' => $row['nama'],
                    'kelas' => $row['kelas'],
                    'daerah' => $row['daerah'],
                    'kepengurusan' => $row['kepengurusan'],
                ],
                'no_paspor' => $row['no_paspor'] ?? '-',
                'exp_date' => $expDate,
                'start_date' => $startDate,
                'is_expired' => $expDate < $today,
            ];

            // Stats
            if (substr($startDate, 0, 7) === $currentMonth) {
                $stats['itasProcessThisMonth']++;
            }
            if (substr($expDate, 0, 7) === $currentMonth) {
                $stats['itasExpThisMonth']++;
            }
            if ($expDate < $today) {
                $stats['itasExpiredTotal']++;
            }
        }

        foreach ($dataPaspor as $row) {
            $expDate = $row['exp_paspor'];
            $startDate = $row['start_proses'];

            // Start processing event
            $events[] = [
                'id' => 'paspor-start-' . $row['kds'],
                'type' => 'paspor_start',
                'date' => $startDate,
                'label' => 'Proses Paspor: ' . $row['nama'],
                'santri' => [
                    'kds' => $row['kds'],
                    'nama' => $row['nama'],
                    'kelas' => $row['kelas'],
                    'daerah' => $row['daerah'],
                    'kepengurusan' => $row['kepengurusan'],
                ],
                'no_paspor' => $row['no_paspor'] ?? '-',
                'exp_date' => $expDate,
                'start_date' => $startDate,
                'is_expired' => $expDate < $today,
            ];

            // Expiry event
            $events[] = [
                'id' => 'paspor-exp-' . $row['kds'],
                'type' => 'paspor_exp',
                'date' => $expDate,
                'label' => 'Exp Paspor: ' . $row['nama'],
                'santri' => [
                    'kds' => $row['kds'],
                    'nama' => $row['nama'],
                    'kelas' => $row['kelas'],
                    'daerah' => $row['daerah'],
                    'kepengurusan' => $row['kepengurusan'],
                ],
                'no_paspor' => $row['no_paspor'] ?? '-',
                'exp_date' => $expDate,
                'start_date' => $startDate,
                'is_expired' => $expDate < $today,
            ];

            // Stats
            if (substr($startDate, 0, 7) === $currentMonth) {
                $stats['pasporProcessThisMonth']++;
            }
            if (substr($expDate, 0, 7) === $currentMonth) {
                $stats['pasporExpThisMonth']++;
            }
            if ($expDate < $today) {
                $stats['pasporExpiredTotal']++;
            }
        }

        $stats['totalEvents'] = count($events);

        return JsonResponse::create([
            'success' => true,
            'data' => [
                'events' => $events,
                'stats' => $stats,
                'kepList' => $kepList,
                'today' => $today,
            ]
        ]);
    }
}
