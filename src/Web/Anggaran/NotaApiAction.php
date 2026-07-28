<?php

declare(strict_types=1);

namespace App\Web\Anggaran;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

class NotaApiAction
{
    public function list(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $pengajuanId = (int) $currentRoute->getArgument('id');
        $notas = $db->createCommand("SELECT * FROM anggaran_nota WHERE pengajuan_id = :id ORDER BY id ASC", [':id' => $pengajuanId])->queryAll();
        
        foreach ($notas as &$n) {
            $n['items'] = $db->createCommand("SELECT * FROM anggaran_nota_items WHERE nota_id = :id", [':id' => $n['id']])->queryAll();
        }

        return JsonResponse::create(['success' => true, 'data' => $notas]);
    }

    public function store(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pengajuanId = (int) $currentRoute->getArgument('id');
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $nomorNota = $data['nomor_nota'] ?? '';
        $tanggal = $data['tanggal'] ?? date('Y-m-d');
        $keterangan = $data['keterangan'] ?? '';
        $bagianNota = $data['bagian'] ?? '';
        $items = $data['items'] ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        $filePath = null;
        if (isset($files['file_nota']) && $files['file_nota']->getError() === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['file_nota']->getClientFilename(), PATHINFO_EXTENSION);
            $safeName = 'Nota_' . $pengajuanId . '_' . time() . '.' . $ext;
            
            $pengajuan = $db->createCommand("SELECT instansi FROM anggaran_pengajuan WHERE id = :id", [':id' => $pengajuanId])->queryOne();
            $instansiKode = $pengajuan ? $pengajuan['instansi'] : null;
            $instansiInfo = $instansiKode ? $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :kode OR nama_instansi = :kode", [':kode' => $instansiKode])->queryOne() : null;
            $folderInstansi = $instansiInfo && !empty($instansiInfo['path_folder']) ? $instansiInfo['path_folder'] : ($instansiKode ? preg_replace('/[^a-zA-Z0-9]/', '_', $instansiKode) : 'global');

            $isAbsolute = str_starts_with(str_replace('\\', '/', $folderInstansi), 'D:/') || str_starts_with(str_replace('\\', '/', $folderInstansi), 'C:/') || str_starts_with($folderInstansi, '/');
            if ($isAbsolute) {
                $dir = rtrim($folderInstansi, '\\/') . '/nota/anggaran_operasional';
            } else {
                $dir = dirname(__DIR__, 3) . '/public/uploads/instansi/' . $folderInstansi . '/nota/anggaran_operasional';
            }
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $fullPath = $dir . DIRECTORY_SEPARATOR . $safeName;
            $files['file_nota']->moveTo($fullPath);
            $filePath = $safeName;
        }

        $transaction = $db->beginTransaction();
        try {
            $totalBelanja = 0;
            foreach ($items as $item) {
                $subtotal = (int)$item['qty'] * (float)$item['harga_satuan'];
                $totalBelanja += $subtotal;
            }

            $notaId = $data['nota_id'] ?? null;
            if ($notaId) {
                $db->createCommand()->delete('anggaran_nota_items', ['nota_id' => $notaId])->execute();
                
                $updateData = [
                    'nomor_nota' => $nomorNota,
                    'tanggal' => $tanggal,
                    'bagian' => $bagianNota,
                    'total_belanja' => $totalBelanja,
                    'keterangan' => $keterangan,
                ];
                if ($filePath) {
                    $updateData['file_path'] = $filePath;
                }
                $db->createCommand()->update('anggaran_nota', $updateData, ['id' => $notaId])->execute();
            } else {
                $db->createCommand()->insert('anggaran_nota', [
                    'pengajuan_id' => $pengajuanId,
                    'nomor_nota' => $nomorNota,
                    'tanggal' => $tanggal,
                    'bagian' => $bagianNota,
                    'total_belanja' => $totalBelanja,
                    'file_path' => $filePath,
                    'keterangan' => $keterangan,
                    'created_by' => $_SESSION['user_id'] ?? null,
                ])->execute();
                $notaId = $db->getLastInsertID();
            }

            foreach ($items as $item) {
                $qty = (int)$item['qty'];
                $harga = (float)$item['harga_satuan'];
                $db->createCommand()->insert('anggaran_nota_items', [
                    'nota_id' => $notaId,
                    'nama_barang' => $item['nama_barang'],
                    'qty' => $qty,
                    'satuan' => $item['satuan'] ?? '',
                    'bagian' => $item['bagian'] ?? '',
                    'harga_satuan' => $harga,
                    'subtotal' => $qty * $harga,
                ])->execute();
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Laporan Nota berhasil disimpan']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        $nota = $db->createCommand("SELECT file_path FROM anggaran_nota WHERE id=:id", [':id' => $id])->queryOne();
        
        $db->createCommand()->delete('anggaran_nota', ['id' => $id])->execute();
        
        if ($nota && !empty($nota['file_path'])) {
            $fullPath = dirname(__DIR__, 3) . '/public' . $nota['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        return JsonResponse::create(['success' => true, 'message' => 'Nota dihapus']);
    }

    /**
     * GET /api/anggaran/view-nota/{id}
     */
    public function viewNota(
        \Yiisoft\Router\CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        $nota = $db->createCommand("SELECT file_path, pengajuan_id FROM anggaran_nota WHERE id = :id", [':id' => $id])->queryOne();

        if (!$nota || empty($nota['file_path'])) {
            return new \HttpSoft\Message\Response(404);
        }

        $filename = basename($nota['file_path']);
        $pengajuanId = $nota['pengajuan_id'];

        $pengajuan = $db->createCommand("SELECT instansi FROM anggaran_pengajuan WHERE id = :id", [':id' => $pengajuanId])->queryOne();
        $instansiKode = $pengajuan ? $pengajuan['instansi'] : null;
        $instansiInfo = $instansiKode ? $db->createCommand("SELECT path_folder FROM master_instansi WHERE kode = :kode OR nama_instansi = :kode", [':kode' => $instansiKode])->queryOne() : null;
        $folderInstansi = $instansiInfo && !empty($instansiInfo['path_folder']) ? $instansiInfo['path_folder'] : ($instansiKode ? preg_replace('/[^a-zA-Z0-9]/', '_', $instansiKode) : 'global');

        $isAbsolute = str_starts_with(str_replace('\\', '/', $folderInstansi), 'D:/') || str_starts_with(str_replace('\\', '/', $folderInstansi), 'C:/') || str_starts_with($folderInstansi, '/');
        if ($isAbsolute) {
            $dir = rtrim($folderInstansi, '\\/') . '/nota/anggaran_operasional';
        } else {
            $dir = dirname(__DIR__, 3) . '/public/uploads/instansi/' . $folderInstansi . '/nota/anggaran_operasional';
        }

        $path = $dir . '/' . $filename;
        
        // Backward compatibility
        if (str_starts_with($nota['file_path'], '/uploads/')) {
            $path = dirname(__DIR__, 3) . '/public' . $nota['file_path'];
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
}
