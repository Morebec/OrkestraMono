<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * Context in which a normalization process is occurring.
 */
interface NormalizationContextInterface
{
    /**
     * Returns the value that needs to be normalized in the current context.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Returns the parent context of this current context.
     * This can happen in cases where some inner properties of objects need to be normalized.
     */
    public function getParentContext(): ?self;
}
