<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

/**
 * Interface Interface for classes responsible for instantiating objects.
 */
interface ClassInstantiatorInterface
{
    /**
     * Instantiates a class.
     */
    public function instantiate(string $className): object;
}
