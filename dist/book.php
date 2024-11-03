<?php

require_once '../vendor/autoload.php';

putenv('GOOGLE_APPLICATION_CREDENTIALS=../google-api-key.json');

$spreadsheetId = '1dfWhRpaudMxnO9F72sEsP2cDsmLL0pw18i9qxN8Jfps';
$range = "'View:ReadLog'!A2:C2";

$client = new Google\Client();
$client->useApplicationDefaultCredentials();
$client->addScope(\Google\Service\Sheets::SPREADSHEETS_READONLY);

try {
    $sheetsApi = new \Google\Service\Sheets($client);
    $sheetData = $sheetsApi->spreadsheets_values->get($spreadsheetId, $range);
    [$title, $author, $year] = $sheetData->values[0];

    header('Cache-control: max-age=10000');
    $response = [
        'title' => $title,
        'author' => $author,
        'year' => $year
    ];
} catch (Throwable $exception) {
    http_response_code(500);
    $response = [
        'error' => 'Failed fetching reading activity.'
    ];
}

header('Content-type: application/json');
echo json_encode($response);