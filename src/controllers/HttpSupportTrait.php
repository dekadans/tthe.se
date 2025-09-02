<?php

namespace tthe\controllers;

use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait HttpSupportTrait
{
    private function negotiateContentType(Request $request, array $acceptable): string
    {
        $acceptHeader = AcceptHeader::fromString(
            $request->headers->get('Accept') ?? '*/*'
        );
        $quality = fn($type) => $acceptHeader->get($type)?->getQuality() ?? 0;

        usort($acceptable, fn($a, $b) => $quality($b) <=> $quality($a));
        return $acceptable[0];
    }

    private function cacheHeaders(Request $request, Response $response): void
    {
        $response->setVary('Accept');
        $response->setEtag(hash('sha1', $response->getContent()));
        $response->isNotModified($request);
    }
}