<?php

namespace Morebec\Orkestra\Modeling;

use InvalidArgumentException;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

/**
 * Simple default implementation of an {@link DomainEventCollectionInterface} that uses
 * an array internally.
 */
class DomainEventCollection implements DomainEventCollectionInterface
{
    /**
     * @var DomainEventInterface[]
     */
    private array $events;

    /**
     * DomainEventCollection constructor.
     *
     * @param DomainEventInterface[] $domainEvents
     */
    public function __construct(iterable $domainEvents = [])
    {
        $this->events = [];
        foreach ($domainEvents as $domainEvent) {
            $this->add($domainEvent);
        }
    }

    public function add(DomainEventInterface $event): void
    {
        $this->events[] = $event;
    }

    public function remove(DomainEventInterface $event): void
    {
        $nbEvents = \count($this->events);
        $this->events = $this->filter(static fn ($e) => $e !== $event)->toArray();

        if ($nbEvents === \count($this->events)) {
            throw new InvalidArgumentException('Domain Event was not found in collection.');
        }
    }

    public function clear(): void
    {
        $this->events = [];
    }

    public function ofType(string $eventClass): DomainEventCollectionInterface
    {
        return $this->filter(static fn (DomainEventInterface $event) => is_a($event, $eventClass, true) || $event::getTypeName() === $eventClass);
    }

    public function filter(callable $predicate): DomainEventCollectionInterface
    {
        $filtered = array_filter($this->events, $predicate);

        return new self($filtered);
    }

    public function map(callable $callable): array
    {
        return array_map($callable, $this->events);
    }

    public function toArray(): array
    {
        return $this->events;
    }

    public function isEmpty(): bool
    {
        return $this->getCount() === 0;
    }

    public function getLast(): ?DomainEventInterface
    {
        $nbEvents = \count($this->events);

        return $this->events[$nbEvents - 1] ?? null;
    }

    public function getLastOfType(string $eventClass): ?DomainEventInterface
    {
        return $this->ofType($eventClass)->getLast();
    }

    public function getFirst(): ?DomainEventInterface
    {
        return $this->events[0] ?? null;
    }

    public function getFirstOfType(string $eventClass): ?DomainEventInterface
    {
        return $this->ofType($eventClass)->getFirst();
    }

    public function copy(): DomainEventCollectionInterface
    {
        return new self($this->events);
    }

    public function getCount(): int
    {
        return \count($this->events);
    }

    public function current()
    {
        return current($this->events);
    }

    public function next()
    {
        next($this->events);
    }

    public function key()
    {
        return key($this->events);
    }

    public function valid()
    {
        return \array_key_exists($this->key(), $this->events);
    }

    public function rewind()
    {
        reset($this->events);
    }

    public function count(): int
    {
        return $this->getCount();
    }
}
