<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use Yiisoft\Router\CurrentRoute;

class PengeluaranAction
{
    /**
     * GET /api/job-desk/pengeluaran — List all pengeluaran for current instansi
     */
    public function list(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $role = $_SESSION['role'] ?? '';

        $where = "WHERE 1=1";
        $params = [];
        if ($role !== 'super_admin' && $instansiId) {
            $where .= " AND p.instansi_id = :instansi_id";
            $params[':instansi_id'] = $instansiId;
        }

        $rows = $db->createCommand("
            SELECT p.*, u.username as created_by_name
            FROM jobdesk_pengeluaran_birokrasi p
            LEFT JOIN users u ON p.created_by = u.username
            $where
            ORDER BY p.tanggal DESC, p.created_at DESC
        ", $params)->queryAll();

        return JsonResponse::create(['success' => true, 'data' => $rows]);
    }

    /**
     * POST /api/job-desk/pengeluaran/create — Create a new pengeluaran entry
     */
    public function create(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $username   = $_SESSION['username'] ?? 'system';

        // Parse multipart form data
        $data  = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        $nominal    = (float)($data['nominal'] ?? 0);
        $keterangan = trim((string)($data['keterangan'] ?? ''));
        $kategori   = trim((string)($data['kategori'] ?? 'Lainnya'));
        $tanggal    = trim((string)($data['tanggal'] ?? date('Y-m-d')));

        if ($nominal <= 0) {
            return JsonResponse::create(['success' => false, 'message' => 'Nominal harus lebih dari 0.'], 400);
        }
        if (empty($keterangan)) {
            return JsonResponse::create(['success' => false, 'message' => 'Keterangan wajib diisi.'], 400);
        }

        // Handle foto nota upload
        $fotoPath = null;
        if (isset($files['foto_nota']) && $files['foto_nota']->getError() === UPLOAD_ERR_OK) {
            $file = $files['foto_nota'];
            $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!in_array($ext, $allowed)) {
                return JsonResponse::create(['success' => false, 'message' => 'Format file tidak didukung. Gunakan: ' . implode(', ', $allowed)], 400);
            }

            $instansiInfo = $instansiId ? $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :kode", [':kode' => $instansiId])->queryOne() : null;
            $folderInstansi = $instansiInfo && !empty($instansiInfo['path_folder']) ? $instansiInfo['path_folder'] : ($instansiId ? $instansiId : 'global');
            
            $isAbsolute = str_starts_with(str_replace('\\', '/', $folderInstansi), 'D:/') || str_starts_with(str_replace('\\', '/', $folderInstansi), 'C:/') || str_starts_with($folderInstansi, '/');
            if ($isAbsolute) {
                $dir = rtrim($folderInstansi, '\\/') . '/nota/operasional birokrasi';
            } else {
                $dir = dirname(__DIR__, 3) . '/public/uploads/instansi/' . $folderInstansi . '/nota/operasional birokrasi';
            }
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $filename = 'nota_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->moveTo($dir . '/' . $filename);
            $fotoPath = $filename;
        }

        $db->createCommand()->insert('jobdesk_pengeluaran_birokrasi', [
            'instansi_id' => $instansiId,
            'nominal'     => $nominal,
            'keterangan'  => $keterangan,
            'kategori'    => $kategori,
            'tanggal'     => $tanggal,
            'foto_nota'   => $fotoPath,
            'created_by'  => $username,
        ])->execute();

        $newId = $db->getLastInsertID();

        return JsonResponse::create([
            'success' => true,
            'message' => 'Pengeluaran berhasil dicatat.',
            'id'      => $newId,
        ]);
    }

    /**
     * POST /api/job-desk/pengeluaran/{id}/update — Update pengeluaran
     */
    public function update(
        CurrentRoute $currentRoute,
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = (int)$currentRoute->getArgument('id');

        $data  = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        $updateFields = [];

        if (isset($data['nominal'])) {
            $updateFields['nominal'] = (float)$data['nominal'];
        }
        if (isset($data['keterangan'])) {
            $updateFields['keterangan'] = trim((string)$data['keterangan']);
        }
        if (isset($data['kategori'])) {
            $updateFields['kategori'] = trim((string)$data['kategori']);
        }
        if (isset($data['tanggal'])) {
            $updateFields['tanggal'] = trim((string)$data['tanggal']);
        }

        $existing = $db->createCommand("SELECT instansi_id FROM jobdesk_pengeluaran_birokrasi WHERE id = :id", [':id' => $id])->queryOne();
        $recordInstansiId = $existing ? $existing['instansi_id'] : null;

        // Handle foto update
        if (isset($files['foto_nota']) && $files['foto_nota']->getError() === UPLOAD_ERR_OK) {
            $file = $files['foto_nota'];
            $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!in_array($ext, $allowed)) {
                return JsonResponse::create(['success' => false, 'message' => 'Format file tidak didukung.'], 400);
            }

            $instansiInfo = $recordInstansiId ? $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :kode", [':kode' => $recordInstansiId])->queryOne() : null;
            $folderInstansi = $instansiInfo && !empty($instansiInfo['path_folder']) ? $instansiInfo['path_folder'] : ($recordInstansiId ? $recordInstansiId : 'global');

            $isAbsolute = str_starts_with(str_replace('\\', '/', $folderInstansi), 'D:/') || str_starts_with(str_replace('\\', '/', $folderInstansi), 'C:/') || str_starts_with($folderInstansi, '/');
            if ($isAbsolute) {
                $dir = rtrim($folderInstansi, '\\/') . '/nota/operasional birokrasi';
            } else {
                $dir = dirname(__DIR__, 3) . '/public/uploads/instansi/' . $folderInstansi . '/nota/operasional birokrasi';
            }
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $filename = 'nota_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->moveTo($dir . '/' . $filename);
            $updateFields['foto_nota'] = $filename;
        }

        if (empty($updateFields)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data yang diubah.'], 400);
        }

        $db->createCommand()->update('jobdesk_pengeluaran_birokrasi', $updateFields, ['id' => $id])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Pengeluaran berhasil diperbarui.']);
    }

    /**
     * GET /api/job-desk/pengeluaran/view-nota/{id} — View nota image securely
     */
    public function viewNota(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = (int)$currentRoute->getArgument('id');
        $pg = $db->createCommand("SELECT foto_nota, instansi_id FROM jobdesk_pengeluaran_birokrasi WHERE id = :id", [':id' => $id])->queryOne();

        if (!$pg || empty($pg['foto_nota'])) {
            return new \HttpSoft\Message\Response(404);
        }

        $filename = basename($pg['foto_nota']);
        $instansiId = $pg['instansi_id'];

        $instansiInfo = $instansiId ? $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :kode", [':kode' => $instansiId])->queryOne() : null;
        $folderInstansi = $instansiInfo && !empty($instansiInfo['path_folder']) ? $instansiInfo['path_folder'] : ($instansiId ? $instansiId : 'global');

        $isAbsolute = str_starts_with(str_replace('\\', '/', $folderInstansi), 'D:/') || str_starts_with(str_replace('\\', '/', $folderInstansi), 'C:/') || str_starts_with($folderInstansi, '/');
        if ($isAbsolute) {
            $dir = rtrim($folderInstansi, '\\/') . '/nota/operasional birokrasi';
        } else {
            $dir = dirname(__DIR__, 3) . '/public/uploads/instansi/' . $folderInstansi . '/nota/operasional birokrasi';
        }

        $path = $dir . '/' . $filename;
        
        // Backward compatibility for old paths stored in DB (starting with /uploads/...)
        if (str_starts_with($pg['foto_nota'], '/uploads/')) {
            $path = dirname(__DIR__, 3) . '/public' . $pg['foto_nota'];
        }

        if (!file_exists($path) || !is_file($path)) {
            return new \HttpSoft\Message\Response(404);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };

        $response = new \HttpSoft\Message\Response(200);
        $response = $response->withHeader('Content-Type', $mime)
                             ->withHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
                             
        $stream = fopen($path, 'rb');
        if ($stream !== false) {
            $response = $response->withBody(new \HttpSoft\Message\Stream($stream));
        } else {
            $response->getBody()->write(file_get_contents($path));
        }

        return $response;
    }

    /**
     * POST /api/job-desk/pengeluaran/{id}/delete — Delete pengeluaran
     */
    public function delete(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = (int)$currentRoute->getArgument('id');

        // Optionally delete associated file
        $existing = $db->createCommand(
            "SELECT foto_nota FROM jobdesk_pengeluaran_birokrasi WHERE id = :id",
            [':id' => $id]
        )->queryOne();

        if ($existing && !empty($existing['foto_nota'])) {
            $filePath = __DIR__ . '/../../../../public' . $existing['foto_nota'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $db->createCommand()->delete('jobdesk_pengeluaran_birokrasi', ['id' => $id])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Pengeluaran berhasil dihapus.']);
    }

    /**
     * POST /api/job-desk/pengeluaran/kategori/create — Create a new category
     */
    public function createKategori(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $data = $request->getParsedBody() ?? [];
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) $data = json_decode($rawBody, true) ?? [];
        }

        $nama = trim((string)($data['nama'] ?? ''));
        $warna = trim((string)($data['warna'] ?? '#0d6efd'));

        if (empty($nama)) {
            return JsonResponse::create(['success' => false, 'message' => 'Nama kategori tidak boleh kosong.'], 400);
        }

        $db->createCommand()->insert('jobdesk_kategori_pengeluaran', [
            'instansi_id' => $instansiId,
            'nama' => $nama,
            'warna' => $warna,
            'icon' => 'bi-tag',
        ])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Kategori berhasil ditambahkan.']);
    }

    /**
     * POST /api/job-desk/pengeluaran/kategori/{id}/delete — Delete a category
     */
    public function deleteKategori(
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = (int)$currentRoute->getArgument('id');

        // Allow deletion by setting is_active = 0 or hard delete. Hard delete here:
        $db->createCommand()->delete('jobdesk_kategori_pengeluaran', ['id' => $id])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }
}

