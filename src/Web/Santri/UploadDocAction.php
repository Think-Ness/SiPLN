<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class UploadDocAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $type = $currentRoute->getArgument('type');
        $id = (int) $currentRoute->getArgument('id');

        if (!in_array($type, ['paspor', 'itas'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Tipe dokumen tidak valid'], 400);
        }

        $table = $type === 'paspor' ? 'mtb_paspor' : 'mtb_itas';
        
        $doc = $db->createCommand("SELECT * FROM $table WHERE id = :id", [':id' => $id])->queryOne();
        if (!$doc) {
            return JsonResponse::create(['success' => false, 'message' => 'Dokumen tidak ditemukan'], 404);
        }

        $santri = $db->createCommand("SELECT stambuk, nama FROM master_santri WHERE kds = :kds", [':kds' => $doc['kds']])->queryOne();
        $stambuk = $santri['stambuk'] ?? '0';
        $nama = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $santri['nama'] ?? '');

        $files = $request->getUploadedFiles();
        if (!isset($files['file']) || $files['file']->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'File tidak valid atau tidak ada file yang diunggah'], 400);
        }

        $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
        $baseDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : __DIR__ . '/../../../../public/uploads';
        $baseDir .= DIRECTORY_SEPARATOR . $type;
        if (!is_dir($baseDir)) @mkdir($baseDir, 0777, true);
        
        $ext = pathinfo($files['file']->getClientFilename(), PATHINFO_EXTENSION);
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array(strtolower($ext), $allowedExts)) {
            return JsonResponse::create(['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya menerima: ' . implode(', ', $allowedExts)], 400);
        }
        $prefix = $type === 'paspor' ? 'Paspor_' : 'ITAS_';
        $safeName = $prefix . $nama . '_' . $stambuk . '_' . time();
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $safeName . '.' . $ext;
        
        try {
            $files['file']->moveTo($fullPath);
            
            // Hapus file lama jika ada
            if (!empty($doc['path_file']) && file_exists($doc['path_file'])) {
                @unlink($doc['path_file']);
            }

            $db->createCommand()->update($table, ['path_file' => $fullPath], ['id' => $id])->execute();
            AuditLogger::log($db, 'UPDATE', 'SANTRI', $doc['kds'], null, "Mengunggah/memperbarui dokumen " . strtoupper($type) . " untuk KDS: " . $doc['kds']);
            return JsonResponse::create(['success' => true, 'message' => 'File berhasil diunggah']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file: ' . $e->getMessage()], 500);
        }
    }
}
