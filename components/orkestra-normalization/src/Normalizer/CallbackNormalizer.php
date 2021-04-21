<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Can be used to define custom normalizers using callback functions.
 */
class CallbackNormalizer implements NormalizerInterface
{
    /**
     * @var callable
     */
    private $supportsCallback;
    /**
     * @var callable
     */
    private $normalizeCallback;

    public function __construct(callable $supportsCallback, callable $normalizeCallback)
    {
        $this->supportsCallback = $supportsCallback;
        $this->normalizeCallback = $normalizeCallback;
    }

    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        return ($this->normalizeCallback)($context);
    }

    public function supports($context): bool
    {
        return ($this->supportsCallback)($context);
    }
}
