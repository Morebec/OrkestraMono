<?php

namespace Tests\Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\UpcastingEventStoreDecorator;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventStore\EventStoreConfiguration;
use PHPUnit\Framework\TestCase;

class EventStoreConfigurationTest extends TestCase
{
    public function testDecoratedBy(): void
    {
        $configuration = new EventStoreConfiguration();
        $configuration->decoratedBy(UpcastingEventStoreDecorator::class);

        self::assertContains(UpcastingEventStoreDecorator::class, $configuration->decorators);
    }

    public function testUsingImplementation(): void
    {
        $configuration = new EventStoreConfiguration();
        $configuration->usingImplementation(InMemoryEventStore::class);

        self::assertEquals(InMemoryEventStore::class, $configuration->implementationClassName);
    }

    public function testNotDecoratedBy(): void
    {
        $configuration = new EventStoreConfiguration();
        $configuration->decoratedBy(UpcastingEventStoreDecorator::class);
        $configuration->notDecoratedBy(UpcastingEventStoreDecorator::class);

        self::assertNotContains(UpcastingEventStoreDecorator::class, $configuration->decorators);
    }

    public function testWithSubscriber(): void
    {
        $configuration = new EventStoreConfiguration();
        $configuration->withSubscriber(EventStoreSubscriberInterface::class, '$all');

        self::assertArrayHasKey(EventStoreSubscriberInterface::class, $configuration->subscribers);
    }

    public function testUsingInMemoryImplementation(): void
    {
        $configuration = new EventStoreConfiguration();
        $configuration->usingInMemoryImplementation();

        self::assertEquals(InMemoryEventStore::class, $configuration->implementationClassName);
    }

    public function testWithUpcaster(): void
    {
        $configuration = new EventStoreConfiguration();
        $configuration->withUpcaster(UpcasterInterface::class);

        self::assertContains(UpcasterInterface::class, $configuration->upcasters);
    }
}
