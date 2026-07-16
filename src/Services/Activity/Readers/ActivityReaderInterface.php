<?php

namespace App\Services\Activity\Readers;

use App\Services\Activity\Activity;

interface ActivityReaderInterface
{
    /**
     * @return Activity[]
     */
    public function read(): array;
}