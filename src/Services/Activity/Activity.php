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
            'type' => $this->type->value,
            'title' => $this->title,
            'uri' => $this->uri,
            'timestamp' => $this->timestamp->format('c'),
            'attributes' => $this->attributes,
        ];
    }

    public static function make(array $jsonData): static
    {
        return new static(
            Type::from($jsonData['type']),
            $jsonData['title'],
            $jsonData['uri'],
            new \DateTimeImmutable($jsonData['timestamp']),
            $jsonData['attributes'],
        );
    }
}
