<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DelegatingDenormalizerInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationExceptionInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\Denormalizer\UnsupportedDenormalizerValueException;

/**
 * Object Denormalizer using reflection.
 */
class ReflectionClassDenormalizer extends AbstractObjectDenormalizer implements DelegatingDenormalizerInterface
{
    /**
     * @var ReflectionClassPropertyTypeResolver
     */
    private $propertyTypeResolver;

    /**
     * @var DenormalizerInterface|null
     */
    private $delegate;

    public function __construct(?DenormalizerInterface $delegate = null)
    {
        parent::__construct();
        $this->propertyTypeResolver = new ReflectionClassPropertyTypeResolver();
        $this->delegate = $delegate;
    }

    public function getDelegate(): ?DenormalizerInterface
    {
        return $this->delegate;
    }

    public function setDelegate(DenormalizerInterface $delegate): void
    {
        $this->delegate = $delegate;
    }

    protected function denormalizeProperty(ClassPropertyDenormalizationContextInterface $propertyContext)
    {
        try {
            return parent::denormalizeProperty($propertyContext);
        } catch (UnsupportedDenormalizerValueException $exception) {
        }

        $className = $propertyContext->getClassName();

        try {
            $property = new \ReflectionProperty($className, $propertyContext->getPropertyName());
        } catch (\ReflectionException $e) {
            return null;
        }
        $propertyType = null;

        // Detect the type of the property if it was not possible to determine the type
        // we will delegate this work to a method that can be overloaded by subclasses.
        $types = $this->propertyTypeResolver->detectPropertyType($property);
        if (!$types) {
            throw new UndefinedPropertyTypeException($property->getName(), $property->getDeclaringClass()->getName());
        }

        $propertyContext = new ClassPropertyDenormalizationContext(
            $propertyContext->getPropertyName(),
            $propertyContext->getValue(),
            implode('|', $types),
            $propertyContext->getParentContext()
        );

        $previousException = null;
        foreach ($types as $propertyType) {
            try {
                return $this->delegate->denormalize(
                    new DenormalizationContext($propertyContext->getValue(), $propertyType, $propertyContext)
                );
            } catch (DenormalizationExceptionInterface $exception) {
                $previousException = $exception;
            }
        }

        throw new CannotDenormalizePropertyException($propertyContext, $previousException);
    }

    protected function applyPropertyValueToInstance(string $propertyName, $denormalizedValue, object $instance): void
    {
        $r = new \ReflectionClass($instance);

        try {
            $property = $r->getProperty($propertyName);
        } catch (\ReflectionException $e) {
            // Property does not exist, NOP
            return;
        }

        $property->setAccessible(true);

        $property->setValue($instance, $denormalizedValue);
    }
}
