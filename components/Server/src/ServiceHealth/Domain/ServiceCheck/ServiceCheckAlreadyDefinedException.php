<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceHealthException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Throwable;

/**
 * Thrown when a service check was expected not to be defined on a service.
 */
class ServiceCheckAlreadyDefinedException extends ServiceHealthException
{
    public function __construct(ServiceId $serviceId, ServiceCheckId $checkId, Throwable $previous = null)
    {
        parent::__construct(sprintf('Service Check "%s" is already defined on Service "%s".', $checkId, $serviceId), $previous);
    }
}
