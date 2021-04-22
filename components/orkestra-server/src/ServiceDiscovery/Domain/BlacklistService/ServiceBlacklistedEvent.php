<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;

class ServiceBlacklistedEvent implements DomainEventInterface
{
    /** @var string */
    public $serviceId;

    public function __construct(ServiceId $serviceId)
    {
        $this->serviceId = (string) $serviceId;
    }

    public static function getTypeName(): string
    {
        return 'service.blacklisted';
    }
}
