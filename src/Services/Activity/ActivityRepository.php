<?php

namespace App\Services\Activity;

class ActivityRepository
{
    public function __construct(
        private ActivityReaderInterface $bookActivity,
        private ActivityReaderInterface $filmActivity
    ) {}

    /**
     * @param int $limit
     * @return Activity[]
     */
    public function books(int $limit): array
    {
        return array_slice($this->bookActivity->read(), 0, $limit);
    }

    /**
     * @param int $limit
     * @return Activity[]
     */
    public function films(int $limit): array
    {
        return array_slice($this->filmActivity->read(), 0, $limit);
    }
}