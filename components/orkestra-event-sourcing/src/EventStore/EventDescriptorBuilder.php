<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Fluent Builder to create an Event Descriptor.
 * Calling withMetadata and withData is optional.
 */
class EventDescriptorBuilder
{
    /** @var EventId|null */
    private $eventId;

    /** @var EventType|null */
    private $eventType;

    /**
     * @var EventData
     */
    private $eventData;

    /**
     * @var EventMetadata
     */
    private $eventMetadata;

    public static function create(): self
    {
        $b = new self();
        $b->withData([]);
        $b->withMetadata([]);

        return $b;
    }

    /**
     * @param EventId|string $eventId
     *
     * @return $this
     */
    public function withId($eventId): self
    {
        if (\is_string($eventId)) {
            $eventId = EventId::fromString($eventId);
        }

        if (!($eventId instanceof EventId)) {
            throw new \InvalidArgumentException(sprintf('Event ID must be a string or "%s", got "%s".', EventId::class, get_debug_type($eventId)));
        }

        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @param EventType|string $type
     *
     * @return $this
     */
    public function withType($type): self
    {
        $this->eventType = EventDescriptorParameterTransformer::stringOrEventType($type);

        return $this;
    }

    /**
     * @param EventData|array $data
     *
     * @return $this
     */
    public function withData($data): self
    {
        $this->eventData = EventDescriptorParameterTransformer::arrayOrEventData($data);

        return $this;
    }

    /**
     * @param EventMetadata|array $metadata
     *
     * @return $this
     */
    public function withMetadata($metadata): self
    {
        $this->eventMetadata = EventDescriptorParameterTransformer::arrayOrEventMetadata($metadata);

        return $this;
    }

    public function build(): EventDescriptorInterface
    {
        if (!$this->eventId) {
            throw new \InvalidArgumentException('No ID specified for Event');
        }

        if (!$this->eventType) {
            throw new \InvalidArgumentException('No Type specified for Event');
        }

        return new EventDescriptor($this->eventId, $this->eventType, $this->eventData, $this->eventMetadata);
    }
}
