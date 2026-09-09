<?php

return [
    // Path to the service account JSON key file on the server
    'credentials_path' => env('GOOGLE_CREDENTIALS_PATH'),

    // Or paste the entire JSON as an env var (useful for cloud deployments)
    'credentials_json' => env('GOOGLE_CREDENTIALS_JSON'),

    // The Google Spreadsheet ID (from the sheet URL)
    'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),

    // Tab names for each program (must match the sheet tab names exactly)
    'tabs' => [
        'sparks'       => env('GOOGLE_TAB_SPARKS', 'Sparks'),
        'kindergarten' => env('GOOGLE_TAB_KINDERGARTEN', 'Kindergarten'),
        '1st_grade'    => env('GOOGLE_TAB_1ST_GRADE', '1st Grade'),
    ],
];
