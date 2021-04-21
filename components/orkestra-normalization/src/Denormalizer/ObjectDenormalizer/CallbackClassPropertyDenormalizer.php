<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

/**
 * Class Property denormalizer based on callbacks.
 */
class CallbackClassPropertyDenormalizer implements ClassPropertyDenormalizerInterface
{
    /** @var callable */
    private $denormalizeCallback;

    /** @var callable */
    private $supportsCallback;

    public function __construct($supportsCallback, $denormalizeCallback)
    {
        $this->supportsCallback = $supportsCallback;
        $this->denormalizeCallback = $denormalizeCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(ClassPropertyDenormalizationContextInterface $context)
    {
        return ($this->denormalizeCallback)($context);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ClassPropertyDenormalizationContextInterface $context): bool
    {
        return ($this->supportsCallback)($context);
    }
}
