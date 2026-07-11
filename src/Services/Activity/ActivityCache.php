<?php

namespace App\Services\Activity;

trait ActivityCache
{
    /**
     * File-based cache support for activity data.
     *
     * @param string $path
     * @param int $ttl
     * @param callable $data
     * @return Activity[]
     */
    protected function cache(string $path, int $ttl, callable $data): array
    {
        if (file_exists($path) && (filemtime($path) + $ttl) > time()) {
            $cacheContent = file_get_contents($path);
            return array_map(Activity::make(...), json_decode($cacheContent, true));
        } else {
            $activity = $data();
            $directory = dirname($path);
            if (!file_exists($directory)) {
                mkdir($directory, recursive: true);
            }
            file_put_contents($path, json_encode($activity));
            return $activity;
        }
    }
}