<?php

namespace App\Services\Activity\Transformers;

use App\Services\Activity\ItemList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ListTransformer
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function transform(ItemList $list, ResourceTransformerInterface $resource): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $list->name,
            'owner' => [
                '@id' => $this->meUri(),
            ],
            'numberOfItems' => count($list),
            'itemListElement' => array_map($resource->transform(...), $list->toArray()),
        ];
    }

    private function meUri(): string
    {
        return $this->urlGenerator->generate('me-json', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
