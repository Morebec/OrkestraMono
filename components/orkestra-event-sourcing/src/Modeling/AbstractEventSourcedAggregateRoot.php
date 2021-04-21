<?php

namespace Morebec\Orkestra\EventSourcing\Modeling;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Modeling\AbstractEventEmittingAggregateRoot;
use Morebec\Orkestra\Modeling\DomainEventCollection;

/**
 * Implementation of an Event Sourced Aggregate Root.
 */
abstract class AbstractEventSourcedAggregateRoot extends AbstractEventEmittingAggregateRoot
{
    public const INITIAL_VERSION_NUMBER = EventStreamVersion::INITIAL_VERSION;

    /**
     * Current version of this aggregate, used for optimistic concurrency control.
     *
     * @var EventSourcedAggregateRootVersion
     */
    protected $version;

    final public function __construct()
    {
        parent::__construct();
        $this->version = EventSourcedAggregateRootVersion::fromInt(self::INITIAL_VERSION_NUMBER);
    }

    /**
     * Reloads this aggregate from a history of previous events.
     *
     * @return static
     */
    public static function loadFromHistory(DomainEventCollection $events): self
    {
        $a = new static();
        $a->applyHistory($events);

        return $a;
    }

    /**
     * Records a new event as having happened to this aggregate.
     */
    public function recordDomainEvent(DomainEventInterface $event): void
    {
        parent::recordDomainEvent($event);

        // Apply the event.
        $this->onDomainEvent($event);
    }

    public function getVersion(): EventSourcedAggregateRootVersion
    {
        return $this->version;
    }

    /**
     * Reapplies a past history to this aggregate.
     */
    protected function applyHistory(DomainEventCollection $events): void
    {
        foreach ($events->toArray() as $event) {
            $this->version = $this->version->next();
            $this->onDomainEvent($event);
        }
    }

    /**
     * Method called to apply a domain event to this aggregate's state.
     */
    abstract protected function onDomainEvent(DomainEventInterface $event): void;
}
