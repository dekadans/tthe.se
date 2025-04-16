<?php

namespace tthe\controllers;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorController
{
    function __invoke(Request $request, FlattenException $exception): Response
    {
        $message = "{$exception->getStatusCode()} {$exception->getStatusText()}";
        if ($_ENV["DEBUG"]) {
            $message .= "\n\n" . print_r($exception->toArray(), true);
        }

        return new Response($message, headers: [
            'Content-Type' => 'text/plain'
        ]);
    }
}