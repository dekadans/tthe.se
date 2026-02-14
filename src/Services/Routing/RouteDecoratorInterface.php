<?php

declare(strict_types=1);

namespace App\Services\Routing;

use Symfony\Component\Routing\Route;

interface RouteDecoratorInterface
{
    public function decorate(Route $route): void;
}
