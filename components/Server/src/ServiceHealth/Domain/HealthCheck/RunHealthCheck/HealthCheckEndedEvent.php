<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckResponse;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckTimeout;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckUrl;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

class HealthCheckEndedEvent implements DomainEventInterface
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
    public $endedAt;

    /** @var string */
    public $status;

    /** @var int|null */
    public $responseStatusCode;

    /** @var array */
    public $responsePayload;

    /** @var array */
    public $responseHeaders;

    /** @var bool */
    public $responseTimedOut;

    public function __construct(
        HealthCheckId $healthCheckId,
        ServiceId $serviceId,
        ServiceCheckId $healthCheckDefinitionId,
        ServiceCheckTimeout $timeout,
        ServiceCheckUrl $url,
        DateTime $endedAt,
        HealthCheckResponse $response
    ) {
        $this->healthCheckId = (string) $healthCheckId;
        $this->serviceId = (string) $serviceId;
        $this->serviceCheckId = (string) $healthCheckDefinitionId;
        $this->timeout = $timeout->toInt();
        $this->url = (string) $url;
        $this->endedAt = $endedAt;

        $this->status = (string) $response->getStatus();
        $this->responseTimedOut = $response->isTimeout();

        $this->responseStatusCode = $response->getStatusCode();
        $this->responsePayload = $response->getPayload();
        $this->responseHeaders = $response->getHeaders();
    }

    public static function getTypeName(): string
    {
        return 'service.health_check.ended';
    }
}
