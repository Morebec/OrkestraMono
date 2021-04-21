<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use InvalidArgumentException;

/**
 * Represents the type of an event.
 * This is modeled explicitly as it can be used for indexing purposes in the event store.
 */
class EventType
{
    /**
     * @var string
     */
    private $value;

    /**
     * EventType constructor.
     */
    private function __construct(string $eventType)
    {
        if (!$eventType) {
            throw new InvalidArgumentException('An Event Type cannot be null');
        }

        $this->value = $eventType;
    }

    /**
     * Returns a string representation of an Event Type.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Constructs a new instance of this class from a string representation
     * of an event Type.
     */
    public static function fromString(string $type): self
    {
        return new self($type);
    }

    /**
     * Indicates if this event Type is equal to another one.
     */
    public function isEqualTo(self $type): bool
    {
        if ($this === $type) {
            return true;
        }

        return $this->value === $type->value;
    }
}
