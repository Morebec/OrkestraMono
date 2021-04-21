<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationExceptionInterface;
use RuntimeException;
use Throwable;

/**
 * Thrown by the ReflectionClassDenormalizer when it cannot detect a type for a property.
 */
class UndefinedPropertyTypeException extends RuntimeException implements DenormalizationExceptionInterface
{
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var string
     */
    private $className;

    public function __construct(string $propertyName, string $className, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Could not find a type for property '$propertyName' on class '$className'", $code, $previous);
        $this->propertyName = $propertyName;
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
