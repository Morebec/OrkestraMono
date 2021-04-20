<?php

namespace Morebec\Orkestra\Modeling;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

/**
 * Aggregate root capable of internally keeping a list of events as they happen.
 */
abstract class AbstractEventEmittingAggregateRoot implements AggregateRootInterface
{
    /** @var DomainEventCollectionInterface */
    private $domainEvents;

    public function __construct()
    {
        $this->domainEvents = new DomainEventCollection();
    }

    /**
     * Clears the list of domain events.
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents->clear();
    }

    /**
     * Returns a copy of the collection of events managed by this entity.
     */
    public function getDomainEvents(): DomainEventCollectionInterface
    {
        return $this->domainEvents->copy();
    }

    /**
     * Adds an event to the list of uncommitted changes.
     */
    public function recordDomainEvent(DomainEventInterface $event): void
    {
        $this->domainEvents->add($event);
    }
}
