<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Google\Client;
use Google\Service\Sheets;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\JsonResponse;

final class SyncHeadersAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db
    ): ResponseInterface {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = json_decode((string)$request->getBody(), true);
        }
        $spreadsheetId = $body['spreadsheet_id'] ?? '';
        $range = $body['sheet_range'] ?? 'Form Responses 1';

        if (empty($spreadsheetId)) {
            return JsonResponse::create(['success' => false, 'message' => 'Spreadsheet ID tidak boleh kosong.'])->withStatus(400);
        }

        $credentialsPath = dirname(__DIR__, 3) . '/config/google_service_account.json';
        $encPath = dirname(__DIR__, 3) . '/config/gcp_key.txt';
        
        // Auto-decode obfuscated key
        if (!file_exists($credentialsPath) && file_exists($encPath)) {
            @file_put_contents($credentialsPath, base64_decode(file_get_contents($encPath)));
        }

        if (!file_exists($credentialsPath)) {
            return JsonResponse::create(['success' => false, 'message' => 'File google_service_account.json tidak ditemukan di folder config!'])->withStatus(400);
        }

        try {
            $client = new Client();
            $client->setApplicationName('SI Santri Capel Sync Headers');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig($credentialsPath);

            $service = new Sheets($client);
            // Append !1:1 to fetch only the first row. Wrap in single quotes to handle spaces safely.
            $headerRange = str_contains($range, '!') ? $range : "'{$range}'!1:1";
            $response = $service->spreadsheets_values->get($spreadsheetId, $headerRange);
            $values = $response->getValues();

            if (empty($values)) {
                return JsonResponse::create(['success' => false, 'message' => 'Tidak ada data ditemukan di Spreadsheet. Pastikan nama tab sheet (Range) benar.']);
            }

            $headers = array_shift($values);
            
            return JsonResponse::create([
                'success' => true, 
                'headers' => $headers
            ]);

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Requested entity was not found')) {
                $msg = "Spreadsheet tidak ditemukan atau Robot belum diberi akses 'Viewer' ke file tersebut.";
            } elseif (str_contains($msg, 'Unable to parse range')) {
                $msg = "Nama/Range Sheet tidak valid. Pastikan nama tab benar (contoh: Form Responses 1).";
            }
            
            return JsonResponse::create([
                'success' => false, 
                'message' => 'Gagal: ' . $msg
            ]);
        }
    }
}
