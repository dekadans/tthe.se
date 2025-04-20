<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use tthe\commands\StaticSiteGenerationCommand;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$application = new Application('tthe.se');
$application->add(new StaticSiteGenerationCommand());

$application->run();