<?php

namespace Morebec\Orkestra\Collections;

/**
 * @template T
 */
class TypedCollection extends Collection
{
    protected string $className;

    public function __construct(string $className, iterable $elements = [], bool $preserveKeys = false)
    {
        $this->className = $className;
        parent::__construct($elements, $preserveKeys);
    }

    public function add($element): void
    {
        $this->validateType($element);
        parent::add($element);
    }

    public function prepend($element): void
    {
        $this->validateType($element);
        parent::prepend($element);
    }

    public function copy(): Collection
    {
        return new self($this->className, $this->elements);
    }

    /**
     * @param T $element
     */
    protected function validateType($element): void
    {
        if (!is_a($element, $this->className, true)) {
            throw new \InvalidArgumentException(sprintf('Expected element of type "%s", got "%s"', $this->className, get_debug_type($element)));
        }
    }
}
