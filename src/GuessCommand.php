<?php

namespace RamyHerrira\Wikilinks;

use Psr\Log\LoggerInterface;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use RoachPHP\Spider\Middleware\MaximumCrawlDepthMiddleware;
use RoachPHP\Testing\FakeLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(name: 'guess')]
class GuessCommand extends Command
{
    protected $wiki;

    public function __construct(string|null $name = null)
    {
        parent::__construct($name);
        $this->wiki = new WikiParser();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper */
        $helper = $this->getHelper('question');

        $output->writeln('Wikilinks starting...');

        $output->writeln('<comment>Fetching article A...</comment>');
        // @todo Article value object
        [
            'title' => $titleA,
            'description' => $descriptionA,
            'url' => $urlA,
        ] = $this->wiki->getRandomPage();
        $output->writeln("<info>Article A</info>: <href=$urlA>$titleA</>");
        
        $confirmationQuestion = new ConfirmationQuestion(
            "<question>Do you want to starting with this article ?</question>\n\n>",
            false
        );

        while (! $helper->ask($input, $output, $confirmationQuestion)) {
            $output->writeln('<comment>Fetching article A...</comment>');
            [
                'title' => $titleA,
                'description' => $descriptionA,
                'url' => $urlA,
            ] = $this->wiki->getRandomPage();
            $output->writeln("<info>Article A</info>: <href=$urlA>$titleA</>");
        }
        $output->writeln("<comment>Description</comment>:\n{$descriptionA}");

        $output->writeln("\n========================================\n\n\n");


        $output->writeln('<comment>Searching for the B article...</comment>');

        $items = Roach::collectSpider(
            SearchSpider::class,
            new Overrides(
                startUrls: [$urlA],
                spiderMiddleware: [
                    [
                        MaximumCrawlDepthMiddleware::class,
                        ['maxCrawlDepth' => $clickCount = 4],
                    ],
                ],
            ),
        );


        $item = $items[count($items) - 1];

        $titleB = $item->get('title');
        $urlB = $item->get('url');
        $output->writeln("<info>Article B</info>: <href=$urlB>$titleB</>");
        $output->writeln("<comment>Description</comment>:\n{$item->get('description')}");

        $output->writeln("\n===================You have {$clickCount} tries==============");
        $output->writeln("=================================================\n\n\n");

        $links = $this->wiki->listArticles($urlA);

        $link = $this->askForWhichArticle($helper, $input, $output, $links);

        while ($link !== $urlB) {
            $links = $this->wiki->listArticles($link);
            $links[] = $urlA;

            // @todo suggestion autocomplete ? too many articles
            $link = $this->askForWhichArticle($helper, $input, $output, $links);
        }

        $output->writeln("\n\n=================================================");
        $output->writeln("\n\n=============Congratulations you won !===========\n\n\n");
        $output->writeln("=================================================\n\n\n");


        return Command::SUCCESS;
    }

    protected function askForWhichArticle($helper, $input, $output, $links): string
    {
        $choiceQuestion = new ChoiceQuestion(
            'Choose from which article to go ?',
            $links,
        );

        $article = $helper->ask($input, $output, $choiceQuestion);
        $output->writeln('You have just selected: ' . $article);

        return $article;
    }
}