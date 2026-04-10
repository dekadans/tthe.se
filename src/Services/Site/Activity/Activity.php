<?php

namespace App\Services\Site\Activity;

readonly class Activity implements \JsonSerializable
{
    public function __construct(
        public Type $type,
        public string $title,
        public string $url,
        public array $attributes,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->name,
            'title' => $this->title,
            'url' => $this->url,
            'attributes' => $this->attributes,
        ];
    }
}
