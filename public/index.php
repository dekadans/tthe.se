<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Dotenv\Dotenv;
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

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

if ($_ENV["DEBUG"]) {
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    error_reporting(0);
}

$fileLocator = new FileLocator(__DIR__.'/../src/');

$routeDirLoader = new AttributeDirectoryLoader($fileLocator, new AttributeRouteControllerLoader());
$routes = $routeDirLoader->load('controllers');

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