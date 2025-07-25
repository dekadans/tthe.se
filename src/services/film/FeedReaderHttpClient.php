<?php

namespace tthe\services\film;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Laminas\Feed\Reader\Http\ClientInterface as FeedReaderHttpClientInterface;
use Laminas\Feed\Reader\Http\Psr7ResponseDecorator;

readonly class FeedReaderHttpClient implements FeedReaderHttpClientInterface
{
    public function __construct(private GuzzleClientInterface $client)
    {
    }

    public function get($uri)
    {
        return new Psr7ResponseDecorator(
            $this->client->request('GET', $uri)
        );
    }
}