<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

class NormalizationContext implements NormalizationContextInterface
{
    /** @var mixed */
    private $data;
    /**
     * @var NormalizationContextInterface|null
     */
    private $parentContext;

    public function __construct($data, ?NormalizationContextInterface $parentContext = null)
    {
        $this->data = $data;
        $this->parentContext = $parentContext;
    }

    public function getValue()
    {
        return $this->data;
    }

    public function getParentContext(): ?NormalizationContextInterface
    {
        return $this->parentContext;
    }
}
