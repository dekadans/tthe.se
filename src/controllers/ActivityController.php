<?php

namespace tthe\controllers;

use Google\Client;
use Google\Service\Sheets;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController
{
    #[Route('/activity/film/rss', name: 'film')]
    public function film(): Response
    {
        $rss = file_get_contents($_ENV['LETTERBOXD_URL']);

        $response = new Response($rss);
        $response->setMaxAge(10000);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    #[Route('/activity/book', name: 'book')]
    public function book(): JsonResponse
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        try {
            $sheetsApi = new Sheets($client);
            $sheetData = $sheetsApi->spreadsheets_values->get($_ENV['SPREADSHEET_ID'], $_ENV['SPREADSHEET_RANGE']);
            [$title, $author, $year] = $sheetData->values[0];

            $data = [
                'title' => $title,
                'author' => $author,
                'year' => $year
            ];
            $response = new JsonResponse($data);
            $response->setMaxAge(10000);
            return $response;
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Failed fetching reading activity.', $exception);
        }
    }
}