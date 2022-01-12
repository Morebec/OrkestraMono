<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * The event store watcher allows to monitor the {@link EventStoreInterface} for new events being appended to it at certain points
 * of executing a {@link EventSourcedTestCase}.
 *
 * This is used in place of a {@link EventStoreSubscriberInterface} since some implementations of the {@link EventStoreInterface}
 * can only notify their subscribers using a dedicated looped process.
 * In the context of test scenarios, it is needed to have access to these newly recorded events during the course of execution
 * of the scenario in the current process.
 *
 * It works by calling an update method which performs the work of looking at what the event store has recorded since the last
 * update call.
 */
class EventStoreWatcher
{
    private EventStoreInterface $eventStore;

    private int $lastSequenceNumberRead;

    private bool $synchronized;

    /** @var RecordedEventDescriptor[] */
    private array $recordedEvents;

    public function __construct(EventStoreInterface $eventStore)
    {
        $this->eventStore = $eventStore;
        $this->recordedEvents = [];
        $this->synchronized = false;
    }

    /**
     * Updates this watchers list of events since the last call to this method and returns these events.
     *
     * @return RecordedEventDescriptor[]
     */
    public function update(): array
    {
        if (!$this->synchronized) {
            $this->synchronize();

            return [];
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        $events = $this->eventStore->readStream(
            $this->eventStore->getGlobalStreamId(),
            ReadStreamOptions::read()->from($this->lastSequenceNumberRead)->forward()
        );

        if ($events->isEmpty()) {
            return [];
        }

        $lastEvent = $events->getLast();

        foreach ($events->toArray() as $event) {
            $this->recordedEvents[] = $event;
        }

        $this->lastSequenceNumberRead = $lastEvent->getSequenceNumber()->toInt();

        return $events->toArray();
    }

    /**
     * Returns the list of all events that were recorded since the first call to the {@link self::update()} method.
     *
     * @return RecordedEventDescriptor[]
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    private function synchronize(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $events = $this->eventStore->readStream($this->eventStore->getGlobalStreamId(), ReadStreamOptions::lastEvent());
        if ($events->isEmpty()) {
            return;
        }

        $lastEvent = $events->getLast();
        $this->lastSequenceNumberRead = $lastEvent->getSequenceNumber()->toInt();
        $this->synchronized = true;
    }
}
