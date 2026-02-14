<?php

declare(strict_types=1);

use App\Application;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Application()->console)();
