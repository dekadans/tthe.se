<?php

declare(strict_types=1);

/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 *
 * Uses PHP-DI by default: https://php-di.org/
 */

use App\Commands\RoutesCommand;
use DI\ContainerBuilder;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

use function DI\autowire;

$containerBuilder = new ContainerBuilder();

/*
 *
 * Application configuration.
 *
 */
$containerBuilder->addDefinitions([
    // Default application timezone.
    // Set to one from https://www.php.net/manual/en/timezones.php
    'app.timezone' => 'UTC',

    // --- HTTP Application Configuration

    // HTTP request event subscribers.
    'app.http.subscribers' => [
        // Add Symfony event subscribers through container references, for example:
        // autowire(App\Events\SomeEventSubscriber:class)
        // They'll be automatically registered with the event dispatcher.
    ],

    // PSR-3 logger implementation for HTTP application.
    'app.http.logger' => function (StreamHandler $handler) {
        // StreamHandler comes configured values from .env variables LOG_STREAM and LOG_LEVEL.
        $handler->setFormatter(new JsonFormatter());
        return new Logger('bagatelle-http', [$handler], [new PsrLogMessageProcessor()]);
    },

    // Overrides default logging for certain exceptions.
    // Specifically lowers the criticality for some HTTP client errors.
    // These exceptions can be used as responses to requests, without polluting the error log.
    'app.http.logger.exceptions' => function () {
        $exceptions = [
            \Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class, // 400
            \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException::class, // 401
            \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class, // 403
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class, // 404
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class, // 405
            \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException::class, // 406
            \Symfony\Component\HttpKernel\Exception\ConflictHttpException::class, // 409
            \Symfony\Component\HttpKernel\Exception\GoneHttpException::class, // 410
            \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException::class, // 415
            \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException::class, // 422
        ];

        return array_fill_keys($exceptions, [
            'log_level' => LogLevel::NOTICE,
            'status_code' => null,
            'log_channel' => null,
        ]);
    },

    // --- Console Application Configuration

    // The name of the console application.
    'app.console.name' => 'Example Console Application',

    // Console commands. Add your command implementation classes here.
    'app.console.commands' => [
        // NOTE: Only add class names, not container references or instances.
        RoutesCommand::class,
    ],

    // Console application event subscribers.
    'app.console.subscribers' => [
        // ...
    ],

    // PSR-3 logger implementation for console application.
    'app.console.logger' => function (ConsoleHandler $handler) {
        return new Logger('bagatelle-cli', [$handler], [new PsrLogMessageProcessor()]);
    },
]);

/*
 *
 * Controllers & Commands.
 *
 */
$containerBuilder->addDefinitions([
    // Adding autowire definitions here is not necessary to make it work,
    // however, it will improve performance when using a compiled container in production.

    \App\Controllers\IndexController::class => autowire(),
    \App\Controllers\ErrorController::class => autowire(),

    RoutesCommand::class => autowire(),
]);

/*
 *
 * Services.
 *
 */
$containerBuilder->addDefinitions([
    // The bundled dependency injection container autowires dependencies when possible, but here you can explicitly
    // define your services when needed, like when there's dependencies on interfaces. For example:
    // App\Services\EncabulationInterface::class => autowire(App\Services\TurboEncabulator::class)
]);

// Add the core Bagatelle configuration.
$containerBuilder->addDefinitions(__DIR__ . '/core.php');

// If configured, we set the container to compile down to set instructions.
if (!empty($_ENV['DI_CACHE_DIR'])) {
    $containerBuilder->enableCompilation(__DIR__ . '/../' . $_ENV['DI_CACHE_DIR']);
}

// This file can return any object implementing the PSR-11 ContainerInterface,
// it doesn't have to be the bundled PHP-DI.
return $containerBuilder->build();
