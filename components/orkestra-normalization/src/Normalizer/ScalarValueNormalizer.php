<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Normalizes a scalar value.
 * This normalizer is quite simple, only ensuring the values received are scalar values and returning them
 * since they are already considered normalized.
 */
class ScalarValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        return $context->getValue();
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return is_scalar($context->getValue()) ? true : false;
    }
}
