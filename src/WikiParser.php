<?php

namespace RamyHerrira\Wikilinks;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class WikiParser
{
    protected $client;

    public function getRandomPage()
    {   
        $response = $this->getClient()->get('https://en.wikipedia.org/wiki/Special:Random');
        
        $crawler = new Crawler($response->getBody());

        $title = $crawler->filter('#firstHeading > span, i')->text();
        $text = $crawler->filter("#mw-content-text div p")->extract(['_text']);

        return new Article([
            'title' => $title,
            'description' => empty(trim($text[0])) ? $text[1] : $text[0],
            'url' => $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER)[0],
        ]);
    }

    /** @return array<string> */
    public function listArticles($url): array
    {
        $response = $this->getClient()->get($url);

        $crawler = new Crawler($response->getBody(), $url);

        return array_map(
            fn ($l) => $l->getUri(),
            $crawler
                ->filter('#bodyContent p a[href^="/wiki/"]')
                ->links()
        );
    }

    protected function getClient()
    {
        return $this->client ?? $this->client = new Client([
            'allow_redirects' => ['track_redirects' => true],
        ]);
    }
}