<?php

namespace App\Services\Activity;

use Traversable;

readonly class ItemList implements \IteratorAggregate, \Countable
{
    /**
     * @param string $name
     * @param Activity[] $items
     */
    public function __construct(
        public string $name,
        private array $items
    ) {}

    /**
     * @return Traversable<Activity>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
