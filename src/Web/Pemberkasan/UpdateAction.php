<?php declare(strict_types=1);
namespace App\Web\Pemberkasan;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;
use App\Shared\UploadPath;

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
            $kodeInstansi = (int)($updateData['kode'] ?? $berkas['kode']);
            
            try {
                $baseDir = UploadPath::getFolder($db, 'berkas penting', $kodeInstansi);
            } catch (\Throwable $e) {
                return JsonResponse::create(['success' => false, 'message' => 'Gagal menentukan folder penyimpanan: ' . $e->getMessage()], 500);
            }

            $uploadedFile = $files['berkas_file'];
            $ext = strtolower(pathinfo($uploadedFile->getClientFilename() ?? '', PATHINFO_EXTENSION));
            
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
            if (!in_array($ext, $allowedExts, true)) {
                return JsonResponse::create(['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya menerima: ' . implode(', ', $allowedExts)], 400);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama_berkas']);
            $fileName = $safeName . '.' . $ext;
            if (file_exists($baseDir . '/' . $fileName)) {
                $fileName = $safeName . '_' . time() . '.' . $ext;
            }
            $fullPath = $baseDir . '/' . $fileName;
            
            try {
                $uploadedFile->moveTo($fullPath);
                $updateData['path_file'] = $fullPath;
                
                // Hapus file lama jika ada dan berbeda path
                if (!empty($berkas['path_file']) && file_exists($berkas['path_file']) && $berkas['path_file'] !== $fullPath) {
                    @unlink($berkas['path_file']);
                }
            } catch (\Throwable $e) {
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
