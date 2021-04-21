<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

use JsonSerializable;

/**
 * Normalizer capable of normalizing json serializable objects.
 */
class JsonSerializableNormalizer implements NormalizerInterface
{
    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        /** @var JsonSerializable $value */
        $value = $context->getValue();

        return json_decode($value->jsonSerialize(), true);
    }

    public function supports($context): bool
    {
        return $context->getValue() instanceof JsonSerializable;
    }
}
