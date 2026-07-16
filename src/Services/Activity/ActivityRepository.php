<?php

namespace App\Services\Activity;

use App\Services\Activity\Readers\ActivityReaderInterface;

class ActivityRepository
{
    public function __construct(
        private ActivityReaderInterface $bookActivity,
        private ActivityReaderInterface $filmActivity
    ) {}

    public function books(int $limit): ItemList
    {
        return new ItemList(
            'Latest books read',
            array_slice($this->bookActivity->read(), 0, $limit)
        );
    }

    public function films(int $limit): ItemList
    {
        return new ItemList(
            'Latest films watched',
            array_slice($this->filmActivity->read(), 0, $limit)
        );
    }
}