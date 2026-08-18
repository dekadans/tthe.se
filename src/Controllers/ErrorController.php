<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use tthe\Bagatelle\Error\ExceptionProblem;
use Twig\Environment as Template;

/**
 * Default error handler.
 * Will display an error page to the user for all uncaught exceptions.
 */
readonly class ErrorController
{
    public function __construct(
        private Template $view
    ) {}

    public function __invoke(Request $request, FlattenException $exception): Response
    {
        $exceptionDetails = ($_ENV["APP_ENV"] ?? '') === 'dev';
        $problem = new ExceptionProblem($exception, $exceptionDetails);

        if (in_array($request->getPreferredFormat(), ['problem', 'json'])) {
            $response = $problem->toResponse();
        } else {
            $html = $this->view->render('@bagatelle/error.html.twig', [
                'title' => $problem->status . ' ' . $problem->title,
                'message' => $problem->detail,
                'exception' => $problem->extensions['exceptions'] ?? null,
            ]);
            $response = new Response($html);
        }

        $response->headers->set('Vary', 'Accept');
        return $response;
    }
}
