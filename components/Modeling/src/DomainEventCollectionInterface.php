<?php

namespace Morebec\Orkestra\Modeling;

use InvalidArgumentException;
use Iterator;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

/**
 * Represents an ordered collection of domain events in order of insertion.
 * This can be used instead of simple event arrays to have more control over the collection's content.
 */
interface DomainEventCollectionInterface extends Iterator, \Countable
{
    /**
     * Adds a domain event to this collection.
     */
    public function add(DomainEventInterface $event): void;

    /**
     * Removes a domain event from this collection. Tests by equality of references.
     * If it is not part of the collection, throws an InvalidArgumentException.
     *
     * @throws InvalidArgumentException
     */
    public function remove(DomainEventInterface $event): void;

    /**
     * Clears this collection.
     */
    public function clear(): void;

    /**
     * Finds all domain events of a given type and return them as a collection.
     * The provided type can be either the classname or the domain message type name.
     *
     * @return DomainEventCollectionInterface
     */
    public function ofType(string $eventClass): self;

    /**
     * Returns the last event added to this collection or null if none matches.
     */
    public function getLast(): ?DomainEventInterface;

    /**
     * Returns the last event added to this collection that was of a given type or null
     * if none matches.
     * The provided type can be either the classname or the domain message type name.
     */
    public function getLastOfType(string $eventClass): ?DomainEventInterface;

    /**
     * Returns the first event that was added to this collection or null if it is empty.
     */
    public function getFirst(): ?DomainEventInterface;

    /**
     * Returns the first event of a given type that was added to this collection or null if none matches.
     * The provided type can be either the classname or the domain message type name.
     */
    public function getFirstOfType(string $eventClass): ?DomainEventInterface;

    /**
     * Filters this collection by a given predicate and returns another collection
     * with the elements that matched.
     */
    public function filter(callable $predicate): self;

    /**
     * Converts this collection to an array.
     *
     * @return DomainEventInterface[]
     */
    public function toArray(): array;

    /**
     * Indicates if this collection is empty or not.
     */
    public function isEmpty(): bool;

    /**
     * Returns a copy of this collection.
     */
    public function copy(): self;
}
