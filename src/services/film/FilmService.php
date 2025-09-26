<?php

namespace tthe\services\film;

use Laminas\Feed\Reader\Entry\Rss as RssEntry;
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
        $this->feed->getXpath()->registerNamespace("tmdb", "https://themoviedb.org");
    }

    /**
     * @return FilmActivity[]
     */
    public function activity(): array
    {
        $list = [];

        foreach ($this->feed as $item) {
            if (!str_contains($item->getId(), 'watch')) {
                continue;
            }

            $list[] = new FilmActivity(
                $this->prop($item, 'letterboxd:filmTitle'),
                $this->prop($item, 'letterboxd:filmYear'),
                new \DateTime($this->prop($item, 'letterboxd:watchedDate')),
                $this->prop($item, 'letterboxd:memberRating'),
                $this->poster($item),
                $item->getLink(),
                $this->prop($item, 'tmdb:movieId')
            );
        }

        return $list;
    }

    private function prop(RssEntry $item, string $name)
    {
        return $item->getXpath()->evaluate(
            "string({$item->getXpathPrefix()}/$name)"
        );
    }

    private function poster(RssEntry $item)
    {
        $links = [];
        preg_match('/https:\/\/[^"]+/', $item->getDescription(), $links);
        return $links[0] ?? '';
    }
}