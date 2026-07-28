<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
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

        if (!isset($templateFiles[$tipeSurat])) {
            return $this->errorResponse("Parameter tipe surat tidak valid.", 400);
        }

        $templateFileName = $templateFiles[$tipeSurat];

        @session_start();
        $instansiId = $mailing['instansi_id'] ?? null;
        if (empty($instansiId)) {
            $instansiId = $_SESSION['instansi_id'] ?? null;
        }

        if (empty($instansiId)) {
            return $this->errorResponse("Instansi tidak dapat ditentukan. Silakan login kembali.", 400);
        }

        $instansi = $db->createCommand(
            "SELECT * FROM master_instansi WHERE kode = :kode",
            [':kode' => $instansiId]
        )->queryOne();

        if (!$instansi || empty($instansi['path_folder'])) {
            return $this->errorResponse("Path Folder Instansi belum diatur.", 400);
        }

        $basePath = rtrim(str_replace('\\', '/', $instansi['path_folder']), '/');
        $kantor = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $jenisPengajuan['kantor']);
        $publicSuratDir = dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';

        $templatePath = $publicSuratDir . '/' . $kantor . '/' . $templateFileName;

        if (!file_exists($templatePath)) {
            return $this->errorResponse(
                "File template tidak ditemukan: {$templatePath}\n\nPastikan file ada di folder:\n{$publicSuratDir}/{$kantor}/"
            );
        }

        // Buka file langsung menggunakan OS default app (Word)
        $windowsPath = str_replace('/', '\\', $templatePath);
        exec('start "" "' . $windowsPath . '"');

        $response = new Response(200);
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Template berhasil dibuka di Word'
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
