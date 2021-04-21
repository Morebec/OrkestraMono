<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

use Throwable;

class DenormalizationException extends \RuntimeException implements DenormalizationExceptionInterface
{
    public function __construct($message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
