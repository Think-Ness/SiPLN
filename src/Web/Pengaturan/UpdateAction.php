<?php
declare(strict_types=1);

namespace App\Web\Pengaturan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\AuditLogger;

final class UpdateAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        // Simple role check
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'super_admin') {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Hanya Super Admin yang berhak merubah pengaturan sistem.'
            ]);
        }

        $body = $request->getParsedBody();
        $fields = $body['fields'] ?? [];

        if (!is_array($fields)) {
            $fields = [];
        }

        // Simpan sebagai JSON
        $jsonFields = json_encode(array_values($fields));

        try {
            $oldFieldsJson = $db->createCommand("SELECT setting_value FROM app_settings WHERE setting_key = 'pindahan_allowed_fields'")->queryScalar();
            $oldData = ['kolom_diizinkan' => $oldFieldsJson ? json_decode($oldFieldsJson, true) : []];
            
            $sql = "
                INSERT INTO app_settings (setting_key, setting_value) 
                VALUES ('pindahan_allowed_fields', :val)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ";
            $db->createCommand($sql, [':val' => $jsonFields])->execute();

            $newData = ['kolom_diizinkan' => array_values($fields)];
            AuditLogger::log($db, 'UPDATE', 'PENGATURAN_SISTEM', null, $oldData, $newData, 'Mengubah pengaturan kolom yang diizinkan untuk diedit oleh kepengurusan pindahan');

            return JsonResponse::create([
                'success' => true,
                'message' => 'Pengaturan akses sunting (edit) berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return JsonResponse::create([
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan: ' . $e->getMessage()
            ]);
        }
    }
}
