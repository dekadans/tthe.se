<?php

namespace App\Services\Activity\Transformers;

use App\Services\Activity\Activity;

class FilmTransformer implements ResourceTransformerInterface
{
    public function transform(Activity $activity): array
    {
        $rating = $activity->attributes['rating'] ? [
            'resultReview' => [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $activity->attributes['rating'],
                ],
            ],
        ] : [];

        return [
            '@id' => $activity->uri,
            '@type' => !empty($rating) ? ['WatchAction', 'ReviewAction'] : ['WatchAction'],
            'sameAs' => $activity->attributes['externalUri'],
            'object' => [
                '@type' => 'Movie',
                'name' => $activity->title,
                'copyrightYear' => $activity->attributes['year'],
            ],
            'endTime' => $activity->timestamp->format('Y-m-d\TH:i:s'),
        ] + $rating;
    }
}