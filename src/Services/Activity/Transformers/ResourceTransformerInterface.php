<?php

namespace App\Services\Activity\Transformers;

use App\Services\Activity\Activity;

interface ResourceTransformerInterface
{
    public function transform(Activity $activity): array;
}