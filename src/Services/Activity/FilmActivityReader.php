<?php

namespace App\Services\Activity;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use tthe\TagScheme\Contracts\TaggingEntityInterface;

class FilmActivityReader implements ActivityReaderInterface
{
    use ActivityCache;

    public function __construct(
        private LoggerInterface $logger,
        private TaggingEntityInterface $tag,
        private RequestFactoryInterface $requestFactory,
        private ClientInterface $client,
        private array $options
    ) {
        if (empty($this->options['url'])) {
            throw new \Exception('Option param url is required.');
        }
    }

    public function read(): array
    {
        $cachePath = $this->options['cache'] ?? '';
        $ttl = $this->options['ttl'] ?? 60;

        if ($cachePath) {
            return $this->cache($cachePath, $ttl, function () {
                $this->logger->info('Film activity cache miss, calling API...');
                return $this->getFromRss();
            });
        } else {
            $this->logger->notice('Film activity cache is not configured.');
            return $this->getFromRss();
        }
    }

    private function getFromRss(): array
    {
        $activity = [];
        $request = $this->requestFactory->createRequest('GET', $this->options['url']);
        $response = $this->client->sendRequest($request);

        $rss = $response->getBody()->getContents();
        $xml = new \SimpleXMLElement($rss);
        $expr = '//item[guid[starts-with(text(), "letterboxd-watch")]]';

        foreach ($xml->xpath($expr) as $item) {
            $film = $item->children('https://letterboxd.com');
            $title = (string) $film->filmTitle;

            preg_match('/film\/(.*)/', $item->link, $matches);
            $slug = rtrim(str_replace('/', '-', $matches[1]), '-');
            $uri = $this->tag->mint("me:action:watch:film:$slug");

            try {
                $activityTime = new \DateTimeImmutable($film->watchedDate);
            } catch (\DateMalformedStringException $e) {
                $this->logger->warning("Unable to parse date $film->watchedDate", ['exception' => $e]);
                continue;
            }

            $attributes = [
                'year' => (string) $film->filmYear,
                'rating' => (string) $film->memberRating,
                'externalUri' => (string) $item->link,
            ];

            $activity[] = new Activity(Type::FILM, $title, $uri, $activityTime, $attributes);
        }

        return $activity;
    }
}
