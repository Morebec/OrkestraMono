<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

class ServiceInitializedEvent implements DomainEventInterface
{
    /**
     * @var string
     */
    public $serviceId;

    public function __construct(ServiceId $serviceId)
    {
        $this->serviceId = (string) $serviceId;
    }

    public static function getTypeName(): string
    {
        return 'service.initialized';
    }
}
