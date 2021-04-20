<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\Modeling\EntityInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthStatus;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceHealthException;

/**
 * Represents a type of check to be performed on a service to determine its health.
 */
class ServiceCheck implements EntityInterface
{
    /** @var ServiceCheckId */
    private $id;

    /** @var HealthStatus */
    private $status;

    /**
     * Display name of this check.
     *
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /** @var ServiceCheckInterval */
    private $interval;

    /** @var ServiceCheckThreshold Represents the number of failures of this check that are tolerated before it is considered degraded. */
    private $degradationThreshold;

    /** @var ServiceCheckThreshold Represents the number of failures of this check that are tolerated before it is considered down. */
    private $failureThreshold;

    /** @var ServiceCheckThreshold Represents the number of success calls for this check before it can transition back to being healthy */
    private $successThreshold;
    /**
     * @var ServiceCheckUrl
     */
    private $url;

    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var ServiceCheckTimeout
     */
    private $timeout;

    public function __construct(
        ServiceCheckId $id,
        ?string $name,
        ?string $description,
        ServiceCheckUrl $url,
        ServiceCheckInterval $interval,
        ServiceCheckThreshold $failureThreshold,
        ServiceCheckThreshold $degradationThreshold,
        ServiceCheckThreshold $successThreshold,
        ServiceCheckTimeout $timeout,
        bool $enabled
    ) {
        $this->id = $id;
        $this->status = HealthStatus::UNHEALTHY();

        $this->name = $name ?: (string) $this->id;
        $this->description = $description ?: null;
        $this->url = $url;
        $this->interval = $interval;
        $this->failureThreshold = $failureThreshold;
        $this->degradationThreshold = $degradationThreshold;
        $this->successThreshold = $successThreshold;
        $this->timeout = $timeout;
        $this->enabled = $enabled;
    }

    /**
     * Records a new status.
     */
    public function changeStatus(HealthStatus $status): void
    {
        $this->status = $status;
    }

    public function getStatusThreshold(HealthStatus $status)
    {
        if ($status->isEqualTo(HealthStatus::HEALTHY())) {
            return $this->successThreshold;
        } elseif ($status->isEqualTo(HealthStatus::DEGRADED())) {
            return $this->degradationThreshold;
        } elseif ($status->isEqualTo(HealthStatus::UNHEALTHY())) {
            return $this->failureThreshold;
        }

        // Should never happen.
        throw new ServiceHealthException(sprintf('Unrecognized status: "%s".', $status));
    }

    public function getId(): ServiceCheckId
    {
        return $this->id;
    }

    public function getDegradationThreshold(): ServiceCheckThreshold
    {
        return $this->degradationThreshold;
    }

    public function getFailureThreshold(): ServiceCheckThreshold
    {
        return $this->failureThreshold;
    }

    public function getSuccessThreshold(): ServiceCheckThreshold
    {
        return $this->successThreshold;
    }

    public function getInterval(): ServiceCheckInterval
    {
        return $this->interval;
    }

    public function getUrl(): ServiceCheckUrl
    {
        return $this->url;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTimeout(): ServiceCheckTimeout
    {
        return $this->timeout;
    }

    public function getStatus(): HealthStatus
    {
        return $this->status;
    }

    public function hasStatus(HealthStatus $status): bool
    {
        return $this->status->isEqualTo($status);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Indicates if this service check is equal to another one.
     */
    public function isEqualTo(self $check): bool
    {
        if (!$this->id->isEqualTo($check->getId())) {
            return false;
        }

        if (!$this->status->isEqualTo($check->status)) {
            return false;
        }

        if ($this->name !== $check->name) {
            return false;
        }

        if ($this->description !== $check->description) {
            return false;
        }

        if (!$this->interval->isEqualTo($check->interval)) {
            return false;
        }

        if (!$this->degradationThreshold->isEqualTo($check->degradationThreshold)) {
            return false;
        }

        if (!$this->failureThreshold->isEqualTo($check->failureThreshold)) {
            return false;
        }

        if (!$this->successThreshold->isEqualTo($check->successThreshold)) {
            return false;
        }

        if (!$this->url->isEqualTo($check->url)) {
            return false;
        }

        if ($this->enabled !== $check->enabled) {
            return false;
        }

        if (!$this->timeout->isEqualTo($check->timeout)) {
            return false;
        }

        return true;
    }

    public function applyServiceCheckUpdated(ServiceCheckUpdatedEvent $event): void
    {
        $this->name = $event->name;
        $this->description = $event->description;
        $this->url = ServiceCheckUrl::fromString($event->url);
        $this->interval = ServiceCheckInterval::fromInt($event->interval);
        $this->failureThreshold = ServiceCheckThreshold::fromInt($event->failureThreshold);
        $this->degradationThreshold = ServiceCheckThreshold::fromInt($event->degradationThreshold);
        $this->successThreshold = ServiceCheckThreshold::fromInt($event->successThreshold);
        $this->timeout = ServiceCheckTimeout::fromInt($event->timeout);
    }
}
