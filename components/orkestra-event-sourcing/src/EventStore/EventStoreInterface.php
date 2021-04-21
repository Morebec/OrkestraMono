<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents a Generic interface for working with an event store.
 * An event store is a simple store managing events in an orderly fashion.
 * They are ordered by order of insertion.
 * The basic requirements for the event store are:
 * - Appending events to a stream of events.
 * - Reading events back from stream in write order.
 * - Protecting against concurrency issues using optimistic concurrency control with the use of a stream version.
 */
interface EventStoreInterface
{
    /**
     * Returns the ID of the stream that is considered the "global" or "all" stream.
     */
    public function getGlobalStreamId(): EventStreamId;

    /**
     * Appends events to a given stream.
     * If the stream does not exist, it will get implicitly created.
     * It takes a stream version parameter to detect if there has been any concurrent appends to
     * this stream, to enforce consistency boundaries when required.
     * This parameter can be null in cases where a consistency check is deemed unnecessary.
     *
     * @param EventDescriptorInterface[] $eventDescriptors
     *
     * @throws ConcurrencyException
     */
    public function appendToStream(EventStreamId $streamId, iterable $eventDescriptors, AppendStreamOptions $options): void;

    /**
     * Reads an event stream using a given set of options.
     *
     * @throws EventStreamNotFoundException
     */
    public function readStream(EventStreamId $streamId, ReadStreamOptions $options): StreamedEventCollectionInterface;

    /**
     * Returns an event stream's information or null if the stream does not exist.
     *
     * @return ?EventStreamInterface
     */
    public function getStream(EventStreamId $streamId): ?EventStreamInterface;

    /**
     * Indicates if a given stream exists or not.
     */
    public function streamExists(EventStreamId $streamId): bool;

    /**
     * Allows to subscribe to a stream.
     */
    public function subscribeToStream(EventStreamId $streamId, EventStoreSubscriberInterface $subscriber): void;
}
