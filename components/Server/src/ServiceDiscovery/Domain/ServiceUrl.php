<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain;

class ServiceUrl
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        if (!$value) {
            throw new \InvalidArgumentException('A service URL cannot be blank');
        }

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
}
