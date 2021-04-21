<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

use InvalidArgumentException;
use Throwable;

/**
 * Thrown when a Normalizer or denormalize was unable to support a given value.
 */
class UnsupportedNormalizerValueException extends InvalidArgumentException implements NormalizerExceptionInterface
{
    public function __construct(NormalizationContextInterface $context, NormalizerInterface $normalizer, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf(
            "Values of type '%s' are not supported by normalizer '%s'",
            get_debug_type($context->getValue()),
            get_debug_type($normalizer)
        ), $code, $previous);
    }
}
