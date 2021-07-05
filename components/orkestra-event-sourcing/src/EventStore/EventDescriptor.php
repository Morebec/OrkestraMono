<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Implementation of an Event descriptor representing an event to be appended to a stream.
 */
class EventDescriptor implements EventDescriptorInterface
{
    private EventId $id;

    private EventType $type;

    private EventMetadata $metadata;

    private EventData $data;

    public function __construct(EventId $id, EventType $type, EventData $data, ?EventMetadata $metadata = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
        $this->metadata = $metadata ?: new EventMetadata();
    }

    /**
     * {@inheritDoc}
     */
    public function getEventId(): EventId
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): EventType
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventData(): EventData
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventMetadata(): EventMetadata
    {
        return $this->metadata;
    }
}
