<?php declare(strict_types=1);
namespace App\Web\ProfilInstansi;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class UpdateAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $data = $request->getParsedBody() ?? [];
        try {
            $instansiId = $_SESSION['instansi_id'] ?? null;
            $myRole = $_SESSION['role'] ?? '';
            
            if ($myRole === 'super_admin') {
                $existing = !empty($data['target_kode']) ? $data['target_kode'] : null;
                if (!$existing) {
                    $existing = $db->createCommand("SELECT kode FROM master_instansi LIMIT 1")->queryScalar();
                }
            } else {
                $existing = $instansiId ? $db->createCommand("SELECT kode FROM master_instansi WHERE kode = :kode", [':kode' => $instansiId])->queryScalar() : null;
            }
            
            $payload = [
                'nama_instansi'     => $data['nama_instansi'] ?? '',
                'no_Instansi'       => (int)($data['no_Instansi'] ?? 0),
                'email'             => $data['email'] ?? '',
                'pondok'            => $data['pondok'] ?? '',
                'path_folder'       => $data['path_folder'] ?? '',
                'def_kepengurusan'  => $data['def_kepengurusan'] ?? '',
                'def_pondok'        => $data['def_pondok'] ?? '',
                'def_jenis_kelamin' => $data['def_jenis_kelamin'] ?? '',
            ];

            $uploadedFiles = $request->getUploadedFiles();
            if (isset($uploadedFiles['kop_surat']) && $uploadedFiles['kop_surat']->getError() === \UPLOAD_ERR_OK) {
                $file = $uploadedFiles['kop_surat'];
                $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
                $filename = 'kop_surat_' . time() . '.' . $ext;
                
                $targetPath = '';
                if (!empty($payload['path_folder'])) {
                    $dir = rtrim(str_replace('\\', '/', $payload['path_folder']), '/');
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                    $targetPath = $dir . '/' . $filename;
                } else {
                    $targetPath = dirname(__DIR__, 3) . '/public/uploads/instansi/' . $filename;
                }
                
                $file->moveTo($targetPath);
                $payload['kop_surat'] = $targetPath;
            }
            $oldInstansi = null;
            if ($existing) {
                $oldInstansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $existing])->queryOne();
                $db->createCommand()->update('master_instansi', $payload, ['kode' => $existing])->execute();
                $newId = $existing;
            } else {
                $db->createCommand()->insert('master_instansi', $payload)->execute();
                $newId = $db->getLastInsertID();
            }
            
            $newInstansi = $db->createCommand("SELECT * FROM master_instansi WHERE kode = :kode", [':kode' => $newId])->queryOne();
            AuditLogger::log($db, 'UPDATE', 'PROFIL_INSTANSI', $newId, $oldInstansi, $newInstansi, 'Memperbarui pengaturan dan profil instansi');
            
            return JsonResponse::create(['success' => true, 'message' => 'Profil instansi berhasil disimpan']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
