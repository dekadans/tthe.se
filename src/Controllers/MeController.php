<?php

namespace App\Controllers;

use App\Services\Site\CvService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as Twig;

class MeController
{
    public function __construct(
        private Twig $view,
        private CvService $cv
    ) {}

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        if (in_array($request->getPreferredFormat(), ['jsonld', 'json'])) {
            $response = new JsonResponse($this->cv->data, headers: [
                'Content-Type' => 'application/ld+json',
            ]);
        } else {
            $html = $this->view->render('me/me.html.twig', $this->cv->data);
            $response = new Response($html);
        }

        $response->setEtag($this->cv->hash, true);
        $response->isNotModified($request);

        return $response;
    }
}