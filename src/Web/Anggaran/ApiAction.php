<?php

declare(strict_types=1);

namespace App\Web\Anggaran;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

class ApiAction
{
    public function store(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $data = $request->getParsedBody();
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $data = json_decode($rawBody, true) ?? [];
            }
        }

        $instansi = $_SESSION['def_pondok'] ?? '';
        if (($_SESSION['role'] ?? '') === 'super_admin' && !empty($data['instansi'])) {
            $instansi = $data['instansi'];
        }

        $bulanHijriah = $data['bulan_hijriah'] ?? '';
        $items = $data['items'] ?? []; // JSON string or array
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (empty($instansi) || empty($bulanHijriah) || empty($items)) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak lengkap'], 400);
        }

        $transaction = $db->beginTransaction();
        try {
            $totalAjuan = 0;
            foreach ($items as $item) {
                $totalAjuan += (float) ($item['nominal_ajuan'] ?? 0);
            }

            // Insert master
            $db->createCommand()->insert('anggaran_pengajuan', [
                'instansi' => $instansi,
                'bulan_hijriah' => $bulanHijriah,
                'status' => 'diajukan',
                'total_ajuan' => $totalAjuan,
                'created_by' => $_SESSION['user_id'] ?? null,
                'tanggal_pengajuan' => date('Y-m-d H:i:s'),
            ])->execute();

            $pengajuanId = $db->getLastInsertID();

            // Insert items
            foreach ($items as $item) {
                $db->createCommand()->insert('anggaran_pengajuan_items', [
                    'pengajuan_id' => $pengajuanId,
                    'nama_item' => $item['nama_item'],
                    'qty' => (int)($item['qty'] ?? 1),
                    'satuan' => $item['satuan'] ?? '',
                    'bagian' => $item['bagian'] ?? '',
                    'harga_satuan' => (float)($item['harga_satuan'] ?? 0),
                    'nominal_ajuan' => (float) ($item['nominal_ajuan'] ?? 0),
                    'nominal_disetujui' => 0, // default
                ])->execute();
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Pengajuan berhasil dikirim']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int) $currentRoute->getArgument('id');
        $data = $request->getParsedBody();
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $data = json_decode($rawBody, true) ?? [];
            }
        }

        $bulanHijriah = $data['bulan_hijriah'] ?? '';
        $items = $data['items'] ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (empty($bulanHijriah) || empty($items)) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak lengkap'], 400);
        }

        $transaction = $db->beginTransaction();
        try {
            $totalAjuan = 0;
            foreach ($items as $item) {
                $totalAjuan += (float) ($item['nominal_ajuan'] ?? 0);
            }

            $updateData = [
                'bulan_hijriah' => $bulanHijriah,
                'total_ajuan' => $totalAjuan
            ];

            if (($_SESSION['role'] ?? '') === 'super_admin' && !empty($data['instansi'])) {
                $updateData['instansi'] = $data['instansi'];
            }

            $db->createCommand()->update('anggaran_pengajuan', $updateData, ['id' => $id])->execute();

            // Clear old items
            $db->createCommand()->delete('anggaran_pengajuan_items', ['pengajuan_id' => $id])->execute();

            // Insert new items
            foreach ($items as $item) {
                $db->createCommand()->insert('anggaran_pengajuan_items', [
                    'pengajuan_id' => $id,
                    'nama_item' => $item['nama_item'],
                    'qty' => (int)($item['qty'] ?? 1),
                    'satuan' => $item['satuan'] ?? '',
                    'bagian' => $item['bagian'] ?? '',
                    'harga_satuan' => (float)($item['harga_satuan'] ?? 0),
                    'nominal_ajuan' => (float) ($item['nominal_ajuan'] ?? 0),
                    'nominal_disetujui' => 0,
                ])->execute();
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Pengajuan berhasil diperbarui']);
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
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->delete('anggaran_pengajuan_items', ['pengajuan_id' => $id])->execute();
            $db->createCommand()->delete('anggaran_nota', ['pengajuan_id' => $id])->execute();
            $db->createCommand()->delete('anggaran_pengajuan', ['id' => $id])->execute();
            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Pengajuan dihapus']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approve(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Self-Approval: No admin check required for internal recording
        $id = (int) $currentRoute->getArgument('id');
        $data = $request->getParsedBody();
        if (empty($data)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $data = json_decode($rawBody, true) ?? [];
            }
        }
        
        $status = $data['status'] ?? 'disetujui';
        $items = $data['items'] ?? []; // [{id, nominal_disetujui}]
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        $transaction = $db->beginTransaction();
        try {
            $totalDisetujui = 0;
            $updateData = [
                'status' => $status
            ];
            
            if (isset($data['catatan_admin'])) {
                $updateData['catatan_admin'] = $data['catatan_admin'];
            }
            
            if ($status === 'disetujui') {
                $updateData['tanggal_disetujui'] = date('Y-m-d H:i:s');
                if (isset($data['total_lumpsum'])) {
                    // Lump Sum Mode
                    $updateData['total_disetujui'] = (float) $data['total_lumpsum'];
                } else {
                    // Per Item Mode
                    foreach ($items as $item) {
                        $nominal = (float) ($item['nominal_disetujui'] ?? 0);
                        $totalDisetujui += $nominal;
                        $db->createCommand()->update('anggaran_pengajuan_items', [
                            'nominal_disetujui' => $nominal
                        ], ['id' => $item['id'], 'pengajuan_id' => $id])->execute();
                    }
                    $updateData['total_disetujui'] = $totalDisetujui;
                }
            } else if ($status === 'selesai') {
                $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
            }

            $db->createCommand()->update('anggaran_pengajuan', $updateData, ['id' => $id])->execute();

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Pengajuan berhasil diproses']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getNota(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        $notasRaw = $db->createCommand("SELECT * FROM anggaran_nota WHERE pengajuan_id = :id ORDER BY id ASC", [':id' => $id])->queryAll();
        
        $notas = [];
        foreach ($notasRaw as $n) {
            $items = $db->createCommand("SELECT * FROM anggaran_nota_items WHERE nota_id = :id", [':id' => $n['id']])->queryAll();
            $n['items'] = $items;
            $notas[] = $n;
        }

        return JsonResponse::create(['success' => true, 'data' => $notas]);
    }

    public function storeNota(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        $data = $request->getParsedBody();
        $items = json_decode($data['items'] ?? '[]', true);

        if (empty($items)) {
            return JsonResponse::create(['success' => false, 'message' => 'Item nota kosong'], 400);
        }

        $filePath = null;
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles['file_nota']) && $uploadedFiles['file_nota']->getError() === UPLOAD_ERR_OK) {
            $file = $uploadedFiles['file_nota'];
            $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $filename = 'nota_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $dir = __DIR__ . '/../../../../public/uploads/nota';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file->moveTo($dir . '/' . $filename);
            $filePath = '/uploads/nota/' . $filename;
        }

        $transaction = $db->beginTransaction();
        try {
            $total = 0;
            foreach ($items as $item) {
                $total += (float) ($item['qty'] * $item['harga_satuan']);
            }

            $db->createCommand()->insert('anggaran_nota', [
                'pengajuan_id' => $id,
                'nomor_nota' => $data['nomor_nota'] ?? '',
                'tanggal' => $data['tanggal'] ?? date('Y-m-d'),
                'total_belanja' => $total,
                'file_path' => $filePath,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();

            $notaId = $db->getLastInsertID();

            foreach ($items as $item) {
                $subtotal = (float) ($item['qty'] * $item['harga_satuan']);
                $db->createCommand()->insert('anggaran_nota_items', [
                    'nota_id' => $notaId,
                    'nama_barang' => $item['nama_barang'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal
                ])->execute();
            }

            $transaction->commit();
            return JsonResponse::create(['success' => true, 'message' => 'Nota berhasil disimpan']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteNota(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        try {
            $nota = $db->createCommand("SELECT file_path FROM anggaran_nota WHERE id = :id", [':id' => $id])->queryOne();
            if ($nota && !empty($nota['file_path'])) {
                $path = __DIR__ . '/../../../../public' . $nota['file_path'];
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            $db->createCommand()->delete('anggaran_nota', ['id' => $id])->execute();
            return JsonResponse::create(['success' => true, 'message' => 'Nota dihapus']);
        } catch (\Exception $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
