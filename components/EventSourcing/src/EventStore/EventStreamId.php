<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use InvalidArgumentException;

/**
 * Represents the Stream ID of an event stream.
 */
class EventStreamId
{
    /**
     * @var string
     */
    private $value;

    /**
     * EventId constructor.
     */
    private function __construct(string $streamId)
    {
        if (!$streamId) {
            throw new InvalidArgumentException('An Event Stream ID cannot be null');
        }

        $this->value = $streamId;
    }

    /**
     * Returns a string representation of an Event Stream ID.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Constructs a new instance of this class from a string representation
     * of an Event Stream ID.
     */
    public static function fromString(string $streamId): self
    {
        return new self($streamId);
    }

    /**
     * Indicates if this Event Stream ID is equal to another one.
     */
    public function isEqualTo(self $streamId): bool
    {
        if ($this === $streamId) {
            return true;
        }

        return $this->value === $streamId->value;
    }
}
