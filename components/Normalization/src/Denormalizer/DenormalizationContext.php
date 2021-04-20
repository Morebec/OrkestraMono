<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

class DenormalizationContext implements DenormalizationContextInterface
{
    /** @var mixed */
    private $value;

    /**
     * @var DenormalizationContextInterface|null
     */
    private $parentContext;

    /**
     * @var string
     */
    private $typeName;

    /**
     * DenormalizationContext constructor.
     *
     * @param mixed $value
     */
    public function __construct($value, string $typeName, ?DenormalizationContextInterface $parentContext = null)
    {
        $this->value = $value;
        $this->parentContext = $parentContext;
        $this->typeName = $typeName;
    }

    public function getParentContext(): ?DenormalizationContextInterface
    {
        return $this->parentContext;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }
}
