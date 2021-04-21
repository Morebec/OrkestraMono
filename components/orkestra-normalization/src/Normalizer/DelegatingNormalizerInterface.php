<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * The delegating normalizer is a type of normalizer that delegated its work to another normalizer.
 */
interface DelegatingNormalizerInterface extends NormalizerInterface
{
    /**
     * Returns the Delegate of this Normalizer.
     *
     * @return NormalizerInterface
     */
    public function getDelegate(): ?NormalizerInterface;

    /**
     * Sets the delegate of this Normalizer.
     */
    public function setDelegate(NormalizerInterface $delegate): void;
}
