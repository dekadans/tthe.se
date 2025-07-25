<?php

namespace tthe\services\film;

use Laminas\Feed\Reader\Feed\FeedInterface;
use Laminas\Feed\Reader\Feed\Rss;
use Laminas\Feed\Reader\Reader;

readonly class FilmService
{
    /** @var FeedInterface|Rss */
    public FeedInterface $feed;

    public function __construct()
    {
    }

    public function load(): void
    {
        $this->feed = Reader::import($_ENV['LETTERBOXD_URL']);
        $this->feed->getXpath()->registerNamespace("letterboxd", "https://letterboxd.com");
    }
}