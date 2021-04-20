<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

/**
 * Can be used to define custom normalizers using callback functions.
 */
class CallbackDenormalizer implements DenormalizerInterface
{
    /**
     * @var callable
     */
    private $supportsCallback;
    /**
     * @var callable
     */
    private $denormalizeCallback;

    public function __construct(callable $supportsCallback, callable $normalizeCallback)
    {
        $this->supportsCallback = $supportsCallback;
        $this->denormalizeCallback = $normalizeCallback;
    }

    public function denormalize(DenormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($context->getValue(), $this);
        }

        return ($this->denormalizeCallback)($context);
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        return ($this->supportsCallback)($context);
    }
}
