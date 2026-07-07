<?php

namespace App\Services\Activity;

interface ActivityReaderInterface
{
    /**
     * @param int $limit
     * @return Activity[]
     */
    public function read(int $limit): array;
}