<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationExceptionInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Throwable;

/**
 * Exception thrown in very specific contexts when a type was considered supported by
 * a denormalizer according to the typing information, but where the value was not the of the right form.
 * E.g.: a field accepting string|array but where the value is an array instead of a string.
 */
class MalformedValueForTargetTypeException extends \InvalidArgumentException implements DenormalizationExceptionInterface
{
    /**
     * @var string
     */
    private $expectedType;
    /**
     * @var DenormalizationContextInterface
     */
    private $context;

    public function __construct(string $expectedType, DenormalizationContextInterface $context, DenormalizerInterface $denormalizer, Throwable $previous = null)
    {
        $actualValue = get_debug_type($context->getValue());
        $denormalizerClass = \get_class($denormalizer);
        parent::__construct(sprintf('Unexpected value encountered: expected "%s" got "%s" in %s.', $expectedType, $actualValue, $denormalizerClass), 0, $previous);
        $this->expectedType = $expectedType;
        $this->context = $context;
    }

    public function getContext(): DenormalizationContextInterface
    {
        return $this->context;
    }

    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    public function getTypeName(): string
    {
        return $this->context->getTypeName();
    }
}
