<?php

namespace RamyHerrira\Wikilinks;

use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;

class SearchSpider extends BasicSpider
{
    public array $extensions = [];

     public function parse(Response $response): \Generator
    {
        $pages = $response
            ->filter('#bodyContent p a[href^="/wiki/"]')
            ->links();
        $page = $pages[array_rand($pages)];
        $title = $response->filter('#firstHeading > span, i')->text();
        $text = $response
            ->filter("#mw-content-text div p")
            ->extract(['_text']);
        $description = empty(trim($text[0])) ? $text[1] : $text[0];
        $description = preg_replace("/$title/i", str_repeat('*', strlen($title)), $description);


        yield $this->item([
            'title' => $title,
            'description' => $description,
            'url' => $response->getUri(),
        ]);

        yield $this->request('GET', $page->getUri());
    }

    protected function encodeUrl(string $url): string
    {
        $path = [];
        preg_match('/(?:\/wiki\/)(.*)/i', $url, $path);

        return preg_replace('/wiki(\/.*)/i', 'wiki/'.urlencode($path[1]), $url);
    }
}