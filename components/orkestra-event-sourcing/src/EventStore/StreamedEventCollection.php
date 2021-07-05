<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Implementation of an Streamed Event Collection with events stored in an in-memory array.
 * TODO: Add Tests.
 */
class StreamedEventCollection implements StreamedEventCollectionInterface
{
    private EventStreamId $streamId;

    /**
     * @var RecordedEventDescriptor[]
     */
    private array $events;

    /**
     * StreamedEventCollection constructor.
     *
     * @param RecordedEventDescriptor[] $events
     */
    public function __construct(EventStreamId $streamId, array $events)
    {
        $this->events = [];
        foreach ($events as $event) {
            $this->add($event);
        }
        $this->streamId = $streamId;
    }

    public function build(EventStreamId $streamId, array $events): StreamedEventCollectionInterface
    {
        return new self($streamId, $events);
    }

    public function getFirst(): ?RecordedEventDescriptor
    {
        return $this->events[0] ?? null;
    }

    public function getLast(): ?RecordedEventDescriptor
    {
        return $this->events[$this->getCount() - 1] ?? null;
    }

    public function toArray(): array
    {
        return $this->events;
    }

    public function getEventStreamId(): EventStreamId
    {
        return $this->streamId;
    }

    public function getCount(): int
    {
        return \count($this->events);
    }

    public function isEmpty(): bool
    {
        return $this->getCount() === 0;
    }

    /**
     * @return RecordedEventDescriptor
     */
    public function current()
    {
        return current($this->events);
    }

    /**
     * @return EventDescriptorInterface
     */
    public function next()
    {
        return next($this->events);
    }

    /**
     * @return int|null
     */
    public function key()
    {
        return key($this->events);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return \array_key_exists($this->key(), $this->events);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->events);
    }

    public function count()
    {
        return $this->getCount();
    }

    /**
     * Adds an event to this collection.
     */
    private function add(RecordedEventDescriptor $event): void
    {
        $this->events[] = $event;
    }
}
