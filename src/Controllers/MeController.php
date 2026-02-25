<?php

namespace App\Controllers;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as Twig;

class MeController
{
    public function __construct(
        private Twig $view,
        private FileLocatorInterface $fileLocator
    ) {}

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $data = $this->getData();

        if ($request->getPreferredFormat() === 'jsonld') {
            return new JsonResponse($data, headers: [
                'Content-Type' => 'application/ld+json',
            ]);
        } else {
            $html = $this->view->render('me/me.html.twig', $data);
            return new Response($html);
        }
    }

    // Move to service
    private function getData(): array
    {
        $path = $this->fileLocator->locate('data/me.jsonld');
        $content = file_get_contents($path);
        return json_decode($content, associative: true);
    }
}