<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\UploadPath;
use HttpSoft\Message\Response;

final class DownloadTemplateAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $mailingId = (int)($queryParams['mailing_id'] ?? 0);
        $tipeSurat = $queryParams['tipe'] ?? '';

        if (!$mailingId || !$tipeSurat) {
            return $this->errorResponse("Parameter mailing_id dan tipe harus ada.", 400);
        }

        $mailing = $db->createCommand("SELECT * FROM surat_mailing WHERE id = :mid", [':mid' => $mailingId])->queryOne();
        if (!$mailing) {
            return $this->errorResponse("Mailing tidak ditemukan.", 404);
        }

        $jenisPengajuan = $db->createCommand(
            "SELECT * FROM surat_jenis_pengajuan WHERE id = :id",
            [':id' => $mailing['jenis_pengajuan_id']]
        )->queryOne();

        $isSekaligus = ($mailing['mode'] === 'sekaligus');
        $suffix = $isSekaligus ? '_Banyak_Orang' : '_Satu_Orang';

        $templateFiles = [
            'SP' => 'Surat_Permohonan' . $suffix . '.docx',
            'SK' => 'Surat_Keterangan' . $suffix . '.docx',
            'SJ' => 'Surat_Jaminan' . $suffix . '.docx',
            'ST' => 'Surat_Tugas.docx'
        ];

        $kantor = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $jenisPengajuan['kantor']);
        
        // Resolve Surat_Menyurat base dari path_folder instansi
        @session_start();
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $instansiBase = $instansiId ? UploadPath::getBase($db, (int)$instansiId) : null;
        $publicSuratDir = $instansiBase !== null ? $instansiBase . '/Surat_Menyurat' : dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';

        if (!isset($templateFiles[$tipeSurat])) {
            // Check dynamic template
            $tipeName = str_replace('_', ' ', $tipeSurat);
            $dynamicTemplate = $db->createCommand(
                "SELECT file_path FROM surat_template_dinamis WHERE instansi_tujuan = :kantor AND nama_template = :tipe",
                [':kantor' => $jenisPengajuan['kantor'], ':tipe' => $tipeName]
            )->queryOne();
            
            if (!$dynamicTemplate) {
                return $this->errorResponse("Parameter tipe surat tidak valid.", 400);
            }
            // Path di DB berupa uploads/{kepengurusan}/...
            $fp = $dynamicTemplate['file_path'];
            if (UploadPath::isAbsolutePath($fp)) {
                $templatePath = str_replace('\\', '/', $fp);
            } else {
                $instansiBaseDL = UploadPath::getBase($db);
                if ($instansiBaseDL !== null) {
                    $normalizedBase = rtrim(str_replace('\\', '/', $instansiBaseDL), '/');
                    $kepengurusan = basename($normalizedBase);
                    $prefix = 'uploads/' . $kepengurusan . '/';
                    if (str_starts_with($fp, $prefix)) {
                        $stripped = substr($fp, strlen($prefix));
                        $templatePath = $instansiBaseDL . '/' . $stripped;
                    } else {
                        $templatePath = dirname(__DIR__, 3) . '/public/' . ltrim($fp, '/');
                    }
                } else {
                    $templatePath = dirname(__DIR__, 3) . '/public/' . ltrim($fp, '/');
                }
            }
        } else {
            $templateFileName = $templateFiles[$tipeSurat];
            $templatePath = $publicSuratDir . '/' . $kantor . '/' . $templateFileName;
        }

        if (!file_exists($templatePath)) {
            return $this->errorResponse(
                "File template tidak ditemukan: {$templatePath}\n\nPastikan file ada di folder:\n{$publicSuratDir}/{$kantor}/"
            );
        }

        if (isset($queryParams['download']) && $queryParams['download'] === '1') {
            $filename = basename($templatePath);
            $content = file_get_contents($templatePath);
            $response = new Response(200);
            $response->getBody()->write($content);
            return $response
                ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withHeader('Cache-Control', 'no-cache');
        }

        // Windows path resolution (tanpa exec di server agar file tidak terkunci oleh user SYSTEM)
        $windowsPath = str_replace('/', '\\', $templatePath);

        // Generate ms-word URI untuk client/laptop dengan hostname sipln
        $host = $request->getUri()->getHost() ?: ($_SERVER['HTTP_HOST'] ?? 'sipln');
        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        $docRoot = str_replace('\\', '/', dirname(__DIR__, 3) . '/public');
        $normalizedTpl = str_replace('\\', '/', $templatePath);
        if (str_starts_with($normalizedTpl, $docRoot)) {
            $relPath = ltrim(substr($normalizedTpl, strlen($docRoot)), '/');
        } else {
            $relPath = 'uploads/' . basename($publicSuratDir) . '/' . $kantor . '/' . ($templateFiles[$tipeSurat] ?? '');
        }
        $relPathWin = str_replace('/', '\\', $relPath);

        if ($host === 'localhost' || $host === '127.0.0.1') {
            $msWordTarget = $windowsPath;
        } else {
            $uncHost = ($host === '192.168.1.10') ? '192.168.1.10' : ($host === '100.68.135.3' ? '100.68.135.3' : 'sipln');
            $msWordTarget = '\\\\' . $uncHost . '\\foreign-pc1\\02. Aplikasi\\XAMPP\\htdocs\\webapp\\public\\' . $relPathWin;
        }

        $siplnUrl = 'sipln://' . rawurlencode($msWordTarget);

        $response = new Response(200);
        $response->getBody()->write(json_encode([
            'success'     => true,
            'message'     => 'Template berhasil dibuka di Microsoft Word',
            'path'        => $windowsPath,
            'unc_path'    => $msWordTarget,
            'ms_word_url' => 'ms-word:ofe|u|' . $msWordTarget,
            'sipln_url'   => $siplnUrl
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function errorResponse(string $message, int $status = 500): ResponseInterface
    {
        $response = new Response($status);
        $response->getBody()->write(json_encode(['success' => false, 'message' => $message]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
