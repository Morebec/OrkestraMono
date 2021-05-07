<?php

namespace Morebec\Orkestra\Privacy;

use RuntimeException;
use Throwable;

/**
 * Thrown when some personal data was expected to be found but was not.
 */
class PersonalDataNotFoundException extends RuntimeException
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function forReferenceToken(string $referenceToken, Throwable $previous = null): self
    {
        return new self(sprintf('No data found for reference token "%s" .', $referenceToken), $previous);
    }
}
