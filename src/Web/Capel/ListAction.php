<?php
declare(strict_types=1);

namespace App\Web\Capel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class ListAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        \Yiisoft\Db\Connection\ConnectionInterface $db
    ): ResponseInterface {
        @session_start();
        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
        $instansiId = $_SESSION['instansi_id'] ?? 0;
        
        if ($isSuperAdmin && !empty($request->getQueryParams()['instansi_id'])) {
            $instansiId = (int)$request->getQueryParams()['instansi_id'];
        }

        $instansiList = [];
        $spreadsheetId = '';
        $spreadsheetRange = 'Form Responses 1';
        $mappingData = null;
        $spreadsheetHeaders = '[]';
        $displayColumns = '[]';
        
        if ($instansiId) {
            $instansi = $db->createCommand("SELECT spreadsheet_id, capel_spreadsheet_range, capel_mapping, capel_spreadsheet_headers, capel_display_columns FROM master_instansi WHERE kode = :id", [':id' => $instansiId])->queryOne();
            if ($instansi) {
                if (!empty($instansi['spreadsheet_id'])) {
                    $spreadsheetId = $instansi['spreadsheet_id'];
                }
                if (!empty($instansi['capel_spreadsheet_range'])) {
                    $spreadsheetRange = $instansi['capel_spreadsheet_range'];
                }
                if (!empty($instansi['capel_mapping'])) {
                    $mappingData = $instansi['capel_mapping'];
                }
                if (!empty($instansi['capel_spreadsheet_headers'])) {
                    $spreadsheetHeaders = $instansi['capel_spreadsheet_headers'];
                }
                if (!empty($instansi['capel_display_columns'])) {
                    $displayColumns = $instansi['capel_display_columns'];
                }
            }
        }
        
        if ($isSuperAdmin) {
            $instansiList = $db->createCommand("SELECT kode as id, nama_instansi FROM master_instansi")->queryAll();
        }
        
        return $viewRenderer->render(__DIR__ . '/template', [
            'isSuperAdmin' => $isSuperAdmin,
            'instansiList' => $instansiList,
            'selectedInstansiId' => $instansiId,
            'defaultSpreadsheetId' => $spreadsheetId,
            'defaultSpreadsheetRange' => $spreadsheetRange,
            'defaultMappingData' => $mappingData,
            'spreadsheetHeaders' => $spreadsheetHeaders,
            'displayColumns' => $displayColumns
        ]);
    }
}
