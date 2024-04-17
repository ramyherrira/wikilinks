<?php

namespace RamyHerrira\Wikilinks;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class SymfonyLogger extends AbstractLogger
{
    public function __construct(protected ConsoleOutputInterface $logger) {}

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string|\Stringable $message
     * @param array $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->writeln("<info>**Hint**</info>: {$message}");
    }
}