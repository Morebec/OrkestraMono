<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

/**
 * Denormalizers are responsible for converting values to normalized values.
 * A normalized value is any scalar values, (associative) arrays of scalar values or null.
 */
interface DenormalizerInterface
{
    /**
     * Denormalizes a normalized value to a given type or class name as described in a DenormalizationContext.
     *
     * @return mixed scalar
     *
     * @throws UnsupportedDenormalizerValueException
     */
    public function denormalize(DenormalizationContextInterface $context);

    /**
     * Indicates if this normalizer can support a given value.
     * This should always be called before any attempt to the extract value.
     */
    public function supports(DenormalizationContextInterface $context): bool;
}
