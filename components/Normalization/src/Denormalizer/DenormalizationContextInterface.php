<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

interface DenormalizationContextInterface
{
    /**
     * Returns the parent context in case of nested values.
     */
    public function getParentContext(): ?self;

    /**
     * Returns the value that should be denormalized.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Name of the type or class to which this value should be denormalized.
     */
    public function getTypeName(): string;
}
