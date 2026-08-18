<?php

namespace App\Services\Activity\Transformers;

use App\Services\Activity\Activity;

class CodeTransformer implements ResourceTransformerInterface
{
    public function transform(Activity $activity): array
    {
        return [
            '@id' => $activity->uri,
            '@type' => 'UpdateAction',
            'object' => [
                '@type' => 'SoftwareSourceCode',
                'name' => $activity->title,
                'description' => $activity->attributes['description'],
                'codeRepository' => $activity->attributes['externalUri'],
                'programmingLanguage' => $activity->attributes['language'],
            ],
            'endTime' => $activity->timestamp->format('c'),
        ];
    }
}