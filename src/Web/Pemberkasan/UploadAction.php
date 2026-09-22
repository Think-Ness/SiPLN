<?php declare(strict_types=1);
namespace App\Web\Pemberkasan;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;
use App\Shared\UploadPath;

final class UploadAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $data  = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        $namaBerkas = trim((string)($data['nama_berkas'] ?? ''));
        if ($namaBerkas === '') {
            return JsonResponse::create(['success' => false, 'message' => 'Nama berkas wajib diisi'], 422);
        }

        if (!isset($files['berkas_file']) || $files['berkas_file']->getError() !== UPLOAD_ERR_OK) {
            $err = isset($files['berkas_file']) ? $files['berkas_file']->getError() : UPLOAD_ERR_NO_FILE;
            $msg = match($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar (melebihi batas server)',
                UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian, silakan coba lagi',
                UPLOAD_ERR_NO_FILE => 'File dokumen wajib dipilih dan diunggah',
                default => 'Gagal menerima file yang diunggah (Kode error: ' . $err . ')'
            };
            return JsonResponse::create(['success' => false, 'message' => $msg], 422);
        }

        $isPublic = isset($data['is_public']) && (string)$data['is_public'] === '1' ? 1 : 0;
        
        $role = $_SESSION['role'] ?? '';
        $instansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
        
        // Cari kode instansi yang sesuai
        $targetKode = $instansiId;
        if ($role === 'super_admin' && !empty($data['target_kode'])) {
            $targetKode = (int)$data['target_kode'];
        }
        
        if (empty($targetKode)) {
            $targetKode = (int) $db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
        }

        $kodeInstansi = (int)($targetKode ?: 1);

        // Resolve folder berkas penting sesuai instansi (otomatis \public\uploads\{Instansi}\berkas penting)
        try {
            $baseDir = UploadPath::getFolder($db, 'berkas penting', $kodeInstansi);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menentukan folder penyimpanan: ' . $e->getMessage()], 500);
        }

        $uploadedFile = $files['berkas_file'];
        $ext = strtolower(pathinfo($uploadedFile->getClientFilename() ?? '', PATHINFO_EXTENSION));
        
        // Security check file extension
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($ext, $allowedExts, true)) {
            return JsonResponse::create(['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya menerima: ' . implode(', ', $allowedExts)], 400);
        }
        
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $namaBerkas);
        $fileName = $safeName . '.' . $ext;
        if (file_exists($baseDir . '/' . $fileName)) {
            $fileName = $safeName . '_' . time() . '.' . $ext;
        }
        
        $fullPath = $baseDir . '/' . $fileName;
        
        try {
            $uploadedFile->moveTo($fullPath);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan file ke disk: ' . $e->getMessage()], 500);
        }

        try {
            $db->createCommand()->insert('mtb_berkas_penting', [
                'kode'        => $kodeInstansi,
                'nama_berkas' => $namaBerkas,
                'path_file'   => $fullPath,
                'is_public'   => $isPublic,
            ])->execute();
            
            $berkasId = $db->getLastInsertID();
            $newBerkas = $db->createCommand("SELECT * FROM mtb_berkas_penting WHERE id = :id", [':id' => $berkasId])->queryOne();
            AuditLogger::log($db, 'CREATE', 'PEMBERKASAN', $berkasId, null, $newBerkas, "Mengunggah berkas penting: {$namaBerkas}");

            return JsonResponse::create(['success' => true, 'message' => 'Berkas "' . $namaBerkas . '" berhasil diunggah!']);
        } catch (\Throwable $e) {
            // Rollback uploaded file on DB insert failure
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan data berkas ke database: ' . $e->getMessage()], 500);
        }
    }
}

