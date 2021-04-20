<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * An Event Descriptor is a informational wrapper around an event
 * so that the event store can effectively work with them.
 * Event descriptors are essentially what are going to be saved in the database.
 * The Event Store never works directly with events coming from the outside world, as it only understands
 * {@link EventDescriptorInterface}.
 */
interface EventDescriptorInterface
{
    /**
     * Returns the ID of the event.
     */
    public function getEventId(): EventId;

    /**
     * Returns the Type of the event.
     */
    public function getEventType(): EventType;

    /**
     * Returns the event described by this descriptor instance.
     */
    public function getEventData(): EventData;

    /**
     * Represents additional metadata about an event.
     */
    public function getEventMetadata(): EventMetadata;
}
