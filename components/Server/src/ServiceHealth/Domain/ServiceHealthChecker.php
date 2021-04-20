<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\Timer\TimerHandlerInterface;
use Morebec\Orkestra\Messaging\Timer\TimerManagerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\CheckHealthTimer;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\HealthCheckEndedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\RunHealthCheckCommand;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckAddedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckNotFoundException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckRemovedEvent;

class ServiceHealthChecker implements DomainEventHandlerInterface, TimerHandlerInterface
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    /**
     * @var TimerManagerInterface
     */
    private $timerManager;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var ServiceCheckThresholdCounterRepositoryInterface
     */
    private $serviceCheckThresholdCounterRepository;

    public function __construct(
        ServiceRepositoryInterface $serviceRepository,
        TimerManagerInterface $timerManager,
        ClockInterface $clock,
        MessageBusInterface $messageBus,
        ServiceCheckThresholdCounterRepositoryInterface $serviceCheckThresholdCounter
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->timerManager = $timerManager;
        $this->clock = $clock;
        $this->messageBus = $messageBus;
        $this->serviceCheckThresholdCounterRepository = $serviceCheckThresholdCounter;
    }

    public function runHealthCheckOnCheckHealthTimer(CheckHealthTimer $timer): void
    {
        // Ensure health checking is enabled
        $serviceId = ServiceId::fromString($timer->serviceId);
        $service = $this->serviceRepository->findById($serviceId);
        if (!$service->isEnabled()) {
            return;
        }

        // Ensure health check is enabled.
        $healthCheckId = ServiceCheckId::fromString($timer->serviceCheckId);
        try {
            $healthCheck = $service->getServiceCheckById($healthCheckId);
            if (!$healthCheck->isEnabled()) {
                return;
            }
        } catch (ServiceCheckNotFoundException $exception) {
            // There might be a change the the check no longer exists between the time it was first scheduled
            // and now.
            return;
        }

        $this->messageBus->sendMessage(new RunHealthCheckCommand($serviceId, $healthCheckId));
    }

    public function onServiceCheckAdded(ServiceCheckAddedEvent $event): void
    {
        $serviceId = ServiceId::fromString($event->serviceId);
        $serviceCheckId = ServiceCheckId::fromString($event->serviceCheckId);

        $this->serviceCheckThresholdCounterRepository->add(new ServiceCheckThresholdCounter(
            $serviceId,
            $serviceCheckId,
            // By default new services are set to unhealthy.
            new HealthStatus($event->status),
            $event->failureThreshold
        ));

        $service = $this->serviceRepository->findById($serviceId);
        if (!$service->isEnabled()) {
            return;
        }

        $check = $service->getServiceCheckById($serviceCheckId);

        if ($check->isEnabled()) {
            $this->scheduleHealthCheck($serviceId, $serviceCheckId, $this->clock->now()->addSeconds($check->getInterval()->toInt()));
        }
    }

    public function onServiceCheckRemoved(ServiceCheckRemovedEvent $event): void
    {
        $this->serviceCheckThresholdCounterRepository->remove(ServiceId::fromString($event->serviceId), ServiceCheckId::fromString($event->serviceCheckId));
    }

    public function onHealthCheckEnded(HealthCheckEndedEvent $event): void
    {
        $serviceId = ServiceId::fromString($event->serviceId);
        $serviceCheckId = ServiceCheckId::fromString($event->serviceCheckId);

        $service = $this->serviceRepository->findById($serviceId);
        $check = $service->getServiceCheckById($serviceCheckId);

        // We need to determine the status of the Service Check.
        $counter = $this->serviceCheckThresholdCounterRepository->find(
            $serviceId,
            $serviceCheckId
        );
        $counter->recordHealthCheckStatus(new HealthStatus($event->status));
        $this->serviceCheckThresholdCounterRepository->update($counter);

        $count = $counter->getHealthCheckCount();
        $status = $counter->getHealthStatus();

        $threshold = $check->getStatusThreshold($status);

        if ($count >= $threshold->toInt()) {
            $service->changeServiceCheckStatus($serviceCheckId, $status);
            $this->serviceRepository->update($service);
        }
    }

    public function scheduleNextCheckWhenHealthCheckEnded(HealthCheckEndedEvent $event): void
    {
        $serviceId = ServiceId::fromString($event->serviceId);
        $service = $this->serviceRepository->findById($serviceId);

        if (!$service->isEnabled()) {
            return;
        }

        $serviceCheckId = ServiceCheckId::fromString($event->serviceCheckId);
        $check = $service->getServiceCheckById($serviceCheckId);

        if ($check->isEnabled()) {
            $this->scheduleHealthCheck($serviceId, $serviceCheckId, $event->endedAt->addSeconds($check->getInterval()->toInt()));
        }
    }

    /**
     * @param HealthCheckEndedEvent          $event
     * @param ServiceCheck\ServiceCheck|null $check
     */
    private function scheduleHealthCheck(ServiceId $serviceId, ServiceCheckId $serviceCheckId, DateTime $date): void
    {
        $timer = new CheckHealthTimer(
            $serviceId,
            $serviceCheckId,
            $date
        );
        $this->timerManager->schedule($timer);
    }
}
