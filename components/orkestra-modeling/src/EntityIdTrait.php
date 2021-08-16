<?php

namespace Morebec\Orkestra\Modeling;

use Ramsey\Uuid\Uuid;

trait EntityIdTrait
{
    private string $value;

    /**
     * Converts this ID to a string.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Creates an instance of this class from a string representation.
     *
     * @return static
     */
    public static function fromString(string $id): self
    {
        return new static($id);
    }

    /**
     * Generates a new unique ID.
     *
     * @return static
     */
    public static function generate(): self
    {
        return new static(Uuid::uuid4());
    }

    /**
     * Indicates if this ID is equal to another.
     *
     * @param EntityIdTrait $o
     */
    public function isEqualTo(self $o): bool
    {
        return $this->value === $o->value;
    }
}
