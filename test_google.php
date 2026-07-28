<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

try {
    $credentialsPath = __DIR__ . '/config/google_service_account.json';
    $client = new Client();
    $client->setApplicationName('SI Santri Capel Sync');
    $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
    $client->setAuthConfig($credentialsPath);
    echo "Client created successfully.\n";
    
    $service = new Sheets($client);
    echo "Service created successfully.\n";
    
    $spreadsheetId = '11hrUFG-sSfEuuKyOdJFiHXTEbPNpqiVXWTvRQahNdIU';
    $range = 'XYZ123';
    
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    echo "Values fetched successfully.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
