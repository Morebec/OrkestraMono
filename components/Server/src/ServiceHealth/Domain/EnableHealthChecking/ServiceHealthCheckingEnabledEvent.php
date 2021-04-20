<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

class ServiceHealthCheckingEnabledEvent implements DomainEventInterface
{
    /** @var string */
    public $serviceId;

    public function __construct(ServiceId $serviceId)
    {
        $this->serviceId = (string) $serviceId;
    }

    public static function getTypeName(): string
    {
        return 'service.health_checking.enabled';
    }
}
