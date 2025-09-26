<?php

namespace tthe\services\film;

readonly class FilmActivity
{
    public function __construct(
        public string    $title,
        public string    $filmYear,
        public \DateTime $date,
        public string    $rating,
        public string    $posterUrl,
        public string    $url,
        public string    $externalId
    ) {}
}