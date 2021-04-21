<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorParameterTransformer;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\MutableEventData;
use Morebec\Orkestra\EventSourcing\EventStore\MutableEventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * Typed implementation of an event that can be upcasted.
 */
class UpcastableEventDescriptor extends RecordedEventDescriptor
{
    /**
     * Builds an instance of this class from a {@link RecordedEventDescriptor}.
     *
     * @return UpcastableEventDescriptor
     */
    public static function fromRecordedEventDescriptor(RecordedEventDescriptor $eventDescriptor): self
    {
        return new self(
            $eventDescriptor->eventId,
            $eventDescriptor->eventType,
            $eventDescriptor->eventMetadata,
            $eventDescriptor->eventData,
            $eventDescriptor->streamId,
            $eventDescriptor->streamVersion,
            $eventDescriptor->sequenceNumber,
            $eventDescriptor->recordedAt
        );
    }

    /**
     * Returns a new instance of this descriptor with a given type.
     *
     * @param EventType|string $type
     */
    public function withType($type): self
    {
        return new self(
            $this->eventId,
            EventDescriptorParameterTransformer::stringOrEventType($type),
            $this->eventMetadata,
            $this->eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * @param EventData|array $data
     */
    public function withData($data): self
    {
        return new self(
            $this->eventId,
            $this->eventType,
            $this->eventMetadata,
            EventDescriptorParameterTransformer::arrayOrEventData($data),
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * @param EventData|array $metadata
     */
    public function withMetadata($metadata): self
    {
        return new self(
            $this->eventId,
            $this->eventType,
            EventDescriptorParameterTransformer::arrayOrEventMetadata($metadata),
            $this->eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * Returns a field in the data.
     *
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function getField(string $fieldName, $defaultValue = null)
    {
        return $this->eventData->getValue($fieldName, $defaultValue);
    }

    /**
     * Returns a new instance with a new field added to this event's data.
     *
     * @param null $defaultValue
     */
    public function withFieldAdded(string $fieldName, $defaultValue = null): self
    {
        $eventData = new MutableEventData($this->getEventData()->toArray());
        $eventData->putValue($fieldName, $defaultValue);

        return new self(
            $this->eventId,
            $this->eventType,
            $this->eventMetadata,
            $eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * Returns a new instance with a given field updated in this event's data.
     */
    public function withFieldUpdated(string $fieldName, $value): self
    {
        $eventData = new MutableEventData($this->getEventData()->toArray());
        $eventData->putValue($fieldName, $value);

        return new self(
            $this->eventId,
            $this->eventType,
            $this->eventMetadata,
            $eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * Returns a new instance of this descriptor with a field renamed in the data.
     */
    public function withFieldRenamed(string $fieldName, string $newFieldName): self
    {
        return $this->withFieldAdded($newFieldName, $this->eventData->getValue($fieldName))
                    ->withFieldRemoved($fieldName)
            ;
    }

    /**
     * Removes a field from this event's data.
     */
    public function withFieldRemoved(string $fieldName): self
    {
        $eventData = new MutableEventData($this->getEventData()->toArray());
        $eventData->removeKey($fieldName);

        return new self(
            $this->eventId,
            $this->eventType,
            $this->eventMetadata,
            $eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * Returns the value of a metadata key.
     *
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function getMetadataKey(string $metadataKey, $defaultValue = null)
    {
        return $this->eventMetadata->getValue($metadataKey, $defaultValue);
    }

    /**
     * Returns a new instance with a new metadata added to this event.
     */
    public function withMetadataKeyAdded(string $metadataKey, $defaultValue = null): self
    {
        $eventMetadata = new MutableEventMetadata($this->getEventData()->toArray());
        $eventMetadata->putValue($metadataKey, $defaultValue);

        return new self(
            $this->eventId,
            $this->eventType,
            $eventMetadata,
            $this->eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * Returns a new instance with a given metadata updated in this event's data.
     */
    public function withMetadataKeyUpdated(string $metadataKey, $value): self
    {
        $eventMetadata = new MutableEventMetadata($this->getEventData()->toArray());
        $eventMetadata->putValue($metadataKey, $value);

        return new self(
            $this->eventId,
            $this->eventType,
            $eventMetadata,
            $this->eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }

    /**
     * Returns a new instance of this descriptor with a metadata renamed.
     */
    public function withMetadataKeyRenamed(string $metadataKey, string $newMetadataKeyName): self
    {
        return $this
            ->withMetadataKeyAdded($newMetadataKeyName, $this->eventMetadata->getValue($metadataKey))
            ->withMetadataKeyRemoved($metadataKey)
            ;
    }

    /**
     * Removes a metadata from this event.
     */
    public function withMetadataKeyRemoved(string $metadataKey): self
    {
        $eventMetadata = new MutableEventMetadata($this->getEventData()->toArray());
        $eventMetadata->removeKey($metadataKey);

        return new self(
            $this->eventId,
            $this->eventType,
            $eventMetadata,
            $this->eventData,
            $this->streamId,
            $this->streamVersion,
            $this->sequenceNumber,
            $this->recordedAt
        );
    }
}
