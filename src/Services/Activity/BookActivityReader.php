<?php

namespace App\Services\Activity;

use Google\Service\Sheets;
use Psr\Log\LoggerInterface;
use tthe\TagScheme\Contracts\TaggingEntityInterface;

class BookActivityReader implements ActivityReaderInterface
{
    use ActivityCache;

    /**
     * Reads book reading activity from a Google Sheet document.
     *
     * @param LoggerInterface $logger
     * @param TaggingEntityInterface $tag
     * @param Sheets $sheets
     * @param array $options
     */
    public function __construct(
        private LoggerInterface $logger,
        private TaggingEntityInterface $tag,
        private Sheets $sheets,
        private array $options
    ) {
        if (!isset($this->options['spreadsheet']) || !isset($this->options['range'])) {
            throw new \ValueError('Option params spreadsheet and range are required.');
        }
    }

    public function read(int $limit): array
    {
        $cachePath = $this->options['cache'] ?? '';
        $ttl = $this->options['ttl'] ?? 60;

        if ($cachePath) {
            return $this->cache($cachePath, $ttl, function() use ($limit) {
                $this->logger->info('Book activity cache miss, calling API...');
                return $this->getFromSheet($limit);
            });
        } else {
            $this->logger->notice('Book activity cache is not configured.');
            return $this->getFromSheet($limit);
        }
    }

    private function getFromSheet(int $limit): array
    {
        $data = $this->sheets->spreadsheets_values->get($this->options['spreadsheet'], $this->options['range']);
        $activity = [];

        for ($r = 0; isset($data[$r]) && $r < $limit; $r++) {
            [$title, $author, $releasedYear, , $readDate] = $data->values[$r];

            try {
                $activityTime = new \DateTimeImmutable($readDate);
            } catch (\DateMalformedStringException $e) {
                $this->logger->warning("Unable to parse date $readDate", ['exception' => $e]);
                continue;
            }

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $uri = $this->tag->mint("me:action:read:book:$slug");

            $attributes = [
                'author' => $author,
                'year' => $releasedYear
            ];

            $activity[] = new Activity(Type::BOOK, $title, $uri, $activityTime, $attributes);
        }

        return $activity;
    }
}