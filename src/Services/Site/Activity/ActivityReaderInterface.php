<?php

namespace App\Services\Site\Activity;

interface ActivityReaderInterface
{
    /**
     * @param int $limit
     * @return Activity[]
     */
    public function read(int $limit): array;
}