<?php declare(strict_types=1);
namespace App\Web\Pemberkasan;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class UpdateAction
{
    public function __invoke(CurrentRoute $currentRoute, ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $id = $currentRoute->getArgument('id');
        $data = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();
        
        if (empty($data['nama_berkas'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama berkas wajib diisi'], 422);
        }

        $berkas = $db->createCommand("SELECT * FROM mtb_berkas_penting WHERE id = :id", [':id' => $id])->queryOne();
        if (!$berkas) {
            return JsonResponse::create(['success' => false, 'message' => 'Berkas tidak ditemukan'], 404);
        }

        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;

        // Validasi kepemilikan (Kecuali Super Admin)
        if ($role !== 'super_admin' && $berkas['kode'] !== $instansiId) {
            return JsonResponse::create(['success' => false, 'message' => 'Akses ditolak. Anda tidak berhak mengedit berkas ini.'], 403);
        }

        $isPublic = isset($data['is_public']) && $data['is_public'] === '1' ? 1 : 0;
        
        $updateData = [
            'nama_berkas' => $data['nama_berkas'],
            'is_public' => $isPublic
        ];

        // Super Admin bisa ubah kepemilikan instansi
        if ($role === 'super_admin' && !empty($data['target_kode']) && $isPublic === 0) {
            $updateData['kode'] = $data['target_kode'];
        }

        // Upload file baru jika ada
        if (isset($files['berkas_file']) && $files['berkas_file']->getError() === UPLOAD_ERR_OK) {
            $kodeInstansi = $updateData['kode'] ?? $berkas['kode'];
            $instansi = $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :kode", [':kode' => $kodeInstansi])->queryOne();
            
            $baseDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : dirname(__DIR__, 4) . '/public/uploads/berkas';
            $baseDir .= DIRECTORY_SEPARATOR . 'berkas penting';
            
            if (!is_dir($baseDir)) {
                if (!@mkdir($baseDir, 0777, true)) {
                    return JsonResponse::create(['success' => false, 'message' => 'Gagal membuat folder tujuan untuk file baru'], 500);
                }
            }

            $ext      = pathinfo($files['berkas_file']->getClientFilename(), PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama_berkas']);
            $fileName = $safeName . '.' . $ext;
            $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
            
            try {
                $files['berkas_file']->moveTo($fullPath);
                $updateData['path_file'] = $fullPath;
                
                // Hapus file lama jika ada dan berbeda path
                if (!empty($berkas['path_file']) && file_exists($berkas['path_file']) && $berkas['path_file'] !== $fullPath) {
                    @unlink($berkas['path_file']);
                }
            } catch (\Exception $e) {
                return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file baru: ' . $e->getMessage()], 500);
            }
        }

        try {
            $db->createCommand()->update('mtb_berkas_penting', $updateData, ['id' => $id])->execute();
            
            $newBerkas = $db->createCommand("SELECT * FROM mtb_berkas_penting WHERE id = :id", [':id' => $id])->queryOne();
            AuditLogger::log($db, 'UPDATE', 'PEMBERKASAN', $id, $berkas, $newBerkas, "Mengupdate profil berkas penting: {$data['nama_berkas']}");

            return JsonResponse::create(['success' => true, 'message' => 'Berkas berhasil diperbarui!']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
