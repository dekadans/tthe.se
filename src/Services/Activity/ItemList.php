<?php

namespace App\Services\Activity;

use Traversable;

readonly class ItemList implements \IteratorAggregate
{
    /**
     * @param string $name
     * @param Activity[] $items
     */
    public function __construct(
        public string $name,
        public array $items
    ) {}

    /**
     * @return Traversable<Activity>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }
}
