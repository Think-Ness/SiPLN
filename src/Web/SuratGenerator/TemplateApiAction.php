<?php

declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use App\Shared\UploadPath;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class TemplateApiAction
{
    public function __construct(private ConnectionInterface $db, private CurrentRoute $currentRoute)
    {
    }

    /**
     * List all dynamic templates
     */
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        @session_start();
        $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
        if (!$targetInstansiId) {
            $targetInstansiId = (int) $this->db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
        }

        $templates = $this->db->createCommand("
            SELECT * FROM surat_template_dinamis
            WHERE instansi_id = :instansi_id
            ORDER BY instansi_tujuan ASC, nama_template ASC
        ", [':instansi_id' => $targetInstansiId])->queryAll();

        return JsonResponse::create(['success' => true, 'data' => $templates]);
    }

    /**
     * Upload a new template
     */
    public function upload(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $namaTemplate = trim($parsedBody['nama_template'] ?? '');
        $instansiTujuan = trim($parsedBody['instansi_tujuan'] ?? '');
        $peruntukan = $parsedBody['peruntukan'] ?? 'perseorangan';

        if ($namaTemplate === '' || $instansiTujuan === '' || empty($uploadedFiles['file'])) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak lengkap atau file tidak ditemukan.'], 400);
        }

        /** @var \Psr\Http\Message\UploadedFileInterface $file */
        $file = $uploadedFiles['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal mengunggah file.'], 400);
        }

        $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if ($extension !== 'docx') {
            return JsonResponse::create(['success' => false, 'message' => 'File harus berupa dokumen Word (.docx).'], 400);
        }

        // Sanitize folder name for instansi tujuan
        $folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $instansiTujuan);
        // Trim trailing/leading underscores
        $folderName = trim($folderName, '_');

        // Resolve base per instansi
        $instansiBase = UploadPath::getBase($this->db);
        if ($instansiBase === null) {
            return JsonResponse::create(['success' => false, 'message' => UploadPath::notConfiguredMessage()], 400);
        }
        $baseUploadDir = $instansiBase . '/Surat_Menyurat';
        $instansiDir = $baseUploadDir . '/' . $folderName;

        if (!is_dir($instansiDir)) {
            if (!mkdir($instansiDir, 0777, true) && !is_dir($instansiDir)) {
                return JsonResponse::create(['success' => false, 'message' => 'Gagal membuat folder instansi tujuan.'], 500);
            }
        }

        // Generate filename: {NamaTemplate}_{Peruntukan}.docx
        $safeTemplateName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $namaTemplate);
        $safeTemplateName = preg_replace('/_+/', '_', $safeTemplateName);
        $safeTemplateName = trim($safeTemplateName, '_');
        
        $suffixMap = [
            'perseorangan' => 'Satu_Orang',
            'sekaligus' => 'Banyak_Orang',
        ];
        $suffix = $suffixMap[$peruntukan] ?? 'Satu_Orang';
        
        $filename = $safeTemplateName . '_' . $suffix . '.docx';
        $targetPath = $instansiDir . '/' . $filename;
        $isOverwrite = ($parsedBody['overwrite'] ?? '0') === '1';

        // Check if file already exists
        if (file_exists($targetPath) && !$isOverwrite) {
            return JsonResponse::create([
                'success' => false, 
                'message' => "Template dengan nama file '$filename' sudah ada di folder '$folderName'. Silakan hapus dulu template lama atau centang opsi timpa file."
            ], 400);
        }

        // Store as uploads/{kepengurusan}/Surat_Menyurat/... as requested by user
        $normalizedBase = rtrim(str_replace('\\', '/', $instansiBase), '/');
        $kepengurusan = basename($normalizedBase); // e.g. 'Ponorogo' atau 'berkas'
        $relativePath = 'uploads/' . $kepengurusan . '/Surat_Menyurat/' . $folderName . '/' . $filename;

        $jsonDataCollection = $parsedBody['json_data_collection'] ?? null;
        if ($jsonDataCollection && !is_string($jsonDataCollection)) {
            $jsonDataCollection = json_encode($jsonDataCollection);
        }

        $jsonCustomInputs = $parsedBody['json_custom_inputs'] ?? null;
        if ($jsonCustomInputs && !is_string($jsonCustomInputs)) {
            $jsonCustomInputs = json_encode($jsonCustomInputs);
        }

        $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
        if (!$targetInstansiId) {
            $targetInstansiId = (int) $this->db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
        }

        try {
            $file->moveTo($targetPath);

            // Insert to DB or update if overwrite
            if ($isOverwrite) {
                // Check if it exists in DB to update timestamp (optional)
                $existing = $this->db->createCommand("SELECT id FROM surat_template_dinamis WHERE file_path = :path AND instansi_id = :instansi_id", [':path' => $relativePath, ':instansi_id' => $targetInstansiId])->queryOne();
                if (!$existing) {
                     $this->db->createCommand("
                        INSERT INTO surat_template_dinamis (instansi_id, nama_template, file_path, instansi_tujuan, peruntukan, json_data_collection, json_custom_inputs)
                        VALUES (:instansi_id, :nama, :path, :instansi, :peruntukan, :json_data, :json_custom)
                    ", [
                        ':instansi_id' => $targetInstansiId,
                        ':nama' => $namaTemplate,
                        ':path' => $relativePath,
                        ':instansi' => $instansiTujuan,
                        ':peruntukan' => $peruntukan,
                        ':json_data' => $jsonDataCollection,
                        ':json_custom' => $jsonCustomInputs
                    ])->execute();
                } else {
                     $this->db->createCommand("
                        UPDATE surat_template_dinamis 
                        SET nama_template = :nama, instansi_tujuan = :instansi, peruntukan = :peruntukan, json_data_collection = :json_data, json_custom_inputs = :json_custom 
                        WHERE id = :id
                    ", [
                        ':nama' => $namaTemplate,
                        ':instansi' => $instansiTujuan,
                        ':peruntukan' => $peruntukan,
                        ':json_data' => $jsonDataCollection,
                        ':json_custom' => $jsonCustomInputs,
                        ':id' => $existing['id']
                    ])->execute();
                }
            } else {
                $this->db->createCommand("
                    INSERT INTO surat_template_dinamis (instansi_id, nama_template, file_path, instansi_tujuan, peruntukan, json_data_collection, json_custom_inputs)
                    VALUES (:instansi_id, :nama, :path, :instansi, :peruntukan, :json_data, :json_custom)
                ", [
                    ':instansi_id' => $targetInstansiId,
                    ':nama' => $namaTemplate,
                    ':path' => $relativePath,
                    ':instansi' => $instansiTujuan,
                    ':peruntukan' => $peruntukan,
                    ':json_data' => $jsonDataCollection,
                    ':json_custom' => $jsonCustomInputs
                ])->execute();
            }

            return JsonResponse::create(['success' => true, 'message' => "Template '$namaTemplate' berhasil " . ($isOverwrite ? "diperbarui" : "diunggah") . " ke folder '$folderName'."]);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menyimpan template: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a template
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int)$this->currentRoute->getArgument('id', '0');
        
        $template = $this->db->createCommand("SELECT file_path FROM surat_template_dinamis WHERE id = :id", [':id' => $id])->queryOne();

        if (!$template) {
            return JsonResponse::create(['success' => false, 'message' => 'Template tidak ditemukan.'], 404);
        }

        // Path di DB berupa uploads/{kepengurusan}/...
        $filePath = $template['file_path'];
        if (UploadPath::isAbsolutePath($filePath)) {
            $fullPath = $filePath;
        } else {
            $instansiBase = UploadPath::getBase($this->db);
            if ($instansiBase !== null) {
                $normalizedBase = rtrim(str_replace('\\', '/', $instansiBase), '/');
                $kepengurusan = basename($normalizedBase);
                $prefix = 'uploads/' . $kepengurusan . '/';
                if (str_starts_with($filePath, $prefix)) {
                    $stripped = substr($filePath, strlen($prefix));
                    $fullPath = $instansiBase . '/' . $stripped;
                } else {
                    $fullPath = dirname(__DIR__, 3) . '/public/' . ltrim($filePath, '/');
                }
            } else {
                $fullPath = dirname(__DIR__, 3) . '/public/' . ltrim($filePath, '/');
            }
        }
        
        try {
            $this->db->createCommand("DELETE FROM surat_template_dinamis WHERE id = :id", [':id' => $id])->execute();
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            return JsonResponse::create(['success' => true, 'message' => 'Template berhasil dihapus.']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Open template using OS default application (Word)
     */
    public function openInWord(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int)$this->currentRoute->getArgument('id', '0');
        
        $template = $this->db->createCommand("SELECT file_path FROM surat_template_dinamis WHERE id = :id", [':id' => $id])->queryOne();

        if (!$template) {
            return JsonResponse::create(['success' => false, 'message' => 'Template tidak ditemukan.'], 404);
        }

        // Path di DB berupa uploads/{kepengurusan}/...
        $filePath = $template['file_path'];
        if (UploadPath::isAbsolutePath($filePath)) {
            $fullPath = str_replace('\\', '/', $filePath);
        } else {
            $instansiBase = UploadPath::getBase($this->db);
            if ($instansiBase !== null) {
                $normalizedBase = rtrim(str_replace('\\', '/', $instansiBase), '/');
                $kepengurusan = basename($normalizedBase);
                $prefix = 'uploads/' . $kepengurusan . '/';
                if (str_starts_with($filePath, $prefix)) {
                    $stripped = substr($filePath, strlen($prefix));
                    $fullPath = $instansiBase . '/' . $stripped;
                } else {
                    $fullPath = dirname(__DIR__, 3) . '/public/' . ltrim($filePath, '/');
                }
            } else {
                $fullPath = dirname(__DIR__, 3) . '/public/' . ltrim($filePath, '/');
            }
        }
        
        if (!file_exists($fullPath)) {
            return JsonResponse::create(['success' => false, 'message' => 'File fisik tidak ditemukan: ' . $fullPath], 404);
        }

        try {
            $windowsPath = str_replace('/', '\\', $fullPath);
            exec('start "" "' . $windowsPath . '"');
            return JsonResponse::create(['success' => true, 'message' => 'Template berhasil dibuka di Word.']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal membuka Word: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Scan Surat_Menyurat/ folders to get instansi tujuan list
     */
    public function instansiTujuanList(ServerRequestInterface $request): ResponseInterface
    {
        $instansiBase = UploadPath::getBase($this->db);
        $suratDir = $instansiBase !== null ? $instansiBase . '/Surat_Menyurat' : dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';
        
        // Ensure default 3 folders exist
        $defaultFolders = ['Imigrasi', 'Kemenag', 'Lain-Lain'];
        if (!is_dir($suratDir)) {
            @mkdir($suratDir, 0777, true);
        }
        foreach ($defaultFolders as $df) {
            $dfPath = $suratDir . '/' . $df;
            if (!is_dir($dfPath)) {
                @mkdir($dfPath, 0777, true);
            }
        }

        $folders = [];
        if (is_dir($suratDir)) {
            $items = scandir($suratDir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..' || $item === 'Output') continue;
                if (is_dir($suratDir . '/' . $item)) {
                    $folders[] = $item;
                }
            }
        }
        
        // Gabungkan juga dengan data yang sudah ada di database (kantor dari jenis pengajuan)
        $dbInstansi = $this->db->createCommand("SELECT DISTINCT kantor FROM surat_jenis_pengajuan WHERE kantor IS NOT NULL AND kantor != ''")->queryColumn();
        
        @session_start();
        $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
        if (!$targetInstansiId) {
            $targetInstansiId = (int) $this->db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
        }
        
        $dbTemplateInstansi = $this->db->createCommand("SELECT DISTINCT instansi_tujuan FROM surat_template_dinamis WHERE instansi_tujuan IS NOT NULL AND instansi_tujuan != '' AND instansi_id = :instansi_id", [':instansi_id' => $targetInstansiId])->queryColumn();
        
        $all = array_unique(array_merge($folders, $dbInstansi, $dbTemplateInstansi));
        $finalList = array_values($all);
        sort($finalList);
        
        return JsonResponse::create(['success' => true, 'data' => $finalList]);
    }

    /**
     * Scan a specific instansi tujuan folder for available .docx templates
     */
    public function instansiTujuanTemplates(ServerRequestInterface $request): ResponseInterface
    {
        $nama = $this->currentRoute->getArgument('nama', '');
        if (empty($nama)) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama instansi tujuan kosong.'], 400);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nama);
        $instansiBase = UploadPath::getBase($this->db);
        $folderPath = ($instansiBase !== null ? $instansiBase : dirname(__DIR__, 3) . '/public/uploads') . '/Surat_Menyurat/' . $safeName;
        
        @session_start();
        $targetInstansiId = !empty($_SESSION['instansi_id']) ? (int)$_SESSION['instansi_id'] : null;
        if (!$targetInstansiId) {
            $targetInstansiId = (int) $this->db->createCommand("SELECT kode FROM master_instansi WHERE def_kepengurusan LIKE '%Ponorogo%' ORDER BY kode ASC LIMIT 1")->queryScalar();
        }
        
        $dbTemplates = $this->db->createCommand(
            "SELECT nama_template, json_data_collection FROM surat_template_dinamis WHERE instansi_tujuan = :inst AND instansi_id = :instansi_id", 
            [':inst' => $nama, ':instansi_id' => $targetInstansiId]
        )->queryAll();
        
        $dbMap = [];
        foreach ($dbTemplates as $dbTpl) {
            $safeTplName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $dbTpl['nama_template']);
            $dbMap[$safeTplName] = $dbTpl['json_data_collection'];
        }

        $templates = [];
        if (is_dir($folderPath)) {
            $files = scandir($folderPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'docx') continue;
                if (strpos($file, '~$') === 0) continue; // skip Word temp files
                
                $baseName = pathinfo($file, PATHINFO_FILENAME);
                $peruntukan = 'unknown';
                $displayName = $baseName;
                
                if (preg_match('/^(.+)_(Satu_Orang|Banyak_Orang)$/', $baseName, $m)) {
                    $displayName = str_replace('_', ' ', $m[1]);
                    $peruntukan = $m[2] === 'Satu_Orang' ? 'perseorangan' : 'sekaligus';
                    $coreName = $m[1];
                } else {
                    $displayName = str_replace('_', ' ', $baseName);
                    $coreName = $baseName;
                }

                $jsonData = $dbMap[$coreName] ?? null;

                $templates[] = [
                    'filename' => $file,
                    'basename' => $baseName,
                    'core_name' => $coreName,
                    'display_name' => $displayName,
                    'peruntukan' => $peruntukan,
                    'json_data_collection' => $jsonData
                ];
            }
        }

        return JsonResponse::create(['success' => true, 'data' => $templates, 'folder' => $safeName]);
    }
}
