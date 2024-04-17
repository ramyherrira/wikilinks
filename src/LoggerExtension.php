<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace RamyHerrira\Wikilinks;

use RoachPHP\Extensions\ExtensionInterface;
use Psr\Log\LoggerInterface;
use RoachPHP\Events\ItemScraped;
use RoachPHP\Support\Configurable;

final class LoggerExtension implements ExtensionInterface
{
    use Configurable;

    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ItemScraped::NAME => ['onItemScraped', 100],
        ];
    }

    public function onItemScraped(ItemScraped $event): void
    {
        $this->logger->info($event->item->get('description'));
    }
}