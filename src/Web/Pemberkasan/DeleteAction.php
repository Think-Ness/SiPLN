<?php declare(strict_types=1);
namespace App\Web\Pemberkasan;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class DeleteAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        try {
            $berkas = $db->createCommand("SELECT path_file FROM mtb_berkas_penting WHERE id=:id", [':id' => $id])->queryOne();
            if ($berkas && !empty($berkas['path_file'])) {
                $path = $berkas['path_file'];
                // Jika path_file berupa absolute path Windows (C:\ atau D:\) atau /home (Linux), langsung hapus
                // Jika relative (dimulai dengan /uploads), cari di folder public
                if (str_starts_with($path, '/') && !file_exists($path)) {
                    $path = __DIR__ . '/../../../../public' . $path;
                }
                
                if (file_exists($path) && is_file($path)) {
                    unlink($path);
                }
            }
            $db->createCommand()->delete('mtb_berkas_penting', ['id' => $id])->execute();
            
            if ($berkas) {
                AuditLogger::log($db, 'DELETE', 'PEMBERKASAN', $id, $berkas, "Menghapus berkas penting ID $id");
            }

            return JsonResponse::create(['success' => true, 'message' => 'Berkas berhasil dihapus']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
