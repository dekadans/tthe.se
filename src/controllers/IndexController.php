<?php

namespace tthe\controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use tthe\framework\FileManager;

class IndexController
{
    use HttpSupportTrait;

    #[Route('/', name: 'index')]
    function __invoke(Request $request, FileManager $files): Response
    {
        $index = $files->read($_ENV['INDEX_FILE']);
        $response = new Response($index);
        $this->cacheHeaders($request, $response);
        return $response;
    }
}