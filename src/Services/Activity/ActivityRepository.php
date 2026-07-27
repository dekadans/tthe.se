<?php

namespace App\Services\Activity;

use App\Services\Activity\Readers\ActivityReaderInterface;

readonly class ActivityRepository
{
    public function __construct(
        private ActivityReaderInterface $bookActivity,
        private ActivityReaderInterface $filmActivity,
        private ActivityReaderInterface $codeActivity
    ) {}

    public function getForType(Type $type, int $limit): ItemList
    {
        [$reader, $name] = match ($type) {
            Type::FILM => [
                $this->filmActivity,
                'Latest films watched',
            ],
            Type::BOOK => [
                $this->bookActivity,
                'Latest books read',
            ],
            Type::CODE => [
                $this->codeActivity,
                'Recently updated repositories',
            ]
        };

        return new ItemList(
            $name,
            array_slice($reader->read(), 0, $limit)
        );
    }
}