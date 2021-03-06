<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterChain;

/**
 * Decorator of an Event Store that is capable of upcasting events to match new schemas.
 */
class UpcastingEventStoreDecorator implements EventStoreInterface
{
    private EventStoreInterface $eventStore;

    private UpcasterChain $upcasterChain;

    public function __construct(EventStoreInterface $eventStore, UpcasterChain $upcasterChain)
    {
        $this->eventStore = $eventStore;
        $this->upcasterChain = $upcasterChain;
    }

    public function getGlobalStreamId(): EventStreamId
    {
        return $this->eventStore->getGlobalStreamId();
    }

    public function appendToStream(EventStreamId $streamId, iterable $eventDescriptors, AppendStreamOptions $options): void
    {
        $this->eventStore->appendToStream($streamId, $eventDescriptors, $options);
    }

    public function readStream(EventStreamId $streamId, ReadStreamOptions $options): StreamedEventCollectionInterface
    {
        $recordedEvents = $this->eventStore->readStream($streamId, $options);

        if ($this->upcasterChain->isEmpty()) {
            return $recordedEvents;
        }

        $events = [];
        /** @var RecordedEventDescriptor $recordedEvent */
        foreach ($recordedEvents as $recordedEvent) {
            $upcastedEvents = $this->upcastEvent($recordedEvent);

            /** @var UpcastableEventDescriptor $event */
            foreach ($upcastedEvents as $event) {
                $events[] = $event;
            }
        }

        return new StreamedEventCollection($streamId, $events);
    }

    public function getStream(EventStreamId $streamId): ?EventStreamInterface
    {
        return $this->eventStore->getStream($streamId);
    }

    public function streamExists(EventStreamId $streamId): bool
    {
        return $this->eventStore->streamExists($streamId);
    }

    public function subscribeToStream(EventStreamId $streamId, EventStoreSubscriberInterface $subscriber): void
    {
        $this->eventStore->subscribeToStream($streamId, $subscriber);
    }

    public function truncateStream(EventStreamId $streamId, TruncateStreamOptions $options): void
    {
        $this->eventStore->truncateStream($streamId, $options);
    }

    /**
     * Upcast an event and returns the result as an array as when upcasting an event might
     * have been split into many new ones.
     */
    private function upcastEvent(RecordedEventDescriptor $event): array
    {
        $upcastableEventDescriptor = UpcastableEventDescriptor::fromRecordedEventDescriptor($event);
        if ($this->upcasterChain->supports($upcastableEventDescriptor)) {
            return $this->upcasterChain->upcast($upcastableEventDescriptor);
        }

        return [$event];
    }
}
