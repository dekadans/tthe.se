<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use tthe\commands\StaticSiteGenerationCommand;

/**
 * @var \tthe\framework\FileManager $fileManager
 */
require_once __DIR__ . '/framework/dependencies.php';

$application = new Application('tthe.se');
$application->add(new StaticSiteGenerationCommand($fileManager));

$application->run();