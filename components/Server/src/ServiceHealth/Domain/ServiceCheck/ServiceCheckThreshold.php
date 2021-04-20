<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

/**
 * Represents a given threshold for certain health check with specific status of a service check.
 */
class ServiceCheckThreshold
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

    public function isEqualTo(self $degradationThreshold): bool
    {
        return $this->value === $degradationThreshold->value;
    }
}
