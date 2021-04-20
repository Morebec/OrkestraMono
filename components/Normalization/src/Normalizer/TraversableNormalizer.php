<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Normalizer capable of normalizing Traversable values.
 */
class TraversableNormalizer implements DelegatingNormalizerInterface
{
    /**
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

        $data = [];
        $value = $context->getValue();

        foreach ($value as $key => $itemValue) {
            $data[$key] = $delegate->normalize(new NormalizationContext($itemValue, $context));
        }

        return $data;
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return $context->getValue() instanceof \Traversable;
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
