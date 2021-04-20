<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\CallbackDenormalizer;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\Denormalizer\UnsupportedDenormalizerValueException;
use Throwable;

/**
 * The Fluent denormalizer allows to easily and fluently configure denormalizers.
 * // TODO Add Tests.
 */
class FluentDenormalizer extends ClassSpecificDenormalizer
{
    public static function for(string $className): self
    {
        return new self($className);
    }

    /**
     * Allows to define a denormalizer that will return a fixed value for the whole denormalized
     * data, instead of going by property.
     */
    public function as(callable $closure): DenormalizerInterface
    {
        $className = $this->className;

        return new CallbackDenormalizer(static function (DenormalizationContext $context) use ($className) {
            return $context->getTypeName() === $className;
        }, $closure);
    }

    /**
     * Allows to execute a given callable when a key is absent.
     *
     * @return $this
     */
    public function whenKeyAbsent(string $key, callable $callable): self
    {
        $this->absentKeysCallbacks[$key] = $callable;

        return $this;
    }

    /**
     * Allows to set a precondition to the denormalization of the class
     * that if a certain key does not exist on the source value, a default value will be provided for the given key.
     *
     * @param mixed $value
     *
     * @return FluentDenormalizer
     */
    public function whenKeyAbsentReturn(string $key, $value): self
    {
        $this->absentKeysCallbacks[$key] = static function (DenormalizationContextInterface $context) use ($value) {
            return $value;
        };

        return $this;
    }

    /**
     * Allows to set a precondition ensuring a given key exists on the array.
     *
     * @return FluentDenormalizer
     */
    public function whenKeyAbsentThrowException(string $key, ?Throwable $throwable = null): self
    {
        $self = $this;
        $this->absentKeysCallbacks[$key] = static function (DenormalizationContextInterface $context) use ($throwable, $self) {
            if (!$throwable) {
                $throwable = new UnsupportedDenormalizerValueException($context, $self);
            }
            throw $throwable;
        };

        return $this;
    }

    /**
     * Allows a key to return a different value than what it has in the denormalized form.
     *
     * @return $this
     */
    public function whereKeyReturns(string $key, $value): self
    {
        $this->propertyDenormalizers[$key] = new CallbackClassPropertyDenormalizer(
            // Supports
            static function (DenormalizationContextInterface $context) use ($key) {
                return $context->getTypeName() === $key;
            },

            // Denormalize
            static function (DenormalizationContextInterface $context) use ($value) {
                return $value;
            }
        );

        return $this;
    }

    /**
     * Allows a key to be denormalized using a callable.
     *
     * @return $this
     */
    public function whereKeyAs(string $key, callable $callable): self
    {
        $this->propertyDenormalizers[$key] = new CallbackClassPropertyDenormalizer(
        // Supports
            static function (DenormalizationContextInterface $context) use ($key) {
                return $context->getTypeName() === $key;
            },

            // Denormalize
            $callable
        );

        return $this;
    }
}
