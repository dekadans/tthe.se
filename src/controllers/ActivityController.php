<?php

namespace tthe\controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController
{
    #[Route('/activity/film/rss', name: 'film')]
    public function film(Request $request): Response
    {
        $url = 'https://letterboxd.com/dekadans/rss/';
        $rss = file_get_contents($url);

        $response = new Response($rss);
        $response->setMaxAge(10000);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}