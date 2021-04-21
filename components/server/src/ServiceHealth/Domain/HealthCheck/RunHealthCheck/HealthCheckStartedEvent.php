<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckTimeout;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckUrl;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

class HealthCheckStartedEvent implements DomainEventInterface
{
    /** @var string */
    public $healthCheckId;

    /** @var string */
    public $serviceId;

    /** @var string */
    public $serviceCheckId;

    /** @var int */
    public $timeout;

    /** @var string */
    public $url;

    /** @var DateTime */
    public $startedAt;

    public function __construct(
        HealthCheckId $healthCheckId,
        ServiceId $serviceId,
        ServiceCheckId $serviceCheckId,
        ServiceCheckTimeout $timeout,
        ServiceCheckUrl $url,
        DateTime $startedAt
    ) {
        $this->healthCheckId = (string) $healthCheckId;
        $this->serviceId = (string) $serviceId;
        $this->serviceCheckId = (string) $serviceCheckId;
        $this->timeout = $timeout->toInt();
        $this->url = (string) $url;
        $this->startedAt = $startedAt;
    }

    public static function getTypeName(): string
    {
        return 'service.health_check.started';
    }
}
