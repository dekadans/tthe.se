<?php

declare(strict_types=1);

/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 *
 * Uses PHP-DI by default: https://php-di.org/
 */

use App\Controllers\ErrorController;
use App\Controllers\IndexController;
use DI\ContainerBuilder;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

use function DI\autowire;
use function DI\create;
use function DI\decorate;
use function DI\get;

$containerBuilder = new ContainerBuilder();

/*
 *
 * Core Bagatelle configuration.
 *
 */
$containerBuilder->addDefinitions(tthe\Bagatelle\Config\DefaultConfiguration::all());

/*
 *
 * Application configuration.
 *
 */
$containerBuilder->addDefinitions([
    'app.root' => dirname(__DIR__),

    // Default application timezone.
    // Set to one from https://www.php.net/manual/en/timezones.php
    'app.timezone' => 'Europe/Stockholm',

    // --- HTTP Application Configuration

    // HTTP request event subscribers.
    'app.http.subscribers' => [
        // Add Symfony event subscribers through container references, for example:
        // autowire(\App\Events\SomeEventSubscriber::class)
        // They'll be automatically registered with the event dispatcher.
    ],

    // PSR-3 logger implementation for HTTP application.
    'app.http.logger' => function (StreamHandler $handler) {
        // StreamHandler comes configured with values from .env variables LOG_STREAM and LOG_LEVEL.
        $handler->setFormatter(new JsonFormatter());
        return new Logger('tthe-http', [$handler], [new PsrLogMessageProcessor()]);
    },

    // Sets log level for exceptions representing 4xx status codes, in \Symfony\Component\HttpKernel\Exception\...
    // These exceptions can be used as responses to requests, without polluting the error log.
    'app.http.logger.client-errors' => LogLevel::NOTICE,

    'app.http.error-handler' => ErrorController::class,

    // --- Console Application Configuration

    // The name of the console application.
    'app.console.name' => 'tthe.se CLI',

    // Console application event subscribers.
    'app.console.subscribers' => [
        // autowire(\App\Events\SomeEventSubscriber::class)
    ],

    // PSR-3 logger implementation for console application.
    'app.console.logger' => function (ConsoleHandler $handler) {
        return new Logger('tthe-cli', [$handler], [new PsrLogMessageProcessor()]);
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

    IndexController::class => autowire(),
    ErrorController::class => autowire(),

    \App\Commands\ActivityCommand::class => autowire(),
]);

/*
 *
 * Services.
 *
 */
$containerBuilder->addDefinitions([
    \Twig\Environment::class => decorate(function (\Twig\Environment $twig) {
        $twig->addExtension(new \Twig\Extra\Intl\IntlExtension());
        return $twig;
    }),

    \tthe\TagScheme\Contracts\TaggingEntityInterface::class => create(\tthe\TagScheme\TaggingEntity::class)
        ->constructor('tthe.se', \tthe\TagScheme\Util\DateUtil::FIRST_OF_YEAR),

    \Psr\Http\Client\ClientInterface::class => create(\GuzzleHttp\Client::class),

    // Remove when added to Bagatelle
    \Psr\Http\Message\RequestFactoryInterface::class => create(\Nyholm\Psr7\Factory\Psr17Factory::class),

    \Google\Service\Sheets::class => function (\Symfony\Component\Config\FileLocatorInterface $locator) {
        $keyPath = $locator->locate($_ENV['ACTIVITY_BOOKS_KEY'] ?? '');
        $apiClient = new \Google\Client();
        $apiClient->setAuthConfig($keyPath);
        $apiClient->addScope(\Google\Service\Sheets::SPREADSHEETS_READONLY);
        return new \Google\Service\Sheets($apiClient);
    },

    'app.activity.book.options' => function () {
        if (!empty($_ENV['ACTIVITY_BOOKS_CACHE'])) {
            $cache = __DIR__ . '/../' . $_ENV['ACTIVITY_BOOKS_CACHE'];
        }

        return [
            'spreadsheet' => $_ENV['ACTIVITY_BOOKS_SHEET'] ?? '',
            'range' => $_ENV['ACTIVITY_BOOKS_RANGE'] ?? '',
            'cache' => $cache ?? null,
            'ttl' => intval($_ENV['ACTIVITY_BOOKS_CACHE_TTL'] ?? '0'),
        ];
    },

    \App\Services\Activity\BookActivityReader::class => autowire()
        ->constructor(options: get('app.activity.book.options')),

    'app.activity.film.options' => function () {
        if (!empty($_ENV['ACTIVITY_FILM_CACHE'])) {
            $cache = __DIR__ . '/../' . $_ENV['ACTIVITY_FILM_CACHE'];
        }

        return [
            'url' => $_ENV['ACTIVITY_FILM_URL'] ?? '',
            'cache' => $cache ?? null,
            'ttl' => intval($_ENV['ACTIVITY_FILM_CACHE_TTL'] ?? '0'),
        ];
    },

    \App\Services\Activity\FilmActivityReader::class => autowire()
        ->constructor(options: get('app.activity.film.options')),

    \App\Services\Activity\ActivityRepository::class => create()
        ->constructor(
            get(\App\Services\Activity\BookActivityReader::class),
            get(\App\Services\Activity\FilmActivityReader::class)
        ),
]);

/*
 *
 * Bagatelle Middleware
 *
 */
$containerBuilder->addDefinitions([
    // Default configuration for the CORS middleware. Overridden by arguments passed to CORS attribute.
    // Allow origins and headers using a wildcard string, '*', or an array of allowed values.
    // Allowed methods always defaults to what the route accepts, unless overridden by attribute argument.
    'bagatelle.http.middleware.cors' => [
        'allow_origin' => '*',
        'allow_headers' => '*',
        'expose_headers' => [],
        'allow_credentials' => false,
        'max_age' => 600,
    ],

    // Default authentication implementation, reading username and password from environment variables.
    // Used by the BasicAuth middleware.
    // Reimplement this for your user storage solution of choice.
    \tthe\Bagatelle\Auth\AuthenticatorInterface::class => autowire(\tthe\Bagatelle\Auth\EnvironmentAuthenticator::class)
        ->constructor(['BASIC_AUTH_USER' => 'BASIC_AUTH_PASSWORD']),
]);

// If configured, we set the container to compile down to set instructions.
if (!empty($_ENV['CACHE_CONTAINER'])) {
    $containerBuilder->enableCompilation(__DIR__ . '/../' . $_ENV['CACHE_CONTAINER']);
}

// This file can return any object implementing the PSR-11 ContainerInterface,
// it doesn't have to be the bundled PHP-DI.
return $containerBuilder->build();
