<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceHealthException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

/**
 * Thrown when a health check was expected to be found.
 */
class ServiceCheckNotFoundException extends ServiceHealthException
{
    public function __construct(ServiceId $serviceId, ServiceCheckId $serviceCheckId, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Service Check "%" was not found on service "%s".', $serviceCheckId, $serviceId), $previous);
    }
}
