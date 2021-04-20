<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\EventSourcing\Modeling\EventSourcedAggregateRootTrait;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\DisableHealthChecking\ServiceHealthCheckingDisabledEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking\ServiceHealthCheckingEnabledEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheck;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckAddedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckAlreadyDefinedException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckInterval;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckNotFoundException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckRemovedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckStatusChangedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckThreshold;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckTimeout;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckUpdatedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckUrl;

class Service extends AbstractEventSourcedAggregateRoot
{
    use EventSourcedAggregateRootTrait;

    /** @var ServiceId */
    private $id;

    /** @var ServiceCheck[] */
    private $checks;

    /** @var bool */
    private $enabled;

    /** @var HealthStatus */
    private $status;

    public static function create(ServiceId $serviceId): self
    {
        $s = new self();
        $s->id = $serviceId;

        return $s;
    }

    public function addServiceCheck(ServiceCheck $check): void
    {
        $found = $this->getServiceCheckById($check->getId());

        if ($found) {
            throw new ServiceCheckAlreadyDefinedException($this->id, $check->getId());
        }

        $this->recordDomainEvent(new ServiceCheckAddedEvent($this->id, $check));
    }

    public function updateServiceCheck(ServiceCheck $check): void
    {
        $found = $this->getServiceCheckById($check->getId());
        if (!$found) {
            throw new ServiceCheckNotFoundException($this->id, $check->getId());
        }

        if ($found->isEqualTo($check)) {
            return;
        }

        $this->recordDomainEvent(new ServiceCheckUpdatedEvent($this->id, $check));
    }

    public function removeServiceCheck(ServiceCheck $check): void
    {
        $found = $this->getServiceCheckById($check->getId());
        if (!$found) {
            // Won't throw exception, since it is the desired result.
            return;
        }

        $this->recordDomainEvent(new ServiceCheckRemovedEvent($this->id, $check));
    }

    public function replaceServiceChecks(array $checks): void
    {
        // Three cases
        // Remove Service Checks
        // Update Service Checks
        // Create new Service check

        // Find removed checks
        $checkIds = array_map(static function (ServiceCheck $c) {
            return $c->getId();
        }, $checks);
        foreach ($this->checks as $check) {
            if (!\in_array((string) $check->getId(), $checkIds)) {
                $this->removeServiceCheck($check);
            }
        }

        // Updates and adds
        foreach ($checks as $check) {
            if ($this->getServiceCheckById($check->getId())) {
                $this->updateServiceCheck($check);
            } else {
                $this->addServiceCheck($check);
            }
        }
    }

    public function changeServiceCheckStatus(ServiceCheckId $serviceCheckId, HealthStatus $status): void
    {
        $check = $this->getServiceCheckById($serviceCheckId);
        if ($check->getStatus()->isEqualTo($status)) {
            return;
        }

        $this->recordDomainEvent(new ServiceCheckStatusChangedEvent($this->id, $serviceCheckId, $status, $check->getStatus()));

        // Determine global status
        foreach ($this->checks as $check) {
            if ($check->hasStatus(HealthStatus::UNHEALTHY())) {
                $this->changeStatus(HealthStatus::UNHEALTHY());

                return;
            }

            if ($check->hasStatus(HealthStatus::DEGRADED())) {
                $this->changeStatus(HealthStatus::DEGRADED());

                return;
            }
        }

        $this->changeStatus(HealthStatus::HEALTHY());
    }

    public function changeStatus(HealthStatus $status): void
    {
        if ($this->status->isEqualTo($status)) {
            return;
        }

        $this->recordDomainEvent(new ServiceStatusChangedEvent($this->id, $status, $this->status));
    }

    /**
     * Enables health checking for this service.
     */
    public function enable(): void
    {
        if ($this->enabled) {
            return;
        }

        $this->recordDomainEvent(new ServiceHealthCheckingEnabledEvent($this->id));
    }

    /**
     * Disables health checking for this service.
     */
    public function disable(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->recordDomainEvent(new ServiceHealthCheckingDisabledEvent($this->id));
    }

    public function getId(): ServiceId
    {
        return $this->id;
    }

    /**
     * @return ServiceCheck[]
     */
    public function getServiceChecks(): array
    {
        return $this->checks;
    }

    public function getServiceCheckById(ServiceCheckId $id): ?ServiceCheck
    {
        foreach ($this->checks as $check) {
            if ($check->getId()->isEqualTo($id)) {
                return $check;
            }
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function applyServiceHealthCheckingEnabled(ServiceHealthCheckingEnabledEvent $event): void
    {
        $this->id = ServiceId::fromString($event->serviceId);
        $this->enabled = true;
        if ($this->checks === null) {
            $this->checks = [];
        }

        if ($this->status === null) {
            $this->status = HealthStatus::UNHEALTHY();
        }
    }

    public function applyServiceHealthCheckingDisabled(ServiceHealthCheckingDisabledEvent $event): void
    {
        $this->enabled = false;
    }

    public function applyServiceCheckAdded(ServiceCheckAddedEvent $event): void
    {
        $this->checks[] = new ServiceCheck(
            ServiceCheckId::fromString($event->serviceCheckId),
            $event->name,
            $event->description,
            ServiceCheckUrl::fromString($event->url),
            ServiceCheckInterval::fromInt($event->interval),
            ServiceCheckThreshold::fromInt($event->failureThreshold),
            ServiceCheckThreshold::fromInt($event->degradationThreshold),
            ServiceCheckThreshold::fromInt($event->successThreshold),
            ServiceCheckTimeout::fromInt($event->timeout),
            $event->enabled
        );
    }

    public function applyServiceCheckUpdated(ServiceCheckUpdatedEvent $event): void
    {
        $check = $this->getServiceCheckById(ServiceCheckId::fromString($event->serviceCheckId));
        $check->applyServiceCheckUpdated($event);
    }

    public function applyServiceCheckRemoved(ServiceCheckRemovedEvent $event): void
    {
        $checkId = ServiceCheckId::fromString($event->serviceCheckId);
        $this->checks = array_values(array_filter($this->checks, static function (ServiceCheck $check) use ($checkId) {
            return !$check->getId()->isEqualTo($checkId);
        }));
    }

    public function applyServiceCheckStatusChanged(ServiceCheckStatusChangedEvent $event): void
    {
        $check = $this->getServiceCheckById(ServiceCheckId::fromString($event->serviceCheckId));
        $check->changeStatus(new HealthStatus($event->status));
    }

    public function applyServiceStatusChanged(ServiceCheckStatusChangedEvent $event): void
    {
        $this->status = new HealthStatus($event->status);
    }
}
