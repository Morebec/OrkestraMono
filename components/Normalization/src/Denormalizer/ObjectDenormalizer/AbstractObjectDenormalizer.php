<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\Denormalizer\UnsupportedDenormalizerValueException;

/**
 * Class ObjectDenormalizer
 * Denormalizing an object is a more complex task that requires deciding what fields inside
 * of the associated class can be denormalized.
 * This class provides an abstract implementation that can be extended for different types of
 * classes or use cases.
 * To do that, this class uses a list of specialized denormalizers that are called ClassPropertyDenormalizers.
 */
abstract class AbstractObjectDenormalizer implements DenormalizerInterface
{
    /**
     * @var ClassInstantiatorInterface
     */
    protected $instantiator;

    /**
     * @var ClassPropertyDenormalizerInterface[]
     */
    protected $propertyDenormalizers;

    public function __construct()
    {
        $this->instantiator = new DoctrineInstantiator();
        $this->propertyDenormalizers = [];
    }

    public function denormalize(DenormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($context, $this);
        }

        $className = $context->getTypeName();
        $instance = $this->instantiator->instantiate($className);

        foreach ($context->getValue() as $key => $value) {
            $propertyContext = new ClassPropertyDenormalizationContext($key, $value, 'undefined', $context);
            $denormalizedValue = $this->denormalizeProperty($propertyContext);
            $this->applyPropertyValueToInstance($key, $denormalizedValue, $instance);
        }

        return $instance;
    }

    public function supports($context): bool
    {
        $typeName = $context->getTypeName();

        return \is_array($context->getValue()) && (class_exists($typeName) || interface_exists($typeName));
    }

    /**
     * Denormalizes an class property by returning the value that should be set on the class instance.
     *
     * @return mixed
     */
    protected function denormalizeProperty(ClassPropertyDenormalizationContextInterface $propertyContext)
    {
        foreach ($this->propertyDenormalizers as $propertyDenormalizer) {
            if ($propertyDenormalizer->supports($propertyContext)) {
                return $propertyDenormalizer->denormalize($propertyContext);
            }
        }

        throw new UnsupportedDenormalizerValueException($propertyContext, $this);
    }

    /**
     * Applies a property value to an class instance.
     */
    abstract protected function applyPropertyValueToInstance(string $propertyName, $denormalizedValue, object $instance): void;
}
