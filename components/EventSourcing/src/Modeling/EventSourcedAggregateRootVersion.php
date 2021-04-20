<?php

namespace Morebec\Orkestra\EventSourcing\Modeling;

/**
 * Value Object representing the version of an event sourced aggregate root.
 */
class EventSourcedAggregateRootVersion
{
    /**
     * @var int
     */
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function isEqualTo(self $version): bool
    {
        return $this->value === $version->value;
    }

    /**
     * Returns the next version following this version.
     */
    public function next(): self
    {
        return new self($this->value + 1);
    }
}
