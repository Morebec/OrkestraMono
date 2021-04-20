<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * A Stream is an ordered collection of events inside the event store.
 * This class is used to provide information about a stream.
 * It does not contain the events making up this stream.
 *
 * Streams handle events internally using optimistic concurrency by keeping a
 * version ID which is updated everytime a new event is appended to the stream.
 * The event store methods make use of this version number to determine whether a
 * concurrency issue has occurred.
 *
 * It is modeled as an interface to allow greater control for specific storage implementations.
 */
interface EventStreamInterface
{
    /**
     * Returns the ID of the stream.
     */
    public function getId(): EventStreamId;

    /**
     * Returns the Version of a Stream.
     */
    public function getVersion(): EventStreamVersion;
}
