<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Normalizes a null value.
 */
class NullValueNormalizer implements NormalizerInterface
{
    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        return null;
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return $context->getValue() === null;
    }
}
