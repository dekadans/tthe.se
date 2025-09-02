<?php

namespace tthe\controllers;

use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use tthe\framework\FileManager;
use tthe\services\posts\PostRepository;

class PostsController
{
    use HttpSupportTrait;

    #[Route('/posts/{id}', name: 'post')]
    public function displayPost(string $id, Request $request, PostRepository $postRepository): Response
    {
        try {
            $mediaType = $this->negotiateContentType($request, ['text/html', 'application/xml']);

            $content = match ($mediaType) {
                'application/xml' => $postRepository->getOneAsXml($id),
                default => $postRepository->getOneAsHtml($id)
            };

            $response = new Response($content);
            $response->headers->set('Content-Type', $mediaType);
            $this->cacheHeaders($request, $response);
            return $response;

        } catch (FileLocatorFileNotFoundException) {
            throw new NotFoundHttpException();
        }
    }
}