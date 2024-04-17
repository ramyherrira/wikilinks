<?php

namespace RamyHerrira\Wikilinks;

class Article
{
    public function __construct(protected array $attributes)
    {
    }

    public function getTitle(): string
    {
        return $this->attributes['title'];
    }

    public function getDescription(): string
    {
        return $this->attributes['description'];
    }

    public function getUrl(): string
    {
        return $this->attributes['url'];
    }
}