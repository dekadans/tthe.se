<?php

declare(strict_types=1);

use tthe\Bagatelle\Application;

$appRoot = dirname(__DIR__);

require_once $appRoot . '/vendor/autoload.php';

(new Application($appRoot)->http)();
