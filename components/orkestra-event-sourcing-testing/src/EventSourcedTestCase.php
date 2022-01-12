<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Doctrine\DBAL\Connection;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class EventSourcedTestCase extends KernelTestCase
{
    use PHPMatcherAssertions;

    protected EventStoreInterface $eventStore;

    protected MessageBusInterface $messageBus;

    protected MessageNormalizerInterface $messageNormalizer;

    protected ClockInterface $clock;

    protected Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->eventStore = $container->get(EventStoreInterface::class);
        $this->messageBus = $container->get(MessageBusInterface::class);
        $this->messageNormalizer = $container->get(MessageNormalizerInterface::class);
        $this->clock = $container->get(ClockInterface::class);

        $this->connection = $container->get(Connection::class);

        $this->setupDependencies($container);
    }

    public function setupDependencies(ContainerInterface $container): void
    {
    }

    /**
     * Allows defining a new test scenario.
     */
    public function defineScenario(): TestScenarioBuilder
    {
        return new TestScenarioBuilder($this->clock, $this->eventStore, $this->messageNormalizer, $this->messageBus);
    }

    public function getEventStore(): EventStoreInterface
    {
        return $this->eventStore;
    }

    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

    public function getMessageNormalizer(): MessageNormalizerInterface
    {
        return $this->messageNormalizer;
    }

    public function getClock(): ClockInterface
    {
        return $this->clock;
    }
}
