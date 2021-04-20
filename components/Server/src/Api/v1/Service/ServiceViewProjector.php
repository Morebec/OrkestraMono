<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\Service;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Projection\AbstractTypedEventProjector;
use Morebec\Orkestra\EventSourcing\Projection\EventHandlerMethodResolvingProjectorTrait;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;
use Morebec\Orkestra\OrkestraServer\Core\Persistence\PostgreSqlObjectStore;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService\ServiceRegisteredEvent;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceInitializedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService\ServiceUnregisteredEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking\ServiceHealthCheckingEnabledEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckAddedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckRemovedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckStatusChangedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckUpdatedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceStatusChangedEvent;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;

class ServiceViewProjector extends AbstractTypedEventProjector
{
    use EventHandlerMethodResolvingProjectorTrait;

    /**
     * @var PostgreSqlObjectStore
     */
    private $store;

    public function __construct(
        PostgreSqlDocumentStore $documentStore,
        ObjectNormalizerInterface $objectNormalizer,
        MessageNormalizerInterface $messageNormalizer
    ) {
        parent::__construct($messageNormalizer);
        $this->store = new PostgreSqlObjectStore(
            $documentStore,
            self::getTypeName(),
            ServiceView::class,
            $objectNormalizer
        );
    }

    public function boot(): void
    {
    }

    public function shutdown(): void
    {
    }

    public function reset(): void
    {
        $this->store->clear();
    }

    public static function getTypeName(): string
    {
        return 'api_v1_service';
    }

    public function onServiceInitialized(ServiceInitializedEvent $event): void
    {
        $service = new ServiceView();
        $service->id = $event->serviceId;

        $this->addService($service);
    }

    public function onServiceRegistered(ServiceRegisteredEvent $event, RecordedEventDescriptor $descriptor): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);
        $service->name = $event->name;
        $service->description = $event->description;
        $service->url = $event->url;
        $service->handledMessages = $event->handledMessages;
        $service->metadata = $event->metadata;
        $service->lastRegisteredAt = $descriptor->getRecordedAt();
        $service->nbRegistrations++;

        $this->updateService($service);
    }

    public function onServiceUnregistered(ServiceUnregisteredEvent $event)
    {
        $this->store->removeObject($event->serviceId);
    }

    public function onServiceCheckAdded(ServiceCheckAddedEvent $event): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);
        $serviceCheck = new ServiceCheckView();

        $serviceCheck->id = $event->serviceCheckId;
        $serviceCheck->status = $event->status;
        $serviceCheck->name = $event->name;
        $serviceCheck->description = $event->description;
        $serviceCheck->interval = $event->interval;
        $serviceCheck->degradationThreshold = $event->degradationThreshold;
        $serviceCheck->failureThreshold = $event->failureThreshold;
        $serviceCheck->successThreshold = $event->successThreshold;
        $serviceCheck->url = $event->url;
        $serviceCheck->enabled = $event->enabled;
        $serviceCheck->timeout = $event->timeout;
        $serviceCheck->lastCheckedAt = null;

        $service->serviceChecks[] = $serviceCheck;

        $this->updateService($service);
    }

    public function onServiceCheckUpdated(ServiceCheckUpdatedEvent $event, RecordedEventDescriptor $descriptor): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);
        foreach ($service->serviceChecks as $serviceCheck) {
            if ($serviceCheck->id !== $event->serviceCheckId) {
                continue;
            }

            $serviceCheck->status = $event->status;
            $serviceCheck->name = $event->name;
            $serviceCheck->description = $event->description;
            $serviceCheck->interval = $event->interval;
            $serviceCheck->degradationThreshold = $event->degradationThreshold;
            $serviceCheck->failureThreshold = $event->failureThreshold;
            $serviceCheck->successThreshold = $event->successThreshold;
            $serviceCheck->url = $event->url;
            $serviceCheck->enabled = $event->enabled;
            $serviceCheck->timeout = $event->timeout;
            $serviceCheck->lastCheckedAt = $descriptor->getRecordedAt();
        }

        $this->updateService($service);
    }

    public function onServiceCheckRemoved(ServiceCheckRemovedEvent $event): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);

        $serviceCheckId = $event->serviceCheckId;
        $service->serviceChecks = array_filter($service->serviceChecks, static function (ServiceCheckView $serviceCheck) use ($serviceCheckId) {
            return $serviceCheck->id !== $serviceCheckId;
        });
        $service->serviceChecks = array_values($service->serviceChecks);

        $this->updateService($service);
    }

    public function onServiceCheckStatusChanged(ServiceCheckStatusChangedEvent $event): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);

        foreach ($service->serviceChecks as $serviceCheck) {
            if ($serviceCheck->id === $event->serviceCheckId) {
                $serviceCheck->status = $event->status;
                break;
            }
        }

        $this->updateService($service);
    }

    public function onServiceStatusChanged(ServiceStatusChangedEvent $event): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);

        $service->status = $event->status;

        $this->updateService($service);
    }

    public function onServiceHealthCheckingEnabled(ServiceHealthCheckingEnabledEvent $event): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);

        $service->healthCheckingEnabled = true;

        $this->updateService($service);
    }

    public function onServiceHealthCheckingDisabled(ServiceHealthCheckingEnabledEvent $event): void
    {
        /** @var ServiceView $service */
        $service = $this->store->findById($event->serviceId);

        $service->healthCheckingEnabled = false;

        $this->updateService($service);
    }

    protected function updateService(ServiceView $service): void
    {
        $service->version++;
        $this->store->updateObject($service->id, $service);
    }

    protected function addService(ServiceView $service): void
    {
        $service->version++;
        $this->store->addObject($service->id, $service);
    }
}
