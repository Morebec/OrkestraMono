<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Normalizers are responsible for converting values to normalized values.
 * A normalized value is any scalar values, (associative) arrays of scalar values or null.
 */
interface NormalizerInterface
{
    /**
     * Extracts a value.
     *
     * @return mixed scalar
     *
     * @throws UnsupportedNormalizerValueException
     */
    public function normalize(NormalizationContextInterface $context);

    /**
     * Indicates if this normalizer can support a given value.
     * This should always be called before any attempt to the extract value.
     */
    public function supports(NormalizationContextInterface $context): bool;
}
