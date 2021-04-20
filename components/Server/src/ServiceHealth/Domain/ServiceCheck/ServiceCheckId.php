<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

/**
 * Represents the ID of a service check definition.
 * It must be unique on a service level.
 */
class ServiceCheckId
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function isEqualTo(self $healthCheckId)
    {
        return $this->value === $healthCheckId->value;
    }
}
