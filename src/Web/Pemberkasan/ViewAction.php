<?php declare(strict_types=1);
namespace App\Web\Pemberkasan;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class ViewAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db, CurrentRoute $currentRoute): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        $berkas = $db->createCommand("SELECT nama_berkas, path_file FROM mtb_berkas_penting WHERE id=:id", [':id' => $id])->queryOne();
        
        if (!$berkas || empty($berkas['path_file'])) {
            $r = new Response(404);
            $r->getBody()->write("File not found in database.");
            return $r;
        }

        $path = (string)$berkas['path_file'];
        
        if (!file_exists($path) || !is_file($path)) {
            // 1. Cek relative ke public
            if (str_starts_with($path, '/')) {
                $check = dirname(__DIR__, 3) . '/public' . $path;
                if (file_exists($check) && is_file($check)) {
                    $path = $check;
                }
            }

            // 2. Cek jika path mengandung /public/uploads/ dari mesin/drive lain
            if (!file_exists($path) || !is_file($path)) {
                $normalized = str_replace('\\', '/', $path);
                $uploadsPos = strpos($normalized, '/public/uploads/');
                if ($uploadsPos !== false) {
                    $relUpload = substr($normalized, $uploadsPos + strlen('/public/uploads/'));
                    $candidate = dirname(__DIR__, 3) . '/public/uploads/' . $relUpload;
                    if (file_exists($candidate) && is_file($candidate)) {
                        $path = $candidate;
                    }
                }
            }

            // 3. Cek pencarian di folder berkas penting instansi
            if (!file_exists($path) || !is_file($path)) {
                $baseDir = \App\Shared\UploadPath::getFolder($db, 'berkas penting', (int)($berkas['kode'] ?? 0));
                $candidate = $baseDir . '/' . basename($path);
                if (file_exists($candidate) && is_file($candidate)) {
                    $path = $candidate;
                }
            }
        }

        if (!file_exists($path) || !is_file($path)) {
            $r = new Response(404);
            $r->getBody()->write("File not found on disk.");
            return $r;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream'
        };

        $response = new Response(200);
        $response = $response->withHeader('Content-Type', $mime);
        
        // Cek jika butuh di-download vs di-view
        $isDownload = $request->getQueryParams()['dl'] ?? false;
        if ($isDownload) {
            $safeName = str_replace(['"', "'", ' '], '_', $berkas['nama_berkas']) . '.' . $ext;
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $safeName . '"');
        } else {
            $response = $response->withHeader('Content-Disposition', 'inline');
        }

        $response->getBody()->write(file_get_contents($path));
        return $response;
    }
}
