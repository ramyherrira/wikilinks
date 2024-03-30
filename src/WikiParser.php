<?php

namespace RamyHerrira\Wikilinks;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class WikiParser
{
    protected $client;

    public function getRandomPage()
    {   
        $response = $this->getClient()->get('https://en.wikipedia.org/wiki/Special:Random');
        
        //var_dump($response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER)[0]);

        $crawler = new Crawler($response->getBody());

        $title = $crawler->filter('#firstHeading > span, i')->text();

        return [
            'title' => $title,
            'url' => $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER)[0],
        ];
    }

    protected function getClient()
    {
        return $this->client ?? $this->client = new Client([
            'allow_redirects' => ['track_redirects' => true],
        ]);
    }
}