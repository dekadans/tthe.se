<?php

namespace App\Services\Activity;

interface ActivityReaderInterface
{
    /**
     * @return Activity[]
     */
    public function read(): array;
}