<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

class ServiceCheckTimeout
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

    public function isEqualTo(self $timeout): bool
    {
        return $this->value === $timeout->value;
    }
}
