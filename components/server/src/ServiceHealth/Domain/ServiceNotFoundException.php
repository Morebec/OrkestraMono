<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

/**
 * Thrown when a service was expected to be found.
 */
class ServiceNotFoundException extends \RuntimeException
{
    public function __construct(ServiceId $serviceId, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Service "%s" was not found.', $serviceId), 0, $previous);
    }
}
