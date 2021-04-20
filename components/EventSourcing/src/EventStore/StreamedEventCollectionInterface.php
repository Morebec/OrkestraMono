<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents a collection of events as read from a given stream.
 * This is an immutable collection.
 */
interface StreamedEventCollectionInterface extends \Iterator, \Countable
{
    /**
     * Builds a new instance of this collection using a given array of events
     * and an eventId as the starting point of this collection.
     * The starting point is never included in this collection and contains
     * all events read after this point.
     * If the starting event is null, it means that this stream was read from the beginning.
     *
     * @param RecordedEventDescriptor[] $events
     *
     * @return StreamedEventCollectionInterface
     */
    public function build(EventStreamId $streamId, array $events): self;

    /**
     * Returns the first event in this collection or null if empty.
     *
     * @return RecordedEventDescriptor
     */
    public function getFirst(): ?RecordedEventDescriptor;

    /**
     * Returns the last event or null if empty.
     */
    public function getLast(): ?RecordedEventDescriptor;

    /**
     * Returns an Array representation of this stream.
     *
     * @return RecordedEventDescriptor[]
     */
    public function toArray(): array;

    /**
     * Returns the ID of the event stream, from which these events have been read.
     */
    public function getEventStreamId(): EventStreamId;

    /**
     * Returns the number of events in this stream.
     */
    public function getCount(): int;

    /**
     * Indicates if the collection is empty or not.
     */
    public function isEmpty(): bool;
}
