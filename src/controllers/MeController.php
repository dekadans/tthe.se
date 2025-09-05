<?php

namespace tthe\controllers;

use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use tthe\framework\FileManager;

class MeController
{
    use HttpSupportTrait;

    const string HTML = "text/html";
    const string JSON = "application/ld+json";

    #[Route('/me', name: 'me')]
    function __invoke(Request $request, FileManager $files): Response
    {
        $filePaths = [
            self::HTML => $_ENV['CV_GENERATED'],
            self::JSON => $_ENV['CV_DATA']
        ];

        $mediaType = $this->negotiateContentType($request, array_keys($filePaths));

        $content = $files->read($filePaths[$mediaType]);

        $response = new Response($content);
        $response->headers->set('Content-Type', $mediaType);
        $this->cacheHeaders($request, $response);
        return $response;
    }
}