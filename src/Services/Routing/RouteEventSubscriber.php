<?php

namespace App\Services\Routing;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Turns Symfony's native lifecycle events into a simple middleware-esque solution.
 */
readonly class RouteEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private ContainerInterface $container) {}

    public function handleRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        foreach ($this->getRouteMiddleware($request) as $middleware) {
            $maybeResponse = $middleware->inbound($request);
            if ($maybeResponse) {
                $event->setResponse($maybeResponse);
            }
        }
    }

    public function handleResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        foreach ($this->getRouteMiddleware($request) as $middleware) {
            $middleware->outbound($request, $event->getResponse());
        }
    }

    /**
     * @param Request $request
     * @return \Generator<AbstractMiddleware>
     */
    private function getRouteMiddleware(Request $request): \Generator
    {
        /** @var \SplPriorityQueue $routeMiddleware */
        $routeMiddleware = $request->attributes->get('_middleware', []);
        foreach ($routeMiddleware as $ref) {
            yield $this->container->get($ref);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'handleRequest',
            KernelEvents::RESPONSE => 'handleResponse',
        ];
    }
}
