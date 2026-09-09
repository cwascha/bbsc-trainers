<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetsService
{
    private Sheets $sheets;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName('BBSC Teams');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);

        $credentialsPath = config('google.credentials_path');
        if ($credentialsPath && file_exists($credentialsPath)) {
            $client->setAuthConfig($credentialsPath);
        } elseif ($json = config('google.credentials_json')) {
            $client->setAuthConfig(json_decode($json, true));
        } else {
            throw new \RuntimeException('Google Sheets credentials not configured.');
        }

        $this->sheets = new Sheets($client);
    }

    // Returns rows from a named tab as arrays. First row is treated as header.
    public function getRows(string $spreadsheetId, string $tabName): array
    {
        $response = $this->sheets->spreadsheets_values->get(
            $spreadsheetId,
            $tabName,
        );

        $rows = $response->getValues() ?? [];
        if (count($rows) < 2) {
            return [];
        }

        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        $result  = [];

        foreach (array_slice($rows, 1) as $row) {
            if (empty(array_filter($row))) {
                continue; // skip blank rows
            }
            $entry = [];
            foreach ($headers as $i => $header) {
                $entry[$header] = $row[$i] ?? '';
            }
            $result[] = $entry;
        }

        return $result;
    }
}
