<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use tthe\framework\FileManager;

// Framework dependencies

$fileLocator = new FileLocator(__DIR__.'/../../');
$fileManager = new FileManager($fileLocator, new Filesystem());

$dotenv = new Dotenv();
$dotenv->load($fileManager->environment());


// Application dependencies

$feedReaderHttpClient = new \tthe\services\film\FeedReaderHttpClient(new \GuzzleHttp\Client());
\Laminas\Feed\Reader\Reader::setHttpClient($feedReaderHttpClient);

return [
    'files' => $fileManager
];