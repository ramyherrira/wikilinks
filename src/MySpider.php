<?php

namespace RamyHerrira\Wikilinks;

use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\Middleware\MaximumCrawlDepthMiddleware;

class MySpider extends BasicSpider
{
    public array $spiderMiddleware = [
        [
            MaximumCrawlDepthMiddleware::class,
            ['maxCrawlDepth' => 5],
        ],
    ];
    

    public function parse(Response $response): \Generator
    {
        $pages = $response
            ->filter('#bodyContent p a[href^="/wiki/"]')
            ->links();
        $page = $pages[array_rand($pages)];

        
        yield $this->item([
            'title' => $response->filter('#firstHeading > span, i')->text(),
            'description' => '',
            'url' => $response->getUri(),
        ]);

        yield $this->request('GET', $page->getUri());


        // foreach ($pages as $page) {
        //     // Since we’re not specifying the second parameter, 
        //     // all article pages will get handled by the 
        //     // spider’s `parse` method.
        //     //var_dump($page->getUri());

        //         yield $this->item([
        //             'title' => 'Ferdinand_von_Richthofen',
        //             'description' => '',
        //             'url' => $page->getUri(),
        //         ]);


        //     }
        //     $url = $page->getUri();
        //     $path = [];
        //     preg_match('/(?:\/wiki\/)(.*)/i', $url, $path);
        //     // var_dump($path);
        //     $url = preg_replace('/wiki(\/.*)/i', 'wiki/'.urlencode($path[1]), $url);
        //     // var_dump($url);
        //     // die();
            

        //     yield $this->request('GET', $url);
        // }
    }

    protected function encodeUrl(string $url): string
    {
        $path = [];
        preg_match('/(?:\/wiki\/)(.*)/i', $url, $path);

        return preg_replace('/wiki(\/.*)/i', 'wiki/'.urlencode($path[1]), $url);
    }
}