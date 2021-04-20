<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Throwable;

/**
 * Thrown when a given message was intended to be handled, but the current actor or process
 * did not have the required privileges.
 *
 * TODO: Base Exception.
 */
class UnauthorizedException extends \RuntimeException
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
