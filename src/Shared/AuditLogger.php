<?php
declare(strict_types=1);

namespace App\Shared;

use Yiisoft\Db\Connection\ConnectionInterface;

final class AuditLogger
{
    /**
     * Catat aktivitas ke tabel audit_logs
     *
     * @param ConnectionInterface $db Koneksi Database
     * @param string $action Aksi (CREATE, UPDATE, DELETE, APPROVE, REJECT, dll)
     * @param string $module Nama Modul (SANTRI, REQUEST_EDIT, JOB_DESK, dll)
     * @param string|int|null $entityId ID entitas yang dimanipulasi (misal kds santri)
     * @param mixed $oldData Data lama sebelum diubah (array/string)
     * @param mixed $newData Data baru setelah diubah (array/string)
     */
    public static function log(
        ConnectionInterface $db,
        string $action,
        string $module,
        $entityId = null,
        $oldData = null,
        $newData = null
    ): void {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? 'System';
            $instansiId = $_SESSION['instansi_id'] ?? null;

            // Pastikan format array di-encode ke JSON
            $oldJson = is_array($oldData) || is_object($oldData) ? json_encode($oldData) : $oldData;
            $newJson = is_array($newData) || is_object($newData) ? json_encode($newData) : $newData;

            $db->createCommand()->insert('audit_logs', [
                'user_id' => $userId,
                'username' => $username,
                'instansi_id' => $instansiId,
                'action' => strtoupper($action),
                'module' => strtoupper($module),
                'entity_id' => (string)$entityId,
                'old_data' => $oldJson,
                'new_data' => $newJson,
                'is_read' => 0
            ])->execute();
        } catch (\Exception $e) {
            // Log gagal direkam tidak boleh menghentikan aplikasi, biarkan berlalu atau catat di file server
            error_log("Gagal merekam audit trail: " . $e->getMessage());
        }
    }
}
