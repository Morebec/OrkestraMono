<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;

/**
 * This denormalizer is used to easily create Class Specific denormalizers.
 * It supports to throw exceptions on missing keys in the denormalized form or
 * provide a default value.
 */
class ClassSpecificDenormalizer extends ReflectionClassDenormalizer
{
    /**
     * @var string
     */
    protected $className;

    /**
     * List of callbacks to be executed when certain keys are missing.
     *
     * @var array
     */
    protected $absentKeysCallbacks;

    public function __construct(
        string $className,
        array $absentKeysCallbacks = [],
        array $propertyDenormalizers = [],
        ?DenormalizerInterface $delegate = null
    ) {
        parent::__construct($delegate);
        $this->className = $className;
        $this->propertyDenormalizers = $propertyDenormalizers;
        $this->absentKeysCallbacks = $absentKeysCallbacks;
    }

    public function denormalize(DenormalizationContextInterface $context)
    {
        // Check denormalized form for preconditions
        $denormalizedForm = $context->getValue();

        foreach ($this->absentKeysCallbacks as $key => $callable) {
            $denormalizedForm = $callable($context);
        }

        return parent::denormalize(
            new DenormalizationContext($denormalizedForm, $context->getTypeName(), $context->getParentContext())
        );
    }

    public function supports($context): bool
    {
        return parent::supports($context) && is_a($context->getTypeName(), $this->className, true);
    }
}
