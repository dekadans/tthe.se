<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use tthe\controllers\ErrorController;
use tthe\framework\AttributeRouteControllerLoader;

/*
 *
 * Bootstrapping of Symfony framework components.
 *
 */

/**
 * @var \Symfony\Component\Config\FileLocatorInterface $fileLocator
 */
$dependencies = require_once __DIR__ . '/../src/framework/dependencies.php';

if ($_ENV["DEBUG"]) {
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    error_reporting(0);
}

$routeDirLoader = new AttributeDirectoryLoader($fileLocator, new AttributeRouteControllerLoader($dependencies));
$routes = $routeDirLoader->load('src/controllers');

$request = Request::createFromGlobals();

$matcher = new UrlMatcher($routes, new RequestContext());

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
$dispatcher->addSubscriber(new ErrorListener(ErrorController::class));

$kernel = new HttpKernel($dispatcher, new ControllerResolver());

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);