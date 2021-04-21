<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

use InvalidArgumentException;
use Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer\ClassPropertyDenormalizationContextInterface;
use Throwable;

class UnsupportedDenormalizerValueException extends InvalidArgumentException implements DenormalizationExceptionInterface
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var DenormalizationContextInterface
     */
    private $context;

    public function __construct(DenormalizationContextInterface $context, DenormalizerInterface $denormalizer, Throwable $previous = null)
    {
        if ($context instanceof ClassPropertyDenormalizationContextInterface) {
            $typeName = $context->getClassName();
        } else {
            $typeName = $context->getTypeName();
        }

        parent::__construct(sprintf(
            "Values of type '%s' are not supported by denormalizer '%s'",
            $typeName,
            get_debug_type($denormalizer)
        ), 0, $previous);
        $this->context = $context;
        $this->denormalizer = $denormalizer;
    }

    public function getDenormalizer(): DenormalizerInterface
    {
        return $this->denormalizer;
    }

    public function getContext(): DenormalizationContextInterface
    {
        return $this->context;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->context->getValue();
    }
}
