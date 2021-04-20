<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

use Throwable;

/**
 * Thrown when a delegating normalizer does not have a delegate set.
 */
class DelegateNotSetOnDenormalizerException extends \RuntimeException implements DenormalizationExceptionInterface
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("No Delegate set for Denormalizer of type '%s'", get_debug_type($denormalizer)), $code, $previous);
        $this->denormalizer = $denormalizer;
    }

    public function getDenormalizer(): DenormalizerInterface
    {
        return $this->denormalizer;
    }
}
