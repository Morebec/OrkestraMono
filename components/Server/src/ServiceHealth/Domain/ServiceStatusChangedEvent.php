<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

class ServiceStatusChangedEvent implements DomainEventInterface
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $status;

    /** @var string */
    public $previousStatus;

    /**
     * ServiceStatusChangedEvent constructor.
     */
    public function __construct(ServiceId $id, HealthStatus $status, HealthStatus $previousStatus)
    {
        $this->serviceId = (string) $id;
        $this->status = (string) $status;
        $this->previousStatus = (string) $previousStatus;
    }

    public static function getTypeName(): string
    {
        return 'service.status_changed';
    }
}
