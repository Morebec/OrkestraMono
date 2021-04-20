<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents a service capable of being notified when new events are appended to
 * a stream in the event store.
 */
interface EventStoreSubscriberInterface
{
    /**
     * Called when a new event is added to a stream in the event store according tot eh way this subscriber was
     * subscribed to the event store.
     */
    public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor): void;

    public function getOptions(): SubscriptionOptions;
}
