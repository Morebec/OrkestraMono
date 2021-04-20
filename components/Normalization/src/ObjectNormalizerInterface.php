<?php

namespace Morebec\Orkestra\Normalization;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\Normalizer\NormalizerInterface;

/**
 * The Object Normalizer is capable of normalizing and denormalizing objects.
 */
interface ObjectNormalizerInterface
{
    /**
     * Normalizers a value.
     */
    public function normalize($value);

    /**
     * Denormalizers a value.
     */
    public function denormalize($value, string $className);

    /**
     * Adds a new normalizer.
     */
    public function addNormalizer(NormalizerInterface $normalizer): void;

    /**
     * Adds a new denormalizer.
     */
    public function addDenormalizer(DenormalizerInterface $denormalizer): void;
}
