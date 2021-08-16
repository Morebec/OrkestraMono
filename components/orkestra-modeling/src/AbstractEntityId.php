<?php

namespace Morebec\Orkestra\Modeling;

use Ramsey\Uuid\Uuid;

abstract class AbstractEntityId
{
    private string $value;

    final public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return static
     */
    public static function fromString(string $id): self
    {
        return new static($id);
    }

    /**
     * @return static
     */
    public static function generate(): self
    {
        return new static(Uuid::uuid4());
    }

    public function isEqualTo(self $id): bool
    {
        return $this->value === $id->value;
    }
}
