<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;

/**
 * Represents a Denormalization Context for a Class Property.
 */
interface ClassPropertyDenormalizationContextInterface extends DenormalizationContextInterface
{
    /**
     * Returns the name of the class holding the property.
     */
    public function getClassName(): string;

    /**
     * Returns the name of the property.
     */
    public function getPropertyName(): string;

    /**
     * Returns the value to be denormalized.
     *
     * @return mixed
     */
    public function getValue();
}
