<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Normalizes an Object through the reflection API.
 */
class ObjectReflectionNormalizer implements NormalizerInterface
{
    /**
     * Delegate normalizer to denormalize individual object properties.
     *
     * @var NormalizerInterface
     */
    private $delegate;

    public function __construct(NormalizerInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }
        $object = $context->getValue();

        $properties = [];

        $reflectionObject = new \ReflectionObject($object);
        do {
            foreach ($reflectionObject->getProperties() as $property) {
                if (!$property->isPublic()) {
                    $property->setAccessible(true);
                }
                $propertyValue = $property->getValue($object);

                $properties[$property->getName()] = $this->delegate->normalize(new NormalizationContext($propertyValue, $context));
            }
        } while ($reflectionObject = $reflectionObject->getParentClass());

        return $properties;
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return \is_object($context->getValue());
    }
}
