<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

/**
 * Denormalizes values with mixed as their type.
 * It works very simply by detecting the type of the value and forwarding this information
 * to a delegate.
 */
class MixedDenormalizer implements DenormalizerInterface
{
    /**
     * @var DenormalizerInterface|null
     */
    private $delegate;

    public function __construct(?DenormalizerInterface $delegate = null)
    {
        $this->delegate = $delegate;
    }

    public function denormalize(DenormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($context, $this);
        }

        if (!$this->delegate) {
            throw new DelegateNotSetOnDenormalizerException($this);
        }

        $value = $context->getValue();

        return $this->delegate->denormalize(
            new DenormalizationContext($value, get_debug_type($value), $context)
        );
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        return $context->getTypeName() === 'mixed';
    }
}
