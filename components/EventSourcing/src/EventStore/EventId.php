<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Represents the unique Identifier of an Event.
 */
class EventId
{
    /**
     * @var string
     */
    private $value;

    /**
     * EventId constructor.
     */
    private function __construct(string $eventId)
    {
        if (!$eventId) {
            throw new InvalidArgumentException('An Event ID cannot be null');
        }

        $this->value = $eventId;
    }

    /**
     * Returns a string representation of an Event ID.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Constructs a new instance of this class from a string representation
     * of an Event ID.
     */
    public static function fromString(string $eventId): self
    {
        return new self($eventId);
    }

    /**
     * Generates a new Event ID using UUIDv4.
     */
    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    /**
     * Indicates if this Event ID is equal to another one.
     */
    public function isEqualTo(self $eventId): bool
    {
        if ($this === $eventId) {
            return true;
        }

        return (string) $this === (string) $eventId;
    }
}
