<?php

namespace App\Controllers;

use App\Services\CvService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use tthe\Bagatelle\Http\Attribute\Get;
use Twig\Environment as Twig;

class MeController
{
    public function __construct(
        private Twig $view,
        private CvService $cv
    ) {}

    #[Get('/me', 'me')]
    public function html(Request $request): Response
    {
        $html = $this->view->render('me/me.html.twig', $this->cv->data);
        $response = new Response($html);

        return $this->setCache($request, $response);
    }

    #[Get('/me.json', 'me-json')]
    public function json(Request $request): Response
    {
        $response = new JsonResponse($this->cv->data, headers: [
            'Content-Type' => 'application/json',
        ]);

        return $this->setCache($request, $response);
    }

    private function setCache(Request $request, Response $response): Response
    {
        $response->setEtag($this->cv->hash, true);
        $response->isNotModified($request);
        return $response;
    }
}