<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

class ServiceId
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        if (!$value) {
            throw new \InvalidArgumentException('A service ID cannot be blank');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Creates a new instance of a ServiceId from a string representation.
     *
     * @return ServiceId
     */
    public static function fromString(string $serviceId): self
    {
        return new self($serviceId);
    }
}
