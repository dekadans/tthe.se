<?php

namespace tthe\framework;

use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

class AttributeRouteControllerLoader extends AttributeClassLoader
{

    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $attr)
    {
        if ($method->getName() === '__invoke') {
            $route->setDefault('_controller', $class->getName());
        } else {
            $route->setDefault('_controller', $class->getName().'::'.$method->getName());
        }
    }
}