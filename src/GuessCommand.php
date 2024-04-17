<?php

namespace RamyHerrira\Wikilinks;


use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use RoachPHP\Spider\Middleware\MaximumCrawlDepthMiddleware;
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

        $articleA = $this->fetchArticleA($output);

        $confirmationQuestion = new ConfirmationQuestion(
            "<question>Do you want to starting with this article ?</question> ",
            false
        );

        while (! $helper->ask($input, $output, $confirmationQuestion)) {
            $articleA = $this->fetchArticleA($output);
        }
        // $output->writeln("<comment>Description</comment>:\n{$articleA->getDescription()}");
        $output->writeln("=======================================================\n");


        $output->writeln("<comment>Searching for the B article...</comment>\n\n");

        $articleB = $this->crawlForARandomArtcle($articleA, $clickCount = 2);

        $output->writeln("=======================================================\n\n\n");

        $titleB = $articleB->getTitle();
        $urlB = $articleB->getUrl();
        $output->writeln("<info>Article B</info>: <href=$urlB>$titleB</>");
        // $output->writeln("<comment>Description</comment>:\n{$articleB->getDescription()}");

        $output->writeln("\n===================Try to make in {$clickCount} tries==============");
        $output->writeln("=======================================================\n\n\n");

        $links = $this->wiki->listArticles($articleA->getUrl());

        $link = $this->askForWhichArticle($helper, $input, $output, $links);

        while ($link !== $urlB) {
            $links = $this->wiki->listArticles($link);
            $links[] = $articleA->getUrl();

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

    protected function fetchArticleA($output): Article
    {
        $output->writeln('<comment>Fetching article A...</comment>');

        $articleA = $this->wiki->getRandomPage();

        $output->writeln("<info>Article A</info>: <href={$articleA->getUrl()}>{$articleA->getTitle()}</>");

        return $articleA;
    }

    protected function crawlForARandomArtcle(Article $article, int $clickCount = 2): Article
    {
        $items = Roach::collectSpider(
            SearchSpider::class,
            new Overrides(
                startUrls: [$article->getUrl()],
                spiderMiddleware: [
                    [
                        MaximumCrawlDepthMiddleware::class,
                        ['maxCrawlDepth' => $clickCount + 1],
                    ],
                ],
                extensions:[
                    LoggerExtension::class,
                ],
            ),
        );


        $item = $items[count($items) - 1];

        return new Article([
            'title' => $item->get('title'),
            'url' => $item->get('url'),
            'description' => $item->get('description'),
        ]);
    }
}