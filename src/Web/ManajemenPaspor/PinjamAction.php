<?php
declare(strict_types=1);

namespace App\Web\ManajemenPaspor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class PinjamAction
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $body = json_decode((string)$request->getBody(), true);
        $pasporId = (int)($body['paspor_id'] ?? 0);
        $kds = (int)($body['kds'] ?? 0);
        $alasan = trim($body['alasan'] ?? '');
        $alasanCustom = trim($body['alasan_custom'] ?? '');
        $tanggalRencanaKembali = $body['tanggal_rencana_kembali'] ?? null;
        $catatan = trim($body['catatan'] ?? '');

        if (!$pasporId || !$kds) {
            return JsonResponse::create(['success' => false, 'message' => 'Data paspor tidak valid.'], 400);
        }

        $finalAlasan = ($alasan === 'Lainnya' && $alasanCustom) ? $alasanCustom : $alasan;
        $dicatatOleh = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'System';

        $db = $this->db;

        try {
            // Verify passport exists and is currently in office
            $paspor = $db->createCommand("SELECT id, status_lokasi FROM mtb_paspor WHERE id = :id", [':id' => $pasporId])->queryOne();
            if (!$paspor) {
                return JsonResponse::create(['success' => false, 'message' => 'Paspor tidak ditemukan.'], 404);
            }
            if ($paspor['status_lokasi'] === 'keluar') {
                return JsonResponse::create(['success' => false, 'message' => 'Paspor ini sudah dalam status keluar.'], 400);
            }

            // Insert log
            $db->createCommand()->insert('log_paspor', [
                'paspor_id' => $pasporId,
                'kds' => $kds,
                'tipe' => 'keluar',
                'alasan' => $finalAlasan,
                'catatan' => $catatan ?: null,
                'tanggal_aksi' => date('Y-m-d'),
                'tanggal_rencana_kembali' => $tanggalRencanaKembali ?: null,
                'dicatat_oleh' => $dicatatOleh,
            ])->execute();

            // Update status
            $db->createCommand()->update('mtb_paspor', ['status_lokasi' => 'keluar'], ['id' => $pasporId])->execute();

            return JsonResponse::create(['success' => true, 'message' => 'Paspor berhasil dicatat KELUAR.']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
