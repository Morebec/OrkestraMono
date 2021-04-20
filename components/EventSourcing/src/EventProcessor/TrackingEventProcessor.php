<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollectionInterface;
use RuntimeException;

/**
 * A {@link TrackingEventProcessor} allows to chase the event store for events and take action
 * upon finding out new events. It delegates the actual work the an {@link EventPublisherInterface}.
 *
 * To perform this work, the {@link TrackingEventProcessor} uses a position (being a sequence number or a stream version number) from an event store
 * or queue.
 *
 * To track this between reboots, the {@link TrackingEventProcessor} stores this information in a {@EventStorePositionStorageInterface}.
 * For that storage, it is advised to use the same data store as the one used to store the actual data of the internal {@link EventPublisherInterface}.
 * For example, if a processor works on denormalizing events in a PostgreSQL database, it would be wise to store this
 * information in the same PostgreSQL database to make use of transactions.
 * This is a simpler solution to Two-phase Transaction Protocols for improving resiliency in case there is a failure between
 * the time the event was successfully published and this tracker to acknowledge this fact in the storage.
 *
 * Every {@link TrackingEventProcessor} has to use a unique Name as to not collide with other processors, since this name is used
 * with the {@EventStorePositionStorageInterface}.
 *
 * Important note on implementation: By default, the {@link TrackingEventProcessor} does not automatically run again
 * and poll the event store for new changes. Depending on the implementation of the event store used,
 * it might be a better idea to start this processor again only when new events are received from the event store.
 * To alter this behaviour, one can simply provide the correct options to this processor.
 *
 * This processor also supports {@link TrackingEventProcessorListenerInterface} that can be used to hook into
 * the work of this processor.
 */
class TrackingEventProcessor implements ListenableEventProcessor, ReplayableEventProcessorInterface
{
    /** @var bool */
    protected $running;

    /** @var EventStorePositionStorageInterface */
    protected $positionStorage;

    /**
     * @var EventPublisherInterface
     */
    protected $publisher;
    /**
     * @var EventStoreInterface
     */
    protected $eventStore;
    /**
     * @var TrackingEventProcessorOptions
     */
    protected $options;

    /** @var ListenableEventProcessorListenerInterface[] */
    protected $listeners;

    public function __construct(
        EventPublisherInterface $publisher,
        EventStoreInterface $eventStore,
        EventStorePositionStorageInterface $storage,
        TrackingEventProcessorOptions $options
    ) {
        if (!$options->name) {
            throw new \InvalidArgumentException('A Tracking Event Processor must have a name.');
        }
        $this->running = false;
        $this->positionStorage = $storage;
        $this->publisher = $publisher;
        $this->eventStore = $eventStore;
        $this->options = $options;
        $this->listeners = [];
    }

    /**
     * Starts this processor.
     * It runs once until it has delegated all detected
     * events to the publisher at the time of calling this function
     * and then stops.
     */
    public function start(): void
    {
        if (!$this->running) {
            $this->running = true;
            foreach ($this->listeners as $listener) {
                $listener->onStart($this);
            }
        }

        while (!($events = $this->getNextEvents($this->options->batchSize))->isEmpty()) {
            $this->processEvents($events);
        }

        $this->onTrackingCompleted();
    }

    public function stop(): void
    {
        $this->running = false;
        foreach ($this->listeners as $listener) {
            $listener->onStop($this);
        }
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Resets this tracker's last position to the beginning of the stream.
     */
    public function reset(): void
    {
        if ($this->isRunning()) {
            throw new RuntimeException('You must stop the TrackingEventProcessor before resetting it.');
        }
        $this->positionStorage->reset($this->options->name);
    }

    /**
     * Returns the next events to be processed. It accepts a certain max amount in order
     * to only process a batch of events.
     */
    public function getNextEvents(int $maxCount = 0): StreamedEventCollectionInterface
    {
        $position = $this->getPosition();

        return $this->eventStore->readStream(
            $this->options->streamId,
            ReadStreamOptions::read()
            ->forward()
            ->from($position)
            ->maxCount($maxCount)
        );
    }

    /**
     * Returns the sequence number or event stream version of the last processed event.
     */
    public function getPosition(): int
    {
        $position = $this->positionStorage->get($this->options->name);

        return $position !== null ? $position : ReadStreamOptions::POSITION_START;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->options->name;
    }

    /**
     * {@inheritDoc}
     */
    public function addListener(ListenableEventProcessorListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * {@inheritDoc}
     */
    public function removeListener(ListenableEventProcessorListenerInterface $listener): void
    {
        $this->listeners = array_filter($this->listeners, static function (ListenableEventProcessorListenerInterface $l) use ($listener) {
            return $listener !== $l;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function replay(int $position = null): void
    {
        if ($position === null) {
            $this->reset();
        } else {
            $this->positionStorage->set($this->getName(), $position);
        }

        $this->start();
    }

    public function getStreamId(): EventStreamId
    {
        return EventStreamId::fromString($this->options->streamId);
    }

    public function getEventStore(): EventStoreInterface
    {
        return $this->eventStore;
    }

    protected function processEvents(StreamedEventCollectionInterface $events): void
    {
        if ($this->options->storePositionForEachBatch && $this->options->storePositionBeforeProcessing) {
            $this->markEventProcessed($events->getFirst());
        }

        foreach ($events as $event) {
            $this->processEvent($event);
        }

        if ($this->options->storePositionForEachBatch && !$this->options->storePositionBeforeProcessing) {
            $this->markEventProcessed($events->getLast());
        }
    }

    protected function processEvent(RecordedEventDescriptor $event): void
    {
        // listeners beforeEvent
        foreach ($this->listeners as $listener) {
            $listener->beforeEvent($this, $event);
        }

        if (!$this->options->storePositionForEachBatch && $this->options->storePositionBeforeProcessing) {
            $this->markEventProcessed($event);
        }

        $this->publisher->publishEvent($event);

        if (!$this->options->storePositionForEachBatch && !$this->options->storePositionBeforeProcessing) {
            $this->markEventProcessed($event);
        }

        // listeners afterEvent
        foreach ($this->listeners as $listener) {
            $listener->afterEvent($this, $event);
        }
    }

    /**
     * Marks an event has being processed.
     */
    protected function markEventProcessed(RecordedEventDescriptor $event): void
    {
        $position = $this->options->streamId->isEqualTo($this->eventStore->getGlobalStreamId()) ?
            $event->getSequenceNumber()->toInt() :
            $event->getStreamVersion()->toInt()
        ;
        $this->positionStorage->set($this->options->name, $position);
    }

    protected function onTrackingCompleted(): void
    {
        $this->stop();
    }
}
