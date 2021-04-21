<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

/**
 * The delegating denormalizer is a type of denormalizer that delegated its work to another denormalizer.
 */
interface DelegatingDenormalizerInterface extends DenormalizerInterface
{
    /**
     * Returns the Delegate of this Denormalizer.
     *
     * @return DenormalizerInterface
     */
    public function getDelegate(): ?DenormalizerInterface;

    /**
     * Sets the delegate of this denormalizer.
     */
    public function setDelegate(DenormalizerInterface $delegate): void;
}
