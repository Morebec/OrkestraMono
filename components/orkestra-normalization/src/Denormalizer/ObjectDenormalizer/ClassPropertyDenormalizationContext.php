<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;

class ClassPropertyDenormalizationContext implements ClassPropertyDenormalizationContextInterface
{
    private $propertyName;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var DenormalizationContextInterface
     */
    private $parentContext;
    /**
     * @var string
     */
    private $propertyTypeName;

    /**
     * ClassPropertyDenormalizationContext constructor.
     *
     * @param mixed $value
     */
    public function __construct(string $propertyName, $value, string $propertyTypeName, DenormalizationContextInterface $parentContext)
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
        $this->parentContext = $parentContext;
        $this->propertyTypeName = $propertyTypeName;
    }

    public function getClassName(): string
    {
        return $this->parentContext->getTypeName();
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getParentContext(): ?DenormalizationContextInterface
    {
        return $this->parentContext;
    }

    public function getTypeName(): string
    {
        return $this->propertyTypeName;
    }
}
