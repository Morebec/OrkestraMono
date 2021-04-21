<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationExceptionInterface;
use Throwable;

class CannotDenormalizePropertyException extends \InvalidArgumentException implements DenormalizationExceptionInterface
{
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var string
     */
    private $className;

    public function __construct(ClassPropertyDenormalizationContextInterface $context, Throwable $previous = null)
    {
        $propertyName = $context->getPropertyName();
        $propertyType = $context->getTypeName();
        $className = $context->getClassName();
        $valueType = get_debug_type($context->getValue());

        $message = sprintf(
            'Type Mismatch: Could not denormalize value of type "%s" for property "%s" with type "%s" on class "%s".',
            $valueType,
            $propertyName,
            $propertyType,
            $className
        );

        parent::__construct($message, 0, $previous);
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
