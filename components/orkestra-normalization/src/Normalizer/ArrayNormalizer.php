<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Normalizer capable of normalizing arrays.
 * In order to do so it will denormalize.
 */
class ArrayNormalizer implements DelegatingNormalizerInterface
{
    /**
     * Delegate normalizer to denormalize individual object properties.
     *
     * @var NormalizerInterface
     */
    private $delegate;

    public function __construct(NormalizerInterface $delegate = null)
    {
        $this->delegate = $delegate;
    }

    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        $delegate = $this->delegate;

        return array_map(function ($itemValue) use ($delegate, $context) {
            return $delegate->normalize(new NormalizationContext($itemValue, $context));
        }, $context->getValue());
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return \is_array($context->getValue());
    }

    public function getDelegate(): ?NormalizerInterface
    {
        return $this->delegate;
    }

    public function setDelegate(NormalizerInterface $delegate): void
    {
        $this->delegate = $delegate;
    }
}
