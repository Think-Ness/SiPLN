<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use App\Shared\AuditLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class JenisPengajuanAction
{
    public function list(ConnectionInterface $db): ResponseInterface
    {
        $data = $db->createCommand("SELECT * FROM surat_jenis_pengajuan ORDER BY jenis_pengajuan ASC")->queryAll();
        return JsonResponse::create(['success' => true, 'data' => $data]);
    }

    public function store(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }
        
        $jenisPengajuan = trim($body['jenis_pengajuan'] ?? '');
        $hal = trim($body['hal'] ?? '');
        $isi = trim($body['isi'] ?? '');
        $kepada = trim($body['kepada'] ?? '');
        $tempat = trim($body['tempat'] ?? '');
        $suratDibutuhkan = trim($body['surat_dibutuhkan'] ?? 'SP,SK,SJ,ST');
        $kantor = trim($body['kantor'] ?? '');

        if (empty($jenisPengajuan)) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama jenis pengajuan wajib diisi.'], 400);
        }

        $instansiId = $_SESSION['instansi_id'] ?? null;
        $templatePath = null;

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles['template_file']) && $uploadedFiles['template_file']->getError() === UPLOAD_ERR_OK) {
            $file = $uploadedFiles['template_file'];
            $filename = $file->getClientFilename();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if ($ext !== 'docx') {
                return JsonResponse::create(['success' => false, 'message' => 'File template harus berupa .docx'], 400);
            }
            
            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/templates';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $newFilename = 'template_' . time() . '_' . rand(1000, 9999) . '.docx';
            $destPath = $uploadDir . '/' . $newFilename;
            $file->moveTo($destPath);
            $templatePath = 'public/uploads/templates/' . $newFilename;
        }

        $db->createCommand(
            "INSERT INTO surat_jenis_pengajuan (jenis_pengajuan, hal, isi, kepada, tempat, surat_dibutuhkan, kantor, output_path, instansi_id, template_path)
             VALUES (:jp, :hal, :isi, :kepada, :tempat, :sd, :kantor, :out, :inst, :tpl)",
            [
                ':jp' => $jenisPengajuan,
                ':hal' => $hal,
                ':isi' => $isi,
                ':kepada' => $kepada,
                ':tempat' => $tempat,
                ':sd' => $suratDibutuhkan,
                ':kantor' => $kantor,
                ':out' => trim($body['output_path'] ?? ''),
                ':inst' => $instansiId,
                ':tpl' => $templatePath,
            ]
        )->execute();

        $id = (int)$db->getLastInsertID();
        AuditLogger::log($db, 'CREATE', 'JENIS_PENGAJUAN', (string)$id, null, "Tambah jenis pengajuan: $jenisPengajuan");

        return JsonResponse::create(['success' => true, 'message' => "Jenis pengajuan '$jenisPengajuan' berhasil ditambahkan.", 'id' => $id]);
    }

    public function update(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface
    {
        $id = (int)$currentRoute->getArgument('id', '0');
        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }

        $oldData = $db->createCommand("SELECT template_path FROM surat_jenis_pengajuan WHERE id = :id", [':id' => $id])->queryOne();
        if (!$oldData) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $templatePath = $oldData['template_path'];

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles['template_file']) && $uploadedFiles['template_file']->getError() === UPLOAD_ERR_OK) {
            $file = $uploadedFiles['template_file'];
            $filename = $file->getClientFilename();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if ($ext !== 'docx') {
                return JsonResponse::create(['success' => false, 'message' => 'File template harus berupa .docx'], 400);
            }
            
            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/templates';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $newFilename = 'template_' . time() . '_' . rand(1000, 9999) . '.docx';
            $destPath = $uploadDir . '/' . $newFilename;
            $file->moveTo($destPath);
            
            // Delete old template if exists
            if (!empty($templatePath)) {
                $oldFile = dirname(__DIR__, 4) . '/' . $templatePath;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            
            $templatePath = 'public/uploads/templates/' . $newFilename;
        }

        $db->createCommand(
            "UPDATE surat_jenis_pengajuan SET
                jenis_pengajuan = :jp, hal = :hal, isi = :isi, kepada = :kepada,
                tempat = :tempat, surat_dibutuhkan = :sd, kantor = :kantor, output_path = :out, template_path = :tpl
             WHERE id = :id",
            [
                ':jp' => trim($body['jenis_pengajuan'] ?? ''),
                ':hal' => trim($body['hal'] ?? ''),
                ':isi' => trim($body['isi'] ?? ''),
                ':kepada' => trim($body['kepada'] ?? ''),
                ':tempat' => trim($body['tempat'] ?? ''),
                ':sd' => trim($body['surat_dibutuhkan'] ?? 'SP,SK,SJ,ST'),
                ':kantor' => trim($body['kantor'] ?? ''),
                ':out' => trim($body['output_path'] ?? ''),
                ':tpl' => $templatePath,
                ':id' => $id,
            ]
        )->execute();

        AuditLogger::log($db, 'UPDATE', 'JENIS_PENGAJUAN', (string)$id, null, "Update jenis pengajuan ID $id");

        return JsonResponse::create(['success' => true, 'message' => 'Jenis pengajuan berhasil diperbarui.']);
    }

    public function delete(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface
    {
        $id = (int)$currentRoute->getArgument('id', '0');

        // Check if used in mailing
        $used = (int)$db->createCommand(
            "SELECT COUNT(*) FROM surat_mailing WHERE jenis_pengajuan_id = :id",
            [':id' => $id]
        )->queryScalar();

        if ($used > 0) {
            $db->createCommand("UPDATE surat_jenis_pengajuan SET aktif = 0 WHERE id = :id", [':id' => $id])->execute();
            AuditLogger::log($db, 'UPDATE', 'JENIS_PENGAJUAN', (string)$id, null, "Nonaktifkan jenis pengajuan ID $id (sudah digunakan di $used mailing)");
            return JsonResponse::create(['success' => true, 'message' => 'Jenis pengajuan dinonaktifkan karena masih digunakan di mailing.']);
        }

        $db->createCommand("DELETE FROM surat_jenis_pengajuan WHERE id = :id", [':id' => $id])->execute();
        AuditLogger::log($db, 'DELETE', 'JENIS_PENGAJUAN', (string)$id, null, "Hapus jenis pengajuan ID $id");

        return JsonResponse::create(['success' => true, 'message' => 'Jenis pengajuan berhasil dihapus.']);
    }
}
