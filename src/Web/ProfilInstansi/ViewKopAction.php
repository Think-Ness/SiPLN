<?php declare(strict_types=1);
namespace App\Web\ProfilInstansi;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class ViewKopAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $kode = $request->getQueryParams()['kode'] ?? null;
        $instansi = null;
        if ($kode) {
            $instansi = $db->createCommand("SELECT kop_surat FROM master_instansi WHERE kode = :k", [':k' => $kode])->queryOne();
        } else {
            $myInstansi = $_SESSION['instansi_id'] ?? null;
            if ($myInstansi) {
                $instansi = $db->createCommand("SELECT kop_surat FROM master_instansi WHERE kode = :k", [':k' => $myInstansi])->queryOne();
            } else {
                $instansi = $db->createCommand("SELECT kop_surat FROM master_instansi WHERE kode = :kode")
                    ->bindValue(':kode', $_SESSION['instansi_id'] ?? 0)
                    ->queryOne();
            }
        }
        
        if (!$instansi || empty($instansi['kop_surat'])) {
            $r = new Response(404);
            $r->getBody()->write("Photo not found.");
            return $r;
        }

        $path = $instansi['kop_surat'];
        if (str_starts_with($path, '/') && !file_exists($path)) {
            // Coba cek path relatif (kasus lama)
            $path = dirname(__DIR__, 4) . '/public' . $path;
        }

        if (!file_exists($path) || !is_file($path)) {
            $r = new Response(404);
            $r->getBody()->write("File not found on disk.");
            return $r;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream'
        };

        $response = new Response(200);
        $response = $response->withHeader('Content-Type', $mime)
                             ->withHeader('Content-Disposition', 'inline')
                             ->withHeader('Cache-Control', 'public, max-age=86400');
                             
        $response->getBody()->write(file_get_contents($path));
        return $response;
    }
}
