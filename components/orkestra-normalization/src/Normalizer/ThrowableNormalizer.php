<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

use Throwable;

class ThrowableNormalizer implements NormalizerInterface
{
    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        /** @var Throwable $value */
        $value = $context->getValue();

        return [
            'type' => \get_class($value),
            'code' => $value->getCode(),
            'message' => $value->getMessage(),
            'line' => $value->getLine(),
            'trace' => $value->getTraceAsString(),
        ];
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return $context->getValue() instanceof Throwable;
    }
}
