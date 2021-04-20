<?php

namespace Morebec\Orkestra\Normalization\Normalizer\ObjectNormalizer;

use Morebec\Orkestra\Normalization\Normalizer\NormalizationContextInterface;
use Morebec\Orkestra\Normalization\Normalizer\NormalizerInterface;
use Morebec\Orkestra\Normalization\Normalizer\UnsupportedNormalizerValueException;

/**
 * The Fluent denormalizer allows to easily and fluently configure denormalizers.
 */
class FluentNormalizer implements NormalizerInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var callable
     */
    private $callable;

    public function __construct(string $className)
    {
        $this->className = $className;
        $this->asNull();
    }

    public static function for(string $className): self
    {
        return new self($className);
    }

    public function as(callable $callable): self
    {
        $this->callable = $callable;

        return $this;
    }

    public function asString(): self
    {
        return $this->as(static function (NormalizationContextInterface $context) {
            return (string) $context->getValue();
        });
    }

    public function asNull(): self
    {
        return $this->asValue(null);
    }

    public function asValue($value): self
    {
        return $this->as(static function (NormalizationContextInterface $context) use ($value) {
            return $value;
        });
    }

    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        return ($this->callable)($context);
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        return is_a($context->getValue(), $this->className);
    }
}
