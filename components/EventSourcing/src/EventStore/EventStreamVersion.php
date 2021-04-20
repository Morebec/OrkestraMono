<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents the Version of a Stream.
 * The version of a stream indicates the number of events that were appended to it overtime.
 * This is used to check concurrency issues.
 */
class EventStreamVersion
{
    /**
     * This initial version is actually an indication of an empty stream, as the first event appended to a stream will receive
     * the version 0.
     *
     * @var int
     */
    public const INITIAL_VERSION = -1;

    /**
     * @var int
     */
    private $value;

    /**
     * EventStreamVersion constructor.
     */
    private function __construct(int $version)
    {
        $this->value = $version;
    }

    /**
     * Returns the initial version of a stream.
     */
    public static function initial(): self
    {
        return new self(self::INITIAL_VERSION);
    }

    /**
     * Constructs an instance of this class using an int.
     */
    public static function fromInt(int $version): self
    {
        return new self($version);
    }

    /**
     * Returns the version as an int.
     */
    public function toInt(): int
    {
        return $this->value;
    }

    /**
     * Indicates if this stream version is equal to another stream version.
     */
    public function isEqualTo(self $version): bool
    {
        if ($this === $version) {
            return true;
        }

        return $this->toInt() === $version->toInt();
    }
}
