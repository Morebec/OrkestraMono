<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * The event sequence number serves to preserve the order of events
 * inside the event store regardless of their stream.
 */
class EventSequenceNumber
{
    /**
     * @var int
     */
    private $value;

    private function __construct(int $seqNo)
    {
        $this->value = $seqNo;
    }

    /**
     * Constructs an instance of this class using an int.
     */
    public static function fromInt(int $seqNo): self
    {
        return new self($seqNo);
    }

    /**
     * Returns the version as an int.
     */
    public function toInt(): int
    {
        return $this->value;
    }

    /**
     * Indicates if this sequence number is equal to another one.
     */
    public function isEqualTo(self $seqNo): bool
    {
        if ($this === $seqNo) {
            return true;
        }

        return $this->toInt() === $seqNo->toInt();
    }
}
