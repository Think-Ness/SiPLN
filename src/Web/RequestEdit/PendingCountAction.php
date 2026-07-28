<?php
declare(strict_types=1);

namespace App\Web\RequestEdit;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class PendingCountAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        $myInstansiKode = $_SESSION['instansi_id'] ?? '';

        // Incoming Pending (Menunggu Persetujuan Anda)
        $sqlPending = "SELECT COUNT(*) FROM santri_edit_requests WHERE status = 'pending'";
        $paramsPending = [];

        if ($myKepengurusan !== '') {
            $sqlPending .= " AND target_kepengurusan = :myKep";
            $paramsPending[':myKep'] = trim($myKepengurusan);
        }

        $pendingCount = $db->createCommand($sqlPending, $paramsPending)->queryScalar();

        // Ensure is_read column exists before querying (in case they haven't visited the page yet)
        try {
            $db->createCommand("ALTER TABLE santri_edit_requests ADD COLUMN is_read TINYINT(1) DEFAULT 0")->execute();
        } catch (\Throwable $t) {}

        // Outgoing Approved (Pengajuan Saya yang disetujui, belum dibaca)
        $sqlApproved = "SELECT COUNT(*) FROM santri_edit_requests WHERE status = 'approved' AND requested_by_instansi_id = :myInstansi AND is_read = 0";
        $approvedCount = $db->createCommand($sqlApproved, [':myInstansi' => $myInstansiKode])->queryScalar();

        // Outgoing Rejected (Pengajuan Saya yang ditolak, belum dibaca)
        $sqlRejected = "SELECT COUNT(*) FROM santri_edit_requests WHERE status = 'rejected' AND requested_by_instansi_id = :myInstansi AND is_read = 0";
        $rejectedCount = $db->createCommand($sqlRejected, [':myInstansi' => $myInstansiKode])->queryScalar();

        return JsonResponse::create([
            'count' => (int)$pendingCount,
            'pending' => (int)$pendingCount,
            'approved' => (int)$approvedCount,
            'rejected' => (int)$rejectedCount
        ]);
    }
}
