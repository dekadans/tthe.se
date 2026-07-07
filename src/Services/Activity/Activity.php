<?php

namespace App\Services\Activity;

readonly class Activity implements \JsonSerializable
{
    public function __construct(
        public Type $type,
        public string $title,
        public string $uri,
        public \DateTimeImmutable $timestamp,
        public array $attributes,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->name,
            'title' => $this->title,
            'uri' => $this->uri,
            'timestamp' => $this->timestamp->format('c'),
            'attributes' => $this->attributes,
        ];
    }
}
