<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;
use App\Shared\UploadPath;
use HttpSoft\Message\Response;

/**
 * Generate file .vbs yang bisa diunduh dan dieksekusi di Windows
 * untuk membuka dokumen Word secara langsung tanpa blokir browser/Office security.
 */
final class OpenTemplateVbsAction
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function __invoke(
        ServerRequestInterface $request
    ): ResponseInterface {
        @session_start();
        $queryParams = $request->getQueryParams();
        $id          = (int)($queryParams['id'] ?? 0);
        $mailingId   = (int)($queryParams['mailing_id'] ?? 0);
        $tipeSurat   = $queryParams['tipe'] ?? '';

        // ── Resolve path file ──────────────────────────────────────────────
        if ($id > 0) {
            // Dari Kelola Template (surat_template_dinamis)
            $template = $this->db->createCommand(
                "SELECT * FROM surat_template_dinamis WHERE id = :id",
                [':id' => $id]
            )->queryOne();

            if (!$template) {
                return $this->errorJson("Template tidak ditemukan.", 404);
            }

            $fullPath = $this->resolveFullPath($template);

        } elseif ($mailingId > 0 && $tipeSurat !== '') {
            // Dari Surat Generator (download-template)
            $mailing = $this->db->createCommand(
                "SELECT * FROM surat_mailing WHERE id = :mid",
                [':mid' => $mailingId]
            )->queryOne();

            if (!$mailing) {
                return $this->errorJson("Mailing tidak ditemukan.", 404);
            }

            $jenisPengajuan = $this->db->createCommand(
                "SELECT * FROM surat_jenis_pengajuan WHERE id = :id",
                [':id' => $mailing['jenis_pengajuan_id']]
            )->queryOne();

            $isSekaligus = ($mailing['mode'] === 'sekaligus');
            $suffix = $isSekaligus ? '_Banyak_Orang' : '_Satu_Orang';

            $templateFiles = [
                'SP' => 'Surat_Permohonan' . $suffix . '.docx',
                'SK' => 'Surat_Keterangan' . $suffix . '.docx',
                'SJ' => 'Surat_Jaminan' . $suffix . '.docx',
                'ST' => 'Surat_Tugas.docx',
            ];

            $kantor      = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $jenisPengajuan['kantor']);
            $instansiId  = $_SESSION['instansi_id'] ?? null;
            $instansiBase = $instansiId ? UploadPath::getBase($this->db, (int)$instansiId) : null;
            $publicDir   = $instansiBase !== null
                ? $instansiBase . '/Surat_Menyurat'
                : dirname(__DIR__, 3) . '/public/uploads/Surat_Menyurat';

            if (!isset($templateFiles[$tipeSurat])) {
                // Dynamic template
                $tipeName = str_replace('_', ' ', $tipeSurat);
                $dyn = $this->db->createCommand(
                    "SELECT file_path FROM surat_template_dinamis WHERE instansi_tujuan = :k AND nama_template = :t",
                    [':k' => $jenisPengajuan['kantor'], ':t' => $tipeName]
                )->queryOne();

                if (!$dyn) {
                    return $this->errorJson("Tipe surat tidak valid.", 400);
                }

                $fp = $dyn['file_path'];
                if (UploadPath::isAbsolutePath($fp)) {
                    $fullPath = str_replace('\\', '/', $fp);
                } else {
                    $base = UploadPath::getBase($this->db);
                    if ($base !== null) {
                        $kepengurusan = basename(rtrim(str_replace('\\', '/', $base), '/'));
                        $prefix = 'uploads/' . $kepengurusan . '/';
                        $fullPath = str_starts_with($fp, $prefix)
                            ? $base . '/' . substr($fp, strlen($prefix))
                            : dirname(__DIR__, 3) . '/public/' . ltrim($fp, '/');
                    } else {
                        $fullPath = dirname(__DIR__, 3) . '/public/' . ltrim($fp, '/');
                    }
                }
            } else {
                $fullPath = $publicDir . '/' . $kantor . '/' . $templateFiles[$tipeSurat];
            }
        } else {
            return $this->errorJson("Parameter id atau mailing_id+tipe harus ada.", 400);
        }

        $fullPath = str_replace('\\', '/', $fullPath);

        if (!file_exists($fullPath)) {
            return $this->errorJson("File tidak ditemukan: " . $fullPath, 404);
        }

        // ── Buat UNC path untuk laptop (akses via Tailscale / LAN) ─────────
        $host = $request->getUri()->getHost() ?: ($_SERVER['HTTP_HOST'] ?? 'sipln');
        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        $docRoot = str_replace('\\', '/', dirname(__DIR__, 3) . '/public');
        if (str_starts_with($fullPath, $docRoot)) {
            $relPath    = ltrim(substr($fullPath, strlen($docRoot)), '/');
        } else {
            $relPath = ltrim(str_replace('\\', '/', $fullPath), '/');
        }
        $relPathWin = str_replace('/', '\\', $relPath);

        if ($host === 'localhost' || $host === '127.0.0.1') {
            // Akses lokal: gunakan path Windows langsung
            $winPath = str_replace('/', '\\', $fullPath);
        } else {
            $uncHost = match(true) {
                $host === '192.168.1.10'  => '192.168.1.10',
                $host === '100.68.135.3'  => '100.68.135.3',
                default                   => 'sipln',
            };
            $winPath = '\\\\' . $uncHost . '\\foreign-pc1\\02. Aplikasi\\XAMPP\\htdocs\\webapp\\public\\' . $relPathWin;
        }

        // Escape untuk VBScript string (ganda double-quote)
        $escapedPath = str_replace('"', '""', $winPath);

        // ── Buat konten VBScript ───────────────────────────────────────────
        $vbsContent = <<<VBS
' SiPLN - Buka Template Word
' Generated: {$winPath}
Option Explicit

Dim strPath
strPath = "{$escapedPath}"

Dim objFSO
Set objFSO = CreateObject("Scripting.FileSystemObject")

If Not objFSO.FileExists(strPath) Then
    MsgBox "File tidak ditemukan:" & Chr(13) & Chr(13) & strPath, vbCritical, "SiPLN - File Tidak Ditemukan"
    WScript.Quit 1
End If

Dim objWord
On Error Resume Next
Set objWord = GetObject(, "Word.Application")
If Err.Number <> 0 Then
    Err.Clear
    Set objWord = CreateObject("Word.Application")
End If
On Error GoTo 0

If objWord Is Nothing Then
    MsgBox "Microsoft Word tidak ditemukan di komputer ini.", vbCritical, "SiPLN"
    WScript.Quit 1
End If

objWord.Visible = True

Dim objDoc
On Error Resume Next
' Buka dengan ReadOnly = False, ConfirmConversions = False
Set objDoc = objWord.Documents.Open(strPath, False, False, False)
If Err.Number <> 0 Then
    Dim errMsg
    errMsg = Err.Description
    Err.Clear
    On Error GoTo 0
    MsgBox "Gagal membuka file:" & Chr(13) & strPath & Chr(13) & Chr(13) & "Error: " & errMsg, vbCritical, "SiPLN"
    WScript.Quit 1
End If
On Error GoTo 0

objWord.Activate

Set objDoc = Nothing
Set objWord = Nothing
Set objFSO = Nothing
VBS;

        $filename = 'buka_template_sipln.vbs';

        $response = new Response(200);
        $response->getBody()->write($vbsContent);

        return $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Content-Length', (string)strlen($vbsContent))
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache');
    }

    private function resolveFullPath(array $template): string
    {
        $filePath = $template['file_path'] ?? '';
        if (UploadPath::isAbsolutePath($filePath)) {
            return str_replace('\\', '/', $filePath);
        }

        $directPublic = dirname(__DIR__, 3) . '/public/' . ltrim($filePath, '/\\');
        if (file_exists($directPublic)) {
            return str_replace('\\', '/', $directPublic);
        }

        $instansiId   = !empty($template['instansi_id']) ? (int)$template['instansi_id'] : null;
        $instansiBase = UploadPath::getBase($this->db, $instansiId);
        if ($instansiBase !== null) {
            $kepengurusan = basename(rtrim(str_replace('\\', '/', $instansiBase), '/'));
            $prefix = 'uploads/' . $kepengurusan . '/';
            if (str_starts_with($filePath, $prefix)) {
                $instansiPath = $instansiBase . '/' . substr($filePath, strlen($prefix));
                if (file_exists($instansiPath)) {
                    return str_replace('\\', '/', $instansiPath);
                }
            }
        }

        return str_replace('\\', '/', $directPublic);
    }

    private function errorJson(string $msg, int $status = 500): ResponseInterface
    {
        $r = new Response($status);
        $r->getBody()->write(json_encode(['success' => false, 'message' => $msg]));
        return $r->withHeader('Content-Type', 'application/json');
    }
}
