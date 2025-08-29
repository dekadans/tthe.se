<?php

namespace tthe\controllers;

use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use tthe\framework\FileManager;

class PostsController
{
    #[Route('/posts/{id}', name: 'post')]
    public function displayPost(string $id, FileManager $files): Response
    {
        try {
            $post = $files->read('src/templates/post.html.twig');
            return new Response($post);
        } catch (FileLocatorFileNotFoundException $ex) {
            throw new NotFoundHttpException();
        }
    }

    #[Route('/posts/{id}/xml', name: 'post-xml')]
    public function displayPostXml(string $id, FileManager $files): Response
    {
        try {
            $post = $files->read("src/data/posts/{$id}.xml");
            $response = new Response($post);
            $response->headers->set('Content-Type', 'application/xml');
            return $response;
        } catch (FileLocatorFileNotFoundException $ex) {
            throw new NotFoundHttpException();
        }
    }
}