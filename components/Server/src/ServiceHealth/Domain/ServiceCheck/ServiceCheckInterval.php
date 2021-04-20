<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

/**
 * Represents the interval at which a check should be performed in seconds.
 */
class ServiceCheckInterval
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

    public function isEqualTo(self $interval): bool
    {
        return $this->value === $interval->value;
    }
}
