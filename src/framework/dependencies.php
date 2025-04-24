<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use tthe\framework\FileManager;


$fileLocator = new FileLocator(__DIR__.'/../../');
$fileManager = new FileManager($fileLocator, new Filesystem());

$dotenv = new Dotenv();
$dotenv->load($fileManager->environment());

return [
    'files' => $fileManager
];