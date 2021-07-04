<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\UpcastingEventStoreDecorator;

class EventStoreConfiguration
{
    /** @var string */
    public $implementationClassName;

    /** @var string[] */
    public $decorators = [];

    /** @var string[] */
    public $subscribers = [];

    /** @var string[] */
    public $upcasters = [];

    /**
     * Configures this event store to use the {@link InMemoryEventStore} implementation.
     *
     * @return $this
     */
    public function usingInMemoryImplementation(): self
    {
        $this->implementationClassName = InMemoryEventStore::class;

        return $this;
    }

    /**
     * Configures the event store to use a certain implementation.
     *
     * @return $this
     */
    public function usingImplementation(string $className): self
    {
        $this->implementationClassName = $className;

        return $this;
    }

    /**
     * Decorates the Event Store Service.
     *
     * @return $this
     */
    public function decoratedBy(string $className): self
    {
        $this->decorators[] = $className;

        return $this;
    }

    /**
     * Configures an event store subscriber.
     *
     * @return EventStoreConfiguration
     */
    public function withSubscriber(string $className, string $streamId): self
    {
        $this->subscribers[$className] = $streamId;

        return $this;
    }

    /**
     * Configures an upcaster with the event store.
     * This requires the {@link UpcastingEventStoreDecorator}.
     *
     * @return $this
     */
    public function withUpcaster(string $className): self
    {
        $this->upcasters[] = $className;

        return $this;
    }
}
