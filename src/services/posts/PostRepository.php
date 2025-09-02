<?php

namespace tthe\services\posts;

use tthe\framework\FileManager;

class PostRepository
{
    private FileManager $files;
    private string $path;
    private string $generatedPath;

    public function __construct(FileManager $files)
    {
        $this->files = $files;
        $this->path = $_ENV['POST_DIRECTORY'];
        $this->generatedPath = $_ENV['POST_GENERATED'];
    }

    /**
     * @return Post[]
     */
    public function getAll(): array
    {
        $allXml = $this->getAllAsXml();
        return array_map(
            fn($k, $v) => $this->parseXml($k, $v),
            array_keys($allXml),
            array_values($allXml)
        );
    }

    public function getOne(string $id): Post
    {
        $xml = $this->getOneAsXml($id);
        return $this->parseXml($id, $xml);
    }

    public function getAllAsXml(): array
    {
        $result = [];
        $paths = $this->files->glob($this->path, '*.xml');

        foreach ($paths as $xmlPath) {
            $id = basename($xmlPath, '.xml');
            $xml = $this->files->read($xmlPath);
            $result[$id] = $xml;
        }

        return $result;
    }

    public function getOneAsXml(string $id): string
    {
        $xml = $this->path . $id . '.xml';
        return $this->files->read($xml);
    }

    public function getOneAsHtml(string $id): string
    {
        $html = $this->generatedPath . $id . '.html';
        return $this->files->read($html);
    }

    private function parseXml(string $id, string $xmlString): Post
    {
        $xml = simplexml_load_string($xmlString);
        return new Post(
            $id,
            (string) $xml->title,
            new \DateTime($xml->date),
            trim((string) $xml->content),
            (string) $xml->content['type']
        );
    }
}