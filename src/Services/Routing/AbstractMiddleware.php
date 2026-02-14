<?php

namespace App\Services\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractMiddleware
{
    /**
     * Process the request before it reaches the controller.
     * If a Response is generated and returned by this function then the controller will not be called.
     * Return NULL to send the request along to the controller.
     *
     * @param Request $request
     * @return Response|null
     */
    public function inbound(Request $request): ?Response
    {
        return null;
    }

    /**
     * Process the generated response before it is sent to the client.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function outbound(Request $request, Response $response): void {}
}
