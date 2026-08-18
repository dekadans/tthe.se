<?php

namespace App\Services\Activity\Transformers;

use App\Services\Activity\Activity;

class BookTransformer implements ResourceTransformerInterface
{
    public function transform(Activity $activity): array
    {
        return [
            '@id' => $activity->uri,
            '@type' => 'ReadAction',
            'object' => [
                '@type' => 'Book',
                'name' => $activity->title,
                'sameAs' => $activity->attributes['externalUri'] ?? null,
                'copyrightYear' => $activity->attributes['year'],
                'author' => [
                    '@type' => 'Person',
                    'name' => $activity->attributes['author'],
                ],
            ],
            'endTime' => $activity->timestamp->format('Y-m-d\TH:i:s'),
        ];
    }
}