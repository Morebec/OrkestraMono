<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService;

use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Throwable;

/**
 * Thrown when a service was expected not to be blacklisted.
 */
class ServiceBlacklistedException extends \RuntimeException
{
    public function __construct(ServiceId $serviceId, Throwable $previous = null)
    {
        parent::__construct(sprintf('Service "%s" is blacklisted.', $serviceId), 0, $previous);
    }
}
