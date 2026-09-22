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
            $instansi = $db->createCommand("SELECT kop_surat, nama_instansi, pondok FROM master_instansi WHERE kode = :k OR pondok = :k OR kode_instansi = :k OR def_pondok = :k", [':k' => $kode])->queryOne();
        } else {
            $myInstansi = $_SESSION['instansi_id'] ?? null;
            if ($myInstansi) {
                $instansi = $db->createCommand("SELECT kop_surat, nama_instansi, pondok FROM master_instansi WHERE kode = :k OR pondok = :k OR kode_instansi = :k OR def_pondok = :k", [':k' => $myInstansi])->queryOne();
            } else {
                $instansi = $db->createCommand("SELECT kop_surat, nama_instansi, pondok FROM master_instansi WHERE kode = :kode")
                    ->bindValue(':kode', $_SESSION['instansi_id'] ?? 0)
                    ->queryOne();
            }
        }
        
        if (!$instansi || empty($instansi['kop_surat'])) {
            $r = new Response(404);
            $r->getBody()->write("Kop surat not configured.");
            return $r;
        }

        $path = $instansi['kop_surat'];
        if (str_starts_with($path, '/') && !file_exists($path)) {
            // Coba cek path relatif (kasus lama)
            $path = dirname(__DIR__, 4) . '/public' . $path;
        }

        if (!file_exists($path) || !is_file($path)) {
            $basename = basename(str_replace('\\', '/', $path));
            $candidates = [
                dirname(__DIR__, 4) . '/public/uploads/instansi/' . $basename,
                dirname(__DIR__, 4) . '/public/uploads/' . $basename,
                'd:/XAMPP/htdocs/webapp/public/uploads/instansi/' . $basename,
                'd:/XAMPP/htdocs/webapp/public/uploads/' . $basename,
                'D:/01. Project/04. Website/pln/berkas/' . $basename,
                'D:/01. Project/04. Website/pln/webapp/public/uploads/instansi/' . $basename,
                '\\\\192.168.1.10\\foreign-pc1\\02. Aplikasi\\XAMPP\\htdocs\\webapp\\public\\uploads\\instansi\\' . $basename,
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand) && is_file($cand)) {
                    $path = $cand;
                    break;
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
