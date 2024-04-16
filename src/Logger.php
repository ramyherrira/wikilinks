<?php

namespace RamyHerrira\Wikilinks;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    public function __construct(protected $logger) {}

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
        $this->logger->log($level, $message, $context);
    }
}