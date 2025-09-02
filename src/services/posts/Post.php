<?php

namespace tthe\services\posts;

readonly class Post
{
    public string $id;
    public string $title;
    public \DateTime $date;
    public string $content;
    public string $contentType;

    public function __construct(string $id, string $title, \DateTime $date, string $content, string $contentType)
    {
        $this->id = $id;
        $this->title = $title;
        $this->date = $date;
        $this->content = $content;
        $this->contentType = $contentType;
    }
}