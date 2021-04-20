<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Connection;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\SubscribedTrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventProcessor\SubscribedTrackingEventProcessorOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollectionInterface;

/**
 * This event processor is tailored for the {@link PostgreSqlEventStore}.
 * It wraps the {@link SubscribedTrackingEventProcessor} with Database transactions.
 * Implementation Notes:
 * Given that the {@link EventStoreInterface} can be decorated, we cannot directly use the {@link PostgreSqlEventStore} in all circumstances.
 * However in some other cases, it is necessary to access some PostgreSQL specific features, therefore, the actual
 * instance of the {@link PostgreSqlEventStore} must be injected additionally. This is why both are injected in the constructor.
 */
class PostgreSqlEventProcessor extends SubscribedTrackingEventProcessor
{
    /**
     * @var PostgreSqlEventStore
     */
    private $postgreSqlEventStore;

    public function __construct(
        EventPublisherInterface $publisher,
        EventStoreInterface $eventStore,
        PostgreSqlEventStore $postgreSqlEventStore,
        EventStorePositionStorageInterface $storage,
        ?SubscribedTrackingEventProcessorOptions $options = null
    ) {
        $this->postgreSqlEventStore = $postgreSqlEventStore;

        if (!$options) {
            $options = (new SubscribedTrackingEventProcessorOptions())
                ->withName('postgresql_processor')
                ->withStreamId($eventStore->getGlobalStreamId())
                ->withBatchSize(1000)
                ->storePositionAfterProcessing()
                ->storePositionPerBatch()
            ;
        }

        /* @var SubscribedTrackingEventProcessorOptions $options */
        parent::__construct($publisher, $eventStore, $storage, $options);
    }

    public function start(): void
    {
        // Do a catchup first
        parent::start();

        // Transition to live listening.
        while ($this->isRunning()) {
            $this->postgreSqlEventStore->notifySubscribers();
        }
    }

    public function replay(int $position = null): void
    {
//        $logger = new DebugStack();
//        $this->postgreSqlEventStore->getConnection()->getConfiguration()->setSQLLogger($logger);
        if ($position === null) {
            $this->reset();
        } else {
            $this->positionStorage->set($this->getName(), $position);
        }

        // We call the parent start instead of this class's implementation to avoid a blocking call
        parent::start();
    }

    protected function processEvents(StreamedEventCollectionInterface $events): void
    {
        if ($this->options->storePositionForEachBatch) {
            $processEvents = function () use ($events) {
                parent::processEvents($events);
            };
            $this->getConnection()->transactional($processEvents);

            return;
        }

        parent::processEvents($events);
    }

    protected function processEvent(RecordedEventDescriptor $event): void
    {
        if (!$this->options->storePositionForEachBatch) {
            $processEvent = function () use ($event) {
                parent::processEvent($event);
            };
            $this->getConnection()->transactional($processEvent);

            return;
        }

        parent::processEvent($event);
    }

    protected function onTrackingCompleted(): void
    {
        // Do nothing.
    }

    private function getConnection(): Connection
    {
        return $this->postgreSqlEventStore->getConnection();
    }
}
