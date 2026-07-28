<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class DeleteDocAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $type = $currentRoute->getArgument('type'); // 'paspor' or 'itas'
        $id = (int) $currentRoute->getArgument('id');

        if (!in_array($type, ['paspor', 'itas'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Tipe dokumen tidak valid'], 400);
        }

        $table = $type === 'paspor' ? 'mtb_paspor' : 'mtb_itas';
        
        $doc = $db->createCommand(
            "SELECT path_file, aktif FROM {$table} WHERE id = :id",
            [':id' => $id]
        )->queryOne();

        if (!$doc) {
            return JsonResponse::create(['success' => false, 'message' => 'Dokumen tidak ditemukan'], 404);
        }



        if (!empty($doc['path_file']) && file_exists($doc['path_file'])) {
            @unlink($doc['path_file']);
        }

        $db->createCommand()->delete($table, ['id' => $id])->execute();
        
        if ($doc) {
            AuditLogger::log($db, 'DELETE', 'SANTRI', $doc['kds'] ?? null, $doc, "Menghapus dokumen " . strtoupper($type) . " (KDS: " . ($doc['kds'] ?? '-') . ")");
        }

        return JsonResponse::create([
            'success' => true,
            'message' => 'Riwayat berhasil dihapus'
        ]);
    }
}
