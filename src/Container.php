<?php

declare(strict_types=1);

namespace RamyHerrira\Wikilinks;

use League\Container\Container as BaseContainer;
use League\Container\ReflectionContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RoachPHP\Core\Engine;
use RoachPHP\Core\EngineInterface;
use RoachPHP\Core\Runner;
use RoachPHP\Core\RunnerInterface;
use RoachPHP\Http\Client;
use RoachPHP\Http\ClientInterface;
use RoachPHP\ItemPipeline\ItemPipeline;
use RoachPHP\ItemPipeline\ItemPipelineInterface;
use RoachPHP\Scheduling\ArrayRequestScheduler;
use RoachPHP\Scheduling\RequestSchedulerInterface;
use RoachPHP\Scheduling\Timing\ClockInterface;
use RoachPHP\Scheduling\Timing\SystemClock;
use RoachPHP\Shell\Resolver\NamespaceResolverInterface;
use RoachPHP\Shell\Resolver\StaticNamespaceResolver;
use RoachPHP\Testing\FakeLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class Container implements ContainerInterface
{
    private BaseContainer $container;

    public function __construct()
    {
        $this->container = (new BaseContainer())->delegate(new ReflectionContainer());

        $this->registerRoachBindings();
    }

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    private function registerRoachBindings(): void
    {
        $this->container->addShared(
            ContainerInterface::class,
            $this->container,
        );
        // $this->container->addShared(
        //     LoggerInterface::class,
        //     static fn () => (new Logger('roach'))->pushHandler(new StreamHandler('php://stdout')),
        // );
        $this->container->add(
            LoggerInterface::class,
            // FakeLogger::class,
            static fn () => new Logger((new MonologLogger('wikilinks'))->pushHandler(new StreamHandler('php://stdout'))),
        );
        $this->container->addShared(EventDispatcher::class, EventDispatcher::class);
        $this->container->addShared(EventDispatcherInterface::class, EventDispatcher::class);
        $this->container->add(ClockInterface::class, SystemClock::class);
        $this->container->add(
            RequestSchedulerInterface::class,
            /** @psalm-suppress MixedReturnStatement, MixedInferredReturnType */
            fn (): RequestSchedulerInterface => $this->container->get(ArrayRequestScheduler::class),
        );
        $this->container->add(ClientInterface::class, Client::class);
        $this->container->add(
            ItemPipelineInterface::class,
            /** @psalm-suppress MixedReturnStatement, MixedInferredReturnType */
            fn (): ItemPipelineInterface => $this->container->get(ItemPipeline::class),
        );
        $this->container->add(NamespaceResolverInterface::class, StaticNamespaceResolver::class);
        $this->container->add(
            EngineInterface::class,
            /** @psalm-suppress MixedReturnStatement, MixedInferredReturnType */
            fn (): EngineInterface => $this->container->get(Engine::class),
        );
        $this->container->add(
            RunnerInterface::class,
            /** @psalm-suppress MixedArgument */
            fn (): RunnerInterface => new Runner($this->container, $this->container->get(EngineInterface::class)),
        );
    }
}
