<?php

namespace tthe\services\posts;

readonly class Post
{
    public string $id;
    public string $title;
    public \DateTime $date;
    public string $content;

    public function __construct(string $id, string $title, \DateTime $date, string $content)
    {
        $this->id = $id;
        $this->title = $title;
        $this->date = $date;
        $this->content = $content;
    }

    public static function parseXml(string $xml): Post
    {
        // XML parsing here...
        return new static("temp", "Temp", new \DateTime(), "Temp content.");
    }


    /*
    function xmlToArraySimple($xmlString) {
        $xml = simplexml_load_string($xmlString);

        // Convert to array and handle attributes
        $array = [
            'title' => (string) $xml->title,
            'date' => (string) $xml->date,
            'content' => [
                'type' => (string) $xml->content['type'],
                'value' => trim((string) $xml->content)
            ]
        ];

        return $array;
    }
    */
}