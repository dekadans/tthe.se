<?php

namespace tthe\controllers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController
{
    #[Route('/', name: 'index')]
    function __invoke(Request $request): Response
    {
        $fs = new Filesystem();
        $index = $fs->readFile(__DIR__.'/../templates/index.html');
        return new Response($index);
    }
}