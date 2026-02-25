<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $exceptionDetails = (bool) $_ENV["ERROR_DETAILS"];
        $format = $request->getPreferredFormat();

        if (in_array($format, ['problem', 'json'])) {
            $data = $this->asJSON($exception, $exceptionDetails);
            return new JsonResponse($data, headers: [
                'Content-Type' => $request->getMimeType($format),
            ]);
        } else {
            $data = $this->asHTML($exception, $exceptionDetails);
            return new Response($data);
        }
    }

    private function getUserMessage(FlattenException $exception): string
    {
        return $exception->getStatusCode() < 500
            ? $exception->getMessage()
            : 'An error occurred when processing the request.';
    }

    private function asHTML(FlattenException $exception, bool $details): string
    {
        return $this->view->render('error.html.twig', [
            'title' => $exception->getStatusCode() . ' ' . $exception->getStatusText(),
            'message' => $this->getUserMessage($exception),
            'exception' => $details ? $this->getExceptionArray($exception) : null,
        ]);
    }

    private function asJSON(FlattenException $exception, bool $details): array
    {
        $data = [
            'type' => 'about:blank',
            'status' => $exception->getStatusCode(),
            'title' => $exception->getStatusText(),
            'detail' => $this->getUserMessage($exception),
        ];

        if ($details) {
            $data['detail'] = $exception->getMessage();
            $data['exceptions'] = $this->getExceptionArray($exception);
        }

        return $data;
    }

    private function getExceptionArray(FlattenException $exception): array
    {
        $data = [];
        foreach ($exception->toArray() as $ex) {
            $filtered = array_filter(
                $ex,
                fn($key) => in_array($key, ['class', 'trace', 'message']),
                ARRAY_FILTER_USE_KEY
            );
            $filtered['trace'] = array_map(
                function ($tr) {
                    return [
                        'file' => $tr['file'],
                        'line' => $tr['line'],
                        'function' => $tr['class'] . $tr['type'] . $tr['function'] . ($tr['function'] ? '()' : ''),
                    ];
                },
                $filtered['trace']
            );
            $data[] = $filtered;
        }

        return $data;
    }
}
