<?php declare(strict_types=1);
namespace App\Web\Pemberkasan;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class UploadAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $data  = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        if (empty($data['nama_berkas'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama berkas wajib diisi'], 422);
        }

        $isPublic = isset($data['is_public']) && $data['is_public'] === '1' ? 1 : 0;
        
        $role = $_SESSION['role'] ?? '';
        $instansiId = $_SESSION['instansi_id'] ?? null;
        
        // Cari path instansi yang sesuai
        $targetKode = $instansiId;
        if ($role === 'super_admin' && !empty($data['target_kode'])) {
            $targetKode = $data['target_kode'];
        }
        
        $instansi = null;
        if ($targetKode) {
            $instansi = $db->createCommand("SELECT kode, path_folder FROM master_instansi WHERE kode = :kode", [':kode' => $targetKode])->queryOne();
        } else {
            $instansi = $db->createCommand("SELECT kode, path_folder FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();
        }
        $kodeInstansi = $instansi ? $instansi['kode'] : '1';

        $filePath = '';
        if (isset($files['berkas_file']) && $files['berkas_file']->getError() === UPLOAD_ERR_OK) {
            // Get path_folder from master_instansi
            $baseDir = !empty($instansi['path_folder']) ? rtrim($instansi['path_folder'], '/\\') : __DIR__ . '/../../../../public/uploads/berkas';
            $baseDir .= DIRECTORY_SEPARATOR . 'berkas penting';
            
            if (!is_dir($baseDir)) {
                if (!@mkdir($baseDir, 0777, true)) {
                    return JsonResponse::create(['success' => false, 'message' => 'Gagal membuat folder tujuan: ' . $baseDir], 500);
                }
            }

            $ext      = pathinfo($files['berkas_file']->getClientFilename(), PATHINFO_EXTENSION);
            
            // SECURITY FIX: Whitelist file extensions
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
            if (!in_array(strtolower($ext), $allowedExts)) {
                return JsonResponse::create(['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya menerima: ' . implode(', ', $allowedExts)], 400);
            }
            
            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['nama_berkas']);
            $fileName = $safeName . '.' . $ext;
            
            // Full absolute path
            $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
            
            try {
                $files['berkas_file']->moveTo($fullPath);
                $filePath = $fullPath; // Simpan absolute path ke DB
            } catch (\Exception $e) {
                return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file: ' . $e->getMessage()], 500);
            }
        }

        try {
            $db->createCommand()->insert('mtb_berkas_penting', [
                'kode'        => $kodeInstansi,
                'nama_berkas' => $data['nama_berkas'],
                'path_file'   => $filePath,
                'is_public'   => $isPublic,
            ])->execute();
            
            $berkasId = $db->getLastInsertID();
            $newBerkas = $db->createCommand("SELECT * FROM mtb_berkas_penting WHERE id = :id", [':id' => $berkasId])->queryOne();
            AuditLogger::log($db, 'CREATE', 'PEMBERKASAN', $berkasId, null, $newBerkas, "Mengunggah berkas penting: {$data['nama_berkas']}");

            return JsonResponse::create(['success' => true, 'message' => 'Berkas berhasil diupload ke: ' . $filePath]);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
