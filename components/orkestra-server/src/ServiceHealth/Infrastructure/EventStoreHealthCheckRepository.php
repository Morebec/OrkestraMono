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
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheck;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckRepositoryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceHealthException;

// TODO implement Event Store Repository.
class EventStoreHealthCheckRepository implements HealthCheckRepositoryInterface
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

    public function add(HealthCheck $healthCheck): void
    {
        $this->eventStore->appendToStream(
            $this->getStreamId($healthCheck->getId()),
            $this->convertEvents($healthCheck->getDomainEvents()),
            AppendStreamOptions::append()
                ->expectVersion(EventStreamVersion::initial())
        );
    }

    public function update(HealthCheck $healthCheck): void
    {
        $this->eventStore->appendToStream(
            $this->getStreamId($healthCheck->getId()),
            $this->convertEvents($healthCheck->getDomainEvents()),
            AppendStreamOptions::append()
                ->expectVersion(EventStreamVersion::fromInt($healthCheck->getVersion()->toInt()))
        );
    }

    public function findById(HealthCheckId $id): HealthCheck
    {
        try {
            $eventDescriptors = $this->eventStore->readStream(
                $this->getStreamId($id),
                ReadStreamOptions::read()
                    ->forward()
                    ->fromStart()
            );
        } catch (EventStreamNotFoundException $e) {
            // TODO Typed exception.
            throw new ServiceHealthException("Health Check $id was not found.", $e);
        }

        $convertToEvent = function (RecordedEventDescriptor $e) {
            return $this->messageNormalizer->denormalize($e->getEventData()->toArray(), $e->getEventType());
        };
        $events = array_map($convertToEvent->bindTo($this), $eventDescriptors->toArray());

        return HealthCheck::loadFromHistory(new DomainEventCollection($events));
    }

    private function getStreamId(HealthCheckId $healthCheckId): EventStreamId
    {
        return EventStreamId::fromString('health_check_'.$healthCheckId);
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
