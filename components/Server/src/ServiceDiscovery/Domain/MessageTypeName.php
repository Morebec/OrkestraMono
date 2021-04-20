<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain;

class MessageTypeName
{
    public function __construct(string $value)
    {
        if (!$value) {
            throw new \InvalidArgumentException('A Message Type Name cannot be blank');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): string
    {
        return new self($value);
    }
}
