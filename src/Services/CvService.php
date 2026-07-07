<?php

namespace App\Services;

use Symfony\Component\Config\FileLocatorInterface;

class CvService
{
    public function __construct(
        private FileLocatorInterface $fileLocator
    ) {
        $this->load();
    }

    public private(set) array $data;
    public private(set) string $hash;

    private function load(): void
    {
        $path = $this->fileLocator->locate('data/me.jsonld');
        $content = file_get_contents($path);
        $this->data = json_decode($content, associative: true);
        $this->hash = hash('sha1', $content);
    }
}
