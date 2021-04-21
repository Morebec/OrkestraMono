<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

/**
 * Interface for Class Property Denormalizers.
 */
interface ClassPropertyDenormalizerInterface
{
    /**
     * Denormalizes a class Property.
     *
     * @return mixed
     */
    public function denormalize(ClassPropertyDenormalizationContextInterface $context);

    /**
     * Indicates if this denormalizer supports a given property.
     */
    public function supports(ClassPropertyDenormalizationContextInterface $context): bool;
}
