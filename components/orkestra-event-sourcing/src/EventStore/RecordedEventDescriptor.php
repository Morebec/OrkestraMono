<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\DateTime\DateTime;

/**
 * Implementation of an Event Descriptor that was appended to a stream.
 * TODO: Add Tests.
 */
class RecordedEventDescriptor implements EventDescriptorInterface
{
    protected EventId $eventId;

    protected EventType $eventType;

    protected EventMetadata $eventMetadata;

    protected EventStreamId $streamId;

    protected EventStreamVersion $streamVersion;

    protected EventData $eventData;

    protected DateTime $recordedAt;

    protected EventSequenceNumber $sequenceNumber;

    public function __construct(
        EventId $eventId,
        EventType $eventType,
        EventMetadata $eventMetadata,
        EventData $eventData,
        EventStreamId $streamId,
        EventStreamVersion $streamVersion,
        EventSequenceNumber $sequenceNumber,
        DateTime $recordedAt
    ) {
        $this->eventId = $eventId;
        $this->eventType = $eventType;
        $this->eventMetadata = $eventMetadata;
        $this->streamId = $streamId;
        $this->streamVersion = $streamVersion;
        $this->eventData = $eventData;
        $this->recordedAt = $recordedAt;
        $this->sequenceNumber = $sequenceNumber;
    }

    /**
     * Constructs a new instance from an Event Descriptor.
     */
    public static function fromEventDescriptor(
        EventDescriptorInterface $eventDescriptor,
        EventStreamId $streamId,
        EventStreamVersion $streamVersion,
        EventSequenceNumber $sequenceNumber,
        DateTime $recordedAt
    ): self {
        return new self(
            $eventDescriptor->getEventId(),
            $eventDescriptor->getEventType(),
            $eventDescriptor->getEventMetadata(),
            $eventDescriptor->getEventData(),
            $streamId,
            $streamVersion,
            $sequenceNumber,
            $recordedAt
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEventId(): EventId
    {
        return $this->eventId;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData(): EventData
    {
        return $this->eventData;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventMetadata(): EventMetadata
    {
        return $this->eventMetadata;
    }

    /**
     * Returns the stream into which this event was recorded.
     */
    public function getStreamId(): EventStreamId
    {
        return $this->streamId;
    }

    /**
     * Returns the version of the stream at which this event was appended.
     */
    public function getStreamVersion(): EventStreamVersion
    {
        return $this->streamVersion;
    }

    public function getSequenceNumber(): EventSequenceNumber
    {
        return $this->sequenceNumber;
    }

    /**
     * Returns the date and time at which this event was recorded in the store.
     */
    public function getRecordedAt(): DateTime
    {
        return $this->recordedAt;
    }
}
