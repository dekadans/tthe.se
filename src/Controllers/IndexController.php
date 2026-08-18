<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use tthe\Bagatelle\Http\Attribute\Get;
use Twig\Environment as Twig;

readonly class IndexController
{
    public function __construct(
        private Twig $view,
    ) {}

    #[Get('/', 'index')]
    public function __invoke(Request $request): Response
    {
        $view = $this->view->render('index.html.twig');
        return new Response($view);
    }
}
