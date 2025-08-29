<?php

namespace tthe\framework;

use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileManager
{
    public function __construct(
        protected FileLocatorInterface $fileLocator,
        protected Filesystem $fs
    ) {}

    /**
     * @throws FileLocatorFileNotFoundException
     * @throws IOException
     */
    public function read(string $name): string
    {
        $fileName = $this->fileLocator->locate($name);
        return $this->fs->readFile($fileName);
    }

    public function write(string $name, $content): void
    {
        $directory = $this->fileLocator->locate('.');
        $this->fs->dumpFile($directory.'/'.$name, $content);
    }

    public function environment(): string
    {
        return $this->fileLocator->locate('.env');
    }
}