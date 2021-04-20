<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure;

use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamNotFoundException;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Modeling\DomainEventCollection;
use Morebec\Orkestra\Modeling\DomainEventCollectionInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\Service;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceNotFoundException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceRepositoryInterface;

class EventStoreServiceRepository implements ServiceRepositoryInterface
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;
    /**
     * @var MessageNormalizerInterface
     */
    private $messageNormalizer;

    public function __construct(EventStoreInterface $eventStore, MessageNormalizerInterface $messageNormalizer)
    {
        $this->eventStore = $eventStore;
        $this->messageNormalizer = $messageNormalizer;
    }

    public function findById(ServiceId $serviceId): Service
    {
        try {
            $eventDescriptors = $this->eventStore->readStream(
                $this->getStreamId($serviceId),
                ReadStreamOptions::read()
                    ->forward()
                    ->fromStart()
            );
        } catch (EventStreamNotFoundException $e) {
            throw new ServiceNotFoundException($serviceId, $e);
        }

        $convertToEvent = function (RecordedEventDescriptor $e) {
            return $this->messageNormalizer->denormalize($e->getEventData()->toArray(), $e->getEventType());
        };
        $events = array_map($convertToEvent->bindTo($this), $eventDescriptors->toArray());

        return Service::loadFromHistory(new DomainEventCollection($events));
    }

    public function add(Service $service): void
    {
        $this->eventStore->appendToStream(
            $this->getStreamId($service->getId()),
            $this->convertEvents($service->getDomainEvents()),
            AppendStreamOptions::append()
                ->expectVersion(EventStreamVersion::fromInt(Service::INITIAL_VERSION_NUMBER))
        );
    }

    public function update(Service $service): void
    {
        $this->eventStore->appendToStream(
            $this->getStreamId($service->getId()),
            $this->convertEvents($service->getDomainEvents()),
            AppendStreamOptions::append()
                ->expectVersion(EventStreamVersion::fromInt($service->getVersion()->toInt()))
        );
    }

    private function getStreamId(ServiceId $serviceId): EventStreamId
    {
        return EventStreamId::fromString('service_health_checking_'.$serviceId);
    }

    private function convertEvents(DomainEventCollectionInterface $collection): iterable
    {
        $convertFunction = function (DomainEventInterface $event) {
            return new EventDescriptor(
                EventId::generate(),
                EventType::fromString($event::getTypeName()),
                new EventData($this->messageNormalizer->normalize($event))
            );
        };
        $convertFunction->bindTo($this);

        return array_map($convertFunction, $collection->toArray());
    }
}
