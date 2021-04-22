<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthStatus;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

class ServiceCheckStatusChangedEvent implements DomainEventInterface
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $serviceCheckId;

    /** @var string */
    public $status;

    /** @var string */
    public $previousHealthStatus;

    public function __construct(ServiceId $serviceId, ServiceCheckId $serviceCheckId, HealthStatus $status, HealthStatus $previousHealthStatus)
    {
        $this->serviceId = (string) $serviceId;
        $this->serviceCheckId = (string) $serviceCheckId;
        $this->status = (string) $status;
        $this->previousHealthStatus = (string) $previousHealthStatus;
    }

    public static function getTypeName(): string
    {
        return 'service.check.status_changed';
    }
}
