<?php

namespace tthe\controllers;

use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use tthe\framework\FileManager;

class MeController
{
    const string HTML = "text/html";
    const string JSON = "application/ld+json";

    #[Route('/me', name: 'me')]
    function __invoke(Request $request, FileManager $files): Response
    {
        $filePaths = [
            self::HTML => $_ENV['STATIC_FILE'],
            self::JSON => $_ENV['DATA_FILE']
        ];

        $acceptHeader = AcceptHeader::fromString(
            $request->headers->get('Accept') ?? '*/*'
        );
        $htmlPrio = $acceptHeader->get(self::HTML)?->getQuality() ?? 0;
        $jsonPrio = $acceptHeader->get(self::JSON)?->getQuality() ?? 0;

        $mediaType = $jsonPrio > $htmlPrio ? self::JSON : self::HTML;
        $content = $files->read($filePaths[$mediaType]);

        $response = new Response($content);
        $response->setVary('Accept');
        $response->setEtag(hash('sha1', $content));
        $response->headers->set('Content-Type', $mediaType);
        $response->isNotModified($request);

        return $response;
    }
}