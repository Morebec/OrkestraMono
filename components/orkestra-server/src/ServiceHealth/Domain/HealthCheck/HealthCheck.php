<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\EventSourcing\Modeling\EventSourcedAggregateRootTrait;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\HealthCheckAlreadyEndedException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\HealthCheckEndedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\HealthCheckStartedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckTimeout;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckUrl;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

/**
 * Represents.
 */
class HealthCheck extends AbstractEventSourcedAggregateRoot
{
    use EventSourcedAggregateRootTrait;

    /**
     * @var HealthCheckId
     */
    private $id;

    /**
     * @var ServiceCheckUrl
     */
    private $url;

    /**
     * @var ServiceCheckTimeout
     */
    private $timeout;

    /**
     * @var ServiceCheckId
     */
    private $serviceCheckId;

    /**
     * @var ServiceId
     */
    private $serviceId;

    /**
     * @var DateTime
     */
    private $startedAt;

    /** @var bool */
    private $ended;

    public static function start(
        ServiceId $serviceId,
        ServiceCheckId $serviceCheckId,
        ServiceCheckTimeout $timeout,
        ServiceCheckUrl $url,
        DateTime $currentDateTime
    ): self {
        $c = new self();
        $c->recordDomainEvent(new HealthCheckStartedEvent(
            HealthCheckId::generate(),
            $serviceId,
            $serviceCheckId,
            $timeout,
            $url,
            $currentDateTime
        ));

        return $c;
    }

    public function end(DateTime $currentDateTime, HealthCheckResponse $response): void
    {
        if ($this->ended) {
            throw new HealthCheckAlreadyEndedException($this->id);
        }
        $this->recordDomainEvent(new HealthCheckEndedEvent(
            $this->id,
            $this->serviceId,
            $this->serviceCheckId,
            $this->timeout,
            $this->url,
            $currentDateTime,
            $response
        ));
    }

    public function getId(): HealthCheckId
    {
        return $this->id;
    }

    public function getUrl(): ServiceCheckUrl
    {
        return $this->url;
    }

    public function getTimeout(): ServiceCheckTimeout
    {
        return $this->timeout;
    }

    public function applyHealthCheckStartedEvent(HealthCheckStartedEvent $event): void
    {
        $this->id = HealthCheckId::fromString($event->healthCheckId);
        $this->url = ServiceCheckUrl::fromString($event->url);
        $this->timeout = ServiceCheckTimeout::fromInt($event->timeout);
        $this->serviceCheckId = ServiceCheckId::fromString($event->definitionId);
        $this->serviceId = ServiceId::fromString($event->serviceId);
        $this->startedAt = $event->startedAt;
        $this->ended = false;
    }

    public function applyHealthCheckEndedEvent(HealthCheckEndedEvent $event): void
    {
        $this->ended = true;
    }
}
