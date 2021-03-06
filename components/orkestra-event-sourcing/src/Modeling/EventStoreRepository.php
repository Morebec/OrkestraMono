<?php

namespace Morebec\Orkestra\EventSourcing\Modeling;

use InvalidArgumentException;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamNotFoundException;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshot;
use Morebec\Orkestra\EventSourcing\Snapshot\SnapshotStoreInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\VersionedMessageInterface;
use Morebec\Orkestra\Modeling\DomainEventCollection;
use Morebec\Orkestra\Modeling\DomainEventCollectionInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;

/**
 * Implementation of an aggregate root repository to be used as an implementation based on an event store.
 * It supports Snapshotting, although would require a background process to perform the work of taking snapshot
 * from time to time.
 */
class EventStoreRepository
{
    protected EventStoreInterface $eventStore;

    protected MessageNormalizerInterface $normalizer;

    protected ?string $streamIdPrefix;

    protected string $aggregateRootClass;

    private SnapshotStoreInterface $snapshotStore;

    private ObjectNormalizerInterface $objectNormalizer;

    public function __construct(
        EventStoreInterface $eventStore,
        MessageNormalizerInterface $normalizer,
        SnapshotStoreInterface $snapshotStore,
        ObjectNormalizerInterface $objectNormalizer,
        string $aggregateRootClass,
        ?string $streamIdPrefix = null
    ) {
        $this->eventStore = $eventStore;
        $this->normalizer = $normalizer;
        $this->aggregateRootClass = $aggregateRootClass;
        $this->streamIdPrefix = $streamIdPrefix;
        $this->snapshotStore = $snapshotStore;
        $this->objectNormalizer = $objectNormalizer;

        if (!is_a($aggregateRootClass, AbstractEventSourcedAggregateRoot::class, true)) {
            throw new InvalidArgumentException(sprintf('"%s" does not extend "%s".', $aggregateRootClass, AbstractEventSourcedAggregateRoot::class));
        }
    }

    /**
     * Saves an aggregate root's event to the event store.
     */
    public function save(string $id, AbstractEventSourcedAggregateRoot $aggregateRoot, ?AppendStreamOptions $options = null): void
    {
        if (!$options) {
            $options = AppendStreamOptions::append()
                ->expectVersion(
                    EventStreamVersion::fromInt($aggregateRoot->getVersion()->toInt())
                );
        }

        $this->eventStore->appendToStream(
            $this->getStreamId($id),
            $this->convertEventsToEventDescriptors($aggregateRoot->getDomainEvents()),
            $options
        );

        $aggregateRoot->clearDomainEvents();
    }

    /**
     * Loads an aggregate root from the event store.
     *
     * @throws EventStreamNotFoundException
     */
    public function load(string $id): AbstractEventSourcedAggregateRoot
    {
        $streamId = $this->getStreamId($id);
        $snapshot = $this->getSnapshot($streamId);

        $eventDescriptors = $this->eventStore->readStream(
            $streamId,
            ReadStreamOptions::read()
                ->forward()
                ->from($snapshot->getStreamVersion()->toInt())
        );
        $convertToEvent = function (RecordedEventDescriptor $e) {
            return $this->normalizer->denormalize($e->getEventData()->toArray(), $e->getEventType());
        };
        /** @var DomainEventInterface[] $events */
        $events = array_map($convertToEvent->bindTo($this), $eventDescriptors->toArray());

        // Get aggregate instance.

        /** @var AbstractEventSourcedAggregateRoot|string $class */
        $class = $this->aggregateRootClass;

        $isInitialVersion = $snapshot->getStreamVersion()->isEqualTo(EventStreamVersion::initial());

        return $isInitialVersion ?
            $class::loadFromHistory(new DomainEventCollection($events)) :
            $this->objectNormalizer->denormalize($snapshot->getState(), $class)
        ;
    }

    protected function getStreamId(string $aggregateId): EventStreamId
    {
        return EventStreamId::fromString($this->streamIdPrefix.$aggregateId);
    }

    protected function convertEventsToEventDescriptors(DomainEventCollectionInterface $collection): array
    {
        return array_map(fn (DomainEventInterface $event) => new EventDescriptor(
            EventId::generate(),
            EventType::fromString($event::getTypeName()),
            new EventData($this->normalizer->normalize($event)),
            new EventMetadata([
                'schemaVersion' => $event instanceof VersionedMessageInterface ? $event::getMessageVersion() : null,
            ])
        ), $collection->toArray());
    }

    /**
     * Gets a snapshot for a stream with a given ID.
     * If no snapshot was taken yet, a snapshot with an empty state is returned.
     */
    protected function getSnapshot(EventStreamId $id): Snapshot
    {
        $snapshot = $this->snapshotStore->findByStreamId($id);

        // We create a fake snapshot, that mostly represents the beginning of the stream.
        $defaultSnapshot = new Snapshot($id, EventStreamVersion::initial(), EventSequenceNumber::fromInt(0), []);

        return $snapshot ?: $defaultSnapshot;
    }
}
