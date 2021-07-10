<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\UpcastingEventStoreDecorator;

/**
 * Configures the event store dependencies.
 */
class EventStoreConfiguration
{
    public string $implementationClassName;

    /** @var string[] */
    public array $decorators = [];

    /** @var string[] */
    public array $subscribers = [];

    /** @var string[] */
    public array $upcasters = [];

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
     * Removes a previously defined decorator.
     *
     * @return $this
     */
    public function notDecoratedBy(string $className): self
    {
        $this->decorators = array_filter($this->decorators, static fn (string $decorator) => $decorator !== $className);

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
