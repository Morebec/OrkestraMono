<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

class NullValueDenormalizer implements DenormalizerInterface
{
    public function denormalize(DenormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($context, $this);
        }

        return null;
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        return $context->getValue() === null || $context->getTypeName() === 'null';
    }
}
