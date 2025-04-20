<?php

namespace tthe\controllers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MeController
{
    const string HTML = "text/html";
    const string JSON = "application/ld+json";

    private Filesystem $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    #[Route('/me', name: 'me')]
    function __invoke(Request $request): Response
    {
        $files = [
            self::HTML => __DIR__.$_ENV['STATIC_FILE'],
            self::JSON => __DIR__.$_ENV['DATA_FILE']
        ];

        $acceptHeader = AcceptHeader::fromString(
            $request->headers->get('Accept') ?? '*/*'
        );
        $htmlPrio = $acceptHeader->get(self::HTML)?->getQuality() ?? 0;
        $jsonPrio = $acceptHeader->get(self::JSON)?->getQuality() ?? 0;

        $mediaType = $jsonPrio > $htmlPrio ? self::JSON : self::HTML;
        $content = $this->fs->readFile($files[$mediaType]);

        $response = new Response($content);
        $response->setVary('Accept');
        $response->setEtag(hash('sha1', $content));
        $response->headers->set('Content-Type', $mediaType);
        $response->isNotModified($request);

        return $response;
    }
}