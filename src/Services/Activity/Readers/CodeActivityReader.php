<?php

namespace App\Services\Activity\Readers;

use App\Services\Activity\Activity;
use App\Services\Activity\Type;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use tthe\TagScheme\Contracts\TaggingEntityInterface;

class CodeActivityReader implements ActivityReaderInterface
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
                $this->logger->info('Code activity cache miss, calling API...');
                return $this->getFromGithub();
            });
        } else {
            $this->logger->notice('Code activity cache is not configured.');
            return $this->getFromGithub();
        }
    }

    private function getFromGithub(): array
    {
        $activity = [];
        $request = $this->requestFactory->createRequest('GET', $this->options['url']);
        $response = $this->client->sendRequest($request);
        $jsonData = json_decode($response->getBody()->getContents(), true);

        foreach ($jsonData as $repo) {
            $name = $repo['name'];
            $uri = $this->tag->mint("me:action:update:code:$name");

            try {
                $activityTime = new \DateTimeImmutable($repo['updated_at']);
            } catch (\DateMalformedStringException $e) {
                $this->logger->warning("Unable to parse date {$repo['updated_at']}", ['exception' => $e]);
                continue;
            }

            $attributes = [
                'externalUri' => $repo['html_url'],
                'description' => $repo['description'],
                'language' => $repo['language'],
            ];

            $activity[] = new Activity(Type::CODE, $name, $uri, $activityTime, $attributes);
        }

        return $activity;
    }
}