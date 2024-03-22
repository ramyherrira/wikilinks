<?php

namespace RamyHerrira\Wikilinks;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'play')]
class GameCommand extends Command
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
        [
            'title' => $titleA,
            'url' => $urlA,
        ] = $this->wiki->getRandomPage();
        $output->writeln("<info>Article A</info>: <href=$urlA>$titleA</>");

        $output->writeln("\n========================================\n");

        $output->writeln('<comment>Fetching article B...</comment>');
        [
            'title' => $titleB,
            'url' => $urlB,
        ] = $this->wiki->getRandomPage();
        $output->writeln("<info>Article B</info>: <href=$urlB>$titleB</>");

        $output->writeln("\n========================================");
        $output->writeln("========================================\n");

        $question = new Question(
            "<question>Can you guess how many links is Article A away from Article B ?</question>\n>"
        );
        
        while (true) {
            $guess = $helper->ask($input, $output, $question);
        }


        return Command::SUCCESS;
    }
}