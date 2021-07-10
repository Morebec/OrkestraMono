<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Throwable;

/**
 * Thrown when a configuration was expected to be configured.
 */
class NotConfiguredException extends \RuntimeException
{
    public function __construct($message = 'Not configured.', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
