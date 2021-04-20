<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

/**
 * Normalizer capable of normalizing arrays.
 * In order to do so it will delegate the denormalization of the array items
 * to a delegate.
 */
class ArrayDenormalizer implements DelegatingDenormalizerInterface
{
    /**
     * Delegate normalizer to denormalize individual object properties.
     *
     * @var DenormalizerInterface|null
     */
    private $delegate;

    public function __construct(?DenormalizerInterface $delegate = null)
    {
        $this->delegate = $delegate;
    }

    public function denormalize(DenormalizationContextInterface $context)
    {
        $className = $context->getTypeName();
        $value = $context->getValue();

        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($value, $this);
        }

        if (!$this->delegate) {
            throw new DelegateNotSetOnDenormalizerException($this);
        }

        $resultingArray = [];

        foreach ($context->getValue() as $itemKey => $itemValue) {
            $itemType = str_replace('[]', '', $className);
            if ($itemType === 'array') {
                // We have a simple array without any type definition.
                // We'll need to try to auto detect the type of the values
                $itemType = get_debug_type($itemValue);
            }
            $resultingArray[$itemKey] = $this->delegate->denormalize(
                new DenormalizationContext($itemValue, $itemType, $context)
            );
        }

        return $resultingArray;
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        $typeName = $context->getTypeName();
        $value = $context->getValue();

        return \is_array($value) && (str_starts_with($typeName, 'array') || str_ends_with($typeName, '[]'));
    }

    public function getDelegate(): DenormalizerInterface
    {
        return $this->delegate;
    }

    public function setDelegate(DenormalizerInterface $delegate): void
    {
        $this->delegate = $delegate;
    }
}
