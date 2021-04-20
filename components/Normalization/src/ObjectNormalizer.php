<?php

namespace Morebec\Orkestra\Normalization;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use Morebec\Orkestra\Normalization\Denormalizer\Denormalizer;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\Normalizer\NormalizationContext;
use Morebec\Orkestra\Normalization\Normalizer\Normalizer;
use Morebec\Orkestra\Normalization\Normalizer\NormalizerInterface;

/**
 * The Object Normalizer is capable of normalizing and denormalizing objects.
 */
class ObjectNormalizer implements ObjectNormalizerInterface
{
    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var Denormalizer
     */
    private $denormalizer;

    public function __construct()
    {
        $this->normalizer = new Normalizer();
        $this->denormalizer = new Denormalizer();
    }

    public function normalize($value)
    {
        return $this->normalizer->normalize(new NormalizationContext($value));
    }

    public function denormalize($value, string $className)
    {
        return $this->denormalizer->denormalize(new DenormalizationContext($value, $className));
    }

    /**
     * Adds a new normalizer.
     */
    public function addNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizer->addNormalizer($normalizer);
    }

    /**
     * Adds a new denormalizer.
     */
    public function addDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer->addDenormalizer($denormalizer);
    }
}
