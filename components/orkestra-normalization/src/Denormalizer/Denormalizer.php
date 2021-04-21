<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer\ReflectionClassDenormalizer;

/**
 * This denormalizer uses multiple other denormalizes, in order to be able to cover a wide range of cases.
 * It also supports registering new custom denormalizers.
 */
class Denormalizer implements DenormalizerInterface
{
    /** @var DenormalizerInterface[] */
    private $builtInDenormalizers;

    /**
     * @var DenormalizerInterface[]
     */
    private $denormalizers;

    public function __construct(array $denormalizers = [])
    {
        $this->denormalizers = [];
        foreach ($denormalizers as $denormalizer) {
            $this->addDenormalizer($denormalizer);
        }

        // Setup Builtin Normalizers.
        $this->builtInDenormalizers = [
            NullValueDenormalizer::class => new NullValueDenormalizer(),
            DateTimeDenormalizer::class => new DateTimeDenormalizer(),
            ReflectionClassDenormalizer::class => new ReflectionClassDenormalizer($this),
            MixedDenormalizer::class => new MixedDenormalizer($this),
            ScalarValueDenormalizer::class => new ScalarValueDenormalizer(),
            ArrayDenormalizer::class => new ArrayDenormalizer($this),
        ];
    }

    public function denormalize(DenormalizationContextInterface $context)
    {
        // Check with Custom Normalizers.
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supports($context)) {
                return $denormalizer->denormalize($context);
            }
        }

        // Check with builtin normalizers.
        foreach ($this->builtInDenormalizers as $denormalizer) {
            if ($denormalizer->supports($context)) {
                return $denormalizer->denormalize($context);
            }
        }

        throw new UnsupportedDenormalizerValueException($context, $this);
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        // Check with Custom Normalizers.
        foreach ($this->denormalizers as $normalizer) {
            if ($normalizer->supports($context)) {
                return true;
            }
        }

        // Check with builtin normalizers.
        foreach ($this->builtInDenormalizers as $normalizer) {
            if ($normalizer->supports($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a new denormalizer.
     */
    public function addDenormalizer(DenormalizerInterface $denormalizer): void
    {
        if ($denormalizer instanceof DelegatingDenormalizerInterface) {
            if (!$denormalizer->getDelegate()) {
                $denormalizer->setDelegate($this);
            }
        }

        array_unshift($this->denormalizers, $denormalizer);
    }
}
