<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Infrastructure;

use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamNotFoundException;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\Modeling\EventStoreRepository;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\Service;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceNotFoundException;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceRegistryInterface;

class EventStoreServiceRegistry extends EventStoreRepository implements ServiceRegistryInterface
{
    public function __construct(EventStoreInterface $eventStore, MessageNormalizerInterface $normalizer)
    {
        parent::__construct($eventStore, $normalizer, Service::class, 'service_');
    }

    public function findById(ServiceId $serviceId): Service
    {
        try {
            /** @var Service $service */
            $service = $this->load($serviceId);

            return $service;
        } catch (EventStreamNotFoundException $e) {
            throw new ServiceNotFoundException($serviceId, $e);
        }
    }

    public function add(Service $service): void
    {
        $this->save(
            $service->getId(),
            $service,
            AppendStreamOptions::append()
                ->expectVersion(EventStreamVersion::fromInt(Service::INITIAL_VERSION_NUMBER))
        );
    }

    public function update(Service $service): void
    {
        $this->save($service->getId(), $service);
    }

    public function remove(Service $service): void
    {
        // Note: the event store is append only; Removing is updating.
        $this->save($service->getId(), $service);
    }
}
